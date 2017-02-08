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

use DateTime;

class Token
{
    /**
     * Scopes expressed in the form of resource URL endpoints. The value of the scope parameter
     * is expressed as a list of space-delimited, case-sensitive strings.
     *
     * @var string
     */
    private $scope;

    /**
     * The access token issued by PayPal. After the access token
     * expires (see $expiresIn), you must request a new access token.
     *
     * @var string
     */
    private $accessToken;

    /**
     * The type of the token issued as described in OAuth2.0 RFC6749,
     * Section 7.1. Value is case insensitive.
     *
     * @var string
     */
    private $tokenType;

    /**
     * The lifetime of the access token, in seconds.
     *
     * @var int
     */
    private $expiresIn;

    /**
     * Calculated expiration date
     *
     * @var DateTime
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
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @return string
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * @return int
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    /**
     * @return DateTime
     */
    public function getExpireDateTime()
    {
        return $this->expireDateTime;
    }

    /**
     * @param array $data
     *
     * @return Token
     */
    public static function fromArray(array $data)
    {
        $token = new self();

        $token->setAccessToken($data['access_token']);
        $token->setExpiresIn((int) $data['expires_in']);
        $token->setScope($data['scope']);
        $token->setTokenType($data['token_type']);

        //Calculate the expiration date manually
        $expirationDateTime = new DateTime();
        $interval = \DateInterval::createFromDateString($token->getExpiresIn() . ' seconds');
        $expirationDateTime = $expirationDateTime->add($interval);

        $token->setExpireDateTime($expirationDateTime);

        return $token;
    }

    /**
     * @param string $scope
     */
    private function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @param string $accessToken
     */
    private function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @param string $tokenType
     */
    private function setTokenType($tokenType)
    {
        $this->tokenType = $tokenType;
    }

    /**
     * @param int $expiresIn
     */
    private function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;
    }

    /**
     * @param DateTime $expireDateTime
     */
    private function setExpireDateTime($expireDateTime)
    {
        $this->expireDateTime = $expireDateTime;
    }
}
