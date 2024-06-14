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
use Exception;
use GuzzleHttp\Client;
use Shopware;
use SwagPaymentPayPalUnified\Components\TransactionReport\TransactionReport;
use SwagPaymentPayPalUnified\Setup\InstanceIdService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TransactionReportSubscriber implements SubscriberInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(Connection $connection, ContainerInterface $container)
    {
        $this->connection = $connection;
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return [
            'Shopware_CronJob_SwagPaymentPayPalUnifiedTransaktionReport' => 'onTransactionReport',
        ];
    }

    /**
     * @return void
     */
    public function onTransactionReport()
    {
        $shopwareVersion = '';
        if (\defined('\Shopware::VERSION')) {
            $shopwareVersion = Shopware::VERSION;
        } else {
            $shopwareVersion = $this->container->getParameter('shopware.release.version');
        }

        try {
            $instanceId = (new InstanceIdService($this->connection))->getInstanceId();
        } catch (Exception $exception) {
            $instanceId = '';
        }

        (new TransactionReport($this->connection))->report(
            $shopwareVersion,
            $instanceId,
            new Client(['base_uri' => TransactionReport::POST_URL])
        );
    }
}
