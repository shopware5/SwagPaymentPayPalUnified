<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\OrderBuilder\OrderHandler;

use DateTime;
use Exception;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware_Components_Snippet_Manager as SnippetManager;
use SwagPaymentPayPalUnified\Components\Exception\BirthdateNotValidException;
use SwagPaymentPayPalUnified\Components\Exception\PhoneNumberCountryCodeNotValidException;
use SwagPaymentPayPalUnified\Components\Exception\PhoneNumberNationalNumberNotValidException;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\Common\CustomerHelper;
use SwagPaymentPayPalUnified\Components\Services\Common\PriceFormatter;
use SwagPaymentPayPalUnified\Components\Services\Common\ReturnUrlHelper;
use SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceFactory;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrder\AmountProvider;
use SwagPaymentPayPalUnified\Components\Services\PayPalOrder\ItemListProvider;
use SwagPaymentPayPalUnified\Components\Services\PhoneNumberService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\ProcessingInstruction;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone\PhoneNumber;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentIntentV2;
use UnexpectedValueException;

class PuiOrderHandler extends AbstractOrderHandler
{
    const GERMAN_PHONE_NUMBER_COUNTRY_CODE = '49';

    /**
     * @var PhoneNumberService
     */
    protected $phoneNumberService;

    /**
     * @var PaymentSourceFactory
     */
    protected $paymentSourceFactory;

    public function __construct(
        SettingsServiceInterface $settingsService,
        ItemListProvider $itemListProvider,
        AmountProvider $amountProvider,
        ReturnUrlHelper $returnUrlHelper,
        ContextServiceInterface $contextService,
        PriceFormatter $priceFormatter,
        CustomerHelper $customerHelper,
        PhoneNumberService $phoneNumberService,
        SnippetManager $snippetManager,
        PaymentSourceFactory $paymentSourceFactory
    ) {
        $this->phoneNumberService = $phoneNumberService;
        $this->paymentSourceFactory = $paymentSourceFactory;

        parent::__construct(
            $settingsService,
            $itemListProvider,
            $amountProvider,
            $returnUrlHelper,
            $contextService,
            $priceFormatter,
            $customerHelper,
            $snippetManager
        );
    }

    public function supports($paymentType)
    {
        return $paymentType === PaymentType::PAYPAL_PAY_UPON_INVOICE_V2;
    }

    public function createOrder(PayPalOrderParameter $orderParameter)
    {
        $order = new Order();

        $order->setProcessingInstruction(ProcessingInstruction::ORDER_COMPLETE_ON_PAYMENT_APPROVAL);
        $order->setIntent(PaymentIntentV2::CAPTURE);
        $order->setPurchaseUnits($this->createPurchaseUnits($orderParameter));
        $order->setPayer($this->createPayer($orderParameter));

        $paymentSource = $this->paymentSourceFactory->createPaymentSource($orderParameter);

        $payUponInvoice = $paymentSource->getPayUponInvoice();
        if (!$payUponInvoice instanceof PayUponInvoice) {
            throw new UnexpectedValueException(sprintf('PayUponInvoice expected. Got "%s"', \gettype($payUponInvoice)));
        }

        $payUponInvoice->setBillingAddress($order->getPayer()->getAddress());

        $order->setPaymentSource($paymentSource);

        return $this->validateOrder($order);
    }

    /**
     * @throws BirthdateNotValidException
     * @throws UnexpectedValueException
     * @throws PhoneNumberCountryCodeNotValidException
     * @throws PhoneNumberNationalNumberNotValidException
     *
     * @return Order
     */
    private function validateOrder(Order $order)
    {
        $paymentSource = $order->getPaymentSource();
        if (!$paymentSource instanceof PaymentSource) {
            throw new UnexpectedValueException(
                \sprintf('Expect instance of PaymentSource. Got %s', \gettype($paymentSource))
            );
        }

        $payUponInvoice = $paymentSource->getPayUponInvoice();
        if (!$payUponInvoice instanceof PayUponInvoice) {
            throw new UnexpectedValueException(
                \sprintf('Expect payment source to be PayUponInvoice. Got %s', \gettype($payUponInvoice))
            );
        }

        $phoneNumber = $payUponInvoice->getPhone();
        if (!$phoneNumber instanceof PhoneNumber) {
            throw new UnexpectedValueException(
                \sprintf('Expect phone number to be PhoneNumber. Got %s', \gettype($phoneNumber))
            );
        }

        $isBirthdateValid = $this->isBirthdayValid($payUponInvoice->getBirthDate());
        if (!$isBirthdateValid) {
            throw new BirthdateNotValidException($payUponInvoice->getBirthDate());
        }

        $phoneNumberCountryCodeIsValid = $phoneNumber->getCountryCode() === self::GERMAN_PHONE_NUMBER_COUNTRY_CODE;
        if (!$phoneNumberCountryCodeIsValid) {
            throw new PhoneNumberCountryCodeNotValidException($phoneNumber->getCountryCode());
        }

        $phoneNumberNationalNumberIsValid = $this->validatePhoneNumber($phoneNumber->getNationalNumber());
        if (!$phoneNumberNationalNumberIsValid) {
            throw new PhoneNumberNationalNumberNotValidException($phoneNumber->getNationalNumber());
        }

        return $order;
    }

    /**
     * @param string $birthdate
     *
     * @return bool
     */
    private function isBirthdayValid($birthdate)
    {
        if (!\is_string($birthdate)) {
            return false;
        }

        try {
            $birthdateDateTime = new DateTime($birthdate);
        } catch (Exception $exception) {
            return false;
        }

        return checkdate(
            (int) $birthdateDateTime->format('n'),
            (int) $birthdateDateTime->format('j'),
            (int) $birthdateDateTime->format('Y')
        );
    }

    /**
     * @param string $phoneNumber
     *
     * @return bool
     */
    private function validatePhoneNumber($phoneNumber)
    {
        $phoneNumber = $this->phoneNumberService->getValidPhoneNumberString($phoneNumber);

        if (!\is_string($phoneNumber)) {
            return false;
        }

        return true;
    }
}
