<?php

if (!function_exists('mrcatz_lang')) {
    /**
     * Get a MrCatz DataTable translated string.
     *
     * Priority: Laravel lang files → config fallback
     *
     * @param string $key
     * @param array  $replace  e.g. [':query' => 'ryan']
     * @return string
     */
    function mrcatz_lang(string $key, array $replace = []): string
    {
        // Try Laravel lang files first (supports lang/vendor/mrcatz override)
        try {
            $langKey = "mrcatz::mrcatz.{$key}";
            $translated = trans($langKey, $replace);

            if ($translated !== $langKey) {
                return $translated;
            }
        } catch (\Throwable $e) {
            // translator not available (e.g. unit tests without full app)
        }

        // Fall back to config
        $locale = config('mrcatz.locale', 'en');
        $text = config("mrcatz.{$locale}.{$key}", config("mrcatz.en.{$key}", $key));

        foreach ($replace as $k => $v) {
            $text = str_replace($k, $v, $text);
        }

        return $text;
    }
}
