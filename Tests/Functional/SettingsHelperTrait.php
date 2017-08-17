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
