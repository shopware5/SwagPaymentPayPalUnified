{namespace name="frontend/paypal_unified/detail/buy"}

{block name="frontend_detail_buy_paypal_unified_installments_simple"}
    <div class="paypal-unified-installments-notification--simple">
        {block name="frontend_detail_buy_paypal_unified_installments_simple_content"}
            <span class="is--block is--bold">
                {s name="simple/title"}You may also finance this article{/s}
            </span>
            {include file="frontend/paypal_unified/installments/_includes/modal_link.tpl"}
        {/block}
    </div>
{/block}
