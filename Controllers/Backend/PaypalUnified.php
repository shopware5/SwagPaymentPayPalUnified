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
use Shopware\Components\Logger;
use Shopware\Components\Model\QueryBuilder;
use Shopware\Models\Order\Order;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\Components\Services\SalesHistoryBuilderService;
use SwagPaymentPayPalUnified\SDK\Resources\PaymentResource;
use SwagPaymentPayPalUnified\SDK\Resources\RefundResource;
use SwagPaymentPayPalUnified\SDK\Resources\SaleResource;
use SwagPaymentPayPalUnified\SDK\Structs\Payment\Transactions\Amount;

class Shopware_Controllers_Backend_PaypalUnified extends Shopware_Controllers_Backend_Application
{
    /** @var string $model */
    protected $model = Order::class;

    /** @var string $alias */
    protected $alias = 'sOrder';

    /** @var Logger $logger */
    protected $logger;

    /** @var array $filterFields */
    protected $filterFields = [
        'number',
        'orderTime',
        'invoiceAmount',
        'customer.email',
        'orderStatus.description',
        'paymentStatus.description'
    ];

    /**
     * {@inheritdoc}
     */
    public function preDispatch()
    {
        $this->logger = $this->container->get('pluginlogger');
        parent::preDispatch();
    }

    /**
     * Handles the payment detail action.
     * It first requests the payment details from the PayPal API and assigns the whole object
     * to the response. Afterwards, it uses the paypal_unified.sales_history_builder_service service to
     * parse the sales history. The sales history is also being assigned to the response.
     *
     * @throws RequestException
     *
     */
    public function paymentDetailsAction()
    {
        $paymentId = $this->Request()->get('paymentId');

        /** @var PaymentResource $paymentResource */
        $paymentResource = $this->container->get('paypal_unified.payment_resource');

        try {
            $paymentDetails = $paymentResource->get($paymentId);

            /** @var SalesHistoryBuilderService $salesBuilder */
            $salesBuilder = $this->container->get('paypal_unified.sales_history_builder_service');

            $this->View()->assign('payment', $paymentDetails);
            $this->View()->assign('sales', $salesBuilder->getSalesHistory($paymentDetails));
        } catch (RequestException $ex) {
            $this->logger->error('PayPal Unified: Could not obtain payment details due to a communication failure', [$ex->getMessage(), $ex->getBody()]);
            throw $ex;
        }
    }

    /**
     * @throws RequestException
     */
    public function saleDetailsAction()
    {
        $saleId = $this->Request()->get('saleId');

        /** @var SaleResource $saleResource */
        $saleResource = $this->container->get('paypal_unified.sale_resource');

        try {
            $this->View()->assign('sale', $saleResource->get($saleId));
        } catch (RequestException $ex) {
            $this->logger->error('PayPal Unified: Could not obtain sale details due to a communication failure', [$ex->getMessage(), $ex->getBody()]);
            throw $ex;
        }
    }

    /**
     * @throws RequestException
     */
    public function refundDetailsAction()
    {
        $saleId = $this->Request()->get('refundId');

        /** @var RefundResource $refundResource */
        $refundResource = $this->container->get('paypal_unified.refund_resource');

        try {
            $this->View()->assign('refund', $refundResource->get($saleId));
        } catch (RequestException $ex) {
            $this->logger->error('PayPal Unified: Could not obtain refund details due to a communication failure', [$ex->getMessage(), $ex->getBody()]);
            throw $ex;
        }
    }

    /**
     * @throws RequestException
     */
    public function refundSaleAction()
    {
        $saleId = $this->Request()->get('saleId');
        $totalAmount = $this->Request()->get('amount');
        $invoiceNumber = $this->Request()->get('invoiceNumber');
        $refundCompletely = $this->Request()->get('refundCompletely');

        /** @var SaleResource $saleResource */
        $saleResource = $this->container->get('paypal_unified.sale_resource');

        try {
            if (!$refundCompletely) {
                $amountStruct = new Amount();
                $amountStruct->setTotal($totalAmount);
                $amountStruct->setCurrency('EUR');

                $this->View()->assign('refund', $saleResource->refund($saleId, $amountStruct, $invoiceNumber));
            } else {
                $this->View()->assign('refund', $saleResource->refund($saleId, null, $invoiceNumber));
            }
        } catch (RequestException $ex) {
            $this->logger->error('PayPal Unified: Could not refund sale due to a communication failure', [$ex->getMessage(), $ex->getBody()]);
            throw $ex;
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
                'direction' => 'DESC'
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
            'value' => 0
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
                    'customer.email' => [ 'alias' => 'customer.email', 'type' => 'string' ],
                    'orderStatus.description' => ['alias' => 'orderStatus.description', 'type' => 'string'],
                    'paymentStatus.description' => ['alias' => 'paymentStatus.description', 'type' => 'string']
                ]
            );
        }

        return $fields;
    }

    /**
     * @param QueryBuilder $builder
     * @return QueryBuilder
     */
    private function prepareOrderQueryBuilder(QueryBuilder $builder)
    {
        $paymentMethodProvider = new PaymentMethodProvider($this->container->get('models'));

        $builder->innerJoin(
            'sOrder.payment',
            'payment',
            \Doctrine\ORM\Query\Expr\Join::WITH,
            'payment.id = ' . $paymentMethodProvider->getPaymentId($this->get('dbal_connection'))
        )
            ->leftJoin('sOrder.shop', 'shop')
            ->leftJoin('sOrder.customer', 'customer')
            ->leftJoin('sOrder.orderStatus', 'orderStatus')
            ->leftJoin('sOrder.paymentStatus', 'paymentStatus')

            ->addSelect('shop')
            ->addSelect('payment')
            ->addSelect('customer')
            ->addSelect('orderStatus')
            ->addSelect('paymentStatus');

        return $builder;
    }
}
