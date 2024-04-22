<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Components\TransactionReport;

class ReportResult
{
    const PRECISION = 2;

    /**
     * @var array<string, array<int, array{'id': int, 'invoice_amount': float}>>
     */
    private $ordersToReport;

    /**
     * @var array<string, array<int, int>>
     */
    private $orderIds = [];

    /**
     * @var array<string, float>
     */
    private $turnover = [];

    /**
     * @var array<int, string>
     */
    private $currencies = [];

    /**
     * @param array<string, array<int, array{'id': int, 'invoice_amount': float}>> $ordersToReport
     */
    public function __construct(array $ordersToReport)
    {
        $this->ordersToReport = $ordersToReport;
        $this->init();
    }

    /**
     * @return array<int, string>
     */
    public function getCurrencies()
    {
        return $this->currencies;
    }

    /**
     * @param string $currency
     *
     * @return float
     */
    public function getTurnover($currency)
    {
        if (!\array_key_exists($currency, $this->turnover)) {
            return 0.0;
        }

        return round($this->turnover[$currency], self::PRECISION, \PHP_ROUND_HALF_UP);
    }

    /**
     * @param string $currency
     *
     * @return array<int, int>
     */
    public function getOrderIds($currency)
    {
        if (!\array_key_exists($currency, $this->orderIds)) {
            return [];
        }

        return $this->orderIds[$currency];
    }

    /**
     * @return void
     */
    private function init()
    {
        foreach ($this->ordersToReport as $currency => $orders) {
            $this->currencies[] = $currency;
            $this->orderIds[$currency] = [];
            $this->turnover[$currency] = 0.0;
            foreach ($orders as $order) {
                $this->turnover[$currency] += (float) $order['invoice_amount'];
                $this->orderIds[$currency][] = $order['id'];
            }
        }
    }
}
