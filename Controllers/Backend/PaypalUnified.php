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

 use Shopware\Components\Model\QueryBuilder;
 use Shopware\Models\Order\Order;
 use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;

 class Shopware_Controllers_Backend_PaypalUnified extends Shopware_Controllers_Backend_Application
 {
     /** @var string $model */
     protected $model = Order::class;

     /** @var string $alias */
     protected $alias = 'sOrder';

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
     protected function getFilterConditions($filters, $model, $alias, $whiteList = array())
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
             "payment.id = " . $paymentMethodProvider->getPaymentMethodModel()->getId()
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
 }
