<?php

namespace Alphacode\Migrations;

use Alphacode\Migrations\Validator;

class SimplePHPCommand
{
    public function handle(array $argv): void
    {
        $command = $argv[1] ?? null;
        $projectPath = getcwd();
        $cliPath = $projectPath . '/cli';

        if (!$command) {
            $this->printHelp();
            return;
        }

        // Comando com ambiente: migrate:dev, rollback:prod, etc.
        if (str_contains($command, ':')) {
            [$action, $env] = explode(':', $command);
            putenv("CLI_ENV=$env");
            $_SERVER['HTTP_HOST'] = $env;

            $scripts = [
                'migrate' => "$cliPath/migrate.php",
                'rollback' => "$cliPath/rollback.php"
            ];

            if (isset($scripts[$action])) {
                if (!file_exists($scripts[$action])) {
                    echo "❌ Script {$scripts[$action]} não encontrado.\n";
                    return;
                }

                passthru("php " . escapeshellarg($scripts[$action]));
                return;
            }
        }

        // Validação
        if ($command === 'validate') {
            Validator::validate($projectPath);
            return;
        }

        // Inicialização do projeto
        if ($command === 'init') {
            $this->initProject($projectPath);
            return;
        }

        // Criação de migrations
        if ($command === 'make:migration') {
            $name = $argv[2] ?? null;
            if (!$name) {
                echo "❌ Nome da migration não informado.\n";
                return;
            }
            $this->makeMigration($projectPath, $name);
            return;
        }

        $this->printHelp("❌ Comando inválido: $command");
    }


    private function initProject(string $path): void
    {
        echo "📦 Iniciando projeto SimplePHP...\n";

        $composerJson = "$path/composer.json";
        $vendorAutoload = "$path/vendor/autoload.php";

        chdir($path);

        // 1. Criar composer.json básico se não existir
        if (!file_exists($composerJson)) {
            echo "📝 Criando composer.json...\n";
            shell_exec("composer init -n --name='project/simplephp' --type=project");

            // Inserir require e autoload manualmente (evita erros de terminal)
            $composerData = json_decode(file_get_contents($composerJson), true);
            $composerData['require']['php'] = ">=7.4";
            $composerData['autoload'] = [
                'psr-4' => [
                    'Alphacode\\Migrations\\' => 'vendor/alphacode/simplephp-migrations/src/'
                ]
            ];
            file_put_contents($composerJson, json_encode($composerData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            // Gerar autoload
            shell_exec("composer dump-autoload");
        }

        // 2. Rodar composer install se vendor/autoload não existir
        if (!file_exists($vendorAutoload)) {
            echo "📥 Instalando dependências...\n";
            shell_exec("composer install");
        }

        // 3. Verifica e adiciona o pacote alphacode/simplephp-migrations se necessário
        $composer = json_decode(file_get_contents($composerJson), true);
        $requires = $composer['require'] ?? [];

        if (!array_key_exists('alphacode/simplephp-migrations', $requires)) {
            echo "➕ Adicionando alphacode/simplephp-migrations...\n";
            shell_exec("composer require alphacode/simplephp-migrations");
        }

        // 4. Criar pasta migrations
        $migrationDir = "$path/migrations";
        if (!is_dir($migrationDir)) {
            mkdir($migrationDir, 0777, true);
            echo "📁 Pasta migrations criada.\n";
        }

        // cria pasta cli se necessário
        $cliDir = "$path/cli";
        if (!is_dir($cliDir)) {
            mkdir($cliDir);
        }

        // copia scripts
        copy(__DIR__ . '/../resources/cli/migrate.php', "$cliDir/migrate.php");
        copy(__DIR__ . '/../resources/cli/rollback.php', "$cliDir/rollback.php");

        // 5. Copiar migration inicial
        $stub = __DIR__ . '/../migrations/_0000_00_00_000000_init_project_structure.php';
        $target = "$migrationDir/_0000_00_00_000000_init_project_structure.php";
        if (!file_exists($target)) {
            copy($stub, $target);
            echo "📄 Migration inicial criada.\n";
        }

        echo "\n✅ Projeto pronto! Agora use:\n";
        echo "  simplephp make:migration nome_da_migration  - Cria uma nova migration\n";
        echo "  simplephp migrate:dev                       - Executa migrations no ambiente DEV\n";
        echo "  simplephp migrate:mac                       - Executa migrations no MAC\n";
        echo "  simplephp migrate:hml                       - Executa migrations na HOMOLOGAÇÃO\n";
        echo "  simplephp migrate:prod                      - Executa migrations na PRODUÇÃO\n";
        echo "  simplephp rollback:dev                      - Rollback no ambiente DEV\n";
        echo "  simplephp rollback:prod                     - Rollback no ambiente PROD\n";
        echo "  simplephp validate                          - Valida se as migrations estão corretas\n";
        echo "\n";
    }

    private function makeMigration(string $path, string $name): void
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/[^a-z0-9_]/', '_', $name);

        $timestamp = date('Y_m_d_His');
        $filename = "_{$timestamp}_{$name}.php";
        $classname = ucfirst(str_replace('.php', '', $filename));

        $stub = <<<PHP
<?php

class $classname
{
    public function up()
    {
        // return SQL para aplicar
    }

    public function down()
    {
        // return SQL para desfazer
    }
}
PHP;

        $filepath = "$path/migrations/$filename";
        file_put_contents($filepath, $stub);

        echo "✅ Migration criada: migrations/$filename\n";
    }

    private function printHelp(?string $error = null): void
    {
        if ($error) {
            echo "$error\n\n";
        }

        echo "📦 Comandos disponíveis para SimplePHP Migrations:\n\n";
        echo "  simplephp init                         - Prepara o projeto com estrutura padrão\n";
        echo "  simplephp make:migration nome_da_migration  - Cria uma nova migration\n";
        echo "  simplephp migrate:dev                       - Executa migrations no ambiente DEV\n";
        echo "  simplephp migrate:mac                       - Executa migrations no MAC\n";
        echo "  simplephp migrate:hml                       - Executa migrations na HOMOLOGAÇÃO\n";
        echo "  simplephp migrate:prod                      - Executa migrations na PRODUÇÃO\n";
        echo "  simplephp rollback:dev                      - Rollback no ambiente DEV\n";
        echo "  simplephp rollback:prod                     - Rollback no ambiente PROD\n";
        echo "  simplephp validate                          - Valida se as migrations estão corretas\n";
        echo "\n";
    }
}