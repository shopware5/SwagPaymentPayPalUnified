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
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class PaymentMeans implements SubscriberInterface
{
    /**
     * @var int
     */
    private $unifiedPaymentId;

    /**
     * @var SettingsServiceInterface
     */
    private $settingsService;

    /**
     * @var \Enlight_Components_Session_Namespace
     */
    private $session;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        Connection $connection,
        SettingsServiceInterface $settingsService,
        \Enlight_Components_Session_Namespace $session
    ) {
        $this->connection = $connection;
        $paymentMethodProvider = new PaymentMethodProvider();
        $this->unifiedPaymentId = $paymentMethodProvider->getPaymentId($connection);
        $this->settingsService = $settingsService;
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
