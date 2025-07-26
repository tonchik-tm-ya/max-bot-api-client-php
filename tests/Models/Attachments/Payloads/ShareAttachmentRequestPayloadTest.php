<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Attachments\Payloads;

use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\ShareAttachmentRequestPayload;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ShareAttachmentRequestPayload::class)]
final class ShareAttachmentRequestPayloadTest extends TestCase
{
    #[Test]
    public function canBeCreatedWithUrlOnly(): void
    {
        $payload = new ShareAttachmentRequestPayload(url: 'https://example.com');

        $this->assertSame('https://example.com', $payload->url);
        $this->assertNull($payload->token);

        $expectedArray = ['url' => 'https://example.com', 'token' => null];
        $this->assertEquals($expectedArray, $payload->toArray());
    }

    #[Test]
    public function canBeCreatedWithTokenOnly(): void
    {
        $payload = new ShareAttachmentRequestPayload(token: 'share_token_abc');

        $this->assertSame('share_token_abc', $payload->token);
        $this->assertNull($payload->url);

        $expectedArray = ['url' => null, 'token' => 'share_token_abc'];
        $this->assertEquals($expectedArray, $payload->toArray());
    }

    /**
     * @return array<string, array{0: ?string, 1: ?string}>
     */
    public static function invalidPayloadProvider(): array
    {
        return [
            'both null' => [null, null],
            'both set' => ['https://a.com', 'token123'],
        ];
    }

    #[Test]
    #[DataProvider('invalidPayloadProvider')]
    public function constructorThrowsExceptionForInvalidArguments(?string $url, ?string $token): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provide exactly one of "url" or "token" for ShareAttachmentRequestPayload.');

        new ShareAttachmentRequestPayload($url, $token);
    }
}
