<?php

namespace MrCatz\DataTable\Tests\Unit;

use MrCatz\DataTable\MrCatzEvent;
use PHPUnit\Framework\TestCase;

class MrCatzEventTest extends TestCase
{
    public function test_event_constants_are_strings(): void
    {
        $this->assertIsString(MrCatzEvent::ADD_DATA);
        $this->assertIsString(MrCatzEvent::EDIT_DATA);
        $this->assertIsString(MrCatzEvent::DELETE_DATA);
        $this->assertIsString(MrCatzEvent::REFRESH_DATA);
        $this->assertIsString(MrCatzEvent::SHOW_NOTIF);
        $this->assertIsString(MrCatzEvent::NOTICE);
        $this->assertIsString(MrCatzEvent::RESET_SELECT);
        $this->assertIsString(MrCatzEvent::OPEN_EXPORT_MODAL);
        $this->assertIsString(MrCatzEvent::SEARCH_TYPING);
        $this->assertIsString(MrCatzEvent::PREPARE_ADD);
        $this->assertIsString(MrCatzEvent::PREPARE_EDIT);
        $this->assertIsString(MrCatzEvent::PREPARE_DELETE);
        $this->assertIsString(MrCatzEvent::BULK_DELETE);
        $this->assertIsString(MrCatzEvent::REFRESH_TABLE);
    }

    public function test_event_constants_are_not_empty(): void
    {
        $reflection = new \ReflectionClass(MrCatzEvent::class);
        $constants = $reflection->getConstants();

        $this->assertNotEmpty($constants);

        foreach ($constants as $name => $value) {
            $this->assertNotEmpty($value, "Constant {$name} should not be empty");
        }
    }

    public function test_event_constants_are_unique(): void
    {
        $reflection = new \ReflectionClass(MrCatzEvent::class);
        $constants = $reflection->getConstants();
        $values = array_values($constants);

        $this->assertCount(count($values), array_unique($values), 'All event constants should be unique');
    }
}
