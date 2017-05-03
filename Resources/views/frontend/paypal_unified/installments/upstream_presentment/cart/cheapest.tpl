{*
    This templates displays either a paypal-panel (if the APR equals 0%) which looks exactly like the panel in "simple.tpl" but with another
    headline or a table with further information about the cheapest rate such as "nominal rate", "total value to pay" and "company information".

    Depending on the viewport width, the template will change itself from "side by side view" (left: title + link, right: table + lender) to "floating down".
    This behaviour is one of the reasons why detail and cart use seperate template files. Another one is that the snippets change.

    Required Parameters:
        [array] "paypalInstallmentsOption" - An array of the cheapest rate that will be displayed
        [array] "companyInfo" - An array of the company information
        [float] "price" - The price of the cart.
*}

{namespace name="frontend/paypal_unified/installments/upstream_presentment/cheapest"}

{block name="frontend_paypal_unified_installments_cheapest_cart"}
    {* If the APR is higher than 0% it is requiered to display further details such as fee or tax about the cheapest rate *}
    {$hasDetails=$paypalInstallmentsOption.creditFinancing.apr > 0}

    {if !$hasDetails}
        {block name="frontend_paypal_unified_installments_cheapest_cart_simple"}
            {* Use the simple message and styling, because that is the final price without any additions *}
            <div class="paypal-unified-installments-notification--simple is--cart">
                {block name="frontend_paypal_installments_up_cart_cheapest_simple_content"}
                    <span class="is--block is--bold">
                        {* example: Financing from 19,99€ per month *}
                        {s name="textBeforePrice"}Financing from{/s} {$paypalInstallmentsOption.monthlyPayment.value|currency} {s name="textAfterPrice"}per month{/s}
                    </span>

                    {* Our current display style is "cheapest", therefore it's required to fake the display style for this link to simple in order to apply the correct styling *}
                    {include file="frontend/paypal_unified/installments/_includes/modal_link.tpl" displayStyle="simple"}
                {/block}
            </div>
        {/block}
    {else}
        {block name="frontend_paypal_unified_installments_cheapest_cart_details"}
            <div class="paypal-unified-installments-notification--cheapest is--cart">
                <div class="panel--left">
                    {block name="frontend_paypal_unified_installments_cheapest_cart_details_title"}
                        <span class="cheapest--title is--block is--bold">
                            {s name="textBeforePrice"}Financing from{/s} {$paypalInstallmentsOption.monthlyPayment.value|currency} {s name="textBeforeMonths"}in{/s} {$paypalInstallmentsOption.creditFinancing.term} {s name="textAfterMonths"}monthly rates with Installments Powered by PayPal{/s}
                        </span>
                    {/block}
                    {block name="frontend_paypal_unified_installments_cheapest_cart_details_additional"}
                        <p>
                            {s name="cart/additionalText"}We allow you to finance your purchase with the help of Installments Powered by PayPal. Fast, completely online, subject to credit check.{/s}
                        </p>
                    {/block}

                    {* Our current display style is "cheapest", therefore it's required to fake the display style for this link to simple in order to apply the correct styling *}
                    {include file="frontend/paypal_unified/installments/_includes/modal_link.tpl" displayStyle="cheapest"}
                </div>
                <div class="panel--right">
                    {block name="frontend_paypal_unified_installments_cheapest_cart_details_content"}
                        <span class="notification--legal-message is--block">
                            {s name="legalMessage" namespace="frontend/paypal_unified/installments/common"}
                                Representative example pursuant to § 6a PAngV (German Price Indication Regulation):
                            {/s}
                        </span>
                    {/block}

                    {include file="frontend/paypal_unified/installments/_includes/rate_table.tpl"}
                    {include file="frontend/paypal_unified/installments/_includes/lender.tpl" centerText=true}
                </div>
            </div>
        {/block}
    {/if}
{/block}