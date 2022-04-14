{extends file="parent:frontend/account/payment.tpl"}

{block name="frontend_account_payment_form"}
    {if $paypalUnifiedUseSepa}
        {$smarty.block.parent}
        <div data-swagPayPalUnifiedSepaEligibility="true"
             data-clientId="{$paypalUnifiedSpbClientId}"
             data-intent="{$paypalUnifiedSpbIntent}"
             data-locale="{$paypalUnifiedSpbButtonLocale}"
             data-currency="{$paypalUnifiedSpbCurrency}"
             data-sepaPaymentMethodId="{$paypalUnifiedSepaPaymentId}">
        </div>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}
