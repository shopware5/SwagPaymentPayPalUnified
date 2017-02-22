<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
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
