// {block name="backend/index/application"}
// {$smarty.block.parent}
// {namespace name="backend/paypal_unified/mixin/onboarding_helper"}
//
Ext.define('Shopware.apps.PaypalUnified.mixin.OnboardingHelper', {
    onboardingHelper: {
        snippets: {
            locale: '{s namespace="backend/base/index" name="script/ext/locale"}{/s}',
            onboardingButton: {
                label: '{s name="fieldset/rest/onboarding_button/label"}Enrol your account for Pay Upon Invoice here{/s}',
                text: '{s name="fieldset/rest/onboarding_button/text"}Authorize{/s}'
            }
        },

        payPalScriptUrl: 'https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js',
        payPalScriptContainerId: 'swag-payment-paypal-unified-paypal-partner-js',
        payPalScriptOnboardingCallback: 'paypalOnboardingCallback',

        payPalOnboardingUrl: {
            live: 'https://www.paypal.com/bizsignup/partner/entry',
            sandbox: 'https://www.sandbox.paypal.com/bizsignup/partner/entry'
        },

        partner: {
            clientId: {
                live: 'AR1aQ13lHxH1c6b3CDd8wSY6SWad2Lt5fv5WkNIZg-qChBoGNfHr2kT180otUmvE_xXtwkgahXUBBurW',
                sandbox: 'AQ9g8qMYHpE8s028VCq_GO3Roy9pjeqGDjKTkR_sxzX0FtncBb3QUWbFtoQMtdpe2lG9NpnDT419dK8s'
            },
            payerId: {
                live: 'DYKPBPEAW5JNA',
                sandbox: '45KXQA7PULGAG',
            }
        },

        params: {
            sellerNonce: {
                live: '{$sellerNonceLive}',
                sandbox: '{$sellerNonceSandbox}',
            },
            general: {
                partnerLogoUrl: 'https://assets.shopware.com/media/logos/shopware_logo_blue.svg',
                integrationType: 'FO',
                features: 'PAYMENT,REFUND,READ_SELLER_DISPUTE,UPDATE_SELLER_DISPUTE,ADVANCED_TRANSACTIONS_SEARCH,ACCESS_MERCHANT_INFORMATION,TRACKING_SHIPMENT_READWRITE',
                displayMode: 'minibrowser'
            },
            ppcp: {
                product: 'PPCP'
            },
            pui: {
                product: 'payment_methods',
                capabilities: 'PAY_UPON_INVOICE'
            },
            combined: {
                product: 'ppcp',
                secondaryProducts: 'payment_methods',
                capabilities: 'PAY_UPON_INVOICE'
            }
        },

        renderMethodReplacedIndicator: 'SwagPaymentPayPalUnifiedRenderMethodReplaced'
    },

    config: {
        sandbox: false,
        eventTarget: null,
        authCodeReceivedEventName: null,
        updateCredentialsUrl: '{url module=backend controller=PayPalUnified action=updateCredentials}'
    },

    constructor: function(config) {
        this.initConfig(config);
    },

    /**
     * @returns { Ext.button.Button }
     */
    createOnboardingButtonStandalone: function (value) {
        var me = this;

        this.submitValue = value;

        return Ext.create('Ext.button.Button', {
            text: this.onboardingHelper.snippets.onboardingButton.text,
            cls: 'primary',
            ui: 'shopware-ui',
            name: 'startOnboarding',
            disabled: false,
            href: me.getOnboardingUrl(),
            listeners: {
                afterrender: function(component) {
                    component.btnEl.dom.dataset.paypalOnboardComplete = me.onboardingHelper.payPalScriptOnboardingCallback;
                    component.btnEl.dom.dataset.paypalButton = true;

                    me.renderOnboardingButton();
                }
            },
            style: {
                paddingTop: '6px',
                paddingBottom: '6px',
                height: '28px',
            }
        });
    },

    createOnboardingButtonFormElement: function (value) {
        var me = this;

        return {
            xtype: 'fieldcontainer',
            fieldLabel: this.onboardingHelper.snippets.onboardingButton.label,
            labelWidth: 250,
            items: [
                me.createOnboardingButtonStandalone(value)
            ]
        };
    },

    getOnboardingParams: function () {
        return Object.assign(
            this._getOnboardingParamsGeneral(),
            this.onboardingHelper.params.combined
        );
    },

    _getOnboardingParamsGeneral: function () {
        var localisation = null;

        if (this.onboardingHelper.snippets.locale) {
            localisation = {
                'country.x': this.onboardingHelper.snippets.locale.slice(-2).toUpperCase(),
                'locale.x': this.onboardingHelper.snippets.locale.replace('_', '-')
            }
        }

        return Object.assign(
            {
                partnerClientId: this.getSandbox() ? this.onboardingHelper.partner.clientId.sandbox : this.onboardingHelper.partner.clientId.live,
                partnerId: this.getSandbox() ? this.onboardingHelper.partner.payerId.sandbox : this.onboardingHelper.partner.payerId.live,
                sellerNonce: this.getSandbox() ? this.onboardingHelper.params.sellerNonce.sandbox : this.onboardingHelper.params.sellerNonce.live,
            },
            this.onboardingHelper.params.general,
            localisation
        );
    },

    /**
     * @returns { String }
     */
    getOnboardingUrl: function () {
        // {literal}
        return Ext.String.format(
            '{0}?{1}',
            this.getSandbox() ? this.onboardingHelper.payPalOnboardingUrl.sandbox : this.onboardingHelper.payPalOnboardingUrl.live,
            Ext.Object.toQueryString(this.getOnboardingParams())
        );
        // {/literal}
    },

    /**
     * Fetches PayPal's partner.js and executes it's render-method after load.
     */
    renderOnboardingButton: function () {
        if (!window[this.onboardingHelper.payPalScriptOnboardingCallback]) {
            window[this.onboardingHelper.payPalScriptOnboardingCallback] = this._onAuthCodeReceived.bind(this);
        }

        if (!document.getElementById(this.onboardingHelper.payPalScriptContainerId)) {
            document.head.appendChild(this._createScript());
        }

        if (window.PAYPAL) {
            this._renderPayPalButton();
        }
    },

    /**
     * @returns { HTMLScriptElement }
     *
     * @private
     */
    _createScript: function() {
        var payPalScript = document.createElement('script');

        payPalScript.id = this.onboardingHelper.payPalScriptContainerId;
        payPalScript.type = 'text/javascript';
        payPalScript.src = this.onboardingHelper.payPalScriptUrl;
        payPalScript.async = true;

        payPalScript.addEventListener('load', this._renderPayPalButton.bind(this), false);

        return payPalScript;
    },

    /**
     * @see https://github.com/shopwareLabs/SwagPayPal/blob/master/src/Resources/app/administration/src/mixin/swag-paypal-credentials-loader.mixin.js
     *
     * @private
     */
    _renderPayPalButton: function() {
        if (!window[this.onboardingHelper.renderMethodReplacedIndicator]) {
            // The original render function inside the partner.js is overwritten here.
            // The function gets overwritten again, as soon as PayPals signup.js is loaded.
            // A loop is created and the render() function is executed until the real render() function is available.
            // PayPal does originally nearly the same, but only once and not in a loop.
            // If the signup.js is loaded to slow the button is not rendered.
            window.PAYPAL.apps.Signup.render = function proxyPPrender() {
                if (window.PAYPAL.apps.Signup.timeout) {
                    clearTimeout(window.PAYPAL.apps.Signup.timeout);
                }

                window.PAYPAL.apps.Signup.timeout = setTimeout(window.PAYPAL.apps.Signup.render, 300);
            };

            window[this.onboardingHelper.renderMethodReplacedIndicator] = true;
        }

        window.PAYPAL.apps.Signup.render();
    },

    /**
     * @param { String } authCode
     * @param { String } sharedId
     *
     * @private
     */
    _onAuthCodeReceived: function (authCode, sharedId) {
        if (this.getEventTarget() !== null) {
            this.getEventTarget().fireEvent(
                this.getAuthCodeReceivedEventName(),
                authCode,
                sharedId,
                this.getSandbox() ? this.onboardingHelper.params.sellerNonce.sandbox : this.onboardingHelper.params.sellerNonce.live,
                this.getSandbox() ? this.onboardingHelper.partner.payerId.sandbox : this.onboardingHelper.partner.payerId.live,
                this.submitValue
            );
        } else {
            this.fireEvent(
                this.getAuthCodeReceivedEventName(),
                authCode,
                sharedId,
                this.getSandbox() ? this.onboardingHelper.params.sellerNonce.sandbox : this.onboardingHelper.params.sellerNonce.live,
                this.getSandbox() ? this.onboardingHelper.partner.payerId.sandbox : this.onboardingHelper.partner.payerId.live,
                this.submitValue
            );
        }
    }
});
// {/block}
