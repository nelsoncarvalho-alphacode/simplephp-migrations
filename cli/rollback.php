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

// Conecta ao banco com PDO
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
        DB_USER,
        DB_PASS
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Erro ao conectar no banco: " . $e->getMessage() . "\n");
}

// Local da pasta de migrations
$migrationsFolder = $projectRoot . '/migrations';

// Busca a última migration executada (excluindo _startup.php)
$ultimaMigration = $pdo->query("
    SELECT migration FROM migrations 
    WHERE executed = 1 AND migration != '_startup.php' 
    ORDER BY id DESC LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

if (!$ultimaMigration) {
    die("⚠️ Nenhuma migration para rollback no ambiente \"$env\".\n");
}

$migration = $ultimaMigration['migration'];
$classname = pathinfo($migration, PATHINFO_FILENAME);
$path = "$migrationsFolder/$migration";

if (!file_exists($path)) {
    die("❌ Arquivo da migration \"$migration\" não encontrado.\n");
}

require_once $path;

if (!class_exists($classname)) {
    die("❌ Classe \"$classname\" não encontrada dentro do arquivo.\n");
}

$obj = new $classname();
$sql = $obj->down();

if (!$sql) {
    die("⚠️ Método down() da migration \"$classname\" está vazio ou inválido.\n");
}

try {
    $pdo->exec($sql);
    $pdo->prepare("UPDATE migrations SET executed = 0 WHERE migration = ?")
        ->execute([$migration]);

    echo "✅ Rollback executado com sucesso para \"$migration\" no ambiente \"$env\".\n";
} catch (PDOException $e) {
    echo "❌ Erro ao executar rollback: " . $e->getMessage() . "\n";
}