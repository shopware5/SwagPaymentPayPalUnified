<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
