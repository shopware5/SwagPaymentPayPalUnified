<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @return Token
     */
    public static function fromArray(array $data)
    {
        $token = new self();

        $token->setAccessToken($data['access_token']);
        $token->setExpiresIn((int) $data['expires_in']);
        $token->setScope($data['scope']);
        $token->setTokenType($data['token_type']);

        // Calculate the expiration date manually
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
