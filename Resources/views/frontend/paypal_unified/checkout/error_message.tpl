{namespace name='frontend/paypal_unified/checkout/messages'}
{block name='frontend_checkout_shipping_payment_paypal_unified_error'}
    <div class="paypal-unified--error">
        {if $paypalUnifiedErrorCode == 1}
            {* Process canceled by the customer *}
            {include file='frontend/_includes/messages.tpl' type='info' content="{s name="error/canceled"}You have canceled the payment process before it was finished, therefore it is not possible to process your order.{/s}"}
        {elseif $paypalUnifiedErrorCode == 2}
            {* Communication failure *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/communication"}An error occured during the payment provider communication, please try again later.{/s}"}
        {elseif $paypalUnifiedErrorCode == 3}
            {* No order to process *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/noOrder"}There is no order that can be processed at this moment.{/s}"}
        {elseif $paypalUnifiedErrorCode == 5}
            {* Order could not be processed *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/communicationFinish"}An error occured while trying to contact the payment provider, please contact the shop manager.{/s}"}
        {elseif $paypalUnifiedErrorCode == 6}
            {* Basket validation error *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/basket"}The basket has been changed during payment process. Please proceed the payment again.{/s}"}
        {elseif $paypalUnifiedErrorCode == 7}
            {* Address validation error *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/address"}PayPal was unable to validate the provided address. Please check your input data.{/s}"}
        {elseif $paypalUnifiedErrorCode == 8}
            {* No dispatch for order error *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/dispatch"}The payment could not be processed because there was no shipping method for your order.{/s}"}
        {else}
            {* Unknown error *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/unkown"}An unknown error occurred while processing the payment.{/s}"}
        {/if}

		{if $paypalUnifiedErrorMessage}
            <div class="paypal-unified--error-message">
                <b>{s name="errorMessagePrefix"}Error message:{/s}</b> {$paypalUnifiedErrorMessage} [{$paypalUnifiedErrorName}]
            </div>
		{/if}
    </div>
{/block}
