<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\ExpressCheckout;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Enlight_Controller_Front;
use Enlight_Controller_Request_Request;
use Shopware\Bundle\AccountBundle\Form\Account\AddressFormType;
use Shopware\Bundle\AccountBundle\Form\Account\PersonalFormType;
use Shopware\Bundle\AccountBundle\Service\RegisterServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Shopware_Components_Config as ShopwareConfig;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\PayPalBundle\Components\LoggerServiceInterface;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order;
use SwagPaymentPayPalUnified\PayPalBundle\V2\Api\Order\PurchaseUnit\Shipping;
use Symfony\Component\Form\FormFactoryInterface;
use UnexpectedValueException;

class CustomerService
{
    const NOT_DEFINED_SALUTATION = 'not_defined';

    const MR_SALUTATION = 'mr';

    /**
     * @var ShopwareConfig
     */
    private $shopwareConfig;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    /**
     * @var RegisterServiceInterface
     */
    private $registerService;

    /**
     * @var PaymentMethodProviderInterface
     */
    private $paymentMethodProvider;

    /**
     * @var Enlight_Controller_Front
     */
    private $front;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var LoggerServiceInterface
     */
    private $logger;

    /**
     * @var ModelManager
     */
    private $modelManager;

    public function __construct(
        ShopwareConfig $shopwareConfig,
        ModelManager $modelManager,
        FormFactoryInterface $formFactory,
        ContextServiceInterface $contextService,
        RegisterServiceInterface $registerService,
        Enlight_Controller_Front $front,
        DependencyProvider $dependencyProvider,
        PaymentMethodProviderInterface $paymentMethodProvider,
        LoggerServiceInterface $logger
    ) {
        $this->shopwareConfig = $shopwareConfig;
        $this->modelManager = $modelManager;
        $this->formFactory = $formFactory;
        $this->contextService = $contextService;
        $this->registerService = $registerService;
        $this->front = $front;
        $this->dependencyProvider = $dependencyProvider;
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->logger = $logger;

        $this->connection = $this->modelManager->getConnection();
    }

    /**
     * @return void
     */
    public function upsertCustomer(Order $orderStruct)
    {
        $this->logger->debug(sprintf('%s CREATE OR UPDATE CUSTOMER FOR PAYPAL EXPRESS ORDER WITH ID: %s', __METHOD__, $orderStruct->getId()));

        $payer = $orderStruct->getPayer();

        $customerModel = $this->getCustomerByPayerId($payer->getPayerId());
        if (!$customerModel instanceof Customer) {
            $this->logger->debug(sprintf('%s NO CUSTOMER FOUND WITH PAYER-ID: %s CONTINUE WITH CREATING A NEW CUSTOMER', __METHOD__, $payer->getPayerId()));

            $this->createNewCustomer($orderStruct);

            return;
        }

        $address = $customerModel->getDefaultBillingAddress();
        if (!$address instanceof Address) {
            $this->logger->debug(sprintf('%s CUSTOMER WITH ID: %s HAS NO ADDRESS.', __METHOD__, $customerModel->getId()));

            return;
        }

        $customerData = $this->createCustomerData($orderStruct);
        if (!\is_array($customerData)) {
            return;
        }

        $customerModel->setEmail($payer->getEmailAddress());
        $customerModel->setPaymentId($this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME));
        $addressForm = $this->formFactory->create(AddressFormType::class, $address);
        $addressForm->submit($customerData);

        $this->logger->debug(sprintf('%s UPDATE ADDRESS WITH ID: %s FOR QUICK ORDERER: %s', __METHOD__, $address->getId(), $customerModel->getId()));

        $this->modelManager->persist($customerModel);
        $this->modelManager->persist($address);
        $this->modelManager->flush();

        $this->logger->debug(sprintf('%s LOG IN EXISTING QUICK ORDERER WITH ID: %s', __METHOD__, $customerModel->getId()));

        $this->loginCustomer($customerModel);
    }

    /**
     * @deprecated in 6.0.2, will be private in 7.0.0. Use CustomerService::upsertCustomer instead.
     */
    public function createNewCustomer(Order $orderStruct)
    {
        $customerData = $this->createCustomerData($orderStruct);
        if (!\is_array($customerData)) {
            return;
        }

        $customerModel = $this->registerCustomer($customerData);
        $this->addIdentifierToCustomerAttribute($customerModel->getId(), $orderStruct->getPayer()->getPayerId());

        $this->logger->debug(sprintf('%s NEW CUSTOMER CREATED WITH ID: %s', __METHOD__, $customerModel->getId()));

        $this->loginCustomer($customerModel);
    }

    /**
     * @return array<string,mixed>|null
     */
    private function createCustomerData(Order $orderStruct)
    {
        $payer = $orderStruct->getPayer();
        if (!empty($orderStruct->getPurchaseUnits())) {
            $shipping = $orderStruct->getPurchaseUnits()[0]->getShipping();
            if (!$shipping instanceof Shipping) {
                $this->logger->error(sprintf('%s COULD NOT CREATE CUSTOMER. ADDRESS IS MISSING', __METHOD__));

                return null;
            }

            $address = $shipping->getAddress();

            $customerNameResult = $this->getCustomerNameResult($shipping->getName()->getFullName());
        } else {
            $address = $payer->getAddress();

            $customerNameResult = new CustomerNameResult(
                $payer->getName()->getGivenName(),
                $payer->getName()->getSurname()
            );
        }

        $salutation = $this->getSalutation();
        $countryId = $this->getCountryId($address->getCountryCode());
        $phone = $payer->getPhone();
        $stateId = null;

        if (\is_string($address->getAdminArea1())) {
            $stateId = $this->getStateId($countryId, $address->getAdminArea1());
        }

        return [
            'email' => $payer->getEmailAddress(),
            'password' => $payer->getPayerId(),
            'accountmode' => 1,
            'salutation' => $salutation,
            'firstname' => $customerNameResult->getFirstName(),
            'lastname' => $customerNameResult->getLastName(),
            'street' => $address->getAddressLine1(),
            'additionalAddressLine1' => $address->getAddressLine2(),
            'zipcode' => $address->getPostalCode(),
            'city' => $address->getAdminArea2(),
            'country' => $countryId,
            'state' => $stateId,
            'phone' => $phone !== null ? $phone->getPhoneNumber()->getNationalNumber() : null,
        ];
    }

    /**
     * @param string $countryCode
     *
     * @return int
     */
    private function getCountryId($countryCode)
    {
        $sql = 'SELECT id FROM s_core_countries WHERE countryiso=:countryCode';

        return (int) $this->connection->fetchColumn($sql, ['countryCode' => $countryCode]);
    }

    /**
     * @param int    $countryId
     * @param string $stateCode
     *
     * @return int
     */
    private function getStateId($countryId, $stateCode)
    {
        $sql = 'SELECT id FROM s_core_countries_states WHERE countryID=:countryId AND shortcode=:stateCode';

        return (int) $this->connection->fetchColumn($sql, [':countryId' => $countryId, 'stateCode' => $stateCode]);
    }

    /**
     * @return Customer
     */
    private function registerCustomer(array $customerData)
    {
        $customer = new Customer();
        $personalForm = $this->formFactory->create(PersonalFormType::class, $customer);
        $personalForm->submit($customerData);

        $customer->setPaymentId($this->paymentMethodProvider->getPaymentId(PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAYMENT_METHOD_NAME));

        $address = new Address();
        $addressForm = $this->formFactory->create(AddressFormType::class, $address);
        $addressForm->submit($customerData);

        $context = $this->contextService->getShopContext();
        $shop = $context->getShop();

        $this->registerService->register($shop, $customer, $address);

        return $customer;
    }

    /**
     * @return string
     */
    private function getSalutation()
    {
        $possibleSalutationsString = $this->shopwareConfig->get('shopsalutations');
        if (!\is_string($possibleSalutationsString) || $possibleSalutationsString === '') {
            return self::MR_SALUTATION;
        }

        $possibleSalutationsArray = \explode(',', $possibleSalutationsString);
        if (\in_array(self::NOT_DEFINED_SALUTATION, $possibleSalutationsArray)) {
            return self::NOT_DEFINED_SALUTATION;
        }

        // as PayPal does not provide a salutation, we have to set one of the possible options
        return isset($possibleSalutationsArray[0]) ? $possibleSalutationsArray[0] : self::MR_SALUTATION;
    }

    private function loginCustomer(Customer $customerModel)
    {
        $this->logger->debug(sprintf('%s LOGIN CUSTOMER WITH ID: %s', __METHOD__, $customerModel->getId()));

        $request = $this->front->Request();

        if (!$request instanceof Enlight_Controller_Request_Request) {
            $this->logger->debug(sprintf('%s NO REQUEST GIVEN', __METHOD__));
            throw new UnexpectedValueException(sprintf('Expected instance of %s, got null', Enlight_Controller_Request_Request::class));
        }

        $request->setPost('email', $customerModel->getEmail());
        $request->setPost('passwordMD5', $customerModel->getPassword());
        $this->dependencyProvider->getModule('admin')->sLogin(true);

        // Set country and area to session, so the cart will be calculated correctly,
        // e.g. the country changed and has different taxes
        $session = $this->dependencyProvider->getSession();
        $customerShippingCountry = $customerModel->getDefaultShippingAddress()->getCountry();
        $session->offsetSet('sCountry', $customerShippingCountry->getId());
        $session->offsetSet('sArea', $customerShippingCountry->getArea()->getId());

        $this->logger->debug(sprintf('%s CUSTOMER WITH ID: %s SUCCESSFUL LOGGED IN', __METHOD__, $customerModel->getId()));
    }

    /**
     * @param int    $customerId
     * @param string $identifier
     *
     * @return void
     */
    private function addIdentifierToCustomerAttribute($customerId, $identifier)
    {
        $this->connection->createQueryBuilder()
            ->update('s_user_attributes')
            ->set('swag_paypal_unified_payer_id', ':identifier')
            ->where('userID = :customerId')
            ->setParameter('identifier', $identifier)
            ->setParameter('customerId', $customerId)
            ->execute();
    }

    /**
     * @param string $payerId
     *
     * @return Customer|null
     */
    private function getCustomerByPayerId($payerId)
    {
        $customer = $this->modelManager->createQueryBuilder()
            ->select(['customer'])
            ->from(Customer::class, 'customer')
            ->innerJoin('customer.attribute', 'attributes')
            ->where('attributes.swagPaypalUnifiedPayerId = :payerId')
            ->setParameter('payerId', $payerId)
            ->getQuery()
            ->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);

        if (!$customer instanceof Customer) {
            return null;
        }

        return $customer;
    }

    /**
     * @param string $fullName
     *
     * @return CustomerNameResult
     */
    private function getCustomerNameResult($fullName)
    {
        $fullNameArray = \explode(' ', $fullName);

        if (\count($fullNameArray) > 1) {
            return new CustomerNameResult(\array_shift($fullNameArray), \implode(' ', $fullNameArray));
        }

        return new CustomerNameResult($fullName, $fullName);
    }
}
