<?php

namespace MrCatz\DataTable\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Unit test for mrcatz_lang() without Laravel app context.
 * Without translator, the function returns the key as-is.
 * Full translation is tested in Feature/MrCatzLangTest.
 */
class HelpersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/../../src/helpers.php';
    }

    public function test_mrcatz_lang_returns_key_without_translator(): void
    {
        $result = mrcatz_lang('added');
        $this->assertEquals('added', $result);
    }

    public function test_mrcatz_lang_returns_key_for_missing(): void
    {
        $result = mrcatz_lang('nonexistent_key');
        $this->assertEquals('nonexistent_key', $result);
    }
}
