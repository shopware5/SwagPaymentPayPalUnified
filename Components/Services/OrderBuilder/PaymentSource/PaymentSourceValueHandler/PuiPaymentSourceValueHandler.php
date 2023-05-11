<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\OrderBuilder\PaymentSource\PaymentSourceValueHandler;

use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\PayPalOrderParameter;
use SwagPaymentPayPalUnified\Components\Services\Common\ReturnUrlHelper;
use SwagPaymentPayPalUnified\Components\Services\PhoneNumberService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Name;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\Payer\Phone\PhoneNumber;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\ExperienceContext;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PaymentSource\PayUponInvoice;

class PuiPaymentSourceValueHandler extends AbstractPaymentSourceValueHandler
{
    /**
     * @var PhoneNumberService
     */
    protected $phoneNumberService;

    public function __construct(
        SettingsServiceInterface $settingsService,
        ContextServiceInterface $contextService,
        ReturnUrlHelper $returnUrlHelper,
        PhoneNumberService $phoneNumberService
    ) {
        parent::__construct(
            $settingsService,
            $contextService,
            $returnUrlHelper
        );

        $this->phoneNumberService = $phoneNumberService;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($paymentType)
    {
        return $paymentType === PaymentType::PAYPAL_PAY_UPON_INVOICE_V2;
    }

    /**
     * {@inheritDoc}
     */
    public function createPaymentSourceValue(PayPalOrderParameter $orderParameter)
    {
        $payUponInvoice = new PayUponInvoice();
        $experienceContext = $this->createExperienceContext($orderParameter);
        $this->setCustomerInstructions($experienceContext);

        $customer = $orderParameter->getCustomer();

        $payUponInvoice->setName($this->createName($customer));
        $payUponInvoice->setEmail($customer['additional']['user']['email']);
        $payUponInvoice->setBirthDate($customer['additional']['user']['birthday']);
        $payUponInvoice->setPhone($this->createPaymentSourcePhoneNumber($customer));
        $payUponInvoice->setExperienceContext($experienceContext);

        return $payUponInvoice;
    }

    /**
     * @param array<string, mixed> $customer
     *
     * @return PhoneNumber
     */
    private function createPaymentSourcePhoneNumber(array $customer)
    {
        if (!isset($customer['billingaddress']['phone'])) {
            return new PhoneNumber();
        }

        return $this->phoneNumberService->buildPayPalPhoneNumber($customer['billingaddress']['phone']);
    }

    /**
     * @param array<string,mixed> $customer
     *
     * @return Name
     */
    private function createName(array $customer)
    {
        $name = new Name();
        $name->setGivenName($customer['additional']['user']['firstname']);
        $name->setSurname($customer['additional']['user']['lastname']);

        return $name;
    }

    /**
     * @return void
     */
    private function setCustomerInstructions(ExperienceContext $experienceContext)
    {
        if ($customerServiceInstructions = $this->settingsService->get(SettingsServiceInterface::SETTING_PUI_CUSTOMER_SERVICE_INSTRUCTIONS, SettingsTable::PAY_UPON_INVOICE)) {
            $experienceContext->setCustomerServiceInstructions([$customerServiceInstructions]);
        }
    }
}
