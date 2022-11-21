<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Subscriber;

use DateTime;
use Doctrine\DBAL\Connection;
use Enlight\Event\SubscriberInterface;
use Enlight_Components_Session_Namespace as Session;
use Enlight_Controller_ActionEventArgs;
use Shopware_Components_Config as CoreConfig;
use Shopware_Controllers_Frontend_Checkout;
use SwagPaymentPayPalUnified\Components\DependencyProvider;
use SwagPaymentPayPalUnified\Components\PaymentMethodProviderInterface;
use SwagPaymentPayPalUnified\Components\Services\PhoneNumberService;

class PayUponInvoice implements SubscriberInterface
{
    const EMPTY_DATE = '0000-00-00';

    /**
     * @var DependencyProvider
     */
    private $dependencyProvider;

    /**
     * @var CoreConfig
     */
    private $config;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var PhoneNumberService
     */
    private $phoneNumberService;

    public function __construct(
        DependencyProvider $dependencyProvider,
        CoreConfig $config,
        Connection $connection,
        PhoneNumberService $phoneNumberService
    ) {
        $this->dependencyProvider = $dependencyProvider;
        $this->config = $config;
        $this->connection = $connection;
        $this->session = $this->dependencyProvider->getSession();
        $this->phoneNumberService = $phoneNumberService;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend_Checkout' => [
                ['onCheckout'],
                ['onFinish'],
            ],
        ];
    }

    /**
     * @return void
     */
    public function onCheckout(Enlight_Controller_ActionEventArgs $args)
    {
        /** @var Shopware_Controllers_Frontend_Checkout $subject */
        $subject = $args->getSubject();
        $actionName = $args->getRequest()->getActionName();

        if ($actionName === 'payment') {
            $dateOfBirth = $args->getRequest()->getParam('puiDateOfBirth');
            $phoneNumber = $args->getRequest()->getParam('puiTelephoneNumber');

            if (\is_array($dateOfBirth)) {
                $dateOfBirth = sprintf('%s-%s-%s', $dateOfBirth['year'], $dateOfBirth['month'], $dateOfBirth['day']);
            }

            $this->handleExtraData($dateOfBirth, $phoneNumber);
        }

        if ($actionName !== 'confirm') {
            return;
        }

        $paymentMethod = $subject->View()->getAssign('sPayment');
        if ($paymentMethod['name'] !== PaymentMethodProviderInterface::PAYPAL_UNIFIED_PAY_UPON_INVOICE_METHOD_NAME) {
            return;
        }

        $viewAssignVariables = [
            'showPayUponInvoiceLegalText' => true,
            'showPayUponInvoicePhoneField' => true,
            'showPayUponInvoiceBirthdayField' => true,
            'puiPhoneNumberWrong' => $args->getRequest()->getParam('puiPhoneNumberWrong'),
            'puiBirthdateWrong' => $args->getRequest()->getParam('puiBirthdateWrong'),
        ];

        $sOrderVariables = $this->session->offsetGet('sOrderVariables');

        $phoneNumber = $this->phoneNumberService->getValidPhoneNumberString($sOrderVariables['sUserData']['billingaddress']['phone']);
        if (\is_string($phoneNumber)) {
            $viewAssignVariables['payUponInvoicePhoneFieldValue'] = $phoneNumber;

            if ($sOrderVariables['sUserData']['billingaddress']['phone'] !== $phoneNumber) {
                // Update Session and Database entry if the validated phone number not equals the given phone number.
                $sOrderVariables['sUserData']['billingaddress']['phone'] = $phoneNumber;

                $this->session->offsetSet('sOrderVariables', $sOrderVariables);
                $this->phoneNumberService->savePhoneNumber($sOrderVariables['sUserData']['billingaddress']['id'], $phoneNumber);
            }
        }

        $birthday = $sOrderVariables['sUserData']['additional']['user']['birthday'];
        if (!empty($birthday) && $birthday !== self::EMPTY_DATE) {
            $isBirthdaySingleTextField = $this->config->get('birthdaySingleField');
            if (!$isBirthdaySingleTextField) {
                $birthday = explode('-', (new DateTime($birthday))->format('j-n-Y'));
            }

            $viewAssignVariables['payUponInvoiceBirthdayFieldValue'] = $birthday;
        }

        $subject->View()->assign($viewAssignVariables);
    }

    /**
     * @return void
     */
    public function onFinish(Enlight_Controller_ActionEventArgs $args)
    {
        if ($args->getRequest()->getActionName() !== 'finish') {
            return;
        }

        $extraField = $this->session->offsetGet('puiExtraFields');
        if ($extraField === null) {
            return;
        }

        $this->saveBirthdayAndPhoneNumberIfFieldsEditable(
            $extraField['customerId'],
            $extraField['billingAddressId'],
            $extraField['dateOfBirth'],
            $extraField['phoneNumber']
        );

        $this->session->offsetUnset('puiExtraFields');
    }

    /**
     * @param string $dateOfBirth
     * @param string $phoneNumber
     *
     * @return void
     */
    private function handleExtraData($dateOfBirth = null, $phoneNumber = null)
    {
        $sOrderVariables = $this->session->offsetGet('sOrderVariables');
        $customerData = $sOrderVariables['sUserData'];

        if ($dateOfBirth !== null) {
            $dateOfBirth = (new DateTime($dateOfBirth))->format('Y-m-d');

            $customerData['additional']['user']['birthday'] = $dateOfBirth;
        }

        if ($phoneNumber !== null) {
            $customerData['billingaddress']['phone'] = $phoneNumber;
        }

        $sOrderVariables['sUserData'] = $customerData;

        $this->session->offsetSet('sOrderVariables', $sOrderVariables);
        $this->session->offsetSet('puiExtraFields', [
            'customerId' => $customerData['additional']['user']['id'],
            'billingAddressId' => $customerData['billingaddress']['id'],
            'dateOfBirth' => $dateOfBirth,
            'phoneNumber' => $phoneNumber,
        ]);
    }

    /**
     * @param int         $customerId
     * @param int         $billingAddressId
     * @param string|null $dateOfBirth
     * @param string|null $phoneNumber
     *
     * @return void
     */
    private function saveBirthdayAndPhoneNumberIfFieldsEditable(
        $customerId,
        $billingAddressId,
        $dateOfBirth = null,
        $phoneNumber = null
    ) {
        $isBirthdayStoreable = $this->config->get('showbirthdayfield');
        if ($isBirthdayStoreable && $dateOfBirth !== null) {
            $this->connection->createQueryBuilder()->update('s_user')
                ->set('birthday', ':dateOfBirth')
                ->where('id = :customerId')
                ->setParameter('dateOfBirth', $dateOfBirth)
                ->setParameter('customerId', $customerId)
                ->execute();
        }

        if ($phoneNumber === null) {
            return;
        }

        $phoneNumber = $this->phoneNumberService->getValidPhoneNumberString($phoneNumber);
        if (!\is_string($phoneNumber)) {
            return;
        }

        $this->phoneNumberService->savePhoneNumber($billingAddressId, $phoneNumber);
    }
}
