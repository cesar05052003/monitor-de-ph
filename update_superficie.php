<?php

$possible = [
    __DIR__ . '/database/database.sqlite',
    __DIR__ . '/ph-monitor-laravel-main/database/database.sqlite',
    __DIR__ . '/ph-monitor-laravel-main/ph-monitor-laravel-main/database/database.sqlite'
];

$dbFile = null;
foreach ($possible as $p) {
    if (file_exists($p)) {
        $dbFile = $p;
        break;
    }
}

if (!$dbFile) {
    echo "SQLite DB file not found in any of the expected locations:\n";
    foreach ($possible as $p) echo "  - $p\n";
    exit(1);
}

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $affected = $pdo->exec("UPDATE mediciones SET tipo_superficie = 'Líquido' WHERE tipo_superficie = 'Importado'");
    if ($affected === false) {
        $err = $pdo->errorInfo();
        echo "Error executing update: " . $err[2] . "\n";
        exit(1);
    }

    echo "Update executed. Rows affected: " . $affected . "\n";
    exit(0);
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
    exit(1);
}
