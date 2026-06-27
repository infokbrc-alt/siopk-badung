<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FonnteWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_device_status_webhook_returns_json(): void
    {
        $response = $this->post('/webhook/fonnte/device-status', [
            'device' => 'test-device',
            'status' => 'connected',
        ]);

        $response->assertSuccessful();
        $response->assertJson(['status' => 'ok']);
    }

    public function test_incoming_message_webhook_returns_json(): void
    {
        $response = $this->post('/webhook/fonnte/incoming', [
            'from' => '628123456789',
            'text' => 'Hello',
        ]);

        $response->assertSuccessful();
        $response->assertJson(['status' => 'ok']);
    }
}
