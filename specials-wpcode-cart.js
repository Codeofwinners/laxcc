/*
 * LAXCC Specials cart bridge — add this file as a WPCode JavaScript Snippet.
 * Do not paste this into the Elementor HTML widget.
 * WPCode placement: Auto Insert -> Site Wide Footer.
 */
(function () {
    'use strict';

    var root = document.getElementById('laxcc-specials-root');
    if (!root || window.LAXCC_SPECIALS_CART_READY) return;
    window.LAXCC_SPECIALS_CART_READY = true;

    var endpoint = 'https://plus.dutchie.com/plus/2021-07/graphql';
    var token = 'public-eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJBUEktQ0xJRU5UIiwiZXhwIjozMzI0MTMzMzI5OCwiaWF0IjoxNjg0NDI0NDk4LCJpc3MiOiJodHRwczovL2R1dGNoaWUuY29tIiwianRpIjoiNjI0ZjYzMjItZDE3Mi00OTIwLTkzZmItNTA1ZGEzMmY2ZWU2IiwiZW50ZXJwcmlzZV9pZCI6ImI0NTg3NDIzLTZkYjYtNDgxMi1iMDlkLWVlYjM4M2Y0YmYzOCIsInV1aWQiOiJhMjhkMzZiNy00ZDJmLTQ4YTUtODhhNC1kZWI0NWUxMzYxOWUifQ.8jKBV95mBDuXJzxZeUajF_5Mv17xt3B9Iwc-w-zVWdo';
    var retailer = '6ed054f8-19a5-4991-b562-c4df2a287ae3';
    var checkoutSlug = '6464ddf45fcec7005c194bee';
    var returnUrl = 'https://laxcc.com/thank-you-2/';

    function request(query, variables) {
        return fetch(endpoint, {method: 'POST', cache: 'no-store', headers: {'Content-Type': 'application/json', 'Authorization': 'Bearer ' + token}, body: JSON.stringify({query: query, variables: variables})})
            .then(function (response) { if (!response.ok) throw new Error('Dutchie HTTP ' + response.status); return response.json(); })
            .then(function (payload) { if (payload.errors && payload.errors.length) throw new Error(payload.errors[0].message); return payload; });
    }

    function checkoutId() {
        try { var data = JSON.parse(localStorage.getItem('leafbridge_cart_data') || '{}'); if (data.new_checkout_id) return String(data.new_checkout_id); } catch (e) {}
        return localStorage.getItem('laxcc_dutchie_checkout_id') || '';
    }

    function checkoutUrl(id) { return 'https://checkout-lax.laxcc.com/checkouts/' + checkoutSlug + '/' + id + '?r=' + encodeURIComponent(returnUrl); }

    function save(checkout) {
        var items = Array.isArray(checkout.items) ? checkout.items : [];
        var qty = items.reduce(function (sum, item) { return sum + (Number(item.quantity) || 1); }, 0);
        var total = checkout.priceSummary && checkout.priceSummary.total != null ? checkout.priceSummary.total : null;
        var url = checkoutUrl(checkout.id);
        var data;
        try { data = JSON.parse(localStorage.getItem('leafbridge_cart_data') || '{}'); } catch (e) { data = {}; }
        data.retailer_id = retailer;
        data.new_checkout_id = checkout.id;
        data.menutype = data.menutype || 'RECREATIONAL';
        data.collection_type = data.collection_type || 'PICKUP';
        data.cart_items_count = items;
        data.products = items.map(function (item) { var product = item.product || {}; return {product_id: product.id || item.productId, variation: item.option || 'N/A', prod_count: item.quantity || 1, prod_name: product.name || ''}; });
        localStorage.setItem('leafbridge_cart_data', JSON.stringify(data));
        localStorage.setItem('laxcc_dutchie_checkout_id', String(checkout.id));
        localStorage.setItem('laxcc_dutchie_checkout_url', url);
        localStorage.setItem('laxcc_dutchie_checkout_count', String(qty));
        if (total != null) localStorage.setItem('laxcc_dutchie_checkout_total', String(total));
        root.querySelectorAll('.cart_count').forEach(function (el) { el.textContent = qty; });
        var bar = root.querySelector('#laxcc-floating-cart');
        if (bar) { bar.classList.add('is-visible'); bar.querySelector('#laxcc-cart-item-count').textContent = qty + ' item' + (qty === 1 ? '' : 's') + ' in cart'; bar.querySelector('#laxcc-cart-total-price').textContent = total == null ? '' : 'Subtotal: $' + (Number(total) / 100).toFixed(2); bar.querySelector('#laxcc-checkout-btn').href = url; }
        var nav = root.querySelector('#laxcc-cart-nav-btn');
        if (nav) { nav.style.display = 'inline-flex'; nav.href = url; nav.querySelector('#laxcc-cart-nav-badge').textContent = qty; }
        var nativeOpen = document.getElementById('open_the_cart');
        if (nativeOpen) nativeOpen.click();
        return checkout;
    }

    function createCheckout() {
        var id = checkoutId();
        if (id) return Promise.resolve({id: id});
        return request('mutation CreateCheckout($retailerId: ID!, $orderType: OrderType!, $pricingType: PricingType!) { createCheckout(retailerId: $retailerId, orderType: $orderType, pricingType: $pricingType) { id items { id productId quantity option product { id name } } priceSummary { total } } }', {retailerId: retailer, orderType: 'PICKUP', pricingType: 'RECREATIONAL'}).then(function (payload) { return payload.data.createCheckout; });
    }

    function add(button) {
        var productId = button.getAttribute('data-product-id');
        var option = button.getAttribute('data-option') || 'N/A';
        var original = button.textContent;
        button.disabled = true;
        button.textContent = 'Adding...';
        return createCheckout().then(function (checkout) {
            return request('mutation AddItem($retailerId: ID!, $checkoutId: ID!, $productId: ID!, $option: String!, $quantity: Int!) { addItem(retailerId: $retailerId, checkoutId: $checkoutId, productId: $productId, option: $option, quantity: $quantity) { id items { id productId quantity option product { id name } } priceSummary { total } } }', {retailerId: retailer, checkoutId: checkout.id, productId: productId, option: option, quantity: 1});
        }).then(function (payload) { var updated = payload.data.addItem; save(updated); button.textContent = 'Added!'; setTimeout(function () { button.disabled = false; button.textContent = original; }, 1800); }).catch(function (error) { button.disabled = false; button.textContent = original; if (window.console) console.warn('[LAXCC] Add to cart failed.', error); alert('Could not add this item to the cart. Please try again.'); });
    }

    root.addEventListener('click', function (event) {
        var button = event.target.closest('.laxcc-prod-order-btn');
        if (!button) return;
        event.preventDefault();
        event.stopPropagation();
        add(button);
    });
}());
