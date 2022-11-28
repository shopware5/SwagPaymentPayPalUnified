{extends file="parent:frontend/address/index.tpl"}

{block name="frontend_address_error_messages"}
    {$smarty.block.parent}

    {if $invalidBillingAddress}
        <div class="panel--body">
            {include file="frontend/_includes/messages.tpl" type="warning" content="{s name="error/invalidBillingAddress" namespace="frontend/paypal_unified/address/error-messsages"}PayPal was unable to validate the provided billing address. Please check your input data.{/s}"}
        </div>
    {elseif $invalidShippingAddress}
        <div class="panel--body">
            {include file="frontend/_includes/messages.tpl" type="warning" content="{s name="error/invalidShippingAddress" namespace="frontend/paypal_unified/address/error-messsages"}PayPal was unable to validate the provided shipping address. Please check your input data.{/s}"}
        </div>
    {/if}
{/block}
