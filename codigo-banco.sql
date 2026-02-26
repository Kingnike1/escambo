-- =====================================================
-- Escambo - Sistema de Troca de Livros
-- Script de criação do banco de dados
-- =====================================================

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema escambo
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `escambo` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `escambo`;

-- -----------------------------------------------------
-- Tabela: usuario
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `usuario` (
  `id_usuario`       INT            NOT NULL AUTO_INCREMENT,
  `usuario_nome`     VARCHAR(255)   NOT NULL,
  `usuario_email`    VARCHAR(255)   NOT NULL,
  `usuario_senha`    VARCHAR(255)   NOT NULL COMMENT 'Hash bcrypt da senha',
  `usuario_endereco` VARCHAR(255)   NOT NULL,
  `criado_em`        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  UNIQUE INDEX `uq_usuario_email` (`usuario_email` ASC)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabela: livro
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `livro` (
  `livro_id`        INT          NOT NULL AUTO_INCREMENT,
  `livro_titulo`    VARCHAR(255) NOT NULL,
  `livro_autor`     VARCHAR(255) NOT NULL,
  `livro_genero`    VARCHAR(100) NOT NULL,
  `livro_descricao` TEXT         NULL,
  `foto1_livro`     VARCHAR(300) NULL,
  `foto2_livro`     VARCHAR(300) NULL,
  `disponivel`      TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '1=disponivel, 0=em troca',
  `criado_em`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `usuario_id`      INT          NOT NULL,
  PRIMARY KEY (`livro_id`),
  INDEX `fk_livro_usuario_idx` (`usuario_id` ASC),
  CONSTRAINT `fk_livro_usuario`
    FOREIGN KEY (`usuario_id`)
    REFERENCES `usuario` (`id_usuario`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabela: troca
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `troca` (
  `troca_id`                INT      NOT NULL AUTO_INCREMENT,
  `troca_data`              DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `troca_status`            ENUM('pendente','aceita','recusada','cancelada') NOT NULL DEFAULT 'pendente',
  `usuario_id_proponente`   INT      NOT NULL,
  `usuario_id_destinatario` INT      NOT NULL,
  `livro_livro_id_desejado` INT      NOT NULL,
  `livro_livro_oferecido`   INT      NOT NULL,
  `mensagem`                TEXT     NULL COMMENT 'Mensagem opcional do proponente',
  PRIMARY KEY (`troca_id`),
  INDEX `fk_troca_proponente_idx`     (`usuario_id_proponente` ASC),
  INDEX `fk_troca_destinatario_idx`   (`usuario_id_destinatario` ASC),
  INDEX `fk_troca_livro_desejado_idx` (`livro_livro_id_desejado` ASC),
  INDEX `fk_troca_livro_oferecido_idx`(`livro_livro_oferecido` ASC),
  CONSTRAINT `fk_troca_proponente`
    FOREIGN KEY (`usuario_id_proponente`)
    REFERENCES `usuario` (`id_usuario`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_troca_destinatario`
    FOREIGN KEY (`usuario_id_destinatario`)
    REFERENCES `usuario` (`id_usuario`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_troca_livro_desejado`
    FOREIGN KEY (`livro_livro_id_desejado`)
    REFERENCES `livro` (`livro_id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_troca_livro_oferecido`
    FOREIGN KEY (`livro_livro_oferecido`)
    REFERENCES `livro` (`livro_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
