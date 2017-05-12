{*
    This templated includes a table that shows information about the cheapest rate.

    Required parameters:
    [array] "paypalInstallmentsOption" - The data that will be displayed.
    [float] "paypalInstallmentsProductPrice" - The price of the article.
*}

{namespace name="frontend/paypal_unified/installments/upstream_presentment/rate_table"}

{block name="frontend_paypal_unified_installments_rate_table"}
    <table class="notification--table">
        <tbody>
        {block name="frontend_paypal_unified_installments_rate_table_value"}
            <tr>
                <td class="table--label is--bold">{s name="table/value"}Value of goods{/s}</td>
                <td class="table--value is--align-right">{$paypalInstallmentsProductPrice|currency}</td>
            </tr>
        {/block}
        {block name="frontend_paypal_unified_installments_rate_table_apr"}
            <tr>
                <td class="table--label is--bold">{s name="table/apr"}effect. APR{/s}</td>
                <td class="table--value is--align-right">{$paypalInstallmentsOption.creditFinancing.apr|number_format:2:',':'.'}%</td>
            </tr>
        {/block}
        {block name="frontend_paypal_unified_installments_rate_table_nominal"}
            <tr>
                <td class="table--label is--bold">{s name="table/nominalRate"}Fix nominal rate{/s}</td>
                <td class="table--value is--align-right">{$paypalInstallmentsOption.creditFinancing.nominalRate|number_format:2:',':'.'}%</td>
            </tr>
        {/block}
        {block name="frontend_paypal_unified_installments_rate_table_total"}
            <tr>
                <td class="table--label is--bold">{s name="table/totalValue"}Total value to pay{/s}</td>
                <td class="table--value is--align-right">{$paypalInstallmentsOption.totalCost.value|currency}</td>
            </tr>
        {/block}
        {block name="frontend_paypal_unified_installments_rate_table_rate"}
            <tr>
                <td class="table--label is--bold">{$financingData.credit_financing.term} {s name="table/monthlyRate"}monthly rates in height of{/s}</td>
                <td class="table--value is--align-right">{$paypalInstallmentsOption.monthlyPayment.value|currency}</td>
            </tr>
        {/block}
        </tbody>
    </table>
{/block}