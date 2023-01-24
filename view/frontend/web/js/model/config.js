define(
    [
        'ko'
    ],
    function (ko) {
        'use strict';

        var gr4vyId = window.checkoutConfig.payment.gr4vy.gr4vy_id,
            enabled = window.checkoutConfig.payment.gr4vy.is_enabled,
            hasErrors = ko.observable(false),
            reloadEmbed = ko.observable(false),
            buyerId = window.checkoutConfig.payment.gr4vy.buyer_id,
            externalIdentifier = window.checkoutConfig.payment.gr4vy.external_identifier,
            environment = window.checkoutConfig.payment.gr4vy.environment,
            store = window.checkoutConfig.payment.gr4vy.store,
            element = ".container",
            form = "#co-payment-form",
            amount = ko.observable(window.checkoutConfig.payment.gr4vy.total_amount),
            currency = window.checkoutConfig.quoteData.quote_currency_code,
            country = window.checkoutConfig.originCountryCode,
            locale = window.checkoutConfig.payment.gr4vy.locale,
            paymentSource = window.checkoutConfig.payment.gr4vy.payment_source,
            requireSecurityCode = window.checkoutConfig.payment.gr4vy.require_security_code,
            theme = window.checkoutConfig.payment.gr4vy.theme,
            statementDescriptor = window.checkoutConfig.payment.gr4vy.statement_descriptor,
            reloadConfigUrl = window.checkoutConfig.payment.gr4vy.reload_config_url,
            token = ko.observable(window.checkoutConfig.payment.gr4vy.authorization_token),
            intent = ko.observable(window.checkoutConfig.payment.gr4vy.intent),
            cartItems = ko.observable(window.checkoutConfig.payment.gr4vy.items),
            metadata = {
                "magento_custom_data": window.checkoutConfig.payment.gr4vy.custom_data
            };

        return {
            gr4vyId: gr4vyId,
            enabled: enabled,
            hasErrors: hasErrors,
            reloadEmbed: reloadEmbed,
            buyerId: buyerId,
            externalIdentifier: externalIdentifier,
            environment: environment,
            store: store,
            element: element,
            form: form,
            amount: amount,
            currency: currency,
            country: country,
            locale: locale,
            paymentSource: paymentSource,
            requireSecurityCode: requireSecurityCode,
            theme: theme,
            statementDescriptor: statementDescriptor,
            token: token,
            intent: intent,
            cartItems: cartItems,
            metadata: metadata,
            reloadConfigUrl: reloadConfigUrl
        };
    }
);
