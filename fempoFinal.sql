-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: db
-- Tiempo de generación: 06-06-2025 a las 14:21:00
-- Versión del servidor: 8.0.42
-- Versión de PHP: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `fempo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ALUMNE`
--

CREATE TABLE `ALUMNE` (
  `id` int NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `llinatge` varchar(100) DEFAULT NULL,
  `grau` varchar(100) DEFAULT NULL,
  `curs` varchar(20) DEFAULT NULL,
  `convocatoria` varchar(20) DEFAULT NULL,
  `promocio` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `professor_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `CONTACTE`
--

CREATE TABLE `CONTACTE` (
  `id` int NOT NULL,
  `empresa_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `EMPRESA`
--

CREATE TABLE `EMPRESA` (
  `id` int NOT NULL,
  `nomE` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `PROFESSOR`
--

CREATE TABLE `PROFESSOR` (
  `id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `PROFESSOR`
--

INSERT INTO `PROFESSOR` (`id`) VALUES
(7),
(9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `SOLICITUD`
--

CREATE TABLE `SOLICITUD` (
  `numeroSolicitud` varchar(50) NOT NULL,
  `estat` varchar(50) DEFAULT NULL,
  `alumne_id` int DEFAULT NULL,
  `empresa_id` int DEFAULT NULL,
  `professor_id` int DEFAULT NULL,
  `grau` varchar(10) NOT NULL,
  `curs` int NOT NULL,
  `convocatoria` varchar(20) NOT NULL,
  `promocio` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `USUARI`
--

CREATE TABLE `USUARI` (
  `id` int NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `llinatges` varchar(100) DEFAULT NULL,
  `correu` varchar(100) DEFAULT NULL,
  `contrasenya` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `USUARI`
--

INSERT INTO `USUARI` (`id`, `nom`, `llinatges`, `correu`, `contrasenya`) VALUES
(7, 'Martin', 'Vich', 'mvich@iesemilidarder.com', '$2y$10$RjytAAOVONFvqBHizO8ogeVAxEG9gBCd.UJK2CMLEBJAtstTkzWtq'),
(9, 'Pepino', 'Pepone', 'pepinopepone@iesemilidarder.com', '$2y$10$aoe6D3Vkeeka5R5SC2NyQu8eiY8YVDEG88yjBjpYntW0M0EhxmYNK');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ALUMNE`
--
ALTER TABLE `ALUMNE`
  ADD PRIMARY KEY (`id`),
  ADD KEY `professor_id` (`professor_id`);

--
-- Indices de la tabla `CONTACTE`
--
ALTER TABLE `CONTACTE`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indices de la tabla `EMPRESA`
--
ALTER TABLE `EMPRESA`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `PROFESSOR`
--
ALTER TABLE `PROFESSOR`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `SOLICITUD`
--
ALTER TABLE `SOLICITUD`
  ADD PRIMARY KEY (`numeroSolicitud`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `professor_id` (`professor_id`),
  ADD KEY `SOLICITUD_ibfk_1` (`alumne_id`);

--
-- Indices de la tabla `USUARI`
--
ALTER TABLE `USUARI`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correu` (`correu`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ALUMNE`
--
ALTER TABLE `ALUMNE`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `EMPRESA`
--
ALTER TABLE `EMPRESA`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `USUARI`
--
ALTER TABLE `USUARI`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `ALUMNE`
--
ALTER TABLE `ALUMNE`
  ADD CONSTRAINT `ALUMNE_ibfk_1` FOREIGN KEY (`professor_id`) REFERENCES `PROFESSOR` (`id`);

--
-- Filtros para la tabla `CONTACTE`
--
ALTER TABLE `CONTACTE`
  ADD CONSTRAINT `CONTACTE_ibfk_1` FOREIGN KEY (`id`) REFERENCES `USUARI` (`id`),
  ADD CONSTRAINT `CONTACTE_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `EMPRESA` (`id`);

--
-- Filtros para la tabla `PROFESSOR`
--
ALTER TABLE `PROFESSOR`
  ADD CONSTRAINT `PROFESSOR_ibfk_1` FOREIGN KEY (`id`) REFERENCES `USUARI` (`id`);

--
-- Filtros para la tabla `SOLICITUD`
--
ALTER TABLE `SOLICITUD`
  ADD CONSTRAINT `SOLICITUD_ibfk_1` FOREIGN KEY (`alumne_id`) REFERENCES `ALUMNE` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `SOLICITUD_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `EMPRESA` (`id`),
  ADD CONSTRAINT `SOLICITUD_ibfk_3` FOREIGN KEY (`professor_id`) REFERENCES `PROFESSOR` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
