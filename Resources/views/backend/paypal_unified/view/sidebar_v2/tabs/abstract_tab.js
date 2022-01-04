// {namespace name="backend/paypal_unified/v2/main"}
// {block name="backend/paypal_unified/sidebarV2/tabs/AbstractTab"}
Ext.define('Shopware.apps.PaypalUnified.view.sidebarV2.tabs.AbstractTab', {
    extend: 'Ext.form.Panel',
    alias: 'widget.paypal-unified-sidebarV2-AbstractTab',

    bodyPadding: 5,

    autoScroll: true,

    style: {
        background: '#EBEDEF'
    },

    initComponent: function() {
        this.items = this.createItems();
        this.dockedItems = this.createDockedItems();

        this.callParent(arguments);
    },

    /**
     * @return { Array|null }
     */
    createDockedItems: function() {
        return null;
    },

    /**
     * @return { Array }
     */
    createItems: function() {
        throw new Error('The method "createItems" should be overwritten');
    },

    /**
     * @param paypalOrderData { Object }
     */
    setOrderData: function(paypalOrderData) {
        throw new Error('The method "setOrderData" should be overwritten');
    },
});
// {/block}
