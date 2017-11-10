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

namespace SwagPaymentPayPalUnified\Tests\Mocks;

use Enlight_View_Default;

class ViewMock extends Enlight_View_Default
{
    /**
     * @var array
     */
    private $templates = [];

    /**
     * @var array
     */
    private $loadedTemplates = [];

    /**
     * @var array
     */
    private $assigns = [];

    /**
     * {@inheritdoc}
     */
    public function addTemplateDir($path)
    {
        $this->templates[] = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateDir()
    {
        return $this->templates;
    }

    /**
     * {@inheritdoc}
     */
    public function loadTemplate($path)
    {
        $this->loadedTemplates = $path;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function assign($spec, $value = null, $nocache = null, $scope = null)
    {
        if (is_array($spec)) {
            $this->assigns = $spec;

            return;
        }
        $this->assigns[$spec] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssign($spec = '')
    {
        if ($spec === '') {
            return $this->assigns;
        }

        return $this->assigns[$spec];
    }
}
