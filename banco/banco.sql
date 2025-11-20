SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id_usuario` int NOT NULL AUTO_INCREMENT,
  `tipo_usuario` int NOT NULL,
  `nome_usuario` varchar(100) NOT NULL,
  `senha` varchar(300) NOT NULL,
  `id_estabelecimento` int NOT NULL,
  PRIMARY KEY (`id_usuario`),
  KEY `fk_usuario_estabelecimento` (`id_estabelecimento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `usuarios` VALUES 
(4, 0, 'VendedorSR', '$2y$10$9a8gj.IKGi22qwbbfX7LwuysbvyenMQLYnecVMiFWmtQ61fn69pWO', 1),
(5, 1, 'VendedorCS', '$2y$10$CK8662L6q05RX1qP0U/iDuHenVMrMsHS5/tFadeFfMPejkzPJtCYi', 2);

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

INSERT INTO `estabelecimento` VALUES
(1, 'Atacadão São Roque', '13722-276', 'Rua São José', 'Jardim São Roque', 'São José do Rio Pardo', 'SP'),
(2, 'Atacadão Caconde', '13774-026', 'Rua João Galdino Ramos', 'Santo Antônio', 'Caconde', 'SP');

DROP TABLE IF EXISTS `categoria`;
CREATE TABLE `categoria` (
  `id_categoria` int NOT NULL AUTO_INCREMENT,
  `nome_categoria` varchar(100) NOT NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `categoria` VALUES
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
(13, 'Paleta'),
(15, 'Skimo'),
(16, 'Especial de Nutella'),
(17, 'Cobertura'),
(18, 'Zero Açúcar');

DROP TABLE IF EXISTS `fornecedor`;
CREATE TABLE `fornecedor` (
  `id_fornecedor` int NOT NULL AUTO_INCREMENT,
  `nome_fornecedor` varchar(120) NOT NULL,
  `cnpj` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `endereco` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id_fornecedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `fornecedor` VALUES
(1, 'Rogério', '75.793.400/0001-09', '(13) 2483-9745', 'rogerio@hotmail.com', 'Litora'),
(2, 'Francisco Parra', '91.615.255/0001-14', '(15) 2913-0339', 'parrafrancis@gmail.com', 'Sorocaba');

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
  KEY `id_fornecedor` (`id_fornecedor`),
  FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`),
  FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedor` (`id_fornecedor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `produto` VALUES
(5, 'Pote 2.5L Trufado', 'Ninho Trufado', 12, 1, '42.00', '20.00'),
(6, 'Pote 2.5L', 'Laka Morango Chocolate', 2, 2, '38.00', '18.00'),
(7, 'Ituzinho - Slechi', 'Limão', 9, 2, '2.50', '0.75'),
(8, 'Ituzinho - Slechi', 'Abacaxi', 9, 2, '2.50', '0.75'),
(9, 'Ituzinho - Slechi', 'Uva', 9, 2, '2.50', '0.75'),
(10, 'Ituzinho - Mozafiato', 'Chocolate Trufado', 10, 1, '3.00', '1.00'),
(11, 'Ituzinho - Mozafiato', 'Ninho com Morango', 10, 1, '3.00', '1.00'),
(12, 'Ituzinho - Mozafiato', 'Doce de Leite', 10, 1, '3.00', '1.00'),
(13, 'Paleta', 'Musse Branco', 13, 2, '14.00', '6.00'),
(14, 'Paleta', 'Pistache', 13, 2, '14.00', '6.00'),
(15, 'Paleta', 'Paçoca', 13, 2, '14.00', '6.00'),
(16, 'Copinho', 'Chocolate', 4, 1, '12.00', '5.00'),
(17, 'Copinho', 'Açaí com leite Condensado', 4, 1, '12.00', '5.00'),
(18, 'Copinho', 'Leitinho Trufado', 4, 1, '12.00', '5.00'),
(19, 'Bebida', 'Água sem Gás', 6, 2, '3.00', '0.50'),
(20, 'Bebida', 'Água com Gás', 6, 1, '3.00', '0.50');

SET FOREIGN_KEY_CHECKS=1;
