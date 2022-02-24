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
    public function insertGeneralSettings(GeneralSettingsModel $model)
    {
        $em = $this->getEntityManager();
        $em->persist($model);
        $em->flush();
    }

    public function insertGeneralSettingsFromArray(array $data)
    {
        if (!isset($data['showSidebarLogo'])) {
            $data['showSidebarLogo'] = false;
        }

        if (!isset($data['useInContext'])) {
            $data['useInContext'] = false;
        }

        if (!isset($data['submitCart'])) {
            $data['submitCart'] = true;
        }

        if (!isset($data['sendOrderNumber'])) {
            $data['sendOrderNumber'] = false;
        }

        if (!isset($data['logLevel'])) {
            $data['logLevel'] = 1;
        }

        if (!isset($data['displayErrors'])) {
            $data['displayErrors'] = true;
        }

        if (!isset($data['brandName'])) {
            $data['brandName'] = 'TestBrandName';
        }

        if (!isset($data['useSmartPaymentButtons'])) {
            $data['useSmartPaymentButtons'] = false;
        }

        $model = new GeneralSettingsModel();
        $model->fromArray($data);

        $this->insertGeneralSettings($model);
    }

    public function insertInstallmentsSettings(InstallmentsSettingsModel $model)
    {
        $em = $this->getEntityManager();
        $em->persist($model);
        $em->flush();
    }

    public function insertInstallmentsSettingsFromArray(array $data)
    {
        if (!isset($data['shopId'])) {
            $data['shopId'] = 1;
        }

        if (!isset($data['advertiseInstallments'])) {
            $data['advertiseInstallments'] = false;
        }

        $model = new InstallmentsSettingsModel();
        $model->fromArray($data);
        $this->insertInstallmentsSettings($model);
    }

    public function insertPlusSettings(PlusSettingsModel $model)
    {
        $em = $this->getEntityManager();
        $em->persist($model);
        $em->flush();
    }

    public function insertPlusSettingsFromArray(array $data)
    {
        if (!isset($data['shopId'])) {
            $data['shopId'] = 1;
        }

        if (!isset($data['restyle'])) {
            $data['restyle'] = 0;
        }

        if (!isset($data['integrateThirdPartyMethods'])) {
            $data['integrateThirdPartyMethods'] = 0;
        }

        $model = new PlusSettingsModel();
        $model->fromArray($data);
        $this->insertPlusSettings($model);
    }

    public function insertExpressCheckoutSettings(ExpressSettingsModel $model)
    {
        $em = $this->getEntityManager();
        $em->persist($model);
        $em->flush();
    }

    public function insertExpressCheckoutSettingsFromArray(array $data)
    {
        if (!isset($data['shopId'])) {
            $data['shopId'] = 1;
        }

        if (!isset($data['detailActive'])) {
            $data['detailActive'] = false;
        }

        if (!isset($data['cartActive'])) {
            $data['cartActive'] = false;
        }

        if (!isset($data['loginActive'])) {
            $data['loginActive'] = false;
        }

        if (!isset($data['submitCart'])) {
            $data['submitCart'] = false;
        }

        if (!isset($data['intent'])) {
            $data['intent'] = 0;
        }

        if (!isset($data['offCanvasActive'])) {
            $data['offCanvasActive'] = 0;
        }

        if (!isset($data['listingActive'])) {
            $data['listingActive'] = 0;
        }

        if (!isset($data['buttonLocale'])) {
            $data['buttonLocale'] = 'en_US';
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
