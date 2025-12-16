const chokidar = require("chokidar");
const { spawn } = require("child_process");
const treeKill = require("tree-kill");

const glob = require("glob");
console.log(glob.sync("src/assets/**/*"));

// Initialize chokidar to watch for add/remove events only
const watcher = chokidar.watch("src/assets", {
   persistent: true,
   usePolling: true,
   interval: 100,
});

let currentProcess = null; // To track the spawned process
let debounceTimeout = null;

function runStartCommand() {
   if (currentProcess) {
      console.log("Terminating existing npm process...");
      terminateProcessTree(currentProcess.pid);
   } else {
      startProcess();
   }
}

function startProcess() {
   console.log("Starting npm process...");
   currentProcess = spawn("npm", ["run", "start"], { stdio: "inherit", shell: true });

   currentProcess.on("close", (code) => {
      console.log(`npm run start exited with code ${code}`);
      currentProcess = null;
   });

   currentProcess.on("error", (err) => {
      console.error(`Failed to start npm: ${err.message}`);
      currentProcess = null;
   });
}

// Function to terminate the process tree (parent and children)
function terminateProcessTree(pid) {
   if (process.platform === "darwin") {
      exec(
         `ps -o pid --ppid ${pid} --noheaders | xargs kill -9 && kill -9 ${pid}`,
         (err, stdout, stderr) => {
            if (err) {
               console.error(`Error killing process tree: ${stderr}`);
            } else {
               console.log(`Terminated process tree for PID: ${pid}`);
               startProcess();
            }
         }
      );
   } else {
      treeKill(pid, "SIGTERM", (err) => {
         if (err) {
            console.error(`Error terminating process tree: ${err.message}`);
         }
         console.log("Existing process terminated. Starting a new one...");
         startProcess();
      });
   }
}

function debounceRestart() {
   // Clear the previous timeout to debounce events
   clearTimeout(debounceTimeout);

   // Set a timeout to delay the restart process
   debounceTimeout = setTimeout(() => {
      console.log("Debounced: Restarting npm process...");
      runStartCommand();
   }, 500); // Adjust delay as needed (500ms works well for most cases)
}

watcher.on("add", (path) => {
   console.log(`File added: ${path}`);
   debounceRestart();
});

watcher.on("unlink", (path) => {
   console.log(`File removed: ${path}`);
   debounceRestart();
});

console.log("Watching for file additions or removals...");
