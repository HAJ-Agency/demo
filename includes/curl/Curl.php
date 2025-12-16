<?php

namespace Akka\Includes\Curl;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class Curl {
   /** Optional request body; for JSON mode provide an array; for form mode also array (form_params). */
   public ?array $payload = null;

   protected Client $client;

   public function __construct(
      private string $base_uri,
      private string $endpoint,
      private string $method = 'GET',
      private ?array  $query = null,

      // ---- Auth / headers ----
      private string $auth = 'bearer',               // 'bearer' (API) | 'basic' (token)
      private ?string $access_token = null,          // bearer
      private ?string $client_id = null,             // basic
      private ?string $client_secret = null,         // basic

      private string $content = 'json',              // 'json' | 'form' | 'raw'
      private ?string $idempotency_key = null,
      private array $extra_headers = [],

      // ---- Retry / limits ----
      private bool $auto_retry = true,
      private int $max_retries = 3,
      private bool $respect_retry_after = true,
      private array $retry_on_status = [429, 503],   // extend if you want

      // ---- Misc ----
      private int $timeout = 20,
      private bool $debug = false                    // set true to error_log request/response meta
   ) {
      $this->client = new Client([
         'base_uri'    => rtrim($this->base_uri, '/') . '/',
         'timeout'     => $this->timeout,
         'http_errors' => true, // let 4xx/5xx throw; we catch & normalize
      ]);
   }

   // --------------------------
   // Public API
   // --------------------------

   /** Main runner with optional auto-retry on 429/503 and Retry-After support */
   public function run(): array {
      $attempt = 0;
      $lastErr = null;

      do {
         $attempt++;
         try {
            $result = $this->sendOnce();
            // success → return immediately (also returns rate-limit info, if present)
            return $result;
         } catch (BadResponseException | ClientException | ServerException | RequestException | ConnectException $e) {
            // See if we should retry
            $resp = method_exists($e, 'getResponse') ? $e->getResponse() : null;
            $status = $resp ? $resp->getStatusCode() : 0;

            $shouldRetry = $this->auto_retry && in_array($status, $this->retry_on_status, true);
            if (!$shouldRetry || $attempt > $this->max_retries) {
               // Give up → return normalized error (still includes rate-limit headers if present)
               return $this->normalizeException($e, $resp, $attempt - 1);
            }

            // Compute wait
            $waitMs = $this->computeBackoffMs($attempt, $resp);
            if ($this->debug) {
               error_log(sprintf(
                  '[Curl Retry] %s %s → status %s, attempt %d/%d, sleeping %dms',
                  $this->method,
                  $this->endpoint,
                  $status,
                  $attempt,
                  $this->max_retries,
                  $waitMs
               ));
            }
            usleep($waitMs * 1000);
            $lastErr = $e;
            // loop continues
         }
      } while (true);
   }

   /** Convenience factory for Fortnox API (Bearer + JSON) */
   public static function fortnoxApi(
      string $endpoint,
      string $method,
      string $access_token,
      ?array $query = null,
      ?array $json  = null,
      string $base  = 'https://api.fortnox.se/3'
   ): self {
      $c = new self(
         base_uri: $base,
         endpoint: $endpoint,
         method: $method,
         query: $query,
         auth: 'bearer',
         access_token: $access_token,
         content: 'json'
      );
      $c->payload = $json;
      return $c;
   }

   /** Convenience factory for Fortnox token/refresh (Basic + form) */
   public static function fortnoxToken(
      array $form,                // ['grant_type' => 'authorization_code'|'refresh_token', ...]
      string $client_id,
      string $client_secret,
      string $endpoint = '/oauth-v1/token',
      string $base = 'https://apps.fortnox.se'
   ): self {
      $c = new self(
         base_uri: $base,
         endpoint: $endpoint,
         method: 'POST',
         query: null,
         auth: 'basic',
         client_id: $client_id,
         client_secret: $client_secret,
         content: 'form'
      );
      $c->payload = $form;
      return $c;
   }

   // Optional mutators
   public function withIdempotencyKey(?string $key): self {
      $this->idempotency_key = $key;
      return $this;
   }
   public function withExtraHeaders(array $headers): self {
      $this->extra_headers = $headers + $this->extra_headers;
      return $this;
   }
   public function withDebug(bool $debug): self {
      $this->debug = $debug;
      return $this;
   }
   public function withAutoRetry(bool $enable, int $maxRetries = 3): self {
      $this->auto_retry = $enable;
      $this->max_retries = $maxRetries;
      return $this;
   }
   public function withRetryOn(array $statuses): self {
      $this->retry_on_status = $statuses;
      return $this;
   }

   // --------------------------
   // Internals
   // --------------------------

   /** One attempt only (no retries) */
   private function sendOnce(): array {
      $options = [
         'headers' => $this->buildHeaders(),
         'query'   => $this->query ?? [],
      ];

      if ($this->payload !== null) {
         if ($this->content === 'json') {
            $options['json'] = $this->payload;
         } elseif ($this->content === 'form') {
            $options['form_params'] = $this->payload;
         } else {
            $options['body'] = $this->payload; // raw
         }
      }

      $resp     = $this->client->request($this->method, ltrim($this->endpoint, '/'), $options);
      $status   = $resp->getStatusCode();
      $raw      = (string) $resp->getBody();
      $decoded  = json_decode($raw, true);

      $out = [
         'ok'          => $status >= 200 && $status < 300,
         'status_code' => $status,
         'headers'     => $resp->getHeaders(),
         'body'        => (json_last_error() === JSON_ERROR_NONE) ? $decoded : $raw,
         'raw'         => $raw,
         'rate_limit'  => $this->extractRateLimit($resp),
         'retries'     => 0,
      ];

      if ($this->debug) {
         error_log('[Curl OK] ' . $this->method . ' ' . $this->endpoint . ' ' . $status);
      }

      return $out;
   }

   /** Build appropriate headers based on auth+content options */
   private function buildHeaders(): array {
      $headers = array_merge(['Accept' => 'application/json'], $this->extra_headers);

      if ($this->auth === 'bearer') {
         if (!$this->access_token) throw new \InvalidArgumentException('Bearer mode requires $access_token.');
         $headers['Authorization'] = 'Bearer ' . $this->access_token;
         if ($this->content === 'json') $headers['Content-Type'] = 'application/json';
      } elseif ($this->auth === 'basic') {
         if (!$this->client_id || !$this->client_secret) {
            throw new \InvalidArgumentException('Basic mode requires $client_id and $client_secret.');
         }
         $headers['Authorization'] = 'Basic ' . base64_encode($this->client_id . ':' . $this->client_secret);
         $headers['Content-Type']  = ($this->content === 'form') ? 'application/x-www-form-urlencoded' : 'application/json';
      }

      if ($this->idempotency_key) {
         $headers['Idempotency-Key'] = $this->idempotency_key;
      }

      return $headers;
   }

   /** Normalize exception and include rate-limit headers if present */
   private function normalizeException($e, ?ResponseInterface $resp, int $retries): array {
      $reqStr  = method_exists($e, 'getRequest')  && $e->getRequest()  ? Message::toString($e->getRequest())  : null;
      $respStr = $resp ? Message::toString($resp) : null;

      if ($this->debug) {
         error_log('[Curl ERROR] ' . $e->getMessage());
      }

      return [
         'ok'          => false,
         'exception'   => (new \ReflectionClass($e))->getShortName(),
         'status_code' => $resp ? $resp->getStatusCode() : $e->getCode(),
         'message'     => $e->getMessage(),
         'request'     => $reqStr,
         'response'    => $respStr,
         'rate_limit'  => $resp ? $this->extractRateLimit($resp) : null,
         'retries'     => $retries,
      ];
   }

   /** Parse common rate-limit headers */
   private function extractRateLimit(ResponseInterface $resp): array {
      // Header names vary across APIs; we try common patterns.
      $limit     = $this->firstHeader($resp, ['X-RateLimit-Limit', 'RateLimit-Limit']);
      $remaining = $this->firstHeader($resp, ['X-RateLimit-Remaining', 'RateLimit-Remaining']);
      $reset     = $this->firstHeader($resp, ['X-RateLimit-Reset', 'RateLimit-Reset']);
      $retry     = $resp->getHeaderLine('Retry-After');

      // Normalize types when possible
      $limit     = is_numeric($limit)     ? (int)$limit     : ($limit ?: null);
      $remaining = is_numeric($remaining) ? (int)$remaining : ($remaining ?: null);
      $reset_at  = null;
      if ($reset !== '') {
         // could be epoch seconds or HTTP date
         if (ctype_digit($reset)) {
            $reset_at = (int)$reset; // epoch seconds
         } else {
            $t = strtotime($reset);
            if ($t !== false) $reset_at = $t;
         }
      }

      $retry_after = $this->parseRetryAfter($retry);

      return [
         'limit'       => $limit,
         'remaining'   => $remaining,
         'reset_at'    => $reset_at,      // unix timestamp (if available)
         'retry_after' => $retry_after,   // seconds (if available)
      ];
   }

   /** Choose first header value among candidates */
   private function firstHeader(ResponseInterface $resp, array $names): string {
      foreach ($names as $n) {
         $line = $resp->getHeaderLine($n);
         if ($line !== '') return $line;
      }
      return '';
   }

   /** Parse Retry-After header (seconds or HTTP date) → seconds|null */
   private function parseRetryAfter(?string $val): ?int {
      if (!$val || $val === '') return null;
      $val = trim($val);
      if (ctype_digit($val)) return (int)$val;

      $t = strtotime($val);
      if ($t === false) return null;

      $diff = $t - time();
      return $diff > 0 ? $diff : 0;
   }

   /** Compute backoff (ms). Prefer Retry-After; else exponential + jitter */
   private function computeBackoffMs(int $attempt, ?ResponseInterface $resp): int {
      $retryAfter = null;
      if ($this->respect_retry_after && $resp) {
         $retryAfter = $this->parseRetryAfter($resp->getHeaderLine('Retry-After'));
      }
      if ($retryAfter !== null) {
         return max(0, (int) round($retryAfter * 1000));
      }

      // Exponential backoff with jitter (base 500ms): min(8s, base*2^(attempt-1)) + rand(0..250ms)
      $base = 500; // ms
      $cap  = 8000; // ms
      $exp  = min($cap, $base * (2 ** max(0, $attempt - 1)));
      $jitter = random_int(0, 250);
      return (int)($exp + $jitter);
   }
}
