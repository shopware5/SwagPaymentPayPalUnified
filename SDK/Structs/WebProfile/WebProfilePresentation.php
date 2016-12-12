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

class WebProfilePresentation
{
    /**
     * A label that overrides the business name in the PayPal account on the PayPal pages.
     * Character length and limitations: 127 single-byte alphanumeric characters.
     *
     * @var string $brandName
     */
    private $brandName;

    /**
     * A URL to the logo image. A valid media type is .gif, .jpg, or .png.
     * The maximum width of the image is 190 pixels. The maximum height of the image is 60 pixels.
     * PayPal crops images that are larger. PayPal places your logo image at the top of the cart review area.
     * PayPal recommends that you store the image on a secure (HTTPS) server.
     * Otherwise, web browsers display a message that checkout pages contain non-secure items.
     * Character length and limit: 127 single-byte alphanumeric characters.
     *
     * @var string $logoImage
     */
    private $logoImage;

    /**
     * The locale of pages displayed by PayPal payment experience.
     *
     * @var string $localeCode
     */
    private $localeCode;

    /**
     * @return string
     */
    public function getBrandName()
    {
        return $this->brandName;
    }

    /**
     * @param string $brandName
     */
    public function setBrandName($brandName)
    {
        $this->brandName = $brandName;
    }

    /**
     * @return string
     */
    public function getLogoImage()
    {
        return $this->logoImage;
    }

    /**
     * @param string $logoImage
     */
    public function setLogoImage($logoImage)
    {
        $this->logoImage = $logoImage;
    }

    /**
     * @return string
     */
    public function getLocaleCode()
    {
        return $this->localeCode;
    }

    /**
     * @param string $localeCode
     */
    public function setLocaleCode($localeCode)
    {
        $this->localeCode = $localeCode;
    }

    /**
     * @param array $data
     * @return WebProfilePresentation
     */
    public static function fromArray(array $data = [])
    {
        $presentation = new WebProfilePresentation();
        $presentation->setBrandName($data['brand_name']);
        $presentation->setLogoImage($data['logo_image']);
        $presentation->setLocaleCode($data['locale_code']);

        return $presentation;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'brand_name' => $this->getBrandName(),
            'logo_image' => $this->getLogoImage(),
            'locale_code' => $this->getLocaleCode(),
        ];
    }
}
