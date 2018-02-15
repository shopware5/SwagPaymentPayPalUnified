<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Functional\PayPalBundle\Services;

use SwagPaymentPayPalUnified\PayPalBundle\Services\WebProfileService;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\WebProfile\WebProfilePresentation;
use SwagPaymentPayPalUnified\Tests\Functional\DatabaseTestCaseTrait;

class WebProfileServiceTest extends \PHPUnit_Framework_TestCase
{
    use DatabaseTestCaseTrait;

    public function test_getCurrentWebProfile_imageUrl_should_be_changed()
    {
        $settings = [
            'shopId' => 1,
            'logoImage' => 'media/image/tea.png',
            'brandName' => 'TEE Test',
        ];

        $service = Shopware()->Container()->get('paypal_unified.web_profile_service');

        $reflectionClass = new \ReflectionClass(WebProfileService::class);
        $method = $reflectionClass->getMethod('getCurrentWebProfile');
        $method->setAccessible(true);

        $property = $reflectionClass->getProperty('settings');
        $property->setAccessible(true);
        $property->setValue($service, $settings);

        /** @var WebProfile $result */
        $result = $method->invoke($service, false);
        /** @var WebProfilePresentation $presentationResult */
        $presentationResult = $result->getPresentation();

        $this->assertInstanceOf(WebProfile::class, $result);
        $this->assertInstanceOf(WebProfilePresentation::class, $presentationResult);
        $this->assertStringStartsWith('http', $presentationResult->getLogoImage());
        $this->assertStringEndsWith('tea.png', $presentationResult->getLogoImage());
        $this->assertNotSame('media/image/tea.png', $presentationResult->getLogoImage());
    }
}
