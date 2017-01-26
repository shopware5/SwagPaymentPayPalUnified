{extends file="parent:frontend/checkout/shipping_payment_core.tpl"}

{block name='frontend_checkout_shipping_payment_core_buttons'}
    {if $error_code}
        <div class="paypal-unified--wrapper">
            {if $error_code == 0}
                {* Payment declined *}
                {include file="frontend/_includes/messages.tpl" type="error" content="{s name="error/payment_declined"}The payment provider did not accept this payment.{/s}"}
            {elseif $error_code == 1}
                {* Order could not be processed *}
                {include file="frontend/_includes/messages.tpl" type="error" content="{s name="error/order"}Could not process this order at the moment.{/s}"}
            {elseif $error_code == 2}
                {* No order to process *}
                {include file="frontend/_includes/messages.tpl" type="error" content="{s name="error/noOrder"}There is no order that can be processed at this moment.{/s}"}
            {elseif $error_code == 3}
                {* Process canceled by the customer *}
                {include file="frontend/_includes/messages.tpl" type="info" content="{s name="error/canceled"}You have canceled the payment process before it was finished, therefore it is not possible to process your order.{/s}"}
            {elseif $error_code == 4}
                {* Communication failure *}
                {include file="frontend/_includes/messages.tpl" type="error" content="{s name="error/communication"}An error occured during the payment provider communication, please try again later.{/s}"}
            {elseif $error_code == 5}
                {* Communication failure *}
                {include file="frontend/_includes/messages.tpl" type="error" content="{s name="error/systemOrder"}The order was not found in the system. Please contact us to verify the order.{/s}"}
            {/if}
        </div>
    {/if}

    {$smarty.block.parent}
{/block}