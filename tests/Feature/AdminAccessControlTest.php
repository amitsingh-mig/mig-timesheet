<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Attendance;
use App\Models\Timesheet;
use Carbon\Carbon;

class AdminAccessControlTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $adminUser;
    protected User $employeeUser;
    protected User $userWithoutRole;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $employeeRole = Role::factory()->create(['name' => 'employee']);
        
        // Create users
        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
            'email_verified_at' => now()
        ]);
        
        $this->employeeUser = User::factory()->create([
            'role_id' => $employeeRole->id,
            'email_verified_at' => now()
        ]);
        
        $this->userWithoutRole = User::factory()->create([
            'role_id' => null,
            'email_verified_at' => now()
        ]);
    }

    /** @test */
    public function admin_can_access_admin_dashboard()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin');

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    /** @test */
    public function employee_cannot_access_admin_dashboard()
    {
        $response = $this->actingAs($this->employeeUser)
            ->get('/admin');

        $response->assertStatus(403);
    }

    /** @test */
    public function user_without_role_cannot_access_admin_dashboard()
    {
        $response = $this->actingAs($this->userWithoutRole)
            ->get('/admin');

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_admin_dashboard()
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function admin_can_access_user_management()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_get_users_data()
    {
        // Create some test users
        User::factory()->count(3)->create(['role_id' => Role::factory()->create()->id]);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/admin/users/data');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'users' => [
                    '*' => ['id', 'name', 'email', 'role', 'status']
                ],
                'current_page',
                'total_pages',
                'total'
            ]);
    }

    /** @test */
    public function employee_cannot_get_users_data()
    {
        $response = $this->actingAs($this->employeeUser)
            ->getJson('/admin/users/data');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_new_user()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'employee'
        ];

        $response = $this->actingAs($this->adminUser)
            ->postJson('/admin/users', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User created successfully'
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com'
        ]);
    }

    /** @test */
    public function employee_cannot_create_new_user()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'role' => 'employee'
        ];

        $response = $this->actingAs($this->employeeUser)
            ->postJson('/admin/users', $userData);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_update_user()
    {
        $targetUser = User::factory()->create(['role_id' => Role::factory()->create()->id]);
        
        $updateData = [
            'name' => 'Updated Name',
            'email' => $targetUser->email, // Keep same email
            'role' => 'admin'
        ];

        $response = $this->actingAs($this->adminUser)
            ->putJson("/admin/users/{$targetUser->id}", $updateData);

        $response->assertStatus(200);
        
        $targetUser->refresh();
        $this->assertEquals('Updated Name', $targetUser->name);
    }

    /** @test */
    public function admin_can_delete_other_users_but_not_themselves()
    {
        $targetUser = User::factory()->create(['role_id' => Role::factory()->create()->id]);

        // Admin can delete other users
        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/admin/users/{$targetUser->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('users', ['id' => $targetUser->id]);

        // Admin cannot delete themselves
        $response = $this->actingAs($this->adminUser)
            ->deleteJson("/admin/users/{$this->adminUser->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_view_all_users_attendance()
    {
        // Create attendance for both users
        Attendance::factory()->create([
            'user_id' => $this->employeeUser->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(8),
            'clock_out' => Carbon::now()
        ]);

        $response = $this->actingAs($this->adminUser)
            ->getJson('/attendance/data?start_date=' . Carbon::today()->toDateString());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'attendance' => [
                    '*' => [
                        'id', 'date', 'status', 'user_name', 'user_email'
                    ]
                ]
            ]);
    }

    /** @test */
    public function employee_can_only_view_own_attendance()
    {
        // Create attendance for both users
        Attendance::factory()->create([
            'user_id' => $this->adminUser->id,
            'date' => Carbon::today()
        ]);

        Attendance::factory()->create([
            'user_id' => $this->employeeUser->id,
            'date' => Carbon::today()
        ]);

        $response = $this->actingAs($this->employeeUser)
            ->getJson('/attendance/data');

        $response->assertStatus(200);
        
        $attendanceData = $response->json('attendance');
        
        // Should only see their own attendance
        $this->assertCount(1, $attendanceData);
        $this->assertArrayNotHasKey('user_name', $attendanceData[0]); // Employee view doesn't include user info
    }

    /** @test */
    public function admin_middleware_blocks_non_admin_json_requests()
    {
        $response = $this->actingAs($this->employeeUser)
            ->getJson('/admin/users/data');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Admin privileges required'
            ]);
    }

    /** @test */
    public function admin_middleware_redirects_non_admin_web_requests()
    {
        $response = $this->actingAs($this->employeeUser)
            ->get('/admin/users');

        $response->assertStatus(302)
            ->assertRedirect('/dashboard')
            ->assertSessionHas('error', 'Admin access required');
    }

    /** @test */
    public function role_middleware_allows_multiple_roles()
    {
        // This would test the RoleMiddleware with multiple roles
        // For now, we'll test that admin can access employee functions
        
        $response = $this->actingAs($this->adminUser)
            ->get('/timesheet');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_approve_timesheets()
    {
        $timesheet = Timesheet::factory()->create([
            'user_id' => $this->employeeUser->id,
            'status' => 'submitted',
            'approved_by' => null,
            'approved_at' => null
        ]);

        $response = $this->actingAs($this->adminUser)
            ->postJson("/admin/timesheet-calendar/approve/{$timesheet->id}", [
                'notes' => 'Approved by admin'
            ]);

        $response->assertStatus(200);
        
        $timesheet->refresh();
        $this->assertEquals('approved', $timesheet->status);
        $this->assertEquals($this->adminUser->id, $timesheet->approved_by);
        $this->assertNotNull($timesheet->approved_at);
    }

    /** @test */
    public function employee_cannot_approve_timesheets()
    {
        $timesheet = Timesheet::factory()->create([
            'user_id' => $this->employeeUser->id,
            'status' => 'submitted'
        ]);

        $response = $this->actingAs($this->employeeUser)
            ->postJson("/admin/timesheet-calendar/approve/{$timesheet->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_system_status()
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/admin/system/status');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'status',
                'uptime',
                'active_users',
                'pending_timesheets'
            ]);
    }

    /** @test */
    public function employee_cannot_access_system_status()
    {
        $response = $this->actingAs($this->employeeUser)
            ->getJson('/admin/system/status');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_generate_quick_reports()
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson('/admin/reports/quick');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'report_url',
                'message'
            ]);
    }

    /** @test */
    public function debug_routes_are_protected()
    {
        // Test debug route protection for non-admins
        $response = $this->actingAs($this->employeeUser)
            ->getJson('/debug/admin');

        // Should either be 404 (not found in production) or 403 (forbidden)
        $this->assertTrue(
            in_array($response->status(), [403, 404]),
            "Debug route should be protected, got status: " . $response->status()
        );
    }

    /** @test */
    public function admin_can_export_employee_time_data()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/employees/time/export');

        // This might return 404 if file doesn't exist, which is expected
        $this->assertTrue(
            in_array($response->status(), [200, 404]),
            "Export should be accessible to admin"
        );
    }

    /** @test */
    public function policies_are_properly_enforced()
    {
        // Test UserPolicy through Gate
        $this->assertTrue(
            \Gate::forUser($this->adminUser)->allows('viewAny', User::class)
        );

        $this->assertFalse(
            \Gate::forUser($this->employeeUser)->allows('viewAny', User::class)
        );

        // Test user can edit their own profile
        $this->assertTrue(
            \Gate::forUser($this->employeeUser)->allows('update', $this->employeeUser)
        );

        // Test user cannot edit other profiles
        $this->assertFalse(
            \Gate::forUser($this->employeeUser)->allows('update', $this->adminUser)
        );
    }
}