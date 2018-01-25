<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources;

class Refund extends RelatedResource
{
    /**
     * @var string;
     */
    private $parentResourceId;

    /**
     * @return string
     */
    public function getParentResourceId()
    {
        return $this->parentResourceId;
    }

    /**
     * @param string $parentResourceId
     */
    public function setParentResourceId($parentResourceId)
    {
        $this->parentResourceId = $parentResourceId;
    }

    /**
     * @param array $data
     *
     * @return Refund
     */
    public static function fromArray(array $data)
    {
        $result = new self();
        $result->prepare($result, $data, ResourceType::REFUND);

        $data['sale_id'] === null ? $result->setParentResourceId($data['capture_id']) : $result->setParentResourceId($data['sale_id']);

        return $result;
    }
}
