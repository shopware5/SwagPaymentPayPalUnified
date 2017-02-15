<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\SettingsService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\SettingsServiceInterface;

class PaymentMeans implements SubscriberInterface
{
    /**
     * @var int
     */
    private $paymentId;

    /**
     * @var SettingsService
     */
    private $settingsService;

    /**
     * @param Connection               $connection
     * @param SettingsServiceInterface $settingsService
     */
    public function __construct(Connection $connection, SettingsServiceInterface $settingsService)
    {
        $paymentMethodProvider = new PaymentMethodProvider(null);
        $this->paymentId = $paymentMethodProvider->getPaymentId($connection);
        $this->settingsService = $settingsService;
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
        $availableMethods = $args->getReturn();

        for ($i = 0; $i < count($availableMethods); ++$i) {
            $paymentMethod = $availableMethods[$i];

            if ((int) $paymentMethod['id'] === $this->paymentId && !$this->settingsService->hasSettings()) {
                //Force unset the payment method, because it's not available without any settings.
                unset($availableMethods[$i]);
                break;
            }
        }

        $args->setReturn($availableMethods);
    }
}
