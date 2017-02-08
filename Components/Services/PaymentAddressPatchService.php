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

namespace SwagPaymentPayPalUnified\Components\Services;

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Country\Country;
use Shopware\Models\Country\State;
use SwagPaymentPayPalUnified\SDK\Components\Patches\PaymentAddressPatch;

class PaymentAddressPatchService
{
    /** @var ModelManager $modelManager */
    private $modelManager;

    /**
     * @param ModelManager $modelManager
     */
    public function __construct(ModelManager $modelManager)
    {
        $this->modelManager = $modelManager;
    }

    /**
     * @param array $addressData
     * @return PaymentAddressPatch
     * @throws \Exception
     */
    public function getPatch(array $addressData)
    {
        $country = $this->getBillingCountry($addressData['countryId']);

        if ($country === null) {
            throw new \Exception('The provided address data does not contain a valid country');
        }

        $addressData['countryiso'] = $country->getIso();

        //Since it is not required to provide a state in shopware,
        //this check indicates if the patch should add it to the call.
        $stateId = $addressData['stateId'];
        if ($stateId !== null) {
            $state = $this->getBillingState($stateId);
            $addressData['stateiso'] = $state->getShortCode();
        }

        return new PaymentAddressPatch($addressData);
    }

    /**
     * @param int $countryId
     * @return null|Country
     */
    private function getBillingCountry($countryId)
    {
        return $this->modelManager->getRepository(Country::class)->find($countryId);
    }

    /**
     * @param int $stateId
     * @return null|State
     */
    private function getBillingState($stateId)
    {
        return $this->modelManager->getRepository(State::class)->find($stateId);
    }
}
