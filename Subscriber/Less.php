<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use Doctrine\Common\Collections\ArrayCollection;
use Enlight\Event\SubscriberInterface;
use Shopware\Components\Theme\LessDefinition;

class Less implements SubscriberInterface
{
    /**
     * @var string
     */
    private $pluginDirectory;

    /**
     * @param string $pluginDirectory
     */
    public function __construct($pluginDirectory)
    {
        $this->pluginDirectory = $pluginDirectory;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Theme_Compiler_Collect_Plugin_Less' => 'onCollectLessFiles',
        ];
    }

    /**
     * Handles the Theme_Compiler_Collect_Plugin_Less event.
     * Will return an ArrayCollection object of all less files that the plugin provides.
     *
     * @return ArrayCollection
     */
    public function onCollectLessFiles()
    {
        $less = new LessDefinition(
            //configuration
            [],
            //less files to compile
            [$this->pluginDirectory . '/Resources/views/frontend/_public/src/less/all.less'],
            //import directory
            $this->pluginDirectory
        );

        return new ArrayCollection([$less]);
    }
}
