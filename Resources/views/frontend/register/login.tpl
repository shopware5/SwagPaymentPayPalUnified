{extends file='parent:frontend/register/login.tpl'}

{block name='frontend_register_login_form'}
    {$smarty.block.parent}

    {block name='frontend_register_login_form_paypal_unified_ec_button'}
        {if $paypalUnifiedEcLoginActive && $paypalIsNotAllowed === false}
            {include file='frontend/paypal_unified/express_checkout/button.tpl' isLoginPage = true}
        {/if}
    {/block}
{/block}
