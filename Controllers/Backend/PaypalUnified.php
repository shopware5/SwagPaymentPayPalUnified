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

use Shopware\Components\HttpClient\RequestException;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Order\Order;
use Shopware\Models\Shop\Shop;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\Legacy\LegacyService;
use SwagPaymentPayPalUnified\Components\Services\TransactionHistoryBuilderService;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
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
     * @var LoggerServiceInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $filterFields = [
        'number',
        'orderTime',
        'invoiceAmount',
        'customer.email',
        'orderStatus.description',
        'paymentStatus.description',
    ];

    /**
     * {@inheritdoc}
     */
    public function preDispatch()
    {
        $this->logger = $this->container->get('paypal_unified.logger_service');

        parent::preDispatch();
    }

    /**
     * Handles the payment detail action.
     * It first requests the payment details from the PayPal API and assigns the whole object
     * to the response. Afterwards, it uses the paypal_unified.sales_history_builder_service service to
     * parse the sales history. The sales history is also being assigned to the response.
     *
     * @throws RequestException
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
        $legacyPaymentId = $legacyService->getClassicPaymentId();

        try {
            //Check for a legacy payment
            if ($legacyPaymentId === $paymentMethodId) {
                $this->prepareLegacyDetails($transactionId);
            } else {
                $this->prepareUnifiedDetails($paymentId);
            }
        } catch (RequestException $ex) {
            $message = json_decode($ex->getBody(), true)['message'];
            $this->logger->error('Could not obtain payment details due to a communication failure', [$ex->getMessage(), $ex->getBody()]);
            $this->View()->assign('message', $message);
            $this->View()->assign('success', false);
            throw $ex;
        }
    }

    public function saleDetailsAction()
    {
        $saleId = $this->Request()->getParam('id');
        $shopId = $this->Request()->getParam('shopId');

        $this->registerShopResource($shopId);

        /** @var SaleResource $saleResource */
        $saleResource = $this->container->get('paypal_unified.sale_resource');

        try {
            $this->View()->assign('details', $saleResource->get($saleId));
            $this->View()->assign('success', true);
        } catch (RequestException $ex) {
            $message = json_decode($ex->getBody(), true)['message'];
            $this->logger->error('Could not obtain sale details due to a communication failure', [$ex->getMessage(), $ex->getBody()]);
            $this->View()->assign('success', false);
            $this->View()->assign('message', $message);
        }
    }

    public function refundDetailsAction()
    {
        $saleId = $this->Request()->getParam('id');
        $shopId = $this->Request()->getParam('shopId');

        $this->registerShopResource($shopId);

        /** @var RefundResource $refundResource */
        $refundResource = $this->container->get('paypal_unified.refund_resource');

        try {
            $this->View()->assign('details', $refundResource->get($saleId));
            $this->View()->assign('success', true);
        } catch (RequestException $ex) {
            $message = json_decode($ex->getBody(), true)['message'];
            $this->logger->error('Could not obtain refund details due to a communication failure', [$ex->getMessage(), $ex->getBody()]);
            $this->View()->assign('success', false);
            $this->View()->assign('message', $message);
        }
    }

    public function captureDetailsAction()
    {
        $captureId = $this->Request()->getParam('id');
        $shopId = $this->Request()->getParam('shopId');

        $this->registerShopResource($shopId);

        /** @var CaptureResource $captureResource */
        $captureResource = $this->container->get('paypal_unified.capture_resource');

        try {
            $this->View()->assign('details', $captureResource->get($captureId));
            $this->View()->assign('success', true);
        } catch (RequestException $ex) {
            $message = json_decode($ex->getBody(), true)['message'];
            $this->logger->error('Could not obtain capture details due to a communication failure', [$ex->getMessage(), $ex->getBody()]);
            $this->View()->assign('success', false);
            $this->View()->assign('message', $message);
        }
    }

    public function authorizationDetailsAction()
    {
        $authorizationId = $this->Request()->getParam('id');
        $shopId = $this->Request()->getParam('shopId');

        $this->registerShopResource($shopId);

        /** @var AuthorizationResource $authorizationResource */
        $authorizationResource = $this->container->get('paypal_unified.authorization_resource');

        try {
            $this->View()->assign('details', $authorizationResource->get($authorizationId));
            $this->View()->assign('success', true);
        } catch (RequestException $ex) {
            $message = json_decode($ex->getBody(), true)['message'];
            $this->logger->error('Could not obtain authorization details due to a communication failure', [$ex->getMessage(), $ex->getBody()]);
            $this->View()->assign('success', false);
            $this->View()->assign('message', $message);
        }
    }

    public function orderDetailsAction()
    {
        $orderId = $this->Request()->getParam('id');
        $shopId = $this->Request()->getParam('shopId');

        $this->registerShopResource($shopId);

        /** @var AuthorizationResource $orderResource */
        $orderResource = $this->container->get('paypal_unified.order_resource');

        try {
            $this->View()->assign('details', $orderResource->get($orderId));
            $this->View()->assign('success', true);
        } catch (RequestException $ex) {
            $message = json_decode($ex->getBody(), true)['message'];
            $this->logger->error('Could not obtain order details due to a communication failure', [$ex->getMessage(), $ex->getBody()]);
            $this->View()->assign('success', false);
            $this->View()->assign('message', $message);
        }
    }

    public function refundSaleAction()
    {
        $saleId = $this->Request()->getParam('id');
        $totalAmount = number_format($this->Request()->getParam('amount'), 2);
        $invoiceNumber = $this->Request()->getParam('invoiceNumber');
        $refundCompletely = $this->Request()->getParam('refundCompletely');
        $shopId = $this->Request()->getParam('shopId');

        try {
            $this->registerShopResource($shopId);

            /** @var SaleResource $saleResource */
            $saleResource = $this->container->get('paypal_unified.sale_resource');

            $refund = new SaleRefund();
            $refund->setInvoiceNumber($invoiceNumber);

            if (!$refundCompletely) {
                $amountStruct = new Amount();
                $amountStruct->setTotal($totalAmount);
                $amountStruct->setCurrency('EUR');

                $refund->setAmount($amountStruct);
            }

            $this->View()->assign('refund', $saleResource->refund($saleId, $refund));
            $this->View()->assign('success', true);
        } catch (RequestException $ex) {
            $message = json_decode($ex->getBody(), true)['message'];
            $this->logger->error('Could not refund sale due to a communication failure', [$ex->getMessage(), $ex->getBody()]);
            $this->View()->assign('success', false);
            $this->View()->assign('message', $message);
        }
    }

    public function captureOrderAction()
    {
        $shopId = $this->Request()->getParam('shopId');
        $authorizationId = $this->Request()->getParam('id');
        $amountToCapture = number_format($this->Request()->getParam('amount'), 2);
        $currency = $this->Request()->getParam('currency');
        $isFinal = $this->Request()->getParam('isFinal');

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

            $this->View()->assign('success', true);
        } catch (RequestException $ex) {
            $message = json_decode($ex->getBody(), true)['message'];
            $this->logger->error('Could not authorize payment due to a communication failure', [$ex->getMessage(), $ex->getBody()]);
            $this->View()->assign('success', false);
            $this->View()->assign('message', $message);
        }
    }

    public function captureAuthorizationAction()
    {
        $shopId = $this->Request()->getParam('shopId');
        $authorizationId = $this->Request()->getParam('id');
        $amountToCapture = number_format($this->Request()->getParam('amount'), 2);
        $currency = $this->Request()->getParam('currency');
        $isFinal = $this->Request()->getParam('isFinal');

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

            $this->View()->assign('success', true);
        } catch (RequestException $ex) {
            $message = json_decode($ex->getBody(), true)['message'];
            $this->logger->error('Could not authorize payment due to a communication failure', [$ex->getMessage(), $ex->getBody()]);
            $this->View()->assign('success', false);
            $this->View()->assign('message', $message);
        }
    }

    public function refundCaptureAction()
    {
        $id = $this->Request()->getParam('id');
        $totalAmount = number_format($this->Request()->getParam('amount'), 2);
        $description = $this->Request()->getParam('note');
        $shopId = $this->Request()->getParam('shopId');

        try {
            $this->registerShopResource($shopId);

            /** @var CaptureResource $captureResource */
            $captureResource = $this->container->get('paypal_unified.capture_resource');

            $refund = new CaptureRefund();
            $refund->setDescription($description);

            $amountStruct = new Amount();
            $amountStruct->setTotal($totalAmount);
            $amountStruct->setCurrency('EUR');
            $refund->setAmount($amountStruct);

            $this->View()->assign('refund', $captureResource->refund($id, $refund));
            $this->View()->assign('success', true);
        } catch (RequestException $ex) {
            $message = json_decode($ex->getBody(), true)['message'];
            $this->logger->error('Could not refund capture due to a communication failure', [$ex->getMessage(), $ex->getBody()]);
            $this->View()->assign('success', false);
            $this->View()->assign('message', $message);
        }
    }

    public function voidAuthorizationAction()
    {
        $id = $this->Request()->getParam('id');
        $shopId = $this->Request()->getParam('shopId');

        try {
            $this->registerShopResource($shopId);

            /** @var AuthorizationResource $authResource */
            $authResource = $this->get('paypal_unified.authorization_resource');
            $this->View()->assign('void', $authResource->void($id));
            $this->View()->assign('success', true);
        } catch (RequestException $ex) {
            $message = json_decode($ex->getBody(), true)['message'];
            $this->logger->error('Could not void authorization due to a communication failure', [$ex->getMessage(), $ex->getBody()]);
            $this->View()->assign('success', false);
            $this->View()->assign('message', $message);
        }
    }

    public function voidOrderAction()
    {
        $id = $this->Request()->getParam('id');
        $shopId = $this->Request()->getParam('shopId');

        try {
            $this->registerShopResource($shopId);

            /** @var OrderResource $orderResource */
            $orderResource = $this->get('paypal_unified.order_resource');
            $this->View()->assign('success', true);
            $this->View()->assign('void', $orderResource->void($id));
        } catch (RequestException $ex) {
            $message = json_decode($ex->getBody(), true)['message'];
            $this->logger->error('Could not void order due to a communication failure', [$ex->getMessage(), $ex->getBody()]);
            $this->View()->assign('success', false);
            $this->View()->assign('message', $message);
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

        return parent::getList($offset, $limit, $sort, $filter, $wholeParams);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilterConditions($filters, $model, $alias, $whiteList = [])
    {
        $conditions = parent::getFilterConditions(
            $filters,
            $model,
            $alias,
            $whiteList
        );

        //Ignore canceled or incomplete orders
        $conditions[] = [
            'property' => 'sOrder.number',
            'expression' => '!=',
            'value' => 0,
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
                    'orderStatus.description' => ['alias' => 'orderStatus.description', 'type' => 'string'],
                    'paymentStatus.description' => ['alias' => 'paymentStatus.description', 'type' => 'string'],
                ]
            );
        }

        return $fields;
    }

    /**
     * @param QueryBuilder $builder
     *
     * @return QueryBuilder
     */
    private function prepareOrderQueryBuilder(QueryBuilder $builder)
    {
        $paymentMethodProvider = new PaymentMethodProvider($this->container->get('models'));

        //If there was paypal classic installed earlier, we also have to query those orders.
        $legacyPaymentId = $this->get('paypal_unified.legacy_service')->getClassicPaymentId();
        if ($legacyPaymentId !== false) {
            $builder->innerJoin(
                'sOrder.payment',
                'payment',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'payment.id IN (' . $paymentMethodProvider->getPaymentId($this->get('dbal_connection')) . ', ' .
                $paymentMethodProvider->getPaymentId($this->get('dbal_connection'), PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME) . ', ' .
                $legacyPaymentId . ')'
            );
        } else {
            //No classic was installed, just query the unified orders
            $builder->innerJoin(
                'sOrder.payment',
                'payment',
                \Doctrine\ORM\Query\Expr\Join::WITH,
                'payment.id IN (' . $paymentMethodProvider->getPaymentId($this->get('dbal_connection')) . ', ' .
                $paymentMethodProvider->getPaymentId($this->get('dbal_connection'), PaymentMethodProvider::PAYPAL_INSTALLMENTS_PAYMENT_METHOD_NAME) . ')'
            );
        }

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
        /** @var \Shopware\Models\Shop\Repository $shopRepository */
        $shopRepository = $this->container->get('models')->getRepository(Shop::class);

        if ($shopId === null) {
            $shopId = $shopRepository->getActiveDefault()->getId();
        }

        $shopRepository->getActiveById($shopId)->registerResources();

        $this->container->get('paypal_unified.settings_service')->refreshDependencies();
    }

    /**
     * @param string $transactionId
     */
    private function prepareLegacyDetails($transactionId)
    {
        $this->View()->assign('success', true);
        $this->View()->assign('legacy', true);

        /** @var SaleResource $saleResource */
        $saleResource = $this->get('paypal_unified.sale_resource');
        $details = $saleResource->get($transactionId);
        $details['intent'] = 'sale';

        /** @var TransactionHistoryBuilderService $transactionHistoryBuilder */
        $transactionHistoryBuilder = $this->get('paypal_unified.transaction_history_builder_service');

        $this->View()->assign('history', $transactionHistoryBuilder->getLegacyHistory(Sale::fromArray($details)));
        $this->View()->assign('payment', $details);
    }

    /**
     * @param string $paymentId
     */
    private function prepareUnifiedDetails($paymentId)
    {
        /** @var PaymentResource $paymentResource */
        $paymentResource = $this->get('paypal_unified.payment_resource');
        $paymentDetails = $paymentResource->get($paymentId);

        /** @var TransactionHistoryBuilderService $transactionHistoryBuilder */
        $transactionHistoryBuilder = $this->get('paypal_unified.transaction_history_builder_service');

        $this->View()->assign('payment', $paymentDetails);
        $this->View()->assign('history', $transactionHistoryBuilder->getTransactionHistory($paymentDetails));

        if ($paymentDetails['intent'] === PaymentIntent::AUTHORIZE) {
            //Separately assign the data, to provide an easier usage
            $this->View()->assign('authorization', $paymentDetails['transactions'][0]['related_resources'][0]['authorization']);
        } elseif ($paymentDetails['intent'] === PaymentIntent::ORDER) {
            $this->View()->assign('order', $paymentDetails['transactions'][0]['related_resources'][0]['order']);
        } elseif ($paymentDetails['intent'] === PaymentIntent::SALE) {
            $this->View()->assign('sale', $paymentDetails['transactions'][0]['related_resources'][0]['sale']);
        }

        $this->View()->assign('success', true);
    }
}
