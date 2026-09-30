<?php

namespace Oxalis\Support;

class Branding
{
    private const LAYOUTS = ['card', 'split', 'bare', 'glass', 'float'];
    private const IMAGE_POSITIONS = ['top', 'bottom'];

    public static function layout(): string
    {
        $layout = strtolower(trim((string) config('oxalis.layout', 'card')));

        return in_array($layout, self::LAYOUTS, true) ? $layout : 'card';
    }

    public static function logoUrl(): ?string
    {
        return self::assetUrl(config('oxalis.brand.logo_url'));
    }

    public static function logoAlt(): string
    {
        return self::text(config('oxalis.brand.logo_alt')) ?: (string) config('app.name', 'App');
    }

    public static function logoHeight(int $default = 52): int
    {
        return self::positiveInt(config('oxalis.brand.logo_height'), $default, 24, 160);
    }

    public static function tagline(): ?string
    {
        return self::text(config('oxalis.brand.tagline'));
    }

    public static function showAppName(): bool
    {
        return filter_var(config('oxalis.brand.show_app_name', false), FILTER_VALIDATE_BOOL);
    }

    public static function cardImageUrl(): ?string
    {
        return self::assetUrl(config('oxalis.brand.card_image_url'));
    }

    public static function cardImageAlt(): string
    {
        return self::text(config('oxalis.brand.card_image_alt')) ?: '';
    }

    public static function cardImagePosition(): string
    {
        $position = strtolower(trim((string) config('oxalis.brand.card_image_position', 'top')));

        return in_array($position, self::IMAGE_POSITIONS, true) ? $position : 'top';
    }

    public static function cardImageHeight(int $default = 140): int
    {
        return self::positiveInt(config('oxalis.brand.card_image_height'), $default, 48, 360);
    }

    private static function assetUrl(mixed $value): ?string
    {
        $path = self::text($value);

        if ($path === null) {
            return null;
        }

        if (
            preg_match('#^(https?:)?//#i', $path) ||
            preg_match('#^(data|blob):#i', $path)
        ) {
            return $path;
        }

        $path = preg_replace('#^public/#', '', ltrim($path, '/'));

        return asset($path);
    }

    private static function text(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private static function positiveInt(mixed $value, int $default, int $min, int $max): int
    {
        $value = filter_var($value, FILTER_VALIDATE_INT);

        if ($value === false) {
            return $default;
        }

        return max($min, min($max, $value));
    }
}
