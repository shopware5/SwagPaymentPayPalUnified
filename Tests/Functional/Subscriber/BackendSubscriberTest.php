<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Subscriber;

use Enlight_Controller_ActionEventArgs;
use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use Generator;
use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\PayPalBundle\Services\NonceService;
use SwagPaymentPayPalUnified\Setup\TranslationUpdater;
use SwagPaymentPayPalUnified\Subscriber\Backend;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Functional\TranslationTestCaseTrait;
use SwagPaymentPayPalUnified\Tests\Mocks\DummyController;
use SwagPaymentPayPalUnified\Tests\Mocks\ViewMock;

class BackendSubscriberTest extends TestCase
{
    use DatabaseTestCaseTrait;
    use TranslationTestCaseTrait;

    /**
     * @return void
     */
    public function testGetSubscribedEventsHasCorrectEvents()
    {
        $events = Backend::getSubscribedEvents();
        static::assertSame('onLoadBackendIndex', $events['Enlight_Controller_Action_PostDispatchSecure_Backend_Index']);
        static::assertSame('onPostDispatchConfig', $events['Enlight_Controller_Action_PostDispatchSecure_Backend_Config']);
        static::assertSame('onPostDispatchPayment', $events['Enlight_Controller_Action_PostDispatchSecure_Backend_Payment']);
        static::assertSame('onPostDispatchOrder', $events['Enlight_Controller_Action_PostDispatchSecure_Backend_Order']);
        static::assertCount(4, $events);
    }

    /**
     * @return void
     */
    public function testOnLoadBackendIndexExtendsTemplate()
    {
        $subscriber = $this->getSubscriber();

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('index');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $subscriber->onLoadBackendIndex($enlightEventArgs);

        static::assertCount(1, $view->getTemplateDir());
    }

    /**
     * @return void
     */
    public function testOnPostDispatchConfigExtendsTemplate()
    {
        $subscriber = $this->getSubscriber();

        $view = new ViewMock(
            new Enlight_Template_Manager()
        );

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('load');

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $subscriber->onPostDispatchConfig($enlightEventArgs);

        static::assertCount(1, $view->getTemplateDir());
    }

    /**
     * @dataProvider OnPostDispatchConfigShouldUpdatePayLaterTranslationsTestDataProvider
     *
     * @param int         $shopId
     * @param int         $localeId
     * @param string|null $expectedResult
     *
     * @return void
     */
    public function testOnPostDispatchConfigShouldUpdatePayLaterTranslations($shopId, $localeId, $expectedResult)
    {
        $paymentMethodId = 8;

        $sql = file_get_contents(__DIR__ . '/../../_fixtures/shops_for_translation.sql');
        static::assertTrue(\is_string($sql));
        $this->getContainer()->get('dbal_connection')->exec($sql);

        $subscriber = $this->getSubscriber();

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setActionName('saveValues');
        $request->setParam('_repositoryClass', 'shop');
        $request->setParam('localeId', $localeId);

        $view = new Enlight_View_Default(new Enlight_Template_Manager());

        $enlightEventArgs = new Enlight_Controller_ActionEventArgs([
            'subject' => new DummyController($request, $view, new Enlight_Controller_Response_ResponseTestCase()),
            'request' => $request,
        ]);

        $subscriber->onPostDispatchConfig($enlightEventArgs);

        $translationReader = $this->getTranslationService();
        $italianResult = $translationReader->read($shopId, TranslationUpdater::TRANSLATION_TYPE, $paymentMethodId, true);

        static::assertSame($expectedResult, $italianResult['description']);
    }

    /**
     * @return Generator<array<int,mixed>>
     */
    public function OnPostDispatchConfigShouldUpdatePayLaterTranslationsTestDataProvider()
    {
        yield 'Update australian shop' => [
            3,
            54,
            'PayPal, Pay in 4',
        ];

        yield 'Update us shop' => [
            4,
            74,
            'PayPal, Pay Later',
        ];

        yield 'Update spain shop' => [
            5,
            85,
            'PayPal, Paga en 3 plazos',
        ];

        yield 'Update french shop' => [
            6,
            108,
            'PayPal, Paiement en 4X',
        ];

        yield 'Update italian shop' => [
            7,
            136,
            'PayPal, Paga in 3 rate',
        ];

        yield 'Update other language shop' => [
            12,
            136,
            null,
        ];
    }

    /**
     * @return Backend
     */
    private function getSubscriber()
    {
        return new Backend(
            $this->getContainer()->getParameter('paypal_unified.plugin_dir'),
            static::createMock(NonceService::class),
            $this->getContainer(),
            $this->getContainer()->get('dbal_connection')
        );
    }
}
