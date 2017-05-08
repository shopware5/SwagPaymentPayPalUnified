{*
    This templates includes a list of all available rates that can be chosen in the next step.

    Required parameters:
        [array] "paypalInstallmentsOptions" - The data that will be displayed in the list.
        [array] "companyInfo" - The company information as array.

    For this template the variable "price" is not required, because no link that would show the modal/offcanvas menu is being generated.
*}
{namespace name="frontend/paypal_unified/installments/upstream_presentment/complete"}

{block name="frontend_paypal_unified_installments_complete_cart"}
    <div class="paypal-unified-installments-notification--full">
        {block name="frontend_paypal_unified_installments_complete_cart_title"}
            <div class="panel--header secondary">
                {s name="title"}Notes about Installments Powered by PayPal{/s}
            </div>
        {/block}

        {block name="frontend_paypal_unified_installments_complete_cart_text"}
            <div class="notification--text">
                <p>
                    {s name="cart/additionalText"}During the payment process, you will be able to select the financing option that best matches your needs. Depending on the selected duration and rate, the total price may change, making the total price displayed above outdated. You can find more detailed information under the link below or during the payment process.{/s}
                </p>
                {block name="frontend_paypal_unified_installments_complete_cart_text_first_rate"}
                    <span class="is--block">
                        {s name="cart/firstRate"}The first rate is due in 38 days{/s}
                    </span>
                {/block}
            </div>
        {/block}

        {include file="frontend/paypal_unified/installments/_includes/options_list.tpl"}
        {include file="frontend/paypal_unified/installments/_includes/lender.tpl" centerText=true}

        {block name="frontend_paypal_unified_installments_cheapest_cart_details_content"}
            <span class="notification--legal-message is--block">
                {s name="legalMessage"}*Representative example pursuant to § 6a PAngV (German Price Indication Regulation){/s}
            </span>
        {/block}
    </div>
{/block}