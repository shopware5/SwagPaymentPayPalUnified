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
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;

class Order implements SubscriberInterface
{
    /**
     * @var PaymentMethodProvider
     */
    private $paymentMethodProvider;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    public function __construct(Connection $connection, DependencyProvider $dependencyProvider)
    {
        $this->paymentMethodProvider = new PaymentMethodProvider();
        $this->connection = $connection;
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Modules_Order_SendMail_Send' => 'onSendOrderMail',
        ];
    }

    public function onSendOrderMail(\Enlight_Event_EventArgs $eventArgs)
    {
        $unifiedPaymentId = $this->paymentMethodProvider->getPaymentId($this->connection);
        $variables = $eventArgs->get('variables');

        if ((int) $variables['additional']['payment']['id'] !== $unifiedPaymentId) {
            return null;
        }

        if (array_key_exists('paypalUnifiedSendMail', $variables) && $variables['paypalUnifiedSendMail']) {
            return null;
        }

        $session = $this->dependencyProvider->getSession();
        $session->offsetSet('paypalUnifiedFinishOrderSendMailVariables', $variables);

        return false;
    }
}
