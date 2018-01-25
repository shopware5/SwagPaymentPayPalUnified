<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources\Authorization;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources\Order;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources\Refund;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources\RelatedResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources\ResourceType;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources\Sale;

class RelatedResources
{
    /**
     * @var RelatedResource[]
     */
    private $resources;

    /**
     * @return RelatedResource[]
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * @param RelatedResource[] $resources
     */
    public function setResources($resources)
    {
        $this->resources = $resources;
    }

    /**
     * @param array $data
     *
     * @return RelatedResources
     */
    public static function fromArray(array $data)
    {
        $result = new self();

        /** @var RelatedResource[] $relatedResources */
        $relatedResources = [];

        foreach ($data as $resource) {
            foreach ($resource as $key => $sale) {
                switch ($key) {
                    case ResourceType::SALE:
                        $relatedResources[] = Sale::fromArray($sale);
                        break;
                    case ResourceType::AUTHORIZATION:
                        $relatedResources[] = Authorization::fromArray($sale);
                        break;

                    case ResourceType::REFUND:
                        $relatedResources[] = Refund::fromArray($sale);
                        break;

                    case ResourceType::CAPTURE:
                        $relatedResources[] = Capture::fromArray($sale);
                        break;

                    case ResourceType::ORDER:
                        $relatedResources[] = Order::fromArray($sale);
                        break;
                }
            }
        }

        $result->setResources($relatedResources);

        return $result;
    }
}
