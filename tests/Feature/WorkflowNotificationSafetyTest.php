<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Services\Notification\NotificationDispatcher;
use App\Services\Workflow\WorkflowService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class WorkflowNotificationSafetyTest extends TestCase
{
    public function test_workflow_is_tenant_bound_and_cannot_be_approved_twice(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $adminA = User::factory()->create(['school_id' => $schoolA->id]);
        $adminA->assignRole('admin');
        $adminB = User::factory()->create(['school_id' => $schoolB->id]);
        $adminB->assignRole('admin');
        $service = app(WorkflowService::class);
        $workflow = $service->create($schoolA->id, $adminA->id, [
            'type' => 'other',
            'title' => 'Permintaan lintas sekolah harus aman',
        ]);

        $this->actingAs($adminB, 'sanctum');
        $this->expectException(HttpException::class);
        $service->approve($workflow);
    }

    public function test_approved_workflow_cannot_be_approved_again(): void
    {
        $school = School::factory()->create();
        $admin = User::factory()->create(['school_id' => $school->id]);
        $admin->assignRole('admin');
        $service = app(WorkflowService::class);
        $workflow = $service->create($school->id, $admin->id, [
            'type' => 'other',
            'title' => 'Workflow satu kali',
        ]);

        $this->actingAs($admin, 'sanctum');
        $service->approve($workflow);
        $this->expectException(HttpException::class);
        $service->approve($workflow->fresh());
    }

    public function test_notification_dispatcher_discards_foreign_recipients(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $foreignUser = User::factory()->create(['school_id' => $schoolB->id]);

        $result = app(NotificationDispatcher::class)->dispatch(
            $schoolA->id,
            [$foreignUser->id],
            'security.test',
            'Notifikasi uji',
            'Tidak boleh terkirim ke sekolah lain.',
        );

        $this->assertSame(['push' => null, 'sms' => null, 'whatsapp' => null], $result);
        $this->assertDatabaseMissing('notifications_log', [
            'school_id' => $schoolA->id,
            'user_id' => $foreignUser->id,
        ]);
    }
}
