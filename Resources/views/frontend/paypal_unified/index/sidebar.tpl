{namespace name='frontend/paypal_unified/index/sidebar'}
{if $paypalUnifiedShowLogo  && $paypalIsNotAllowed === false}
    {block name='frontend_index_sidebar_paypal_unified_logo'}
        <div class="panel is--rounded paypal--sidebar">
            {block name='frontend_index_sidebar_paypal_unified_logo_body'}
                <div class="panel--body is--wide paypal--sidebar-inner">
                    {block name='frontend_index_sidebar_paypal_unified_logo_body_image'}
                        <a href="https://www.paypal.com/de/webapps/mpp/personal"
                           target="_blank"
                           title="{"{s name="logo/paypal/title"}PayPal - Pay fast and secure{/s}"|escape}">
                            <img class="logo--image"
                                 src="{link file='frontend/_public/src/img/sidebar-paypal-generic.png'}"
                                 alt="{"{s name="logo/paypal/title"}PayPal - Pay fast and secure{/s}"|escape}"/>
                        </a>
                    {/block}
                </div>
            {/block}
        </div>
    {/block}
{/if}

{if $paypalUnifiedInstallmentsBanner && $paypalIsNotAllowed === false && $paypalUnifiedInstallmentsBannerCurrency && $paypalUnifiedInstallmentsBannerBuyerCountry}
    {block name='frontend_index_sidebar_paypal_unified_installments_banner'}
        <div class="panel is--rounded paypal--sidebar">
            {block name='frontend_index_sidebar_paypal_unified_installments_banner_body'}
                <div class="panel--body is--wide paypal--sidebar-inner">
                    <div data-paypalUnifiedInstallmentsBanner="true"
                         {block name='paypal_unified_installments_banner_data_attributes'}
                         data-ratio="1x1"
                         data-currency="{$paypalUnifiedInstallmentsBannerCurrency}"
                         data-buyerCountry="{$paypalUnifiedInstallmentsBannerBuyerCountry}"
                         {/block}>
                    </div>
                </div>
            {/block}
        </div>
    {/block}
{/if}
