{extends file='parent:frontend/index/header.tpl'}

{block name='frontend_index_header_javascript_modernizr_lib'}
    {block name='frontend_index_header_javascript_modernizr_lib_paypal_unified_checkout_button'}
        {if $paypalUnifiedEcCartActive || $paypalUnifiedEcDetailActive || $paypalUnifiedEcLoginActive || $paypalUnifiedUseInContext}
            <script src="https://www.paypalobjects.com/api/checkout.js"></script>
        {/if}
    {/block}

    {$smarty.block.parent}
{/block}
