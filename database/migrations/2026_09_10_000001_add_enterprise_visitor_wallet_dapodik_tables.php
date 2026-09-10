<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addDapodikIdentifiers();
        $this->createVisitorTables();
        $this->extendWalletTables();
        $this->createDapodikTables();
    }

    private function addDapodikIdentifiers(): void
    {
        if (Schema::hasTable('students') && ! Schema::hasColumn('students', 'dapodik_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('dapodik_id', 150)->nullable()->after('admission_no');
                $table->index(['school_id', 'dapodik_id']);
            });
        }
        if (Schema::hasTable('staffs') && ! Schema::hasColumn('staffs', 'dapodik_id')) {
            Schema::table('staffs', function (Blueprint $table) {
                $table->string('dapodik_id', 150)->nullable()->after('employee_id');
                $table->index(['school_id', 'dapodik_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dapodik_conflicts');
        Schema::dropIfExists('dapodik_entity_mappings');
        Schema::dropIfExists('dapodik_sync_items');
        Schema::dropIfExists('dapodik_sync_runs');
        Schema::dropIfExists('dapodik_connections');

        Schema::dropIfExists('wallet_settlements');
        Schema::dropIfExists('wallet_refunds');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('canteen_order_items');
        Schema::dropIfExists('canteen_merchants');

        Schema::dropIfExists('visitor_audit_logs');
        Schema::dropIfExists('visitor_badges');
        Schema::dropIfExists('visitor_blacklists');
        Schema::dropIfExists('visitor_visits');
        Schema::dropIfExists('visitors');
    }

    private function createVisitorTables(): void
    {
        if (! Schema::hasTable('visitors')) {
            Schema::create('visitors', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->string('name', 200);
                $table->string('identity_type', 30)->nullable();
                $table->string('identity_number', 80)->nullable();
                $table->string('phone', 30)->nullable();
                $table->string('email')->nullable();
                $table->string('photo_path')->nullable();
                $table->string('company', 150)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['school_id', 'identity_number']);
                $table->index(['school_id', 'phone']);
            });
        }

        if (! Schema::hasTable('visitor_visits')) {
            Schema::create('visitor_visits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('visitor_id')->constrained('visitors')->restrictOnDelete();
                $table->date('visit_date');
                $table->string('purpose', 255);
                $table->foreignId('host_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('destination', 150)->nullable();
                $table->dateTime('expected_arrival')->nullable();
                $table->dateTime('check_in_at')->nullable();
                $table->dateTime('check_out_at')->nullable();
                $table->string('badge_number', 30)->nullable();
                $table->string('qr_token', 128)->nullable()->unique();
                $table->string('invitation_token', 128)->nullable()->unique();
                $table->string('status', 30)->default('pending');
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('pre_registered')->default(false);
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->index(['school_id', 'visit_date', 'status']);
                $table->index(['school_id', 'check_in_at', 'check_out_at']);
            });
        }

        if (! Schema::hasTable('visitor_blacklists')) {
            Schema::create('visitor_blacklists', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('visitor_id')->nullable()->constrained('visitors')->nullOnDelete();
                $table->string('identity_type', 30)->nullable();
                $table->string('identity_number', 80)->nullable();
                $table->string('full_name', 200);
                $table->text('reason');
                $table->boolean('is_active')->default(true);
                $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->index(['school_id', 'identity_number', 'is_active']);
            });
        }

        if (! Schema::hasTable('visitor_badges')) {
            Schema::create('visitor_badges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('visitor_visit_id')->constrained('visitor_visits')->restrictOnDelete();
                $table->string('badge_number', 30);
                $table->string('qr_token', 128)->unique();
                $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('issued_at');
                $table->dateTime('returned_at')->nullable();
                $table->string('status', 30)->default('issued');
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['school_id', 'badge_number']);
                $table->index(['school_id', 'status']);
            });
        }

        if (! Schema::hasTable('visitor_audit_logs')) {
            Schema::create('visitor_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('visitor_id')->nullable()->constrained('visitors')->nullOnDelete();
                $table->foreignId('visitor_visit_id')->nullable()->constrained('visitor_visits')->nullOnDelete();
                $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('event', 50);
                $table->ipAddress('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->index(['school_id', 'event', 'created_at']);
            });
        }
    }

    private function extendWalletTables(): void
    {
        if (! Schema::hasTable('canteen_merchants')) {
            Schema::create('canteen_merchants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->string('name', 150);
                $table->string('phone', 30)->nullable();
                $table->string('email')->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('settings')->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['school_id', 'name']);
            });
        }

        if (Schema::hasTable('canteen_menu_items') && ! Schema::hasColumn('canteen_menu_items', 'canteen_merchant_id')) {
            Schema::table('canteen_menu_items', function (Blueprint $table) {
                $table->foreignId('canteen_merchant_id')->nullable()->after('school_id')->constrained('canteen_merchants')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('canteen_order_items')) {
            Schema::create('canteen_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('canteen_order_id')->constrained('canteen_orders')->restrictOnDelete();
                $table->foreignId('canteen_menu_item_id')->nullable()->constrained('canteen_menu_items')->nullOnDelete();
                $table->string('name', 200);
                $table->unsignedBigInteger('unit_price');
                $table->unsignedInteger('quantity');
                $table->unsignedBigInteger('subtotal');
                $table->timestamps();
                $table->index(['school_id', 'canteen_order_id']);
            });
        }

        if (Schema::hasTable('canteen_orders')) {
            Schema::table('canteen_orders', function (Blueprint $table) {
                if (! Schema::hasColumn('canteen_orders', 'canteen_merchant_id')) {
                    $table->foreignId('canteen_merchant_id')->nullable()->after('canteen_wallet_id')->constrained('canteen_merchants')->nullOnDelete();
                }
                if (! Schema::hasColumn('canteen_orders', 'idempotency_key')) {
                    $table->string('idempotency_key', 120)->nullable()->after('order_no');
                    $table->unique(['school_id', 'idempotency_key']);
                }
            });
        }

        if (Schema::hasTable('canteen_wallets')) {
            Schema::table('canteen_wallets', function (Blueprint $table) {
                if (! Schema::hasColumn('canteen_wallets', 'monthly_limit')) {
                    $table->unsignedInteger('monthly_limit')->default(0)->after('daily_limit');
                }
                if (! Schema::hasColumn('canteen_wallets', 'low_balance_threshold')) {
                    $table->unsignedInteger('low_balance_threshold')->default(0)->after('monthly_limit');
                }
                if (! Schema::hasColumn('canteen_wallets', 'allow_negative')) {
                    $table->boolean('allow_negative')->default(false)->after('is_locked');
                }
                if (! Schema::hasColumn('canteen_wallets', 'transfer_enabled')) {
                    $table->boolean('transfer_enabled')->default(false)->after('allow_negative');
                }
            });
        }

        if (Schema::hasTable('canteen_topups')) {
            Schema::table('canteen_topups', function (Blueprint $table) {
                if (! Schema::hasColumn('canteen_topups', 'idempotency_key')) {
                    $table->string('idempotency_key', 100)->nullable()->after('status');
                    $table->unique(['school_id', 'idempotency_key']);
                }
                if (! Schema::hasColumn('canteen_topups', 'completed_at')) {
                    $table->dateTime('completed_at')->nullable()->after('status');
                }
                if (! Schema::hasColumn('canteen_topups', 'metadata')) {
                    $table->json('metadata')->nullable()->after('completed_at');
                }
            });
        }

        if (! Schema::hasTable('wallet_transactions')) {
            Schema::create('wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
                $table->foreignId('canteen_wallet_id')->constrained('canteen_wallets')->restrictOnDelete();
                $table->string('type', 20);
                $table->string('transaction_type', 30);
                $table->unsignedBigInteger('amount');
                $table->bigInteger('balance_after');
                $table->string('reference_type', 100)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('idempotency_key', 120)->nullable();
                $table->string('description', 255)->nullable();
                $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->unique(['school_id', 'idempotency_key'], 'wallet_tx_school_idempotency_unique');
                $table->index(['school_id', 'canteen_wallet_id', 'created_at'], 'wallet_tx_wallet_created_idx');
                $table->index(['school_id', 'student_id', 'transaction_type', 'created_at'], 'wallet_tx_student_created_idx');
                $table->index(['reference_type', 'reference_id'], 'wallet_tx_reference_idx');
            });
        } else {
            $indexes = collect(Schema::getIndexes('wallet_transactions'))->pluck('name');
            Schema::table('wallet_transactions', function (Blueprint $table) use ($indexes): void {
                if (! $indexes->contains('wallet_tx_wallet_created_idx')) {
                    $table->index(['school_id', 'canteen_wallet_id', 'created_at'], 'wallet_tx_wallet_created_idx');
                }
                if (! $indexes->contains('wallet_tx_student_created_idx')) {
                    $table->index(['school_id', 'student_id', 'transaction_type', 'created_at'], 'wallet_tx_student_created_idx');
                }
                if (! $indexes->contains('wallet_tx_reference_idx')) {
                    $table->index(['reference_type', 'reference_id'], 'wallet_tx_reference_idx');
                }
            });
        }

        if (Schema::hasTable('wallet_transactions') && ! DB::table('wallet_transactions')->where('transaction_type', 'opening_balance')->exists()) {
            DB::table('canteen_wallets')->where('balance', '>', 0)->orderBy('id')->each(function ($wallet): void {
                DB::table('wallet_transactions')->insert([
                    'school_id' => $wallet->school_id,
                    'student_id' => $wallet->student_id,
                    'canteen_wallet_id' => $wallet->id,
                    'type' => 'credit',
                    'transaction_type' => 'opening_balance',
                    'amount' => $wallet->balance,
                    'balance_after' => $wallet->balance,
                    'description' => 'Saldo awal wallet saat migrasi ledger',
                    'metadata' => json_encode(['migrated_from_balance' => true]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        }

        if (! Schema::hasTable('wallet_refunds')) {
            Schema::create('wallet_refunds', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('canteen_wallet_id')->constrained('canteen_wallets')->restrictOnDelete();
                $table->foreignId('canteen_order_id')->constrained('canteen_orders')->restrictOnDelete();
                $table->foreignId('wallet_transaction_id')->nullable()->constrained('wallet_transactions')->nullOnDelete();
                $table->unsignedBigInteger('amount');
                $table->string('reason', 255);
                $table->string('status', 20)->default('completed');
                $table->string('idempotency_key', 120)->nullable();
                $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['school_id', 'idempotency_key']);
            });
        }

        if (! Schema::hasTable('wallet_settlements')) {
            Schema::create('wallet_settlements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('canteen_merchant_id')->nullable()->constrained('canteen_merchants')->nullOnDelete();
                $table->date('period_start');
                $table->date('period_end');
                $table->unsignedBigInteger('gross_sales')->default(0);
                $table->unsignedBigInteger('refunds')->default(0);
                $table->unsignedBigInteger('net_amount')->default(0);
                $table->string('status', 20)->default('draft');
                $table->dateTime('settled_at')->nullable();
                $table->unsignedBigInteger('journal_entry_id')->nullable();
                $table->string('idempotency_key', 120)->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['school_id', 'idempotency_key']);
                $table->index(['school_id', 'period_start', 'period_end']);
            });
        }
    }

    private function createDapodikTables(): void
    {
        if (! Schema::hasTable('dapodik_connections')) {
            Schema::create('dapodik_connections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('connection_type', 30)->default('rest');
                $table->string('host', 500)->nullable();
                $table->string('npsn', 15);
                $table->string('token_encrypted', 700)->nullable();
                $table->string('username_encrypted', 700)->nullable();
                $table->string('password_encrypted', 700)->nullable();
                $table->string('registration_code_encrypted', 700)->nullable();
                $table->unsignedSmallInteger('timeout')->default(30);
                $table->boolean('verify_ssl')->default(true);
                $table->string('status', 30)->default('unconfigured');
                $table->json('field_mappings')->nullable();
                $table->timestamp('last_tested_at')->nullable();
                $table->timestamp('last_sync_at')->nullable();
                $table->timestamps();
            });

            if (Schema::hasTable('dapodik_config')) {
                DB::table('dapodik_config')->orderBy('id')->each(function ($legacy): void {
                    DB::table('dapodik_connections')->updateOrInsert(
                        ['school_id' => $legacy->school_id],
                        [
                            'connection_type' => 'rest',
                            'host' => $legacy->endpoint_url,
                            'npsn' => $legacy->npsn,
                            'username_encrypted' => $legacy->username_encrypted,
                            'password_encrypted' => $legacy->password_encrypted,
                            'field_mappings' => $legacy->field_mappings,
                            'last_sync_at' => $legacy->last_sync_at,
                            'status' => $legacy->endpoint_url ? 'configured' : 'unconfigured',
                            'created_at' => $legacy->created_at,
                            'updated_at' => $legacy->updated_at,
                        ]
                    );
                });
            }
        }

        if (! Schema::hasTable('dapodik_sync_runs')) {
            Schema::create('dapodik_sync_runs', function (Blueprint $table) {
                $table->id();
                $table->uuid('run_uuid')->unique();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->string('entity_type', 50);
                $table->string('direction', 20)->default('import');
                $table->string('status', 20)->default('queued');
                $table->dateTime('started_at')->nullable();
                $table->dateTime('completed_at')->nullable();
                $table->unsignedInteger('total')->default(0);
                $table->unsignedInteger('inserted')->default(0);
                $table->unsignedInteger('updated')->default(0);
                $table->unsignedInteger('unchanged')->default(0);
                $table->unsignedInteger('conflicted')->default(0);
                $table->unsignedInteger('failed')->default(0);
                $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('error_summary')->nullable();
                $table->timestamps();
                $table->index(['school_id', 'entity_type', 'created_at']);
                $table->index(['school_id', 'status']);
            });
        }

        if (! Schema::hasTable('dapodik_sync_items')) {
            Schema::create('dapodik_sync_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('dapodik_sync_run_id')->constrained('dapodik_sync_runs')->cascadeOnDelete();
                $table->string('entity_type', 50);
                $table->string('external_id', 150);
                $table->string('local_type', 150)->nullable();
                $table->unsignedBigInteger('local_id')->nullable();
                $table->string('status', 20)->default('new');
                $table->json('source_payload')->nullable();
                $table->json('normalized_payload')->nullable();
                $table->json('local_snapshot')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();
                $table->unique(['dapodik_sync_run_id', 'entity_type', 'external_id'], 'dapodik_run_item_unique');
                $table->index(['school_id', 'entity_type', 'external_id']);
            });
        }

        if (! Schema::hasTable('dapodik_entity_mappings')) {
            Schema::create('dapodik_entity_mappings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->string('entity_type', 50);
                $table->string('external_id', 150);
                $table->string('local_type', 150);
                $table->unsignedBigInteger('local_id');
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();
                $table->unique(['school_id', 'entity_type', 'external_id'], 'dapodik_mapping_external_unique');
                $table->unique(['school_id', 'entity_type', 'local_type', 'local_id'], 'dapodik_mapping_local_unique');
            });
        }

        if (! Schema::hasTable('dapodik_conflicts')) {
            Schema::create('dapodik_conflicts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('school_id')->constrained()->cascadeOnDelete();
                $table->foreignId('dapodik_sync_item_id')->constrained('dapodik_sync_items')->cascadeOnDelete();
                $table->string('field_name', 100);
                $table->text('local_value')->nullable();
                $table->text('external_value')->nullable();
                $table->string('resolution', 30)->default('pending');
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->text('resolution_note')->nullable();
                $table->timestamps();
                $table->index(['school_id', 'resolution']);
            });
        }
    }
};
