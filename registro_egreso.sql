-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-10-2026 a las 04:02:46
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
-- Base de datos: `registro_egreso`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas`
--

CREATE TABLE `personas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `cargo` varchar(150) NOT NULL,
  `departamento` varchar(150) NOT NULL,
  `documento` varchar(50) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personas`
--

INSERT INTO `personas` (`id`, `nombre`, `cargo`, `departamento`, `documento`, `activo`, `created_at`) VALUES
(1, 'Empleado', 'Empleado', 'General', 'empleado', 1, '2026-10-03 01:40:48'),
(2, 'Juan', 'Empleado', 'General', '45486377', 1, '2026-10-03 01:58:12'),
(3, 'Juan Perez', 'Empleado', 'General', 'juan.perez', 1, '2026-10-03 02:00:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registros`
--

CREATE TABLE `registros` (
  `id` int(11) NOT NULL,
  `persona_id` int(11) NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `destino` varchar(255) NOT NULL,
  `fecha_salida` date NOT NULL,
  `hora_salida` time NOT NULL,
  `observaciones` text DEFAULT NULL,
  `usuario_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `registros`
--

INSERT INTO `registros` (`id`, `persona_id`, `tipo`, `motivo`, `destino`, `fecha_salida`, `hora_salida`, `observaciones`, `usuario_id`, `created_at`) VALUES
(1, 1, 'Ingreso', 'Registro rápido', 'Trabajo', '2026-10-03', '03:40:00', '', 2, '2026-10-03 01:40:48'),
(2, 1, 'Salida', 'Registro rápido', 'Trabajo', '2026-10-03', '03:41:00', '', 2, '2026-10-03 01:41:00'),
(3, 1, 'Salida', 'Registro rápido', 'Trabajo', '2026-10-03', '03:41:00', '', 2, '2026-10-03 01:41:48'),
(4, 3, 'Ingreso', 'Registro rápido', 'Trabajo', '2026-10-03', '04:00:00', '', 12, '2026-10-03 02:00:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `rol` varchar(50) NOT NULL DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `nombre`, `rol`, `created_at`) VALUES
(1, 'admin', '$2y$10$y1LP0oty2nhs6wu3byaS7OZkmoMQq8bb33ouRt12dZ8w6L3O4xB7O', 'Administrador', 'admin', '2026-10-03 01:33:45'),
(2, 'empleado', '$2y$10$S2qAjepDRwYn1urbb6TgweyA2e2qn5nRu34irB7AiTtKAk0XgnMva', 'Empleado', 'empleado', '2026-10-03 01:36:55'),
(12, 'juan.perez', '$2y$10$53tk20BbDLaWlsw/b0u34uyq/4bNfZ0b1VFKHF3kKlGdDq1/CN802', 'Juan Perez', 'empleado', '2026-10-03 01:43:52'),
(13, 'maria.lopez', '$2y$10$reDMh1oz8lM8XN8NA.b.fOKfA/uGrZh1cyj94mNYgoYRWy2.1glJi', 'Maria Lopez', 'empleado', '2026-10-03 01:43:52'),
(14, 'carlos.garcia', '$2y$10$7dbpj.Cp5VK05gMlbMtkS.CZx6s2rK7mLDcvB0ATHwEm6MZwrhoo6', 'Carlos Garcia', 'empleado', '2026-10-03 01:43:52'),
(15, 'ana.morales', '$2y$10$mrAgJv8SPj7CtJPzPaP6LOqYnPO9zPTYPjF2/uH87e6Mu1yX3EV.C', 'Ana Morales', 'empleado', '2026-10-03 01:43:52'),
(16, 'diego.rivera', '$2y$10$GpymsfuH.qIXETEqv3pkq.2oXhZ9T4VMvnTECWxZbWHkV3VLnuLuW', 'Diego Rivera', 'empleado', '2026-10-03 01:43:52'),
(17, 'sofia.navarro', '$2y$10$iyNHbKWg/NCg6BU/n0mioOPNKcQM9kDzucx04tbLIyrNKjbwvf.xi', 'Sofia Navarro', 'empleado', '2026-10-03 01:43:52');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `personas`
--
ALTER TABLE `personas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `documento` (`documento`);

--
-- Indices de la tabla `registros`
--
ALTER TABLE `registros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `persona_id` (`persona_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `personas`
--
ALTER TABLE `personas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `registros`
--
ALTER TABLE `registros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `registros`
--
ALTER TABLE `registros`
  ADD CONSTRAINT `registros_ibfk_1` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registros_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
