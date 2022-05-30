{extends file='parent:frontend/index/index.tpl'}

{block name='frontend_index_header_javascript'}
    {$smarty.block.parent}

    {block name='frontend_index_header_javascript_installments_banner'}
        {include file='frontend/paypal_unified/installments/banner/index.tpl'}
    {/block}

    {block name='frontend_index_header_javascript_paylater_description_message'}
        {include file='frontend/paypal_unified/pay_later/message.tpl'}
    {/block}
{/block}
