<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

abstract class AbstractFactoryCompilerPass implements CompilerPassInterface
{
    /**
     * @return string
     */
    abstract public function getFactoryId();

    /**
     * @return string
     */
    abstract public function getFactoryTag();

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->getFactoryId())) {
            return;
        }

        $definition = $container->getDefinition($this->getFactoryId());

        $taggedServices = $container->findTaggedServiceIds($this->getFactoryTag());

        foreach ($taggedServices as $id => $attributes) {
            $container->getDefinition($id)->setPublic(true);

            $definition->addMethodCall(
                'addHandler',
                [new Reference($id)]
            );
        }
    }
}
