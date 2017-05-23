{namespace name="documents/index"}

{block name="document_index_paypal_unified_installments"}
    <div id="amount">
        {block name="document_index_amount_table"}
            <table width="300px" cellpadding="0" cellspacing="0">
                <tbody>
                {* For installments, the tax label should be reworded from Plus to incl. *}
                {block name="document_index_amount_table_tax"}
                    {if $Document.netto == false}
                        {foreach from=$Order._tax key=key item=tax}
                            {block name="documents_paypal_unified_installments_tax_value"}
                                <tr>
                                    <td align="right">{s name="installments/tax"}{/s}</td>
                                    <td align="right">{$tax|currency}</td>
                                </tr>
                            {/block}
                        {/foreach}
                    {/if}
                {/block}

                {* For installments, the total sum label should be renamed to sum *}
                {block name="document_index_amount_table_sum"}
                    <tr>
                        <td align="right" class="head"><b>{s name="installments/sum"}Sum:{/s}</b></td>
                        <td align="right" class="head"><b>{$Order._amount|currency}</b></td>
                    </tr>
                {/block}

                {* Add credit information to the table *}
                {block name="document_index_amount_table_paypal_unified_installments_credit"}
                    {block name="document_index_amount_table_paypal_unified_installments_credit_fee_amount"}
                        <tr>
                            <td align="right">{s name="installments/financingCost"}Financing cost:{/s}</td>
                            <td align="right">{$paypalInstallmentsCredit.feeAmount|currency}</td>
                        </tr>
                    {/block}
                    {block name="document_index_amount_table_paypal_unified_installments_credit_cost"}
                        <tr>
                            <td align="right"><b>{s name="installments/totalCost"}Total amount (incl. financing cost):{/s}</b></td>
                            <td align="right"><b>{$paypalInstallmentsCredit.totalCost|currency}</b></td>
                        </tr>
                    {/block}
                {/block}
                </tbody>
            </table>
        {/block}
    </div>
{/block}
