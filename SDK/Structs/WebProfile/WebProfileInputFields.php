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

namespace SwagPaymentPayPalUnified\SDK\Structs\WebProfile;

class WebProfileInputFields
{
    /**
     * Indicates whether the buyer can enter a note to the merchant on the PayPal page during checkout.
     *
     * @var bool $allowNote
     */
    private $allowNote = false;

    /**
     * Indicates whether PayPal displays shipping address fields on the experience pages.
     * For digital goods, this field is required and must be 1.
     *
     * Value is:
     * [0] Displays the shipping address on the PayPal pages.
     * [1] Redacts shipping address fields from the PayPal pages.
     * [2] Gets the shipping address from the buyer's account profile.
     *
     * @var int $noShipping
     */
    private $noShipping = 0;

    /**
     * Indicates whether to display the shipping address that is passed to this call
     * rather than the one on file with PayPal for this buyer on the PayPal experience pages.
     *
     * Value is:
     * [0] Displays the shipping address on file.
     * [1] Displays the shipping address supplied to this call. The buyer cannot edit this shipping address.
     *
     * @var int $addressOverride
     */
    private $addressOverride = 1;

    /**
     * @return boolean
     */
    public function getAllowNote()
    {
        return $this->allowNote;
    }

    /**
     * @param boolean $allowNote
     */
    public function setAllowNote($allowNote)
    {
        $this->allowNote = $allowNote;
    }

    /**
     * @return int
     */
    public function getNoShipping()
    {
        return $this->noShipping;
    }

    /**
     * @param int $noShipping
     */
    public function setNoShipping($noShipping)
    {
        $this->noShipping = $noShipping;
    }

    /**
     * @return int
     */
    public function getAddressOverride()
    {
        return $this->addressOverride;
    }

    /**
     * @param int $addressOverride
     */
    public function setAddressOverride($addressOverride)
    {
        $this->addressOverride = $addressOverride;
    }

    /**
     * @param array $data
     * @return WebProfileInputFields
     */
    public static function fromArray(array $data = [])
    {
        $inputFields = new WebProfileInputFields();
        $inputFields->setAllowNote($data['allow_note']);
        $inputFields->setNoShipping($data['no_shipping']);
        $inputFields->setAddressOverride($data['address_override']);

        return $inputFields;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'allow_note' => $this->getAllowNote(),
            'no_shipping' => $this->getNoShipping(),
            'address_override' => $this->getAddressOverride()
        ];
    }
}
