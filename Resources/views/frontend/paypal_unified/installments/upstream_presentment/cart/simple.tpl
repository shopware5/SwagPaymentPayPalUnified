{namespace name='frontend/paypal_unified/installments/upstream_presentment/simple'}

{block name='frontend_paypal_unified_installments_simple_cart'}
    <div class="paypal-unified-installments-notification--simple is--cart">
        {block name='frontend_paypal_unified_installments_simple_content'}
            <span class="is--block is--bold">
                {s name='cart/title'}You may also finance this cart{/s}
            </span>
            {include file='frontend/paypal_unified/installments/_includes/modal_link.tpl' displayStyle='simple'}
        {/block}
    </div>
{/block}
