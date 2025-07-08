<?php

namespace Alphacode\Migrations;

class SimplePHPCommand
{
    public function handle(array $argv): void
    {
        $command = $argv[1] ?? null;
        $projectPath = getcwd();

        if (!$command) {
            $this->printHelp();
            return;
        }

        // Comando: make:migration NomeDaMigration
        if ($command === 'make:migration') {
            $name = $argv[2] ?? null;
            if (!$name) {
                echo "❌ Nome da migration não informado.\n";
                echo "Exemplo: simplephp make:migration create_users_table\n";
                return;
            }

            $this->createMigration($projectPath, $name);
            return;
        }

        // Comandos com sufixo de ambiente: migrate:dev, rollback:prod, etc.
        if (str_contains($command, ':')) {
            [$action, $env] = explode(':', $command);
            putenv("CLI_ENV=$env");
            $_SERVER['HTTP_HOST'] = $env; // Simula ambiente no terminal

            if ($action === 'migrate') {
                passthru("php {$projectPath}/cli/migrate.php");
                return;
            }

            if ($action === 'rollback') {
                passthru("php {$projectPath}/cli/rollback.php");
                return;
            }
        }

        // Comando de validação
        if ($command === 'validate') {
            Validator::validate($projectPath);
            return;
        }

        // Comando não reconhecido
        $this->printHelp("❌ Comando inválido: $command");
    }

    private function createMigration(string $projectPath, string $name): void
    {
        $slug = strtolower(str_replace(' ', '_', $name));
        $timestamp = date('Y_m_d_His');
        $filename = "_{$timestamp}_{$slug}.php";
        $classname = "_{$timestamp}_{$slug}";
        $filePath = $projectPath . '/migrations/' . $filename;

        if (!is_dir($projectPath . '/migrations')) {
            mkdir($projectPath . '/migrations', 0755, true);
        }

        if (file_exists($filePath)) {
            echo "❌ Arquivo já existe: $filename\n";
            return;
        }

        $template = <<<PHP
<?php

class $classname
{
    public function up()
    {
        // return "CREATE TABLE ...";
    }

    public function down()
    {
        // return "DROP TABLE ...";
    }
}
PHP;

        file_put_contents($filePath, $template);

        echo "✅ Migration criada com sucesso: migrations/$filename\n";
    }

    private function printHelp(?string $error = null): void
    {
        if ($error) {
            echo "$error\n\n";
        }

        echo "📦 Comandos disponíveis para SimplePHP Migrations:\n\n";
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