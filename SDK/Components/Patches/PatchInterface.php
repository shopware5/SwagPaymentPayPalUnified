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

namespace SwagPaymentPayPalUnified\SDK\Components\Patches;

interface PatchInterface
{
    const OPERATION_ADD = 'add';

    const OPERATION_REPLACE = 'replace';

    /**
     * Returns the value that should be transferred to PayPal
     *
     * @return string|array
     */
    public function getValue();

    /**
     * Returns the operation that should be triggered.
     *
     * @return string
     */
    public function getOperation();

    /**
     * Returns the path for the patch call.
     *
     * @return string
     */
    public function getPath();
}
