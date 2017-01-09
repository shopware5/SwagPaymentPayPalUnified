{*
    This template adds the payment logo to the sidebar.

    Required parameters:
    [bool] "showPaypalLogo" - A value indicating whether or not the logo should be displayed
*}
{extends file="parent:frontend/index/sidebar.tpl"}
{block name="frontend_index_left_menu"}
    {$smarty.block.parent}
    {if $showPaypalLogo}
        {block name="frontend_index_sidebar_paypal_unified_logo"}
            <div class="panel is--rounded paypal--sidebar">
                {block name="frontend_index_sidebar_paypal_unified_logo_body"}
                    <div class="panel--body is--wide paypal--sidebar-inner">
                        {block name="frontend_index_sidebar_paypal_unified_logo_body_image"}
                            <a href="https://www.paypal.com/de/webapps/mpp/personal"
                               target="_blank" alt="{s name="logo/alt"}PayPal - Pay fast and secure.{/s}"
                               title="{s name="logo/alt"}PayPal - Pay fast and secure.{/s}">
                                <img class="logo--image" src="{link file='frontend/_public/src/img/sidebar-paypal-generic.png'}"/>
                            </a>
                        {/block}
                    </div>
                {/block}
            </div>
        {/block}
    {/if}
{/block}