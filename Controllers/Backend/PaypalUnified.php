<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query\Expr\Join;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Order\Order;
use SwagPaymentPayPalUnified\Components\Backend\CaptureService;
use SwagPaymentPayPalUnified\Components\Backend\PaymentDetailsService;
use SwagPaymentPayPalUnified\Components\Backend\VoidService;
use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentStatus;
use SwagPaymentPayPalUnified\Components\Services\PaymentStatusService;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\AuthorizationResource;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\CaptureResource;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\OrderResource;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\RefundResource;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\SaleResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\SaleRefund;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\Amount;

class Shopware_Controllers_Backend_PaypalUnified extends Shopware_Controllers_Backend_Application
{
    /**
     * {@inheritdoc}
     */
    protected $model = Order::class;

    /**
     * @var string
     */
    protected $alias = 'sOrder';

    /**
     * @var ExceptionHandlerServiceInterface
     */
    protected $exceptionHandler;

    /**
     * @var array
     */
    protected $filterFields = [
        'number',
        'orderTime',
        'invoiceAmount',
        'customer.email',
    ];

    /**
     * {@inheritdoc}
     */
    public function preDispatch()
    {
        $this->exceptionHandler = $this->container->get('paypal_unified.exception_handler_service');
        $this->container->get('paypal_unified.backend.shop_registration_service')->registerShopById((int) $this->request->getParam('shopId'));

        parent::preDispatch();
    }

    /**
     * Handles the payment detail action.
     * It first requests the payment details from the PayPal API and assigns the whole object
     * to the response. Afterwards, it uses the paypal_unified.sales_history_builder_service service to
     * parse the sales history. The sales history is also being assigned to the response.
     */
    public function paymentDetailsAction()
    {
        $paymentId = $this->Request()->getParam('id');
        $paymentMethodId = $this->Request()->getParam('paymentMethodId');
        //For legacy orders, the payment id is the transactionId and not the temporaryId
        $transactionId = $this->Request()->getParam('transactionId');

        /** @var PaymentDetailsService $paymentDetailService */
        $paymentDetailService = $this->get('paypal_unified.backend.payment_details_service');
        $viewParameter = $paymentDetailService->getPaymentDetails($paymentId, $paymentMethodId, $transactionId);

        $this->View()->assign($viewParameter);
    }

    public function saleDetailsAction()
    {
        $saleId = $this->Request()->getParam('id');
        $view = $this->View();

        /** @var SaleResource $saleResource */
        $saleResource = $this->get('paypal_unified.sale_resource');

        try {
            $view->assign('details', $saleResource->get($saleId));
            $view->assign('success', true);
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'obtain sale details');

            $view->assign([
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ]);
        }
    }

    public function refundDetailsAction()
    {
        $saleId = $this->Request()->getParam('id');
        $view = $this->View();

        /** @var RefundResource $refundResource */
        $refundResource = $this->get('paypal_unified.refund_resource');

        try {
            $view->assign('details', $refundResource->get($saleId));
            $view->assign('success', true);
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'obtain refund details');

            $view->assign([
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ]);
        }
    }

    public function captureDetailsAction()
    {
        $captureId = $this->Request()->getParam('id');
        $view = $this->View();

        /** @var CaptureResource $captureResource */
        $captureResource = $this->get('paypal_unified.capture_resource');

        try {
            $view->assign('details', $captureResource->get($captureId));
            $view->assign('success', true);
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'obtain capture details');

            $view->assign([
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ]);
        }
    }

    public function authorizationDetailsAction()
    {
        $authorizationId = $this->Request()->getParam('id');
        $view = $this->View();

        /** @var AuthorizationResource $authorizationResource */
        $authorizationResource = $this->get('paypal_unified.authorization_resource');

        try {
            $view->assign('details', $authorizationResource->get($authorizationId));
            $view->assign('success', true);
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'obtain authorization details');

            $view->assign([
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ]);
        }
    }

    public function orderDetailsAction()
    {
        $orderId = $this->Request()->getParam('id');
        $view = $this->View();

        /** @var OrderResource $orderResource */
        $orderResource = $this->get('paypal_unified.order_resource');

        try {
            $view->assign('details', $orderResource->get($orderId));
            $view->assign('success', true);
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'obtain order details');

            $view->assign([
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ]);
        }
    }

    public function refundSaleAction()
    {
        $saleId = $this->Request()->getParam('id');
        $totalAmount = number_format($this->Request()->getParam('amount'), 2);
        $invoiceNumber = $this->Request()->getParam('invoiceNumber');
        $refundCompletely = (bool) $this->Request()->getParam('refundCompletely');
        $currency = $this->Request()->getParam('currency');
        $view = $this->View();

        $refund = new SaleRefund();
        $refund->setInvoiceNumber($invoiceNumber);

        if (!$refundCompletely) {
            $amountStruct = new Amount();
            $amountStruct->setTotal($totalAmount);
            $amountStruct->setCurrency($currency);

            $refund->setAmount($amountStruct);
        }

        /** @var SaleResource $saleResource */
        $saleResource = $this->get('paypal_unified.sale_resource');
        /** @var PaymentStatusService $paymentStatusService */
        $paymentStatusService = $this->get('paypal_unified.payment_status_service');

        try {
            $refundData = $saleResource->refund($saleId, $refund);

            if ($refundData['state'] === PaymentStatus::PAYMENT_COMPLETED) {
                $paymentStatusService->updatePaymentStatus(
                    $refundData['parent_payment'],
                    PaymentStatus::PAYMENT_STATUS_REFUNDED
                );
            }

            $view->assign('refund', $refundData);
            $view->assign('success', true);
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'refund sale');

            $view->assign([
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ]);
        }
    }

    public function captureOrderAction()
    {
        $orderId = $this->Request()->getParam('id');
        $amountToCapture = number_format($this->Request()->getParam('amount'), 2);
        $currency = $this->Request()->getParam('currency');
        $isFinal = (bool) $this->Request()->getParam('isFinal');

        /** @var CaptureService $captureService */
        $captureService = $this->get('paypal_unified.backend.capture_service');
        $viewParameter = $captureService->captureOrder($orderId, $amountToCapture, $currency, $isFinal);

        $this->View()->assign($viewParameter);
    }

    public function captureAuthorizationAction()
    {
        $authorizationId = $this->Request()->getParam('id');
        $amountToCapture = number_format($this->Request()->getParam('amount'), 2);
        $currency = $this->Request()->getParam('currency');
        $isFinal = (bool) $this->Request()->getParam('isFinal');

        /** @var CaptureService $captureService */
        $captureService = $this->get('paypal_unified.backend.capture_service');
        $viewParameter = $captureService->captureAuthorization($authorizationId, $amountToCapture, $currency, $isFinal);

        $this->View()->assign($viewParameter);
    }

    public function refundCaptureAction()
    {
        $captureId = $this->Request()->getParam('id');
        $totalAmount = number_format($this->Request()->getParam('amount'), 2);
        $description = $this->Request()->getParam('note');
        $currency = $this->Request()->getParam('currency');

        /** @var CaptureService $captureService */
        $captureService = $this->get('paypal_unified.backend.capture_service');
        $viewParameter = $captureService->refundCapture($captureId, $totalAmount, $currency, $description);

        $this->View()->assign($viewParameter);
    }

    public function voidAuthorizationAction()
    {
        $authorizationId = $this->Request()->getParam('id');

        /** @var VoidService $voidService */
        $voidService = $this->get('paypal_unified.backend.void_service');
        $viewParameter = $voidService->voidAuthorization($authorizationId);

        $this->View()->assign($viewParameter);
    }

    public function voidOrderAction()
    {
        $orderId = $this->Request()->getParam('id');

        /** @var VoidService $voidService */
        $voidService = $this->get('paypal_unified.backend.void_service');
        $viewParameter = $voidService->voidOrder($orderId);

        $this->View()->assign($viewParameter);
    }

    /**
     * {@inheritdoc}
     */
    public function getDetailQuery($id)
    {
        return $this->prepareOrderQueryBuilder(parent::getDetailQuery($id));
    }

    /**
     * {@inheritdoc}
     */
    protected function getListQuery()
    {
        return $this->prepareOrderQueryBuilder(parent::getListQuery());
    }

    /**
     * {@inheritdoc}
     */
    protected function getList($offset, $limit, $sort = [], $filter = [], array $wholeParams = [])
    {
        //Sets the initial sort to orderTime descending
        if (!$sort) {
            $defaultSort = [
                'property' => 'orderTime',
                'direction' => 'DESC',
            ];
            $sort[] = $defaultSort;
        }

        $orderList = parent::getList($offset, $limit, $sort, $filter, $wholeParams);

        /*
         * After the removal of the order/payment status description in Shopware 5.5,
         * we need to add the translations manually.
         */
        $orderStatusNamespace = $this->container->get('snippets')->getNamespace('backend/static/order_status');
        $paymentStatusNamespace = $this->container->get('snippets')->getNamespace('backend/static/payment_status');

        $orderList['data'] = array_map(static function ($order) use ($orderStatusNamespace, $paymentStatusNamespace) {
            if (!isset($order['orderStatus']['description'])) {
                $order['orderStatus']['description'] = $orderStatusNamespace->get($order['orderStatus']['name']);
            }

            if (!isset($order['paymentStatus']['description'])) {
                $order['paymentStatus']['description'] = $paymentStatusNamespace->get($order['paymentStatus']['name']);
            }

            return $order;
        }, $orderList['data']);

        return $orderList;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilterConditions($filters, $model, $alias, $whiteList = [])
    {
        if ($this->isFilterRequest($filters)) {
            $whiteList[] = 'status';
            $whiteList[] = 'cleared';
        }

        $conditions = parent::getFilterConditions(
            $filters,
            $model,
            $alias,
            $whiteList
        );

        // Ignore canceled or incomplete orders
        $conditions[] = [
            'property' => 'sOrder.number',
            'expression' => '!=',
            'value' => '0',
        ];

        // Ignore order with PayPal as payment method but without valid transaction ID
        $conditions[] = [
            'property' => 'sOrder.transactionId',
            'expression' => '!=',
            'value' => '',
        ];

        return $conditions;
    }

    /**
     * {@inheritdoc}
     */
    protected function getModelFields($model, $alias = null)
    {
        $fields = parent::getModelFields($model, $alias);

        if ($model === $this->model) {
            $fields = array_merge(
                $fields,
                [
                    'customer.email' => ['alias' => 'customer.email', 'type' => 'string'],
                ]
            );
        }

        return $fields;
    }

    /**
     * @return QueryBuilder
     */
    private function prepareOrderQueryBuilder(QueryBuilder $builder)
    {
        $paymentMethodProvider = $this->get('paypal_unified.payment_method_provider');
        $connection = $this->get('dbal_connection');

        // If there was PayPal classic installed earlier, those orders have to be queried.
        $legacyPaymentIds = $this->get('paypal_unified.legacy_service')->getClassicPaymentIds();
        $paymentIds = [
            $paymentMethodProvider->getPaymentId(PaymentMethodProvider::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME),
            $paymentMethodProvider->getPaymentId(PaymentMethodProvider::PAYPAL_UNIFIED_INSTALLMENTS_METHOD_NAME),
        ];

        $paymentIds = array_merge($paymentIds, $legacyPaymentIds);

        $builder->innerJoin(
            'sOrder.payment',
            'payment',
            Join::WITH,
            'payment.id IN (:paymentIds)'
        )->setParameter('paymentIds', $paymentIds, Connection::PARAM_INT_ARRAY);

        $builder->leftJoin('sOrder.languageSubShop', 'languageSubShop')
            ->leftJoin('sOrder.customer', 'customer')
            ->leftJoin('sOrder.orderStatus', 'orderStatus')
            ->leftJoin('sOrder.paymentStatus', 'paymentStatus')
            ->leftJoin('sOrder.attribute', 'attribute')
            ->addSelect('languageSubShop')
            ->addSelect('payment')
            ->addSelect('customer')
            ->addSelect('orderStatus')
            ->addSelect('paymentStatus')
            ->addSelect('attribute');

        return $builder;
    }

    /**
     * Checks if one of the filter conditions is "search". If not, the filters were set by the filter panel
     *
     * @return bool
     */
    private function isFilterRequest(array $filters)
    {
        return !\in_array('search', array_column($filters, 'property'), true);
    }
}
