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

namespace SwagPaymentPayPalUnified\SDK\Components\Webhook;

class WebhookException extends \Exception
{
    /**
     * The name of the webhook event that triggered this exception
     *
     * @see WebhookEventTypes
     * @var string $eventType
     */
    private $eventType;

    /**
     * Sets the event type for this exception.
     * The name of the event specifies the webhook that triggered this exception
     *
     * @see WebhookEventTypes
     * @param $eventType
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;
    }

    /**
     * Returns the name of the event that has caused the exception.
     *
     * @see WebhookEventTypes
     * @return string
     */
    public function getEventType()
    {
        return $this->eventType;
    }
}
