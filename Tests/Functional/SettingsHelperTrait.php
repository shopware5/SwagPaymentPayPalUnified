<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional;

use Shopware\Components\Model\ModelManager;
use SwagPaymentPayPalUnified\Models\Settings\ExpressCheckout as ExpressSettingsModel;
use SwagPaymentPayPalUnified\Models\Settings\General as GeneralSettingsModel;
use SwagPaymentPayPalUnified\Models\Settings\Installments as InstallmentsSettingsModel;
use SwagPaymentPayPalUnified\Models\Settings\Plus as PlusSettingsModel;

trait SettingsHelperTrait
{
    /**
     * @param GeneralSettingsModel $model
     */
    public function insertGeneralSettings(GeneralSettingsModel $model)
    {
        $em = $this->getEntityManager();
        $em->persist($model);
        $em->flush();
    }

    /**
     * @param array $data
     */
    public function insertGeneralSettingsFromArray(array $data)
    {
        if (empty($data['showSidebarLogo'])) {
            $data['showSidebarLogo'] = false;
        }

        if (empty($data['useInContext'])) {
            $data['useInContext'] = false;
        }

        if (empty($data['sendOrderNumber'])) {
            $data['sendOrderNumber'] = false;
        }

        if (empty($data['logLevel'])) {
            $data['logLevel'] = 1;
        }

        if (empty($data['displayErrors'])) {
            $data['displayErrors'] = false;
        }

        if (empty($data['advertiseReturns'])) {
            $data['advertiseReturns'] = false;
        }

        if (empty($data['brandName'])) {
            $data['brandName'] = 'TestBrandName';
        }

        $model = new GeneralSettingsModel();
        $model->fromArray($data);

        $this->insertGeneralSettings($model);
    }

    /**
     * @param InstallmentsSettingsModel $model
     */
    public function insertInstallmentsSettings(InstallmentsSettingsModel $model)
    {
        $em = $this->getEntityManager();
        $em->persist($model);
        $em->flush();
    }

    /**
     * @param array $data
     */
    public function insertInstallmentsSettingsFromArray(array $data)
    {
        if (empty($data['shopId'])) {
            $data['shopId'] = 1;
        }

        if (empty($data['showLogo'])) {
            $data['showLogo'] = 0;
        }

        if (empty($data['intent'])) {
            $data['intent'] = 0;
        }

        $model = new InstallmentsSettingsModel();
        $this->insertInstallmentsSettings($model->fromArray($data));
    }

    /**
     * @param PlusSettingsModel $model
     */
    public function insertPlusSettings(PlusSettingsModel $model)
    {
        $em = $this->getEntityManager();
        $em->persist($model);
        $em->flush();
    }

    /**
     * @param array $data
     */
    public function insertPlusSettingsFromArray(array $data)
    {
        if (empty($data['shopId'])) {
            $data['shopId'] = 1;
        }

        if (empty($data['restyle'])) {
            $data['restyle'] = 0;
        }

        if (empty($data['integrateThirdPartyMethods'])) {
            $data['integrateThirdPartyMethods'] = 0;
        }

        $model = new PlusSettingsModel();
        $this->insertPlusSettings($model->fromArray($data));
    }

    /**
     * @param ExpressSettingsModel $model
     */
    public function insertExpressCheckoutSettings(ExpressSettingsModel $model)
    {
        $em = $this->getEntityManager();
        $em->persist($model);
        $em->flush();
    }

    /**
     * @param array $data
     */
    public function insertExpressCheckoutSettingsFromArray(array $data)
    {
        if (empty($data['shopId'])) {
            $data['shopId'] = 1;
        }

        if (empty($data['detailActive'])) {
            $data['detailActive'] = false;
        }

        if (empty($data['cartActive'])) {
            $data['cartActive'] = false;
        }

        if (empty($data['loginActive'])) {
            $data['loginActive'] = false;
        }

        if (empty($data['submitCart'])) {
            $data['submitCart'] = false;
        }

        if (empty($data['intent'])) {
            $data['intent'] = 0;
        }

        $model = new ExpressSettingsModel();
        $this->insertExpressCheckoutSettings($model->fromArray($data));
    }

    /**
     * @return ModelManager
     */
    private function getEntityManager()
    {
        return Shopware()->Container()->get('models');
    }
}
