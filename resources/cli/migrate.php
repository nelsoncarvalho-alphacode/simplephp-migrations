<?php

$projectRoot = realpath(__DIR__ . '/..');
require_once $projectRoot . '/vendor/autoload.php';

// 📥 Carrega os hosts definidos
require_once $projectRoot . '/config/environments.php';

// 🌐 Detecta o ambiente via CLI_ENV e mapeia para um host
$env = getenv('CLI_ENV') ?: 'dev';

$hostMap = [
    'dev' => defined('DEVELOPMENT_URL') ? DEVELOPMENT_URL : null,
    'mac' => defined('MAC_URL') ? MAC_URL : null,
    'hml' => defined('TEST_URL') ? TEST_URL : null,
    'prod' => $PRODUCTION_URLS[0] ?? null
];

if (!isset($hostMap[$env]) || !$hostMap[$env]) {
    die("❌ Ambiente '$env' não configurado corretamente em environments.php.\n");
}

// 🧪 Simula o HTTP_HOST para que db.php funcione
$_SERVER['HTTP_HOST'] = $hostMap[$env];

require_once $projectRoot . '/config/db.php';

$pdoHost = (DB_HOST === 'localhost') ? '127.0.0.1' : DB_HOST;

// ✅ Conexão PDO usando as constantes do db.php
$pdo = new PDO(
    "mysql:host=" . $pdoHost . ";dbname=" . DB_NAME . ";charset=utf8",
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