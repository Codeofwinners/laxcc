# Specials installation

1. In WPCode, add a **JavaScript Snippet** named **LAXCC Specials Page**. Paste the complete contents of `specials-wpcode.js`, without script tags. Choose **Auto Insert → Site Wide Footer**, then activate it. Use only one copy of this snippet.
2. Replace the existing Specials Elementor HTML widget contents with `specials.html`. This file now contains markup and CSS only. Save the draft and test the preview first.
3. The card CSS is already included in `specials.html`; the separate `specials-card-styles.css` is optional and is not required for this installation. Do not install the older `specials-wpcode-cart.js` alongside this snippet.

The JavaScript waits for the DOM and exits before network calls, storage access, timers, or cart handlers unless `#laxcc-specials-root` exists. A per-widget initialization guard prevents duplicate initialization. Other pages without that widget do not run this code.

On the Specials page, the existing integration still writes shared LeafBridge cart storage after adding items and updates native cart UI. Separation does not verify or repair that integration. Verify adding a product, opening the native cart without reloading, removing a product, and proceeding to checkout before publishing. Do not place a test order.

The repeated five-second checkout API timer has been removed. The existing local-storage change watcher remains.

This separation removes the JavaScript payload from Elementor saves. The cause of the reported save-time HTTP 403 has not been confirmed, and WPCode saves can also be blocked by server security. If a save still fails, inspect that failed save response and server security logs; do not disable the firewall wholesale.
