{extends file="parent:frontend/checkout/finish.tpl"}

{block name='frontend_checkout_finish_teaser'}
    {$smarty.block.parent}
    {include file='frontend/paypal_unified/checkout/finish.tpl'}
{/block}