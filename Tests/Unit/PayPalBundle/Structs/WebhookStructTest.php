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
    public function test_getId()
    {
        $struct = Webhook::fromArray(['id' => 10]);

        static::assertSame(10, $struct->getId());
    }

    public function test_getCreationTime()
    {
        $struct = Webhook::fromArray(['create_time' => '01-01-1970']);

        static::assertSame('01-01-1970', $struct->getCreationTime());
    }

    public function test_getResourceType()
    {
        $struct = Webhook::fromArray(['resource_type' => 'Test']);

        static::assertSame('Test', $struct->getResourceType());
    }

    public function test_getEventType()
    {
        $struct = Webhook::fromArray(['event_type' => 'Test-Event']);

        static::assertSame('Test-Event', $struct->getEventType());
    }

    public function test_getSummary()
    {
        $struct = Webhook::fromArray(['summary' => 'Test notification triggered in PHPUnit']);

        static::assertSame('Test notification triggered in PHPUnit', $struct->getSummary());
    }

    public function test_getResource()
    {
        $struct = Webhook::fromArray(['resource' => ['name' => 'test']]);

        static::assertSame('test', $struct->getResource()['name']);
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

        $data = Webhook::fromArray($data)->toArray();

        static::assertSame('01-01-1970', $data['creationTime']);
        static::assertSame('Test object', $data['summary']);
        static::assertSame('Test', $data['resourceType']);
        static::assertSame('Test event', $data['eventType']);
        static::assertSame('Test id', $data['id']);
        static::assertSame('Test Resource', $data['resource']['name']);
    }
}
