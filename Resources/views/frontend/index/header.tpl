{extends file='parent:frontend/index/header.tpl'}

{block name='frontend_index_header_javascript_modernizr_lib'}
    {block name='frontend_index_header_javascript_modernizr_lib_paypal_unified_checkout_button'}
        {if $paypalUnifiedEcCartActive || $paypalUnifiedEcDetailActive || $paypalUnifiedEcLoginActive || $paypalUnifiedUseInContext}
            {if $paypalUnifiedModeSandbox}
                <script src="https://www.paypalobjects.com/api/checkout.js"></script>
            {else}
                <script src="https://www.paypalobjects.com/api/checkout.min.js"></script>
            {/if}
        {/if}
    {/block}

    {$smarty.block.parent}
{/block}
