<?php

namespace Alphacode\Migrations;

class Validator
{
    public static function validate($path)
    {
        $folder = $path . '/migrations';
        $migrations = scandir($folder);

        echo "🔍 Validando migrations em: $folder\n\n";

        foreach ($migrations as $migration) {
            if ($migration === '.' || $migration === '..') continue;

            $classname = pathinfo($migration, PATHINFO_FILENAME);
            require_once "$folder/$migration";

            if (!class_exists($classname)) {
                echo "❌ Classe não encontrada: $classname\n";
                continue;
            }

            $obj = new $classname();

            if (!method_exists($obj, 'up') || !method_exists($obj, 'down')) {
                echo "❌ Métodos up() ou down() ausentes em $classname\n";
                continue;
            }

            $up = $obj->up();
            $down = $obj->down();

            if (!$up || !$down) {
                echo "⚠️ up() ou down() vazios em $classname\n";
                continue;
            }

            echo "✅ $classname OK\n";
        }

        echo "\n🎯 Validação concluída.\n";
    }
}