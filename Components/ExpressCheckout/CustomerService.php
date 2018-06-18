<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\ExpressCheckout;

use Doctrine\DBAL\Connection;
use sAdmin;
use Shopware\Bundle\AccountBundle\Form\Account\AddressFormType;
use Shopware\Bundle\AccountBundle\Form\Account\PersonalFormType;
use Shopware\Bundle\AccountBundle\Service\RegisterServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Models\Customer\Address;
use Shopware\Models\Customer\Customer;
use Shopware_Components_Config as ShopwareConfig;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProvider;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment;
use Symfony\Component\Form\FormFactoryInterface;

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
     * @var PaymentMethodProvider
     */
    private $paymentMethodProvider;

    /**
     * @var \Enlight_Controller_Front
     */
    private $front;

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var sAdmin
     */
    private $adminModule;

    /**
     * @param ShopwareConfig            $shopwareConfig
     * @param Connection                $connection
     * @param FormFactoryInterface      $formFactory
     * @param ContextServiceInterface   $contextService
     * @param RegisterServiceInterface  $registerService
     * @param \Enlight_Controller_Front $front
     * @param DependencyProvider        $dependencyProvider
     */
    public function __construct(
        ShopwareConfig $shopwareConfig,
        Connection $connection,
        FormFactoryInterface $formFactory,
        ContextServiceInterface $contextService,
        RegisterServiceInterface $registerService,
        \Enlight_Controller_Front $front,
        DependencyProvider $dependencyProvider
    ) {
        $this->shopwareConfig = $shopwareConfig;
        $this->connection = $connection;
        $this->formFactory = $formFactory;
        $this->contextService = $contextService;
        $this->registerService = $registerService;
        $this->paymentMethodProvider = new PaymentMethodProvider();
        $this->front = $front;
        $this->dependencyProvider = $dependencyProvider;
    }

    /**
     * @param Payment $paymentStruct
     */
    public function createNewCustomer(Payment $paymentStruct)
    {
        $this->adminModule = $this->dependencyProvider->getModule('admin');

        $payerInfo = $paymentStruct->getPayer()->getPayerInfo();
        $salutation = $this->getSalutation();
        $address = $payerInfo->getBillingAddress();
        $countryId = $this->getCountryId($address->getCountryCode());
        $stateId = null;
        if ($address->getState()) {
            $stateId = $this->getStateId($countryId, $address->getState());
        }

        $customerData = [
            'email' => $payerInfo->getEmail(),
            'password' => $payerInfo->getPayerId(),
            'accountmode' => 1,
            'salutation' => $salutation,
            'firstname' => $payerInfo->getFirstName(),
            'lastname' => $payerInfo->getLastName(),
            'street' => $address->getLine1(),
            'additionalAddressLine1' => $address->getLine2(),
            'zipcode' => $address->getPostalCode(),
            'city' => $address->getCity(),
            'country' => $countryId,
            'stateID' => $stateId,
            'phone' => $payerInfo->getPhone(),
        ];

        $customerModel = $this->registerCustomer($customerData);

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
     * @param array $customerData
     *
     * @return Customer
     */
    private function registerCustomer(array $customerData)
    {
        $customer = new Customer();
        $form = $this->formFactory->create(PersonalFormType::class, $customer);
        $form->submit($customerData);

        $customer->setPaymentId($this->paymentMethodProvider->getPaymentId($this->connection));

        $address = new Address();
        $form = $this->formFactory->create(AddressFormType::class, $address);
        $form->submit($customerData);

        /** @var ShopContextInterface $context */
        $context = $this->contextService->getShopContext();

        /** @var Shop $shop */
        $shop = $context->getShop();

        $this->registerService->register($shop, $customer, $address, $address);

        return $customer;
    }

    /**
     * @return string
     */
    private function getSalutation()
    {
        $possibleSalutations = $this->shopwareConfig->get('shopsalutations');
        $possibleSalutations = explode(',', $possibleSalutations);

        // as PayPal does not provide a salutation, we have to set one of the possible options
        return isset($possibleSalutations[0]) ? $possibleSalutations[0] : 'mr';
    }

    /**
     * @param Customer $customerModel
     */
    private function loginCustomer(Customer $customerModel)
    {
        $request = $this->front->Request();
        $request->setPost('email', $customerModel->getEmail());
        $request->setPost('passwordMD5', $customerModel->getPassword());
        $this->adminModule->sLogin(true);
    }
}
