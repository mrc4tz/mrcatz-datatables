<?php

namespace MrCatz\DataTable\Tests\Unit;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $mrcatzConfig = require __DIR__ . '/../../config/mrcatz.php';

        $app = Container::getInstance();
        $app->singleton('config', function () use ($mrcatzConfig) {
            return new Repository(['mrcatz' => $mrcatzConfig]);
        });
        $app->singleton('app', fn() => $app);

        \Illuminate\Support\Facades\Facade::setFacadeApplication($app);

        require_once __DIR__ . '/../../src/helpers.php';
    }

    protected function tearDown(): void
    {
        Container::setInstance(new Container());
        parent::tearDown();
    }

    public function test_mrcatz_lang_returns_english_string(): void
    {
        $result = mrcatz_lang('added');
        $this->assertEquals('added!', $result);
    }

    public function test_mrcatz_lang_returns_crud_strings(): void
    {
        $this->assertEquals('added!', mrcatz_lang('added'));
        $this->assertEquals('updated!', mrcatz_lang('updated'));
        $this->assertEquals('deleted!', mrcatz_lang('deleted'));
        $this->assertEquals('successfully', mrcatz_lang('success'));
        $this->assertEquals('failed to process!', mrcatz_lang('failed'));
    }

    public function test_mrcatz_lang_with_replacement(): void
    {
        $result = mrcatz_lang('no_results_for', [':query' => 'test']);
        $this->assertEquals("No results found for 'test'", $result);
    }

    public function test_mrcatz_lang_returns_key_for_missing(): void
    {
        $result = mrcatz_lang('nonexistent_key');
        $this->assertEquals('nonexistent_key', $result);
    }

    public function test_mrcatz_lang_button_strings(): void
    {
        $this->assertEquals('Add', mrcatz_lang('btn_add'));
        $this->assertEquals('Save', mrcatz_lang('btn_save'));
        $this->assertEquals('Cancel', mrcatz_lang('btn_cancel'));
        $this->assertEquals('Delete', mrcatz_lang('btn_delete'));
    }
}
