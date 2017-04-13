{namespace name="frontend/paypal_unified/installments/modal/lender"}

<div class="">
            <span class="is--bold">
                {s name="lender"}Lender:{/s}
            </span>

    <span>{$companyInfo.name},</span>

    <address>
        {if $multiLine == true}
            {$companyInfo.address|nl2br}
        {else}
            {* Replace white spaces with commas *}
            {trim(preg_replace('/\s+/', ', ', $companyInfo.address))}
        {/if}
    </address>
</div>
