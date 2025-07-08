<?php

class _0000_00_00_000000_init_project_structure
{
    public function up()
    {
        return <<<SQL
    -- Tabelas

    CREATE TABLE IF NOT EXISTS adm_modulos (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    nome varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
    especifico int(1) DEFAULT '0',
    ordem int(10) DEFAULT '100',
    status int(1) DEFAULT '0',
    label varchar(255) DEFAULT NULL,
    icone varchar(255) DEFAULT '',
    conta_id int(11) DEFAULT '1',
    PRIMARY KEY (id),
    UNIQUE KEY nome (nome)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS codigos_senha (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    token varchar(255) DEFAULT NULL,
    tabela varchar(255) DEFAULT NULL,
    usuario_id int(11) DEFAULT '1',
    time timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    ip varchar(255) DEFAULT NULL,
    conta_id int(11) DEFAULT '1',
    PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS log (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    tabela varchar(150) DEFAULT NULL,
    registro_id int(11) DEFAULT NULL,
    campo varchar(255) DEFAULT NULL,
    anterior longtext,
    novo longtext,
    usuario_id int(11) DEFAULT NULL,
    conta_id int(11) DEFAULT NULL,
    ip varchar(15) DEFAULT NULL,
    time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS log_api (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    url varchar(225) DEFAULT NULL,
    method varchar(255) DEFAULT NULL,
    body longtext,
    headers text,
    encrypt tinyint(1) DEFAULT '0',
    usuario_id int(11) DEFAULT NULL,
    appversion varchar(15) DEFAULT NULL,
    time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip varchar(15) DEFAULT NULL,
    conta_id int(11) DEFAULT NULL,
    PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS log_erro (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    tabela varchar(50) DEFAULT NULL,
    query text,
    conteudo text,
    retorno text,
    backtrace text,
    usuario_id int(11) DEFAULT '1',
    time timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    ip varchar(255) DEFAULT NULL,
    conta_id int(11) DEFAULT '1',
    PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS log_uploads (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    mimetype varchar(100) DEFAULT NULL,
    tamanho int(11) DEFAULT NULL,
    nome varchar(100) DEFAULT NULL,
    caminho varchar(255) DEFAULT NULL,
    backtrace text,
    usuario_id int(11) DEFAULT '1',
    time timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    ip varchar(255) DEFAULT NULL,
    conta_id int(11) DEFAULT '1',
    PRIMARY KEY (id),
    KEY idx_mimetype (mimetype),
    KEY idx_nome (nome)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS notificacoes (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    cliente_id int(11) unsigned NOT NULL,
    titulo varchar(255) DEFAULT NULL,
    mensagem text,
    status varchar(10) NOT NULL DEFAULT 'Pendente',
    ip varchar(255) DEFAULT NULL,
    conta_id int(11) DEFAULT '1',
    usuario_id int(11) DEFAULT '1',
    time timestamp NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY cliente_id (cliente_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS sessoes (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    token varchar(255) DEFAULT NULL,
    status varchar(10) DEFAULT 'Ativo',
    usuario_id int(11) DEFAULT '1',
    time timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    ip varchar(255) DEFAULT NULL,
    conta_id int(11) DEFAULT '1',
    PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    CREATE TABLE IF NOT EXISTS usuarios (
    id int(11) unsigned NOT NULL AUTO_INCREMENT,
    nome varchar(255) DEFAULT NULL,
    email varchar(255) DEFAULT NULL,
    senha varchar(255) DEFAULT NULL,
    tentativas_senha int(1) NOT NULL DEFAULT '0',
    permissoes text,
    status varchar(100) NOT NULL DEFAULT 'Ativo',
    excluido int(1) DEFAULT '0',
    usuario_id int(10) DEFAULT NULL,
    time timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ip varchar(100) DEFAULT NULL,
    conta_id int(10) DEFAULT '0',
    PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- Dados iniciais

    INSERT IGNORE INTO adm_modulos (id, nome, especifico, ordem, status, label, icone, conta_id) VALUES
    (1, 'dashboard', 0, 0, 1, 'Dashboard', '<i class="fas fa-fw fa-tachometer-alt"></i>', 1),
    (2, 'usuarios', 0, 100, 1, 'Usuários do sistema', '<i class="far fa-fw fa-id-card"></i>', 1);

    INSERT IGNORE INTO usuarios (id, nome, email, senha, tentativas_senha, permissoes, status, excluido, usuario_id, time, ip, conta_id) VALUES
    (1, 'Admin Alphacode', 'admin@alphacode.com.br', '$2y$10$7MVw6DCQqDv./tJKJlSoceshXIPIW/Hg1qkS6rKBIAX.DkK/WTfgu', 0, '{\"1\":{\"visualizar\":\"1\"},\"2\":{\"visualizar\":\"1\"}}', 'Ativo', 0, 1, NOW(), NULL, 1);
    SQL;
    }

    public function down()
    {
        return <<<SQL
    DROP TABLE IF EXISTS usuarios;
    DROP TABLE IF EXISTS sessoes;
    DROP TABLE IF EXISTS notificacoes;
    DROP TABLE IF EXISTS log_uploads;
    DROP TABLE IF EXISTS log_erro;
    DROP TABLE IF EXISTS log_api;
    DROP TABLE IF EXISTS log;
    DROP TABLE IF EXISTS codigos_senha;
    DROP TABLE IF EXISTS adm_modulos;
    SQL;
    }
}