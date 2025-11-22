-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 22/11/2025 às 18:33
-- Versão do servidor: 9.1.0
-- Versão do PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `banco`
--
CREATE DATABASE IF NOT EXISTS `banco` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `banco`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `caixa_status`
--

DROP TABLE IF EXISTS `caixa_status`;
CREATE TABLE IF NOT EXISTS `caixa_status` (
  `id_status` int NOT NULL AUTO_INCREMENT,
  `status` enum('aberto','fechado') NOT NULL DEFAULT 'fechado',
  `valor_inicial` decimal(10,2) DEFAULT '0.00',
  `data_status` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_status`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `caixa_status`
--

INSERT INTO `caixa_status` (`id_status`, `status`, `valor_inicial`, `data_status`) VALUES
(1, 'aberto', 100.00, '2025-11-22 02:07:48'),
(2, 'fechado', 0.00, '2025-11-22 02:21:44'),
(3, 'aberto', 100.00, '2025-11-22 15:21:58'),
(4, 'fechado', 0.00, '2025-11-22 15:23:59');

DROP TABLE IF EXISTS `estabelecimento`;
CREATE TABLE `estabelecimento` (
  `id_estabelecimento` int NOT NULL AUTO_INCREMENT,
  `nome_estabelecimento` varchar(100) NOT NULL,
  `cep` varchar(255) NOT NULL,
  `rua` varchar(255) NOT NULL,
  `bairro` varchar(255) NOT NULL,
  `cidade` varchar(120) NOT NULL,
  `estado` char(2) NOT NULL,
  PRIMARY KEY (`id_estabelecimento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Estrutura para tabela `categoria`
--

DROP TABLE IF EXISTS `categoria`;
CREATE TABLE `categoria` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nome_categoria` varchar(100) NOT NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `nome_categoria`) VALUES
(2, 'Pote 2.5L'),
(3, 'Pote 5L'),
(4, 'Copinho'),
(5, 'Torta'),
(6, 'Bebida'),
(7, 'Picolé - Slechi'),
(8, 'Picolé - Mozafiato'),
(9, 'Ituzinho - Slechi'),
(10, 'Ituzinho - Mozafiato'),
(11, 'Pote - 5L Trufado'),
(12, 'Pote 2.5L Trufado'),
(13, 'Paleta');

-- --------------------------------------------------------

--
-- Estrutura para tabela `estabelecimento`
--

DROP TABLE IF EXISTS `estabelecimento`;
CREATE TABLE IF NOT EXISTS `estabelecimento` (
  `id_estabelecimento` int NOT NULL AUTO_INCREMENT,
  `nome_estabelecimento` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `cep` varchar(255) NOT NULL,
  `rua` varchar(255) NOT NULL,
  `bairro` varchar(255) NOT NULL,
  `cidade` varchar(120) NOT NULL,
  `estado` char(2) NOT NULL,
  PRIMARY KEY (`id_estabelecimento`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `estabelecimento`
--

INSERT INTO `estabelecimento` (`id_estabelecimento`, `nome_estabelecimento`, `cep`, `rua`, `bairro`, `cidade`, `estado`) VALUES
(1, 'Atacadão São Roque', '13722-276', 'Rua São José', 'Jardim São Roque', 'São José do Rio Pardo', 'SP'),
(2, 'Atacadão Buenos Aires', '13727-096', 'Rua Maria Teresa de Oliveira Rocha', 'Buenos Aires', 'São José do Rio Pardo', 'SP');

-- --------------------------------------------------------

--
-- Estrutura para tabela `estoque`
--

DROP TABLE IF EXISTS `estoque`;
CREATE TABLE IF NOT EXISTS `estoque` (
  `id_estoque` int NOT NULL AUTO_INCREMENT,
  `produto` varchar(255) NOT NULL,
  `sabor` varchar(255) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `estoque_atual` int NOT NULL,
  `data` datetime NOT NULL,
  `movimentacao` enum('Entrada','Saída') NOT NULL,
  `valor_custo` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id_estoque`)
) ENGINE=MyISAM AUTO_INCREMENT=99 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `estoque`
--

INSERT INTO `estoque` (`id_estoque`, `produto`, `sabor`, `tipo`, `estoque_atual`, `data`, `movimentacao`, `valor_custo`) VALUES
(1, 'Bebida', 'Água com Gás', 'Bebida', 240, '2025-10-07 00:54:18', 'Entrada', 0.75),
(2, 'Bebida', 'Água sem Gás', 'Bebida', 240, '2025-10-07 00:54:33', 'Entrada', 0.50),
(3, 'Copinho', 'Açaí com leite Condensado', 'Copinho', 240, '2025-10-07 00:54:48', 'Entrada', 5.00),
(4, 'Copinho', 'Chocolate', 'Copinho', 240, '2025-10-07 00:54:56', 'Entrada', 5.00),
(5, 'Copinho', 'Leitinho Trufado', 'Copinho', 240, '2025-10-07 00:55:09', 'Entrada', 5.00),
(6, 'Ituzinho - Mozafiato', 'Chocolate Trufado', 'Ituzinho - Mozafiato', 240, '2025-10-07 00:55:35', 'Entrada', 1.00),
(7, 'Ituzinho - Mozafiato', 'Doce de Leite', 'Ituzinho - Mozafiato', 240, '2025-10-07 00:55:44', 'Entrada', 1.00),
(8, 'Ituzinho - Mozafiato', 'Ninho com Morango', 'Ituzinho - Mozafiato', 240, '2025-10-07 00:55:50', 'Entrada', 1.00),
(9, 'Ituzinho - Slechi', 'Abacaxi', 'Ituzinho - Slechi', 240, '2025-10-07 00:56:00', 'Entrada', 0.75),
(10, 'Ituzinho - Slechi', 'Limão', 'Ituzinho - Slechi', 240, '2025-10-07 00:56:19', 'Entrada', 0.75),
(11, 'Ituzinho - Slechi', 'Uva', 'Ituzinho - Slechi', 240, '2025-10-07 00:56:30', 'Entrada', 0.75),
(12, 'Paleta', 'Musse Branco', 'Paleta', 240, '2025-10-07 00:57:13', 'Entrada', 6.00),
(13, 'Paleta', 'Paçoca', 'Paleta', 240, '2025-10-07 00:57:21', 'Entrada', 6.00),
(14, 'Paleta', 'Pistache', 'Paleta', 240, '2025-10-07 00:57:25', 'Entrada', 6.00),
(15, 'Picolé - Mozafiato', 'Abacati', 'Picolé - Mozafiato', 240, '2025-10-07 00:57:49', 'Entrada', 0.50),
(16, 'Picolé - Mozafiato', 'Coco Branco', 'Picolé - Mozafiato', 240, '2025-10-07 00:58:03', 'Entrada', 0.50),
(17, 'Picolé - Mozafiato', 'Milho Verde', 'Picolé - Mozafiato', 240, '2025-10-07 00:58:14', 'Entrada', 0.50),
(18, 'Picolé - Slechi', 'Chocolate', 'Picolé - Slechi', 240, '2025-10-07 00:58:32', 'Entrada', 0.50),
(19, 'Picolé - Slechi', 'Goiaba', 'Picolé - Slechi', 240, '2025-10-07 00:58:40', 'Entrada', 0.50),
(20, 'Picolé - Slechi', 'Leite Condensado', 'Picolé - Slechi', 240, '2025-10-07 00:58:50', 'Entrada', 0.50),
(21, 'Pote - 5L', 'Charlote Ninho e Sensação', 'Pote 5L', 240, '2025-10-07 00:59:19', 'Entrada', 20.00),
(22, 'Pote - 5L', 'Ninho Brigadeiro e Chocolate', 'Pote 5L', 240, '2025-10-07 00:59:25', 'Entrada', 20.00),
(23, 'Pote - 5L', 'Ovomaltine Morango e Ninho', 'Pote 5L', 240, '2025-10-07 00:59:38', 'Entrada', 20.00),
(24, 'Pote - 5L Trufado', 'Morango Trufado', 'Pote - 5L Trufado', 240, '2025-10-07 00:59:57', 'Entrada', 46.00),
(25, 'Pote - 5L Trufado', 'Ninho Trufado', 'Pote - 5L Trufado', 240, '2025-10-07 01:00:06', 'Entrada', 46.00),
(26, 'Pote - 5L Trufado', 'Ninho Trufado com Brigadeiro', 'Pote - 5L Trufado', 240, '2025-10-07 01:00:14', 'Entrada', 46.00),
(27, 'Pote 2.5L', 'Abacaxi Limão Maracuja', 'Pote 2.5L', 240, '2025-10-07 01:00:38', 'Entrada', 18.00),
(28, 'Pote 2.5L', 'Charlote Ninho e Sensação', 'Pote 2.5L', 240, '2025-10-07 01:00:44', 'Entrada', 18.00),
(29, 'Pote 2.5L', 'Laka Morango Chocolate', 'Pote 2.5L', 240, '2025-10-07 01:00:50', 'Entrada', 18.00),
(30, 'Pote 2.5L Trufado', 'Morango Trufado', 'Pote 2.5L Trufado', 240, '2025-10-07 01:00:59', 'Entrada', 20.00),
(31, 'Pote 2.5L Trufado', 'Ninho Trufado', 'Pote 2.5L Trufado', 240, '2025-10-07 01:01:07', 'Entrada', 20.00),
(32, 'Pote 2.5L Trufado', 'Ninho Trufado com Brigadeiro', 'Pote 2.5L Trufado', 240, '2025-10-07 01:01:24', 'Entrada', 20.00),
(33, 'Torta', 'Amêndoas', 'Torta', 240, '2025-10-07 01:01:33', 'Entrada', 75.00),
(34, 'Torta', 'Pistache', 'Torta', 2, '2025-10-07 01:01:39', 'Entrada', 75.00),
(53, 'Picolé - Slechi', 'Leite Condensado', 'Picolé - Slechi', 3, '2025-11-18 23:33:17', 'Saída', 0.00),
(36, 'Paleta', 'Musse Branco', 'Paleta', 2, '2025-10-10 20:05:45', 'Saída', 0.00),
(37, 'Paleta', 'Pistache', 'Paleta', 2, '2025-10-10 20:05:45', 'Saída', 0.00),
(38, 'Paleta', 'Paçoca', 'Paleta', 3, '2025-10-10 20:05:45', 'Saída', 0.00),
(40, 'Ituzinho - Mozafiato', 'Doce de Leite', 'Ituzinho - Mozafiato', 1, '2025-10-10 20:05:45', 'Saída', 0.00),
(41, 'Ituzinho - Mozafiato', 'Ninho com Morango', 'Ituzinho - Mozafiato', 1, '2025-10-10 20:05:45', 'Saída', 0.00),
(42, 'Pote 2.5L', 'Laka Morango Chocolate', 'Pote 2.5L', 6, '2025-10-10 20:57:17', 'Saída', 0.00),
(43, 'Ituzinho - Mozafiato', 'Ninho com Morango', 'Ituzinho - Mozafiato', 3, '2025-10-10 20:57:17', 'Saída', 0.00),
(44, 'Pote - 5L Trufado', 'Ninho Trufado com Brigadeiro', 'Pote - 5L Trufado', 1, '2025-10-24 16:18:43', 'Saída', 0.00),
(54, 'Bebida', 'Água sem Gás', 'Bebida', 1, '2025-11-19 21:42:33', 'Saída', 0.00),
(47, 'Ituzinho - Mozafiato', 'Chocolate Trufado', 'Ituzinho - Mozafiato', 1, '2025-11-12 22:19:09', 'Saída', 0.00),
(48, 'Ituzinho - Mozafiato', 'Ninho com Morango', 'Ituzinho - Mozafiato', 1, '2025-11-12 22:19:09', 'Saída', 0.00),
(49, 'Ituzinho - Mozafiato', 'Doce de Leite', 'Ituzinho - Mozafiato', 5, '2025-11-12 22:19:09', 'Saída', 0.00),
(56, 'Picolé - Slechi', 'Leite Condensado', 'Picolé - Slechi', 1, '2025-11-19 21:42:33', 'Saída', 0.00),
(55, 'Bebida', 'Água com Gás', 'Bebida', 1, '2025-11-19 21:42:33', 'Saída', 0.00),
(57, 'Pote 2.5L Trufado', 'Ninho Trufado', 'Pote 2.5L Trufado', 2, '2025-11-20 11:00:36', 'Saída', 0.00),
(58, 'Pote - 5L', 'Ninho Brigadeiro e Chocolate', 'Pote 5L', 2, '2025-11-20 11:01:27', 'Saída', 0.00),
(59, 'Ituzinho - Mozafiato', 'Ninho com Morango', 'Ituzinho - Mozafiato', 10, '2025-11-20 11:20:37', 'Saída', 0.00),
(60, 'Ituzinho - Mozafiato', 'Doce de Leite', 'Ituzinho - Mozafiato', 1, '2025-11-20 11:20:37', 'Saída', 0.00),
(61, 'Pote 2.5L', 'Charlote Ninho e Sensação', 'Pote 2.5L', 1, '2025-11-20 11:20:37', 'Saída', 0.00),
(62, 'Pote 2.5L Trufado', 'Morango Trufado', 'Pote 2.5L Trufado', 5, '2025-11-20 11:21:38', 'Saída', 0.00),
(63, 'Picolé - Mozafiato', 'Abacati', 'Picolé - Mozafiato', 5, '2025-11-20 11:21:38', 'Saída', 0.00),
(64, 'Pote 2.5L Trufado', 'Morango Trufado', 'Pote 2.5L Trufado', 1, '2025-11-20 11:41:45', 'Saída', 0.00),
(65, 'Ituzinho - Slechi', 'Limão', 'Ituzinho - Slechi', 1, '2025-11-20 11:42:06', 'Saída', 0.00),
(66, 'Picolé - Slechi', 'Chocolate', 'Picolé - Slechi', 1, '2025-11-20 12:13:22', 'Saída', 0.00),
(67, 'Picolé - Slechi', 'Leite Condensado', 'Picolé - Slechi', 1, '2025-11-20 12:13:22', 'Saída', 0.00),
(68, 'Picolé - Slechi', 'Goiaba', 'Picolé - Slechi', 1, '2025-11-20 12:13:22', 'Saída', 0.00),
(69, 'Pote - 5L Trufado', 'Ninho Trufado', 'Pote - 5L Trufado', 1, '2025-11-20 12:27:37', 'Saída', 0.00),
(70, 'Pote - 5L Trufado', 'Morango Trufado', 'Pote - 5L Trufado', 1, '2025-11-20 12:27:37', 'Saída', 0.00),
(71, 'Pote - 5L Trufado', 'Ninho Trufado com Brigadeiro', 'Pote - 5L Trufado', 1, '2025-11-20 12:27:37', 'Saída', 0.00),
(72, 'Pote - 5L', 'Charlote Ninho e Sensação', 'Pote 5L', 1, '2025-11-20 12:29:41', 'Saída', 0.00),
(73, 'Ituzinho - Slechi', 'Abacaxi', 'Ituzinho - Slechi', 5, '2025-11-20 12:29:41', 'Saída', 0.00),
(74, 'Bebida', 'Água com Gás', 'Bebida', 16, '2025-11-20 14:39:06', 'Entrada', 0.75),
(75, 'Bebida', 'Água sem Gás', 'Bebida', 1, '2025-11-20 14:48:48', 'Entrada', 0.50),
(76, 'Pote 2.5L', 'Abacaxi Limão Maracuja', 'Pote 2.5L', 1, '2025-11-21 01:09:07', 'Saída', 0.00),
(77, 'Pote 2.5L', 'Charlote Ninho e Sensação', 'Pote 2.5L', 1, '2025-11-21 01:09:07', 'Saída', 0.00),
(78, 'Pote 2.5L Trufado', 'Ninho Trufado', 'Pote 2.5L Trufado', 1, '2025-11-21 13:13:36', 'Saída', 0.00),
(79, 'Pote 2.5L Trufado', 'Morango Trufado', 'Pote 2.5L Trufado', 1, '2025-11-21 13:13:36', 'Saída', 0.00),
(80, 'Pote 2.5L Trufado', 'Ninho Trufado com Brigadeiro', 'Pote 2.5L Trufado', 1, '2025-11-21 13:13:36', 'Saída', 0.00),
(81, 'Pote - 5L Trufado', 'Morango Trufado', 'Pote - 5L Trufado', 4, '2025-11-21 21:05:31', 'Saída', 0.00),
(82, 'Copinho', 'Açaí com leite Condensado', 'Copinho', 1, '2025-11-21 21:05:31', 'Saída', 0.00),
(83, 'Ituzinho - Mozafiato', 'Chocolate Trufado', 'Ituzinho - Mozafiato', 5, '2025-11-21 22:14:36', 'Saída', 0.00),
(84, 'Ituzinho - Mozafiato', 'Ninho com Morango', 'Ituzinho - Mozafiato', 8, '2025-11-21 22:14:36', 'Saída', 0.00),
(85, 'Ituzinho - Mozafiato', 'Doce de Leite', 'Ituzinho - Mozafiato', 9, '2025-11-21 22:14:36', 'Saída', 0.00),
(86, 'Pote - 5L', 'Charlote Ninho e Sensação', 'Pote 5L', 1, '2025-11-21 22:14:36', 'Saída', 0.00),
(87, 'Picolé - Mozafiato', 'Abacati', 'Picolé - Mozafiato', 1, '2025-11-22 02:08:25', 'Saída', 0.00),
(88, 'Pote - 5L Trufado', 'Ninho Trufado', 'Pote - 5L Trufado', 1, '2025-11-22 02:08:25', 'Saída', 0.00),
(89, 'Copinho', 'Leitinho Trufado', 'Copinho', 1, '2025-11-22 02:08:25', 'Saída', 0.00),
(90, 'Pote - 5L', 'Ovomaltine Morango e Ninho', 'Pote 5L', 1, '2025-11-22 02:09:57', 'Saída', 0.00),
(91, 'Pote 2.5L Trufado', 'Morango Trufado', 'Pote 2.5L Trufado', 1, '2025-11-22 02:16:57', 'Saída', 0.00),
(92, 'Pote - 5L', 'Ovomaltine Morango e Ninho', 'Pote 5L', 1, '2025-11-22 03:07:16', 'Entrada', 0.00),
(93, 'Picolé - Mozafiato', 'Abacati', 'Picolé - Mozafiato', 1, '2025-11-22 15:22:44', 'Saída', 0.00),
(94, 'Picolé - Mozafiato', 'Coco Branco', 'Picolé - Mozafiato', 1, '2025-11-22 15:22:44', 'Saída', 0.00),
(95, 'Ituzinho - Mozafiato', 'Ninho com Morango', 'Ituzinho - Mozafiato', 1, '2025-11-22 15:22:44', 'Saída', 0.00),
(96, 'Copinho', 'Leitinho Trufado', 'Copinho', 1, '2025-11-22 15:22:44', 'Saída', 0.00),
(97, 'Torta', 'Pistache', 'Torta', 1, '2025-11-22 15:23:23', 'Saída', 0.00),
(98, 'Pote - 5L Trufado', 'Morango Trufado', 'Pote - 5L Trufado', 1, '2025-11-22 15:23:24', 'Saída', 0.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `fechamento_caixa`
--

DROP TABLE IF EXISTS `fechamento_caixa`;
CREATE TABLE IF NOT EXISTS `fechamento_caixa` (
  `id_fechamento` int NOT NULL AUTO_INCREMENT,
  `data_abertura` datetime DEFAULT NULL,
  `data_fechamento` datetime DEFAULT NULL,
  `operador` varchar(50) DEFAULT NULL,
  `valor_abertura` decimal(10,2) DEFAULT '0.00',
  `total_dinheiro` decimal(10,2) DEFAULT '0.00',
  `total_cartaoC` decimal(10,2) DEFAULT '0.00',
  `total_cartaoD` decimal(10,2) DEFAULT '0.00',
  `total_pix` decimal(10,2) DEFAULT '0.00',
  `valor_final_informado` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`id_fechamento`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `fechamento_caixa`
--

INSERT INTO `fechamento_caixa` (`id_fechamento`, `data_abertura`, `data_fechamento`, `operador`, `valor_abertura`, `total_dinheiro`, `total_cartaoC`, `total_cartaoD`, `total_pix`, `valor_final_informado`) VALUES
(1, '2025-11-22 02:07:48', '2025-11-22 02:21:44', 'Vendedor', 100.00, 89.50, 0.00, 42.00, 65.00, 0.00),
(2, '2025-11-22 15:21:58', '2025-11-22 15:23:59', 'Vendedor', 100.00, 20.00, 0.00, 0.00, 225.00, 0.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `forma_pagamento`
--

DROP TABLE IF EXISTS `forma_pagamento`;
CREATE TABLE IF NOT EXISTS `forma_pagamento` (
  `id_forma_pagamento` int NOT NULL AUTO_INCREMENT,
  `nome_pagamento` varchar(50) NOT NULL,
  PRIMARY KEY (`id_forma_pagamento`),
  UNIQUE KEY `nome_pagamento` (`nome_pagamento`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `forma_pagamento`
--

INSERT INTO `forma_pagamento` (`id_forma_pagamento`, `nome_pagamento`) VALUES
(3, 'CRÉDITO'),
(2, 'DÉBITO'),
(1, 'DINHEIRO'),
(4, 'PIX');

-- --------------------------------------------------------

--
-- Estrutura para tabela `fornecedor`
--

DROP TABLE IF EXISTS `fornecedor`;
CREATE TABLE `fornecedor` (
  `id_fornecedor` int NOT NULL AUTO_INCREMENT,
  `nome_fornecedor` varchar(120) NOT NULL,
  `cnpj` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `endereco` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id_fornecedor`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `fornecedor`
--

INSERT INTO `fornecedor` VALUES
(1, 'Rogério', '75.793.400/0001-09', '(13) 2483-9745', 'rogerio@hotmail.com', 'Litora'),
(2, 'Francisco Parra', '75.616.214/0001-96', '(19) 29017-7648', 'parrafrancisco@gmail.com', 'Sorocaba');

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto`
--

DROP TABLE IF EXISTS `produto`;
CREATE TABLE `produto` (
  `id_produto` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `sabor` varchar(50) NOT NULL,
  `id_categoria` int NOT NULL,
  `id_fornecedor` int NOT NULL,
  `preco_venda` decimal(10,2) NOT NULL,
  `preco_compra` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_produto`),
  KEY `id_categoria` (`id_categoria`),
  KEY `id_fornecedor` (`id_fornecedor`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `produto`
--

INSERT INTO `produto` (`id_produto`, `nome`, `sabor`, `id_categoria`, `id_fornecedor`, `preco_venda`, `preco_compra`) VALUES
(5, 'Pote 2.5L Trufado', 'Ninho Trufado', 12, 1, 42.00, 20.00),
(6, 'Pote 2.5L', 'Laka Morango Chocolate', 2, 2, 38.00, 18.00),
(7, 'Ituzinho - Slechi', 'Limão', 9, 2, 2.50, 0.75),
(8, 'Ituzinho - Slechi', 'Abacaxi', 9, 2, 2.50, 0.75),
(9, 'Ituzinho - Slechi', 'Uva', 9, 2, 2.50, 0.75),
(10, 'Ituzinho - Mozafiato', 'Chocolate Trufado', 10, 1, 3.00, 1.00),
(11, 'Ituzinho - Mozafiato', 'Ninho com Morango', 10, 1, 3.00, 1.00),
(12, 'Ituzinho - Mozafiato', 'Doce de Leite', 10, 1, 3.00, 1.00),
(13, 'Paleta', 'Musse Branco', 13, 2, 14.00, 6.00),
(14, 'Paleta', 'Pistache', 13, 2, 14.00, 6.00),
(15, 'Paleta', 'Paçoca', 13, 2, 14.00, 6.00),
(16, 'Copinho', 'Chocolate', 4, 1, 12.00, 5.00),
(17, 'Copinho', 'Açaí com leite Condensado', 4, 1, 12.00, 5.00),
(18, 'Copinho', 'Leitinho Trufado', 4, 1, 12.00, 5.00),
(19, 'Bebida', 'Água sem Gás', 6, 2, 3.00, 0.50),
(20, 'Bebida', 'Água com Gás', 6, 1, 3.00, 0.50),
(21, 'Picolé - Mozafiato', 'Abacati', 8, 2, 2.50, 0.50),
(22, 'Picolé - Mozafiato', 'Milho Verde', 8, 2, 2.50, 0.50),
(23, 'Picolé - Mozafiato', 'Coco Branco', 8, 2, 2.50, 0.50),
(24, 'Picolé - Slechi', 'Chocolate', 7, 1, 2.50, 0.50),
(25, 'Picolé - Slechi', 'Leite Condensado', 7, 1, 2.50, 0.50),
(26, 'Picolé - Slechi', 'Goiaba', 7, 1, 2.50, 0.50),
(27, 'Pote - 5L Trufado', 'Ninho Trufado', 11, 2, 75.00, 46.00),
(28, 'Pote - 5L Trufado', 'Morango Trufado', 11, 2, 75.00, 46.00),
(29, 'Pote - 5L Trufado', 'Ninho Trufado com Brigadeiro', 11, 2, 75.00, 46.00),
(30, 'Pote 2.5L', 'Abacaxi Limão Maracuja', 2, 2, 38.00, 18.00),
(31, 'Pote 2.5L', 'Charlote Ninho e Sensação', 2, 2, 38.00, 18.00),
(32, 'Pote 2.5L Trufado', 'Morango Trufado', 12, 1, 42.00, 20.00),
(33, 'Pote 2.5L Trufado', 'Ninho Trufado com Brigadeiro', 12, 2, 42.00, 20.00),
(34, 'Pote - 5L', 'Ninho Brigadeiro e Chocolate', 3, 1, 65.00, 20.00),
(35, 'Pote - 5L', 'Charlote Ninho e Sensação', 3, 1, 65.00, 20.00),
(36, 'Pote - 5L', 'Ovomaltine Morango e Ninho', 3, 1, 65.00, 20.00),
(37, 'Torta', 'Amêndoas', 5, 2, 150.00, 75.00),
(38, 'Torta', 'Pistache', 5, 1, 150.00, 75.00);

-- --------------------------------------------------------

--
-- Estrutura para tabela `saida_produtos`
--

DROP TABLE IF EXISTS `saida_produtos`;
CREATE TABLE IF NOT EXISTS `saida_produtos` (
  `id_saida` int NOT NULL AUTO_INCREMENT,
  `id_produto` int NOT NULL,
  `quantidade` int DEFAULT NULL,
  `data` date DEFAULT NULL,
  `processado` tinyint DEFAULT '0',
  `venda_id` int NOT NULL,
  PRIMARY KEY (`id_saida`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `saida_produtos`
--

INSERT INTO `saida_produtos` (`id_saida`, `id_produto`, `quantidade`, `data`, `processado`, `venda_id`) VALUES
(1, 21, 1, '2025-11-22', 1, 1),
(2, 27, 1, '2025-11-22', 1, 1),
(3, 18, 1, '2025-11-22', 1, 1),
(4, 36, 1, '2025-11-22', 1, 2),
(5, 32, 1, '2025-11-22', 1, 3),
(6, 21, 1, '2025-11-22', 1, 4),
(7, 23, 1, '2025-11-22', 1, 4),
(8, 11, 1, '2025-11-22', 1, 4),
(9, 18, 1, '2025-11-22', 1, 4),
(10, 38, 1, '2025-11-22', 1, 5),
(11, 28, 1, '2025-11-22', 1, 5);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `tipo_usuario` int NOT NULL,
  `nome_usuario` varchar(100) NOT NULL,
  `senha` varchar(300) NOT NULL,
  `id_estabelecimento` int NOT NULL,
  PRIMARY KEY (`id_usuario`),
  KEY `fk_usuario_estabelecimento` (`id_estabelecimento`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `tipo_usuario`, `nome_usuario`, `senha`, `id_estabelecimento`) VALUES
(1, 1, 'Admin', '$2y$10$DoNr2lUkCJrcGq0AGxPeo.cCQfW/1CYTDfQsCzBoMtWunu.j4soWS', 2),
(2, 0, 'Vendedor', '$2y$10$pY6ohWt210gUuJ2iet/hSOBW8Iv80mpL7vWZnytbDz/jCjqpufYTS', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--

DROP TABLE IF EXISTS `vendas`;
CREATE TABLE IF NOT EXISTS `vendas` (
  `id_venda` int NOT NULL AUTO_INCREMENT,
  `data_hora` datetime NOT NULL,
  `valor_total` decimal(10,2) NOT NULL,
  `id_forma_pagamento` int DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pendente',
  `fechamento_caixa_id` int DEFAULT NULL,
  `cliente` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_venda`),
  KEY `fk_venda_pagamento` (`id_forma_pagamento`),
  KEY `idx_fechamento_caixa` (`fechamento_caixa_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `vendas`
--

INSERT INTO `vendas` (`id_venda`, `data_hora`, `valor_total`, `id_forma_pagamento`, `status`, `fechamento_caixa_id`, `cliente`) VALUES
(1, '2025-11-22 02:08:25', 89.50, 1, 'finalizada', 1, ''),
(2, '2025-11-22 02:09:57', 65.00, 4, 'cancelada', 1, ''),
(3, '2025-11-22 02:16:57', 42.00, 2, 'finalizada', 1, ''),
(4, '2025-11-22 15:22:44', 20.00, 1, 'finalizada', 2, ''),
(5, '2025-11-22 15:23:23', 225.00, 4, 'finalizada', 2, '');

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `produto`
--
ALTER TABLE `produto`
  ADD CONSTRAINT `produto_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`),
  ADD CONSTRAINT `produto_ibfk_2` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedor` (`id_fornecedor`);

--
-- Restrições para tabelas `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_estabelecimento` FOREIGN KEY (`id_estabelecimento`) REFERENCES `estabelecimento` (`id_estabelecimento`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

SET FOREIGN_KEY_CHECKS=1;
