{namespace name='frontend/paypal_unified/installments/common'}

{block name='widgets_paypal_unified_installments_modal_content'}
    <div class="paypal-unified-installments--modal-content">
        {* Offcanvas specific element, should be invisible in the modal box *}
        {block name='frontend_paypal_installments_up_modal_content_close'}
            <div class="buttons--off-canvas">
                <a class="close--off-canvas">
                    <i class="icon--arrow-left"></i>
                    {s name='offcanvas/close' namespace='frontend/payment_paypal_installments/upstream_presentment/modal'}Back{/s}
                </a>
            </div>
        {/block}

        {block name='widgets_paypal_unified_installments_modal_content_logo'}
            <img src="{link file='frontend/_public/src/img/sidebar-paypal-installments.png'}"
                 class="modal-content--logo"
                 alt="{"{s name="logo/installments/title" namespace="frontend/paypal_unified/index/sidebar"}Installments Powered by PayPal{/s}"|escape}">
        {/block}
        {include file='frontend/paypal_unified/installments/upstream_presentment/cart/complete.tpl'}
    </div>
{/block}
