(function bootSpecials() {
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", bootSpecials, { once: true });
        return;
    }
    var specialsRoot = document.getElementById("laxcc-specials-root");
    if (!specialsRoot || specialsRoot.dataset.laxccSpecialsInitialized === "true") return;
    specialsRoot.dataset.laxccSpecialsInitialized = "true";
    'use strict';

    var DUTCHIE_ENDPOINT = 'https://plus.dutchie.com/plus/2021-07/graphql';
    var DUTCHIE_PUBLIC_TOKEN = 'public-eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOiJBUEktQ0xJRU5UIiwiZXhwIjozMzI0MTMzMzI5OCwiaWF0IjoxNjg0NDI0NDk4LCJpc3MiOiJodHRwczovL2R1dGNoaWUuY29tIiwianRpIjoiNjI0ZjYzMjItZDE3Mi00OTIwLTkzZmItNTA1ZGEzMmY2ZWU2IiwiZW50ZXJwcmlzZV9pZCI6ImI0NTg3NDIzLTZkYjYtNDgxMi1iMDlkLWVlYjM4M2Y0YmYzOCIsInV1aWQiOiJhMjhkMzZiNy00ZDJmLTQ4YTUtODhhNC1kZWI0NWUxMzYxOWUifQ.8jKBV95mBDuXJzxZeUajF_5Mv17xt3B9Iwc-w-zVWdo';
    var RETAILER_ID = '6ed054f8-19a5-4991-b562-c4df2a287ae3';
    var REFRESH_INTERVAL_MS = 5 * 60 * 1000;

    // Special metadata definitions
    var SPECIALS_INFO = {
        '6aa98409cf525b0007f59833': {
            name: 'Manzanita Natural BOGO Discount',
            tag: 'Manzanita BOGO',
            desc: 'Buy one craft THC beverage, get one promo discount. In stock today.',
            badgeText: 'BOGO Deal',
            badgeBg: '#7ECBB3',
            badgeColor: '#ffffff'
        },
        '6a4ef24300a4a00007ff6f69': {
            name: 'BOGO Koa Mega Gummies',
            tag: 'Koa Mega Gummies BOGO',
            desc: 'Buy one 100mg multi-flavor gummy pack, get one promo discount.',
            badgeText: 'BOGO Gummy',
            badgeBg: '#7ECBB3',
            badgeColor: '#ffffff'
        },
        '6a334fe14d0ccd00079451e0': {
            name: 'Kyle\'s Premium Flower BOGO',
            tag: 'Kyle\'s Flower BOGO',
            desc: 'Buy one 3.5g craft indoor flower jar, get one promo discount drop.',
            badgeText: 'Flower BOGO',
            badgeBg: '#7ECBB3',
            badgeColor: '#ffffff'
        },
        '6a90fff6610fcc00075f5afb': {
            name: 'Lusty 7g Meta O.G BOGO',
            tag: 'Lusty Meta OG BOGO',
            desc: 'Special BOGO savings on Lusty Meta OG exotic indoor flower.',
            badgeText: 'Lusty BOGO',
            badgeBg: '#7ECBB3',
            badgeColor: '#ffffff'
        }
    };

    // State
    var allSpecialProducts = [];
    var currentActiveSpecial = 'all';
    var currentSort = 'featured';

    // Elements
    var gridEl = document.getElementById('laxcc-sp-grid');
    var emptyEl = document.getElementById('laxcc-sp-empty');
    var activeTitleEl = document.getElementById('laxcc-active-deal-title');
    var activeSubEl = document.getElementById('laxcc-active-deal-sub');
    var activeCountEl = document.getElementById('laxcc-active-count');
    var countAllEl = document.getElementById('laxcc-count-all');
    var spotlightNameEl = document.getElementById('laxcc-spotlight-name');
    var spotlightDescEl = document.getElementById('laxcc-spotlight-desc');
    var sortSelectEl = document.getElementById('laxcc-sp-sort');
    var tabsContainer = document.getElementById('laxcc-specials-tabs');
    var resetBtn = document.getElementById('laxcc-reset-filters');
    var cartNavBtn = document.getElementById('laxcc-cart-nav-btn');
    var cartNavBadge = document.getElementById('laxcc-cart-nav-badge');
    var floatingCart = document.getElementById('laxcc-floating-cart');
    var cartItemCountEl = document.getElementById('laxcc-cart-item-count');
    var cartTotalPriceEl = document.getElementById('laxcc-cart-total-price');
    var checkoutBtn = document.getElementById('laxcc-checkout-btn');

    // Dutchie Cart / Site-Wide Leafbridge Checkout State
    var RETAILER_CHECKOUT_SLUG = '6464ddf45fcec7005c194bee';
    var ORDER_RETURN_URL = 'https://laxcc.com/thank-you-2/';

    // Helper: format canonical Dutchie checkout URL with thank-you return & analytics
    function formatCheckoutUrl(redirectUrl) {
        if (!redirectUrl) return '';
        var url = redirectUrl;
        if (url.indexOf('r=') === -1) {
            var sep = url.indexOf('?') !== -1 ? '&' : '?';
            url += sep + 'r=' + encodeURIComponent(ORDER_RETURN_URL);
        }
        try {
            var params = new URLSearchParams(window.location.search);
            var gl = params.get('_gl');
            if (gl && url.indexOf('_gl=') === -1) {
                url += '&_gl=' + encodeURIComponent(gl);
            }
        } catch(e) {}
        return url;
    }

    function getCheckoutUrl(chkId) {
        if (!chkId) return '';
        return formatCheckoutUrl('https://checkout-lax.laxcc.com/checkouts/' + RETAILER_CHECKOUT_SLUG + '/' + chkId);
    }

    function getStoredCheckoutId() {
        try {
            var lb = JSON.parse(localStorage.getItem('leafbridge_cart_data'));
            if (lb && lb.new_checkout_id && lb.new_checkout_id !== 0 && lb.new_checkout_id !== '0') {
                return String(lb.new_checkout_id);
            }
        } catch(e) {}
        return localStorage.getItem('laxcc_dutchie_checkout_id') || null;
    }

    function getInitialCartSession() {
        var chkId = getStoredCheckoutId();
        var count = 0;
        var total = null;

        try {
            var lb = JSON.parse(localStorage.getItem('leafbridge_cart_data'));
            if (lb && typeof lb === 'object') {
                if (Array.isArray(lb.cart_items_count) && lb.cart_items_count.length > 0) {
                    count = lb.cart_items_count.reduce(function(acc, it) { return acc + (parseInt(it.quantity, 10) || 1); }, 0);
                } else if (Array.isArray(lb.products) && lb.products.length > 0) {
                    count = lb.products.reduce(function(acc, it) { return acc + (parseInt(it.prod_count, 10) || 1); }, 0);
                } else if (lb.new_checkout_id && Array.isArray(lb.products) && lb.products.length === 0) {
                    count = 0;
                }
            }
        } catch(e) {}

        if (!count && !localStorage.getItem('leafbridge_cart_data')) {
            count = parseInt(localStorage.getItem('laxcc_dutchie_checkout_count') || '0', 10);
        }

        total = localStorage.getItem('laxcc_dutchie_checkout_total') || null;

        return {
            id: chkId,
            count: count,
            total: total
        };
    }

    var initialSession = getInitialCartSession();
    var activeCheckoutId = initialSession.id;
    var activeCheckoutTotalCount = initialSession.count;
    var activeCheckoutTotalPrice = initialSession.total;
    var activeCheckoutUrl = activeCheckoutId ? getCheckoutUrl(activeCheckoutId) : (localStorage.getItem('laxcc_dutchie_checkout_url') || null);

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    // Flame SVG snippet
    var flameSvgHtml = '<span class="laxcc-flame-icon mini" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M12 23c-4.97 0-9-3.92-9-8.75 0-3.5 2.12-6.52 4.19-8.76a.75.75 0 0 1 1.25.56c-.05 1.5.3 3.03 1.06 4.2.3-.43.64-.84 1-1.23 2.18-2.36 3.5-5.32 3.5-8.52a.75.75 0 0 1 1.34-.47c2.93 3.63 5.66 8.35 5.66 14.22C21 19.08 16.97 23 12 23z" fill="#ff7700"/><path d="M12 22c-3.1 0-5.6-2.5-5.6-5.6 0-2.2 1.3-4.2 2.6-5.6.45-.48 1.25-.16 1.25.5v1.2c0 .41.34.75.75.75s.75-.34.75-.75c0-1.2.6-2.2 1.3-3 .42-.48 1.22-.16 1.22.5 0 3.2 2.1 4.5 2.1 6.8 0 3.1-1.97 5.2-4.27 5.2z" fill="#ffdd00"/></svg></span>';

    // Parse URL params on load
    function getUrlSpecialId() {
        var params = new URLSearchParams(window.location.search);
        return params.get('specials_id') || params.get('special_id') || 'all';
    }

    // Fetch all products with pagination recursively
    function fetchAllProducts(offset, accumulated) {
        offset = offset || 0;
        accumulated = accumulated || [];

        var query = [
            'query GetSpecialsInventory($retailerId: ID!, $offset: Int!) {',
            '  menu(retailerId: $retailerId, pagination: { limit: 100, offset: $offset }) {',
            '    productsCount',
            '    products {',
            '      id name slug category strainType',
            '      images { url }',
            '      brand { id name }',
            '      potencyThc { formatted }',
            '      variants {',
            '        id option priceRec specialPriceRec quantity',
            '        specialRewardIds specialConditionIds',
            '      }',
            '    }',
            '  }',
            '}'
        ].join(' ');

        return fetch(DUTCHIE_ENDPOINT, {
            method: 'POST',
            cache: 'no-store',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + DUTCHIE_PUBLIC_TOKEN
            },
            body: JSON.stringify({
                query: query,
                variables: {
                    retailerId: RETAILER_ID,
                    offset: offset
                }
            })
        })
        .then(function(res) {
            if (!res.ok) throw new Error('Dutchie HTTP error ' + res.status);
            return res.json();
        })
        .then(function(json) {
            if (json.errors && json.errors.length) throw new Error(json.errors[0].message);
            var menu = json.data && json.data.menu;
            if (!menu || !Array.isArray(menu.products)) throw new Error('Invalid inventory payload');

            var combined = accumulated.concat(menu.products);
            var total = menu.productsCount;

            if (combined.length >= total || menu.products.length === 0) {
                return combined;
            }
            return fetchAllProducts(offset + menu.products.length, combined);
        });
    }

    function processSpecialProducts(rawProducts) {
        var results = [];

        rawProducts.forEach(function(product) {
            if (!product || !product.variants) return;

            // Check if any variant has a special promotion reward or condition ID
            var matchingVariants = product.variants.filter(function(v) {
                var rewards = v.specialRewardIds || [];
                var conds = v.specialConditionIds || [];
                return rewards.length > 0 || conds.length > 0;
            });

            if (!matchingVariants.length) return;

            // Collect all special IDs
            var specialIds = [];
            matchingVariants.forEach(function(v) {
                (v.specialRewardIds || []).forEach(function(id) {
                    if (specialIds.indexOf(id) === -1) specialIds.push(id);
                });
                (v.specialConditionIds || []).forEach(function(id) {
                    if (specialIds.indexOf(id) === -1) specialIds.push(id);
                });
            });

            var primaryVariant = matchingVariants[0];
            var price = primaryVariant.priceRec || 0;
            var specialPrice = primaryVariant.specialPriceRec;
            var option = primaryVariant.option || '';
            var image = product.images && product.images[0] && product.images[0].url ? product.images[0].url : '';
            var brandName = product.brand && product.brand.name ? product.brand.name : '';
            var categoryName = product.category ? product.category.replace(/_/g, ' ') : '';
            var strain = product.strainType && product.strainType !== 'NOT_APPLICABLE' ? product.strainType.replace(/_/g, ' ') : '';
            var thc = product.potencyThc && product.potencyThc.formatted ? product.potencyThc.formatted : '';

            // Check special info for badge
            var primarySpecialId = specialIds[0];
            var specialConfig = SPECIALS_INFO[primarySpecialId] || {
                badgeText: 'BOGO Special',
                badgeBg: '#7ECBB3',
                badgeColor: '#ffffff'
            };

            results.push({
                id: product.id,
                name: product.name,
                slug: product.slug,
                brand: brandName,
                category: categoryName,
                strain: strain,
                thc: thc,
                image: image,
                price: price,
                specialPrice: specialPrice,
                option: option,
                specialIds: specialIds,
                specialConfig: specialConfig
            });
        });

        return results;
    }

    // Build the filter tabs from the promotions returned by the live menu.
    // The IDs change when LeafBridge/Dutchie rotates a promotion, so the
    // static Elementor buttons cannot be the source of truth.
    function rebuildSpecialTabs(products) {
        if (!tabsContainer) return;

        var allButton = tabsContainer.querySelector('[data-special-id="all"]');
        var specialIds = [];
        var specialProducts = {};

        products.forEach(function(product) {
            (product.specialIds || []).forEach(function(id) {
                if (specialIds.indexOf(id) === -1) specialIds.push(id);
                if (!specialProducts[id]) specialProducts[id] = product;
            });
        });

        tabsContainer.innerHTML = '';
        if (allButton) {
            allButton.setAttribute('aria-selected', 'true');
            tabsContainer.appendChild(allButton);
        }

        specialIds.forEach(function(id) {
            var info = SPECIALS_INFO[id] || {};
            var sample = specialProducts[id] || {};
            var label = info.tag || (sample.brand ? sample.brand + ' BOGO' : 'Live Special');
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'laxcc-filter-pill';
            button.dataset.specialId = id;
            button.setAttribute('role', 'tab');
            button.setAttribute('aria-selected', 'false');
            button.textContent = label;
            tabsContainer.appendChild(button);

            if (!SPECIALS_INFO[id]) {
                SPECIALS_INFO[id] = {
                    name: label,
                    tag: label,
                    desc: 'Live promotion currently available at LAX Cannabis Club.',
                    badgeText: 'BOGO Special',
                    badgeBg: '#7ECBB3',
                    badgeColor: '#ffffff'
                };
            }
        });
    }

    // Helper: format canonical Dutchie checkout URL with thank-you return & analytics
    function formatCheckoutUrl(redirectUrl) {
        if (!redirectUrl) return '';
        var url = redirectUrl;
        if (url.indexOf('r=') === -1) {
            var sep = url.indexOf('?') !== -1 ? '&' : '?';
            url += sep + 'r=' + encodeURIComponent(ORDER_RETURN_URL);
        }
        try {
            var params = new URLSearchParams(window.location.search);
            var gl = params.get('_gl');
            if (gl && url.indexOf('_gl=') === -1) {
                url += '&_gl=' + encodeURIComponent(gl);
            }
        } catch(e) {}
        return url;
    }

    // Save cart state to both local fallbacks
    function saveCheckoutState(chkId, chkUrl, count, total) {
        activeCheckoutId = chkId;
        activeCheckoutUrl = chkUrl;
        activeCheckoutTotalCount = count;
        activeCheckoutTotalPrice = total;
        if (chkId) {
            localStorage.setItem('laxcc_dutchie_checkout_id', String(chkId));
        } else {
            localStorage.removeItem('laxcc_dutchie_checkout_id');
        }
        if (chkUrl) {
            localStorage.setItem('laxcc_dutchie_checkout_url', String(chkUrl));
        } else {
            localStorage.removeItem('laxcc_dutchie_checkout_url');
        }
        localStorage.setItem('laxcc_dutchie_checkout_count', String(count || 0));
        if (total != null) {
            localStorage.setItem('laxcc_dutchie_checkout_total', String(total));
        } else {
            localStorage.removeItem('laxcc_dutchie_checkout_total');
        }
    }

    function clearCheckoutSession() {
        activeCheckoutId = null;
        activeCheckoutUrl = null;
        activeCheckoutTotalCount = 0;
        activeCheckoutTotalPrice = null;
        localStorage.removeItem('laxcc_dutchie_checkout_id');
        localStorage.removeItem('laxcc_dutchie_checkout_url');
        localStorage.removeItem('laxcc_dutchie_checkout_count');
        localStorage.removeItem('laxcc_dutchie_checkout_total');

        try {
            var lb = JSON.parse(localStorage.getItem('leafbridge_cart_data'));
            if (lb) {
                delete lb.new_checkout_id;
                lb.products = [];
                lb.cart_items_count = [];
                localStorage.setItem('leafbridge_cart_data', JSON.stringify(lb));
            }
        } catch(e) {}
    }

    // Persist to site-wide Leafbridge localStorage schema so ALL pages on laxcc.com share the same cart
    function persistToLeafbridge(checkout, newProduct) {
        try {
            var lbData = null;
            try {
                lbData = JSON.parse(localStorage.getItem('leafbridge_cart_data'));
            } catch(e) { lbData = null; }

            if (!lbData || typeof lbData !== 'object') {
                lbData = {
                    retailer_id: RETAILER_ID,
                    menutype: 'RECREATIONAL',
                    collection_type: 'PICKUP',
                    products: [],
                    cart_creation_utc: new Date().toISOString()
                };
            }

            lbData.retailer_id = RETAILER_ID;
            lbData.menutype = lbData.menutype || 'RECREATIONAL';
            lbData.collection_type = lbData.collection_type || 'PICKUP';
            lbData.new_checkout_id = checkout.id;
            lbData.cart_items_count = checkout.items || [];

            if (newProduct) {
                if (!Array.isArray(lbData.products)) lbData.products = [];
                var existingIndex = -1;
                for (var i = 0; i < lbData.products.length; i++) {
                    if (lbData.products[i].product_id === newProduct.id && lbData.products[i].variation === newProduct.option) {
                        existingIndex = i;
                        break;
                    }
                }
                if (existingIndex !== -1) {
                    lbData.products[existingIndex].prod_count = (parseInt(lbData.products[existingIndex].prod_count, 10) || 1) + 1;
                } else {
                    lbData.products.push({
                        product_id: newProduct.id,
                        variation: newProduct.option || 'N/A',
                        prod_count: 1,
                        prod_name: newProduct.name || ''
                    });
                }
            }

            localStorage.setItem('leafbridge_cart_data', JSON.stringify(lbData));

            if (!localStorage.getItem('leafbridge_wizard_data')) {
                localStorage.setItem('leafbridge_wizard_data', JSON.stringify({
                    age_confirm: true,
                    collection_method: 'PICKUP',
                    created_time: new Date(),
                    default_set: true,
                    event: 'EMPTY CART',
                    menu_type: 'RECREATIONAL',
                    retailer_id: RETAILER_ID
                }));
            }
        } catch(err) {
            console.warn('[LAXCC] Leafbridge sync warning:', err);
        }
    }

    // Harmonize with site-wide Leafbridge elements (header counter, floating cart drawer)
    function harmonizeWithSiteWideElements() {
        var totalQty = activeCheckoutTotalCount;
        // Update all Leafbridge .cart_count badges across the site
        document.querySelectorAll('.cart_count').forEach(function(el) {
            el.textContent = totalQty > 0 ? String(totalQty) : '';
        });

        // Update native Leafbridge floating drawer if present
        var siteFloatingCart = document.getElementById('floating_cart');
        if (siteFloatingCart) {
            var footerLink = siteFloatingCart.querySelector('.floating_cart_footer a');
            if (footerLink && activeCheckoutUrl) {
                footerLink.setAttribute('href', activeCheckoutUrl);
            }
            var cartTotalSpan = siteFloatingCart.querySelector('.floating_cart_footer a span.cart_total');
            if (cartTotalSpan && activeCheckoutTotalPrice != null) {
                cartTotalSpan.textContent = ' - $' + (Number(activeCheckoutTotalPrice) / 100).toFixed(2);
            }
            var footerWrap = siteFloatingCart.querySelector('.floating_cart_footer_wrapper');
            if (footerWrap) {
                footerWrap.style.display = totalQty > 0 ? 'block' : 'none';
            }
        }
    }

    // Render cart UI state
    function updateCartUI() {
        if (activeCheckoutTotalCount > 0 && activeCheckoutUrl) {
            if (cartNavBtn) {
                cartNavBtn.style.display = 'inline-flex';
                cartNavBtn.href = activeCheckoutUrl;
            }
            if (cartNavBadge) {
                cartNavBadge.textContent = activeCheckoutTotalCount;
            }
            if (floatingCart) {
                floatingCart.classList.add('is-visible');
            }
            if (cartItemCountEl) {
                cartItemCountEl.textContent = activeCheckoutTotalCount + ' item' + (activeCheckoutTotalCount === 1 ? '' : 's') + ' in cart';
            }
            if (cartTotalPriceEl && activeCheckoutTotalPrice) {
                cartTotalPriceEl.textContent = 'Subtotal: $' + (Number(activeCheckoutTotalPrice) / 100).toFixed(2);
            }
            if (checkoutBtn) {
                checkoutBtn.href = activeCheckoutUrl;
            }
        } else {
            if (cartNavBtn) cartNavBtn.style.display = 'none';
            if (floatingCart) floatingCart.classList.remove('is-visible');
        }

        harmonizeWithSiteWideElements();
    }

    // Sync site-wide cart on page load from Dutchie GraphQL using existing Leafbridge/site checkoutId
    function syncSiteWideCart() {
        var chkId = getStoredCheckoutId();
        if (!chkId) {
            updateCartUI();
            return Promise.resolve(false);
        }

        var getCheckoutQuery = [
            'query GetCheckoutSession($retailerId: ID!, $checkoutId: ID!) {',
            '  checkout(retailerId: $retailerId, id: $checkoutId) {',
            '    id',
            '    items { id quantity option product { id name } }',
            '    priceSummary { total subtotal }',
            '    redirectUrl',
            '  }',
            '}'
        ].join(' ');

        return fetch(DUTCHIE_ENDPOINT, {
            method: 'POST',
            cache: 'no-store',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + DUTCHIE_PUBLIC_TOKEN
            },
            body: JSON.stringify({
                query: getCheckoutQuery,
                variables: {
                    retailerId: RETAILER_ID,
                    checkoutId: chkId
                }
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(json) {
            if (json.data && json.data.checkout) {
                var checkout = json.data.checkout;
                var totalQty = 0;
                if (Array.isArray(checkout.items)) {
                    totalQty = checkout.items.reduce(function(acc, it) { return acc + (it.quantity || 1); }, 0);
                }
                var totalPrice = (checkout.priceSummary && checkout.priceSummary.total != null) ? checkout.priceSummary.total : null;
                var formattedUrl = formatCheckoutUrl(checkout.redirectUrl);

                saveCheckoutState(checkout.id, formattedUrl, totalQty, totalPrice);
                persistToLeafbridge(checkout, null);
                updateCartUI();
                return true;
            } else if (json.errors && json.errors.length) {
                if (/checkout not found|expired|invalid/i.test(json.errors[0].message)) {
                    clearCheckoutSession();
                    updateCartUI();
                }
                return false;
            }
            updateCartUI();
            return false;
        })
        .catch(function(err) {
            console.warn('[LAXCC] Could not sync checkout session:', err);
            updateCartUI();
            return false;
        });
    }

    // Keep this page synchronized when the native LeafBridge drawer changes
    // the shared cart (especially after removing an item). This is read-only
    // polling of the existing local state; it does not call WordPress AJAX.
    var lastLeafbridgeCartSignature = '';
    function watchLeafbridgeCart() {
        var raw = localStorage.getItem('leafbridge_cart_data') || '';
        if (raw === lastLeafbridgeCartSignature) return;
        lastLeafbridgeCartSignature = raw;
        syncSiteWideCart();
    }

    // Dutchie Checkout Management: Get or Create Checkout
    function getOrCreateCheckout() {
        var existingId = getStoredCheckoutId();
        if (existingId) {
            activeCheckoutId = existingId;
            if (!activeCheckoutUrl) {
                activeCheckoutUrl = formatCheckoutUrl('https://checkout-lax.laxcc.com/checkouts/' + RETAILER_CHECKOUT_SLUG + '/' + existingId);
            }
            return Promise.resolve({ id: activeCheckoutId, redirectUrl: activeCheckoutUrl });
        }

        var createMutation = [
            'mutation CreateCheckout($retailerId: ID!, $orderType: OrderType!, $pricingType: PricingType!) {',
            '  createCheckout(retailerId: $retailerId, orderType: $orderType, pricingType: $pricingType) {',
            '    id',
            '    redirectUrl',
            '  }',
            '}'
        ].join(' ');

        return fetch(DUTCHIE_ENDPOINT, {
            method: 'POST',
            cache: 'no-store',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + DUTCHIE_PUBLIC_TOKEN
            },
            body: JSON.stringify({
                query: createMutation,
                variables: {
                    retailerId: RETAILER_ID,
                    orderType: 'PICKUP',
                    pricingType: 'RECREATIONAL'
                }
            })
        })
        .then(function(res) {
            if (!res.ok) throw new Error('Checkout creation failed: HTTP ' + res.status);
            return res.json();
        })
        .then(function(json) {
            if (json.errors && json.errors.length) throw new Error(json.errors[0].message);
            var chk = json.data && json.data.createCheckout;
            if (!chk || !chk.id) throw new Error('Invalid checkout session returned');
            var formattedUrl = formatCheckoutUrl(chk.redirectUrl);
            saveCheckoutState(chk.id, formattedUrl, 0, 0);
            persistToLeafbridge({ id: chk.id, items: [] }, null);
            return { id: chk.id, redirectUrl: formattedUrl };
        });
    }

    // Add item to Dutchie cart mutation
    function addItemToDutchieCart(productId, option, quantity, productName) {
        return getOrCreateCheckout().then(function(checkout) {
            var addMutation = [
                'mutation AddItem($retailerId: ID!, $checkoutId: ID!, $productId: ID!, $option: String!, $quantity: Int!) {',
                '  addItem(retailerId: $retailerId, checkoutId: $checkoutId, productId: $productId, option: $option, quantity: $quantity) {',
                '    id',
                '    items { id quantity option product { id name } }',
                '    priceSummary { total subtotal }',
                '    redirectUrl',
                '  }',
                '}'
            ].join(' ');

            return fetch(DUTCHIE_ENDPOINT, {
                method: 'POST',
                cache: 'no-store',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + DUTCHIE_PUBLIC_TOKEN
                },
                body: JSON.stringify({
                    query: addMutation,
                    variables: {
                        retailerId: RETAILER_ID,
                        checkoutId: checkout.id,
                        productId: productId,
                        option: option || 'N/A',
                        quantity: quantity || 1
                    }
                })
            })
            .then(function(res) {
                if (!res.ok) throw new Error('Add to cart failed: HTTP ' + res.status);
                return res.json();
            })
            .then(function(json) {
                if (json.errors && json.errors.length) {
                    var errMsg = json.errors[0].message;
                    // If checkout session expired or not found, clear and retry once
                    if (/checkout not found|expired|invalid/i.test(errMsg)) {
                        clearCheckoutSession();
                        return addItemToDutchieCart(productId, option, quantity, productName);
                    }
                    throw new Error(errMsg);
                }
                var updatedCart = json.data && json.data.addItem;
                if (updatedCart) {
                    var totalQty = 0;
                    if (Array.isArray(updatedCart.items)) {
                        totalQty = updatedCart.items.reduce(function(acc, it) { return acc + (it.quantity || 1); }, 0);
                    } else {
                        totalQty = activeCheckoutTotalCount + 1;
                    }
                    var totalPrice = (updatedCart.priceSummary && updatedCart.priceSummary.total != null) ? updatedCart.priceSummary.total : null;
                    var formattedUrl = formatCheckoutUrl(updatedCart.redirectUrl);

                    saveCheckoutState(updatedCart.id, formattedUrl, totalQty, totalPrice);
                    persistToLeafbridge(updatedCart, { id: productId, option: option, name: productName });
                    updateCartUI();
                }
                return updatedCart;
            });
        });
    }

    // Global click handler for Add to Cart
    window.laxccAddToCart = function(event, btn) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (!btn || btn.disabled) return;

        var productId = btn.getAttribute('data-product-id');
        var productName = btn.getAttribute('data-product-name') || '';
        var option = btn.getAttribute('data-option') || 'N/A';

        var originalText = btn.innerHTML;
        btn.disabled = true;
        btn.classList.add('is-loading');
        btn.innerHTML = '<span>Adding...</span>';

        addItemToDutchieCart(productId, option, 1, productName)
            .then(function(cartData) {
                btn.classList.remove('is-loading');
                btn.classList.add('is-success');
                btn.innerHTML = '<span>Added! ✓</span>';

                setTimeout(function() {
                    btn.disabled = false;
                    btn.classList.remove('is-success');
                    btn.innerHTML = originalText;
                }, 2200);
            })
            .catch(function(err) {
                console.error('[LAXCC Cart Error]', err);
                btn.classList.remove('is-loading');
                btn.innerHTML = '<span>Failed</span>';
                alert('Could not add item to cart: ' + (err.message || 'Please try again.'));
                setTimeout(function() {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }, 2000);
            });
    };

    function renderCard(product) {
        var imgHtml = product.image
            ? '<div class="laxcc-product-photo"><img src="' + escapeHtml(product.image) + '" alt="' + escapeHtml(product.name) + '" class="laxcc-prod-img" loading="lazy" decoding="async"></div>'
            : '<div class="laxcc-product-photo"><div class="laxcc-prod-img" style="display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:12px;">Image Coming Soon</div></div>';

        var bogoBadge = '<span class="laxcc-prod-bogo-badge">' + flameSvgHtml + escapeHtml(product.specialConfig.badgeText) + '</span>';
        var catBadge = product.category ? '<span class="laxcc-prod-category-badge">' + escapeHtml(product.category) + '</span>' : '';
        var strainBadge = product.strain ? '<span class="laxcc-prod-strain-badge">' + escapeHtml(product.strain) + '</span>' : '';
        var brandHtml = product.brand ? '<span class="laxcc-prod-brand">' + escapeHtml(product.brand) + '</span>' : '';

        var priceDisplay = '$' + Number(product.price).toFixed(2);
        var rawOption = product.option ? String(product.option).trim() : '';
        if (/^\.\d+/.test(rawOption)) {
            rawOption = '0' + rawOption;
        }
        var optionText = rawOption ? escapeHtml(rawOption) : '';
        var optionBadge = optionText ? '<span class="laxcc-prod-option">' + optionText + '</span>' : '';

        return [
            '<a href="https://laxcc.com/product/' + encodeURIComponent(product.slug) + '/" class="laxcc-special-card" target="_blank" rel="noopener">',
            '  <div class="laxcc-special-card-body">',
            '    <div class="laxcc-deal-watermark" aria-hidden="true">🔥</div>',
            '    <div class="laxcc-prod-img-wrap">',
            '      <span class="laxcc-deal-flame laxcc-deal-flame-1" aria-hidden="true">🔥</span>',
            '      <span class="laxcc-deal-flame laxcc-deal-flame-2" aria-hidden="true">🔥</span>',
            '      <span class="laxcc-deal-flame laxcc-deal-flame-3" aria-hidden="true">🔥</span>',
            '      <span class="laxcc-deal-flame laxcc-deal-flame-4" aria-hidden="true">🔥</span>',
            imgHtml,
            bogoBadge,
            catBadge,
            strainBadge,
            optionBadge,
            '    </div>',
            brandHtml,
            '    <h3 class="laxcc-prod-name">' + escapeHtml(product.name) + '</h3>',
            '    <div class="laxcc-prod-footer">',
            '      <div class="laxcc-prod-pricing">',
            '        <span class="laxcc-prod-price">' + priceDisplay + '</span>',
            '      </div>',
            '      <button type="button" class="laxcc-prod-order-btn" data-product-id="' + escapeHtml(product.id) + '" data-product-name="' + escapeHtml(product.name) + '" data-option="' + escapeHtml(product.option || 'N/A') + '" onclick="laxccAddToCart(event, this)">Add to Cart</button>',
            '    </div>',
            '  </div>',
            '</a>'
        ].join('\n');
    }

    function filterAndSortProducts() {
        var filtered = allSpecialProducts.slice();

        // Filter by tab
        if (currentActiveSpecial !== 'all') {
            filtered = filtered.filter(function(p) {
                return p.specialIds.indexOf(currentActiveSpecial) !== -1;
            });
        }

        // Sort
        if (currentSort === 'price-asc') {
            filtered.sort(function(a, b) { return a.price - b.price; });
        } else if (currentSort === 'price-desc') {
            filtered.sort(function(a, b) { return b.price - a.price; });
        } else if (currentSort === 'name-asc') {
            filtered.sort(function(a, b) { return a.name.localeCompare(b.name); });
        } else {
            // Featured sort: prioritize Manzanita Naturals drinks if selected or on all
            filtered.sort(function(a, b) {
                var aManz = /manzanita/i.test(a.brand) ? 1 : 0;
                var bManz = /manzanita/i.test(b.brand) ? 1 : 0;
                return bManz - aManz;
            });
        }

        return filtered;
    }

    function updateView() {
        var list = filterAndSortProducts();

        if (countAllEl) countAllEl.textContent = allSpecialProducts.length;
        if (activeCountEl) activeCountEl.textContent = list.length + ' Product' + (list.length === 1 ? '' : 's');

        // Update titles and spotlight banner depending on active tab
        if (currentActiveSpecial === 'all') {
            activeTitleEl.textContent = 'All Active Specials';
            activeSubEl.textContent = 'Showing all verified dispensary promotions and BOGO items in stock today.';
            if (spotlightNameEl) spotlightNameEl.textContent = 'Manzanita Natural BOGO Discount';
            if (spotlightDescEl) spotlightDescEl.textContent = 'Buy one craft THC beverage, get one promo discount. In stock today.';
        } else if (SPECIALS_INFO[currentActiveSpecial]) {
            var info = SPECIALS_INFO[currentActiveSpecial];
            activeTitleEl.textContent = info.name;
            activeSubEl.textContent = info.desc;
            if (spotlightNameEl) spotlightNameEl.textContent = info.name;
            if (spotlightDescEl) spotlightDescEl.textContent = info.desc;
        }

        if (list.length === 0) {
            gridEl.innerHTML = '';
            gridEl.classList.add('laxcc-hidden');
            emptyEl.classList.remove('laxcc-hidden');
        } else {
            emptyEl.classList.add('laxcc-hidden');
            gridEl.classList.remove('laxcc-hidden');
            gridEl.innerHTML = list.map(renderCard).join('\n');
        }
    }

    function setActiveTab(specialId) {
        currentActiveSpecial = specialId;

        // Update button states
        var pills = tabsContainer.querySelectorAll('.laxcc-filter-pill');
        pills.forEach(function(pill) {
            var id = pill.dataset.specialId;
            var isActive = (id === specialId);
            pill.classList.toggle('active', isActive);
            pill.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        // Update URL query state without full reload
        var url = new URL(window.location);
        if (specialId !== 'all') {
            url.searchParams.set('specials_id', specialId);
        } else {
            url.searchParams.delete('specials_id');
            url.searchParams.delete('special_id');
        }
        window.history.replaceState({}, '', url);

        updateView();
    }

    function initEvents() {
        if (tabsContainer) {
            tabsContainer.addEventListener('click', function(e) {
                var pill = e.target.closest('.laxcc-filter-pill');
                if (!pill) return;
                var specialId = pill.dataset.specialId;
                setActiveTab(specialId);
            });
        }

        if (sortSelectEl) {
            sortSelectEl.addEventListener('change', function(e) {
                currentSort = e.target.value;
                updateView();
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                if (sortSelectEl) sortSelectEl.value = 'featured';
                currentSort = 'featured';
                setActiveTab('all');
            });
        }

        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', function(e) {
                var checkoutId = getStoredCheckoutId();
                if (!checkoutId) return;

                e.preventDefault();
                checkoutBtn.setAttribute('aria-busy', 'true');
                syncSiteWideCart().then(function() {
                    var href = activeCheckoutUrl || getCheckoutUrl(checkoutId);
                    checkoutBtn.removeAttribute('aria-busy');
                    if (href) window.location.assign(href);
                });
            });
        }
    }

    function loadSpecials() {
        fetchAllProducts(0, [])
            .then(function(products) {
                allSpecialProducts = processSpecialProducts(products);
                rebuildSpecialTabs(allSpecialProducts);
                
                // Initialize default tab from URL if present
                var initialSpecialId = getUrlSpecialId();
                if (initialSpecialId && (SPECIALS_INFO[initialSpecialId] || initialSpecialId === 'all')) {
                    currentActiveSpecial = initialSpecialId;
                } else {
                    currentActiveSpecial = 'all';
                }

                setActiveTab(currentActiveSpecial);
            })
            .catch(function(err) {
                console.error('[LAXCC Specials] Error loading live inventory:', err);
                gridEl.innerHTML = '<div class="laxcc-sp-loading"><p style="color:#ef4444;font-weight:700;">Unable to load live specials at this time. Please reload or check back in a few minutes.</p></div>';
            });
    }

    // Initialize
    updateCartUI();
    initEvents();
    watchLeafbridgeCart();
    setInterval(watchLeafbridgeCart, 1500);
    loadSpecials();

    // Auto-refresh every 5 minutes
    setInterval(loadSpecials, REFRESH_INTERVAL_MS);
})();
