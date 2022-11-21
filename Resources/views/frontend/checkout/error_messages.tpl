{extends file='parent:frontend/checkout/error_messages.tpl'}

{block name='frontend_checkout_error_messages_basket_error'}
    {$smarty.block.parent}

    {if $payerActionRequired}
        {include file="frontend/_includes/messages.tpl" type="warning" content="{s name="error/basket" namespace="frontend/paypal_unified/checkout/messages"}The basket has been changed during payment process. Please proceed the payment again.{/s}"}
    {/if}

    {if $payerInstrumentDeclined}
        {include file="frontend/_includes/messages.tpl" type="warning" content="{s name="error/instrumentDeclined" namespace="frontend/paypal_unified/checkout/messages"}The basket has been changed during payment process. Please proceed the payment again.{/s}"}
    {/if}

    {if $puiPhoneNumberWrong}
        {include file="frontend/_includes/messages.tpl" type="warning" content="{s name="error/pui/phoneNumberError" namespace="frontend/paypal_unified/checkout/messages"}Please enter a valide phone number.{/s}"}
    {/if}

    {if $puiBirthdateWrong}
        {include file="frontend/_includes/messages.tpl" type="warning" content="{s name="error/pui/birthdateError" namespace="frontend/paypal_unified/checkout/messages"}Please enter a valid date for your date of birth.{/s}"}
    {/if}
{/block}
