{extends file='parent:frontend/index/header.tpl'}

{block name='frontend_index_header_javascript_modernizr_lib'}
    {if $paypalExpressCheckoutActive}
        <script src="https://www.paypalobjects.com/api/checkout.js"></script>
    {/if}

    {$smarty.block.parent}
{/block}
