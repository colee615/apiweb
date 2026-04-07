<?php

namespace App\Support;

class ContentSecurity
{
    public const ASSET_KEYS = [
        'logo_url',
        'background_image',
        'iconImage',
        'image',
        'src',
        'poster',
        'poster_image',
        'seal_logo',
    ];

    public const LINK_KEYS = [
        'url',
        'app_store_url',
        'play_store_url',
        'view_all_url',
        'primary_button_url',
        'secondary_button_url',
    ];

    public const COLOR_KEYS = [
        'primary_color',
        'secondary_color',
        'accent_color',
    ];

    public static function sanitizePageData(array $data): array
    {
        if (array_key_exists('theme', $data) && is_array($data['theme'])) {
            $data['theme'] = self::sanitizeArray($data['theme']);
        }

        if (array_key_exists('sections', $data) && is_array($data['sections'])) {
            $data['sections'] = array_values(array_map(function ($section) {
                if (! is_array($section)) {
                    return [];
                }

                if (array_key_exists('settings', $section) && is_array($section['settings'])) {
                    $section['settings'] = self::sanitizeArray($section['settings']);
                }

                if (array_key_exists('items', $section) && is_array($section['items'])) {
                    $section['items'] = array_values(array_map(function ($item) {
                        if (! is_array($item)) {
                            return [];
                        }

                        if (array_key_exists('data', $item) && is_array($item['data'])) {
                            $item['data'] = self::sanitizeArray($item['data']);
                        }

                        return $item;
                    }, $section['items']));
                }

                return $section;
            }, $data['sections']));
        }

        return $data;
    }

    public static function sanitizeArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::sanitizeArray($value);
                continue;
            }

            if (in_array($key, self::ASSET_KEYS, true)) {
                $data[$key] = self::sanitizeAssetUrl($value);
                continue;
            }

            if (in_array($key, self::LINK_KEYS, true)) {
                $data[$key] = self::sanitizeLinkUrl($value);
                continue;
            }

            if (in_array($key, self::COLOR_KEYS, true)) {
                $data[$key] = self::sanitizeHexColor($value);
            }
        }

        return $data;
    }

    public static function sanitizeAssetUrl(?string $value): ?string
    {
        return self::sanitizeUrl($value, true);
    }

    public static function sanitizeLinkUrl(?string $value): ?string
    {
        return self::sanitizeUrl($value, false);
    }

    public static function sanitizeHexColor(?string $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $value) ? $value : null;
    }

    public static function normalizeAssetUrl(?string $value): ?string
    {
        $value = self::sanitizeAssetUrl($value);

        if (! $value) {
            return $value;
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        if (str_starts_with($value, '/storage/') || str_starts_with($value, 'storage/')) {
            return url(ltrim($value, '/'));
        }

        return $value;
    }

    protected static function sanitizeUrl(?string $value, bool $asset): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if ($value === '#') {
            return $value;
        }

        if (preg_match('/^(javascript|data|vbscript):/i', $value)) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }

        if ($asset && preg_match('/^(\/)?storage\//i', ltrim($value, '/'))) {
            return str_starts_with($value, '/') ? $value : '/' . ltrim($value, '/');
        }

        if (preg_match('/^\//', $value)) {
            return $value;
        }

        if (! $asset && preg_match('/^(mailto|tel):/i', $value)) {
            return $value;
        }

        return null;
    }
}
