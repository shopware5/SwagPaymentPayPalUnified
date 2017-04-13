{namespace name="frontend/paypal_unified/installments/modal/options_list"}

    <div class="panel--group block-group">
            <span class="is--block is--strong is--align-center">
                {s name="list/orgPrice"}Ware worth:{/s} {$payPalUnifiedInstallmentsProductPrice|currency}
            </span>
        {foreach from=$payPalUnifiedInstallmentsFinancingOptions item=entry}
                <div class="installment--wrapper block">
                    <div class="panel has--border installment--item is--rounded">
                            <div class="panel--header secondary">
                                {* e.g "6 rates / 67,68€ per month" *}
                                {$entry.creditFinancing.term} {s name="list/rates"}rates{/s} {s name="list/rateDelimiter"}/{/s} {$entry.monthlyPayment.value|currency} {s name="list/textAfterPrice"}per month{/s}{if $entry.has_star === true}*{/if}
                            </div>
                        <div class="panel--body">
                                <table>
                                    <tbody>
                                        <tr>
                                            <td class="table--label">
                                                {$entry.creditFinancing.term} {s name="list/monthlyRate"}monthly rates in height of{/s}
                                            </td>
                                            <td class="table--value">
                                                {$entry.monthlyPayment.value|currency}
                                            </td>
                                        </tr>
                                        <tr class="table--row">
                                            <td class="table--label">
                                                {s name="list/apr"}effective apr{/s}
                                            </td>
                                            <td class="table--value">
                                                {$entry.creditFinancing.apr|number_format:2:',':'.'}%
                                            </td>
                                        </tr>
                                        <tr class="table--row">
                                            <td class="table--label">
                                                {s name="list/nominal"}nominal rate{/s}
                                            </td>
                                            <td class="table--value">
                                                {$entry.creditFinancing.nominalRate|number_format:2:',':'.'}%
                                            </td>
                                        </tr>
                                        <tr class="table--row">
                                            <td class="table--label">
                                                {s name="list/interest"}interest{/s}
                                            </td>
                                            <td class="table--value">
                                                {$entry.totalInterest.value|currency}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                        </div>

                            <span class="wrapper--total-cost is--bold is--block">
                                {s name="list/totalCost"}Total cost:{/s} {$entry.totalCost.value|currency}
                            </span>
                    </div>
                </div>
        {/foreach}
    </div>
<pre>
{$payPalUnifiedInstallmentsFinancingOptions|var_dump}
</pre>