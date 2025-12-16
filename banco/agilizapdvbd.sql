-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Tempo de geração: 15/12/2025 às 20:17
-- Versão do servidor: 8.4.7
-- Versão do PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `agilizapdvbd`
--
CREATE DATABASE IF NOT EXISTS `agilizapdvbd` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `agilizapdvbd`;

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
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `caixa_status`
--

INSERT INTO `caixa_status` (`id_status`, `status`, `valor_inicial`, `data_status`) VALUES
(1, 'aberto', 15.00, '2025-11-20 22:45:24'),
(2, 'fechado', 0.00, '2025-11-20 22:49:11'),
(3, 'aberto', 20.00, '2025-11-20 22:50:17'),
(4, 'fechado', 0.00, '2025-11-20 22:51:11'),
(5, 'aberto', 50.00, '2025-11-21 00:51:20'),
(6, 'fechado', 0.00, '2025-11-21 00:53:53'),
(7, 'aberto', 33.00, '2025-11-21 00:54:25'),
(8, 'fechado', 0.00, '2025-11-21 00:55:50'),
(9, 'aberto', 100.00, '2025-11-22 00:57:39'),
(10, 'fechado', 0.00, '2025-11-22 00:59:00'),
(11, 'aberto', 60.00, '2025-11-22 00:59:38'),
(12, 'fechado', 0.00, '2025-11-22 01:00:32'),
(13, 'aberto', 100.00, '2025-11-23 01:01:44'),
(14, 'fechado', 0.00, '2025-11-23 01:03:45'),
(15, 'aberto', 60.00, '2025-11-23 01:04:08'),
(16, 'fechado', 0.00, '2025-11-23 01:04:40'),
(17, 'aberto', 50.00, '2025-11-24 01:06:59'),
(18, 'fechado', 0.00, '2025-11-24 01:08:26'),
(19, 'aberto', 2.50, '2025-11-24 01:10:31'),
(20, 'fechado', 0.00, '2025-11-24 01:11:05'),
(21, 'aberto', 50.00, '2025-12-14 17:02:13'),
(22, 'fechado', 0.00, '2025-12-14 17:03:04'),
(23, 'aberto', 50.00, '2025-12-15 15:53:46');

-- --------------------------------------------------------

--
-- Estrutura para tabela `categoria`
--

DROP TABLE IF EXISTS `categoria`;
CREATE TABLE IF NOT EXISTS `categoria` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nome_categoria` varchar(100) NOT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `nome_categoria`, `ativo`) VALUES
(2, 'Pote 2.5L', 1),
(3, 'Pote 5L', 1),
(4, 'Copinho', 1),
(5, 'Torta', 1),
(6, 'Bebida', 1),
(7, 'Picolé - Slechi', 1),
(8, 'Picolé - Mozafiato', 1),
(9, 'Ituzinho - Slechi', 1),
(10, 'Ituzinho - Mozafiato', 1),
(11, 'Pote - 5L Trufado', 1),
(12, 'Pote 2.5L Trufado', 1),
(13, 'Paleta', 1),
(15, 'Teste', 0);

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
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_estabelecimento`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `estabelecimento`
--

INSERT INTO `estabelecimento` (`id_estabelecimento`, `nome_estabelecimento`, `cep`, `rua`, `bairro`, `cidade`, `estado`, `ativo`) VALUES
(1, 'Atacadão São Roque', '13722-276', 'Rua São José', 'Jardim São Roque', 'São José do Rio Pardo', 'SP', 1),
(2, 'Atacadão Buenos Aires', '13727-096', 'Rua Maria Teresa de Oliveira Rocha', 'Buenos Aires', 'São José do Rio Pardo', 'SP', 1);

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
) ENGINE=MyISAM AUTO_INCREMENT=241 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(98, 'Pote - 5L Trufado', 'Morango Trufado', 'Pote - 5L Trufado', 1, '2025-11-22 15:23:24', 'Saída', 0.00),
(99, 'Bebida', 'Água sem Gás', 'Bebida', 1, '2025-11-23 01:40:48', 'Saída', 0.00),
(100, 'Bebida', 'Água com Gás', 'Bebida', 1, '2025-11-23 01:40:48', 'Saída', 0.00),
(101, 'Torta', 'Pistache', 'Torta', 15, '2025-11-23 14:30:58', 'Entrada', 75.00),
(102, 'Torta', 'Pistache', 'Torta', 5, '2025-11-23 14:41:13', 'Saída', 0.00),
(103, 'Pote 2.5L Trufado', 'Ninho Trufado com Brigadeiro', 'Pote 2.5L Trufado', 1, '2025-11-23 14:41:13', 'Saída', 0.00),
(104, 'Torta', 'Pistache', 'Torta', 2, '2025-11-25 10:32:08', 'Saída', 0.00),
(105, 'Ituzinho - Mozafiato', 'Chocolate Trufado', 'Ituzinho - Mozafiato', 5, '2025-11-26 01:36:56', 'Saída', 0.00),
(106, 'Torta', 'Pistache', 'Torta', 20, '2025-11-26 01:45:45', 'Entrada', 75.00),
(107, 'Pote - 5L', 'Charlote Ninho e Sensação', 'Pote 5L', 1, '2025-11-20 22:46:35', 'Saída', 0.00),
(108, 'Pote - 5L Trufado', 'Ninho Trufado com Brigadeiro', 'Pote - 5L Trufado', 1, '2025-11-20 22:46:35', 'Saída', 0.00),
(109, 'Bebida', 'Água sem Gás', 'Bebida', 1, '2025-11-20 22:46:35', 'Saída', 0.00),
(110, 'Ituzinho - Slechi', 'Abacaxi', 'Ituzinho - Slechi', 1, '2025-11-20 22:47:02', 'Saída', 0.00),
(111, 'Ituzinho - Slechi', 'Uva', 'Ituzinho - Slechi', 1, '2025-11-20 22:47:02', 'Saída', 0.00),
(112, 'Bebida', 'Água com Gás', 'Bebida', 1, '2025-11-20 22:47:02', 'Saída', 0.00),
(113, 'Paleta', 'Pistache', 'Paleta', 1, '2025-11-20 22:48:31', 'Saída', 0.00),
(114, 'Ituzinho - Mozafiato', 'Ninho com Morango', 'Ituzinho - Mozafiato', 1, '2025-11-20 22:48:31', 'Saída', 0.00),
(115, 'Pote 2.5L', 'Charlote Ninho e Sensação', 'Pote 2.5L', 5, '2025-11-20 22:48:41', 'Saída', 0.00),
(116, 'Pote 2.5L', 'Abacaxi Limão Maracuja', 'Pote 2.5L', 1, '2025-11-20 22:48:41', 'Saída', 0.00),
(117, 'Picolé - Slechi', 'Chocolate', 'Picolé - Slechi', 2, '2025-11-20 22:48:52', 'Saída', 0.00),
(118, 'Picolé - Slechi', 'Leite Condensado', 'Picolé - Slechi', 4, '2025-11-20 22:48:52', 'Saída', 0.00),
(119, 'Picolé - Slechi', 'Goiaba', 'Picolé - Slechi', 2, '2025-11-20 22:48:52', 'Saída', 0.00),
(120, 'Copinho', 'Açaí com leite Condensado', 'Copinho', 1, '2025-11-20 22:49:03', 'Saída', 0.00),
(121, 'Copinho', 'Leitinho Trufado', 'Copinho', 1, '2025-11-20 22:49:03', 'Saída', 0.00),
(122, 'Pote - 5L', 'Ovomaltine Morango e Ninho', 'Pote 5L', 1, '2025-11-20 22:50:44', 'Saída', 0.00),
(123, 'Torta', 'Amêndoas', 'Torta', 1, '2025-11-20 22:50:44', 'Saída', 0.00),
(124, 'Ituzinho - Mozafiato', 'Ninho com Morango', 'Ituzinho - Mozafiato', 1, '2025-11-20 22:50:51', 'Saída', 0.00),
(125, 'Ituzinho - Mozafiato', 'Doce de Leite', 'Ituzinho - Mozafiato', 1, '2025-11-20 22:50:51', 'Saída', 0.00),
(126, 'Bebida', 'Água com Gás', 'Bebida', 1, '2025-11-20 22:50:59', 'Saída', 0.00),
(127, 'Bebida', 'Água sem Gás', 'Bebida', 1, '2025-11-20 22:50:59', 'Saída', 0.00),
(128, 'Torta', 'Amêndoas', 'Torta', 1, '2025-11-20 22:51:04', 'Saída', 0.00),
(129, 'Pote 2.5L Trufado', 'Morango Trufado', 'Pote 2.5L Trufado', 1, '2025-11-20 22:51:08', 'Saída', 0.00),
(130, 'Torta', 'Pistache', 'Torta', 1, '2025-11-20 22:56:23', 'Entrada', 75.00),
(131, 'Bebida', 'Água sem Gás', 'Bebida', 1, '2025-11-21 00:51:40', 'Saída', 0.00),
(132, 'Copinho', 'Chocolate', 'Copinho', 1, '2025-11-21 00:51:40', 'Saída', 0.00),
(133, 'Copinho', 'Açaí com leite Condensado', 'Copinho', 1, '2025-11-21 00:51:40', 'Saída', 0.00),
(134, 'Ituzinho - Mozafiato', 'Chocolate Trufado', 'Ituzinho - Mozafiato', 1, '2025-11-21 00:51:56', 'Saída', 0.00),
(135, 'Ituzinho - Mozafiato', 'Ninho com Morango', 'Ituzinho - Mozafiato', 1, '2025-11-21 00:51:56', 'Saída', 0.00),
(136, 'Ituzinho - Mozafiato', 'Doce de Leite', 'Ituzinho - Mozafiato', 1, '2025-11-21 00:51:56', 'Saída', 0.00),
(137, 'Ituzinho - Slechi', 'Limão', 'Ituzinho - Slechi', 1, '2025-11-21 00:51:56', 'Saída', 0.00),
(138, 'Ituzinho - Slechi', 'Abacaxi', 'Ituzinho - Slechi', 1, '2025-11-21 00:51:56', 'Saída', 0.00),
(139, 'Pote 2.5L', 'Charlote Ninho e Sensação', 'Pote 2.5L', 1, '2025-11-21 00:52:10', 'Saída', 0.00),
(140, 'Ituzinho - Mozafiato', 'Ninho com Morango', 'Ituzinho - Mozafiato', 1, '2025-11-21 00:52:10', 'Saída', 0.00),
(141, 'Ituzinho - Mozafiato', 'Doce de Leite', 'Ituzinho - Mozafiato', 1, '2025-11-21 00:52:10', 'Saída', 0.00),
(142, 'Torta', 'Pistache', 'Torta', 1, '2025-11-21 00:52:23', 'Saída', 0.00),
(143, 'Pote - 5L', 'Charlote Ninho e Sensação', 'Pote 5L', 1, '2025-11-21 00:52:23', 'Saída', 0.00),
(144, 'Picolé - Slechi', 'Leite Condensado', 'Picolé - Slechi', 2, '2025-11-21 00:52:23', 'Saída', 0.00),
(145, 'Picolé - Slechi', 'Goiaba', 'Picolé - Slechi', 1, '2025-11-21 00:52:23', 'Saída', 0.00),
(146, 'Picolé - Mozafiato', 'Abacati', 'Picolé - Mozafiato', 1, '2025-11-21 00:52:30', 'Saída', 0.00),
(147, 'Picolé - Mozafiato', 'Coco Branco', 'Picolé - Mozafiato', 1, '2025-11-21 00:52:30', 'Saída', 0.00),
(148, 'Pote - 5L', 'Charlote Ninho e Sensação', 'Pote 5L', 1, '2025-11-21 00:52:30', 'Saída', 0.00),
(149, 'Picolé - Slechi', 'Chocolate', 'Picolé - Slechi', 1, '2025-11-21 00:53:32', 'Saída', 0.00),
(150, 'Picolé - Slechi', 'Leite Condensado', 'Picolé - Slechi', 1, '2025-11-21 00:53:32', 'Saída', 0.00),
(151, 'Picolé - Slechi', 'Goiaba', 'Picolé - Slechi', 1, '2025-11-21 00:53:32', 'Saída', 0.00),
(152, 'Bebida', 'Água sem Gás', 'Bebida', 1, '2025-11-21 00:53:48', 'Saída', 0.00),
(153, 'Ituzinho - Mozafiato', 'Ninho com Morango', 'Ituzinho - Mozafiato', 1, '2025-11-21 00:53:48', 'Saída', 0.00),
(154, 'Picolé - Slechi', 'Goiaba', 'Picolé - Slechi', 1, '2025-11-21 00:53:48', 'Saída', 0.00),
(155, 'Bebida', 'Água sem Gás', 'Bebida', 1, '2025-11-21 00:54:37', 'Saída', 0.00),
(156, 'Copinho', 'Açaí com leite Condensado', 'Copinho', 1, '2025-11-21 00:54:37', 'Saída', 0.00),
(157, 'Ituzinho - Mozafiato', 'Doce de Leite', 'Ituzinho - Mozafiato', 1, '2025-11-21 00:54:37', 'Saída', 0.00),
(158, 'Paleta', 'Paçoca', 'Paleta', 1, '2025-11-21 00:54:46', 'Saída', 0.00),
(159, 'Paleta', 'Pistache', 'Paleta', 1, '2025-11-21 00:54:46', 'Saída', 0.00),
(160, 'Paleta', 'Musse Branco', 'Paleta', 1, '2025-11-21 00:54:46', 'Saída', 0.00),
(161, 'Picolé - Mozafiato', 'Coco Branco', 'Picolé - Mozafiato', 1, '2025-11-21 00:54:56', 'Saída', 0.00),
(162, 'Picolé - Mozafiato', 'Abacati', 'Picolé - Mozafiato', 1, '2025-11-21 00:54:56', 'Saída', 0.00),
(163, 'Pote 2.5L Trufado', 'Morango Trufado', 'Pote 2.5L Trufado', 1, '2025-11-21 00:54:56', 'Saída', 0.00),
(164, 'Bebida', 'Água com Gás', 'Bebida', 1, '2025-11-21 00:54:56', 'Saída', 0.00),
(165, 'Pote 2.5L', 'Charlote Ninho e Sensação', 'Pote 2.5L', 4, '2025-11-21 00:55:03', 'Saída', 0.00),
(166, 'Pote - 5L Trufado', 'Morango Trufado', 'Pote - 5L Trufado', 1, '2025-11-21 00:55:16', 'Saída', 0.00),
(167, 'Pote - 5L Trufado', 'Ninho Trufado com Brigadeiro', 'Pote - 5L Trufado', 1, '2025-11-21 00:55:16', 'Saída', 0.00),
(168, 'Torta', 'Pistache', 'Torta', 1, '2025-11-21 00:55:16', 'Saída', 0.00),
(169, 'Picolé - Mozafiato', 'Abacati', 'Picolé - Mozafiato', 1, '2025-11-21 00:55:16', 'Saída', 0.00),
(170, 'Picolé - Mozafiato', 'Coco Branco', 'Picolé - Mozafiato', 1, '2025-11-21 00:55:16', 'Saída', 0.00),
(171, 'Bebida', 'Água com Gás', 'Bebida', 1, '2025-11-21 00:55:16', 'Saída', 0.00),
(172, 'Bebida', 'Água sem Gás', 'Bebida', 1, '2025-11-21 00:55:16', 'Saída', 0.00),
(173, 'Pote 2.5L', 'Laka Morango Chocolate', 'Pote 2.5L', 1, '2025-11-21 00:55:24', 'Saída', 0.00),
(174, 'Copinho', 'Açaí com leite Condensado', 'Copinho', 1, '2025-11-21 00:55:24', 'Saída', 0.00),
(175, 'Paleta', 'Pistache', 'Paleta', 1, '2025-11-21 00:55:36', 'Saída', 0.00),
(176, 'Copinho', 'Chocolate', 'Copinho', 1, '2025-11-21 00:55:36', 'Saída', 0.00),
(177, 'Bebida', 'Água sem Gás', 'Bebida', 1, '2025-11-21 00:55:36', 'Saída', 0.00),
(178, 'Picolé - Slechi', 'Chocolate', 'Picolé - Slechi', 1, '2025-11-21 00:55:45', 'Saída', 0.00),
(179, 'Picolé - Slechi', 'Leite Condensado', 'Picolé - Slechi', 1, '2025-11-21 00:55:45', 'Saída', 0.00),
(180, 'Pote 2.5L Trufado', 'Morango Trufado', 'Pote 2.5L Trufado', 1, '2025-11-22 00:58:03', 'Saída', 0.00),
(181, 'Copinho', 'Açaí com leite Condensado', 'Copinho', 1, '2025-11-22 00:58:03', 'Saída', 0.00),
(182, 'Torta', 'Amêndoas', 'Torta', 1, '2025-11-22 00:58:03', 'Saída', 0.00),
(183, 'Pote 2.5L', 'Abacaxi Limão Maracuja', 'Pote 2.5L', 1, '2025-11-22 00:58:15', 'Saída', 0.00),
(184, 'Pote 2.5L', 'Charlote Ninho e Sensação', 'Pote 2.5L', 1, '2025-11-22 00:58:15', 'Saída', 0.00),
(185, 'Pote 2.5L', 'Laka Morango Chocolate', 'Pote 2.5L', 3, '2025-11-22 00:58:15', 'Saída', 0.00),
(186, 'Ituzinho - Mozafiato', 'Chocolate Trufado', 'Ituzinho - Mozafiato', 1, '2025-11-22 00:58:23', 'Saída', 0.00),
(187, 'Ituzinho - Mozafiato', 'Ninho com Morango', 'Ituzinho - Mozafiato', 5, '2025-11-22 00:58:23', 'Saída', 0.00),
(188, 'Paleta', 'Pistache', 'Paleta', 1, '2025-11-22 00:58:33', 'Saída', 0.00),
(189, 'Paleta', 'Paçoca', 'Paleta', 1, '2025-11-22 00:58:33', 'Saída', 0.00),
(190, 'Ituzinho - Mozafiato', 'Chocolate Trufado', 'Ituzinho - Mozafiato', 1, '2025-11-22 00:58:33', 'Saída', 0.00),
(191, 'Ituzinho - Mozafiato', 'Ninho com Morango', 'Ituzinho - Mozafiato', 1, '2025-11-22 00:58:33', 'Saída', 0.00),
(192, 'Picolé - Slechi', 'Chocolate', 'Picolé - Slechi', 1, '2025-11-22 00:58:33', 'Saída', 0.00),
(193, 'Picolé - Slechi', 'Leite Condensado', 'Picolé - Slechi', 1, '2025-11-22 00:58:33', 'Saída', 0.00),
(194, 'Pote - 5L Trufado', 'Ninho Trufado', 'Pote - 5L Trufado', 1, '2025-11-22 00:58:41', 'Saída', 0.00),
(195, 'Torta', 'Pistache', 'Torta', 3, '2025-11-22 00:58:48', 'Saída', 0.00),
(196, 'Paleta', 'Pistache', 'Paleta', 1, '2025-11-22 00:58:56', 'Saída', 0.00),
(197, 'Ituzinho - Mozafiato', 'Ninho com Morango', 'Ituzinho - Mozafiato', 2, '2025-11-22 00:58:56', 'Saída', 0.00),
(198, 'Pote 2.5L Trufado', 'Ninho Trufado', 'Pote 2.5L Trufado', 1, '2025-11-22 00:59:45', 'Saída', 0.00),
(199, 'Paleta', 'Pistache', 'Paleta', 1, '2025-11-22 00:59:45', 'Saída', 0.00),
(200, 'Torta', 'Pistache', 'Torta', 1, '2025-11-22 00:59:50', 'Saída', 0.00),
(201, 'Bebida', 'Água sem Gás', 'Bebida', 1, '2025-11-22 00:59:58', 'Saída', 0.00),
(202, 'Bebida', 'Água com Gás', 'Bebida', 1, '2025-11-22 00:59:58', 'Saída', 0.00),
(203, 'Pote - 5L', 'Charlote Ninho e Sensação', 'Pote 5L', 3, '2025-11-22 01:00:08', 'Saída', 0.00),
(204, 'Ituzinho - Mozafiato', 'Ninho com Morango', 'Ituzinho - Mozafiato', 2, '2025-11-22 01:00:08', 'Saída', 0.00),
(205, 'Ituzinho - Mozafiato', 'Chocolate Trufado', 'Ituzinho - Mozafiato', 2, '2025-11-22 01:00:08', 'Saída', 0.00),
(206, 'Pote - 5L', 'Charlote Ninho e Sensação', 'Pote 5L', 1, '2025-11-22 01:00:18', 'Saída', 0.00),
(207, 'Pote 2.5L', 'Abacaxi Limão Maracuja', 'Pote 2.5L', 1, '2025-11-22 01:00:24', 'Saída', 0.00),
(208, 'Copinho', 'Açaí com leite Condensado', 'Copinho', 1, '2025-11-23 01:02:45', 'Saída', 0.00),
(209, 'Copinho', 'Chocolate', 'Copinho', 2, '2025-11-23 01:02:45', 'Saída', 0.00),
(210, 'Ituzinho - Mozafiato', 'Ninho com Morango', 'Ituzinho - Mozafiato', 1, '2025-11-23 01:02:45', 'Saída', 0.00),
(211, 'Bebida', 'Água sem Gás', 'Bebida', 1, '2025-11-23 01:02:45', 'Saída', 0.00),
(212, 'Picolé - Slechi', 'Chocolate', 'Picolé - Slechi', 1, '2025-11-23 01:02:57', 'Saída', 0.00),
(213, 'Pote - 5L', 'Ovomaltine Morango e Ninho', 'Pote 5L', 1, '2025-11-23 01:03:41', 'Saída', 0.00),
(214, 'Pote 2.5L', 'Laka Morango Chocolate', 'Pote 2.5L', 1, '2025-11-23 01:03:41', 'Saída', 0.00),
(215, 'Pote - 5L Trufado', 'Morango Trufado', 'Pote - 5L Trufado', 1, '2025-11-23 01:04:19', 'Saída', 0.00),
(216, 'Pote 2.5L Trufado', 'Morango Trufado', 'Pote 2.5L Trufado', 1, '2025-11-23 01:04:19', 'Saída', 0.00),
(217, 'Ituzinho - Slechi', 'Limão', 'Ituzinho - Slechi', 1, '2025-11-23 01:04:29', 'Saída', 0.00),
(218, 'Ituzinho - Slechi', 'Abacaxi', 'Ituzinho - Slechi', 1, '2025-11-23 01:04:29', 'Saída', 0.00),
(219, 'Ituzinho - Mozafiato', 'Chocolate Trufado', 'Ituzinho - Mozafiato', 5, '2025-11-23 01:04:29', 'Saída', 0.00),
(220, 'Copinho', 'Chocolate', 'Copinho', 2, '2025-11-23 01:04:29', 'Saída', 0.00),
(221, 'Torta', 'Amêndoas', 'Torta', 1, '2025-11-23 01:04:35', 'Saída', 0.00),
(222, 'Picolé - Slechi', 'Chocolate', 'Picolé - Slechi', 9, '2025-11-24 01:07:13', 'Saída', 0.00),
(223, 'Copinho', 'Chocolate', 'Copinho', 1, '2025-11-24 01:07:13', 'Saída', 0.00),
(224, 'Pote 2.5L', 'Abacaxi Limão Maracuja', 'Pote 2.5L', 1, '2025-11-24 01:07:20', 'Saída', 0.00),
(225, 'Pote 2.5L', 'Charlote Ninho e Sensação', 'Pote 2.5L', 1, '2025-11-24 01:07:20', 'Saída', 0.00),
(226, 'Paleta', 'Pistache', 'Paleta', 1, '2025-11-24 01:07:28', 'Saída', 0.00),
(227, 'Paleta', 'Paçoca', 'Paleta', 1, '2025-11-24 01:07:28', 'Saída', 0.00),
(228, 'Picolé - Mozafiato', 'Abacati', 'Picolé - Mozafiato', 1, '2025-11-24 01:07:40', 'Saída', 0.00),
(229, 'Picolé - Slechi', 'Chocolate', 'Picolé - Slechi', 1, '2025-11-24 01:07:40', 'Saída', 0.00),
(230, 'Pote - 5L', 'Charlote Ninho e Sensação', 'Pote 5L', 1, '2025-11-24 01:07:47', 'Saída', 0.00),
(231, 'Copinho', 'Açaí com leite Condensado', 'Copinho', 1, '2025-11-24 01:07:54', 'Saída', 0.00),
(232, 'Copinho', 'Leitinho Trufado', 'Copinho', 1, '2025-11-24 01:07:54', 'Saída', 0.00),
(233, 'Pote 2.5L Trufado', 'Morango Trufado', 'Pote 2.5L Trufado', 1, '2025-11-24 01:08:23', 'Saída', 0.00),
(234, 'Pote 2.5L Trufado', 'Ninho Trufado', 'Pote 2.5L Trufado', 3, '2025-11-24 01:10:41', 'Saída', 0.00),
(235, 'Picolé - Slechi', 'Chocolate', 'Picolé - Slechi', 7, '2025-11-24 01:10:49', 'Saída', 0.00),
(236, 'Pote 2.5L', 'Laka Morango Chocolate', 'Pote 2.5L', 1, '2025-11-24 01:10:55', 'Saída', 0.00),
(237, 'Pote 2.5L', 'Abacaxi Limão Maracuja', 'Pote 2.5L', 1, '2025-11-24 01:10:55', 'Saída', 0.00),
(238, 'Bebida', 'Água sem Gás', 'Bebida', 1, '2025-11-24 01:11:01', 'Saída', 0.00),
(239, 'Pote - 5L Trufado', 'Morango Trufado', 'Pote - 5L Trufado', 1, '2025-12-14 17:02:32', 'Saída', 0.00),
(240, 'Pote - 5L', 'Charlote Ninho e Sensação', 'Pote 5L', 1, '2025-12-15 16:14:14', 'Saída', 0.00);

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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `fechamento_caixa`
--

INSERT INTO `fechamento_caixa` (`id_fechamento`, `data_abertura`, `data_fechamento`, `operador`, `valor_abertura`, `total_dinheiro`, `total_cartaoC`, `total_cartaoD`, `total_pix`, `valor_final_informado`) VALUES
(1, '2025-11-20 22:45:24', '2025-11-20 22:49:11', 'Vendedor', 15.00, 28.00, 17.00, 252.00, 143.00, 0.00),
(2, '2025-11-20 22:50:17', '2025-11-20 22:51:11', 'Vendedor2', 20.00, 12.00, 150.00, 42.00, 215.00, 0.00),
(3, '2025-11-21 00:51:20', '2025-11-21 00:53:53', 'Vendedor', 50.00, 16.00, 363.50, 14.00, 0.00, 0.00),
(4, '2025-11-21 00:54:25', '2025-11-21 00:55:50', 'Vendedor2', 33.00, 97.00, 311.00, 199.00, 50.00, 0.00),
(5, '2025-11-22 00:57:39', '2025-11-22 00:59:00', 'Vendedor', 100.00, 285.00, 489.00, 18.00, 204.00, 0.00),
(6, '2025-11-22 00:59:38', '2025-11-22 01:00:32', 'Vendedor2', 60.00, 71.00, 0.00, 301.00, 150.00, 0.00),
(7, '2025-11-23 01:01:44', '2025-11-23 01:03:45', 'Vendedor', 100.00, 145.00, 0.00, 0.00, 2.50, 0.00),
(8, '2025-11-23 01:04:08', '2025-11-23 01:04:40', 'Vendedor2', 60.00, 0.00, 44.00, 150.00, 117.00, 0.00),
(9, '2025-11-24 01:06:59', '2025-11-24 01:08:26', 'Vendedor', 50.00, 109.00, 99.50, 42.00, 24.00, 0.00),
(10, '2025-11-24 01:10:31', '2025-11-24 01:11:05', 'Vendedor2', 2.50, 20.50, 76.00, 126.00, 0.00, 0.00),
(11, '2025-12-14 17:02:13', '2025-12-14 17:03:04', 'Vendedor', 50.00, 75.00, 0.00, 0.00, 0.00, 0.00);

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
CREATE TABLE IF NOT EXISTS `fornecedor` (
  `id_fornecedor` int NOT NULL AUTO_INCREMENT,
  `nome_fornecedor` varchar(120) NOT NULL,
  `cnpj` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `endereco` varchar(200) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_fornecedor`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `fornecedor`
--

INSERT INTO `fornecedor` (`id_fornecedor`, `nome_fornecedor`, `cnpj`, `telefone`, `email`, `endereco`, `ativo`) VALUES
(1, 'Rogério', '75.793.400/0001-09', '(13) 2483-9745', 'rogerio@hotmail.com', 'Litora', 1),
(2, 'Francisco Parra', '75.616.214/0001-96', '(19) 29017-7648', 'parrafrancisco@gmail.com', 'Sorocaba', 1),
(3, 'Teste', '92.838.343/0001-48', '(13) 33361-7383', 'teste@gmail.com', 'teste', 0);

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto`
--

DROP TABLE IF EXISTS `produto`;
CREATE TABLE IF NOT EXISTS `produto` (
  `id_produto` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `sabor` varchar(50) NOT NULL,
  `id_categoria` int NOT NULL,
  `id_fornecedor` int NOT NULL,
  `preco_venda` decimal(10,2) NOT NULL,
  `preco_compra` decimal(10,2) NOT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_produto`),
  KEY `id_categoria` (`id_categoria`),
  KEY `id_fornecedor` (`id_fornecedor`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `produto`
--

INSERT INTO `produto` (`id_produto`, `nome`, `sabor`, `id_categoria`, `id_fornecedor`, `preco_venda`, `preco_compra`, `ativo`) VALUES
(5, 'Pote 2.5L Trufado', 'Ninho Trufado', 12, 1, 42.00, 20.00, 1),
(6, 'Pote 2.5L', 'Laka Morango Chocolate', 2, 2, 38.00, 18.00, 1),
(7, 'Ituzinho - Slechi', 'Limão', 9, 2, 2.50, 0.75, 1),
(8, 'Ituzinho - Slechi', 'Abacaxi', 9, 2, 2.50, 0.75, 1),
(9, 'Ituzinho - Slechi', 'Uva', 9, 2, 2.50, 0.75, 1),
(10, 'Ituzinho - Mozafiato', 'Chocolate Trufado', 10, 1, 3.00, 1.00, 1),
(11, 'Ituzinho - Mozafiato', 'Ninho com Morango', 10, 1, 3.00, 1.00, 1),
(12, 'Ituzinho - Mozafiato', 'Doce de Leite', 10, 1, 3.00, 1.00, 1),
(13, 'Paleta', 'Musse Branco', 13, 2, 14.00, 6.00, 1),
(14, 'Paleta', 'Pistache', 13, 2, 14.00, 6.00, 1),
(15, 'Paleta', 'Paçoca', 13, 2, 14.00, 6.00, 1),
(16, 'Copinho', 'Chocolate', 4, 1, 12.00, 5.00, 1),
(17, 'Copinho', 'Açaí com leite Condensado', 4, 1, 12.00, 5.00, 1),
(18, 'Copinho', 'Leitinho Trufado', 4, 1, 12.00, 5.00, 1),
(19, 'Bebida', 'Água sem Gás', 6, 2, 3.00, 0.50, 1),
(20, 'Bebida', 'Água com Gás', 6, 1, 3.00, 0.50, 1),
(21, 'Picolé - Mozafiato', 'Abacati', 8, 2, 2.50, 0.50, 1),
(22, 'Picolé - Mozafiato', 'Milho Verde', 8, 2, 2.50, 0.50, 1),
(23, 'Picolé - Mozafiato', 'Coco Branco', 8, 2, 2.50, 0.50, 1),
(24, 'Picolé - Slechi', 'Chocolate', 7, 1, 2.50, 0.50, 1),
(25, 'Picolé - Slechi', 'Leite Condensado', 7, 1, 2.50, 0.50, 1),
(26, 'Picolé - Slechi', 'Goiaba', 7, 1, 2.50, 0.50, 1),
(27, 'Pote - 5L Trufado', 'Ninho Trufado', 11, 2, 75.00, 46.00, 1),
(28, 'Pote - 5L Trufado', 'Morango Trufado', 11, 2, 75.00, 46.00, 1),
(29, 'Pote - 5L Trufado', 'Ninho Trufado com Brigadeiro', 11, 2, 75.00, 46.00, 1),
(30, 'Pote 2.5L', 'Abacaxi Limão Maracuja', 2, 2, 38.00, 18.00, 1),
(31, 'Pote 2.5L', 'Charlote Ninho e Sensação', 2, 2, 38.00, 18.00, 1),
(32, 'Pote 2.5L Trufado', 'Morango Trufado', 12, 1, 42.00, 20.00, 1),
(33, 'Pote 2.5L Trufado', 'Ninho Trufado com Brigadeiro', 12, 2, 42.00, 20.00, 1),
(34, 'Pote - 5L', 'Ninho Brigadeiro e Chocolate', 3, 1, 65.00, 20.00, 1),
(35, 'Pote - 5L', 'Charlote Ninho e Sensação', 3, 1, 65.00, 20.00, 1),
(36, 'Pote - 5L', 'Ovomaltine Morango e Ninho', 3, 1, 65.00, 20.00, 1),
(37, 'Torta', 'Amêndoas', 5, 2, 150.00, 75.00, 1),
(38, 'Torta', 'Pistache', 5, 1, 150.00, 75.00, 1),
(39, 'teste', 'teste', 15, 3, 100.00, 80.00, 0);

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
) ENGINE=MyISAM AUTO_INCREMENT=134 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `saida_produtos`
--

INSERT INTO `saida_produtos` (`id_saida`, `id_produto`, `quantidade`, `data`, `processado`, `venda_id`) VALUES
(1, 35, 1, '2025-11-20', 1, 1),
(2, 29, 1, '2025-11-20', 1, 1),
(3, 19, 1, '2025-11-20', 1, 1),
(4, 8, 1, '2025-11-20', 1, 2),
(5, 9, 1, '2025-11-20', 1, 2),
(6, 20, 1, '2025-11-20', 1, 2),
(7, 14, 1, '2025-11-20', 1, 3),
(8, 11, 1, '2025-11-20', 1, 3),
(9, 31, 5, '2025-11-20', 1, 4),
(10, 30, 1, '2025-11-20', 1, 4),
(11, 24, 2, '2025-11-20', 1, 5),
(12, 25, 4, '2025-11-20', 1, 5),
(13, 26, 2, '2025-11-20', 1, 5),
(14, 17, 1, '2025-11-20', 1, 6),
(15, 18, 1, '2025-11-20', 1, 6),
(16, 36, 1, '2025-11-20', 1, 7),
(17, 37, 1, '2025-11-20', 1, 7),
(18, 11, 1, '2025-11-20', 1, 8),
(19, 12, 1, '2025-11-20', 1, 8),
(20, 20, 1, '2025-11-20', 1, 9),
(21, 19, 1, '2025-11-20', 1, 9),
(22, 37, 1, '2025-11-20', 1, 10),
(23, 32, 1, '2025-11-20', 1, 11),
(24, 19, 1, '2025-11-21', 1, 12),
(25, 16, 1, '2025-11-21', 1, 12),
(26, 17, 1, '2025-11-21', 1, 12),
(27, 10, 1, '2025-11-21', 1, 13),
(28, 11, 1, '2025-11-21', 1, 13),
(29, 12, 1, '2025-11-21', 1, 13),
(30, 7, 1, '2025-11-21', 1, 13),
(31, 8, 1, '2025-11-21', 1, 13),
(32, 31, 1, '2025-11-21', 1, 14),
(33, 11, 1, '2025-11-21', 1, 14),
(34, 12, 1, '2025-11-21', 1, 14),
(35, 38, 1, '2025-11-21', 1, 15),
(36, 35, 1, '2025-11-21', 1, 15),
(37, 25, 2, '2025-11-21', 1, 15),
(38, 26, 1, '2025-11-21', 1, 15),
(39, 21, 1, '2025-11-21', 1, 16),
(40, 23, 1, '2025-11-21', 1, 16),
(41, 35, 1, '2025-11-21', 1, 16),
(42, 24, 1, '2025-11-21', 1, 17),
(43, 25, 1, '2025-11-21', 1, 17),
(44, 26, 1, '2025-11-21', 1, 17),
(45, 19, 1, '2025-11-21', 1, 18),
(46, 11, 1, '2025-11-21', 1, 18),
(47, 26, 1, '2025-11-21', 1, 18),
(48, 19, 1, '2025-11-21', 1, 19),
(49, 17, 1, '2025-11-21', 1, 19),
(50, 12, 1, '2025-11-21', 1, 19),
(51, 15, 1, '2025-11-21', 1, 20),
(52, 14, 1, '2025-11-21', 1, 20),
(53, 13, 1, '2025-11-21', 1, 20),
(54, 23, 1, '2025-11-21', 1, 21),
(55, 21, 1, '2025-11-21', 1, 21),
(56, 32, 1, '2025-11-21', 1, 21),
(57, 20, 1, '2025-11-21', 1, 21),
(58, 31, 4, '2025-11-21', 1, 22),
(59, 28, 1, '2025-11-21', 1, 23),
(60, 29, 1, '2025-11-21', 1, 23),
(61, 38, 1, '2025-11-21', 1, 23),
(62, 21, 1, '2025-11-21', 1, 23),
(63, 23, 1, '2025-11-21', 1, 23),
(64, 20, 1, '2025-11-21', 1, 23),
(65, 19, 1, '2025-11-21', 1, 23),
(66, 6, 1, '2025-11-21', 1, 24),
(67, 17, 1, '2025-11-21', 1, 24),
(68, 14, 1, '2025-11-21', 1, 25),
(69, 16, 1, '2025-11-21', 1, 25),
(70, 19, 1, '2025-11-21', 1, 25),
(71, 24, 1, '2025-11-21', 1, 26),
(72, 25, 1, '2025-11-21', 1, 26),
(73, 32, 1, '2025-11-22', 1, 27),
(74, 17, 1, '2025-11-22', 1, 27),
(75, 37, 1, '2025-11-22', 1, 27),
(76, 30, 1, '2025-11-22', 1, 28),
(77, 31, 1, '2025-11-22', 1, 28),
(78, 6, 3, '2025-11-22', 1, 28),
(79, 10, 1, '2025-11-22', 1, 29),
(80, 11, 5, '2025-11-22', 1, 29),
(81, 14, 1, '2025-11-22', 1, 30),
(82, 15, 1, '2025-11-22', 1, 30),
(83, 10, 1, '2025-11-22', 1, 30),
(84, 11, 1, '2025-11-22', 1, 30),
(85, 24, 1, '2025-11-22', 1, 30),
(86, 25, 1, '2025-11-22', 1, 30),
(87, 27, 1, '2025-11-22', 1, 31),
(88, 38, 3, '2025-11-22', 1, 32),
(89, 14, 1, '2025-11-22', 1, 33),
(90, 11, 2, '2025-11-22', 1, 33),
(91, 5, 1, '2025-11-22', 1, 34),
(92, 14, 1, '2025-11-22', 1, 34),
(93, 38, 1, '2025-11-22', 1, 35),
(94, 19, 1, '2025-11-22', 1, 36),
(95, 20, 1, '2025-11-22', 1, 36),
(96, 35, 3, '2025-11-22', 1, 37),
(97, 11, 2, '2025-11-22', 1, 37),
(98, 10, 2, '2025-11-22', 1, 37),
(99, 35, 1, '2025-11-22', 1, 38),
(100, 30, 1, '2025-11-22', 1, 39),
(101, 17, 1, '2025-11-23', 1, 40),
(102, 16, 2, '2025-11-23', 1, 40),
(103, 11, 1, '2025-11-23', 1, 40),
(104, 19, 1, '2025-11-23', 1, 40),
(105, 24, 1, '2025-11-23', 1, 41),
(106, 36, 1, '2025-11-23', 1, 42),
(107, 6, 1, '2025-11-23', 1, 42),
(108, 28, 1, '2025-11-23', 1, 43),
(109, 32, 1, '2025-11-23', 1, 43),
(110, 7, 1, '2025-11-23', 1, 44),
(111, 8, 1, '2025-11-23', 1, 44),
(112, 10, 5, '2025-11-23', 1, 44),
(113, 16, 2, '2025-11-23', 1, 44),
(114, 37, 1, '2025-11-23', 1, 45),
(115, 24, 9, '2025-11-24', 1, 46),
(116, 16, 1, '2025-11-24', 1, 46),
(117, 30, 1, '2025-11-24', 1, 47),
(118, 31, 1, '2025-11-24', 1, 47),
(119, 14, 1, '2025-11-24', 1, 48),
(120, 15, 1, '2025-11-24', 1, 48),
(121, 21, 1, '2025-11-24', 1, 49),
(122, 24, 1, '2025-11-24', 1, 49),
(123, 35, 1, '2025-11-24', 1, 50),
(124, 17, 1, '2025-11-24', 1, 51),
(125, 18, 1, '2025-11-24', 1, 51),
(126, 32, 1, '2025-11-24', 1, 52),
(127, 5, 3, '2025-11-24', 1, 53),
(128, 24, 7, '2025-11-24', 1, 54),
(129, 6, 1, '2025-11-24', 1, 55),
(130, 30, 1, '2025-11-24', 1, 55),
(131, 19, 1, '2025-11-24', 1, 56),
(132, 28, 1, '2025-12-14', 1, 57),
(133, 35, 1, '2025-12-15', 1, 58);

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
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_usuario`),
  KEY `fk_usuario_estabelecimento` (`id_estabelecimento`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `tipo_usuario`, `nome_usuario`, `senha`, `id_estabelecimento`, `ativo`) VALUES
(1, 1, 'Admin', '$2y$10$DoNr2lUkCJrcGq0AGxPeo.cCQfW/1CYTDfQsCzBoMtWunu.j4soWS', 2, 1),
(2, 0, 'Vendedor', '$2y$10$pY6ohWt210gUuJ2iet/hSOBW8Iv80mpL7vWZnytbDz/jCjqpufYTS', 1, 1),
(6, 0, 'Vendedor2', '$2y$10$x77gvMlzOjlDdmTGExNz3.t7BwRQhfc4aL5Joop.eK5yb8LpjRu0y', 2, 0);

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
) ENGINE=MyISAM AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `vendas`
--

INSERT INTO `vendas` (`id_venda`, `data_hora`, `valor_total`, `id_forma_pagamento`, `status`, `fechamento_caixa_id`, `cliente`) VALUES
(1, '2025-11-20 22:46:35', 143.00, 4, 'finalizada', 1, ''),
(2, '2025-11-20 22:47:02', 8.00, 1, 'finalizada', 1, ''),
(3, '2025-11-20 22:48:31', 17.00, 3, 'finalizada', 1, ''),
(4, '2025-11-20 22:48:41', 228.00, 2, 'finalizada', 1, ''),
(5, '2025-11-20 22:48:52', 20.00, 1, 'finalizada', 1, ''),
(6, '2025-11-20 22:49:03', 24.00, 2, 'finalizada', 1, ''),
(7, '2025-11-20 22:50:44', 215.00, 4, 'finalizada', 2, ''),
(8, '2025-11-20 22:50:51', 6.00, 1, 'finalizada', 2, ''),
(9, '2025-11-20 22:50:59', 6.00, 1, 'finalizada', 2, ''),
(10, '2025-11-20 22:51:04', 150.00, 3, 'finalizada', 2, ''),
(11, '2025-11-20 22:51:08', 42.00, 2, 'finalizada', 2, ''),
(12, '2025-11-21 00:51:40', 27.00, 3, 'finalizada', 3, ''),
(13, '2025-11-21 00:51:56', 14.00, 2, 'finalizada', 3, ''),
(14, '2025-11-21 00:52:10', 44.00, 3, 'finalizada', 3, ''),
(15, '2025-11-21 00:52:23', 222.50, 3, 'finalizada', 3, ''),
(16, '2025-11-21 00:52:30', 70.00, 3, 'finalizada', 3, ''),
(17, '2025-11-21 00:53:32', 7.50, 1, 'finalizada', 3, ''),
(18, '2025-11-21 00:53:48', 8.50, 1, 'finalizada', 3, ''),
(19, '2025-11-21 00:54:37', 18.00, 2, 'finalizada', 4, ''),
(20, '2025-11-21 00:54:46', 42.00, 1, 'finalizada', 4, ''),
(21, '2025-11-21 00:54:56', 50.00, 4, 'finalizada', 4, ''),
(22, '2025-11-21 00:55:03', 152.00, 2, 'finalizada', 4, ''),
(23, '2025-11-21 00:55:16', 311.00, 3, 'finalizada', 4, ''),
(24, '2025-11-21 00:55:24', 50.00, 1, 'finalizada', 4, ''),
(25, '2025-11-21 00:55:36', 29.00, 2, 'finalizada', 4, ''),
(26, '2025-11-21 00:55:45', 5.00, 1, 'finalizada', 4, ''),
(27, '2025-11-22 00:58:03', 204.00, 4, 'finalizada', 5, ''),
(28, '2025-11-22 00:58:15', 190.00, 1, 'finalizada', 5, ''),
(29, '2025-11-22 00:58:23', 18.00, 2, 'finalizada', 5, ''),
(30, '2025-11-22 00:58:33', 39.00, 3, 'finalizada', 5, ''),
(31, '2025-11-22 00:58:41', 75.00, 1, 'finalizada', 5, ''),
(32, '2025-11-22 00:58:48', 450.00, 3, 'finalizada', 5, ''),
(33, '2025-11-22 00:58:56', 20.00, 1, 'finalizada', 5, ''),
(34, '2025-11-22 00:59:45', 56.00, 2, 'finalizada', 6, ''),
(35, '2025-11-22 00:59:50', 150.00, 4, 'finalizada', 6, ''),
(36, '2025-11-22 00:59:58', 6.00, 1, 'finalizada', 6, ''),
(37, '2025-11-22 01:00:08', 207.00, 2, 'finalizada', 6, ''),
(38, '2025-11-22 01:00:18', 65.00, 1, 'finalizada', 6, ''),
(39, '2025-11-22 01:00:24', 38.00, 2, 'finalizada', 6, ''),
(40, '2025-11-23 01:02:45', 42.00, 1, 'finalizada', 7, ''),
(41, '2025-11-23 01:02:57', 2.50, 4, 'finalizada', 7, ''),
(42, '2025-11-23 01:03:41', 103.00, 1, 'finalizada', 7, ''),
(43, '2025-11-23 01:04:19', 117.00, 4, 'finalizada', 8, ''),
(44, '2025-11-23 01:04:29', 44.00, 3, 'finalizada', 8, ''),
(45, '2025-11-23 01:04:35', 150.00, 2, 'finalizada', 8, ''),
(46, '2025-11-24 01:07:13', 34.50, 3, 'finalizada', 9, ''),
(47, '2025-11-24 01:07:20', 76.00, 1, 'finalizada', 9, ''),
(48, '2025-11-24 01:07:28', 28.00, 1, 'finalizada', 9, ''),
(49, '2025-11-24 01:07:40', 5.00, 1, 'finalizada', 9, ''),
(50, '2025-11-24 01:07:47', 65.00, 3, 'finalizada', 9, ''),
(51, '2025-11-24 01:07:54', 24.00, 4, 'finalizada', 9, ''),
(52, '2025-11-24 01:08:23', 42.00, 2, 'finalizada', 9, ''),
(53, '2025-11-24 01:10:41', 126.00, 2, 'finalizada', 10, ''),
(54, '2025-11-24 01:10:49', 17.50, 1, 'finalizada', 10, ''),
(55, '2025-11-24 01:10:55', 76.00, 3, 'finalizada', 10, ''),
(56, '2025-11-24 01:11:01', 3.00, 1, 'finalizada', 10, ''),
(57, '2025-12-14 17:02:32', 75.00, 1, 'finalizada', 11, ''),
(58, '2025-12-15 16:14:14', 65.00, 4, 'finalizada', NULL, ''),
(59, '2025-12-15 16:44:46', 100.00, 4, 'finalizada', NULL, '');

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
