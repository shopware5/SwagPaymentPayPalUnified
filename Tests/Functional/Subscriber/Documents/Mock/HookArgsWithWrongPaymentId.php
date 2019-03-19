<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber\Documents\Mock;

class HookArgsWithWrongPaymentId extends \Enlight_Hook_HookArgs
{
    /**
     * @param bool $isShopware55
     */
    public function __construct($isShopware55 = false)
    {
        if ($isShopware55) {
            parent::__construct(new \stdClass(), '');
        }
    }

    /**
     * @return \Enlight_Class
     */
    public function getSubject()
    {
        $subject = \Enlight_Class::Instance(\Shopware_Components_Document::class);

        $subject->_order = new \Shopware_Models_Document_Order(15);

        return $subject;
    }
}
