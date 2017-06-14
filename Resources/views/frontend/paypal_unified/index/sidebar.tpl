{namespace name="frontend/paypal_unified/index/sidebar"}
{if $paypalUnifiedShowLogo}
    {block name="frontend_index_sidebar_paypal_unified_logo"}
        <div class="panel is--rounded paypal--sidebar">
            {block name="frontend_index_sidebar_paypal_unified_logo_body"}
                <div class="panel--body is--wide paypal--sidebar-inner">
                    {block name="frontend_index_sidebar_paypal_unified_logo_body_image"}
                        <a href="https://www.paypal.com/de/webapps/mpp/personal"
                           target="_blank"
                           title="{s name="logo/paypal/title"}PayPal - Pay fast and secure{/s}">
                            <img class="logo--image" src="{link file='frontend/_public/src/img/sidebar-paypal-generic.png'}"/>
                        </a>
                    {/block}
                </div>
            {/block}
        </div>
    {/block}
{/if}

{if $paypalUnifiedShowInstallmentsLogo}
    {block name="frontend_index_sidebar_paypal_unified_installments_logo"}
        <div class="panel is--rounded paypal--sidebar">
            {block name="frontend_index_sidebar_paypal_unified_installments_logo_body"}
                <div class="panel--body is--wide paypal--sidebar-inner">
                    {block name="frontend_index_sidebar_paypal_unified_installments_logo_body_image"}
                        <a href="https://www.paypal.com/de/webapps/mpp/installments"
                           target="_blank"
                           title="{s name="logo/installments/title"}Installments Powered by PayPal{/s}">
                            <img class="logo--image" src="{link file='frontend/_public/src/img/sidebar-paypal-installments.png'}"/>
                        </a>
                    {/block}
                </div>
            {/block}
        </div>
    {/block}
{/if}
