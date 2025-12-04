<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider1_endpoint_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/employees/provider1', [
            'emp_id' => 'P1_001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email_address' => 'john@example.com',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'API token is required',
            ]);
    }

    public function test_provider2_endpoint_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/employees/provider2', [
            'employee_number' => 'P2_001',
            'personal_info' => [
                'given_name' => 'Jane',
                'family_name' => 'Doe',
                'email' => 'jane@example.com',
            ],
            'work_info' => [
                'role' => 'Guard',
                'division' => 'Security',
            ],
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'API token is required',
            ]);
    }

    public function test_invalid_token_is_rejected(): void
    {
        $response = $this->withToken('invalid_token')
            ->postJson('/api/v1/employees/provider1', [
                'emp_id' => 'P1_001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email_address' => 'john@example.com',
            ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid API token',
            ]);
    }

    public function test_valid_token_grants_access(): void
    {
        config(['auth.provider1_api_token' => 'valid_token']);

        // Mock the action to prevent actual API calls
        $this->mock(\App\Actions\Employees\CreateEmployeeInTrackTikAction::class)
            ->shouldReceive('execute')
            ->once()
            ->andReturn(new \App\Models\EmployeeSync([
                'id' => 1,
                'provider' => \App\Enums\Provider::PROVIDER1,
                'provider_employee_id' => 'P1_001',
                'sync_status' => \App\Enums\SyncStatus::SUCCESS,
                'tracktik_employee_id' => 'TT_001',
            ]));

        $response = $this->withToken('valid_token')
            ->postJson('/api/v1/employees/provider1', [
                'emp_id' => 'P1_001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email_address' => 'john@example.com',
            ]);

        $response->assertStatus(200);
    }
}
