//
// {namespace name="backend/config/view/document"}
// {block name="backend/config/view/form/document"}
// {$smarty.block.parent}
Ext.define('Shopware.apps.Config.view.form.DocumentPaypalUnified', {
    override: 'Shopware.apps.Config.view.form.Document',
    alias: 'widget.config-form-document-paypal-unified',

    initComponent: function() {
        var me = this;
        me.callParent(arguments);
    },

    /**
     * Overrides the getFormItems method and appends the PayPal form items
     * @return { Array }
     */
    getFormItems: function() {
        var formItems = this.callParent(arguments);

        var elementFieldSetIndex = -1;
        formItems.forEach(function(item, index) {
            if (item && item.name === 'elementFieldSet') {
                elementFieldSetIndex = index;

                return false;
            }
        });

        if (elementFieldSetIndex === -1) {
            return formItems;
        }

        formItems[elementFieldSetIndex].items.push({
            xtype: 'tinymce',
            fieldLabel: '{s name=document/detail/content_footer_label}Footer content{/s}',
            labelWidth: 100,
            name: 'PayPal_Unified_Instructions_Footer_Value',
            hidden: true,
            translatable: true
        }, {
            xtype: 'textarea',
            fieldLabel: '{s name=document/detail/style_footer_label}Footer style{/s}',
            labelWidth: 100,
            name: 'PayPal_Unified_Instructions_Footer_Style',
            hidden: true,
            translatable: true
        }, {
            xtype: 'tinymce',
            fieldLabel: '{s name=document/detail/content_content_info_label}Content info content{/s}',
            labelWidth: 100,
            name: 'PayPal_Unified_Instructions_Content_Value',
            hidden: true,
            translatable: true
        }, {
            xtype: 'textarea',
            fieldLabel: '{s name=document/detail/style_content_info_label}Content info style{/s}',
            labelWidth: 100,
            name: 'PayPal_Unified_Instructions_Content_Style',
            hidden: true,
            translatable: true
        });

        return formItems;
    }
});
// {/block}
