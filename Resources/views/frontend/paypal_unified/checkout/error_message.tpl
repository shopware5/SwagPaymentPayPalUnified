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
        {elseif $paypalUnifiedErrorCode == 10}
            {* Instrument declined *}
            {* @see https://developer.paypal.com/docs/checkout/standard/customize/handle-funding-failures/#link-handlefundingfailures *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/instrumentDeclined"}The payment could not be processed, because either the billing address associated with the financial instrument could not be confirmed, the transaction exceeds the card limit, or the card issuer denied the transaction.{/s}"}
        {elseif $paypalUnifiedErrorCode == 11}
            {* TRANSACTION_REFUSED *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/transaction_refused"}The payment provider has rejected the payment. Please check your entries.{/s}"}
        {elseif $paypalUnifiedErrorCode == 12}
            {* PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED*}
            {* @see https://developer.paypal.com/limited-release/orders-apms/pay-upon-invoice/integrate-pui-partners/#link-testyourintegrationbysimulatingsuccessfulandfailurescenarios *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/paymentSourceInfoCannotBeVerified"}The combination of your name and address could not be validated. Please correct your data and try again.{/s} {s name="error/puiAdditional"}You can find further information in the <a href='https://www.ratepay.com/legal-payment-dataprivacy/' class='is--underline'>Ratepay Data Privacy Statement</a> or you can contact Ratepay using this <a href='https://www.ratepay.com/en/contact/' class='is--underline'>contact form</a>.{/s}"}
        {elseif $paypalUnifiedErrorCode == 13}
            {* PAYMENT_SOURCE_DECLINED_BY_PROCESSOR *}
            {* @see https://developer.paypal.com/limited-release/orders-apms/pay-upon-invoice/integrate-pui-partners/#link-testyourintegrationbysimulatingsuccessfulandfailurescenarios *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/paymentSourceDeclinedByProcessor"}It is not possible to use the selected payment method. This decision is based on automated data processing.{/s} {s name="error/puiAdditional"}You can find further information in the <a href='https://www.ratepay.com/legal-payment-dataprivacy/' class='is--underline'>Ratepay Data Privacy Statement</a> or you can contact Ratepay using this <a href='https://www.ratepay.com/en/contact/' class='is--underline'>contact form</a>.{/s}"}
        {elseif $paypalUnifiedErrorCode == 14 || $paypalUnifiedErrorCode == 15 || $paypalUnifiedErrorCode == 16 || $paypalUnifiedErrorCode == 17}
            {* Authorization is denied or 3D-Secure check failed *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/authorization/capture/denied"}{/s}"}
        {elseif $paypalUnifiedErrorCode === 1001}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/threeDSecure/1000" namespace="frontend/paypal_unified/checkout/messages"}{/s}"}
        {elseif $paypalUnifiedErrorCode === 1001}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/threeDSecure/1001" namespace="frontend/paypal_unified/checkout/messages"}{/s}"}
        {elseif $paypalUnifiedErrorCode === 1002}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/threeDSecure/1002" namespace="frontend/paypal_unified/checkout/messages"}{/s}"}
        {elseif $paypalUnifiedErrorCode === 1006}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/threeDSecure/1006" namespace="frontend/paypal_unified/checkout/messages"}{/s}"}
        {elseif $paypalUnifiedErrorCode === 1003
            || $paypalUnifiedErrorCode === 1004
            || $paypalUnifiedErrorCode === 1005
            || $paypalUnifiedErrorCode === 1007
            || $paypalUnifiedErrorCode === 1008}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/threeDSecure/1003" namespace="frontend/paypal_unified/checkout/messages"}{/s}"}
        {else}
            {* Unknown error *}
            {include file='frontend/_includes/messages.tpl' type='error' content="{s name="error/unknown"}An unknown error occurred while processing the payment.{/s}"}
        {/if}

        {if $paypalUnifiedErrorMessage}
            <div class="paypal-unified--error-message">
                <b>{s name="errorMessagePrefix"}Error message:{/s}</b> {$paypalUnifiedErrorMessage} [{$paypalUnifiedErrorName}]
            </div>
        {/if}
    </div>
{/block}
