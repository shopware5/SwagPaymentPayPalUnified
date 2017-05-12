{*
    This templates displays either a paypal-panel (if the APR equals 0%) which looks exactly like the panel in "simple.tpl" but with another
    headline or a table with further information about the cheapest rate such as "nominal rate", "total value to pay" and "company information".

    Required Parameters:
        [array] "paypalInstallmentsOption" - An array of the cheapest rate that will be displayed
        [array] "companyInfo" - An array of the company information (legal convention)
        [float] "price" - The price of the cart.
*}

{namespace name="frontend/paypal_unified/installments/upstream_presentment/cheapest"}
{block name="frontend_paypal_unified_installments_cheapest_detail"}
    {* If the APR is higher than 0% it is requiered to display further details such as fee or tax about the cheapest rate *}
    {$hasDetails = $paypalInstallmentsOption.creditFinancing.apr > 0}
    {if !$hasDetails}
        {block name="frontend_paypal_unified_installments_cheapest_detail_simple"}
            {* Use the simple message and styling, because that is the final price without any additions *}
            <div class="paypal-unified-installments-notification--simple">
                {block name="frontend_paypal_unified_installments_cheapest_simple_content"}
                    {block name="frontend_paypal_unified_installments_cheapest_simple_content_text"}
                        <span class="is--block is--bold">
                            {* example: You may also finance this article for 19,99 per month *}
                            {s name="textBeforePrice"}Financing from{/s} {$paypalInstallmentsOption.monthlyPayment.value|currency} {s name="textAfterPrice"}per month{/s}
                        </span>
                    {/block}
                    {* Our current display style is "cheapest", therefore it's required to fake the display style for this link to simple in order to apply the correct styling *}
                    {include file="frontend/paypal_unified/installments/_includes/modal_link.tpl" displayStyle="simple"}
                {/block}
            </div>
        {/block}
    {else}
        {block name="frontend_paypal_unified_installments_cheapest_detail_details"}
            <div class="paypal-unified-installments-notification--cheapest">
                <div class="panel has--border is--rounded">
                    {block name="frontend_paypal_unified_installments_cheapest_detail_details_title"}
                        <div class="panel--title is--underline">
                            {* example: Financing from 18.78 € in 24 monthly rates with Installments Powered by PayPal *}
                            {s name="textBeforePrice"}Financing from{/s} {$paypalInstallmentsOption.monthlyPayment.value|currency} {s name="textBeforeMonths"}in{/s} {$paypalInstallmentsOption.creditFinancing.term} {s name="textAfterMonths"}monthly rates with Installments Powered by PayPal{/s}
                        </div>
                    {/block}
                    {block name="frontend_paypal_unified_installments_cheapest_detail_details_content"}
                        <div class="panel--body is--wide">
                            {block name="frontend_paypal_unified_installments_cheapest_details_legal_message"}
                                <span class="notification--legal-message is--block">
                                    {s name="legalMessage" namespace="frontend/paypal_unified/installments/common"}
                                        Representative example pursuant to § 6a PAngV (German Price Indication Regulation):
                                    {/s}
                                </span>
                            {/block}

                            {include file="frontend/paypal_unified/installments/_includes/rate_table.tpl"}
                            {include file="frontend/paypal_unified/installments/_includes/lender.tpl" centerText=true}

                            {* Our current display style is "cheapest", therefore it's required to fake the display style for this link to simple in order to apply the correct styling *}
                            {include file="frontend/paypal_unified/installments/_includes/modal_link.tpl" displayStyle="cheapest"}
                        </div>
                    {/block}
                </div>
            </div>
        {/block}
    {/if}
{/block}