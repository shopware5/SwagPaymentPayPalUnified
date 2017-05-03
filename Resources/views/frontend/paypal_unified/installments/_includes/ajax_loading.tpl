{block name="frontend_detail_paypal_unified_installments_ajax_loading"}
    <div class="paypal--installments-ajax-loading"
         data-paypalAjaxInstallments="true"
         data-paypalInstallmentsRequestUrl="{url controller=PaypalUnifiedInstallments module=widgets action=cheapestRate}"
         data-paypalInstallmentsProductPrice="{$paypalProductPrice}">

        <div class="paypal-unified-installments--loading-indicator"></div>

        {* The jQuery plugin will use this div to display the details *}
        <div class="paypal--installments"></div>
    </div>
{/block}