-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-06-2025 a las 13:50:39
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
-- Base de datos: `gestion_consultas`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `consultas_a_cancelar` (IN `p_idprofesor` INT, IN `p_fecha_desde` DATE, IN `p_fecha_hasta` DATE)   BEGIN
    SELECT 
        ch.idconsultas_horario,
        m.nombre_materia,
        ch.fecha_consulta AS fecha,
        ch.hora_ini,
        ch.dia
    FROM 
        consultas_horario ch
        INNER JOIN materia m ON ch.idmateria = m.idmateria
    WHERE 
        ch.idprofesor = p_idprofesor
        AND ch.estado = 'Aceptada'
        AND (
            (ch.fecha_consulta BETWEEN p_fecha_desde AND p_fecha_hasta)
            OR
            (ch.fecha_consulta IS NULL AND ch.dia = DAYNAME(p_fecha_desde))
        )
        AND NOT EXISTS (
            SELECT 1 FROM consultas_horarios_bloqueos chb
            WHERE chb.idconsultas_horario = ch.idconsultas_horario
            AND chb.fecha_bloqueo = p_fecha_desde
        );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `consultas_canceladas` (IN `p_idprofesor` INT, IN `p_offset` INT, IN `p_limit` INT)   BEGIN
    -- Consultas rechazadas
    SELECT 
        m.nombre_materia,
        p.nombre_profesor,
        c.fecha AS fecha_bloqueo,
        ch.hora_ini,
        ch.hora_fin,
        'Rechazado' AS motivo
    FROM 
        consultas c
        INNER JOIN consultas_horario ch ON c.idconsultas_horario = ch.idconsultas_horario
        INNER JOIN materia m ON ch.idmateria = m.idmateria
        INNER JOIN profesor p ON ch.idprofesor = p.idprofesor
    WHERE 
        ch.idprofesor = p_idprofesor
        AND c.estado = 'Rechazado'
        AND c.fecha >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
    
    UNION ALL
    
    -- Bloqueos programados
    SELECT 
        m.nombre_materia,
        p.nombre_profesor,
        chb.fecha_bloqueo AS fecha_bloqueo,
        ch.hora_ini,
        ch.hora_fin,
        chb.motivo AS motivo
    FROM 
        consultas_horarios_bloqueos chb
        INNER JOIN consultas_horario ch ON chb.idconsultas_horario = ch.idconsultas_horario
        INNER JOIN materia m ON ch.idmateria = m.idmateria
        INNER JOIN profesor p ON ch.idprofesor = p.idprofesor
    WHERE 
        ch.idprofesor = p_idprofesor
        AND chb.fecha_bloqueo >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
    
    ORDER BY 
        fecha_bloqueo DESC
    LIMIT p_offset, p_limit;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `filtro_consultas` (IN `p_idmateria` INT, IN `p_idprofesor` INT)   BEGIN
    SELECT 
        ch.idconsultas_horario,
        m.nombre_materia,
        p.nombre_profesor,
        ch.fecha_consulta AS fecha,
        ch.dia,
        ch.hora_ini,
        ch.hora_fin
    FROM 
        consultas_horario ch
        INNER JOIN materia m ON ch.idmateria = m.idmateria
        INNER JOIN profesor p ON ch.idprofesor = p.idprofesor
    WHERE 
        (p_idmateria = -1 OR ch.idmateria = p_idmateria) AND
        (p_idprofesor = -1 OR ch.idprofesor = p_idprofesor) AND
        ch.estado = 'Aceptada';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `proximas_consultas` (IN `p_idprofesor` INT, IN `p_offset` INT, IN `p_limit` INT)   BEGIN
    SELECT 
        m.nombre_materia,
        p.nombre_profesor,
        ch.fecha_consulta AS fecha_gen,
        ch.hora_ini,
        ch.hora_fin,
        ch.dia,
        COUNT(c.idconsultas) AS cantidad_alumnos
    FROM 
        consultas_horario ch
        INNER JOIN materia m ON ch.idmateria = m.idmateria
        INNER JOIN profesor p ON ch.idprofesor = p.idprofesor
        LEFT JOIN consultas c ON ch.idconsultas_horario = c.idconsultas_horario 
            AND c.estado IN ('Confirmado', 'Aceptado')  -- Ampliar estados válidos
    WHERE 
        ch.idprofesor = p_idprofesor
        AND ch.estado IN ('Activo', 'Aceptada')  -- Ampliar estados válidos
        AND (ch.fecha_consulta >= CURDATE() || ch.fecha_consulta is null)  -- Solo futuras
        AND (c.fecha >= CURDATE())
    GROUP BY 
        ch.idconsultas_horario
    ORDER BY 
        ch.fecha_consulta ASC, ch.hora_ini ASC
    LIMIT p_offset, p_limit;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumno`
--

CREATE TABLE `alumno` (
  `idalumno` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `legajo` varchar(20) NOT NULL,
  `correo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alumno`
--

INSERT INTO `alumno` (`idalumno`, `nombre`, `apellido`, `legajo`, `correo`) VALUES
(1, 'JUAN FDSFSDD', 'schar', '41798', 'alumno1@gmail.com'),
(2, 'JUAN TEST', '', '41796', 'test1@gmail.com'),
(3, 'JOSE ASDFD', '', '41795', 'alumno1@gmail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consultas`
--

CREATE TABLE `consultas` (
  `idconsultas` int(11) NOT NULL,
  `idalumno` int(11) NOT NULL,
  `idconsultas_horario` int(11) NOT NULL,
  `fecha` date NULL,
  `estado` enum('Pendiente','Confirmado','Rechazado') DEFAULT 'Pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `consultas`
--

INSERT INTO `consultas` (`idconsultas`, `idalumno`, `idconsultas_horario`, `fecha`, `estado`) VALUES
(1, 2, 17, '2025-05-02', 'Confirmado'),
(2, 3, 17, '2025-05-02', 'Rechazado'),
(3, 1, 17, '2025-05-02', 'Confirmado'),
(4, 1, 18, '2025-05-03', 'Pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consultas_horario`
--

CREATE TABLE `consultas_horario` (
  `idconsultas_horario` int(11) NOT NULL,
  `idmateria` int(11) NOT NULL,
  `idprofesor` int(11) NOT NULL,
  `dia` varchar(10) NOT NULL,
  `id_dia` int(11) NOT NULL,
  `hora_ini` time NOT NULL,
  `hora_fin` time NOT NULL,
  `fecha_consulta` date DEFAULT NULL,
  `estado` enum('Activo','Inactivo','Pendiente','Aceptada','Rechazada') DEFAULT 'Pendiente',
  `Fecha_carga` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `consultas_horario`
--

INSERT INTO `consultas_horario` (`idconsultas_horario`, `idmateria`, `idprofesor`, `dia`, `id_dia`, `hora_ini`, `hora_fin`, `fecha_consulta`, `estado`, `Fecha_carga`) VALUES
(16, 1, 1, 'lunes', 0, '12:30:00', '23:15:00', '2025-04-30', 'Aceptada', '2025-04-29'),
(17, 2, 1, 'martes', 1, '11:15:00', '23:30:00', '2025-05-02', 'Aceptada', '2025-04-29'),
(18, 3, 1, 'miércoles', 2, '13:45:00', '23:30:00', '2025-05-03', 'Aceptada', '2025-04-29'),
(19, 2, 2, 'jueves', 3, '19:00:00', '16:30:00', NULL, 'Aceptada', '2025-04-29'),
(20, 3, 2, 'viernes', 4, '08:30:00', '17:30:00', NULL, 'Aceptada', '2025-04-29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `consultas_horarios_bloqueos`
--

CREATE TABLE `consultas_horarios_bloqueos` (
  `id` int(11) NOT NULL,
  `idconsultas_horario` int(11) NOT NULL,
  `fecha_bloqueo` date NOT NULL,
  `motivo` text NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `consultas_horarios_bloqueos`
--

INSERT INTO `consultas_horarios_bloqueos` (`id`, `idconsultas_horario`, `fecha_bloqueo`, `motivo`, `fecha_creacion`) VALUES
(1, 17, '2025-05-02', 'No estoy ese día', '2025-05-01 15:08:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materia`
--

CREATE TABLE `materia` (
  `idmateria` int(11) NOT NULL,
  `nombre_materia` varchar(100) NOT NULL,
  `cod_materia` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `materia`
--

INSERT INTO `materia` (`idmateria`, `nombre_materia`, `cod_materia`) VALUES
(1, 'Entornos graficos', 'B101'),
(2, 'Simulación', 'S354'),
(3, 'Inteligencia Artificial', 'S489');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias_profesores`
--

CREATE TABLE `materias_profesores` (
  `id` int(11) NOT NULL,
  `idmateria` int(11) NOT NULL,
  `idprofesor` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `profesor`
--

CREATE TABLE `profesor` (
  `idprofesor` int(11) NOT NULL,
  `nombre_profesor` varchar(100) NOT NULL,
  `legajo` varchar(20) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `profesor`
--

INSERT INTO `profesor` (`idprofesor`, `nombre_profesor`, `legajo`, `correo`, `observaciones`) VALUES
(1, 'test prof', '105698', 'test@gmail.com', 'observacion 1'),
(2, 'Guiliana', '104891', 'giu@gmail.com', 'dfsgfggg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `idusuario` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `idprofesor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idusuario`, `usuario`, `password`, `idprofesor`) VALUES
(1, 'juanschar', '7a42f3f6148e2404542e1b4eade9a918', NULL),
(2, 'testprof', '7a42f3f6148e2404542e1b4eade9a918', 1);

INSERT INTO `materias_profesores` (`id`, `idmateria`, `idprofesor`) VALUES 
(1, 1, 1), (2, 3, 1), (5, 2, 1), (3, 2, 2), (4, 3, 2);
-- --------------------------------------------------------

--
-- Estructura para la vista `consultas_pendientes_aprobacion`
--

CREATE VIEW `consultas_pendientes_aprobacion`  AS SELECT `c`.`idconsultas` AS `id`, `m`.`nombre_materia` AS `nombre_materia`, `c`.`fecha` AS `fecha`, `ch`.`dia` AS `dia`, concat(`ch`.`hora_ini`,' - ',`ch`.`hora_fin`) AS `hora_ini_fin`, concat(`a`.`nombre`,' ',`a`.`apellido`) AS `nombre`, `a`.`correo` AS `correo` FROM (((`consultas` `c` join `alumno` `a` on(`c`.`idalumno` = `a`.`idalumno`)) join `consultas_horario` `ch` on(`c`.`idconsultas_horario` = `ch`.`idconsultas_horario`)) join `materia` `m` on(`ch`.`idmateria` = `m`.`idmateria`)) WHERE `c`.`estado` = 'Pendiente' ;

-- --------------------------------------------------------

--
-- Estructura para la vista `consultas_pendientes_aprobacion_admin`
--

CREATE VIEW `consultas_pendientes_aprobacion_admin`  AS SELECT `ch`.`idconsultas_horario` AS `id`, `m`.`nombre_materia` AS `nombre_materia`, `p`.`nombre_profesor` AS `nombre_profesor`, `p`.`legajo` AS `legajo`, `ch`.`fecha_consulta` AS `fecha_consulta`, `ch`.`dia` AS `dia`, concat(`ch`.`hora_ini`,' - ',`ch`.`hora_fin`) AS `hora_ini_fin`, `ch`.`estado` AS `estado` FROM ((`consultas_horario` `ch` join `materia` `m` on(`ch`.`idmateria` = `m`.`idmateria`)) join `profesor` `p` on(`ch`.`idprofesor` = `p`.`idprofesor`)) WHERE `ch`.`estado` = 'Pendiente' ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alumno`
--
ALTER TABLE `alumno`
  ADD PRIMARY KEY (`idalumno`);

--
-- Indices de la tabla `consultas`
--
ALTER TABLE `consultas`
  ADD PRIMARY KEY (`idconsultas`),
  ADD KEY `idalumno` (`idalumno`),
  ADD KEY `idconsultas_horario` (`idconsultas_horario`);

--
-- Indices de la tabla `consultas_horario`
--
ALTER TABLE `consultas_horario`
  ADD PRIMARY KEY (`idconsultas_horario`),
  ADD KEY `idmateria` (`idmateria`),
  ADD KEY `idprofesor` (`idprofesor`);

--
-- Indices de la tabla `consultas_horarios_bloqueos`
--
ALTER TABLE `consultas_horarios_bloqueos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idconsultas_horario` (`idconsultas_horario`);

--
-- Indices de la tabla `materia`
--
ALTER TABLE `materia`
  ADD PRIMARY KEY (`idmateria`),
  ADD UNIQUE KEY `cod_materia` (`cod_materia`);

--
-- Indices de la tabla `materias_profesores`
--
ALTER TABLE `materias_profesores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idmateria` (`idmateria`,`idprofesor`),
  ADD KEY `idprofesor` (`idprofesor`);

--
-- Indices de la tabla `profesor`
--
ALTER TABLE `profesor`
  ADD PRIMARY KEY (`idprofesor`),
  ADD UNIQUE KEY `legajo` (`legajo`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`idusuario`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `idprofesor` (`idprofesor`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alumno`
--
ALTER TABLE `alumno`
  MODIFY `idalumno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `consultas`
--
ALTER TABLE `consultas`
  MODIFY `idconsultas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `consultas_horario`
--
ALTER TABLE `consultas_horario`
  MODIFY `idconsultas_horario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `consultas_horarios_bloqueos`
--
ALTER TABLE `consultas_horarios_bloqueos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `materia`
--
ALTER TABLE `materia`
  MODIFY `idmateria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `materias_profesores`
--
ALTER TABLE `materias_profesores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `profesor`
--
ALTER TABLE `profesor`
  MODIFY `idprofesor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `idusuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `consultas`
--
ALTER TABLE `consultas`
  ADD CONSTRAINT `consultas_ibfk_1` FOREIGN KEY (`idalumno`) REFERENCES `alumno` (`idalumno`),
  ADD CONSTRAINT `consultas_ibfk_2` FOREIGN KEY (`idconsultas_horario`) REFERENCES `consultas_horario` (`idconsultas_horario`);

--
-- Filtros para la tabla `consultas_horario`
--
ALTER TABLE `consultas_horario`
  ADD CONSTRAINT `consultas_horario_ibfk_1` FOREIGN KEY (`idmateria`) REFERENCES `materia` (`idmateria`),
  ADD CONSTRAINT `consultas_horario_ibfk_2` FOREIGN KEY (`idprofesor`) REFERENCES `profesor` (`idprofesor`);

--
-- Filtros para la tabla `consultas_horarios_bloqueos`
--
ALTER TABLE `consultas_horarios_bloqueos`
  ADD CONSTRAINT `consultas_horarios_bloqueos_ibfk_1` FOREIGN KEY (`idconsultas_horario`) REFERENCES `consultas_horario` (`idconsultas_horario`);

--
-- Filtros para la tabla `materias_profesores`
--
ALTER TABLE `materias_profesores`
  ADD CONSTRAINT `materias_profesores_ibfk_1` FOREIGN KEY (`idmateria`) REFERENCES `materia` (`idmateria`),
  ADD CONSTRAINT `materias_profesores_ibfk_2` FOREIGN KEY (`idprofesor`) REFERENCES `profesor` (`idprofesor`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`idprofesor`) REFERENCES `profesor` (`idprofesor`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
