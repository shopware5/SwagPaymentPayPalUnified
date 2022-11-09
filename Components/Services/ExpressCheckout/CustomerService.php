<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\Services\ExpressCheckout;

use Doctrine\DBAL\Connection;
use Enlight_Controller_Front;
use Enlight_Controller_Request_Request;
use Shopware\Bundle\AccountBundle\Form\Account\AddressFormType;
use Shopware\Bundle\AccountBundle\Form\Account\PersonalFormType;
use Shopware\Bundle\AccountBundle\Service\RegisterServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
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

    public function __construct(
        ShopwareConfig $shopwareConfig,
        Connection $connection,
        FormFactoryInterface $formFactory,
        ContextServiceInterface $contextService,
        RegisterServiceInterface $registerService,
        Enlight_Controller_Front $front,
        DependencyProvider $dependencyProvider,
        PaymentMethodProviderInterface $paymentMethodProvider,
        LoggerServiceInterface $logger
    ) {
        $this->shopwareConfig = $shopwareConfig;
        $this->connection = $connection;
        $this->formFactory = $formFactory;
        $this->contextService = $contextService;
        $this->registerService = $registerService;
        $this->front = $front;
        $this->dependencyProvider = $dependencyProvider;
        $this->paymentMethodProvider = $paymentMethodProvider;
        $this->logger = $logger;
    }

    public function createNewCustomer(Order $orderStruct)
    {
        $payer = $orderStruct->getPayer();
        if (!empty($orderStruct->getPurchaseUnits())) {
            $shipping = $orderStruct->getPurchaseUnits()[0]->getShipping();
            if (!$shipping instanceof Shipping) {
                $this->logger->error(sprintf('%s COULD NOT CREATE CUSTOMER. ADDRESS IS MISSING', __METHOD__));

                return;
            }
            $address = $shipping->getAddress();
            $names = \explode(' ', $shipping->getName()->getFullName());
            $firstName = \array_shift($names);
            $lastName = \implode(' ', $names);
        } else {
            $address = $payer->getAddress();
            $firstName = $payer->getName()->getGivenName();
            $lastName = $payer->getName()->getSurname();
        }

        $salutation = $this->getSalutation();
        $countryId = $this->getCountryId($address->getCountryCode());
        $phone = $payer->getPhone();
        $stateId = null;

        if (\is_string($address->getAdminArea1())) {
            $stateId = $this->getStateId($countryId, $address->getAdminArea1());
        }

        $customerData = [
            'email' => $payer->getEmailAddress(),
            'password' => $payer->getPayerId(),
            'accountmode' => 1,
            'salutation' => $salutation,
            'firstname' => $firstName,
            'lastname' => $lastName,
            'street' => $address->getAddressLine1(),
            'additionalAddressLine1' => $address->getAddressLine2(),
            'zipcode' => $address->getPostalCode(),
            'city' => $address->getAdminArea2(),
            'country' => $countryId,
            'state' => $stateId,
            'phone' => $phone !== null ? $phone->getPhoneNumber()->getNationalNumber() : null,
        ];

        $customerModel = $this->registerCustomer($customerData);

        $this->logger->debug(sprintf('%s NEW CUSTOMER CREATED WITH ID: %s', __METHOD__, $customerModel->getId()));

        $this->loginCustomer($customerModel);
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
        $possibleSalutations = $this->shopwareConfig->get('shopsalutations');
        $possibleSalutations = \explode(',', $possibleSalutations);

        // as PayPal does not provide a salutation, we have to set one of the possible options
        return isset($possibleSalutations[0]) ? $possibleSalutations[0] : 'mr';
    }

    private function loginCustomer(Customer $customerModel)
    {
        $this->logger->debug(sprintf('%s LOGIN NEW CUSTOMER WITH ID: %s', __METHOD__, $customerModel->getId()));

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

        $this->logger->debug(sprintf('%s NEW CUSTOMER WITH ID: %s SUCCESSFUL LOGGED IN', __METHOD__, $customerModel->getId()));
    }
}
