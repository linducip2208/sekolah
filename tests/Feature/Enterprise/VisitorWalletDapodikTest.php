<?php

namespace Tests\Feature\Enterprise;

use App\Models\Academic\Student;
use App\Models\Canteen\CanteenCategory;
use App\Models\Canteen\CanteenMenuItem;
use App\Models\Canteen\WalletTransaction;
use App\Models\Dapodik\DapodikEntityMapping;
use App\Models\Plan;
use App\Models\School;
use App\Models\User;
use App\Models\Visitor\VisitorBlacklistEntry;
use App\Services\Canteen\CanteenService;
use App\Services\Dapodik\DapodikService;
use App\Services\Integrations\Dapodik\FakeDapodikClient;
use App\Services\Visitor\VisitorService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorWalletDapodikTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_topup_purchase_and_refund_are_ledger_backed_and_idempotent(): void
    {
        [$school, $admin, $student] = $this->schoolAdminStudent();
        $category = CanteenCategory::create(['school_id' => $school->id, 'name' => 'Makanan']);
        $menu = CanteenMenuItem::create([
            'school_id' => $school->id,
            'canteen_category_id' => $category->id,
            'name' => 'Nasi Goreng',
            'price' => 10000,
            'is_available' => true,
            'stock_today' => 10,
        ]);
        $this->actingAs($admin);
        $service = app(CanteenService::class);

        $service->topUp($school->id, $student->id, $admin->id, 50000, null, 'topup-1');
        $service->topUp($school->id, $student->id, $admin->id, 50000, null, 'topup-1');
        $order = $service->placeOrder($school->id, $student->id, [['menu_item_id' => $menu->id, 'qty' => 2]], 'walkin', null, 'order-1');
        $service->placeOrder($school->id, $student->id, [['menu_item_id' => $menu->id, 'qty' => 2]], 'walkin', null, 'order-1');

        $wallet = $service->getOrCreateWallet($school->id, $student->id);
        $this->assertSame(30000, $service->ledgerBalance($wallet));
        $this->assertSame(30000, (int) $wallet->fresh()->balance);
        $this->assertSame(2, WalletTransaction::withoutGlobalScopes()->where('canteen_wallet_id', $wallet->id)->count());

        $service->refundOrder($order, 20000, 'Pesanan dibatalkan', 'refund-1');
        $service->refundOrder($order, 20000, 'Pesanan dibatalkan', 'refund-1');
        $this->assertSame(50000, $service->ledgerBalance($wallet->fresh()));
    }

    public function test_wallet_rejects_insufficient_balance_and_cross_school_menu(): void
    {
        [$school, $admin, $student] = $this->schoolAdminStudent();
        $otherSchool = School::factory()->create(['plan_id' => $school->plan_id]);
        $category = CanteenCategory::create(['school_id' => $otherSchool->id, 'name' => 'Lain']);
        $menu = CanteenMenuItem::create(['school_id' => $otherSchool->id, 'canteen_category_id' => $category->id, 'name' => 'Tidak boleh', 'price' => 1, 'is_available' => true]);
        $this->actingAs($admin);
        $this->expectException(ModelNotFoundException::class);
        app(CanteenService::class)->placeOrder($school->id, $student->id, [['menu_item_id' => $menu->id, 'qty' => 1]]);
    }

    public function test_visitor_flow_issues_badge_and_records_checkout(): void
    {
        [$school, $admin] = $this->schoolAdmin();
        $this->actingAs($admin);
        $visit = app(VisitorService::class)->register($school->id, [
            'name' => 'Budi', 'phone' => '0812', 'purpose' => 'Rapat', 'host_user_id' => $admin->id,
        ], $admin->id, true);
        $visit = app(VisitorService::class)->checkIn($school->id, $visit->id, $admin->id);
        $this->assertSame('checked_in', $visit->status);
        $this->assertNotNull($visit->badge_number);
        $visit = app(VisitorService::class)->checkOut($school->id, $visit->id, $admin->id);
        $this->assertSame('checked_out', $visit->status);
        $this->assertNotNull($visit->check_out_at);
    }

    public function test_blacklisted_visitor_cannot_register(): void
    {
        [$school, $admin] = $this->schoolAdmin();
        VisitorBlacklistEntry::create([
            'school_id' => $school->id, 'identity_type' => 'nik', 'identity_number' => '123',
            'full_name' => 'Budi', 'reason' => 'Dilarang masuk', 'is_active' => true, 'added_by' => $admin->id,
        ]);
        $this->actingAs($admin);
        $this->expectException(\RuntimeException::class);
        app(VisitorService::class)->register($school->id, [
            'name' => 'Budi', 'identity_type' => 'nik', 'identity_number' => '123', 'purpose' => 'Masuk',
        ], $admin->id);
    }

    public function test_dapodik_preview_and_sync_is_idempotent_by_external_id(): void
    {
        [$school, $admin] = $this->schoolAdmin();
        $this->actingAs($admin);
        $service = app(DapodikService::class);
        $fake = new FakeDapodikClient(['students' => [['external_id' => 'DP-1', 'name' => 'Siti', 'gender' => 'female']]]);
        $run = $service->createPreview($school->id, $admin->id, 'students', [['external_id' => 'DP-1', 'name' => 'Siti', 'gender' => 'female']]);
        $service->syncPreviewedRun($run);
        $second = $service->createPreview($school->id, $admin->id, 'students', [['external_id' => 'DP-1', 'name' => 'Siti', 'gender' => 'female']]);
        $service->syncPreviewedRun($second);

        $this->assertDatabaseCount('students', 1);
        $this->assertDatabaseHas('students', ['school_id' => $school->id, 'dapodik_id' => 'DP-1']);
        $this->assertSame(1, DapodikEntityMapping::withoutGlobalScopes()->where('school_id', $school->id)->where('external_id', 'DP-1')->count());
        $this->assertTrue($fake->testConnection($service->getOrCreateConnection($school->id))['ok']);
    }

    private function schoolAdminStudent(): array
    {
        [$school, $admin] = $this->schoolAdmin();
        $studentUser = User::factory()->create(['school_id' => $school->id]);
        $student = Student::create(['school_id' => $school->id, 'user_id' => $studentUser->id, 'admission_no' => 'S-1']);

        return [$school, $admin, $student];
    }

    private function schoolAdmin(): array
    {
        $school = School::factory()->create(['plan_id' => Plan::factory()->create()->id]);
        $admin = User::factory()->create(['school_id' => $school->id]);
        $admin->assignRole('admin');

        return [$school, $admin];
    }
}
