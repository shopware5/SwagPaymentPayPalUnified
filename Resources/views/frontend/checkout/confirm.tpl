{extends file="parent:frontend/checkout/confirm.tpl"}

{block name="frontend_index_header_javascript_jquery_lib"}
    {$smarty.block.parent}
    <script src="https://www.paypalobjects.com/webstatic/ppplus/ppplus.min.js"></script>
{/block}

{* PayPal Plus integration *}
{block name='frontend_checkout_confirm_premiums'}
    {if $usePayPalPlus && $sUserData.additional.payment.id == $paypalUnifiedPaymentId }
        {include file="frontend/paypal_unified/plus/checkout/payment_wall_premiums.tpl"}
    {/if}

    {$smarty.block.parent}
{/block}

{* PayPal Installments integration *}
{block name='frontend_checkout_confirm_confirm_table_actions'}
    {if $paypalInstallmentsMode === 'cheapest'}
        {include file='frontend/paypal_unified/installments/upstream_presentment.tpl'}
    {/if}

    {if $paypalInstallmentsMode === 'simple'}
        {include file="frontend/paypal_unified/installments/upstream_presentment/cart/simple.tpl"}
    {/if}

    {$smarty.block.parent}
{/block}