# 🚀 SimplePHP Migrations

Ferramenta de linha de comando para gerenciamento de _migrations_ em projetos que utilizam
o framework **SimplePHP**.
Esse pacote permite criar, aplicar, desfazer e validar migrations em ambientes diferentes, de forma rápida e prática.

## 📦 Instalação

### 🔧 Instalação Global

Execute no terminal:

#### composer global require alphacode/simplephp-migrations

#### Após isso, certifique-se de que o diretório ~/.composer/vendor/bin (ou

#### ~/.config/composer/vendor/bin) esteja no seu PATH.

### 📁 Instalação por projeto

Dentro da raiz do seu projeto, rode:

#### composer require alphacode/simplephp-migrations

## ⚙ Inicialização do Projeto

Para preparar seu projeto para uso do pacote, execute:

#### simplephp init

Esse comando realiza:

- Criação do composer.json (se não existir)
- Instalação do pacote de migrations
- Criação da pasta migrations
- Criação da migration inicial
- Criação da pasta cli/ com os arquivos migrate.php e rollback.php


## 📚 Comandos disponíveis

**- Criar nova migration:**

#### simplephp make:migration NomeDaMigration

**- Executar migrations:**

#### simplephp migrate:dev

#### simplephp migrate:mac

#### simplephp migrate:hml

#### simplephp migrate:prod

**- Rollback da última migration:**

#### simplephp rollback:dev

#### simplephp rollback:prod

**- Validar migrations:**

#### simplephp validate

## 🌍 Ambientes

Você pode definir o ambiente pelo comando (ele seta automaticamente a variável CLI_ENV).

Ambientes disponíveis:

- dev (desenvolvimento)
- mac (ambiente Mac local)
- hml (homologação)
- prod (produção)

## 📁 Estrutura esperada do projeto

#### /back-office

#### ├── cli/

#### │ ├── migrate.php

#### │ └── rollback.php

#### ├── config/

#### │ ├── db.php

#### │ └── environments.php

#### ├── migrations/

#### │ └── _0000_00_00_000000_init_project_structure.php

#### └── composer.json


## 💡 Importante

- O arquivo environments.php deve conter as URLs dos ambientes.
- O db.php deve usar $_SERVER['HTTP_HOST'] para mapear a conexão
    correta.
- O pacote simula o host de execução CLI para que db.php funcione sem mudanças
    no projeto existente.
- O pacote **não interfere na lógica já existente** , apenas integra o mecanismo de
    migrations.

## 👨💻 Exemplos

#### simplephp make:migration criar_tabela_usuarios

#### simplephp migrate:dev

#### simplephp rollback:dev

#### simplephp validate

## 🏢 

📧 E-mail: nelson@alphacode.com.br



