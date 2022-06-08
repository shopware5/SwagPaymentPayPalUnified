{if $payLaterMesssage && $payLaterMesssageClientId && $payLaterMessageCurrency}
    <script
        src="https://www.paypal.com/sdk/js?client-id={$payLaterMesssageClientId}&currency={$payLaterMessageCurrency}&components=messages"
        data-namespace="payPalPayLaterJS">
    </script>
{/if}
