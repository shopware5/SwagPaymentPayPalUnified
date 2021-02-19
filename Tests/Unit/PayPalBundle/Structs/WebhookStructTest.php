<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagPaymentPayPalUnified\Tests\Unit\PayPalBundle\Structs;

use PHPUnit\Framework\TestCase;
use SwagPaymentPayPalUnified\PayPalBundle\Structs\Webhook;

class WebhookStructTest extends TestCase
{
    public function testGetId()
    {
        $struct = Webhook::fromArray(['id' => 10]);

        static::assertSame(10, $struct->getId());
    }

    public function testGetCreationTime()
    {
        $struct = Webhook::fromArray(['create_time' => '01-01-1970']);

        static::assertSame('01-01-1970', $struct->getCreationTime());
    }

    public function testGetResourceType()
    {
        $struct = Webhook::fromArray(['resource_type' => 'Test']);

        static::assertSame('Test', $struct->getResourceType());
    }

    public function testGetEventType()
    {
        $struct = Webhook::fromArray(['event_type' => 'Test-Event']);

        static::assertSame('Test-Event', $struct->getEventType());
    }

    public function testGetSummary()
    {
        $struct = Webhook::fromArray(['summary' => 'Test notification triggered in PHPUnit']);

        static::assertSame('Test notification triggered in PHPUnit', $struct->getSummary());
    }

    public function testGetResource()
    {
        $struct = Webhook::fromArray(['resource' => ['name' => 'test']]);

        static::assertSame('test', $struct->getResource()['name']);
    }

    public function testToArray()
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

        $data = Webhook::fromArray($data)->toArray();

        static::assertSame('01-01-1970', $data['creationTime']);
        static::assertSame('Test object', $data['summary']);
        static::assertSame('Test', $data['resourceType']);
        static::assertSame('Test event', $data['eventType']);
        static::assertSame('Test id', $data['id']);
        static::assertSame('Test Resource', $data['resource']['name']);
    }
}
