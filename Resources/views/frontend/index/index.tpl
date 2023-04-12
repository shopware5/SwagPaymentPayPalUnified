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

{block name='frontend_index_after_body'}
    {block name='frontend_index_after_body_paypal_unified_meta_data_container'}
        <div data-paypalUnifiedMetaDataContainer="true"
             data-paypalUnifiedRestoreOrderNumberUrl="{url module=widgets controller=PaypalUnifiedOrderNumber action=restoreOrderNumber forceSecure}"
                {block name='frontend_index_after_body_paypal_unified_meta_data_container_add'}{/block}
             class="is--hidden">
        </div>
    {/block}

    {$smarty.block.parent}
{/block}
