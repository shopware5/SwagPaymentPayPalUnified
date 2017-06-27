{namespace name='frontend/paypal_unified/checkout/error_message'}
{block name='frontend_checkout_shipping_payment_paypal_unified_error'}
    <div class="paypal-unified--error">
        {if $paypalUnifiedErrorCode == 0}
            {* Order could not be processed *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/order"}Could not process this order at the moment.{/s}"}
        {elseif $paypalUnifiedErrorCode == 1}
            {* Process canceled by the customer *}
            {include file='frontend/_includes/messages.tpl' type='info' content="{s name="error/canceled"}You have canceled the payment process before it was finished, therefore it is not possible to process your order.{/s}"}
        {elseif $paypalUnifiedErrorCode == 2}
            {* Communication failure *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/communication"}An error occured during the payment provider communication, please try again later.{/s}"}
        {elseif $paypalUnifiedErrorCode == 3}
            {* No order to process *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/noOrder"}There is no order that can be processed at this moment.{/s}"}
        {elseif $paypalUnifiedErrorCode == 5}
            {* Installments error *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/installments"}Could not process credit order. The provided order could not be associated by the payment provider.{/s}"}
        {elseif $paypalUnifiedErrorCode == 6}
            {* Basket validation error *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/basket"}The basket has been changed during payment process. Please proceed the payment again.{/s}"}
        {else}
            {* Unknown error *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/unkown"}An unknown error occurred while processing the payment.{/s}"}
        {/if}
    </div>
{/block}
