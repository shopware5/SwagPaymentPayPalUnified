{namespace name='frontend/paypal_unified/installments/modal/content'}

{block name='widgets_paypal_unified_installments_modal_content'}
    <div class="paypal-unified-installments--modal-content">
        {block name='widgets_paypal_unified_installments_modal_content_logo'}
            <img src="{link file='frontend/_public/src/img/sidebar-paypal-installments.png'}" class="modal-content--logo">
        {/block}
        {block name='widgets_paypal_unified_installments_modal_content_introduction_text'}
            <div class="modal-content--introduction-text">
                <p>
                    {s name='introductionText'}During the payment process, you will be able to select the financing option that best matches your needs. Depending on the selected duration and rate, the total price may change, making the total price displayed above outdated. You can find more detailed information under the link below or during the payment process.{/s}
                    <span class="is--block">
                        <a href="https://www.paypal.com/de/webapps/mpp/installments" target="_blank" title="{s name='linkTitle'}Installments Powered by PayPal - Homepage{/s}">{s name='linkText'}Further information{/s}</a>
                    </span>
                </p>
                <span class="is--block">
                    {s name='firstRate'}The first rate is due in 38 days{/s}
                </span>
            </div>
        {/block}

        {include file='widgets/paypal_unified_installments/modal_content/options_list.tpl'}

        {block name='widgets_paypal_unified_installments_modal_content_legal_message'}
            <span class="modal-content--legal-message is--block">
                *{s name='legalMessage'}Representative example pursuant to § 6a PAngV (German Price Indication Regulation):{/s}
            </span>
        {/block}


        {*{include file='widgets/paypal_unified_installments/modal_content/lender.tpl'}*}
    </div>
{/block}
