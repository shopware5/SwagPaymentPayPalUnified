{namespace name='frontend/paypal_unified/installments/return/confirm'}

{block name="frontend_paypal_unified_installments_financing_header"}
    <div class="tos--panel panel has--border">
        {block name="frontend_paypal_unified_installments_financing_header_body"}
            <div class="panel--body is--wide installments--header">
                {block name="frontend_paypal_unified_installments_financing_header_body_img"}
                    <div class="installments--header-img">
                        <img src="{link file="frontend/_public/src/img/installments_header.png" }"/>
                    </div>
                {/block}

                {block name="frontend_paypal_unified_installments_financing_header_body_msg"}
                    {include file="frontend/_includes/messages.tpl" type="warning" content="{s name="FinancingHeaderText"}{/s}"}
                {/block}
            </div>
        {/block}
    </div>
{/block}
