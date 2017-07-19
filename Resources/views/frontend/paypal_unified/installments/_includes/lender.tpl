{*
    This templates adds the lender (company name & info) to the view.

    [example]: {include file='frontend/payment_paypal_installments/upstream_presentment/_includes/lender.tpl' centerText=true multiline=true}

    Available parameters:
       [array] 'paypalInstallmentsCompanyInfo': An array that contains the company information
       [bool] 'centerText': A value indicating whether the address and company name should be displayed centered or not.
       [bool] 'multiLine': A value indicating whether the address should be displayed in multible lines or not.
*}

{namespace name='frontend/paypal_unified/installments/common'}

{block name='frontend_paypal_unified_installments_lender'}
    <div class="notification--lender{if $centerText == true} is--centered{/if}">
        {block name='frontend_paypal_unified_installments_lender_title'}
            <span class="is--bold">
                {s name='lender'}Lender:{/s}
            </span>
        {/block}

        {block name='frontend_paypal_unified_installments_lender_company'}
            <span> {$paypalInstallmentsCompanyInfo.name},</span>
        {/block}

        {block name='frontend_paypal_unified_installments_lender_address'}
            <address>
                {if $multiLine == true}
                    {$paypalInstallmentsCompanyInfo.address|nl2br}
                {else}
                    {* Replace line-breaks with commas for a single line presentation *}
                    {"\n"|str_replace:', ':$paypalInstallmentsCompanyInfo.address}
                {/if}
            </address>
        {/block}
    </div>
{/block}
