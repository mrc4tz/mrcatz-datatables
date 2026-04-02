<?php

namespace MrCatz\DataTable\Tests\Feature;

use MrCatz\DataTable\Tests\TestCase;

class MrCatzLangTest extends TestCase
{
    public function test_english_translation(): void
    {
        config()->set('mrcatz.locale', 'en');

        $this->assertEquals('added!', mrcatz_lang('added'));
        $this->assertEquals('updated!', mrcatz_lang('updated'));
        $this->assertEquals('deleted!', mrcatz_lang('deleted'));
        $this->assertEquals('successfully', mrcatz_lang('success'));
        $this->assertEquals('failed to process!', mrcatz_lang('failed'));
    }

    public function test_indonesian_translation(): void
    {
        config()->set('mrcatz.locale', 'id');

        $this->assertEquals('ditambahkan!', mrcatz_lang('added'));
        $this->assertEquals('diupdate!', mrcatz_lang('updated'));
        $this->assertEquals('dihapus!', mrcatz_lang('deleted'));
        $this->assertEquals('berhasil', mrcatz_lang('success'));
        $this->assertEquals('gagal diproses!', mrcatz_lang('failed'));
    }

    public function test_button_strings(): void
    {
        config()->set('mrcatz.locale', 'en');

        $this->assertEquals('Add', mrcatz_lang('btn_add'));
        $this->assertEquals('Save', mrcatz_lang('btn_save'));
        $this->assertEquals('Cancel', mrcatz_lang('btn_cancel'));
        $this->assertEquals('Delete', mrcatz_lang('btn_delete'));
    }

    public function test_replacement_with_colon_prefix(): void
    {
        config()->set('mrcatz.locale', 'en');

        $result = mrcatz_lang('no_results_for', [':query' => 'test']);
        $this->assertEquals("No results found for 'test'", $result);
    }

    public function test_replacement_without_colon_prefix(): void
    {
        config()->set('mrcatz.locale', 'en');

        $result = mrcatz_lang('no_results_for', ['query' => 'test']);
        $this->assertEquals("No results found for 'test'", $result);
    }

    public function test_replacement_indonesian(): void
    {
        config()->set('mrcatz.locale', 'id');

        $result = mrcatz_lang('no_results_for', [':query' => 'test']);
        $this->assertEquals("Tidak ada hasil ditemukan untuk pencarian 'test'", $result);
    }

    public function test_missing_key_returns_key(): void
    {
        config()->set('mrcatz.locale', 'en');

        $result = mrcatz_lang('nonexistent_key_xyz');
        $this->assertEquals('nonexistent_key_xyz', $result);
    }

    public function test_default_locale_is_english(): void
    {
        // Default from config is 'en'
        $this->assertEquals('added!', mrcatz_lang('added'));
    }

    public function test_all_english_keys_translated(): void
    {
        $this->app->setLocale('en');
        $translations = trans('mrcatz::mrcatz');

        $this->assertIsArray($translations);
        $this->assertNotEmpty($translations);

        $requiredKeys = ['btn_add', 'btn_save', 'added', 'deleted', 'success', 'failed', 'loading'];
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $translations, "English key '{$key}' should exist");
        }
    }

    public function test_all_indonesian_keys_translated(): void
    {
        $this->app->setLocale('id');
        $translations = trans('mrcatz::mrcatz');

        $this->assertIsArray($translations);
        $this->assertNotEmpty($translations);

        $requiredKeys = ['btn_add', 'btn_save', 'added', 'deleted', 'success', 'failed', 'loading'];
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $translations, "Indonesian key '{$key}' should exist");
        }
    }
}
