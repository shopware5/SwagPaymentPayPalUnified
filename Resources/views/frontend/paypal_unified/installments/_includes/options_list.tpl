{*
    This template adds a list of available financing options to the current template.

    Required parameters:
    [array] "paypalInstallmentsOptions" - The data that will be displayed in the list.
    [float] "price" - The price of the article or basket.
*}

{namespace name="frontend/paypal_unified/installments/upstream_presentment/complete"}

{block name="frontend_paypal_unified_installments_complete_cart_option_list"}
    <div class="panel--group block-group">
        {block name="frontend_paypal_unified_installments_complete_cart_option_list_org_price"}
            <span class="is--block is--strong is--align-center">
                {s name="list/orgPrice"}Net loan value:{/s} {$paypalInstallmentsProductPrice|currency}
            </span>
        {/block}
        {foreach from=$paypalInstallmentsOptions item=entry}
            {block name="frontend_paypal_unified_installments_complete_cart_list_entry"}
                <div class="installment--wrapper block">
                    <div class="panel has--border installment--item is--rounded">
                        {block name="frontend_paypal_unified_installments_complete_cart_list_entry_title"}
                            <div class="panel--header secondary">
                                {* e.g "6 rates / 67,68€ per month" *}
                                {$entry.creditFinancing.term} {s name="list/rates"}rates{/s} {s name="list/rateDelimiter"}/{/s} {$entry.monthlyPayment.value|currency} {s name="list/textAfterPrice"}per month{/s}{if $entry.hasStar === true}*{/if}
                            </div>
                        {/block}
                        <div class="panel--body">
                            {block name="frontend_paypal_unified_installments_complete_cart_list_entry_table"}
                                <table>
                                    <tbody>
                                    {block name="frontend_paypal_unified_installments_complete_cart_list_entry_table_monthly"}
                                        <tr>
                                            <td class="table--label">
                                                {$entry.creditFinancing.term} {s name="list/monthlyRate"}monthly rates in height of{/s}
                                            </td>
                                            <td class="table--value">
                                                {$entry.monthlyPayment.value|currency}
                                            </td>
                                        </tr>
                                    {/block}
                                    {block name="frontend_paypal_unified_installments_complete_cart_list_entry_table_apr"}
                                        <tr class="table--row">
                                            <td class="table--label">
                                                {s name="list/apr"}effective apr{/s}
                                            </td>
                                            <td class="table--value">
                                                {$entry.creditFinancing.apr|number_format:2:',':'.'}%
                                            </td>
                                        </tr>
                                    {/block}
                                    {block name="frontend_paypal_unified_installments_complete_cart_list_entry_table_nominal"}
                                        <tr class="table--row">
                                            <td class="table--label">
                                                {s name="list/nominal"}nominal rate{/s}
                                            </td>
                                            <td class="table--value">
                                                {$entry.creditFinancing.nominalRate|number_format:2:',':'.'}%
                                            </td>
                                        </tr>
                                    {/block}
                                    {block name="frontend_paypal_unified_installments_complete_cart_list_entry_table_interest"}
                                        <tr class="table--row">
                                            <td class="table--label">
                                                {s name="list/interest"}interest{/s}
                                            </td>
                                            <td class="table--value">
                                                {$entry.totalInterest.value|currency}
                                            </td>
                                        </tr>
                                    {/block}
                                    </tbody>
                                </table>
                            {/block}
                        </div>

                        {block name="frontend_paypal_unified_installments_complete_cart_list_entry_total_cost"}
                            <span class="wrapper--total-cost is--bold is--block">
                                {s name="list/totalCost"}Total cost:{/s} {$entry.totalCost.value|currency}
                            </span>
                        {/block}
                    </div>
                </div>
            {/block}
        {/foreach}
    </div>
{/block}