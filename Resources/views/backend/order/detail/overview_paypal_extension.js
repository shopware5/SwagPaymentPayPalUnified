//
//{block name="backend/order/view/detail/overview"}
//{$smarty.block.parent}

Ext.define('Shopware.apps.Order.view.detail.PaypalExtensionOverview', {
    override: 'Shopware.apps.Order.view.detail.Overview',

    paypalPaymentMethodNames: '{$paypalPaymentMethodNames}',

    trackingUrlTemplate: 'https://www.paypal.com/addtracking/add/%s',

    trackingButtonText: '{s namespace="backend/order/detail/overview" name="trackingButtonText"}Add tracking code to Paypal{/s}',

    createRightDetailElements: function () {
        var items = this.callParent(arguments);

        this.paypalPaymentMethodNames = Ext.JSON.decode(this.paypalPaymentMethodNames);

        if (this.record.raw.payment === null) {
            return items;
        }

        if (this.paypalPaymentMethodNames.indexOf(this.record.raw.payment.name) >= 0) {
            items.push(this.createExtendedTrackingItem());
        }

        return items;
    },

    /**
     * @returns { Ext.container.Container }
     */
    createExtendedTrackingItem: function () {
        return Ext.create('Ext.container.Container', {
            style: {
                marginTop: '10px'
            },
            items: [
                this.createPayPalDeepLink()
            ]
        });
    },

    /**
     * @returns { Ext.button.Button }
     */
    createPayPalDeepLink: function () {
        var url = this.trackingUrlTemplate.replace('%s', this.record.raw.transactionId);

        return Ext.create('Ext.button.Button', {
            flex: 1,
            text: this.trackingButtonText,
            ui: 'shopware-ui',
            cls: 'primary paypalTrackingButton',
            href: url,
            hrefTarget: '_blank',
            style: {
                paddingTop: '7px',
                paddingBottom: '5px',
                height: '28px',
                margin: '0px',
            }
        })
    },
});
// {/block}
