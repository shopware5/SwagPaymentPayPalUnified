<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\PayPalBundle\Structs;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfileFlowConfig;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfileInputFields;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfilePresentation;

class WebProfile
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $temporary;

    /**
     * @var WebProfilePresentation
     */
    private $presentation;

    /**
     * @var WebProfileFlowConfig
     */
    private $flowConfig;

    /**
     * @var WebProfileInputFields
     */
    private $inputFields;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isTemporary()
    {
        return $this->temporary;
    }

    /**
     * @param bool $temporary
     */
    public function setTemporary($temporary)
    {
        $this->temporary = $temporary;
    }

    /**
     * @return WebProfilePresentation
     */
    public function getPresentation()
    {
        return $this->presentation;
    }

    /**
     * @param WebProfilePresentation $presentation
     */
    public function setPresentation($presentation)
    {
        $this->presentation = $presentation;
    }

    /**
     * @return WebProfileFlowConfig
     */
    public function getFlowConfig()
    {
        return $this->flowConfig;
    }

    /**
     * @param WebProfileFlowConfig $flowConfig
     */
    public function setFlowConfig($flowConfig)
    {
        $this->flowConfig = $flowConfig;
    }

    /**
     * @return WebProfileInputFields
     */
    public function getInputFields()
    {
        return $this->inputFields;
    }

    /**
     * @param WebProfileInputFields $inputFields
     */
    public function setInputFields($inputFields)
    {
        $this->inputFields = $inputFields;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param array $data
     *
     * @return WebProfile
     */
    public static function fromArray(array $data = [])
    {
        $webProfile = new self();
        $webProfile->setName($data['name']);
        $webProfile->setTemporary($data['temporary']);

        if (isset($data['input_fields'])) {
            $webProfile->setInputFields(WebProfileInputFields::fromArray($data['input_fields']));
        }

        if (isset($data['flow_config'])) {
            $webProfile->setFlowConfig(WebProfileFlowConfig::fromArray($data['flow_config']));
        }

        if (isset($data['presentation'])) {
            $webProfile->setPresentation(WebProfilePresentation::fromArray($data['presentation']));
        }

        $webProfile->setId($data['id']);

        return $webProfile;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'temporary' => $this->isTemporary(),
            'flow_config' => $this->getFlowConfig()->toArray(),
            'input_fields' => $this->getInputFields()->toArray(),
            'presentation' => $this->getPresentation()->toArray(),
        ];
    }

    /**
     * @param WebProfile $webProfile
     *
     * @return bool
     */
    public function equals(WebProfile $webProfile)
    {
        return $this->getName() === $webProfile->getName()
            && $this->isTemporary() === $webProfile->isTemporary()
            && $this->getPresentation() === $webProfile->getPresentation()
            && $this->getInputFields() === $webProfile->getInputFields()
            && $this->getFlowConfig() === $webProfile->getFlowConfig();
    }
}
