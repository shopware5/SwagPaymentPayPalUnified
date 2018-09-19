<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\Installments\ValidationService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsTable;

class PaymentMeans implements SubscriberInterface
{
    /**
     * @var int
     */
    private $unifiedPaymentId;

    /**
     * @var int
     */
    private $installmentsPaymentId;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var ValidationService
     */
    private $installmentsValidationService;

    /**
     * @var \Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection                            $connection
     * @param SettingsServiceInterface              $settingsService
     * @param ValidationService                     $installmentsValidationService
     * @param \Enlight_Components_Session_Namespace $session
     */
    public function __construct(
        Connection $connection,
        SettingsServiceInterface $settingsService,
        ValidationService $installmentsValidationService,
        \Enlight_Components_Session_Namespace $session
    ) {
        $this->connection = $connection;
        $paymentMethodProvider = new PaymentMethodProvider();
        $this->unifiedPaymentId = $paymentMethodProvider->getPaymentId($connection);
        $this->installmentsPaymentId = $paymentMethodProvider->getPaymentId($connection, PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME);
        $this->settingsService = $settingsService;
        $this->installmentsValidationService = $installmentsValidationService;
        $this->session = $session;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Admin_GetPaymentMeans_DataFilter' => 'onFilterPaymentMeans',
        ];
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     */
    public function onFilterPaymentMeans(\Enlight_Event_EventArgs $args)
    {
        /** @var array $availableMethods */
        $availableMethods = $args->getReturn();

        foreach ($availableMethods as $index => $paymentMethod) {
            if ((int) $paymentMethod['id'] === $this->unifiedPaymentId
                && (!$this->settingsService->hasSettings() || !$this->settingsService->get('active'))
            ) {
                //Force unset the payment method, because it's not available without any settings.
                unset($availableMethods[$index]);
            }

            if ((int) $paymentMethod['id'] === $this->installmentsPaymentId
                && (!$this->settingsService->hasSettings()
                    || !$this->settingsService->get('active')
                    || !$this->settingsService->get('active', SettingsTable::INSTALLMENTS))
            ) {
                unset($availableMethods[$index]);
            }

            if ((int) $paymentMethod['id'] === $this->installmentsPaymentId) {
                $productPrice = (float) $this->session->get('sBasketAmount');
                $customerData = $this->session->get('sOrderVariables')['sUserData'];

                if ($customerData === null) {
                    $customerData = $this->getCustomerData();
                }

                if (!$this->installmentsValidationService->validatePrice($productPrice)
                    || !$this->installmentsValidationService->validateCustomer($customerData)
                ) {
                    unset($availableMethods[$index]);
                }
            }
        }

        $args->setReturn($availableMethods);
    }

    /**
     * @return array
     */
    private function getCustomerData()
    {
        $customerData = [];
        $registerData = $this->session->get('sRegister');
        $customerData['billingaddress']['company'] = null;
        if (isset($registerData['billing']['company'])) {
            $customerData['billingaddress']['company'] = $registerData['billing']['company'];
        }

        $countryIso = $this->connection->createQueryBuilder()
            ->select('countryiso')
            ->from('s_core_countries')
            ->where('id = :countryId')
            ->setParameter('countryId', $registerData['billing']['country'])
            ->execute()
            ->fetchColumn();

        $customerData['additional']['country']['countryiso'] = $countryIso;

        return $customerData;
    }
}
