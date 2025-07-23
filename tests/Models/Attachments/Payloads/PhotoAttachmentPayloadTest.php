<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Attachments\Payloads;

use BushlanovDev\MaxMessengerBot\Attributes\ArrayOf;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\PhotoAttachmentPayload;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\PhotoToken;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhotoAttachmentPayload::class)]
#[UsesClass(PhotoToken::class)]
#[UsesClass(ArrayOf::class)]
final class PhotoAttachmentPayloadTest extends TestCase
{
    #[Test]
    public function canBeCreatedWithUrlOnly(): void
    {
        $payload = new PhotoAttachmentPayload(url: 'https://example.com/photo.jpg');

        $this->assertSame('https://example.com/photo.jpg', $payload->url);
        $this->assertNull($payload->token);
        $this->assertNull($payload->photos);

        $expectedArray = [
            'url' => 'https://example.com/photo.jpg',
            'token' => null,
            'photos' => null,
        ];
        $this->assertEquals($expectedArray, $payload->toArray());
    }

    #[Test]
    public function canBeCreatedWithTokenOnly(): void
    {
        $payload = new PhotoAttachmentPayload(token: 'uploaded_token_abc');

        $this->assertSame('uploaded_token_abc', $payload->token);
        $this->assertNull($payload->url);
        $this->assertNull($payload->photos);

        $expectedArray = [
            'token' => 'uploaded_token_abc',
            'url' => null,
            'photos' => null,
        ];
        $this->assertEquals($expectedArray, $payload->toArray());
    }

    #[Test]
    public function canBeCreatedWithPhotosOnly(): void
    {
        $photos = [
            new PhotoToken('token_1'),
            new PhotoToken('token_2'),
        ];
        $payload = new PhotoAttachmentPayload(photos: $photos);

        $this->assertSame($photos, $payload->photos);
        $this->assertNull($payload->url);
        $this->assertNull($payload->token);

        $expectedArray = [
            'photos' => [
                ['token' => 'token_1'],
                ['token' => 'token_2'],
            ],
            'url' => null,
            'token' => null,
        ];
        $this->assertEquals($expectedArray, $payload->toArray());
    }

    /**
     * Data provider for invalid constructor arguments.
     *
     * @return array<string, array{0: string|null, 1: string|null, 2: array|null}>
     */
    public static function invalidPayloadProvider(): array
    {
        return [
            'all null (no arguments)' => [null, null, null],
            'url and token provided' => ['https://a.com', 'token123', null],
            'url and photos provided' => ['https://a.com', null, [new PhotoToken('t')]],
            'token and photos provided' => [null, 'token123', [new PhotoToken('t')]],
            'all three arguments provided' => ['https://a.com', 'token123', [new PhotoToken('t')]],
        ];
    }

    #[Test]
    #[DataProvider('invalidPayloadProvider')]
    public function constructorThrowsExceptionForInvalidArguments(
        ?string $url,
        ?string $token,
        ?array $photos
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provide exactly one of "url", "token", or "photos" for PhotoAttachmentPayload.');

        new PhotoAttachmentPayload($url, $token, $photos);
    }
}
