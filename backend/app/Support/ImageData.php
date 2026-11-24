<?php

namespace App\Support;

use App\Models\ProjectImage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/** Shapes media files for the public API. Only same-origin paths are returned, never absolute URLs. */
class ImageData
{
    /** @return array<string, mixed>|null */
    public static function fromMedia(?Media $media, ?string $alt = null, bool $placeholder = false, ?string $caption = null): ?array
    {
        if ($media === null) {
            return null;
        }

        [$width, $height] = self::dimensions($media);

        return [
            'original_url' => self::path($media),
            'width' => $width,
            'height' => $height,
            'mime' => $media->mime_type,
            'alt' => $alt,
            'is_placeholder' => $placeholder,
            'caption' => $caption,
        ];
    }

    /** @return array<string, mixed>|null */
    public static function fromProjectImage(ProjectImage $image): ?array
    {
        return self::fromMedia($image->getFirstMedia('image'), $image->alt, (bool) $image->is_placeholder, $image->caption);
    }

    /** @return array<string, mixed> */
    public static function download(Media $media): array
    {
        return [
            'label' => $media->name,
            'url' => self::path($media),
            'mime' => $media->mime_type,
            'size' => $media->size,
        ];
    }

    private static function path(Media $media): string
    {
        $url = $media->getUrl();

        return parse_url($url, PHP_URL_PATH) ?: $url;
    }

    /** @return array{0: int|null, 1: int|null} */
    private static function dimensions(Media $media): array
    {
        $path = $media->getPath();
        $size = is_file($path) ? @getimagesize($path) : false;

        return $size ? [$size[0], $size[1]] : [null, null];
    }
}
