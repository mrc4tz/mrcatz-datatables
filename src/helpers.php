<?php

if (!function_exists('mrcatz_lang')) {
    /**
     * Get a MrCatz DataTable translated string.
     *
     * Uses Laravel lang files (lang/vendor/mrcatz/) with locale from
     * app locale or config('mrcatz.locale') fallback.
     *
     * Replacement keys accept both ':key' and 'key' formats.
     *
     * @param string $key
     * @param array  $replace  e.g. [':query' => 'ryan'] or ['query' => 'ryan']
     * @return string
     */
    function mrcatz_lang(string $key, array $replace = []): string
    {
        // Normalize replacement keys: strip leading colon for trans() compatibility
        $normalized = [];
        foreach ($replace as $k => $v) {
            $normalized[ltrim($k, ':')] = $v;
        }

        try {
            $locale = app()->getLocale() ?? config('mrcatz.locale', 'en');
            $langKey = "mrcatz::mrcatz.{$key}";
            $translated = trans($langKey, $normalized, $locale);

            if ($translated !== $langKey) {
                return $translated;
            }
        } catch (\Throwable $e) {
            // translator not available (e.g. unit tests without full app)
        }

        return $key;
    }
}
