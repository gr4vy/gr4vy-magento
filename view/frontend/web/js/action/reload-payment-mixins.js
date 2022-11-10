/* eslint-disable */
define([
  'mage/utils/wrapper',
  'mage/storage',
  'Magento_Checkout/js/model/payment/renderer-list',
  'Magento_Checkout/js/model/quote',
  'Gr4vy_Magento/js/model/config',
  'jquery'
], function (wrapper, storage, renderer, quote, config, $) {
  'use strict';

  var last_shipping_country_id = '';
  var last_billing_country_id = '';
  var last_shipping_company = '';
  var last_billing_company = '';

  return function (overriddenFunction) {
    return wrapper.wrap(overriddenFunction, function (originalAction, shippingMethod) {
      var originalResult = originalAction(shippingMethod);

      if (typeof originalResult === 'object') {
        if (quote.billingAddress() !== null) {
          originalResult['countryId'] = quote.billingAddress().countryId;
          originalResult['company'] = quote.billingAddress().company;
        }
      }

      var recalculate_params = false;
      var ajax_params = {};

      if (quote.isVirtual() && originalResult !== undefined && 'countryId' in originalResult) {
        ajax_params = {
          billing_country_id: originalResult['countryId'],
          billing_company: originalResult['company']
        };

        if (last_billing_country_id !== ajax_params['billing_country_id']) {
          recalculate_params = true;
        }
        if (last_billing_company !== ajax_params['billing_company']) {
          recalculate_params = true;
        }

        last_billing_company = ajax_params['billing_company'];
        last_billing_country_id = ajax_params['billing_country_id'];
      } else {
        if (quote.shippingAddress() === null) {
          return originalResult;
        }

        ajax_params = {
          shipping_country_id: quote.shippingAddress().countryId,
          shipping_company: quote.shippingAddress().company
        };

        if (last_shipping_country_id !== ajax_params['shipping_country_id']) {
          recalculate_params = true;
        }
        if (last_shipping_company !== ajax_params['shipping_company']) {
          recalculate_params = true;
        }

        last_shipping_company = ajax_params['shipping_company'];
        last_shipping_country_id = ajax_params['shipping_country_id'];
      }

      // only reload if params require recalculation and gr4vy payment form was rendered before
      if (recalculate_params && window.checkoutConfig.payment.gr4vy.rendered) {
          var gr4vy_entry = {
              type: 'gr4vy',
              component: 'Gr4vy_Magento/js/view/payment/method-renderer/gr4vy-method'
          };

          renderer.remove(gr4vy_entry);
          renderer.push(gr4vy_entry);
          config.reloadEmbed(new Date().getTime());
      }

      return originalResult;
    });
  };
});
