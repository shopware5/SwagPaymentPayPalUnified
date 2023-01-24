<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Shopware\Components\DependencyInjection\Bridge\Session;
use Shopware\Models\Order\Status;
use SwagPaymentPayPalUnified\Components\ErrorCodes;
use SwagPaymentPayPalUnified\Components\Exception\BirthdateNotValidException;
use SwagPaymentPayPalUnified\Components\Exception\PhoneNumberCountryCodeNotValidException;
use SwagPaymentPayPalUnified\Components\Exception\PhoneNumberNationalNumberNotValidException;
use SwagPaymentPayPalUnified\Components\Exception\PuiValidationException;
use SwagPaymentPayPalUnified\Components\PayPalOrderParameter\ShopwareOrderData;
use SwagPaymentPayPalUnified\Controllers\Frontend\AbstractPaypalPaymentController;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\InvalidBillingAddressException;
use SwagPaymentPayPalUnified\Controllers\Frontend\Exceptions\InvalidShippingAddressException;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentType;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\PaymentStatusV2;
use SwagPaymentPayPalUnified\Subscriber\PayUponInvoice;

class Shopware_Controllers_Frontend_PaypalUnifiedV2PayUponInvoice extends AbstractPaypalPaymentController
{
    /**
     * @var Enlight_Components_Session_Namespace
     */
    private $session;

    public function preDispatch()
    {
        parent::preDispatch();

        $this->session = $this->get('paypal_unified.dependency_provider')->getSession();
    }

    /**
     * @return void
     */
    public function indexAction()
    {
        $this->logger->debug(sprintf('%s START', __METHOD__));

        $session = $this->dependencyProvider->getSession();
        $shopwareSessionOrderData = $session->offsetGet('sOrderVariables');

        $shopwareOrderData = new ShopwareOrderData($shopwareSessionOrderData['sUserData'], $shopwareSessionOrderData['sBasket']);

        $orderParams = $this->payPalOrderParameterFacade->createPayPalOrderParameter(PaymentType::PAYPAL_PAY_UPON_INVOICE_V2, $shopwareOrderData);

        try {
            $payPalOrder = $this->createPayPalOrder($orderParams);
        } catch (BirthdateNotValidException $birthdateNotValidException) {
            $this->redirectToConfirmWithError(['puiBirthdateWrong' => true], $birthdateNotValidException);

            return;
        } catch (PhoneNumberCountryCodeNotValidException $phoneNumberCountryCodeNotValidException) {
            $this->redirectToConfirmWithError(['puiPhoneNumberWrong' => true], $phoneNumberCountryCodeNotValidException);

            return;
        } catch (PhoneNumberNationalNumberNotValidException $phoneNumberNationalNumberNotValidException) {
            $this->redirectToConfirmWithError(['puiPhoneNumberWrong' => true], $phoneNumberNationalNumberNotValidException);

            return;
        } catch (InvalidBillingAddressException $invalidBillingAddressException) {
            $this->redirectInvalidAddress(['invalidBillingAddress' => true]);

            return;
        } catch (InvalidShippingAddressException $invalidShippingAddressException) {
            $this->redirectInvalidAddress(['invalidShippingAddress' => true]);

            return;
        }

        if (!$payPalOrder instanceof Order) {
            return;
        }

        $payPalOrderId = $payPalOrder->getId();

        if ($this->isPaymentCompleted($payPalOrderId, PaymentStatusV2::ORDER_PAYMENT_PENDING_APPROVAL)) {
            $payPalOrder = $this->getPayPalOrder($payPalOrderId);
            if (!$payPalOrder instanceof Order) {
                $this->orderNumberService->restoreOrdernumberToPool();

                return;
            }

            $shopwareOrderNumber = $this->createShopwareOrder($payPalOrderId, PaymentType::PAYPAL_PAY_UPON_INVOICE_V2, Status::PAYMENT_STATE_COMPLETELY_PAID);
            $this->session->offsetSet(PayUponInvoice::PUI_SHOPWARE_ORDER, $shopwareOrderNumber);

            $this->setTransactionId($shopwareOrderNumber, $payPalOrder);

            $this->logger->debug(sprintf('%s REDIRECT TO checkout/finish', __METHOD__));

            $this->redirect([
                'module' => 'frontend',
                'controller' => 'checkout',
                'action' => 'finish',
                'sUniqueID' => $payPalOrderId,
            ]);

            return;
        }

        $this->orderNumberService->restoreOrdernumberToPool();

        $redirectDataBuilder = $this->redirectDataBuilderFactory->createRedirectDataBuilder()
            ->setCode(ErrorCodes::COMMUNICATION_FAILURE);

        $this->paymentControllerHelper->handleError($this, $redirectDataBuilder);
    }

    /**
     * @param array<string,bool> $error
     *
     * @return void
     */
    private function redirectToConfirmWithError(array $error, PuiValidationException $exception)
    {
        $this->logger->debug(
            sprintf(
                '%s %s',
                __METHOD__,
                $exception->getMessage()
            ),
            $exception->getTrace()
        );

        $this->redirect(array_merge([
            'module' => 'frontend',
            'controller' => 'checkout',
            'action' => 'confirm',
        ], $error));
    }
}
