{namespace name='frontend/paypal_unified/installments/upstream_presentment/simple'}

{block name='frontend_paypal_unified_installments_simple_detail'}
    <div class="paypal-unified-installments-notification--simple">
        {block name='frontend_paypal_unified_installments_simple_content'}
            <span class="is--block is--bold">
                {s name='detail/title'}You may also finance this product{/s}
            </span>
            {include file='frontend/paypal_unified/installments/_includes/modal_link.tpl' displayStyle='simple'}
        {/block}
    </div>
{/block}
