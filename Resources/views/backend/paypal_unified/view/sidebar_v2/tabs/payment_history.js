// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebarV2/tabs/PaymentHistory"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.tabs.PaymentHistory', {
    extend: 'Shopware.apps.PaypalUnified.view.sidebarV2.tabs.AbstractTab',
    alias: 'widget.paypal-unified-sidebarV2-PaymentHistory',

    title: '{s name="tabs/title/PaymentHistory"}Payment history{/s}',

    paypalOrderIntent: {
        AUTHORIZE: 'AUTHORIZE',
        CAPTURE: 'CAPTURE',
    },

    paypalOrderAuthorizationStatus: {
        CAPTURED: 'CAPTURED',
        VOIDED: 'VOIDED',
    },

    /**
     * @returns { Array }
     */
    createItems: function() {
        var items = [];

        this.histroyGrid = Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.order.grid.PaymentHistoryGrid');
        this.paymentDetails = Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.order.fieldset.paymentHistory.PaymentDetails');
        this.refundButton = Ext.create('Shopware.apps.PaypalUnified.view.sidebarV2.order.fieldset.paymentHistory.RefundButton');

        items.push(this.histroyGrid);
        items.push(this.paymentDetails);
        items.push(this.refundButton);

        return items;
    },

    /**
     * @returns { Array }
     */
    createDockedItems: function() {
        var dockedItems = [];

        this.toolbar = this.createToolbar();

        dockedItems.push(this.toolbar);

        return dockedItems;
    },

    /**
     * @param paypalOrderData { Object }
     */
    setOrderData: function(paypalOrderData) {
        this.histroyGrid.setOrderData(paypalOrderData);

        this.handleToolbarAndButtonVisibility(paypalOrderData);
    },

    /**
     * @return { String }
     */
    getCurrentPaymentType: function() {
        return this.histroyGrid.getCurrentPaymentType();
    },

    /**
     * @return { Ext.toolbar.Toolbar }
     */
    createToolbar: function() {
        return Ext.create('Ext.toolbar.Toolbar', {
            dock: 'bottom',
            ui: 'shopware-ui',
            hidden: true,
            items: [
                this.createCancelAuthorizationButton(),
                '->',
                this.createCaptureButton()
            ],
        });
    },

    /**
     * @return { Ext.button.Button }
     */
    createCaptureButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: '{s name="paymentTypes/type/capture"}Capture{/s}',
            cls: 'primary',
            handler: function() {
                me.fireEvent('capture', '');
            },
        });
    },

    /**
     * @return { Ext.button.Button }
     */
    createCancelAuthorizationButton: function() {
        var me = this;

        me.cancelAuthorizationButton = Ext.create('Ext.button.Button', {
            text: '{s name="tabs/PaymentHistory/cancelAuthorization"}Cancel authorization{/s}',
            cls: 'secondary',
            hidden: true,
            handler: function() {
                me.fireEvent('cancelAuthorization', '');
            },
        });

        return me.cancelAuthorizationButton;
    },

    /**
     * @param paypalOrderData { Object }
     */
    handleToolbarAndButtonVisibility: function(paypalOrderData) {
        var authorizations;

        this.toolbar.hide();
        this.cancelAuthorizationButton.hide();
        this.refundButton.disable();

        if (paypalOrderData.intent === this.paypalOrderIntent.AUTHORIZE) {
            this.toolbar.show();
        }

        if (this.isShowCancelAuthorizationButton(paypalOrderData)) {
            this.cancelAuthorizationButton.show();
        }

        if (paypalOrderData.intent === this.paypalOrderIntent.CAPTURE) {
            this.refundButton.enable();
            return;
        }

        authorizations = paypalOrderData.purchase_units[0].payments.authorizations;
        if (Ext.isArray(authorizations) && authorizations.length &&
            authorizations[0].status === this.paypalOrderAuthorizationStatus.CAPTURED) {
            this.refundButton.enable();
        }
    },

    /**
     * @param paypalOrderData { Object }
     */
    isShowCancelAuthorizationButton: function(paypalOrderData) {
        var payments = paypalOrderData.purchase_units[0].payments

        if (Ext.isArray(payments.captures) && payments.captures.length) {
            return false
        }

        if (Ext.isArray(payments.refunds) && payments.refunds.length) {
            return false;
        }

        if (!Ext.isArray(payments.authorizations || !payments.authorizations.length)) {
            return false;
        }

        if (payments.authorizations[0].status === this.paypalOrderAuthorizationStatus.VOIDED) {
            return false;
        }

        return true;
    },
});
// {/block}
