<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $cols = DB::connection('DATA_MYSQL')->select("SHOW COLUMNS FROM scctbill_cut LIKE 'IS_SHOW'");
    if (count($cols) > 0) {
        echo "IS_SHOW already exists\n";
        exit(0);
    }

    DB::connection('DATA_MYSQL')->statement(
        "ALTER TABLE scctbill_cut ADD COLUMN IS_SHOW TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=tampil, 0=sembunyi' AFTER REASON"
    );
    echo "IS_SHOW column added\n";
} catch (Throwable $e) {
    echo "ERR: " . $e->getMessage() . "\n";
    exit(1);
}
