<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\PayPalBundle\Structs;

use SwagPaymentPayPalUnified\PayPalBundle\Structs\Webhook;

class WebhookStructTest extends \PHPUnit_Framework_TestCase
{
    public function test_getId()
    {
        $struct = Webhook::fromArray(['id' => 10]);

        $this->assertEquals(10, $struct->getId());
    }

    public function test_getCreationTime()
    {
        $struct = Webhook::fromArray(['create_time' => '01-01-1970']);

        $this->assertEquals('01-01-1970', $struct->getCreationTime());
    }

    public function test_getResourceType()
    {
        $struct = Webhook::fromArray(['resource_type' => 'Test']);

        $this->assertEquals('Test', $struct->getResourceType());
    }

    public function test_getEventType()
    {
        $struct = Webhook::fromArray(['event_type' => 'Test-Event']);

        $this->assertEquals('Test-Event', $struct->getEventType());
    }

    public function test_getSummary()
    {
        $struct = Webhook::fromArray(['summary' => 'Test notification triggered in PHPUnit']);

        $this->assertEquals('Test notification triggered in PHPUnit', $struct->getSummary());
    }

    public function test_getResource()
    {
        $struct = Webhook::fromArray(['resource' => ['name' => 'test']]);

        $this->assertEquals('test', $struct->getResource()['name']);
    }

    public function test_toArray()
    {
        $data = [
            'create_time' => '01-01-1970',
            'summary' => 'Test object',
            'resource_type' => 'Test',
            'event_type' => 'Test event',
            'id' => 'Test id',
            'resource' => [
                'name' => 'Test Resource',
            ],
        ];

        $struct = Webhook::fromArray($data);
        $data = $struct->toArray();

        $this->assertEquals('01-01-1970', $data['creationTime']);
        $this->assertEquals('Test object', $data['summary']);
        $this->assertEquals('Test', $data['resourceType']);
        $this->assertEquals('Test event', $data['eventType']);
        $this->assertEquals('Test id', $data['id']);
        $this->assertEquals('Test Resource', $data['resource']['name']);
    }
}
