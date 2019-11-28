{namespace name='frontend/paypal_unified/index/sidebar'}
{if $paypalUnifiedShowLogo}
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

{if $paypalUnifiedShowInstallmentsLogo}
    {block name='frontend_index_sidebar_paypal_unified_installments_logo'}
        <div class="panel is--rounded paypal--sidebar">
            {block name='frontend_index_sidebar_paypal_unified_installments_logo_body'}
                <div class="panel--body is--wide paypal--sidebar-inner">
                    {block name='frontend_index_sidebar_paypal_unified_installments_logo_body_image'}
                        <a href="https://www.paypal.com/de/webapps/mpp/installments"
                           target="_blank"
                           title="{"{s name="logo/installments/title"}Installments Powered by PayPal{/s}"|escape}">
                            <img class="logo--image"
                                 src="{link file='frontend/_public/src/img/sidebar-paypal-installments.png'}"
                                 alt="{"{s name="logo/installments/title"}Installments Powered by PayPal{/s}"|escape}"/>
                        </a>
                    {/block}
                </div>
            {/block}
        </div>
    {/block}
{/if}

{if $paypalUnifiedAdvertiseReturns}
    {block name='frontend_index_sidebar_paypal_unified_advertise_returns'}
        <div class="panel is--rounded paypal--sidebar">
            {block name='frontend_index_sidebar_paypal_unified_advertise_returns_body'}
                <div class="panel--body is--wide paypal--sidebar-inner-returns">
                    {block name='frontend_index_sidebar_paypal_unified_advertise_returns_script'}
                        <script src="https://ad.doubleclick.net/ddm/adj/N426203.3552PAYPAL/B21012703.220637106;sz=180x180;u=PayerID;ord=[timestamp];dc_lat=;dc_rdid=;tag_for_child_directed_treatment=;tfua="></script>
                    {/block}
                </div>
            {/block}
        </div>
    {/block}
{/if}

{if $paypalUnifiedInstallmentsBanner}
    {block name='frontend_index_sidebar_paypal_unified_installments_banner'}
        <div class="panel is--rounded paypal--sidebar">
            {block name='frontend_index_sidebar_paypal_unified_installments_banner_body'}
                <div class="panel--body is--wide paypal--sidebar-inner">
                    <div data-paypalUnifiedInstallmentsBanner="true"
                         {block name='paypal_unified_installments_banner_data_attributes'}
                         data-ratio="1x1"
                         data-currency="{$paypalUnifiedInstallmentsBannerCurrency}"
                         {/block}>
                    </div>
                </div>
            {/block}
        </div>
    {/block}
{/if}
