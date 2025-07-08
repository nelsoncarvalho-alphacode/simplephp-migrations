<?php

$projectRoot = realpath(__DIR__ . '/..');
require_once $projectRoot . '/vendor/autoload.php';
require_once $projectRoot . '/config/db.php';

$env = getenv('CLI_ENV') ?: 'dev';
$_SERVER['HTTP_HOST'] = $env;

define('DEVELOPMENT_URL', 'dev');
define('MAC_URL', 'mac');
define('TEST_URL', 'hml');
$PRODUCTION_URLS = ['prod'];

// ✅ Conexão PDO usando as constantes do db.php
$pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
    DB_USER,
    DB_PASS
);

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$migrationsFolder = $projectRoot . '/migrations';

// 🧱 Cria tabela de controle se necessário
$pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255),
    executed TINYINT DEFAULT 0,
    run_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$migrations = scandir($migrationsFolder);
$executadas = $pdo->query("SELECT migration FROM migrations WHERE executed = 1")->fetchAll(PDO::FETCH_COLUMN);

foreach ($migrations as $migration) {
    if ($migration === '.' || $migration === '..' || $migration === '_startup.php') continue;
    if (in_array($migration, $executadas)) continue;

    echo "🔼 Executando: $migration\n";

    $classname = pathinfo($migration, PATHINFO_FILENAME);
    require_once "$migrationsFolder/$migration";

    if (!class_exists($classname)) {
        echo "❌ Classe $classname não encontrada.\n";
        continue;
    }

    $obj = new $classname();
    $sql = $obj->up();

    if (!$sql) {
        echo "⚠️ Migration $classname não retornou SQL.\n";
        continue;
    }

    try {
        $pdo->exec($sql);
        $stmt = $pdo->prepare("INSERT INTO migrations (migration, executed) VALUES (?, 1)");
        $stmt->execute([$migration]);
        echo "✅ Migration $migration executada com sucesso.\n";
    } catch (Exception $e) {
        echo "❌ Erro ao executar $migration: " . $e->getMessage() . "\n";
    }
}

echo "\n🎉 Migrations concluídas.\n";