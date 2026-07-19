<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver !== 'mysql' && $driver !== 'mariadb') {
            return;
        }

        $this->addFulltext('users', ['name', 'email']);
        $this->addFulltext('students', ['admission_no', 'address', 'guardian_name']);
        $this->addFulltext('fee_invoices', ['invoice_no', 'period']);
        $this->addFulltext('notices', ['title', 'body']);
        $this->addFulltext('staffs', ['employee_id', 'department', 'designation']);
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver !== 'mysql' && $driver !== 'mariadb') {
            return;
        }

        $this->dropFulltext('users', 'users_fulltext');
        $this->dropFulltext('students', 'students_fulltext');
        $this->dropFulltext('fee_invoices', 'fee_invoices_fulltext');
        $this->dropFulltext('notices', 'notices_fulltext');
        $this->dropFulltext('staffs', 'staffs_fulltext');
    }

    private function addFulltext(string $table, array $columns): void
    {
        if (!Schema::hasTable($table)) return;

        foreach ($columns as $c) {
            if (!Schema::hasColumn($table, $c)) return;
        }

        $idxName = $table . '_fulltext';
        $exists = DB::selectOne(
            "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?",
            [$table, $idxName]
        );
        if ($exists && (int) $exists->cnt > 0) return;

        $cols = implode('`,`', $columns);
        DB::statement("ALTER TABLE `{$table}` ADD FULLTEXT INDEX `{$idxName}` (`{$cols}`)");
    }

    private function dropFulltext(string $table, string $idxName): void
    {
        if (!Schema::hasTable($table)) return;
        $exists = DB::selectOne(
            "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?",
            [$table, $idxName]
        );
        if ($exists && (int) $exists->cnt > 0) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$idxName}`");
        }
    }
};
