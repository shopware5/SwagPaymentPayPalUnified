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

namespace SwagPaymentPayPalUnified\SDK\Structs;

use DateTime;

class Token
{
    /**
     * Scopes expressed in the form of resource URL endpoints. The value of the scope parameter
     * is expressed as a list of space-delimited, case-sensitive strings.
     *
     * @var string $scope
     */
    private $scope;

    /**
     * The access token issued by PayPal. After the access token
     * expires (see $expiresIn), you must request a new access token.
     *
     * @var string $accessToken
     */
    private $accessToken;

    /**
     * The type of the token issued as described in OAuth2.0 RFC6749,
     * Section 7.1. Value is case insensitive.
     *
     * @var string $tokenType
     */
    private $tokenType;

    /**
     * The lifetime of the access token, in seconds.
     *
     * @var int $expiresIn
     */
    private $expiresIn;

    /**
     * Calculated expiration date
     *
     * @var DateTime $expireDateTime
     */
    private $expireDateTime;

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return string
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * @param string $tokenType
     */
    public function setTokenType($tokenType)
    {
        $this->tokenType = $tokenType;
    }

    /**
     * @return int
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    /**
     * @param int $expiresIn
     */
    public function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;
    }

    /**
     * @return DateTime
     */
    public function getExpireDateTime()
    {
        return $this->expireDateTime;
    }

    /**
     * @param DateTime $expireDateTime
     */
    public function setExpireDateTime($expireDateTime)
    {
        $this->expireDateTime = $expireDateTime;
    }

    /**
     * @param $data
     * @return Token
     */
    public static function fromArray($data)
    {
        $result = new Token();

        $result->setAccessToken($data['access_token']);
        $result->setExpiresIn((int)$data['expires_in']);
        $result->setScope($data['scope']);
        $result->setTokenType($data['token_type']);

        //Calculate the expiration date manually
        $expirationDateTime = new DateTime();
        $interval = \DateInterval::createFromDateString($result->getExpiresIn() . ' seconds');
        $expirationDateTime = $expirationDateTime->add($interval);

        $result->setExpireDateTime($expirationDateTime);
        return $result;
    }
}
