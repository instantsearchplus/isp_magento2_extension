if (typeof requirejs !== 'undefined') {
    requirejs([
        'jquery',
        'Magento_Customer/js/customer-data'
    ], function ($, customerData) {
        'use strict';

        $(function () {
            var body = $('body'),
                reloading = false,
                skipped = false,
                pending = false;

            if (!(body.hasClass('catalog-product-view')
                || body.hasClass('catalog-category-view')
                || body.hasClass('instantsearchplus-result-index')
                || body.hasClass('catalogsearch-result-index'))) {
                return;
            }

            // Product page: read the id from the page itself. The session value the isp_config
            // section used to return is only set when the page is rendered by PHP, so it is empty
            // whenever the page is served from full-page cache / Varnish / Fastly.
            function publishProductId() {
                var productId;

                if (!body.hasClass('catalog-product-view')) {
                    return;
                }
                // The main add-to-cart form first: a CatalogWidget products grid on the page
                // carries its own hidden `product` input after it, so `.last()` alone can pick
                // a widget item. The bare fallback covers themes that rename the form.
                productId = $('#product_addtocart_form input[name=product]').val()
                    || $('input[type=hidden][name=product]').last().val();
                if (!productId) {
                    return;
                }
                if (typeof window.checkout !== 'undefined') {
                    window.checkout.isp_product_id = productId;
                } else {
                    window.isp_product_id = productId;
                }
            }

            function applyConfig(data) {
                if (!data || !data.hasOwnProperty('QuoteID')) {
                    return;
                }
                if (typeof window.checkout !== 'undefined') {
                    window.checkout.QuoteID = data.QuoteID;
                } else {
                    window.isp_quote_id = data.QuoteID;
                }
            }

            // The quote id only changes when the cart does, so re-fetch isp_config only when
            // Magento's own cart section is newer than the cached isp_config, and only when
            // there is a quote worth knowing about (items in the cart, or a cached id that may
            // now be stale). The section also carries customer_group_id, which the storefront
            // reads from mage-cache-storage, so a `customer` section newer than isp_config (a
            // login - the AJAX popup invalidates `customer` and `cart`, never isp_config)
            // re-fetches too. A visitor with neither - every crawler - never triggers a request.
            // No timed re-fetch for an admin group change: core applies it to the session only on
            // that customer's next POST, which this GET section/load can never see.
            // data_id is a whole-second server time(). Strict `>` keeps sections from one response
            // (equal stamps) from re-fetching; the re-check below passes orEqual, because an update
            // that raced an in-flight reload is most likely stamped in that same second.
            function isNewer(section, config, orEqual) {
                return section.data_id && (!config.data_id
                    || (orEqual ? section.data_id >= config.data_id : section.data_id > config.data_id));
            }

            function refreshIfChanged(recheck) {
                var cart, customer, config;

                if (reloading) {
                    // Re-checked once this reload succeeds: the update may be newer than the
                    // isp_config the in-flight request returns.
                    skipped = true;
                    return;
                }
                cart = customerData.get('cart')() || {};
                customer = customerData.get('customer')() || {};
                config = customerData.get('isp_config')() || {};
                if (!((isNewer(cart, config, recheck) && (cart.summary_count > 0 || config.QuoteID))
                    || isNewer(customer, config, recheck))) {
                    return;
                }
                reloading = true;
                // Re-check only when an update was actually skipped, never unconditionally: an
                // unconditional re-check would reload again for as long as the returned data_id
                // stays older (a clock skew between web nodes), and on failure the section is
                // still older, so a failing endpoint would be retried in a loop.
                customerData.reload(['isp_config'], false).done(function () {
                    reloading = false;
                    if (skipped) {
                        skipped = false;
                        // Bounded: `skipped` is cleared just above, so the reload this may start
                        // re-checks only if yet another update races it.
                        refreshIfChanged(true);
                    }
                });
                // No .fail handler: core's getFromServer attaches one first, which throws on every
                // failure through 2.4.7 and on any non-zero status from 2.4.8 (jQuery then stops
                // that callback list), so ours would not run reliably. Either way `.done` never
                // runs, and a failed reload leaves `reloading` set for the rest of this page view -
                // no retry loop, and the next page view re-evaluates from the cached sections.
            }

            // One response can carry `customer`, `cart` and `isp_config` together, and core
            // notifies subscribers one section at a time. Defer the check to the next tick so it
            // sees the isp_config from that same response instead of reloading it again.
            function scheduleRefresh() {
                if (!pending) {
                    pending = true;
                    setTimeout(function () {
                        pending = false;
                        refreshIfChanged(false);
                    }, 0);
                }
            }

            function start() {
                applyConfig(customerData.get('isp_config')());
                refreshIfChanged(false);
            }

            try {
                publishProductId();
                customerData.get('isp_config').subscribe(applyConfig);
                customerData.get('cart').subscribe(scheduleRefresh);
                customerData.get('customer').subscribe(scheduleRefresh);

                if (typeof customerData.getInitCustomerData === 'function') {
                    customerData.getInitCustomerData().done(start);
                } else {
                    try {
                        customerData.initStorage();
                    } catch (e) {}
                    start();
                }
            } catch (e) {}
        });
    });
}
