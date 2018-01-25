<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile;

class WebProfileInputFields
{
    /**
     * Indicates whether the buyer can enter a note to the merchant on the PayPal page during checkout.
     *
     * @var bool
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
     * @var int
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
     * @var int
     */
    private $addressOverride = 1;

    /**
     * @return bool
     */
    public function getAllowNote()
    {
        return $this->allowNote;
    }

    /**
     * @param bool $allowNote
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
     *
     * @return WebProfileInputFields
     */
    public static function fromArray(array $data = [])
    {
        $inputFields = new self();
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
            'address_override' => $this->getAddressOverride(),
        ];
    }
}
