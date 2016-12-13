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

namespace SwagPaymentPayPalUnified\Components\Structs\Payment;

use SwagPaymentPayPalUnified\Components\Structs\Basket\Payer;
use SwagPaymentPayPalUnified\Components\Structs\Basket\RedirectUrls;
use SwagPaymentPayPalUnified\Components\Structs\Basket\Transactions;

class Payment
{
    /**
     * @var string $intent
     */
    private $intent;

    /**
     * @var integer $experienceProfileId
     */
    private $experienceProfileId;

    /**
     * @var Payer $payer
     */
    private $payer;

    /**
     * @var Transactions $transactions
     */
    private $transactions;

    /**
     * @var RedirectUrls $redirectUrls
     */
    private $redirectUrls;

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
     * @return integer
     */
    public function getProfile()
    {
        return $this->experienceProfileId;
    }

    /**
     * @param integer $experienceProfileId
     */
    public function setProfile($experienceProfileId)
    {
        $this->experienceProfileId = $experienceProfileId;
    }

    /**
     * @return Payer
     */
    public function getPayer()
    {
        return $this->payer;
    }

    /**
     * @param Payer $payer
     */
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

    /**
     * @param Transactions $transactions
     */
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

    /**
     * @param RedirectUrls $redirectUrls
     */
    public function setRedirectUrls(RedirectUrls $redirectUrls)
    {
        $this->redirectUrls = $redirectUrls;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'intent' => $this->getIntent(),
            'experience_profile_id' => $this->getProfile(),
            'payer' => $this->getPayer()->toArray(),
            'transactions' => [
                $this->getTransactions()->toArray()
            ],
            'redirect_urls' => $this->getRedirectUrls()->toArray()
        ];
    }
}
