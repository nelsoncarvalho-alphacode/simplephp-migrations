<?php

namespace Alphacode\Migrations;

use Alphacode\Migrations\Validator;

class SimplePHPCommand
{
    public function handle(array $argv): void
    {
        $command = $argv[1] ?? null;
        $projectPath = getcwd();
        $cliPath = __DIR__ . '/../cli';

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
                    echo "❌ Script $scripts[$action] não encontrado.\n";
                    return;
                }
                passthru("php " . escapeshellarg($scripts[$action]));
                return;
            }
        }

        // Validação
        if ($command === 'validate') {
            if ($command === 'validate') {
                Validator::validate(getcwd());
                return;
            }
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

        $migrationDir = "$path/migrations";
        if (!is_dir($migrationDir)) {
            mkdir($migrationDir, 0777, true);
            echo "📁 Pasta migrations criada.\n";
        }

        // Copiar migration inicial
        $stub = __DIR__ . '/../migrations/_0000_00_00_000000_init_project_structure.php';
        $target = "$migrationDir/_0000_00_00_000000_init_project_structure.php";
        if (!file_exists($target)) {
            copy($stub, $target);
            echo "📄 Migration inicial criada: _0000_00_00_000000_init_project_structure.php\n";
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