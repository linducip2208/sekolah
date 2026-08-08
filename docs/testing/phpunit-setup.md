# PHPUnit Setup — MySQL Database

## Konfigurasi (MySQL, bukan SQLite)

Sikad Pro menggunakan MySQL 8 untuk testing agar behavior sama persis dengan production.
SQLite tidak mendukung beberapa fitur yang dipakai (JSON columns, fulltext index, dll).

---

## phpunit.xml

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         testdox="true"
>
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>

    <coverage>
        <include>
            <directory>app</directory>
            <directory>modules</directory>
        </include>
        <report>
            <html outputDirectory="coverage/html"/>
            <clover outputFile="coverage/clover.xml"/>
        </report>
    </coverage>

    <php>
        <env name="APP_ENV"           value="testing"/>
        <env name="APP_DEBUG"         value="true"/>
        <env name="APP_KEY"           value="base64:testkey1234567890123456789012="/>

        <!-- MySQL Test Database -->
        <env name="DB_CONNECTION"     value="mysql"/>
        <env name="DB_HOST"           value="127.0.0.1"/>
        <env name="DB_PORT"           value="3306"/>
        <env name="DB_DATABASE"       value="sikadpro_test"/>
        <env name="DB_USERNAME"       value="root"/>
        <env name="DB_PASSWORD"       value=""/>

        <!-- Cache & Session: array (tidak perlu Redis saat test) -->
        <env name="CACHE_DRIVER"      value="array"/>
        <env name="SESSION_DRIVER"    value="array"/>

        <!-- Queue: sync (jalankan job langsung, tidak perlu worker) -->
        <env name="QUEUE_CONNECTION"  value="sync"/>

        <!-- Mail: fake (tidak kirim email nyata) -->
        <env name="MAIL_MAILER"       value="array"/>

        <!-- Broadcasting: null (tidak butuh Pusher saat test) -->
        <env name="BROADCAST_DRIVER"  value="null"/>

        <!-- License: skip di testing -->
        <env name="LICENSE_CHECK"     value="false"/>

        <!-- Storage: local (tidak butuh S3 saat test) -->
        <env name="FILESYSTEM_DISK"   value="local"/>
    </php>

    <extensions>
        <!-- Parallel testing support -->
    </extensions>
</phpunit>
```

---

## Setup MySQL Test Database

```bash
# Buat database khusus testing (sekali saja)
mysql -u root -p -e "CREATE DATABASE sikadpro_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Atau dengan password langsung
mysql -u root -pYOUR_PASSWORD -e "CREATE DATABASE sikadpro_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

---

## TestCase Base Class

```php
// tests/TestCase.php
namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\RolePermissionSeeder;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles & permissions sebelum setiap test
        $this->seed(RolePermissionSeeder::class);
    }
}
```

---

## RefreshDatabase vs DatabaseTransactions

```php
// RefreshDatabase (default) — migrate ulang tiap test suite run
// Lebih lambat tapi isolated sempurna. Dipakai di Feature tests.
use Illuminate\Foundation\Testing\RefreshDatabase;

// DatabaseTransactions — wrap setiap test dalam transaction, rollback setelah selesai
// Lebih cepat tapi tidak bisa test queue/events lintas transaksi.
// Dipakai jika test tidak pakai queue.
use Illuminate\Foundation\Testing\DatabaseTransactions;
```

---

## Running Tests

```bash
# Setup sekali
mysql -u root -e "CREATE DATABASE IF NOT EXISTS sikadpro_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Jalankan semua test
php artisan test

# Parallel (pakai banyak proses, lebih cepat)
php artisan test --parallel --processes=4

# Satu testsuite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Filter by module
php artisan test --filter=Attendance
php artisan test --filter=BulkMarkTest

# Dengan coverage (butuh Xdebug atau PCOV)
XDEBUG_MODE=coverage php artisan test --coverage --min=80

# Stop on first failure
php artisan test --stop-on-failure
```

---

## CI/CD — GitHub Actions MySQL Setup

```yaml
# .github/workflows/deploy.yml
services:
  mysql:
    image: mysql:8.0
    env:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: sikadpro_test
      MYSQL_ALLOW_EMPTY_PASSWORD: 'yes'
    options: >-
      --health-cmd="mysqladmin ping -h localhost"
      --health-interval=10s
      --health-timeout=5s
      --health-retries=5
    ports:
      - 3306:3306

steps:
  - name: Configure test DB
    run: |
      echo "DB_CONNECTION=mysql"    >> .env.testing
      echo "DB_HOST=127.0.0.1"     >> .env.testing
      echo "DB_PORT=3306"          >> .env.testing
      echo "DB_DATABASE=sikadpro_test" >> .env.testing
      echo "DB_USERNAME=root"      >> .env.testing
      echo "DB_PASSWORD=root"      >> .env.testing
      echo "LICENSE_CHECK=false"   >> .env.testing
```

---

## Tips MySQL Testing

```php
// Gunakan factories, bukan hardcode ID
$school  = School::factory()->create();
$student = Student::factory()->for($school)->create();

// Cek database state setelah action
$this->assertDatabaseHas('attendances', [
    'school_id'  => $school->id,
    'student_id' => $student->id,
    'status'     => 'present',
]);

$this->assertDatabaseMissing('attendances', [
    'school_id'  => $school->id,
    'student_id' => $student->id,
    'status'     => 'absent',
]);

// Cek soft delete
$this->assertSoftDeleted('books', ['id' => $book->id]);

// Cek count
$this->assertDatabaseCount('fee_invoices', 10);

// JSON column assertion
$this->assertDatabaseHas('report_cards', [
    'student_id' => $student->id,
    'is_passed'  => true,
]);
```
