{block name="frontend_checkout_shipping_payment_paypal_unified_error"}
    <div class="paypal-unified--wrapper">
        {if $paypal_unified_error_code == 0}
            {* Order could not be processed *}
            {include file="frontend/_includes/messages.tpl" type="error" content="{s name="error/order"}Could not process this order at the moment.{/s}"}
        {elseif $paypal_unified_error_code == 1}
            {* Process canceled by the customer *}
            {include file="frontend/_includes/messages.tpl" type="info" content="{s name="error/canceled"}You have canceled the payment process before it was finished, therefore it is not possible to process your order.{/s}"}
        {elseif $paypal_unified_error_code == 2}
            {* Communication failure *}
            {include file="frontend/_includes/messages.tpl" type="error" content="{s name="error/communication"}An error occured during the payment provider communication, please try again later.{/s}"}
        {elseif $paypal_unified_error_code == 3}
            {* No order to process *}
            {include file="frontend/_includes/messages.tpl" type="error" content="{s name="error/noOrder"}There is no order that can be processed at this moment.{/s}"}
        {else}
            {* Unknown error *}
            {include file="frontend/_includes/messages.tpl" type="error" content="{s name="error/unkown"}An unknown error occurred while processing the payment.{/s}"}
        {/if}
    </div>
{/block}
