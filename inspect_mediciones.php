<?php
$possible = [
    __DIR__ . '/database/database.sqlite',
    __DIR__ . '/ph-monitor-laravel-main/database/database.sqlite',
    __DIR__ . '/ph-monitor-laravel-main/ph-monitor-laravel-main/database/database.sqlite'
];
$dbFile = null;
foreach ($possible as $p) {
    if (file_exists($p)) { $dbFile = $p; break; }
}
if (!$dbFile) { echo "No DB found\n"; exit(1); }
try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $rows = $pdo->query("SELECT tipo_superficie, COUNT(*) as cnt FROM mediciones GROUP BY tipo_superficie")->fetchAll(PDO::FETCH_ASSOC);
    echo "Using DB: $dbFile\n";
    foreach ($rows as $r) {
        echo "'{$r['tipo_superficie']}' => {$r['cnt']}\n";
    }
    $total = $pdo->query('SELECT COUNT(*) FROM mediciones')->fetchColumn();
    echo "Total rows: $total\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
