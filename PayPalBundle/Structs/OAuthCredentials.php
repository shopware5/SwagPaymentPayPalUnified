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

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs;

class OAuthCredentials
{
    /** @var string $restId */
    private $restId;

    /** @var string $restSecret */
    private $restSecret;

    /**
     * @return string
     */
    public function getRestId()
    {
        return $this->restId;
    }

    /**
     * @param string $restId
     */
    public function setRestId($restId)
    {
        $this->restId = $restId;
    }

    /**
     * @return string
     */
    public function getRestSecret()
    {
        return $this->restSecret;
    }

    /**
     * @param string $restSecret
     */
    public function setRestSecret($restSecret)
    {
        $this->restSecret = $restSecret;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return 'Basic ' . base64_encode($this->restId . ':' . $this->restSecret);
    }
}
