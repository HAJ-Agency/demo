/* global dataLayer */
(function () {
   // Helpers --------------------------------------------------------
   function toNumber(v) {
     var n = Number(v); return isFinite(n) ? n : 0;
   }
   function attrVariant(attrs) {
     // attrs: { attribute_pa_size: 'm', attribute_color: 'blue' }
     if (!attrs) return '';
     var vals = [];
     for (var k in attrs) { if (!attrs[k]) continue; vals.push(String(attrs[k])); }
     return vals.join(' / ');
   }
   function firstOr(u, arr) { return Array.isArray(arr) && arr.length ? arr[0] : u; }
 
   // Main listener --------------------------------------------------
   document.addEventListener('hapi:add_to_cart', function (e) {
     if (!e || !e.detail || !e.detail.payload) return;
     var p = e.detail.payload;
 
     // Common fields
     var currency = p.currency || 'SEK';
     var qty      = toNumber(p.quantity || 1);
     var brand    = p.brand || undefined;
     var cats     = Array.isArray(p.categories) ? p.categories : [];
     var cat1 = cats[0], cat2 = cats[1], cat3 = cats[2], cat4 = cats[3], cat5 = cats[4];
 
     // Resolve id/name/variant/price
     var isVar = !!p.variation;
     var varId = isVar ? String(p.variation.id) : null;
     var varSku= isVar ? (p.variation.sku || null) : null;
     var price = isVar ? toNumber(p.variation.price) : toNumber(p.parentPrice);
 
     // Prefer SKU as item_id when available; fallback to (variation_)ID
     var itemId = (varSku || p.productSku || (isVar ? varId : String(p.productId)));
 
     // For name, use parent product name (or your own naming convention)
     var itemName = p.productName || ('Product ' + p.productId);
 
     var variantLabel = isVar ? attrVariant(p.variation.attributes) : undefined;
 
     // GA4 payload ---------------------------------------------------
     var ga4Item = {
       item_id: itemId,
       item_name: itemName,
       item_brand: brand,
       item_category: cat1,
       item_category2: cat2,
       item_category3: cat3,
       item_category4: cat4,
       item_category5: cat5,
       item_variant: variantLabel,
       price: price,
       quantity: qty
     };
 
     var ga4Event = {
       event: 'add_to_cart',
       ecommerce: {
         currency: currency,
         value: +(price * qty).toFixed(2),
         items: [ga4Item]
       }
     };
 
    /* UA Enhanced Ecommerce (optional; remove if you don't use UA) ----------- */
    //  var uaProduct = {
    //    id: itemId,
    //    name: itemName,
    //    brand: brand,
    //    category: [cat1,cat2,cat3,cat4,cat5].filter(Boolean).join('/'),
    //    variant: variantLabel,
    //    price: String(price),
    //    quantity: qty
    //  };
    //  var uaEvent = {
    //    event: 'eec.addToCart',
    //    ecommerce: {
    //      currencyCode: currency,
    //      add: { products: [uaProduct] }
    //    }
    //  };
 
     /* Push to dataLayer (create if absent) */
     window.dataLayer = window.dataLayer || [];
     window.dataLayer.push(ga4Event);
    //  window.dataLayer.push(uaEvent);
   });
 })();
 