{extends file='parent:frontend/index/index.tpl'}

{block name='frontend_index_header_javascript'}
    {$smarty.block.parent}

    {block name='frontend_index_header_javascript_installments_banner'}
        {include file='frontend/paypal_unified/installments/banner/index.tpl'}
    {/block}
{/block}
