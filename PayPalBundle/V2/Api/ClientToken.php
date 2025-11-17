<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\V2\Api;

use DateInterval;
use DateTime;
use DateTimeInterface;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PayPalApiStruct;

class ClientToken extends PayPalApiStruct
{
    /**
     * 15 minutes / 900 seconds compensation buffer to prevent API errors
     */
    const COMPENSATION_BUFFER = 900;

    const EXPIRES_IN_KEY = 'expires_in';

    const INTERVAL_TEMPLATE = 'PT%dS';

    /**
     * @var string
     */
    private $clientToken;

    /**
     * @var string
     */
    private $idToken;

    /**
     * @var int
     */
    private $expiresIn;

    /**
     * @var DateTimeInterface
     */
    private $expires;

    /**
     * @return string
     */
    public function getClientToken()
    {
        return $this->clientToken;
    }

    /**
     * @param string $clientToken
     *
     * @return void
     */
    public function setClientToken($clientToken)
    {
        $this->clientToken = $clientToken;
    }

    /**
     * @return string
     */
    public function getIdToken()
    {
        return $this->idToken;
    }

    /**
     * @param string $idToken
     *
     * @return void
     */
    public function setIdToken($idToken)
    {
        $this->idToken = $idToken;
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
     *
     * @return void
     */
    public function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;
    }

    /**
     * @return DateTimeInterface
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @param DateTimeInterface $expires
     *
     * @return void
     */
    public function setExpires($expires)
    {
        $this->expires = $expires;
    }

    /**
     * @param array<mixed> $arrayDataWithSnakeCaseKeys
     */
    public function assign(array $arrayDataWithSnakeCaseKeys)
    {
        parent::assign($arrayDataWithSnakeCaseKeys);

        $intervalString = \sprintf(
            self::INTERVAL_TEMPLATE,
            $arrayDataWithSnakeCaseKeys[self::EXPIRES_IN_KEY] - self::COMPENSATION_BUFFER
        );

        $this->expires = (new DateTime())->add(new DateInterval($intervalString));

        return $this;
    }
}
