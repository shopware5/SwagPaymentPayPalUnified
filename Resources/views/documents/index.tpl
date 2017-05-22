{extends file="parent:documents/index.tpl"}

{* PayPal Installments integration *}
{block name="document_index_amount"}
    {if !$paypalInstallmentsCredit}
        {$smarty.block.parent}
    {else}
        {include file="documents/paypal_unified/installments/credit_info.tpl"}
    {/if}
{/block}
