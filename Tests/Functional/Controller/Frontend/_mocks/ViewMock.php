<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\Controller\Frontend\_mocks;

use Enlight_View_Default;

class ViewMock extends Enlight_View_Default
{
    /**
     * @param string $template_name
     *
     * @return string
     */
    public function fetch($template_name)
    {
        return $template_name;
    }
}
