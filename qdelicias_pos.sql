-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-05-2026 a las 02:49:20
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `qdelicias_pos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`) VALUES
(1, 'Alitas'),
(2, 'Waffles'),
(3, 'Fresas'),
(4, 'Mini waffles'),
(5, 'Malteadas'),
(6, 'Obleas'),
(7, 'Jugos'),
(9, 'Combos'),
(10, 'Gaseosas'),
(11, 'Merengones');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
--

CREATE TABLE `detalle_venta` (
  `id` int(11) NOT NULL,
  `venta_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_venta`
--

INSERT INTO `detalle_venta` (`id`, `venta_id`, `producto_id`, `cantidad`, `precio`) VALUES
(1, 1, 1, 1, 5000.00),
(2, 1, 5, 1, 3000.00),
(3, 1, 4, 1, 15000.00),
(4, 1, 2, 1, 7000.00),
(5, 2, 1, 7, 5000.00),
(6, 2, 2, 1, 7000.00),
(7, 2, 5, 1, 3000.00),
(8, 2, 4, 1, 15000.00),
(9, 3, 2, 1, 7000.00),
(10, 3, 1, 1, 5000.00),
(11, 3, 5, 1, 3000.00),
(12, 4, 9, 1, 4500.00),
(13, 4, 12, 1, 6500.00),
(14, 5, 9, 2, 4500.00),
(15, 5, 6, 1, 11000.00),
(16, 6, 8, 1, 20000.00),
(17, 7, 5, 1, 3000.00),
(18, 7, 2, 1, 7000.00),
(19, 8, 4, 1, 15000.00),
(20, 8, 1, 1, 5000.00),
(21, 9, 8, 1, 20000.00),
(22, 10, 13, 1, 7500.00),
(23, 10, 13, 1, 7500.00),
(24, 11, 17, 1, 58000.00),
(25, 11, 6, 1, 11000.00),
(26, 11, 2, 1, 7000.00),
(27, 12, 1, 1, 5000.00),
(28, 13, 4, 1, 15000.00),
(29, 14, 8, 1, 20000.00),
(30, 15, 8, 1, 20000.00),
(31, 16, 16, 1, 22000.00),
(32, 16, 6, 1, 11000.00),
(33, 16, 13, 1, 7500.00),
(34, 17, 16, 1, 22000.00),
(35, 18, 13, 1, 7500.00),
(36, 18, 13, 1, 7500.00),
(37, 19, 17, 1, 58000.00),
(38, 20, 15, 1, 10000.00),
(39, 21, 13, 2, 7500.00),
(40, 21, 15, 1, 10000.00),
(41, 22, 16, 1, 22000.00),
(42, 23, 17, 1, 58000.00),
(43, 24, 8, 1, 20000.00),
(44, 25, 13, 4, 7500.00),
(45, 25, 4, 2, 15000.00),
(46, 26, 8, 1, 20000.00),
(47, 26, 5, 1, 3000.00),
(48, 26, 14, 1, 7500.00),
(49, 27, 15, 1, 10000.00),
(50, 27, 8, 1, 20000.00),
(51, 28, 13, 1, 7500.00),
(52, 28, 13, 1, 7500.00),
(53, 29, 13, 1, 7500.00),
(54, 30, 13, 1, 7500.00),
(55, 30, 13, 1, 7500.00),
(56, 31, 17, 1, 58000.00),
(57, 31, 16, 1, 22000.00),
(58, 31, 15, 1, 10000.00),
(59, 32, 15, 1, 10000.00),
(60, 33, 15, 1, 10000.00),
(61, 34, 17, 1, 58000.00),
(62, 34, 13, 1, 7500.00),
(63, 35, 13, 1, 7500.00),
(64, 35, 17, 1, 58000.00),
(65, 36, 20, 1, 4000.00),
(66, 37, 21, 1, 6000.00),
(67, 38, 5, 1, 3000.00),
(68, 39, 17, 1, 58000.00),
(69, 40, 1, 1, 5000.00),
(70, 41, 2, 1, 7000.00),
(71, 42, 4, 1, 15000.00),
(72, 43, 17, 1, 58000.00),
(73, 43, 13, 1, 7500.00),
(74, 44, 21, 1, 6000.00),
(75, 45, 13, 1, 7500.00),
(76, 46, 16, 1, 22000.00),
(77, 47, 15, 1, 10000.00),
(78, 47, 20, 1, 4000.00),
(79, 48, 2, 1, 7000.00),
(80, 49, 15, 1, 10000.00),
(81, 49, 21, 1, 6000.00),
(82, 50, 18, 1, 10000.00),
(83, 51, 15, 1, 10000.00),
(84, 52, 15, 1, 10000.00),
(85, 52, 13, 1, 7500.00),
(86, 53, 17, 1, 58000.00),
(87, 54, 15, 1, 10000.00),
(88, 55, 15, 1, 10000.00),
(89, 56, 1, 1, 5000.00),
(90, 56, 15, 1, 10000.00),
(91, 57, 1, 1, 5000.00),
(92, 57, 20, 1, 4000.00),
(93, 58, 19, 1, 10000.00),
(94, 59, 8, 1, 20000.00),
(95, 60, 19, 1, 10000.00),
(96, 61, 19, 1, 10000.00),
(97, 62, 8, 1, 20000.00),
(98, 63, 19, 1, 10000.00),
(99, 64, 19, 1, 10000.00),
(100, 65, 8, 1, 20000.00),
(101, 66, 15, 1, 10000.00),
(102, 66, 2, 1, 7000.00),
(103, 66, 6, 1, 11000.00),
(104, 66, 20, 1, 4000.00),
(105, 66, 14, 1, 7500.00),
(106, 66, 3, 1, 12000.00),
(107, 67, 6, 1, 11000.00),
(108, 67, 13, 1, 7500.00),
(109, 68, 17, 1, 58000.00),
(110, 69, 28, 1, 40000.00),
(111, 70, 25, 9, 10000.00),
(112, 71, 23, 4, 7000.00),
(113, 72, 8, 4, 20000.00),
(114, 73, 27, 4, 7000.00),
(115, 74, 15, 1, 10000.00),
(116, 74, 23, 1, 7000.00),
(117, 74, 26, 1, 15000.00),
(118, 75, 5, 1, 3000.00),
(119, 76, 15, 1, 10000.00),
(120, 76, 2, 1, 7000.00),
(121, 76, 6, 1, 11000.00),
(122, 76, 13, 1, 7500.00),
(123, 77, 2, 1, 7000.00),
(124, 78, 24, 1, 5000.00),
(125, 78, 27, 1, 7000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta_extras`
--

CREATE TABLE `detalle_venta_extras` (
  `id` int(11) NOT NULL,
  `detalle_venta_id` int(11) NOT NULL,
  `extra_id` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_venta_extras`
--

INSERT INTO `detalle_venta_extras` (`id`, `detalle_venta_id`, `extra_id`, `precio`) VALUES
(1, 22, 8, 0.00),
(2, 22, 6, 0.00),
(3, 22, 9, 0.00),
(4, 22, 2, 0.00),
(5, 22, 3, 3000.00),
(6, 23, 8, 0.00),
(7, 23, 6, 0.00),
(8, 23, 9, 0.00),
(9, 23, 3, 0.00),
(10, 23, 3, 3000.00),
(11, 27, 7, 0.00),
(12, 27, 7, 500.00),
(13, 28, 8, 0.00),
(14, 28, 6, 0.00),
(15, 28, 9, 0.00),
(16, 28, 2, 0.00),
(17, 28, 3, 3000.00),
(18, 28, 10, 500.00),
(19, 30, 6, 1500.00),
(20, 30, 5, 2500.00),
(21, 33, 9, 0.00),
(22, 33, 3, 0.00),
(23, 33, 3, 3000.00),
(24, 33, 5, 0.00),
(25, 33, 7, 0.00),
(26, 35, 6, 0.00),
(27, 35, 9, 0.00),
(28, 35, 2, 0.00),
(29, 35, 10, 0.00),
(30, 36, 8, 0.00),
(31, 36, 6, 0.00),
(32, 36, 9, 0.00),
(33, 36, 2, 0.00),
(34, 36, 3, 3000.00),
(35, 39, 6, 0.00),
(36, 39, 9, 0.00),
(37, 39, 1, 0.00),
(38, 39, 10, 0.00),
(39, 44, 6, 0.00),
(40, 44, 9, 0.00),
(41, 44, 1, 0.00),
(42, 44, 10, 0.00),
(43, 51, 13, 0.00),
(44, 51, 11, 2000.00),
(45, 51, 9, 0.00),
(46, 51, 3, 0.00),
(47, 51, 5, 0.00),
(48, 53, 13, 0.00),
(49, 53, 9, 0.00),
(50, 53, 3, 0.00),
(51, 53, 1, 3000.00),
(52, 53, 5, 0.00),
(53, 54, 13, 0.00),
(54, 54, 9, 0.00),
(55, 54, 3, 0.00),
(56, 54, 5, 0.00),
(57, 55, 13, 0.00),
(58, 55, 11, 2000.00),
(59, 55, 9, 0.00),
(60, 55, 6, 0.00),
(61, 55, 2, 0.00),
(62, 55, 3, 3000.00),
(63, 62, 13, 0.00),
(64, 62, 9, 0.00),
(65, 62, 6, 0.00),
(66, 62, 2, 0.00),
(67, 63, 13, 0.00),
(68, 63, 9, 0.00),
(69, 63, 6, 0.00),
(70, 63, 3, 0.00),
(71, 63, 3, 3000.00),
(72, 65, 13, 0.00),
(73, 65, 9, 0.00),
(74, 66, 13, 0.00),
(75, 66, 9, 0.00),
(76, 66, 5, 0.00),
(77, 66, 12, 1500.00),
(78, 73, 13, 0.00),
(79, 73, 9, 0.00),
(80, 73, 6, 0.00),
(81, 73, 3, 0.00),
(82, 75, 13, 0.00),
(83, 75, 9, 0.00),
(84, 75, 6, 0.00),
(85, 75, 2, 0.00),
(86, 81, 13, 0.00),
(87, 81, 9, 0.00),
(88, 81, 6, 0.00),
(89, 81, 2, 3000.00),
(90, 85, 13, 0.00),
(91, 85, 6, 0.00),
(92, 85, 3, 0.00),
(93, 92, 13, 0.00),
(94, 92, 11, 2000.00),
(95, 92, 9, 0.00),
(96, 92, 6, 1500.00),
(97, 92, 3, 3000.00),
(98, 108, 13, 0.00),
(99, 108, 9, 0.00),
(100, 108, 6, 0.00),
(101, 108, 3, 0.00),
(102, 118, 13, 0.00),
(103, 120, 13, 500.00),
(104, 120, 5, 1500.00),
(105, 122, 13, 0.00),
(106, 122, 9, 0.00),
(107, 122, 6, 0.00),
(108, 122, 2, 0.00),
(109, 123, 13, 0.00),
(110, 123, 6, 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta_sabores`
--

CREATE TABLE `detalle_venta_sabores` (
  `id` int(11) NOT NULL,
  `detalle_venta_id` int(11) NOT NULL,
  `sabor_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_venta_sabores`
--

INSERT INTO `detalle_venta_sabores` (`id`, `detalle_venta_id`, `sabor_id`, `cantidad`) VALUES
(1, 61, 1, 10),
(3, 61, 2, 10),
(4, 64, 1, 15),
(5, 64, 2, 15),
(6, 68, 1, 30),
(7, 72, 1, 15),
(8, 72, 2, 15),
(9, 76, 1, 10),
(10, 77, 1, 4),
(11, 80, 1, 2),
(12, 80, 2, 2),
(13, 83, 1, 2),
(14, 83, 2, 2),
(15, 84, 1, 4),
(16, 86, 1, 30),
(17, 87, 1, 2),
(18, 87, 2, 2),
(19, 88, 1, 2),
(20, 88, 2, 2),
(21, 90, 1, 4),
(22, 101, 1, 4),
(23, 109, 1, 20),
(24, 109, 2, 10),
(25, 110, 2, 20),
(26, 114, 8, 1),
(27, 115, 1, 4),
(28, 119, 1, 2),
(29, 119, 2, 2),
(30, 124, 12, 1),
(31, 125, 10, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `extras`
--

CREATE TABLE `extras` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `tipo` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `extras`
--

INSERT INTO `extras` (`id`, `nombre`, `precio`, `tipo`) VALUES
(1, 'Helado de Vainilla ', 3000.00, 'helado'),
(2, 'Helado de Fresa', 3000.00, 'helado'),
(3, 'Helado de Oreo', 3000.00, 'helado'),
(5, 'Mani', 1500.00, 'topping clasico'),
(6, 'Gusanitos de goma', 1500.00, 'topping clasico'),
(7, 'Durazno', 500.00, 'salsa'),
(8, 'chocolate', 500.00, 'salsa'),
(9, 'Fruta del dia', 1000.00, 'Fruta del dia'),
(10, 'Salsa Mora', 500.00, 'salsa'),
(11, 'Boli Pop Morada', 2000.00, 'topping premium'),
(12, 'Trululus', 1500.00, 'topping clasico'),
(13, 'Arequipe', 500.00, 'salsa'),
(14, 'Boli Pop Rojo', 2000.00, 'topping premium'),
(15, 'Boli Pop de Calabaza ', 2000.00, 'topping premium');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `tipo_configuracion` varchar(50) DEFAULT 'extras'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `precio`, `imagen`, `categoria_id`, `tipo_configuracion`) VALUES
(1, 'Fresa Mini', 5000.00, 'assets/img/productos/producto_69eca58f8536c.jpg', 3, 'extras'),
(2, 'Fresa Clásica', 7000.00, 'assets/img/productos/producto_69eca5611f9ad.jpg', 3, 'extras'),
(3, 'Waffle Sencillo', 12000.00, 'assets/img/productos/producto_69eca3f708e44.jpg', 2, 'extras'),
(4, 'Waffle Especial', 15000.00, 'assets/img/productos/producto_69eca3ed531be.png', 2, 'extras'),
(5, 'Oblea Clásica', 3000.00, 'assets/img/productos/producto_69eca60adfe22.jpg', 6, 'extras'),
(6, 'Malteada Oreo', 11000.00, 'assets/img/productos/producto_69eca3e2664d4.jpg', 5, 'simple'),
(8, 'Fresa Premium', 20000.00, 'assets/img/productos/producto_69eca357505b3.jpg', 3, 'extras'),
(9, 'Oblea Mix', 4500.00, 'assets/img/productos/producto_69eca5fad81e8.jpg', 6, 'extras'),
(12, 'Oblea Plus', 6500.00, 'assets/img/productos/producto_69eca5f317a73.jpg', 6, 'extras'),
(13, 'Mini Waffle Plus', 7500.00, 'assets/img/productos/producto_69eca33fa6cec.jpg', 4, 'extras'),
(14, 'Oblea Premium', 7500.00, 'assets/img/productos/producto_69eca56a877c5.jpg', 6, 'extras'),
(15, 'Alitas Personales', 10000.00, 'assets/img/productos/producto_69ec99f7cd5e5.webp', 1, 'sabores'),
(16, 'Alas Pareja ', 22000.00, 'assets/img/productos/producto_69eca1806f91e.jpg', 1, 'sabores'),
(17, 'Alas Familiar', 58000.00, 'assets/img/productos/producto_69eca16c9aff4.jpg', 1, 'sabores'),
(18, 'Malteada Fresa', 10000.00, 'assets/img/productos/producto_69eca32b851e7.jpg', 5, 'simple'),
(19, 'Malteada Vainilla', 10000.00, 'assets/img/productos/producto_69eca29fc86bf.jpg', 5, 'simple'),
(20, 'Mini Waffle Clasico', 4000.00, 'assets/img/productos/producto_69ec9d7715813.jpg', 4, 'extras'),
(21, 'Mini Waffle Mix', 6000.00, 'assets/img/productos/producto_69ec9d2f5f9cd.jpeg', 4, 'extras'),
(23, 'Limonada de Coco', 7000.00, 'assets/img/productos/producto_69ec9ace29ce0.jpg', 7, 'simple'),
(24, 'Jugos en agua', 5000.00, 'assets/img/productos/producto_69efff1e3457c.jpg', 7, 'sabores'),
(25, 'Fresas Mix', 10000.00, 'assets/img/productos/producto_69f00c1e9e8fa.jpg', 3, 'extras'),
(26, 'Fresas Plus', 15000.00, 'assets/img/productos/producto_69f00c7c705b9.jpg', 3, 'extras'),
(27, 'Jugos en leche', 7000.00, 'assets/img/productos/producto_69f00d2551aad.jpg', 7, 'sabores'),
(28, 'Alas x 20', 40000.00, 'assets/img/productos/producto_69f2968841208.jpg', 1, 'sabores');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_reglas_extras`
--

CREATE TABLE `producto_reglas_extras` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `tipo_extra` varchar(50) NOT NULL,
  `cantidad_incluida` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto_reglas_extras`
--

INSERT INTO `producto_reglas_extras` (`id`, `producto_id`, `tipo_extra`, `cantidad_incluida`) VALUES
(1, 13, 'helado', 1),
(2, 13, 'salsa', 1),
(3, 13, 'topping clasico', 1),
(4, 13, 'Fruta del dia', 1),
(5, 1, 'salsa', 1),
(6, 4, 'helado', 1),
(7, 4, 'salsa', 1),
(8, 4, 'topping clasico', 1),
(9, 4, 'Fruta del dia', 1),
(10, 20, 'salsa', 1),
(11, 20, 'Fruta del dia', 1),
(12, 21, 'Fruta del dia', 1),
(13, 21, 'salsa', 1),
(14, 21, 'topping clasico', 1),
(15, 5, 'salsa', 2),
(16, 2, 'topping clasico', 1),
(17, 2, 'salsa', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_sabores`
--

CREATE TABLE `producto_sabores` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `sabor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto_sabores`
--

INSERT INTO `producto_sabores` (`id`, `producto_id`, `sabor_id`) VALUES
(6, 15, 1),
(7, 16, 1),
(8, 17, 1),
(9, 15, 2),
(10, 16, 2),
(11, 17, 2),
(37, 27, 8),
(38, 27, 10),
(39, 27, 9),
(40, 27, 6),
(41, 27, 7),
(44, 28, 1),
(45, 28, 2),
(46, 24, 8),
(47, 24, 10),
(48, 24, 9),
(49, 24, 12),
(50, 24, 6),
(51, 24, 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sabores`
--

CREATE TABLE `sabores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `tipo` varchar(50) NOT NULL DEFAULT 'general'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sabores`
--

INSERT INTO `sabores` (`id`, `nombre`, `activo`, `tipo`) VALUES
(1, 'BBQ', 1, 'salsa_alitas'),
(2, 'Miel mostaza', 1, 'salsa_alitas'),
(6, 'Lulo', 1, 'fruta_jugo'),
(7, 'Mora', 1, 'fruta_jugo'),
(8, 'Fresa', 1, 'fruta_jugo'),
(9, 'Frutos Rojos', 1, 'fruta_jugo'),
(10, 'Frutos Amarillos', 1, 'fruta_jugo'),
(12, 'Guanábana ', 1, 'fruta_jugo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_extra`
--

CREATE TABLE `tipos_extra` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_extra`
--

INSERT INTO `tipos_extra` (`id`, `nombre`) VALUES
(5, 'Fruta del dia'),
(3, 'helado'),
(1, 'salsa'),
(2, 'topping clasico'),
(4, 'topping premium');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) DEFAULT NULL,
  `contraseña` varchar(100) DEFAULT NULL,
  `rol` varchar(20) DEFAULT NULL,
  `nombre` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `contraseña`, `rol`, `nombre`) VALUES
(1, 'admin', '1234', 'admin', 'Carolina'),
(2, 'vendedor1', '1234', 'vendedor', 'Laura');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) DEFAULT NULL,
  `metodo_pago` varchar(50) NOT NULL DEFAULT 'efectivo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `fecha`, `total`, `metodo_pago`) VALUES
(1, '2026-04-16 16:15:26', 30000.00, 'efectivo'),
(2, '2026-04-16 16:24:48', 60000.00, 'efectivo'),
(3, '2026-04-16 16:33:03', 15000.00, 'efectivo'),
(4, '2026-04-16 16:51:56', 11000.00, 'efectivo'),
(5, '2026-04-16 17:47:09', 20000.00, 'efectivo'),
(6, '2026-04-17 13:02:04', 20000.00, 'efectivo'),
(7, '2026-04-17 13:02:35', 10000.00, 'efectivo'),
(8, '2026-04-17 13:02:43', 20000.00, 'efectivo'),
(9, '2026-04-17 13:03:03', 20000.00, 'efectivo'),
(10, '2026-04-17 13:22:28', 21000.00, 'efectivo'),
(11, '2026-04-17 14:29:34', 76000.00, 'efectivo'),
(12, '2026-04-17 14:32:07', 5500.00, 'efectivo'),
(13, '2026-04-17 16:03:46', 18500.00, 'efectivo'),
(14, '2026-04-17 16:15:06', 20000.00, 'efectivo'),
(15, '2026-04-17 16:29:20', 24000.00, 'efectivo'),
(16, '2026-04-17 19:39:34', 43500.00, 'efectivo'),
(17, '2026-04-20 16:02:06', 22000.00, 'efectivo'),
(18, '2026-04-20 16:32:43', 18000.00, 'efectivo'),
(19, '2026-04-20 17:23:51', 58000.00, 'efectivo'),
(20, '2026-04-20 18:28:19', 10000.00, 'efectivo'),
(21, '2026-04-22 15:28:11', 25000.00, 'mixto'),
(22, '2026-04-22 15:28:56', 22000.00, 'nequi'),
(23, '2026-04-22 15:29:02', 58000.00, 'daviplata'),
(24, '2026-04-22 15:29:07', 20000.00, 'transferencia'),
(25, '2026-04-22 16:11:19', 60000.00, 'mixto'),
(26, '2026-04-22 16:49:34', 30500.00, 'mixto'),
(27, '2026-04-22 16:50:43', 30000.00, 'mixto'),
(28, '2026-04-22 19:37:19', 17000.00, 'mixto'),
(29, '2026-04-22 20:57:34', 10500.00, 'mixto'),
(30, '2026-04-22 21:13:16', 20000.00, 'mixto'),
(31, '2026-04-23 15:09:05', 90000.00, 'efectivo'),
(32, '2026-04-23 15:56:16', 10000.00, 'nequi'),
(33, '2026-04-23 16:35:30', 10000.00, 'mixto'),
(34, '2026-04-23 16:42:59', 65500.00, 'efectivo'),
(35, '2026-04-23 22:20:16', 68500.00, 'mixto'),
(36, '2026-04-23 22:21:05', 4000.00, 'efectivo'),
(37, '2026-04-23 22:29:30', 7500.00, 'daviplata'),
(38, '2026-04-24 18:06:55', 3000.00, 'nequi'),
(39, '2026-04-24 18:11:24', 58000.00, 'efectivo'),
(40, '2026-04-24 18:19:24', 5000.00, 'nequi'),
(41, '2026-04-24 18:21:12', 7000.00, 'nequi'),
(42, '2026-04-24 18:32:07', 15000.00, 'transferencia'),
(43, '2026-04-24 19:31:24', 65500.00, 'mixto'),
(44, '2026-04-24 19:31:37', 6000.00, 'nequi'),
(45, '2026-04-24 19:44:32', 7500.00, 'nequi'),
(46, '2026-04-24 19:46:03', 22000.00, 'mixto'),
(47, '2026-04-24 20:24:20', 14000.00, 'efectivo'),
(48, '2026-04-24 20:53:33', 7000.00, 'nequi'),
(49, '2026-04-24 20:54:13', 19000.00, 'daviplata'),
(50, '2026-04-24 20:54:57', 10000.00, 'mixto'),
(51, '2026-04-24 21:17:27', 10000.00, 'mixto'),
(52, '2026-04-24 21:18:51', 17500.00, 'daviplata'),
(53, '2026-04-24 23:55:27', 58000.00, 'efectivo'),
(54, '2026-04-25 00:06:12', 10000.00, 'efectivo'),
(55, '2026-04-25 00:24:23', 10000.00, 'efectivo'),
(56, '2026-04-25 00:26:50', 15000.00, 'efectivo'),
(57, '2026-04-25 01:48:38', 15500.00, 'nequi'),
(58, '2026-04-25 04:25:09', 10000.00, 'mixto'),
(59, '2026-04-25 04:34:54', 20000.00, 'efectivo'),
(60, '2026-04-25 04:35:09', 10000.00, 'mixto'),
(61, '2026-04-25 04:36:09', 10000.00, 'mixto'),
(62, '2026-04-25 04:41:55', 20000.00, 'mixto'),
(63, '2026-04-25 04:43:08', 10000.00, 'efectivo'),
(64, '2026-04-25 04:43:47', 10000.00, 'mixto'),
(65, '2026-04-25 04:45:26', 20000.00, 'mixto'),
(66, '2026-04-25 05:17:43', 51500.00, 'nequi'),
(67, '2026-04-27 20:19:32', 18500.00, 'efectivo'),
(68, '2026-04-27 20:34:53', 58000.00, 'nequi'),
(69, '2026-04-29 18:40:50', 40000.00, 'efectivo'),
(70, '2026-04-29 20:35:36', 90000.00, 'efectivo'),
(71, '2026-04-29 20:35:44', 28000.00, 'efectivo'),
(72, '2026-04-29 20:35:55', 80000.00, 'nequi'),
(73, '2026-04-29 21:22:29', 28000.00, 'daviplata'),
(74, '2026-04-29 21:26:47', 32000.00, 'transferencia'),
(75, '2026-04-29 21:35:38', 3000.00, 'efectivo'),
(76, '2026-04-30 22:37:16', 37500.00, 'mixto'),
(77, '2026-04-30 22:46:58', 7000.00, 'efectivo'),
(78, '2026-04-30 22:49:51', 12000.00, 'nequi');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `venta_pagos`
--

CREATE TABLE `venta_pagos` (
  `id` int(11) NOT NULL,
  `venta_id` int(11) NOT NULL,
  `metodo_pago` varchar(50) NOT NULL,
  `monto` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `venta_pagos`
--

INSERT INTO `venta_pagos` (`id`, `venta_id`, `metodo_pago`, `monto`) VALUES
(1, 25, 'efectivo', 30000.00),
(2, 25, 'nequi', 30000.00),
(3, 26, 'efectivo', 10000.00),
(4, 26, 'nequi', 20500.00),
(5, 27, 'daviplata', 10000.00),
(6, 27, 'transferencia', 20000.00),
(7, 28, 'efectivo', 10000.00),
(8, 28, 'daviplata', 7000.00),
(9, 29, 'efectivo', 5000.00),
(10, 29, 'nequi', 5500.00),
(11, 30, 'efectivo', 10000.00),
(12, 30, 'transferencia', 10000.00),
(13, 31, 'efectivo', 90000.00),
(14, 32, 'nequi', 10000.00),
(15, 33, 'efectivo', 5000.00),
(16, 33, 'nequi', 5000.00),
(17, 34, 'efectivo', 65500.00),
(18, 35, 'efectivo', 8500.00),
(19, 35, 'nequi', 60000.00),
(20, 36, 'efectivo', 4000.00),
(21, 37, 'daviplata', 7500.00),
(22, 38, 'nequi', 3000.00),
(23, 39, 'efectivo', 58000.00),
(24, 40, 'nequi', 5000.00),
(25, 41, 'nequi', 7000.00),
(26, 42, 'transferencia', 15000.00),
(27, 43, 'nequi', 30000.00),
(28, 43, 'daviplata', 35500.00),
(29, 44, 'nequi', 6000.00),
(30, 45, 'nequi', 7500.00),
(31, 46, 'efectivo', 20000.00),
(32, 46, 'daviplata', 2000.00),
(33, 47, 'efectivo', 14000.00),
(34, 48, 'nequi', 7000.00),
(35, 49, 'daviplata', 19000.00),
(36, 50, 'efectivo', 5000.00),
(37, 50, 'daviplata', 5000.00),
(38, 51, 'efectivo', 5000.00),
(39, 51, 'nequi', 5000.00),
(40, 52, 'daviplata', 17500.00),
(41, 53, 'efectivo', 58000.00),
(42, 54, 'efectivo', 10000.00),
(43, 55, 'efectivo', 10000.00),
(44, 56, 'efectivo', 15000.00),
(45, 57, 'nequi', 15500.00),
(46, 58, 'efectivo', 5000.00),
(47, 58, 'nequi', 5000.00),
(48, 59, 'efectivo', 20000.00),
(49, 60, 'efectivo', 5000.00),
(50, 60, 'nequi', 5000.00),
(51, 61, 'efectivo', 5000.00),
(52, 61, 'nequi', 5000.00),
(53, 62, 'efectivo', 5000.00),
(54, 62, 'nequi', 5000.00),
(55, 62, 'daviplata', 10000.00),
(56, 63, 'efectivo', 10000.00),
(57, 64, 'efectivo', 5000.00),
(58, 64, 'nequi', 5000.00),
(59, 65, 'efectivo', 5000.00),
(60, 65, 'nequi', 5000.00),
(61, 65, 'daviplata', 5000.00),
(62, 65, 'transferencia', 5000.00),
(63, 66, 'nequi', 51500.00),
(64, 67, 'efectivo', 18500.00),
(65, 68, 'nequi', 58000.00),
(66, 69, 'efectivo', 40000.00),
(67, 70, 'efectivo', 90000.00),
(68, 71, 'efectivo', 28000.00),
(69, 72, 'nequi', 80000.00),
(70, 73, 'daviplata', 28000.00),
(71, 74, 'transferencia', 32000.00),
(72, 75, 'efectivo', 3000.00),
(73, 76, 'efectivo', 17500.00),
(74, 76, 'nequi', 20000.00),
(75, 77, 'efectivo', 7000.00),
(76, 78, 'nequi', 12000.00);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_venta_extras`
--
ALTER TABLE `detalle_venta_extras`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detalle_venta_sabores`
--
ALTER TABLE `detalle_venta_sabores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detalle_venta_id` (`detalle_venta_id`),
  ADD KEY `sabor_id` (`sabor_id`);

--
-- Indices de la tabla `extras`
--
ALTER TABLE `extras`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_producto_categoria` (`categoria_id`);

--
-- Indices de la tabla `producto_reglas_extras`
--
ALTER TABLE `producto_reglas_extras`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `producto_sabores`
--
ALTER TABLE `producto_sabores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `sabor_id` (`sabor_id`);

--
-- Indices de la tabla `sabores`
--
ALTER TABLE `sabores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipos_extra`
--
ALTER TABLE `tipos_extra`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `venta_pagos`
--
ALTER TABLE `venta_pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venta_id` (`venta_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT de la tabla `detalle_venta_extras`
--
ALTER TABLE `detalle_venta_extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT de la tabla `detalle_venta_sabores`
--
ALTER TABLE `detalle_venta_sabores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `extras`
--
ALTER TABLE `extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `producto_reglas_extras`
--
ALTER TABLE `producto_reglas_extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `producto_sabores`
--
ALTER TABLE `producto_sabores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT de la tabla `sabores`
--
ALTER TABLE `sabores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `tipos_extra`
--
ALTER TABLE `tipos_extra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT de la tabla `venta_pagos`
--
ALTER TABLE `venta_pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_venta_sabores`
--
ALTER TABLE `detalle_venta_sabores`
  ADD CONSTRAINT `detalle_venta_sabores_ibfk_1` FOREIGN KEY (`detalle_venta_id`) REFERENCES `detalle_venta` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_venta_sabores_ibfk_2` FOREIGN KEY (`sabor_id`) REFERENCES `sabores` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_producto_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);

--
-- Filtros para la tabla `producto_sabores`
--
ALTER TABLE `producto_sabores`
  ADD CONSTRAINT `producto_sabores_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `producto_sabores_ibfk_2` FOREIGN KEY (`sabor_id`) REFERENCES `sabores` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `venta_pagos`
--
ALTER TABLE `venta_pagos`
  ADD CONSTRAINT `venta_pagos_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
