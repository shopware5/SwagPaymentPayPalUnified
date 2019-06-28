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
use Shopware\Models\Order\Status;
use Shopware\Models\Shop\Repository as ShopRepository;
use Shopware\Models\Shop\Shop;
use SwagPaymentPayPalUnified\Components\ExceptionHandlerServiceInterface;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\PaymentStatus;
use SwagPaymentPayPalUnified\Components\Services\Legacy\LegacyService;
use SwagPaymentPayPalUnified\Components\Services\TransactionHistoryBuilderService;
use SwagPaymentPayPalUnified\PayPalBundle\PaymentIntent;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\AuthorizationResource;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\CaptureResource;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\OrderResource;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\PaymentResource;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\RefundResource;
use SwagPaymentPayPalUnified\PayPalBundle\Resources\SaleResource;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Capture;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\CaptureRefund;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RelatedResources\Sale;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\SaleRefund;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions\Amount;

class Shopware_Controllers_Backend_PaypalUnified extends Shopware_Controllers_Backend_Application
{
    /**
     * @var string
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
        $this->exceptionHandler = $this->get('paypal_unified.exception_handler_service');

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
        $shopId = $this->Request()->getParam('shopId');

        $this->registerShopResource($shopId);

        /** @var LegacyService $legacyService */
        $legacyService = $this->get('paypal_unified.legacy_service');
        $legacyPaymentIds = $legacyService->getClassicPaymentIds();

        try {
            //Check for a legacy payment
            if (in_array($paymentMethodId, $legacyPaymentIds, true)) {
                $this->prepareLegacyDetails($transactionId);
            } else {
                $this->prepareUnifiedDetails($paymentId);
            }
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'obtain payment details');

            $this->View()->assign([
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ]);
        }
    }

    public function saleDetailsAction()
    {
        $saleId = $this->Request()->getParam('id');
        $shopId = $this->Request()->getParam('shopId');
        $view = $this->View();

        $this->registerShopResource($shopId);

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
        $shopId = $this->Request()->getParam('shopId');
        $view = $this->View();

        $this->registerShopResource($shopId);

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
        $shopId = $this->Request()->getParam('shopId');
        $view = $this->View();

        $this->registerShopResource($shopId);

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
        $shopId = $this->Request()->getParam('shopId');
        $view = $this->View();

        $this->registerShopResource($shopId);

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
        $shopId = $this->Request()->getParam('shopId');
        $view = $this->View();

        $this->registerShopResource($shopId);

        /** @var AuthorizationResource $orderResource */
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
        $refundCompletely = $this->Request()->getParam('refundCompletely');
        $shopId = $this->Request()->getParam('shopId');
        $currency = $this->Request()->getParam('currency');
        $view = $this->View();

        try {
            $this->registerShopResource($shopId);

            /** @var SaleResource $saleResource */
            $saleResource = $this->get('paypal_unified.sale_resource');

            $refund = new SaleRefund();
            $refund->setInvoiceNumber($invoiceNumber);

            if (!$refundCompletely) {
                $amountStruct = new Amount();
                $amountStruct->setTotal($totalAmount);
                $amountStruct->setCurrency($currency);

                $refund->setAmount($amountStruct);
            }

            $refundData = $saleResource->refund($saleId, $refund);

            if ($refundData['state'] === 'completed') {
                $this->updatePaymentStatus($refundData);
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
        $shopId = $this->Request()->getParam('shopId');
        $authorizationId = $this->Request()->getParam('id');
        $amountToCapture = number_format($this->Request()->getParam('amount'), 2);
        $currency = $this->Request()->getParam('currency');
        $isFinal = $this->Request()->getParam('isFinal');
        $view = $this->View();

        try {
            $this->registerShopResource($shopId);

            $capture = new Capture();
            $amount = new Amount();
            $amount->setCurrency($currency);
            $amount->setTotal($amountToCapture);
            $capture->setAmount($amount);
            $capture->setIsFinalCapture($isFinal);

            /** @var AuthorizationResource $orderResource */
            $orderResource = $this->get('paypal_unified.order_resource');
            $orderResource->capture($authorizationId, $capture);

            $view->assign('success', true);
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'authorize payment');

            $view->assign([
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ]);
        }
    }

    public function captureAuthorizationAction()
    {
        $shopId = $this->Request()->getParam('shopId');
        $authorizationId = $this->Request()->getParam('id');
        $amountToCapture = number_format($this->Request()->getParam('amount'), 2);
        $currency = $this->Request()->getParam('currency');
        $isFinal = $this->Request()->getParam('isFinal');
        $view = $this->View();

        try {
            $this->registerShopResource($shopId);

            $capture = new Capture();
            $amount = new Amount();
            $amount->setCurrency($currency);
            $amount->setTotal($amountToCapture);
            $capture->setAmount($amount);
            $capture->setIsFinalCapture($isFinal);

            /** @var AuthorizationResource $authResource */
            $authResource = $this->get('paypal_unified.authorization_resource');
            $authResource->capture($authorizationId, $capture);

            $view->assign('success', true);
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'authorize payment');

            $view->assign([
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ]);
        }
    }

    public function refundCaptureAction()
    {
        $id = $this->Request()->getParam('id');
        $totalAmount = number_format($this->Request()->getParam('amount'), 2);
        $description = $this->Request()->getParam('note');
        $shopId = $this->Request()->getParam('shopId');
        $currency = $this->Request()->getParam('currency');
        $view = $this->View();

        try {
            $this->registerShopResource($shopId);

            /** @var CaptureResource $captureResource */
            $captureResource = $this->get('paypal_unified.capture_resource');

            $refund = new CaptureRefund();
            $refund->setDescription($description);

            $amountStruct = new Amount();
            $amountStruct->setTotal($totalAmount);
            $amountStruct->setCurrency($currency);
            $refund->setAmount($amountStruct);

            $refundData = $captureResource->refund($id, $refund);

            if ($refundData['state'] === 'completed') {
                $this->updatePaymentStatus($refundData);
            }

            $view->assign('refund', $refundData);
            $view->assign('success', true);
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'refund capture');

            $view->assign([
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ]);
        }
    }

    public function voidAuthorizationAction()
    {
        $id = $this->Request()->getParam('id');
        $shopId = $this->Request()->getParam('shopId');
        $view = $this->View();

        try {
            $this->registerShopResource($shopId);

            /** @var AuthorizationResource $authResource */
            $authResource = $this->get('paypal_unified.authorization_resource');
            $view->assign('void', $authResource->void($id));
            $view->assign('success', true);
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'void authorization');

            $view->assign([
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ]);
        }
    }

    public function voidOrderAction()
    {
        $id = $this->Request()->getParam('id');
        $shopId = $this->Request()->getParam('shopId');
        $view = $this->View();

        try {
            $this->registerShopResource($shopId);

            /** @var OrderResource $orderResource */
            $orderResource = $this->get('paypal_unified.order_resource');
            $view->assign('success', true);
            $view->assign('void', $orderResource->void($id));
        } catch (Exception $e) {
            $error = $this->exceptionHandler->handle($e, 'void order');

            $view->assign([
                'success' => false,
                'message' => $error->getCompleteMessage(),
            ]);
        }
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
        $paymentMethodProvider = new PaymentMethodProvider($this->get('models'));

        // If there was PayPal classic installed earlier, those orders have to be queried.
        $legacyPaymentIds = $this->get('paypal_unified.legacy_service')->getClassicPaymentIds();
        $paymentIds = [
            $paymentMethodProvider->getPaymentId($this->get('dbal_connection')),
            $paymentMethodProvider->getPaymentId($this->get('dbal_connection'), PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME),
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
     * @param int $shopId
     */
    private function registerShopResource($shopId = null)
    {
        /** @var ShopRepository $shopRepository */
        $shopRepository = $this->get('models')->getRepository(Shop::class);

        if ($shopId === null) {
            $shopId = $shopRepository->getActiveDefault()->getId();
        }

        $shopRepository->getActiveById($shopId)->registerResources();

        $this->get('paypal_unified.settings_service')->refreshDependencies();
    }

    /**
     * @param string $transactionId
     */
    private function prepareLegacyDetails($transactionId)
    {
        $view = $this->View();
        $view->assign('success', true);
        $view->assign('legacy', true);

        /** @var SaleResource $saleResource */
        $saleResource = $this->get('paypal_unified.sale_resource');
        $details = $saleResource->get($transactionId);
        $details['intent'] = 'sale';

        /** @var TransactionHistoryBuilderService $transactionHistoryBuilder */
        $transactionHistoryBuilder = $this->get('paypal_unified.transaction_history_builder_service');

        $view->assign('history', $transactionHistoryBuilder->getLegacyHistory(Sale::fromArray($details)));
        $view->assign('payment', $details);
    }

    /**
     * @param string $paymentId
     */
    private function prepareUnifiedDetails($paymentId)
    {
        /** @var PaymentResource $paymentResource */
        $paymentResource = $this->get('paypal_unified.payment_resource');
        $paymentDetails = $paymentResource->get($paymentId);
        $view = $this->View();

        /** @var TransactionHistoryBuilderService $transactionHistoryBuilder */
        $transactionHistoryBuilder = $this->get('paypal_unified.transaction_history_builder_service');

        $view->assign('payment', $paymentDetails);
        $view->assign('history', $transactionHistoryBuilder->getTransactionHistory($paymentDetails));

        if ($paymentDetails['intent'] === PaymentIntent::AUTHORIZE) {
            //Separately assign the data, to provide an easier usage
            $view->assign('authorization', $paymentDetails['transactions'][0]['related_resources'][0]['authorization']);
        } elseif ($paymentDetails['intent'] === PaymentIntent::ORDER) {
            $view->assign('order', $paymentDetails['transactions'][0]['related_resources'][0]['order']);
        } elseif ($paymentDetails['intent'] === PaymentIntent::SALE) {
            $view->assign('sale', $paymentDetails['transactions'][0]['related_resources'][0]['sale']);
        }

        $view->assign('success', true);
    }

    private function updatePaymentStatus(array $refundData)
    {
        /** @var Order $orderModel */
        $orderModel = $this->getModelManager()->getRepository(Order::class)
            ->findOneBy(['temporaryId' => $refundData['parent_payment']]);

        if (!($orderModel instanceof Order)) {
            return;
        }

        /** @var Status $orderStatusModel */
        $orderStatusModel = $this->getModelManager()->getRepository(Status::class)
            ->find(PaymentStatus::PAYMENT_STATUS_REFUNDED);

        $orderModel->setPaymentStatus($orderStatusModel);

        $this->getModelManager()->flush($orderModel);
    }

    /**
     * Checks if one of the filter conditions is "search". If not, the filters were set by the filter panel
     *
     * @return bool
     */
    private function isFilterRequest(array $filters)
    {
        return !in_array('search', array_column($filters, 'property'), true);
    }
}
