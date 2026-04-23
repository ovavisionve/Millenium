-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-04-2026 a las 02:32:27
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
-- Base de datos: `millennium`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bancos`
--

CREATE TABLE `bancos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `bancos`
--

INSERT INTO `bancos` (`id`, `nombre`, `descripcion`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Banesco Karina', 'Cuenta bancaria operativa de Karina.', 1, '2026-04-16 17:45:19', '2026-04-16 17:45:19'),
(2, 'Banesco Nelson', 'Cuenta bancaria operativa de Nelson.', 1, '2026-04-16 17:45:19', '2026-04-16 17:45:19'),
(3, 'BNC', 'Banco Nacional de Credito.', 1, '2026-04-16 17:45:19', '2026-04-16 17:45:19'),
(4, 'Banesco', 'Banco demo', 1, '2026-04-16 20:13:31', '2026-04-16 20:13:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `codigo` varchar(32) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `unidad` varchar(16) NOT NULL DEFAULT 'unidad',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `codigo`, `nombre`, `descripcion`, `unidad`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'VACA', 'Vaca', 'Línea vacuno (animal / venta por categoría). El código se usa en reportes y filtros para comparar ventas de vaca vs otras líneas.', 'unidad', 1, '2026-04-16 04:58:21', '2026-04-16 04:58:21'),
(2, 'BUF', 'Búfalo', 'Línea búfalo. Un código corto (BUF) permite segmentar ventas por categoría en reportes cruzados.', 'unidad', 1, '2026-04-16 04:58:21', '2026-04-16 04:58:21'),
(3, 'TRASTE', 'Trastes', 'Trastes y similares (categoría aparte). Útil para medir cuánto se vende de esa línea sin mezclarla con otras.', 'unidad', 1, '2026-04-16 04:58:21', '2026-04-21 16:56:48'),
(4, 'KG', 'Toro', 'Categoría de prueba con unidad kg para reportes.', 'kg', 1, '2026-04-16 20:13:31', '2026-04-21 16:57:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ciudades`
--

CREATE TABLE `ciudades` (
  `id_ciudad` int(10) UNSIGNED NOT NULL,
  `id_estado` int(10) UNSIGNED NOT NULL,
  `nombre_ciudad` varchar(200) NOT NULL,
  `es_capital` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ciudades`
--

INSERT INTO `ciudades` (`id_ciudad`, `id_estado`, `nombre_ciudad`, `es_capital`) VALUES
(1, 1, 'Maroa', 0),
(2, 1, 'Puerto Ayacucho', 1),
(3, 1, 'San Fernando de Atabapo', 0),
(4, 2, 'Anaco', 0),
(5, 2, 'Aragua de Barcelona', 0),
(6, 2, 'Barcelona', 1),
(7, 2, 'Boca de Uchire', 0),
(8, 2, 'Cantaura', 0),
(9, 2, 'Clarines', 0),
(10, 2, 'El Chaparro', 0),
(11, 2, 'El Pao Anzoátegui', 0),
(12, 2, 'El Tigre', 0),
(13, 2, 'El Tigrito', 0),
(14, 2, 'Guanape', 0),
(15, 2, 'Guanta', 0),
(16, 2, 'Lechería', 0),
(17, 2, 'Onoto', 0),
(18, 2, 'Pariaguán', 0),
(19, 2, 'Píritu', 0),
(20, 2, 'Puerto La Cruz', 0),
(21, 2, 'Puerto Píritu', 0),
(22, 2, 'Sabana de Uchire', 0),
(23, 2, 'San Mateo Anzoátegui', 0),
(24, 2, 'San Pablo Anzoátegui', 0),
(25, 2, 'San Tomé', 0),
(26, 2, 'Santa Ana de Anzoátegui', 0),
(27, 2, 'Santa Fe Anzoátegui', 0),
(28, 2, 'Santa Rosa', 0),
(29, 2, 'Soledad', 0),
(30, 2, 'Urica', 0),
(31, 2, 'Valle de Guanape', 0),
(43, 3, 'Achaguas', 0),
(44, 3, 'Biruaca', 0),
(45, 3, 'Bruzual', 0),
(46, 3, 'El Amparo', 0),
(47, 3, 'El Nula', 0),
(48, 3, 'Elorza', 0),
(49, 3, 'Guasdualito', 0),
(50, 3, 'Mantecal', 0),
(51, 3, 'Puerto Páez', 0),
(52, 3, 'San Fernando de Apure', 1),
(53, 3, 'San Juan de Payara', 0),
(54, 4, 'Barbacoas', 0),
(55, 4, 'Cagua', 0),
(56, 4, 'Camatagua', 0),
(58, 4, 'Choroní', 0),
(59, 4, 'Colonia Tovar', 0),
(60, 4, 'El Consejo', 0),
(61, 4, 'La Victoria', 0),
(62, 4, 'Las Tejerías', 0),
(63, 4, 'Magdaleno', 0),
(64, 4, 'Maracay', 1),
(65, 4, 'Ocumare de La Costa', 0),
(66, 4, 'Palo Negro', 0),
(67, 4, 'San Casimiro', 0),
(68, 4, 'San Mateo', 0),
(69, 4, 'San Sebastián', 0),
(70, 4, 'Santa Cruz de Aragua', 0),
(71, 4, 'Tocorón', 0),
(72, 4, 'Turmero', 0),
(73, 4, 'Villa de Cura', 0),
(74, 4, 'Zuata', 0),
(75, 5, 'Barinas', 1),
(76, 5, 'Barinitas', 0),
(77, 5, 'Barrancas', 0),
(78, 5, 'Calderas', 0),
(79, 5, 'Capitanejo', 0),
(80, 5, 'Ciudad Bolivia', 0),
(81, 5, 'El Cantón', 0),
(82, 5, 'Las Veguitas', 0),
(83, 5, 'Libertad de Barinas', 0),
(84, 5, 'Sabaneta', 0),
(85, 5, 'Santa Bárbara de Barinas', 0),
(86, 5, 'Socopó', 0),
(87, 6, 'Caicara del Orinoco', 0),
(88, 6, 'Canaima', 0),
(89, 6, 'Ciudad Bolívar', 1),
(90, 6, 'Ciudad Piar', 0),
(91, 6, 'El Callao', 0),
(92, 6, 'El Dorado', 0),
(93, 6, 'El Manteco', 0),
(94, 6, 'El Palmar', 0),
(95, 6, 'El Pao', 0),
(96, 6, 'Guasipati', 0),
(97, 6, 'Guri', 0),
(98, 6, 'La Paragua', 0),
(99, 6, 'Matanzas', 0),
(100, 6, 'Puerto Ordaz', 0),
(101, 6, 'San Félix', 0),
(102, 6, 'Santa Elena de Uairén', 0),
(103, 6, 'Tumeremo', 0),
(104, 6, 'Unare', 0),
(105, 6, 'Upata', 0),
(106, 7, 'Bejuma', 0),
(107, 7, 'Belén', 0),
(108, 7, 'Campo de Carabobo', 0),
(109, 7, 'Canoabo', 0),
(110, 7, 'Central Tacarigua', 0),
(111, 7, 'Chirgua', 0),
(112, 7, 'Ciudad Alianza', 0),
(113, 7, 'El Palito', 0),
(114, 7, 'Guacara', 0),
(115, 7, 'Guigue', 0),
(116, 7, 'Las Trincheras', 0),
(117, 7, 'Los Guayos', 0),
(118, 7, 'Mariara', 0),
(119, 7, 'Miranda', 0),
(120, 7, 'Montalbán', 0),
(121, 7, 'Morón', 0),
(122, 7, 'Naguanagua', 0),
(123, 7, 'Puerto Cabello', 0),
(124, 7, 'San Joaquín', 0),
(125, 7, 'Tocuyito', 0),
(126, 7, 'Urama', 0),
(127, 7, 'Valencia', 1),
(128, 7, 'Vigirimita', 0),
(129, 8, 'Aguirre', 0),
(130, 8, 'Apartaderos Cojedes', 0),
(131, 8, 'Arismendi', 0),
(132, 8, 'Camuriquito', 0),
(133, 8, 'El Baúl', 0),
(134, 8, 'El Limón', 0),
(135, 8, 'El Pao Cojedes', 0),
(136, 8, 'El Socorro', 0),
(137, 8, 'La Aguadita', 0),
(138, 8, 'Las Vegas', 0),
(139, 8, 'Libertad de Cojedes', 0),
(140, 8, 'Mapuey', 0),
(141, 8, 'Piñedo', 0),
(142, 8, 'Samancito', 0),
(143, 8, 'San Carlos', 1),
(144, 8, 'Sucre', 0),
(145, 8, 'Tinaco', 0),
(146, 8, 'Tinaquillo', 0),
(147, 8, 'Vallecito', 0),
(148, 9, 'Tucupita', 1),
(149, 24, 'Caracas', 1),
(150, 24, 'El Junquito', 0),
(151, 10, 'Adícora', 0),
(152, 10, 'Boca de Aroa', 0),
(153, 10, 'Cabure', 0),
(154, 10, 'Capadare', 0),
(155, 10, 'Capatárida', 0),
(156, 10, 'Chichiriviche', 0),
(157, 10, 'Churuguara', 0),
(158, 10, 'Coro', 1),
(159, 10, 'Cumarebo', 0),
(160, 10, 'Dabajuro', 0),
(161, 10, 'Judibana', 0),
(162, 10, 'La Cruz de Taratara', 0),
(163, 10, 'La Vela de Coro', 0),
(164, 10, 'Los Taques', 0),
(165, 10, 'Maparari', 0),
(166, 10, 'Mene de Mauroa', 0),
(167, 10, 'Mirimire', 0),
(168, 10, 'Pedregal', 0),
(169, 10, 'Píritu Falcón', 0),
(170, 10, 'Pueblo Nuevo Falcón', 0),
(171, 10, 'Puerto Cumarebo', 0),
(172, 10, 'Punta Cardón', 0),
(173, 10, 'Punto Fijo', 0),
(174, 10, 'San Juan de Los Cayos', 0),
(175, 10, 'San Luis', 0),
(176, 10, 'Santa Ana Falcón', 0),
(177, 10, 'Santa Cruz De Bucaral', 0),
(178, 10, 'Tocopero', 0),
(179, 10, 'Tocuyo de La Costa', 0),
(180, 10, 'Tucacas', 0),
(181, 10, 'Yaracal', 0),
(182, 11, 'Altagracia de Orituco', 0),
(183, 11, 'Cabruta', 0),
(184, 11, 'Calabozo', 0),
(185, 11, 'Camaguán', 0),
(196, 11, 'Chaguaramas Guárico', 0),
(197, 11, 'El Socorro', 0),
(198, 11, 'El Sombrero', 0),
(199, 11, 'Las Mercedes de Los Llanos', 0),
(200, 11, 'Lezama', 0),
(201, 11, 'Onoto', 0),
(202, 11, 'Ortíz', 0),
(203, 11, 'San José de Guaribe', 0),
(204, 11, 'San Juan de Los Morros', 1),
(205, 11, 'San Rafael de Laya', 0),
(206, 11, 'Santa María de Ipire', 0),
(207, 11, 'Tucupido', 0),
(208, 11, 'Valle de La Pascua', 0),
(209, 11, 'Zaraza', 0),
(210, 12, 'Aguada Grande', 0),
(211, 12, 'Atarigua', 0),
(212, 12, 'Barquisimeto', 1),
(213, 12, 'Bobare', 0),
(214, 12, 'Cabudare', 0),
(215, 12, 'Carora', 0),
(216, 12, 'Cubiro', 0),
(217, 12, 'Cují', 0),
(218, 12, 'Duaca', 0),
(219, 12, 'El Manzano', 0),
(220, 12, 'El Tocuyo', 0),
(221, 12, 'Guaríco', 0),
(222, 12, 'Humocaro Alto', 0),
(223, 12, 'Humocaro Bajo', 0),
(224, 12, 'La Miel', 0),
(225, 12, 'Moroturo', 0),
(226, 12, 'Quíbor', 0),
(227, 12, 'Río Claro', 0),
(228, 12, 'Sanare', 0),
(229, 12, 'Santa Inés', 0),
(230, 12, 'Sarare', 0),
(231, 12, 'Siquisique', 0),
(232, 12, 'Tintorero', 0),
(233, 13, 'Apartaderos Mérida', 0),
(234, 13, 'Arapuey', 0),
(235, 13, 'Bailadores', 0),
(236, 13, 'Caja Seca', 0),
(237, 13, 'Canaguá', 0),
(238, 13, 'Chachopo', 0),
(239, 13, 'Chiguara', 0),
(240, 13, 'Ejido', 0),
(241, 13, 'El Vigía', 0),
(242, 13, 'La Azulita', 0),
(243, 13, 'La Playa', 0),
(244, 13, 'Lagunillas Mérida', 0),
(245, 13, 'Mérida', 1),
(246, 13, 'Mesa de Bolívar', 0),
(247, 13, 'Mucuchíes', 0),
(248, 13, 'Mucujepe', 0),
(249, 13, 'Mucuruba', 0),
(250, 13, 'Nueva Bolivia', 0),
(251, 13, 'Palmarito', 0),
(252, 13, 'Pueblo Llano', 0),
(253, 13, 'Santa Cruz de Mora', 0),
(254, 13, 'Santa Elena de Arenales', 0),
(255, 13, 'Santo Domingo', 0),
(256, 13, 'Tabáy', 0),
(257, 13, 'Timotes', 0),
(258, 13, 'Torondoy', 0),
(259, 13, 'Tovar', 0),
(260, 13, 'Tucani', 0),
(261, 13, 'Zea', 0),
(262, 14, 'Araguita', 0),
(263, 14, 'Carrizal', 0),
(264, 14, 'Caucagua', 0),
(265, 14, 'Chaguaramas Miranda', 0),
(266, 14, 'Charallave', 0),
(267, 14, 'Chirimena', 0),
(268, 14, 'Chuspa', 0),
(269, 14, 'Cúa', 0),
(270, 14, 'Cupira', 0),
(271, 14, 'Curiepe', 0),
(272, 14, 'El Guapo', 0),
(273, 14, 'El Jarillo', 0),
(274, 14, 'Filas de Mariche', 0),
(275, 14, 'Guarenas', 0),
(276, 14, 'Guatire', 0),
(277, 14, 'Higuerote', 0),
(278, 14, 'Los Anaucos', 0),
(279, 14, 'Los Teques', 1),
(280, 14, 'Ocumare del Tuy', 0),
(281, 14, 'Panaquire', 0),
(282, 14, 'Paracotos', 0),
(283, 14, 'Río Chico', 0),
(284, 14, 'San Antonio de Los Altos', 0),
(285, 14, 'San Diego de Los Altos', 0),
(286, 14, 'San Fernando del Guapo', 0),
(287, 14, 'San Francisco de Yare', 0),
(288, 14, 'San José de Los Altos', 0),
(289, 14, 'San José de Río Chico', 0),
(290, 14, 'San Pedro de Los Altos', 0),
(291, 14, 'Santa Lucía', 0),
(292, 14, 'Santa Teresa', 0),
(293, 14, 'Tacarigua de La Laguna', 0),
(294, 14, 'Tacarigua de Mamporal', 0),
(295, 14, 'Tácata', 0),
(296, 14, 'Turumo', 0),
(297, 15, 'Aguasay', 0),
(298, 15, 'Aragua de Maturín', 0),
(299, 15, 'Barrancas del Orinoco', 0),
(300, 15, 'Caicara de Maturín', 0),
(301, 15, 'Caripe', 0),
(302, 15, 'Caripito', 0),
(303, 15, 'Chaguaramal', 0),
(305, 15, 'Chaguaramas Monagas', 0),
(307, 15, 'El Furrial', 0),
(308, 15, 'El Tejero', 0),
(309, 15, 'Jusepín', 0),
(310, 15, 'La Toscana', 0),
(311, 15, 'Maturín', 1),
(312, 15, 'Miraflores', 0),
(313, 15, 'Punta de Mata', 0),
(314, 15, 'Quiriquire', 0),
(315, 15, 'San Antonio de Maturín', 0),
(316, 15, 'San Vicente Monagas', 0),
(317, 15, 'Santa Bárbara', 0),
(318, 15, 'Temblador', 0),
(319, 15, 'Teresen', 0),
(320, 15, 'Uracoa', 0),
(321, 16, 'Altagracia', 0),
(322, 16, 'Boca de Pozo', 0),
(323, 16, 'Boca de Río', 0),
(324, 16, 'El Espinal', 0),
(325, 16, 'El Valle del Espíritu Santo', 0),
(326, 16, 'El Yaque', 0),
(327, 16, 'Juangriego', 0),
(328, 16, 'La Asunción', 1),
(329, 16, 'La Guardia', 0),
(330, 16, 'Pampatar', 0),
(331, 16, 'Porlamar', 0),
(332, 16, 'Puerto Fermín', 0),
(333, 16, 'Punta de Piedras', 0),
(334, 16, 'San Francisco de Macanao', 0),
(335, 16, 'San Juan Bautista', 0),
(336, 16, 'San Pedro de Coche', 0),
(337, 16, 'Santa Ana de Nueva Esparta', 0),
(338, 16, 'Villa Rosa', 0),
(339, 17, 'Acarigua', 0),
(340, 17, 'Agua Blanca', 0),
(341, 17, 'Araure', 0),
(342, 17, 'Biscucuy', 0),
(343, 17, 'Boconoito', 0),
(344, 17, 'Campo Elías', 0),
(345, 17, 'Chabasquén', 0),
(346, 17, 'Guanare', 1),
(347, 17, 'Guanarito', 0),
(348, 17, 'La Aparición', 0),
(349, 17, 'La Misión', 0),
(350, 17, 'Mesa de Cavacas', 0),
(351, 17, 'Ospino', 0),
(352, 17, 'Papelón', 0),
(353, 17, 'Payara', 0),
(354, 17, 'Pimpinela', 0),
(355, 17, 'Píritu de Portuguesa', 0),
(356, 17, 'San Rafael de Onoto', 0),
(357, 17, 'Santa Rosalía', 0),
(358, 17, 'Turén', 0),
(359, 18, 'Altos de Sucre', 0),
(360, 18, 'Araya', 0),
(361, 18, 'Cariaco', 0),
(362, 18, 'Carúpano', 0),
(363, 18, 'Casanay', 0),
(364, 18, 'Cumaná', 1),
(365, 18, 'Cumanacoa', 0),
(366, 18, 'El Morro Puerto Santo', 0),
(367, 18, 'El Pilar', 0),
(368, 18, 'El Poblado', 0),
(369, 18, 'Guaca', 0),
(370, 18, 'Guiria', 0),
(371, 18, 'Irapa', 0),
(372, 18, 'Manicuare', 0),
(373, 18, 'Mariguitar', 0),
(374, 18, 'Río Caribe', 0),
(375, 18, 'San Antonio del Golfo', 0),
(376, 18, 'San José de Aerocuar', 0),
(377, 18, 'San Vicente de Sucre', 0),
(378, 18, 'Santa Fe de Sucre', 0),
(379, 18, 'Tunapuy', 0),
(380, 18, 'Yaguaraparo', 0),
(381, 18, 'Yoco', 0),
(382, 19, 'Abejales', 0),
(383, 19, 'Borota', 0),
(384, 19, 'Bramon', 0),
(385, 19, 'Capacho', 0),
(386, 19, 'Colón', 0),
(387, 19, 'Coloncito', 0),
(388, 19, 'Cordero', 0),
(389, 19, 'El Cobre', 0),
(390, 19, 'El Pinal', 0),
(391, 19, 'Independencia', 0),
(392, 19, 'La Fría', 0),
(393, 19, 'La Grita', 0),
(394, 19, 'La Pedrera', 0),
(395, 19, 'La Tendida', 0),
(396, 19, 'Las Delicias', 0),
(397, 19, 'Las Hernández', 0),
(398, 19, 'Lobatera', 0),
(399, 19, 'Michelena', 0),
(400, 19, 'Palmira', 0),
(401, 19, 'Pregonero', 0),
(402, 19, 'Queniquea', 0),
(403, 19, 'Rubio', 0),
(404, 19, 'San Antonio del Tachira', 0),
(405, 19, 'San Cristobal', 1),
(406, 19, 'San José de Bolívar', 0),
(407, 19, 'San Josecito', 0),
(408, 19, 'San Pedro del Río', 0),
(409, 19, 'Santa Ana Táchira', 0),
(410, 19, 'Seboruco', 0),
(411, 19, 'Táriba', 0),
(412, 19, 'Umuquena', 0),
(413, 19, 'Ureña', 0),
(414, 20, 'Batatal', 0),
(415, 20, 'Betijoque', 0),
(416, 20, 'Boconó', 0),
(417, 20, 'Carache', 0),
(418, 20, 'Chejende', 0),
(419, 20, 'Cuicas', 0),
(420, 20, 'El Dividive', 0),
(421, 20, 'El Jaguito', 0),
(422, 20, 'Escuque', 0),
(423, 20, 'Isnotú', 0),
(424, 20, 'Jajó', 0),
(425, 20, 'La Ceiba', 0),
(426, 20, 'La Concepción de Trujllo', 0),
(427, 20, 'La Mesa de Esnujaque', 0),
(428, 20, 'La Puerta', 0),
(429, 20, 'La Quebrada', 0),
(430, 20, 'Mendoza Fría', 0),
(431, 20, 'Meseta de Chimpire', 0),
(432, 20, 'Monay', 0),
(433, 20, 'Motatán', 0),
(434, 20, 'Pampán', 0),
(435, 20, 'Pampanito', 0),
(436, 20, 'Sabana de Mendoza', 0),
(437, 20, 'San Lázaro', 0),
(438, 20, 'Santa Ana de Trujillo', 0),
(439, 20, 'Tostós', 0),
(440, 20, 'Trujillo', 1),
(441, 20, 'Valera', 0),
(442, 21, 'Carayaca', 0),
(443, 21, 'Litoral', 0),
(444, 25, 'Archipiélago Los Roques', 0),
(445, 22, 'Aroa', 0),
(446, 22, 'Boraure', 0),
(447, 22, 'Campo Elías de Yaracuy', 0),
(448, 22, 'Chivacoa', 0),
(449, 22, 'Cocorote', 0),
(450, 22, 'Farriar', 0),
(451, 22, 'Guama', 0),
(452, 22, 'Marín', 0),
(453, 22, 'Nirgua', 0),
(454, 22, 'Sabana de Parra', 0),
(455, 22, 'Salom', 0),
(456, 22, 'San Felipe', 1),
(457, 22, 'San Pablo de Yaracuy', 0),
(458, 22, 'Urachiche', 0),
(459, 22, 'Yaritagua', 0),
(460, 22, 'Yumare', 0),
(461, 23, 'Bachaquero', 0),
(462, 23, 'Bobures', 0),
(463, 23, 'Cabimas', 0),
(464, 23, 'Campo Concepción', 0),
(465, 23, 'Campo Mara', 0),
(466, 23, 'Campo Rojo', 0),
(467, 23, 'Carrasquero', 0),
(468, 23, 'Casigua', 0),
(469, 23, 'Chiquinquirá', 0),
(470, 23, 'Ciudad Ojeda', 0),
(471, 23, 'El Batey', 0),
(472, 23, 'El Carmelo', 0),
(473, 23, 'El Chivo', 0),
(474, 23, 'El Guayabo', 0),
(475, 23, 'El Mene', 0),
(476, 23, 'El Venado', 0),
(477, 23, 'Encontrados', 0),
(478, 23, 'Gibraltar', 0),
(479, 23, 'Isla de Toas', 0),
(480, 23, 'La Concepción del Zulia', 0),
(481, 23, 'La Paz', 0),
(482, 23, 'La Sierrita', 0),
(483, 23, 'Lagunillas del Zulia', 0),
(484, 23, 'Las Piedras de Perijá', 0),
(485, 23, 'Los Cortijos', 0),
(486, 23, 'Machiques', 0),
(487, 23, 'Maracaibo', 1),
(488, 23, 'Mene Grande', 0),
(489, 23, 'Palmarejo', 0),
(490, 23, 'Paraguaipoa', 0),
(491, 23, 'Potrerito', 0),
(492, 23, 'Pueblo Nuevo del Zulia', 0),
(493, 23, 'Puertos de Altagracia', 0),
(494, 23, 'Punta Gorda', 0),
(495, 23, 'Sabaneta de Palma', 0),
(496, 23, 'San Francisco', 0),
(497, 23, 'San José de Perijá', 0),
(498, 23, 'San Rafael del Moján', 0),
(499, 23, 'San Timoteo', 0),
(500, 23, 'Santa Bárbara Del Zulia', 0),
(501, 23, 'Santa Cruz de Mara', 0),
(502, 23, 'Santa Cruz del Zulia', 0),
(503, 23, 'Santa Rita', 0),
(504, 23, 'Sinamaica', 0),
(505, 23, 'Tamare', 0),
(506, 23, 'Tía Juana', 0),
(507, 23, 'Villa del Rosario', 0),
(508, 21, 'La Guaira', 1),
(509, 21, 'Catia La Mar', 0),
(510, 21, 'Macuto', 0),
(511, 21, 'Naiguatá', 0),
(512, 25, 'Archipiélago Los Monjes', 0),
(513, 25, 'Isla La Tortuga y Cayos adyacentes', 0),
(514, 25, 'Isla La Sola', 0),
(515, 25, 'Islas Los Testigos', 0),
(516, 25, 'Islas Los Frailes', 0),
(517, 25, 'Isla La Orchila', 0),
(518, 25, 'Archipiélago Las Aves', 0),
(519, 25, 'Isla de Aves', 0),
(520, 25, 'Isla La Blanquilla', 0),
(521, 25, 'Isla de Patos', 0),
(522, 25, 'Islas Los Hermanos', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tipo_documento` varchar(1) NOT NULL DEFAULT 'V',
  `documento_numero` varchar(32) NOT NULL,
  `nombre_razon_social` varchar(180) NOT NULL,
  `email` varchar(180) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `id_estado` int(10) UNSIGNED DEFAULT NULL,
  `id_ciudad` int(10) UNSIGNED DEFAULT NULL,
  `id_municipio` int(10) UNSIGNED DEFAULT NULL,
  `id_parroquia` int(10) UNSIGNED DEFAULT NULL,
  `telefono` varchar(11) DEFAULT NULL,
  `zona` varchar(120) DEFAULT NULL,
  `vendedor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `tipo_documento`, `documento_numero`, `nombre_razon_social`, `email`, `direccion`, `id_estado`, `id_ciudad`, `id_municipio`, `id_parroquia`, `telefono`, `zona`, `vendedor_id`, `created_at`, `updated_at`) VALUES
(1, 'V', '28187874', 'Victor', 'victorcarrillox2@gmail.com', 'Aragua', 1, 1, NULL, NULL, '04124578781', 'Sur', 3, '2026-04-16 07:27:09', '2026-04-16 07:27:09'),
(2, 'J', 'DEMO0001', 'Cliente Demo 1', 'cliente1@demo.local', 'Dirección demo 1', 12, 60, 9, 18, '04126340390', 'Centro', 6, '2026-04-16 20:13:31', '2026-04-16 21:56:52'),
(3, 'J', 'DEMO0002', 'Cliente Demo 2', 'cliente2@demo.local', 'Dirección demo 2', 5, 201, 16, 33, '04126837230', 'Araure', 6, '2026-04-16 20:13:31', '2026-04-16 21:56:52'),
(4, 'J', 'DEMO0003', 'Cliente Demo 3', 'cliente3@demo.local', 'Dirección demo 3', 21, 182, 3, 1, '04127739116', 'Araure', 6, '2026-04-16 20:13:31', '2026-04-16 21:56:52'),
(5, 'J', 'DEMO0004', 'Cliente Demo 4', 'cliente4@demo.local', 'Dirección demo 4', 25, 232, 15, 42, '04129103540', 'Acarigua', 6, '2026-04-16 20:13:31', '2026-04-16 21:56:52'),
(6, 'J', 'DEMO0005', 'Cliente Demo 5', 'cliente5@demo.local', 'Dirección demo 5', 17, 277, 2, 32, '04125033347', 'Araure', 5, '2026-04-16 20:13:31', '2026-04-16 21:56:52'),
(7, 'J', 'DEMO0006', 'Cliente Demo 6', 'cliente6@demo.local', 'Dirección demo 6', 10, 4, 3, 43, '04121049631', '', 5, '2026-04-16 20:13:31', '2026-04-16 21:56:52'),
(8, 'J', 'DEMO0007', 'Cliente Demo 7', 'cliente7@demo.local', 'Dirección demo 7', 9, 433, 12, 19, '04125260051', 'Píritu', 5, '2026-04-16 20:13:31', '2026-04-16 21:56:52'),
(9, 'J', 'DEMO0008', 'Cliente Demo 8', 'cliente8@demo.local', 'Dirección demo 8', 25, 205, 6, 49, '04125704451', 'Píritu', 5, '2026-04-16 20:13:31', '2026-04-16 21:56:52'),
(10, 'J', 'DEMO0009', 'Cliente Demo 9', 'cliente9@demo.local', 'Dirección demo 9', 10, 291, 5, 37, '04122066557', 'Biscucuy', 4, '2026-04-16 20:13:31', '2026-04-16 21:56:52'),
(11, 'J', 'DEMO0010', 'Cliente Demo 10', 'cliente10@demo.local', 'Dirección demo 10', 22, 441, 17, 26, '04123932142', NULL, 4, '2026-04-16 20:13:31', '2026-04-16 21:56:52'),
(12, 'J', 'DEMO0011', 'Cliente Demo 11', 'cliente11@demo.local', 'Dirección demo 11', 19, 152, 18, 30, '04125056502', '', 5, '2026-04-16 20:13:31', '2026-04-16 21:56:52'),
(13, 'J', 'DEMO0012', 'Cliente Demo 12', 'cliente12@demo.local', 'Dirección demo 12', 5, 432, 2, 31, '04128013967', 'Centro', 5, '2026-04-16 20:13:31', '2026-04-16 21:56:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados`
--

CREATE TABLE `estados` (
  `id_estado` int(10) UNSIGNED NOT NULL,
  `nombre_estado` varchar(250) NOT NULL,
  `codigo_iso_3166_2` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estados`
--

INSERT INTO `estados` (`id_estado`, `nombre_estado`, `codigo_iso_3166_2`) VALUES
(1, 'Amazonas', 'VE-X'),
(2, 'Anzoátegui', 'VE-B'),
(3, 'Apure', 'VE-C'),
(4, 'Aragua', 'VE-D'),
(5, 'Barinas', 'VE-E'),
(6, 'Bolívar', 'VE-F'),
(7, 'Carabobo', 'VE-G'),
(8, 'Cojedes', 'VE-H'),
(9, 'Delta Amacuro', 'VE-Y'),
(10, 'Falcón', 'VE-I'),
(11, 'Guárico', 'VE-J'),
(12, 'Lara', 'VE-K'),
(13, 'Mérida', 'VE-L'),
(14, 'Miranda', 'VE-M'),
(15, 'Monagas', 'VE-N'),
(16, 'Nueva Esparta', 'VE-O'),
(17, 'Portuguesa', 'VE-P'),
(18, 'Sucre', 'VE-R'),
(19, 'Táchira', 'VE-S'),
(20, 'Trujillo', 'VE-T'),
(21, 'La Guaira', 'VE-W'),
(22, 'Yaracuy', 'VE-U'),
(23, 'Zulia', 'VE-V'),
(24, 'Distrito Capital', 'VE-A'),
(25, 'Dependencias Federales', 'VE-Z');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cliente_id` bigint(20) UNSIGNED NOT NULL,
  `vendedor_id` bigint(20) UNSIGNED DEFAULT NULL,
  `numero_factura` varchar(64) DEFAULT NULL,
  `fecha_emision` date NOT NULL,
  `dias_credito` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
  `metodo_pago_previsto` varchar(30) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_vencimiento` date NOT NULL,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `saldo_pendiente` decimal(15,2) NOT NULL DEFAULT 0.00,
  `estado_pago` varchar(20) NOT NULL DEFAULT 'abierta',
  `creado_por` bigint(20) UNSIGNED NOT NULL,
  `verificado_por` bigint(20) UNSIGNED DEFAULT NULL,
  `fecha_verificacion` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`id`, `cliente_id`, `vendedor_id`, `numero_factura`, `fecha_emision`, `dias_credito`, `metodo_pago_previsto`, `observaciones`, `fecha_vencimiento`, `total`, `saldo_pendiente`, `estado_pago`, `creado_por`, `verificado_por`, `fecha_verificacion`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '1', '2026-04-16', 0, 'panama', 'AAAA', '2026-04-16', 144.00, 0.00, 'pagada', 1, NULL, NULL, '2026-04-16 07:27:39', '2026-04-21 17:14:04'),
(22, 2, 4, '2', '2026-04-16', 0, 'pago_movil', 'DEMO-SEED reportes', '2026-04-16', 1128.13, 1128.13, 'abierta', 1, 7, '2026-04-21 17:02:55', '2026-04-16 21:56:52', '2026-04-21 17:02:55'),
(23, 3, 4, '3', '2026-04-15', 7, 'pago_movil', 'DEMO-SEED reportes', '2026-04-22', 1128.13, 1128.13, 'abierta', 1, 6, '2026-04-15 14:00:00', '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(24, 4, 5, '4', '2026-04-16', 0, 'transferencia', 'DEMO-SEED reportes', '2026-04-16', 1128.13, 0.00, 'pagada', 1, 6, '2026-04-16 14:00:00', '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(25, 5, 5, '5', '2026-04-06', 15, 'transferencia', 'DEMO-SEED reportes', '2026-04-21', 1128.13, 0.00, 'pagada', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(26, 6, 5, '6', '2026-03-27', 30, 'panama', 'DEMO-SEED reportes', '2026-04-26', 1128.13, 676.88, 'abierta', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(27, 7, 4, '7', '2026-03-07', 15, 'efectivo', 'DEMO-SEED reportes', '2026-03-22', 1128.13, 0.00, 'pagada', 1, 6, '2026-03-07 14:00:00', '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(28, 12, 5, '8', '2026-03-08', 30, 'efectivo', NULL, '2026-04-07', 4659.67, 4659.67, 'abierta', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(29, 4, 6, '9', '2026-02-19', 15, 'efectivo', NULL, '2026-03-06', 3842.35, 3842.35, 'abierta', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(30, 4, 6, '10', '2026-03-11', 0, 'pago_movil', NULL, '2026-03-11', 7006.29, 7006.29, 'abierta', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(31, 13, 5, '11', '2026-02-18', 30, 'efectivo', NULL, '2026-03-20', 4354.23, 4354.23, 'abierta', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(32, 13, 5, '12', '2026-03-19', 0, 'panama', 'Demo: caso especial para reportes #5', '2026-03-19', 1500.79, 1500.79, 'abierta', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(33, 2, 6, '13', '2026-03-04', 7, 'panama', NULL, '2026-03-11', 533.93, 533.93, 'abierta', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(34, 7, 5, '14', '2026-04-07', 30, 'zelle', NULL, '2026-05-07', 2989.22, 1404.93, 'abierta', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(35, 10, 4, '15', '2026-02-08', 0, 'usdt', NULL, '2026-02-08', 3874.81, 2906.11, 'abierta', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(36, 8, 5, '16', '2026-04-03', 7, 'efectivo', NULL, '2026-04-10', 289.48, 110.00, 'abierta', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(37, 12, 5, '17', '2026-02-04', 15, 'transferencia', 'Demo: caso especial para reportes #10', '2026-02-19', 549.86, 280.43, 'abierta', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(38, 6, 5, '18', '2026-03-29', 0, 'efectivo', NULL, '2026-03-29', 1323.84, 860.50, 'abierta', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(39, 11, 4, '19', '2026-02-28', 15, 'efectivo', NULL, '2026-03-15', 3964.29, 1625.36, 'abierta', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(40, 3, 6, '20', '2026-02-08', 30, 'usdt', NULL, '2026-03-10', 2528.13, 0.00, 'pagada', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(41, 13, 5, '21', '2026-04-05', 30, 'pago_movil', NULL, '2026-05-05', 2901.17, 0.00, 'pagada', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(42, 7, 5, '22', '2026-03-16', 0, 'efectivo', 'Demo: caso especial para reportes #15', '2026-03-16', 2531.89, 0.00, 'pagada', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(43, 10, 4, '23', '2026-03-08', 15, 'usdt', NULL, '2026-03-23', 2356.32, 0.00, 'pagada', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(44, 9, 4, '24', '2026-04-06', 0, 'transferencia', NULL, '2026-04-06', 3410.87, 0.00, 'pagada', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(45, 2, 6, '25', '2026-03-18', 7, 'efectivo', NULL, '2026-03-25', 3007.02, 0.00, 'pagada', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(46, 3, 6, '26', '2026-04-03', 30, 'pago_movil', NULL, '2026-05-03', 5532.68, 0.00, 'pagada', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(47, 10, 4, '27', '2026-03-14', 7, 'panama', 'Demo: caso especial para reportes #20', '2026-03-21', 5889.79, 0.00, 'pagada', 1, NULL, NULL, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(48, 2, 3, '28', '2026-04-16', 1, 'panama', 'hola', '2026-04-17', 5000.00, 0.00, 'pagada', 7, NULL, NULL, '2026-04-17 02:13:19', '2026-04-17 02:29:02'),
(49, 1, 3, 'A-9920', '2026-04-09', 10, 'efectivo', NULL, '2026-04-19', 545.60, 545.60, 'abierta', 7, NULL, NULL, '2026-04-21 16:44:15', '2026-04-21 16:44:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura_lineas`
--

CREATE TABLE `factura_lineas` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `factura_id` bigint(20) UNSIGNED NOT NULL,
  `categoria_id` bigint(20) UNSIGNED NOT NULL,
  `cantidad_animales` smallint(5) UNSIGNED DEFAULT NULL,
  `cantidad` decimal(12,3) NOT NULL,
  `precio_unitario` decimal(15,4) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `factura_lineas`
--

INSERT INTO `factura_lineas` (`id`, `factura_id`, `categoria_id`, `cantidad_animales`, `cantidad`, `precio_unitario`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 12, 12.000, 12.0000, 144.00, '2026-04-16 07:27:39', '2026-04-16 07:27:39'),
(46, 22, 4, NULL, 100.500, 6.2500, 628.13, '2026-04-16 21:56:52', '2026-04-16 21:56:52'),
(47, 22, 1, 2, 2.000, 250.0000, 500.00, '2026-04-16 21:56:52', '2026-04-16 21:56:52'),
(48, 23, 4, NULL, 100.500, 6.2500, 628.13, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(49, 23, 1, 2, 2.000, 250.0000, 500.00, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(50, 24, 4, NULL, 100.500, 6.2500, 628.13, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(51, 24, 1, 2, 2.000, 250.0000, 500.00, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(52, 25, 4, NULL, 100.500, 6.2500, 628.13, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(53, 25, 1, 2, 2.000, 250.0000, 500.00, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(54, 26, 4, NULL, 100.500, 6.2500, 628.13, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(55, 26, 1, 2, 2.000, 250.0000, 500.00, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(56, 27, 4, NULL, 100.500, 6.2500, 628.13, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(57, 27, 1, 2, 2.000, 250.0000, 500.00, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(58, 28, 2, 2, 2.000, 368.2057, 736.41, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(59, 28, 2, 6, 1.000, 256.4603, 256.46, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(60, 28, 1, 6, 6.000, 611.1338, 3666.80, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(61, 29, 3, 4, 5.000, 299.7030, 1498.52, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(62, 29, 1, 5, 2.000, 222.4778, 444.96, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(63, 29, 2, 2, 3.000, 632.9554, 1898.87, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(64, 30, 4, NULL, 195.832, 8.4479, 1654.37, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(65, 30, 1, 3, 6.000, 556.2597, 3337.56, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(66, 30, 2, 2, 4.000, 503.5910, 2014.36, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(67, 31, 1, 6, 3.000, 302.5652, 907.70, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(68, 31, 2, 4, 6.000, 350.5429, 2103.26, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(69, 31, 4, NULL, 262.142, 5.1242, 1343.27, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(70, 32, 2, 6, 4.000, 375.1972, 1500.79, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(71, 33, 2, 1, 1.000, 254.0894, 254.09, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(72, 33, 1, 2, 1.000, 279.8437, 279.84, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(73, 34, 4, NULL, 139.972, 8.4369, 1180.93, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(74, 34, 2, 5, 5.000, 361.6572, 1808.29, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(75, 35, 4, NULL, 238.079, 11.5098, 2740.24, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(76, 35, 2, 6, 2.000, 567.2830, 1134.57, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(77, 36, 3, 3, 1.000, 289.4802, 289.48, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(78, 37, 1, 3, 2.000, 274.9300, 549.86, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(79, 38, 1, 5, 1.000, 185.6013, 185.60, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(80, 38, 2, 5, 3.000, 379.4146, 1138.24, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(81, 39, 1, 3, 3.000, 637.5409, 1912.62, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(82, 39, 3, 3, 4.000, 512.9170, 2051.67, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(83, 40, 4, NULL, 73.585, 6.7081, 493.62, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(84, 40, 4, NULL, 42.277, 10.5633, 446.58, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(85, 40, 4, NULL, 272.096, 5.8359, 1587.93, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(86, 41, 1, 5, 6.000, 349.7995, 2098.80, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(87, 41, 1, 5, 4.000, 200.5927, 802.37, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(88, 42, 2, 6, 4.000, 632.9722, 2531.89, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(89, 43, 4, NULL, 73.369, 9.7086, 712.31, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(90, 43, 3, 1, 5.000, 328.8022, 1644.01, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(91, 44, 1, 2, 5.000, 279.2684, 1396.34, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(92, 44, 4, NULL, 95.162, 8.8380, 841.04, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(93, 44, 3, 1, 4.000, 293.3728, 1173.49, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(94, 45, 2, 6, 5.000, 601.4049, 3007.02, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(95, 46, 2, 4, 4.000, 509.9025, 2039.61, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(96, 46, 1, 2, 6.000, 582.1785, 3493.07, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(97, 47, 2, 4, 3.000, 287.0088, 861.03, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(98, 47, 3, 1, 6.000, 417.4553, 2504.73, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(99, 47, 2, 4, 4.000, 631.0076, 2524.03, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(100, 48, 2, 2, 500.000, 10.0000, 5000.00, '2026-04-17 02:13:19', '2026-04-17 02:13:19'),
(101, 49, 4, 5, 124.000, 4.4000, 545.60, '2026-04-21 16:44:15', '2026-04-21 16:44:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_04_10_035739_add_role_and_is_active_to_users_table', 1),
(5, '2026_04_10_114258_create_categorias_table', 1),
(6, '2026_04_10_114258_create_productos_table', 1),
(7, '2026_04_10_114259_create_clientes_table', 1),
(8, '2026_04_10_115006_create_facturas_table', 1),
(9, '2026_04_11_120000_create_pagos_table', 1),
(10, '2026_04_12_100000_add_estado_validacion_banco_to_pagos_table', 1),
(11, '2026_04_12_130000_alter_clientes_telefono_varchar11', 1),
(12, '2026_04_13_120000_refactor_clientes_venezuelan_identity', 1),
(13, '2026_04_14_120000_add_cobranza_detalle_fields_to_pagos_table', 1),
(14, '2026_04_14_170000_create_municipios_table', 1),
(15, '2026_04_14_170100_create_parroquias_table', 1),
(16, '2026_04_14_170200_add_ubicacion_and_contacto_to_clientes_table', 1),
(17, '2026_04_14_190000_create_estados_ciudades_and_link_municipios', 1),
(18, '2026_04_14_200000_add_estado_ciudad_to_clientes_table', 1),
(19, '2026_04_14_201000_make_zona_nullable_on_clientes_table', 1),
(20, '2026_04_15_120000_factura_lineas_categoria_drop_productos', 1),
(21, '2026_04_16_120000_add_cantidad_animales_to_factura_lineas', 2),
(22, '2026_04_16_140000_add_vendedor_id_to_facturas', 3),
(23, '2026_04_16_160000_add_metodo_pago_previsto_to_facturas', 4),
(24, '2026_04_16_180000_add_observaciones_to_facturas', 5),
(25, '2026_04_15_120000_migrate_vendedor_role_to_vendedor_normal', 6),
(26, '2026_04_16_160000_create_bancos_table', 7),
(27, '2026_04_21_130000_create_saldos_a_favor_table', 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `municipios`
--

CREATE TABLE `municipios` (
  `id_municipio` int(10) UNSIGNED NOT NULL,
  `id_estado` int(10) UNSIGNED DEFAULT NULL,
  `nombre_municipio` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `municipios`
--

INSERT INTO `municipios` (`id_municipio`, `id_estado`, `nombre_municipio`) VALUES
(1, NULL, 'Atanasio Girardot'),
(2, NULL, 'Bolivar'),
(3, NULL, 'Camatagua'),
(4, NULL, 'Francisco Linares Alcentara'),
(5, NULL, 'Jose Angel Lamas'),
(6, NULL, 'Jose Felix Ribas'),
(7, NULL, 'Jose Rafael Revenga'),
(8, NULL, 'Libertador'),
(9, NULL, 'Mario Briceno Iragorry'),
(10, NULL, 'Ocumare de la Costa de Oro'),
(11, NULL, 'San Casimiro'),
(12, NULL, 'San Sebastien'),
(13, NULL, 'Santiago Marino'),
(14, NULL, 'Santos Michelena'),
(15, NULL, 'Sucre'),
(16, NULL, 'Tovar'),
(17, NULL, 'Urdaneta'),
(18, NULL, 'Zamora');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `factura_id` bigint(20) UNSIGNED NOT NULL,
  `fecha_recibo` date NOT NULL,
  `fecha_publicacion` date DEFAULT NULL,
  `monto_aplicado_usd` decimal(15,2) NOT NULL,
  `tipo_tasa` varchar(20) NOT NULL,
  `valor_tasa` decimal(15,4) NOT NULL,
  `monto_bs` decimal(15,2) DEFAULT NULL,
  `metodo_pago` varchar(30) NOT NULL,
  `estado_validacion_banco` varchar(30) DEFAULT NULL,
  `referencia` varchar(255) DEFAULT NULL,
  `banco_destino` varchar(100) DEFAULT NULL,
  `cuenta_destino` varchar(255) DEFAULT NULL,
  `recibido_por` varchar(255) DEFAULT NULL,
  `comprobante_path` varchar(500) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `registrado_por` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pagos`
--

INSERT INTO `pagos` (`id`, `factura_id`, `fecha_recibo`, `fecha_publicacion`, `monto_aplicado_usd`, `tipo_tasa`, `valor_tasa`, `monto_bs`, `metodo_pago`, `estado_validacion_banco`, `referencia`, `banco_destino`, `cuenta_destino`, `recibido_por`, `comprobante_path`, `notas`, `registrado_por`, `created_at`, `updated_at`) VALUES
(25, 24, '2026-04-27', NULL, 1128.13, 'bcv', 37.6283, 42449.61, 'pago_movil', 'pendiente', 'REF726287', 'BNC', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(26, 25, '2026-04-14', NULL, 1128.13, 'paralelo', 53.6750, 60552.38, 'pago_movil', 'pendiente', 'REF904821', 'Banesco', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(27, 26, '2026-03-28', NULL, 451.25, 'bcv', 1.0000, NULL, 'usdt', NULL, 'REF273519', 'BNC', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(28, 27, '2026-03-17', NULL, 1128.13, 'bcv', 1.0000, NULL, 'transferencia', NULL, 'REF173205', 'Banesco Nelson', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(29, 34, '2026-04-18', NULL, 1584.29, 'paralelo', 1.0000, NULL, 'transferencia', NULL, 'REF391314', 'Banesco Nelson', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(30, 35, '2026-02-16', NULL, 968.70, 'paralelo', 53.4524, 51779.34, 'pago_movil', 'pendiente', 'REF677915', 'Banesco', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(31, 36, '2026-04-05', NULL, 179.48, 'paralelo', 1.0000, NULL, 'transferencia', NULL, 'REF964805', 'Banesco Karina', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(32, 37, '2026-02-10', NULL, 269.43, 'paralelo', 31.8626, 8584.74, 'pago_movil', 'pendiente', 'REF334781', 'Banesco Karina', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(33, 38, '2026-04-06', NULL, 463.34, 'bcv', 45.3772, 21025.07, 'pago_movil', 'pendiente', 'REF260881', 'Banesco', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(34, 39, '2026-03-03', NULL, 2338.93, 'paralelo', 31.1173, 72781.19, 'pago_movil', 'pendiente', 'REF576087', 'Banesco', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(35, 40, '2026-02-10', NULL, 2528.13, 'paralelo', 1.0000, NULL, 'transferencia', NULL, 'REF701984', 'BNC', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(36, 41, '2026-04-17', NULL, 1363.55, 'paralelo', 1.0000, NULL, 'efectivo', NULL, 'REF407310', 'Banesco', NULL, 'Caja demo', NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(37, 41, '2026-04-13', NULL, 845.69, 'paralelo', 1.0000, NULL, 'usdt', NULL, 'REF128238', 'Banesco Nelson', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(38, 41, '2026-04-07', NULL, 691.93, 'bcv', 1.0000, NULL, 'efectivo', NULL, 'REF838395', 'Banesco Karina', NULL, 'Caja demo', NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(39, 42, '2026-03-27', NULL, 1012.76, 'paralelo', 1.0000, NULL, 'efectivo', NULL, 'REF747108', 'Banesco', NULL, 'Caja demo', NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(40, 42, '2026-03-23', NULL, 1519.13, 'paralelo', 1.0000, NULL, 'zelle', NULL, 'REF973058', 'Banesco Nelson', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(41, 43, '2026-03-11', NULL, 1107.47, 'bcv', 1.0000, NULL, 'efectivo', NULL, 'REF307780', 'Banesco', NULL, 'Caja demo', NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(42, 43, '2026-03-16', NULL, 1248.85, 'paralelo', 1.0000, NULL, 'zelle', NULL, 'REF712672', 'Banesco', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(43, 44, '2026-04-15', NULL, 1091.48, 'bcv', 58.6027, 63963.67, 'pago_movil', 'pendiente', 'REF979524', 'Banesco Karina', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(44, 44, '2026-04-17', NULL, 742.20, 'bcv', 1.0000, NULL, 'efectivo', NULL, 'REF395692', 'Banesco', NULL, 'Caja demo', NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(45, 44, '2026-04-13', NULL, 1577.19, 'paralelo', 1.0000, NULL, 'usdt', NULL, 'REF817257', 'Banesco Nelson', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(46, 45, '2026-03-28', NULL, 1744.07, 'bcv', 1.0000, NULL, 'transferencia', NULL, 'REF943009', 'Banesco Karina', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(47, 45, '2026-03-24', NULL, 1262.95, 'paralelo', 1.0000, NULL, 'transferencia', NULL, 'REF846279', 'Banesco Karina', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(48, 46, '2026-04-03', NULL, 1936.44, 'paralelo', 1.0000, NULL, 'transferencia', NULL, 'REF150812', 'Banesco Karina', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(49, 46, '2026-04-05', NULL, 3596.24, 'paralelo', 1.0000, NULL, 'transferencia', NULL, 'REF929345', 'BNC', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(50, 47, '2026-03-22', NULL, 1943.63, 'paralelo', 1.0000, NULL, 'transferencia', NULL, 'REF259050', 'Banesco', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(51, 47, '2026-03-21', NULL, 1775.77, 'bcv', 47.8783, 85020.85, 'pago_movil', 'pendiente', 'REF878287', 'Banesco', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(52, 47, '2026-03-23', NULL, 2170.39, 'bcv', 1.0000, NULL, 'transferencia', NULL, 'REF138800', 'Banesco', NULL, NULL, NULL, 'Demo seed', 1, '2026-04-16 21:56:53', '2026-04-16 21:56:53'),
(53, 48, '2026-04-16', NULL, 5000.00, 'bcv', 1.0000, NULL, 'zelle', NULL, '6575', 'Banesco', NULL, NULL, 'comprobantes/agQTHcfeFWS6GbcJ7BVyzqKrPplADLFtzWEGcYFb.jpg', 'ggg', 7, '2026-04-17 02:29:02', '2026-04-17 02:29:02'),
(54, 1, '2026-04-13', NULL, 144.00, 'paralelo', 650.0000, 93600.00, 'pago_movil', 'pendiente', '3245544', 'Banesco', NULL, NULL, NULL, NULL, 7, '2026-04-21 17:14:04', '2026-04-21 17:14:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `parroquias`
--

CREATE TABLE `parroquias` (
  `id_parroquia` int(10) UNSIGNED NOT NULL,
  `id_municipio` int(10) UNSIGNED NOT NULL,
  `nombre_parroquia` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `parroquias`
--

INSERT INTO `parroquias` (`id_parroquia`, `id_municipio`, `nombre_parroquia`) VALUES
(5, 1, 'Andres Eloy Blanco'),
(8, 1, 'Choroni'),
(2, 1, 'Joaquin Crespo'),
(3, 1, 'Jose Casanova Godoy'),
(7, 1, 'Las Delicias'),
(6, 1, 'Los Tacarigua'),
(4, 1, 'Madre Maria de San Jose'),
(1, 1, 'Pedro Jose Ovalles'),
(9, 2, 'Bolivar'),
(10, 3, 'Camatagua'),
(11, 3, 'Carmen de Cura'),
(13, 4, 'Francisco de Miranda'),
(14, 4, 'Mosenor Feliciano Gonzelez'),
(12, 4, 'Santa Rita'),
(15, 5, 'Santa Cruz'),
(17, 6, 'Castor Nieves Rios'),
(16, 6, 'Jose Felix Ribas'),
(18, 6, 'Las Guacamayas'),
(19, 6, 'Pao de Zerate'),
(20, 6, 'Zuata'),
(21, 7, 'Jose Rafael Revenga'),
(22, 8, 'Palo Negro'),
(23, 8, 'San Martin de Porres'),
(25, 9, 'Cana de Azucar'),
(24, 9, 'El Limon'),
(26, 10, 'Ocumare de la Costa'),
(28, 11, 'Guiripa'),
(29, 11, 'Ollas de Caramacate'),
(27, 11, 'San Casimiro'),
(30, 11, 'Valle Morin'),
(31, 12, 'San Sebastian'),
(36, 13, 'Alfredo Pacheco Miranda'),
(33, 13, 'Arevalo Aponte'),
(34, 13, 'Chuao'),
(35, 13, 'Saman de Guere'),
(32, 13, 'Turmero'),
(37, 14, 'Santos Michelena'),
(38, 14, 'Tiara'),
(40, 15, 'Bella Vista'),
(39, 15, 'Cagua'),
(41, 16, 'Tovar'),
(43, 17, 'Las Penitas'),
(44, 17, 'San Francisco de Cara'),
(45, 17, 'Taguay'),
(42, 17, 'Urdaneta'),
(50, 18, 'Augusto Mijares'),
(47, 18, 'Magdaleno'),
(48, 18, 'San Francisco de Asis'),
(49, 18, 'Valles de Tucutunemo'),
(46, 18, 'Zamora');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `saldos_a_favor`
--

CREATE TABLE `saldos_a_favor` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cliente_id` bigint(20) UNSIGNED NOT NULL,
  `origen_pago_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fecha_recibo` date DEFAULT NULL,
  `monto_usd` decimal(15,2) NOT NULL,
  `saldo_usd` decimal(15,2) NOT NULL,
  `notas` text DEFAULT NULL,
  `registrado_por` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('CQAHBjpJPRP0T5XHX6cjZ04IRI6z35hEacws0dNy', 7, '38.248.129.102', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_7 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.1 Mobile/15E148 Safari/604.1', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiOVoyTEpVN2ROdVZUUGFPWmpHRTlWWkVCY1pkQ3d4bnQ0V2FwVXNiWSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTUyOiJodHRwczovL21ham9yaXR5LXNhdHVyZGF5LXNjb3V0LWludGVyZXN0LnRyeWNsb3VkZmxhcmUuY29tL3JlcG9ydGVzP2NhdGVnb3JpYV9pZD0mZGVzZGU9MjAyNi0wNC0yMSZlc3RhZG9fcGFnbz0mZ2VuZXJhcj0xJmhhc3RhPSZpZF9lc3RhZG89JnZlbmRlZG9yX2lkPSI7czo1OiJyb3V0ZSI7czoxNDoicmVwb3J0ZXMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo3O30=', 1776785573),
('ha3x3nbr43K5EPZBUDyeTMZ1wJBCf5AnTXGNt8td', NULL, '127.0.0.1', 'curl/8.14.1', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSkhDOFlzaW9XbGN1S05wWWxmNUpqd2FmejFBWTVlSm1aS1NkR2d4TyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMDoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2NvYnJhbnphIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jb2JyYW56YSI7czo1OiJyb3V0ZSI7czoxNDoiY29icmFuemEuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1776785530),
('lq3xNfCXdoh02yxYRrCLbiTMBhzhY6QlxwnxVUuU', 7, '186.14.82.33', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoib1RwWVpBV3ZQVm50OXYyMjl6R3NNUXlsamJwbExHNFh0ZllJSFloQSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Njc6Imh0dHBzOi8vbWFqb3JpdHktc2F0dXJkYXktc2NvdXQtaW50ZXJlc3QudHJ5Y2xvdWRmbGFyZS5jb20vY29icmFuemEiO3M6NToicm91dGUiO3M6MTQ6ImNvYnJhbnphLmluZGV4Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6Nzt9', 1776786034),
('ni4V63P0QYujuieH91ojPO3mwucWsYgJteFvgMIv', NULL, '200.8.79.82', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/601.2.4 (KHTML, like Gecko) Version/9.0.1 Safari/601.2.4 facebookexternalhit/1.1 Facebot Twitterbot/1.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiTURRSEJLWVVNSWlkd0thb253TGVYeU8wV2VFSDZpRG9xVlZRb3FPWSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo2ODoiaHR0cHM6Ly9tYWpvcml0eS1zYXR1cmRheS1zY291dC1pbnRlcmVzdC50cnljbG91ZGZsYXJlLmNvbS9kYXNoYm9hcmQiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo2NDoiaHR0cHM6Ly9tYWpvcml0eS1zYXR1cmRheS1zY291dC1pbnRlcmVzdC50cnljbG91ZGZsYXJlLmNvbS9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1776785386),
('u6F86QzTFTdw8qP8CVLkMJGdOgVyK4nCTlEeLn0q', NULL, '200.8.79.82', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiNFVWWmtFc3JaU1IxVWRDQWkxbXFMak1IbVZxZlFBYmVUZGJrQk9TVCI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo2ODoiaHR0cHM6Ly9tYWpvcml0eS1zYXR1cmRheS1zY291dC1pbnRlcmVzdC50cnljbG91ZGZsYXJlLmNvbS9kYXNoYm9hcmQiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo2ODoiaHR0cHM6Ly9tYWpvcml0eS1zYXR1cmRheS1zY291dC1pbnRlcmVzdC50cnljbG91ZGZsYXJlLmNvbS9kYXNoYm9hcmQiO3M6NToicm91dGUiO3M6OToiZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1776785385),
('vLJ3cbMElCOx12UFmujR7u8ezE0nrXqqTN69iWyw', NULL, '2a06:98c0:360b:f5ea:72f1:60cd:b218:c298', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.107 Safari/537.36 (compatible; +https://developers.cloudflare.com/security-center/)', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoid05QOUg3a1ZTdFVSa2I1RklGZ3A2ZjB0VXM3OGcweGFFanlsWFMxbyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1776800707),
('xtFYUgaWYCf433uwagjuvc2MoQjdQ2htNJXPl7WG', NULL, '200.8.79.82', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiT1p2MVVNaFljQlZkeVo1TnhhTWJDdDBhMGo3REllR3lCcEZCNnR1byI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjQ6Imh0dHBzOi8vbWFqb3JpdHktc2F0dXJkYXktc2NvdXQtaW50ZXJlc3QudHJ5Y2xvdWRmbGFyZS5jb20vbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1776785386),
('y6BaCqzMq2IUHjJA18mZQL8e9Lrq330vxQ8OanBQ', 7, '200.8.79.82', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/26.3 Safari/605.1.15', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoidkZWYW4ySmhlMXFLMGkxRGI5UHdwQ3hqNEVSQTY0MG03WXdPdjRSTCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Njg6Imh0dHBzOi8vbWFqb3JpdHktc2F0dXJkYXktc2NvdXQtaW50ZXJlc3QudHJ5Y2xvdWRmbGFyZS5jb20vZGFzaGJvYXJkIjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjc7fQ==', 1776785523),
('zp8DlfESmpkR4V9xWtZUXlE4jv1FE0kJa4zKnBuE', NULL, '127.0.0.1', 'curl/8.14.1', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUTdFdzVlSWV1SFRTNXJRTk5Qb3J4Sk5TWnlBbnNTZzRSWjZYTGtaNSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMDoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2NvYnJhbnphIjt9czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jb2JyYW56YSI7czo1OiJyb3V0ZSI7czoxNDoiY29icmFuemEuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1776786434);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(32) NOT NULL DEFAULT 'vendedor',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Administrador Millennium', 'admin@millennium.local', '2026-04-16 21:56:51', '$2y$12$.dsnE/D5Iq/LvkzMrB18gulD77EWvxmuv7UlF72vMBTk/TqoMQbuG', 'admin', 1, NULL, '2026-04-16 04:58:20', '2026-04-16 21:56:51'),
(2, 'Administrador (demo / video)', 'video@millennium.local', '2026-04-16 21:56:51', '$2y$12$XJwKjIga/aWVvE6tVqlCqetT5jBhlXSWQLrabU.UBBhKf0cqkjSLe', 'admin', 1, NULL, '2026-04-16 04:58:21', '2026-04-16 21:56:51'),
(3, 'Administrador (colaborador)', 'colaborador@millennium.local', '2026-04-16 21:56:51', '$2y$12$NJQnvMYiM9Bubki205ecdeXi4NF3EivDGc8BA3dekR9qAz5SiG55W', 'admin', 1, NULL, '2026-04-16 04:58:21', '2026-04-16 21:56:52'),
(4, 'Vendedor 1 (demo)', 'vendedor1@millennium.local', '2026-04-16 21:56:52', '$2y$12$m8tpRH9PnClPoDLcIxoFxu1pCoKuevh7KO1AF8NbZtIlsMSJbeq6u', 'vendedor_normal', 1, NULL, '2026-04-16 20:13:31', '2026-04-16 21:56:52'),
(5, 'Vendedor 2 (demo)', 'vendedor2@millennium.local', '2026-04-16 21:56:52', '$2y$12$4ZJQoRUeeaYGwKe1i5WbVuP.FrqjoObAED7aSOMIudnvVzCmt0dhq', 'vendedor_normal', 1, NULL, '2026-04-16 20:13:31', '2026-04-16 21:56:52'),
(6, 'Verificador (demo)', 'verificador@millennium.local', '2026-04-16 21:56:52', '$2y$12$V.P1BdV9psIIUIIs602S5uOzVKPyNrsZ2ylXcmJ8J52z3ds2yPDay', 'verificador', 1, NULL, '2026-04-16 20:13:31', '2026-04-16 21:56:52'),
(7, 'Demo — Administrador', 'demo-admin@millennium.local', '2026-04-21 16:14:28', '$2y$12$fJpcTk2WYzMFItohdAc6SONH24nVd3JfOILRFH6mjM/3vO/8pz/lO', 'admin', 1, NULL, '2026-04-16 22:31:22', '2026-04-21 16:14:29'),
(8, 'Demo — Vendedor general', 'demo-vendedor-general@millennium.local', '2026-04-21 16:14:29', '$2y$12$BUSNJiF.OeVa.3A0tS9.g.tOiX0Gb4Gm9IIwEkaU5oqCXDg3J0RBG', 'vendedor_general', 1, NULL, '2026-04-16 22:31:22', '2026-04-21 16:14:30'),
(9, 'Demo — Vendedor', 'demo-vendedor@millennium.local', '2026-04-21 16:14:31', '$2y$12$Xijvb4LUhB1nf2NHohFKfuxuOHE4kaS9cWYOLafG.xVcY6fYudb7G', 'vendedor_normal', 1, NULL, '2026-04-16 22:31:22', '2026-04-21 16:14:32');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `bancos`
--
ALTER TABLE `bancos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bancos_nombre_unique` (`nombre`);

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categorias_codigo_unique` (`codigo`);

--
-- Indices de la tabla `ciudades`
--
ALTER TABLE `ciudades`
  ADD PRIMARY KEY (`id_ciudad`),
  ADD KEY `ciudades_id_estado_nombre_ciudad_index` (`id_estado`,`nombre_ciudad`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `clientes_tipo_documento_numero_unique` (`tipo_documento`,`documento_numero`),
  ADD KEY `clientes_vendedor_id_foreign` (`vendedor_id`),
  ADD KEY `clientes_id_municipio_foreign` (`id_municipio`),
  ADD KEY `clientes_id_parroquia_foreign` (`id_parroquia`),
  ADD KEY `clientes_id_estado_foreign` (`id_estado`),
  ADD KEY `clientes_id_ciudad_foreign` (`id_ciudad`);

--
-- Indices de la tabla `estados`
--
ALTER TABLE `estados`
  ADD PRIMARY KEY (`id_estado`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `facturas_numero_factura_unique` (`numero_factura`),
  ADD KEY `facturas_cliente_id_foreign` (`cliente_id`),
  ADD KEY `facturas_creado_por_foreign` (`creado_por`),
  ADD KEY `facturas_verificado_por_foreign` (`verificado_por`),
  ADD KEY `facturas_vendedor_id_foreign` (`vendedor_id`);

--
-- Indices de la tabla `factura_lineas`
--
ALTER TABLE `factura_lineas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `factura_lineas_factura_id_foreign` (`factura_id`),
  ADD KEY `factura_lineas_categoria_id_foreign` (`categoria_id`);

--
-- Indices de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indices de la tabla `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indices de la tabla `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `municipios`
--
ALTER TABLE `municipios`
  ADD PRIMARY KEY (`id_municipio`),
  ADD KEY `municipios_id_estado_nombre_municipio_index` (`id_estado`,`nombre_municipio`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pagos_factura_id_foreign` (`factura_id`),
  ADD KEY `pagos_registrado_por_foreign` (`registrado_por`);

--
-- Indices de la tabla `parroquias`
--
ALTER TABLE `parroquias`
  ADD PRIMARY KEY (`id_parroquia`),
  ADD KEY `parroquias_id_municipio_nombre_parroquia_index` (`id_municipio`,`nombre_parroquia`);

--
-- Indices de la tabla `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indices de la tabla `saldos_a_favor`
--
ALTER TABLE `saldos_a_favor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `saldos_a_favor_origen_pago_id_foreign` (`origen_pago_id`),
  ADD KEY `saldos_a_favor_registrado_por_foreign` (`registrado_por`),
  ADD KEY `saldos_a_favor_cliente_id_saldo_usd_index` (`cliente_id`,`saldo_usd`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `bancos`
--
ALTER TABLE `bancos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT de la tabla `factura_lineas`
--
ALTER TABLE `factura_lineas`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT de la tabla `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de la tabla `saldos_a_favor`
--
ALTER TABLE `saldos_a_favor`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `ciudades`
--
ALTER TABLE `ciudades`
  ADD CONSTRAINT `ciudades_id_estado_foreign` FOREIGN KEY (`id_estado`) REFERENCES `estados` (`id_estado`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_id_ciudad_foreign` FOREIGN KEY (`id_ciudad`) REFERENCES `ciudades` (`id_ciudad`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `clientes_id_estado_foreign` FOREIGN KEY (`id_estado`) REFERENCES `estados` (`id_estado`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `clientes_id_municipio_foreign` FOREIGN KEY (`id_municipio`) REFERENCES `municipios` (`id_municipio`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `clientes_id_parroquia_foreign` FOREIGN KEY (`id_parroquia`) REFERENCES `parroquias` (`id_parroquia`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `clientes_vendedor_id_foreign` FOREIGN KEY (`vendedor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `facturas_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `facturas_creado_por_foreign` FOREIGN KEY (`creado_por`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `facturas_vendedor_id_foreign` FOREIGN KEY (`vendedor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `facturas_verificado_por_foreign` FOREIGN KEY (`verificado_por`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `factura_lineas`
--
ALTER TABLE `factura_lineas`
  ADD CONSTRAINT `factura_lineas_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `factura_lineas_factura_id_foreign` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `municipios`
--
ALTER TABLE `municipios`
  ADD CONSTRAINT `municipios_id_estado_foreign` FOREIGN KEY (`id_estado`) REFERENCES `estados` (`id_estado`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_factura_id_foreign` FOREIGN KEY (`factura_id`) REFERENCES `facturas` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pagos_registrado_por_foreign` FOREIGN KEY (`registrado_por`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `parroquias`
--
ALTER TABLE `parroquias`
  ADD CONSTRAINT `parroquias_id_municipio_foreign` FOREIGN KEY (`id_municipio`) REFERENCES `municipios` (`id_municipio`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `saldos_a_favor`
--
ALTER TABLE `saldos_a_favor`
  ADD CONSTRAINT `saldos_a_favor_cliente_id_foreign` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `saldos_a_favor_origen_pago_id_foreign` FOREIGN KEY (`origen_pago_id`) REFERENCES `pagos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `saldos_a_favor_registrado_por_foreign` FOREIGN KEY (`registrado_por`) REFERENCES `users` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
