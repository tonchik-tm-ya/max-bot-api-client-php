<?php

declare(strict_types=1);

namespace BushlanovDev\MaxMessengerBot\Models\Attachments\Requests;

use BushlanovDev\MaxMessengerBot\Enums\AttachmentType;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\PhotoAttachmentPayload;
use BushlanovDev\MaxMessengerBot\Models\Attachments\Payloads\PhotoToken;

/**
 * Request to attach some data to message.
 */
final readonly class PhotoAttachmentRequest extends AbstractAttachmentRequest
{
    /**
     * Creates a request to attach an image by URL.
     *
     * @param string $url
     *
     * @return PhotoAttachmentRequest
     */
    public static function fromUrl(string $url): self
    {
        return new self(new PhotoAttachmentPayload(url: $url));
    }

    /**
     * Creates a request to attach an image using the token received after uploading.
     *
     * @param string $token
     *
     * @return PhotoAttachmentRequest
     */
    public static function fromToken(string $token): self
    {
        return new self(new PhotoAttachmentPayload(token: $token));
    }

    /**
     * Creates a request to attach an image using the tokens received after uploading.
     *
     * @param PhotoToken[] $photos
     *
     * @return PhotoAttachmentRequest
     */
    public static function fromPhotos(array $photos): self
    {
        return new self(new PhotoAttachmentPayload(photos: $photos));
    }

    /**
     * @param PhotoAttachmentPayload $payload Request to attach image.
     */
    private function __construct(PhotoAttachmentPayload $payload)
    {
        parent::__construct(AttachmentType::Image, $payload);
    }
}
