<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\ItemList;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Common\Address;

class ShippingAddress extends Address
{
    /**
     * @var string
     */
    private $recipientName;

    /**
     * @return string
     */
    public function getRecipientName()
    {
        return $this->recipientName;
    }

    /**
     * @param string $recipientName
     */
    public function setRecipientName($recipientName)
    {
        $this->recipientName = $recipientName;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $result = parent::toArray();
        $result['recipient_name'] = $this->getRecipientName();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $data = null)
    {
        $result = new self();

        if ($data === null) {
            return $result;
        }

        $result->setCity($data['city']);
        $result->setCountryCode($data['country_code']);
        $result->setLine1($data['line1']);
        $result->setLine2($data['line2']);
        $result->setPostalCode($data['postal_code']);
        $result->setState($data['state']);
        $result->setPhone($data['phone']);
        $result->setRecipientName($data['recipient_name']);

        return $result;
    }
}
