<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Common\Link;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\ApplicationContext;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Payer;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\PaymentInstruction;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\RedirectUrls;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Payment\Transactions;

class Payment
{
    /**
     * @var string
     */
    private $intent;

    /**
     * @var Payer|null
     */
    private $payer;

    /**
     * @var Transactions
     */
    private $transactions;

    /**
     * @var RedirectUrls
     */
    private $redirectUrls;

    /**
     * @var Link[]
     */
    private $links;

    /**
     * @var PaymentInstruction|null
     */
    private $paymentInstruction;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $cart;

    /**
     * @var string
     */
    private $createTime;

    /**
     * @var string
     */
    private $updateTime;

    /**
     * @var ApplicationContext
     */
    private $applicationContext;

    /**
     * @return string
     */
    public function getIntent()
    {
        return $this->intent;
    }

    /**
     * @param string $intent
     */
    public function setIntent($intent)
    {
        $this->intent = $intent;
    }

    /**
     * @return Payer|null
     */
    public function getPayer()
    {
        return $this->payer;
    }

    public function setPayer(Payer $payer)
    {
        $this->payer = $payer;
    }

    /**
     * @return Transactions
     */
    public function getTransactions()
    {
        return $this->transactions;
    }

    public function setTransactions(Transactions $transactions)
    {
        $this->transactions = $transactions;
    }

    /**
     * @return RedirectUrls
     */
    public function getRedirectUrls()
    {
        return $this->redirectUrls;
    }

    public function setRedirectUrls(RedirectUrls $redirectUrls)
    {
        $this->redirectUrls = $redirectUrls;
    }

    /**
     * @return Link[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param Link[] $links
     */
    public function setLinks(array $links)
    {
        $this->links = $links;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param string $cart
     */
    public function setCart($cart)
    {
        $this->cart = $cart;
    }

    /**
     * @return string
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * @param string $createTime
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;
    }

    /**
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->updateTime;
    }

    /**
     * @param string $updateTime
     */
    public function setUpdateTime($updateTime)
    {
        $this->updateTime = $updateTime;
    }

    /**
     * @return PaymentInstruction|null
     */
    public function getPaymentInstruction()
    {
        return $this->paymentInstruction;
    }

    public function setPaymentInstruction(PaymentInstruction $paymentInstruction)
    {
        $this->paymentInstruction = $paymentInstruction;
    }

    /**
     * @return ApplicationContext
     */
    public function getApplicationContext()
    {
        return $this->applicationContext;
    }

    public function setApplicationContext(ApplicationContext $applicationContext)
    {
        $this->applicationContext = $applicationContext;
    }

    /**
     * @return Payment
     */
    public static function fromArray(array $data = [])
    {
        $result = new self();

        $result->setIntent($data['intent']);
        $result->setCart($data['cart']);
        $result->setId($data['id']);
        $result->setState($data['state']);
        $result->setCreateTime($data['create_time']);
        $result->setUpdateTime($data['update_time']);

        if (\array_key_exists('amount', $data['transactions'][0])) {
            $result->setTransactions(Transactions::fromArray($data['transactions'][0]));
        } else {
            $result->setTransactions(Transactions::fromArray($data['transactions']));
        }

        if (\array_key_exists('payment_instruction', $data)) {
            $result->setPaymentInstruction(PaymentInstruction::fromArray($data['payment_instruction']));
        }

        if (\array_key_exists('payer', $data)) {
            $result->setPayer(Payer::fromArray($data['payer']));
        }

        $links = [];
        foreach ($data['links'] as $link) {
            $links[] = Link::fromArray($link);
        }
        $result->setLinks($links);

        $result->setRedirectUrls(RedirectUrls::fromArray($data['redirect_urls']));

        return $result;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'intent' => $this->getIntent(),
            'application_context' => $this->getApplicationContext()->toArray(),
            'payer' => $this->getPayer()->toArray(),
            'transactions' => [
                $this->getTransactions()->toArray(),
            ],
            'redirect_urls' => $this->getRedirectUrls()->toArray(),
            'create_time' => $this->getCreateTime(),
            'update_time' => $this->getUpdateTime(),
            'id' => $this->getId(),
            'cart' => $this->getCart(),
            'state' => $this->getState(),
        ];
    }
}
