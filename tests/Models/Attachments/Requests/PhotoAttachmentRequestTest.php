<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Tests\Models\Attachments\Requests;

use BushlanovDev\MaxMessengerBot\Enums\AttachmentType;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\PhotoAttachmentRequestPayload;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\PhotoToken;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Requests\PhotoAttachmentRequest;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhotoAttachmentRequest::class)]
#[UsesClass(PhotoAttachmentRequestPayload::class)]
#[UsesClass(PhotoToken::class)]
final class PhotoAttachmentRequestTest extends TestCase
{
    #[Test]
    public function testFromUrl(): void
    {
        $url = 'https://example.com/image.jpg';
        $request = PhotoAttachmentRequest::fromUrl($url);

        $this->assertInstanceOf(PhotoAttachmentRequest::class, $request);
        $this->assertSame(AttachmentType::Image, $request->type);
        $this->assertInstanceOf(PhotoAttachmentRequestPayload::class, $request->payload);
        $this->assertSame($url, $request->payload->url);
        $this->assertNull($request->payload->token);
        $this->assertNull($request->payload->photos);

        $expectedArray = [
            'type' => 'image',
            'payload' => [
                'url' => $url,
                'token' => null,
                'photos' => null,
            ],
        ];
        $this->assertEquals($expectedArray, $request->toArray());
    }

    #[Test]
    public function testFromToken(): void
    {
        $token = 'some_upload_token_12345';
        $request = PhotoAttachmentRequest::fromToken($token);

        $this->assertInstanceOf(PhotoAttachmentRequest::class, $request);
        $this->assertSame(AttachmentType::Image, $request->type);
        $this->assertInstanceOf(PhotoAttachmentRequestPayload::class, $request->payload);
        $this->assertSame($token, $request->payload->token);
        $this->assertNull($request->payload->url);
        $this->assertNull($request->payload->photos);

        $expectedArray = [
            'type' => 'image',
            'payload' => [
                'token' => $token,
                'url' => null,
                'photos' => null,
            ],
        ];
        $this->assertEquals($expectedArray, $request->toArray());
    }

    #[Test]
    public function testFromPhotos(): void
    {
        $photos = [
            new PhotoToken('token_A'),
            new PhotoToken('token_B'),
        ];
        $request = PhotoAttachmentRequest::fromPhotos($photos);

        $this->assertInstanceOf(PhotoAttachmentRequest::class, $request);
        $this->assertSame(AttachmentType::Image, $request->type);
        $this->assertInstanceOf(PhotoAttachmentRequestPayload::class, $request->payload);
        $this->assertSame($photos, $request->payload->photos);
        $this->assertNull($request->payload->url);
        $this->assertNull($request->payload->token);

        $expectedArray = [
            'type' => 'image',
            'payload' => [
                'photos' => [
                    ['token' => 'token_A'],
                    ['token' => 'token_B'],
                ],
                'url' => null,
                'token' => null,
            ],
        ];
        $this->assertEquals($expectedArray, $request->toArray());
    }

    /**
     * @return array<string, array{0: string|null, 1: string|null, 2: array|null}>
     */
    public static function invalidPayloadProvider(): array
    {
        return [
            'no arguments' => [null, null, null],
            'url and token' => ['http://a.com', 'token123', null],
            'token and photos' => [null, 'token123', [new PhotoToken('t')]],
            'all arguments' => ['http://a.com', 'token123', [new PhotoToken('t')]],
        ];
    }

    #[Test]
    #[DataProvider('invalidPayloadProvider')]
    public function payloadThrowsExceptionWhenNotExactlyOneArgumentIsProvided(
        ?string $url,
        ?string $token,
        ?array $photos
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provide exactly one of "url", "token", or "photos" for PhotoAttachmentRequestPayload.');

        new PhotoAttachmentRequestPayload($url, $token, $photos);
    }
}
