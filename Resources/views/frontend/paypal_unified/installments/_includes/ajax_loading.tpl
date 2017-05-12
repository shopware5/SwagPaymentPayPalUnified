{block name="frontend_detail_paypal_unified_installments_ajax_loading"}
    <div class="paypal--installments-ajax-loading"
         data-paypalAjaxInstallments="true"
         data-paypalInstallmentsPageType="{$paypalInstallmentsPageType}"
         data-paypalInstallmentsRequestUrl="{url controller=PaypalUnifiedInstallments module=widgets action=cheapestRate forceSecure}"
         data-paypalInstallmentsProductPrice="{$paypalInstallmentsProductPrice}"
         data-paypalInstallmentsRequestCompleteList="{$paypalInstallmentsRequestCompleteList}"
         data-paypalInstallmentsRequestCompleteListUrl="{url controller=PaypalUnifiedInstallments module=widgets action=list forceSecure}"> {* Only if paypal installments is selected and we are on the confirm page *}

        <div class="paypal-unified-installments--loading-indicator"></div>

        {* The jQuery plugin will use this div to display the details *}
        <div class="paypal--installments"></div>
    </div>
{/block}