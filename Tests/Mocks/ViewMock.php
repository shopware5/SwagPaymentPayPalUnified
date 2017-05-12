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
     * @param string $path
     */
    public function addTemplateDir($path)
    {
        $this->templates[] = $path;
    }

    /**
     * @return array
     */
    public function getTemplateDir()
    {
        return $this->templates;
    }

    /**
     * @param string $path
     *
     * @return Enlight_View_Default
     */
    public function loadTemplate($path)
    {
        $this->loadedTemplates = $path;

        return $this;
    }

    /**
     * @return array
     */
    public function getLoadedTemplates()
    {
        return $this->loadedTemplates;
    }

    public function assign($spec, $value = null, $nocache = null, $scope = null)
    {
        $this->assigns[$spec] = $value;
    }

    /**
     * @param string $spec
     *
     * @return mixed
     */
    public function getAssign($spec = '')
    {
        return $this->assigns[$spec];
    }
}
