<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber\Documents\Mock;

use Enlight_Class;
use Enlight_Hook_HookArgs;
use Shopware_Components_Document;
use Shopware_Models_Document_Order;
use stdClass;

class HookArgsWithWrongPaymentId extends Enlight_Hook_HookArgs
{
    /**
     * @param bool $isShopware55
     */
    public function __construct($isShopware55 = false)
    {
        if ($isShopware55) {
            parent::__construct(new stdClass(), '');
        }
    }

    /**
     * @return Shopware_Components_Document
     */
    public function getSubject()
    {
        $subject = Enlight_Class::Instance(Shopware_Components_Document::class);
        \assert($subject instanceof Shopware_Components_Document);

        $subject->_order = new Shopware_Models_Document_Order(15);

        return $subject;
    }
}
