<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\ServiceDefinition;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\Tests\Functional\ContainerTrait;
use SwagPaymentPayPalUnified\Tests\Functional\ShopRegistrationTrait;
use Throwable;

class ServiceDefinitionTest extends TestCase
{
    use ContainerTrait;
    use ShopRegistrationTrait;

    /**
     * @return void
     */
    public function testServicesAreInstantiatable()
    {
        $container = $this->getContainer();
        $counter = 0;

        foreach ($container->getServiceIds() as $serviceId) {
            $isPayPalService = stripos($serviceId, 'paypal_unified');
            if ($isPayPalService === false) {
                continue;
            }

            ++$counter;

            try {
                $container->get($serviceId);
            } catch (Throwable $exception) {
                static::fail(\sprintf('Service with id: %s not found. Exception: %s', $serviceId, $exception->getMessage()));
            }
        }

        static::assertGreaterThan(1, $counter);
    }
}
