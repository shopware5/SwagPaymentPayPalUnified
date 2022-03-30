<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional;

use PayPalUnifiedTestKernel;
use Shopware\Components\DependencyInjection\Container;
use UnexpectedValueException;

trait ContainerTrait
{
    /**
     * @return Container
     */
    public function getContainer()
    {
        $container = PayPalUnifiedTestKernel::getKernel()->getContainer();

        if (!$container instanceof Container) {
            throw new UnexpectedValueException('Container not found');
        }

        return $container;
    }
}
