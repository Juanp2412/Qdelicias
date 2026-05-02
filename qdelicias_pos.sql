-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-04-2026 a las 02:24:11
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
(33, 16, 13, 1, 7500.00);

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
(25, 33, 7, 0.00);

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
(5, 'Premium', 2500.00, 'topping clasico'),
(6, 'Clasico', 1500.00, 'topping clasico'),
(7, 'salsas', 500.00, 'salsa'),
(8, 'chocolate', 500.00, 'salsa'),
(9, 'Fruta del dia', 1000.00, 'Fruta del dia'),
(10, 'Salsa Mora', 500.00, 'salsa'),
(11, 'Boli Pop Morada', 2000.00, 'topping premium');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `precio`) VALUES
(1, 'Fresa Mini', 5000.00),
(2, 'Fresa Clásica', 7000.00),
(3, 'Waffle Sencillo', 12000.00),
(4, 'Waffle Especial', 15000.00),
(5, 'Oblea Clásica', 3000.00),
(6, 'Malteada Oreo', 11000.00),
(8, 'Fresa Premium', 20000.00),
(9, 'Oblea Mix', 4500.00),
(12, 'Oblea Plus', 6500.00),
(13, 'Mini Waffle Plus', 7500.00),
(14, 'Oblea Premium', 7500.00),
(15, 'Alitas Personales', 10000.00),
(16, 'Alas Pareja ', 22000.00),
(17, 'Alas Familiar', 58000.00);

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
(9, 4, 'Fruta del dia', 1);

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
  `rol` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `contraseña`, `rol`) VALUES
(1, 'admin', '1234', 'admin');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `fecha`, `total`) VALUES
(1, '2026-04-16 16:15:26', 30000.00),
(2, '2026-04-16 16:24:48', 60000.00),
(3, '2026-04-16 16:33:03', 15000.00),
(4, '2026-04-16 16:51:56', 11000.00),
(5, '2026-04-16 17:47:09', 20000.00),
(6, '2026-04-17 13:02:04', 20000.00),
(7, '2026-04-17 13:02:35', 10000.00),
(8, '2026-04-17 13:02:43', 20000.00),
(9, '2026-04-17 13:03:03', 20000.00),
(10, '2026-04-17 13:22:28', 21000.00),
(11, '2026-04-17 14:29:34', 76000.00),
(12, '2026-04-17 14:32:07', 5500.00),
(13, '2026-04-17 16:03:46', 18500.00),
(14, '2026-04-17 16:15:06', 20000.00),
(15, '2026-04-17 16:29:20', 24000.00),
(16, '2026-04-17 19:39:34', 43500.00);

--
-- Índices para tablas volcadas
--

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
-- Indices de la tabla `extras`
--
ALTER TABLE `extras`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `producto_reglas_extras`
--
ALTER TABLE `producto_reglas_extras`
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
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `detalle_venta_extras`
--
ALTER TABLE `detalle_venta_extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `extras`
--
ALTER TABLE `extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `producto_reglas_extras`
--
ALTER TABLE `producto_reglas_extras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `tipos_extra`
--
ALTER TABLE `tipos_extra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
