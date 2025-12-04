<?php

namespace Tests\Feature;

use App\Actions\Employees\CreateEmployeeInTrackTikAction;
use App\Enums\Provider;
use App\Enums\SyncStatus;
use App\Exceptions\TrackTikApiException;
use App\Models\EmployeeSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class EmployeeControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'auth.provider1_api_token' => 'test_provider1_token',
            'auth.provider2_api_token' => 'test_provider2_token',
        ]);
    }

    public function test_sync_provider1_successfully(): void
    {
        $mockAction = Mockery::mock(CreateEmployeeInTrackTikAction::class);
        $this->app->instance(CreateEmployeeInTrackTikAction::class, $mockAction);

        $employeeSync = new EmployeeSync([
            'id' => 1,
            'provider' => Provider::PROVIDER1,
            'provider_employee_id' => 'P1_001',
            'tracktik_employee_id' => 'TT_001',
            'sync_status' => SyncStatus::SUCCESS,
            'error_message' => null,
        ]);
        $employeeSync->exists = true;

        $mockAction->shouldReceive('execute')
            ->once()
            ->with(
                Provider::PROVIDER1,
                'P1_001',
                Mockery::type('array')
            )
            ->andReturn($employeeSync);

        $response = $this->withToken('test_provider1_token')
            ->postJson('/api/v1/employees/provider1', [
                'emp_id' => 'P1_001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email_address' => 'john.doe@example.com',
                'phone' => '+1234567890',
                'job_title' => 'Security Guard',
                'dept' => 'Security',
                'hire_date' => '2024-01-15',
                'employment_status' => 'active',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Employee synced successfully',
                'data' => [
                    'provider' => 'provider1',
                    'provider_employee_id' => 'P1_001',
                    'tracktik_employee_id' => 'TT_001',
                    'sync_status' => 'success',
                ],
            ]);
    }

    public function test_sync_provider2_successfully(): void
    {
        $mockAction = Mockery::mock(CreateEmployeeInTrackTikAction::class);
        $this->app->instance(CreateEmployeeInTrackTikAction::class, $mockAction);

        $employeeSync = new EmployeeSync([
            'id' => 2,
            'provider' => Provider::PROVIDER2,
            'provider_employee_id' => 'P2_001',
            'tracktik_employee_id' => 'TT_002',
            'sync_status' => SyncStatus::SUCCESS,
            'error_message' => null,
        ]);
        $employeeSync->exists = true;

        $mockAction->shouldReceive('execute')
            ->once()
            ->with(
                Provider::PROVIDER2,
                'P2_001',
                Mockery::type('array')
            )
            ->andReturn($employeeSync);

        $response = $this->withToken('test_provider2_token')
            ->postJson('/api/v1/employees/provider2', [
                'employee_number' => 'P2_001',
                'personal_info' => [
                    'given_name' => 'Jane',
                    'family_name' => 'Doe',
                    'email' => 'jane.doe@example.com',
                    'mobile' => '+1234567890',
                ],
                'work_info' => [
                    'role' => 'Security Guard',
                    'division' => 'Night Shift',
                    'start_date' => '2024-02-01',
                    'current_status' => 'employed',
                ],
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Employee synced successfully',
                'data' => [
                    'provider' => 'provider2',
                    'provider_employee_id' => 'P2_001',
                    'sync_status' => 'success',
                ],
            ]);
    }

    public function test_provider1_validation_fails_with_missing_fields(): void
    {
        $response = $this->withToken('test_provider1_token')
            ->postJson('/api/v1/employees/provider1', [
                'emp_id' => 'P1_001',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email_address']);
    }

    public function test_provider2_validation_fails_with_missing_fields(): void
    {
        $response = $this->withToken('test_provider2_token')
            ->postJson('/api/v1/employees/provider2', [
                'employee_number' => 'P2_001',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['personal_info']);
    }

    public function test_provider1_validation_fails_with_invalid_email(): void
    {
        $response = $this->withToken('test_provider1_token')
            ->postJson('/api/v1/employees/provider1', [
                'emp_id' => 'P1_001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email_address' => 'invalid-email',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email_address']);
    }

    public function test_sync_provider1_handles_api_exception(): void
    {
        $mockAction = Mockery::mock(CreateEmployeeInTrackTikAction::class);
        $this->app->instance(CreateEmployeeInTrackTikAction::class, $mockAction);

        $mockAction->shouldReceive('execute')
            ->once()
            ->andThrow(new TrackTikApiException('TrackTik API is unavailable'));

        $response = $this->withToken('test_provider1_token')
            ->postJson('/api/v1/employees/provider1', [
                'emp_id' => 'P1_001',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email_address' => 'john.doe@example.com',
            ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Failed to sync employee',
                'error' => 'TrackTik API is unavailable',
            ]);
    }

    public function test_sync_provider2_handles_generic_exception(): void
    {
        $mockAction = Mockery::mock(CreateEmployeeInTrackTikAction::class);
        $this->app->instance(CreateEmployeeInTrackTikAction::class, $mockAction);

        $mockAction->shouldReceive('execute')
            ->once()
            ->andThrow(new \Exception('Database connection failed'));

        $response = $this->withToken('test_provider2_token')
            ->postJson('/api/v1/employees/provider2', [
                'employee_number' => 'P2_001',
                'personal_info' => [
                    'given_name' => 'Jane',
                    'family_name' => 'Doe',
                    'email' => 'jane@example.com',
                ],
                'work_info' => [
                    'role' => 'Guard',
                ],
            ]);

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Failed to sync employee',
                'error' => 'Database connection failed',
            ]);
    }

    public function test_provider1_accepts_optional_fields(): void
    {
        $mockAction = Mockery::mock(CreateEmployeeInTrackTikAction::class);
        $this->app->instance(CreateEmployeeInTrackTikAction::class, $mockAction);

        $employeeSync = new EmployeeSync([
            'provider' => Provider::PROVIDER1,
            'provider_employee_id' => 'P1_002',
            'sync_status' => SyncStatus::SUCCESS,
        ]);
        $employeeSync->exists = true;

        $mockAction->shouldReceive('execute')->once()->andReturn($employeeSync);

        $response = $this->withToken('test_provider1_token')
            ->postJson('/api/v1/employees/provider1', [
                'emp_id' => 'P1_002',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email_address' => 'jane.smith@example.com',
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);
    }
}
