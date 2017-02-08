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

class ErrorResponse
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $informationLink;

    /**
     * @var string
     */
    private $debugId;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getInformationLink()
    {
        return $this->informationLink;
    }

    /**
     * @param string $informationLink
     */
    public function setInformationLink($informationLink)
    {
        $this->informationLink = $informationLink;
    }

    /**
     * @return string
     */
    public function getDebugId()
    {
        return $this->debugId;
    }

    /**
     * @param string $debugId
     */
    public function setDebugId($debugId)
    {
        $this->debugId = $debugId;
    }

    /**
     * @param array $data
     *
     * @return null|ErrorResponse
     */
    public static function fromArray(array $data)
    {
        if (!$data) {
            return null;
        }

        $result = new self();
        $result->setName($data['name']);
        $result->setMessage($data['message']);
        $result->setInformationLink($data['information_link']);
        $result->setDebugId($data['debug_id']);

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
}
