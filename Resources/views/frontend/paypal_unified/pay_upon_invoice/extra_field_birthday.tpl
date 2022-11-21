{if $showPayUponInvoiceBirthdayField}
    <div class="extra-fields--birthday">
        {if {config name="birthdaySingleField"}}
            {block name='paypal_unified_pay_upon_invoice_confirm_birthday_single_field'}
                <input type="text"
                       data-datepicker="true"
                       data-minDate="{date('Y')-120}-1-1"
                       data-maxDate="today"
                       id="puiDateOfBirth"
                       name="puiDateOfBirth"
                       value="{$payUponInvoiceBirthdayFieldValue}"
                       class="pui-extra-field pui--birthday"
                       autocomplete="off"
                       required="required"
                       placeholder="{s namespace="frontend/paypal_unified/checkout/confirm" name="payUponInvoice/birthday"}Date of birth{/s}"
                       aria-label="{s namespace="frontend/paypal_unified/checkout/confirm" name="payUponInvoice/birthday"}Date of birth{/s}"/>
            {/block}
        {else}
            {block name='paypal_unified_pay_upon_invoice_confirm_birthday_field'}
                <label for="puiDateOfBirth">{s name='RegisterPlaceholderBirthday' namespace="frontend/register/personal_fieldset"}{/s}</label>
                <div class="pui-date-of-birth-container">
                    {block name="paypal_unified_pay_upon_invoice_confirm_birthday_field_day"}
                        <div class="field--select select-field"
                             aria-label="{s name='RegisterPlaceholderBirthday' namespace="frontend/register/personal_fieldset"}{/s}">
                            <select name="puiDateOfBirth[day]"
                                    aria-label="{s name='RegisterBirthdaySelectDay' namespace="frontend/register/personal_fieldset"}{/s}"
                                    value="{$payUponInvoiceBirthdayFieldValue[0]}"
                                    required="required">
                                <option
                                    value="">{s name='RegisterBirthdaySelectDay' namespace="frontend/register/personal_fieldset"}{/s}</option>
                                {for $day = 1 to 31}
                                    {if $day == $payUponInvoiceBirthdayFieldValue[0]}
                                        <option value="{$day}" selected="selected">{$day}</option>
                                    {/if}
                                    <option value="{$day}">{$day}</option>
                                {/for}
                            </select>
                        </div>
                    {/block}

                    {block name="paypal_unified_pay_upon_invoice_confirm_birthday_field_month"}
                        <div class="field--select select-field">
                            <select name="puiDateOfBirth[month]"
                                    aria-label="{s name='RegisterBirthdaySelectMonth' namespace="frontend/register/personal_fieldset"}{/s}"
                                    value="{$payUponInvoiceBirthdayFieldValue[1]}"
                                    required="required">
                                <option
                                    value="">{s name='RegisterBirthdaySelectMonth' namespace="frontend/register/personal_fieldset"}{/s}</option>
                                {for $month = 1 to 12}
                                    {if $month == $payUponInvoiceBirthdayFieldValue[1]}
                                        <option value="{$month}" selected="selected">{$month}</option>
                                    {/if}
                                    <option value="{$month}">{$month}</option>
                                {/for}
                            </select>
                        </div>
                    {/block}

                    {block name="paypal_unified_pay_upon_invoice_confirm_birthday_field_year"}
                        <div class="field--select select-field">
                            <select name="puiDateOfBirth[year]"
                                    aria-label="{s name='RegisterBirthdaySelectYear' namespace="frontend/register/personal_fieldset"}{/s}"
                                    value="{$payUponInvoiceBirthdayFieldValue[2]}"
                                    required="required">
                                <option
                                    value="">{s name='RegisterBirthdaySelectYear' namespace="frontend/register/personal_fieldset"}{/s}</option>
                                {for $year = date("Y") to date("Y")-120 step=-1}
                                    {if $year == $payUponInvoiceBirthdayFieldValue[2]}
                                        <option value="{$year}" selected="selected">{$year}</option>
                                    {/if}
                                    <option value="{$year}">{$year}</option>
                                {/for}
                            </select>
                        </div>
                    {/block}
                </div>
            {/block}
        {/if}
    </div>
{/if}

