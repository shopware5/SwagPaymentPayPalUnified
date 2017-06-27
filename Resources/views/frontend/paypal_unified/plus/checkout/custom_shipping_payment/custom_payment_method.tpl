{block name='frontend_checkout_payment_content_paypal_unified_custom_payment_item'}
    {$isPayPalUnified = $payment_mean.name eq 'SwagPaymentPayPalUnified'}
    <div class="payment--method block{if $payment_mean@last} method_last{else} method{/if}{if $payment_mean.id eq $sFormData.payment or (!$sFormData && !$smarty.foreach.register_payment_mean.index)} is--active{/if} {if $isPayPalUnified}is--plus-container{/if} unified--payment">

        {* Radio Button *}
        {block name='frontend_checkout_payment_fieldset_input_radio'}
            <div class="method--input">
                <input type="radio" name="payment" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}"{if $payment_mean.id eq $sFormData.payment or (!$sFormData && !$smarty.foreach.register_payment_mean.index)} checked="checked"{/if} />
            </div>
        {/block}

        {* Method Name *}
        {block name='frontend_checkout_payment_fieldset_input_label'}
            <div class="method--label is--first">
                <label class="method--name" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
            </div>
        {/block}

        {* Method Description *}
        {block name='frontend_checkout_payment_fieldset_description'}
            <div class="method--description is--last">
                {include file="string:{$payment_mean.additionaldescription}"}
            </div>
        {/block}

        {* Method Logo *}
        {block name='frontend_checkout_payment_fieldset_template'}
            <div class="payment--method-logo payment_logo_{$payment_mean.name}"></div>
            {if "frontend/plugins/payment/`$payment_mean.template`"|template_exists}
                <div class="method--bankdata{if $payment_mean.id != $form_data.payment} is--hidden{/if}">
                    {include file="frontend/plugins/payment/`$payment_mean.template`" form_data=$sFormData error_flags=$sErrorFlag payment_means=$sPayments}
                </div>
            {/if}
        {/block}
    </div>
{/block}
