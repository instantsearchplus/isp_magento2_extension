if (typeof requirejs !== 'undefined') {
    requirejs([
        'jquery',
        'Magento_Customer/js/customer-data'
    ], function ($, customerData) {
        'use strict';
        if ($('body').hasClass('catalog-product-view')
            || $('body').hasClass('catalog-category-view')
            || $('body').hasClass('instantsearchplus-result-index')
            || $('body').hasClass('catalogsearch-result-index')) {
            var sections = ['isp_config'];//replace cart with your custom section name if you want to load your custom data
            try {
                try {
                    customerData.initStorage();
                } catch (e) {}
                customerData.reload(sections, true).done(
                    function (d) {
                        if (typeof window.checkout !== 'undefined') {
                            window.checkout.QuoteID = d['isp_config'].QuoteID;
                            window.checkout.isp_product_id = d['isp_config'].product_id;
                        } else {
                            window.isp_quote_id = d['isp_config'].QuoteID;
                            window.isp_product_id = d['isp_config'].product_id;
                        }
                    }
                );
            } catch (e) {}
        }
    });
}