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
                <div class="panel--body is--wide paypal--sidebar-advertise-returns">
                    {block name='frontend_index_sidebar_paypal_unified_advertise_returns_body'}
                        <a href="http://adfarm.mediaplex.com/ad/nc/27730-212148-12439-92?mpt=[CACHEBUSTER]"
                           target="_blank"
                           title="{"{s name="logo/advertise_returns/title"}Free returns via PayPal{/s}"|escape}">
                            <img src="http://adfarm.mediaplex.com/ad/nb/27730-212148-12439-92?mpt=[CACHEBUSTER]"
                                 alt="{"{s name="logo/advertise_returns/title"}Free returns via PayPal{/s}"|escape}"
                                 border="0">
                        </a>
                    {/block}
                </div>
            {/block}
        </div>
    {/block}
{/if}
