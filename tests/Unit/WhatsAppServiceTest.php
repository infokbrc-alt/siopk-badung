<?php

namespace Tests\Unit;

use App\Services\WhatsAppService;
use Tests\TestCase;

class WhatsAppServiceTest extends TestCase
{
    public function test_is_enabled_returns_bool(): void
    {
        $service = new WhatsAppService;

        $this->assertIsBool($service->isEnabled());
    }

    public function test_normalize_number_method_exists(): void
    {
        $reflection = new \ReflectionClass(WhatsAppService::class);
        $method = $reflection->getMethod('normalizeNumber');
        $method->setAccessible(true);

        $this->assertTrue($method->isPrivate());
        $this->assertEquals('normalizeNumber', $method->getName());
    }
}
