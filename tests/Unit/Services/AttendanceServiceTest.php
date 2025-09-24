<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AttendanceService;
use App\Repositories\AttendanceRepository;
use App\Models\User;
use App\Models\Role;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected AttendanceService $attendanceService;
    protected $attendanceRepositoryMock;
    protected User $testUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->attendanceRepositoryMock = Mockery::mock(AttendanceRepository::class);
        $this->attendanceService = new AttendanceService($this->attendanceRepositoryMock);
        
        // Create test user with role
        $role = Role::factory()->create(['name' => 'employee']);
        $this->testUser = User::factory()->create(['role_id' => $role->id]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_clock_in_successfully()
    {
        // Arrange
        $this->attendanceRepositoryMock
            ->shouldReceive('getActiveSession')
            ->once()
            ->with($this->testUser->id)
            ->andReturn(null);

        // Act
        $result = $this->attendanceService->clockIn($this->testUser);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('clocked_in', $result['status']);
        $this->assertArrayHasKey('attendance', $result);
        
        // Verify database
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->testUser->id,
            'date' => Carbon::today()->toDateString()
        ]);
    }

    /** @test */
    public function it_prevents_double_clock_in()
    {
        // Arrange
        $activeAttendance = Attendance::factory()->create([
            'user_id' => $this->testUser->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHour(),
            'clock_out' => null
        ]);

        $this->attendanceRepositoryMock
            ->shouldReceive('getActiveSession')
            ->once()
            ->with($this->testUser->id)
            ->andReturn($activeAttendance);

        // Act
        $result = $this->attendanceService->clockIn($this->testUser);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('already_clocked_in', $result['status']);
        $this->assertStringContains('Already clocked in', $result['message']);
    }

    /** @test */
    public function it_enforces_maximum_daily_sessions()
    {
        // Arrange - Create 5 existing sessions for today
        Attendance::factory()->count(5)->create([
            'user_id' => $this->testUser->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHours(2),
            'clock_out' => Carbon::now()->subHour()
        ]);

        $this->attendanceRepositoryMock
            ->shouldReceive('getActiveSession')
            ->once()
            ->with($this->testUser->id)
            ->andReturn(null);

        // Act
        $result = $this->attendanceService->clockIn($this->testUser);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('max_sessions_reached', $result['status']);
        $this->assertStringContains('Maximum daily clock-in sessions reached', $result['message']);
    }

    /** @test */
    public function it_can_clock_out_successfully()
    {
        // Arrange
        $activeAttendance = Attendance::factory()->create([
            'user_id' => $this->testUser->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHour(), // 1 hour ago
            'clock_out' => null
        ]);

        $this->attendanceRepositoryMock
            ->shouldReceive('getActiveSession')
            ->once()
            ->with($this->testUser->id)
            ->andReturn($activeAttendance);

        // Act
        $result = $this->attendanceService->clockOut($this->testUser);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('clocked_out', $result['status']);
        $this->assertArrayHasKey('total_hours', $result);
        
        // Verify database
        $activeAttendance->refresh();
        $this->assertNotNull($activeAttendance->clock_out);
    }

    /** @test */
    public function it_enforces_minimum_work_duration()
    {
        // Arrange - Create attendance with very short duration
        $activeAttendance = Attendance::factory()->create([
            'user_id' => $this->testUser->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subMinutes(5), // Only 5 minutes ago
            'clock_out' => null
        ]);

        $this->attendanceRepositoryMock
            ->shouldReceive('getActiveSession')
            ->once()
            ->with($this->testUser->id)
            ->andReturn($activeAttendance);

        // Act
        $result = $this->attendanceService->clockOut($this->testUser);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('minimum_duration_not_met', $result['status']);
        $this->assertStringContains('Minimum work duration is 15 minutes', $result['message']);
    }

    /** @test */
    public function it_prevents_clock_out_without_active_session()
    {
        // Arrange
        $this->attendanceRepositoryMock
            ->shouldReceive('getActiveSession')
            ->once()
            ->with($this->testUser->id)
            ->andReturn(null);

        $this->attendanceRepositoryMock
            ->shouldReceive('getTodayCompletedSessions')
            ->once()
            ->with($this->testUser->id)
            ->andReturn(collect());

        // Act
        $result = $this->attendanceService->clockOut($this->testUser);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('no_active_session', $result['status']);
        $this->assertStringContains('No active clock-in session found', $result['message']);
    }

    /** @test */
    public function it_returns_correct_status_when_clocked_in()
    {
        // Arrange
        $activeAttendance = Attendance::factory()->create([
            'user_id' => $this->testUser->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::now()->subHour(),
            'clock_out' => null
        ]);

        $this->attendanceRepositoryMock
            ->shouldReceive('getActiveSession')
            ->once()
            ->with($this->testUser->id)
            ->andReturn($activeAttendance);

        // Act
        $result = $this->attendanceService->getAttendanceStatus($this->testUser);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertEquals('clocked_in', $result['status']);
        $this->assertTrue($result['is_clocked_in']);
        $this->assertFalse($result['is_clocked_out']);
        $this->assertTrue($result['can_clock_out']);
        $this->assertFalse($result['can_clock_in']);
    }

    /** @test */
    public function it_validates_attendance_business_rules()
    {
        // Arrange
        $data = [
            'clock_in' => Carbon::now()->subHours(15)->toTimeString(), // 15 hours ago
            'clock_out' => Carbon::now()->toTimeString(),
            'date' => Carbon::now()->startOfWeek()->addDay()->toDateString() // Monday
        ];

        // Act
        $result = $this->attendanceService->validateAttendanceRules($this->testUser, $data);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertContains('Work duration exceeds 12 hours', $result['errors']);
    }

    /** @test */
    public function it_validates_weekend_work_rules_for_non_admin()
    {
        // Arrange
        $weekendDate = Carbon::now()->next(Carbon::SATURDAY)->toDateString();
        $data = [
            'date' => $weekendDate,
            'clock_in' => '09:00',
            'clock_out' => '17:00'
        ];

        // Act
        $result = $this->attendanceService->validateAttendanceRules($this->testUser, $data);

        // Assert
        $this->assertFalse($result['valid']);
        $this->assertContains('Weekend work requires manager approval', $result['errors']);
    }

    /** @test */
    public function it_allows_weekend_work_for_admin()
    {
        // Arrange
        $adminRole = Role::factory()->create(['name' => 'admin']);
        $adminUser = User::factory()->create(['role_id' => $adminRole->id]);
        
        $weekendDate = Carbon::now()->next(Carbon::SATURDAY)->toDateString();
        $data = [
            'date' => $weekendDate,
            'clock_in' => '09:00',
            'clock_out' => '17:00'
        ];

        // Act
        $result = $this->attendanceService->validateAttendanceRules($adminUser, $data);

        // Assert
        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    /** @test */
    public function it_generates_attendance_report()
    {
        // Arrange
        $filters = [
            'start_date' => Carbon::now()->startOfMonth()->toDateString(),
            'end_date' => Carbon::now()->endOfMonth()->toDateString(),
            'user_id' => $this->testUser->id,
            'is_admin' => false
        ];

        $mockData = collect([
            (object)['date' => Carbon::today()->toDateString(), 'total_minutes' => 480]
        ]);

        $this->attendanceRepositoryMock
            ->shouldReceive('getUserAttendanceReport')
            ->once()
            ->with($this->testUser->id, $filters)
            ->andReturn($mockData);

        // Act
        $result = $this->attendanceService->generateReport($filters);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('generated_at', $result);
    }

    /** @test */
    public function it_caches_attendance_data_for_regular_users()
    {
        // Arrange
        $filters = ['user_id' => $this->testUser->id, 'is_admin' => false];
        
        $this->attendanceRepositoryMock
            ->shouldReceive('getAttendanceData')
            ->once()
            ->with($filters)
            ->andReturn(['success' => true, 'data' => []]);

        // Act - Call twice to test caching
        $result1 = $this->attendanceService->getAttendanceData($filters);
        $result2 = $this->attendanceService->getAttendanceData($filters);

        // Assert
        $this->assertTrue($result1['success']);
        $this->assertTrue($result2['success']);
        // Repository should only be called once due to caching
    }

    /** @test */
    public function it_does_not_cache_attendance_data_for_admins()
    {
        // Arrange
        $filters = ['user_id' => $this->testUser->id, 'is_admin' => true];
        
        $this->attendanceRepositoryMock
            ->shouldReceive('getAttendanceData')
            ->twice() // Should be called twice (no caching)
            ->with($filters)
            ->andReturn(['success' => true, 'data' => []]);

        // Act
        $result1 = $this->attendanceService->getAttendanceData($filters);
        $result2 = $this->attendanceService->getAttendanceData($filters);

        // Assert
        $this->assertTrue($result1['success']);
        $this->assertTrue($result2['success']);
    }
}