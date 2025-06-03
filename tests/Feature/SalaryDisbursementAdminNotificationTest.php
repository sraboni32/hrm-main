<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\SalaryDisbursement;
use App\Notifications\SalaryDisbursementAdminNotification;
use Illuminate\Support\Facades\Notification;

class SalaryDisbursementAdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_receives_notification_when_employee_reviews_salary()
    {
        // Fake notifications to capture them
        Notification::fake();

        // Create a super admin user
        $admin = User::factory()->create(['role_users_id' => 1]);
        
        // Create an employee user
        $employeeUser = User::factory()->create(['role_users_id' => 2]);
        $employee = Employee::factory()->create([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'basic_salary' => 5000
        ]);
        
        // Associate employee with user (assuming there's a relationship)
        // This might need adjustment based on your actual model relationships
        
        // Create a salary disbursement
        $disbursement = SalaryDisbursement::create([
            'employee_id' => $employee->id,
            'month' => '2024-01',
            'basic_salary' => 5000,
            'adjustments' => 0,
            'leave_deductions' => 0,
            'bonus_allowance' => 500,
            'gross_salary' => 5500,
            'net_payable' => 5500,
            'status' => 'sent_for_review'
        ]);

        // Act as the employee and submit a review
        $response = $this->actingAs($employeeUser)
            ->postJson("/salary-disbursement/{$disbursement->id}/review", [
                'status' => 'reviewed',
                'feedback' => null
            ]);

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert that the admin received the notification
        Notification::assertSentTo(
            $admin,
            SalaryDisbursementAdminNotification::class,
            function ($notification, $channels) use ($disbursement) {
                $data = $notification->toArray(null);
                return $data['salary_disbursement_id'] === $disbursement->id &&
                       $data['review_status'] === 'reviewed' &&
                       $data['employee_name'] === 'John Doe';
            }
        );
    }

    /** @test */
    public function admin_receives_high_priority_notification_for_feedback()
    {
        // Fake notifications to capture them
        Notification::fake();

        // Create a super admin user
        $admin = User::factory()->create(['role_users_id' => 1]);
        
        // Create an employee user
        $employeeUser = User::factory()->create(['role_users_id' => 2]);
        $employee = Employee::factory()->create([
            'firstname' => 'Jane',
            'lastname' => 'Smith',
            'basic_salary' => 6000
        ]);

        // Create a salary disbursement
        $disbursement = SalaryDisbursement::create([
            'employee_id' => $employee->id,
            'month' => '2024-01',
            'basic_salary' => 6000,
            'adjustments' => 0,
            'leave_deductions' => 200,
            'bonus_allowance' => 0,
            'gross_salary' => 5800,
            'net_payable' => 5800,
            'status' => 'sent_for_review'
        ]);

        // Act as the employee and submit feedback
        $response = $this->actingAs($employeeUser)
            ->postJson("/salary-disbursement/{$disbursement->id}/review", [
                'status' => 'feedback',
                'feedback' => 'I believe the leave deduction is incorrect.'
            ]);

        // Assert the response is successful
        $response->assertStatus(200);

        // Assert that the admin received a high priority notification
        Notification::assertSentTo(
            $admin,
            SalaryDisbursementAdminNotification::class,
            function ($notification, $channels) use ($disbursement) {
                $data = $notification->toArray(null);
                return $data['salary_disbursement_id'] === $disbursement->id &&
                       $data['review_status'] === 'feedback' &&
                       $data['priority'] === 'high' &&
                       $data['employee_name'] === 'Jane Smith';
            }
        );
    }
}
