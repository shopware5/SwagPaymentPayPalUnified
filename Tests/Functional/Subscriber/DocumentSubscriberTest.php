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

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use SwagPaymentPayPalUnified\Subscriber\Document;

class DocumentSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function test_can_be_created()
    {
        $subscriber = $this->createDocumentSubscriber();

        $this->assertNotNull($subscriber);
    }

    public function test_getSubscribedEvents_has_correct_events()
    {
        $events = Document::getSubscribedEvents();

        $this->assertCount(1, $events);
        $this->assertEquals('onBeforeRenderDocument', $events['Shopware_Components_Document::assignValues::after']);
    }

    public function test_onBeforeRenderDocument_missing_document()
    {
        $subscriber = $this->createDocumentSubscriber();
        $hookArgs = new \Enlight_Hook_HookArgs();

        $result = $subscriber->onBeforeRenderDocument($hookArgs);

        $this->assertNull($result);
    }

    /**
     * @return Document
     */
    private function createDocumentSubscriber()
    {
        $paymentInstructionService = Shopware()->Container()->get('paypal_unified.payment_instruction_service');
        $templateService = Shopware()->Container()->get('paypal_unified.document_template_service');
        $modelManager = Shopware()->Container()->get('models');

        return new Document($paymentInstructionService, $templateService, $modelManager);
    }
}
