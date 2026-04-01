<?php

if (!function_exists('mrcatz_lang')) {
    /**
     * Get a MrCatz DataTable translated string.
     *
     * @param string $key
     * @param array  $replace  e.g. [':query' => 'ryan']
     * @return string
     */
    function mrcatz_lang(string $key, array $replace = []): string
    {
        $locale = config('mrcatz.locale', 'en');
        $text = config("mrcatz.{$locale}.{$key}", config("mrcatz.en.{$key}", $key));

        foreach ($replace as $k => $v) {
            $text = str_replace($k, $v, $text);
        }

        return $text;
    }
}
