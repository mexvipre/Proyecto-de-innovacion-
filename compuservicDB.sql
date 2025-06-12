-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-06-2025 a las 22:01:21
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `compuservic`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `ActualizarEquipo` (IN `p_iddetequipo` INT, IN `p_modelo` VARCHAR(255), IN `p_numserie` VARCHAR(255), IN `p_descripcionentrada` TEXT)   BEGIN
    DECLARE v_modelo VARCHAR(255);
    DECLARE v_numserie VARCHAR(255);
    DECLARE v_descripcionentrada TEXT;

    SELECT modelo, numserie, descripcionentrada
    INTO v_modelo, v_numserie, v_descripcionentrada
    FROM detequipos
    WHERE iddetequipo = p_iddetequipo;

    SET v_modelo = IF(p_modelo IS NULL OR p_modelo = '', v_modelo, p_modelo);
    SET v_numserie = IF(p_numserie IS NULL OR p_numserie = '', v_numserie, p_numserie);
    SET v_descripcionentrada = IF(p_descripcionentrada IS NULL OR p_descripcionentrada = '', v_descripcionentrada, p_descripcionentrada);

    UPDATE detequipos
    SET modelo = v_modelo,
        numserie = v_numserie,
        descripcionentrada = v_descripcionentrada
    WHERE iddetequipo = p_iddetequipo;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `agregarDistrito` (IN `p_nombre` VARCHAR(100), IN `p_provincia` VARCHAR(100), IN `p_departamento` VARCHAR(100))   BEGIN
    INSERT INTO distritos (nombre, provincia, departamento) 
    VALUES (p_nombre, p_provincia, p_departamento);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `asignar_tecnico_equipo_simple` (IN `p_iddetequipo` INT, IN `p_idusuario_soporte` INT)   BEGIN
    INSERT INTO detalle_servicios (
        iddetequipo,
        idusuario_soporte
    )
    VALUES (
        p_iddetequipo,
        p_idusuario_soporte
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `crear_orden_servicio` (IN `p_fecha` DATETIME, IN `p_idcliente` INT, IN `p_idusuario` INT)   BEGIN
    INSERT INTO orden_de_servicios (fecha_recepcion, idcliente, idusuario_crea)
    VALUES (p_fecha, p_idcliente, p_idusuario);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `eliminar_equipo` (IN `id` INT)   BEGIN
    -- Eliminar los registros relacionados en la tabla servicios
    DELETE FROM servicios WHERE iddetservicio IN (SELECT iddetservicio FROM detalle_servicios WHERE iddetequipo = id);
    
    -- Eliminar los registros relacionados en la tabla evidencia_tecnica
    DELETE FROM evidencia_tecnica 
    WHERE iddetservicio IN (SELECT iddetservicio FROM detalle_servicios WHERE iddetequipo = id);
    
    -- Eliminar los registros relacionados en la tabla detalle_servicios
    DELETE FROM detalle_servicios WHERE iddetequipo = id;
    
    -- Eliminar el registro en la tabla detequipos
    DELETE FROM detequipos WHERE iddetequipo = id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetEquipmentCharacteristics` (IN `p_iddetequipo` INT)   BEGIN
    SELECT 
        esp.especificacion AS caracteristica_nombre,
        car.valor AS caracteristica_valor,
        esp.id_especificacion
    FROM 
        caracteristicas car
    JOIN 
        especificaciones esp ON car.id_especificacion = esp.id_especificacion
    WHERE 
        car.iddetequipo = p_iddetequipo;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetEquipmentDetails` (IN `p_iddetequipo` INT)   BEGIN
    SELECT 
        d.iddetequipo,
        d.modelo,
        d.numserie,
        c.NombreCategoria AS tipo_equipo,
        s.Nombre_SubCategoria AS subcategoria,
        m.Nombre_Marca AS marca
    FROM 
        detequipos d
    LEFT JOIN 
        marcasasoc ma ON d.idmarcasoc = ma.idmarcasoc
    LEFT JOIN 
        subcategoria s ON ma.id_subcategoria = s.id_subcategoria
    LEFT JOIN 
        categorias c ON s.id_categoria = c.id_categoria
    LEFT JOIN 
        marcas m ON ma.id_marca = m.id_marca
    WHERE 
        d.iddetequipo = p_iddetequipo;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `insertar_evidencia_y_actualizar_detequipo` (IN `p_ruta` VARCHAR(255), IN `p_iddetequipo` INT)   BEGIN
    DECLARE nuevo_id_evidencia INT;

    -- Insertar en evidencias_entrada
    INSERT INTO evidencias_entrada (ruta_Evidencia_Entrada)
    VALUES (p_ruta);

    SET nuevo_id_evidencia = LAST_INSERT_ID();

    -- Actualizar la tabla detequipos
    UPDATE detequipos
    SET idEvidencia = nuevo_id_evidencia
    WHERE iddetequipo = p_iddetequipo;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ListarOrdenesServicio` ()   BEGIN
    SELECT 
        os.idorden_Servicio,
        os.fecha_recepcion,
        
        -- Nombre completo del usuario que creó la orden
        CONCAT(up.nombres, ' ', up.Primer_Apellido, ' ', up.Segundo_Apellido) AS usuario_creador,
        
        -- Mostrar nombre del cliente (persona o empresa)
        COALESCE(
            CONCAT(cp.nombres, ' ', cp.Primer_Apellido, ' ', cp.Segundo_Apellido),
            ce.razon_social
        ) AS nombre_cliente,
        
        -- Mostrar DNI o RUC
        COALESCE(cp.numerodoc, ce.ruc) AS documento_cliente,
        
        -- Mostrar teléfono del cliente
        COALESCE(cp.telefono, ce.telefono) AS telefono_cliente

    FROM 
        orden_de_servicios os
    INNER JOIN 
        clientes c ON os.idcliente = c.idcliente
    LEFT JOIN 
        personas cp ON c.idpersona = cp.idpersona
    LEFT JOIN 
        empresas ce ON c.idempresa = ce.idempresa

    -- Unión para obtener el nombre del usuario que creó la orden
    LEFT JOIN 
        usuarios u ON os.idusuario_crea = u.idusuario
    LEFT JOIN 
        contratos con ON u.idcontrato = con.idcontrato
    LEFT JOIN 
        personas up ON con.idpersona = up.idpersona

    ORDER BY os.idorden_Servicio DESC; -- 🔴 AQUI LO ORDENAS DE MAYOR A MENOR
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ListarOrdenPorID` (IN `orden_id` INT)   BEGIN
    SELECT 
        os.idorden_Servicio,
        os.fecha_recepcion,
        CONCAT(up.nombres, ' ', up.Primer_Apellido, ' ', up.Segundo_Apellido) AS usuario_creador,
        COALESCE(
            CONCAT(cp.nombres, ' ', cp.Primer_Apellido, ' ', cp.Segundo_Apellido),
            ce.razon_social
        ) AS nombre_cliente,
        COALESCE(cp.numerodoc, ce.ruc) AS documento_cliente,
        COALESCE(cp.telefono, ce.telefono) AS telefono_cliente
    FROM 
        orden_de_servicios os
    INNER JOIN clientes c ON os.idcliente = c.idcliente
    LEFT JOIN personas cp ON c.idpersona = cp.idpersona
    LEFT JOIN empresas ce ON c.idempresa = ce.idempresa
    LEFT JOIN usuarios u ON os.idusuario_crea = u.idusuario
    LEFT JOIN contratos con ON u.idcontrato = con.idcontrato
    LEFT JOIN personas up ON con.idpersona = up.idpersona
    WHERE os.idorden_Servicio = orden_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `listar_equipos` (IN `tipo_equipo_id` INT)   BEGIN
    SELECT e.id_equipo, e.NomEquipo, e.idTipo_Equipos
    FROM equipos e
    WHERE (tipo_equipo_id IS NULL OR e.idTipo_Equipos = tipo_equipo_id)
    ORDER BY e.NomEquipo;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `listar_equipos_y_tipos` ()   BEGIN
    SELECT e.id_equipo, e.Nom_Equipo, t.NomTipoEquipo
    FROM equipos e
    LEFT JOIN tipo_equipos t ON e.idTipo_Equipos = t.idTipo_Equipos;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `listar_evidencias_por_servicio` (IN `p_iddetservicio` INT)   BEGIN
    -- Selecciona las evidencias asociadas al iddetservicio especificado
    SELECT 
        et.idEvidencia_Tecnica,
        et.imagen_tecnico,
        et.comentarios
    FROM 
        evidencia_tecnica et
    INNER JOIN
        detalle_servicios ds ON et.iddetservicio = ds.iddetservicio
    WHERE 
        ds.iddetservicio = p_iddetservicio;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `listar_tecnicos` ()   BEGIN
    SELECT 
        p.idpersona,
        CONCAT(p.nombres, ' ', p.Primer_Apellido, ' ', p.Segundo_Apellido) AS nombre_completo
    FROM contratos c
    INNER JOIN personas p ON c.idpersona = p.idpersona
    WHERE c.idrol = 3;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `listar_tipo_equipos` ()   BEGIN
    -- Consulta para obtener todos los registros de la tabla tipo_equipos
    SELECT idTipo_Equipos, NomTipoEquipo
    FROM tipo_equipos;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `obtenerClientesV4` ()   BEGIN
    SELECT 
        c.idcliente,
        
        -- Datos de la persona o empresa (cliente)
        IFNULL(p.nombres, e.razon_social) AS cliente_nombre,  
        IFNULL(CONCAT(p.Primer_Apellido, ' ', p.Segundo_Apellido), '') AS persona_apellidos, 
        IFNULL(e.razon_social, '') AS empresa_razon_social,
        p.telefono AS persona_telefono, 
        p.tipodoc AS persona_tipodoc, 
        p.numerodoc AS persona_numerodoc,
        p.direccion AS persona_direccion,
        p.estado AS persona_estado,
        
        -- Datos del distrito (de la persona)
        IFNULL(d.nombre, '') AS persona_distrito_nombre,  
        IFNULL(d.provincia, '') AS persona_distrito_provincia,
        IFNULL(d.departamento, '') AS persona_distrito_departamento,
        
        -- Datos adicionales de la empresa
        e.ruc AS empresa_ruc, 
        e.telefono AS empresa_telefono, 
        e.email AS empresa_email, 
        e.direccion AS empresa_direccion, 
        e.fecha_creacion AS empresa_fecha_creacion, 
        e.fecha_modificacion AS empresa_fecha_modificacion, 
        e.estado AS empresa_estado

    FROM clientes c
    LEFT JOIN personas p ON c.idpersona = p.idpersona
    LEFT JOIN empresas e ON c.idempresa = e.idempresa
    LEFT JOIN distritos d ON p.iddistrito = d.iddistrito
    ORDER BY c.idcliente DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerConteoEstadosGeneral` ()   BEGIN
    SELECT 
        CASE 
            WHEN ds.fechahorainicio IS NULL THEN 'En espera'
            WHEN ds.fechahorainicio IS NOT NULL AND ds.fechahorafin IS NULL THEN 'En proceso'
            WHEN ds.fechahorafin IS NOT NULL THEN 'Finalizado'
            ELSE 'Desconocido'
        END AS estado_equipo,
        COUNT(*) AS cantidad
    FROM detalle_servicios ds 
    JOIN detequipos d ON ds.iddetequipo = d.iddetequipo
    JOIN orden_de_servicios os ON d.idorden_servicio = os.idorden_Servicio 
    JOIN clientes c2 ON os.idcliente = c2.idcliente 
    JOIN personas p ON c2.idpersona = p.idpersona 
    JOIN usuarios u_tecnico ON ds.idusuario_soporte = u_tecnico.idusuario 
    JOIN marcasasoc masoc ON d.idmarcasoc = masoc.idmarcasoc
    JOIN subcategoria s ON masoc.id_subcategoria = s.id_subcategoria
    JOIN categorias c ON s.id_categoria = c.id_categoria
    GROUP BY estado_equipo;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerEstadoPorEquipo` (IN `p_iddetequipo` INT)   BEGIN
    SELECT 
        CONCAT(p.nombres, ' ', p.Primer_Apellido, ' ', p.Segundo_Apellido) AS cliente,
        u_tecnico.namuser AS tecnico,
        d.modelo AS equipo_modelo,
        d.numserie AS numero_serie,
        c.NombreCategoria AS categoria,
        s.Nombre_SubCategoria AS subcategoria,
        ds.observaciones,
        ds.fechahorainicio,
        ds.fechahorafin,
        os.fecha_recepcion,
        CASE 
            WHEN ds.fechahorainicio IS NULL THEN 'En espera'
            WHEN ds.fechahorainicio IS NOT NULL AND ds.fechahorafin IS NULL THEN 'En proceso'
            WHEN ds.fechahorafin IS NOT NULL THEN 'Finalizado'
            ELSE 'Desconocido'
        END AS estado_equipo
    FROM detalle_servicios ds 
    JOIN detequipos d ON ds.iddetequipo = d.iddetequipo
    JOIN orden_de_servicios os ON d.idorden_servicio = os.idorden_Servicio 
    JOIN clientes c2 ON os.idcliente = c2.idcliente 
    JOIN personas p ON c2.idpersona = p.idpersona 
    JOIN usuarios u_tecnico ON ds.idusuario_soporte = u_tecnico.idusuario 
    JOIN marcasasoc masoc ON d.idmarcasoc = masoc.idmarcasoc
    JOIN subcategoria s ON masoc.id_subcategoria = s.id_subcategoria
    JOIN categorias c ON s.id_categoria = c.id_categoria
    WHERE d.iddetequipo = p_iddetequipo;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerOrdenConEquipos` (IN `orden_id` INT)   BEGIN
    SELECT 
        os.idorden_Servicio,
        os.fecha_recepcion,
        CONCAT(up.nombres, ' ', up.Primer_Apellido, ' ', up.Segundo_Apellido) AS usuario_creador,
        COALESCE(
            CONCAT(cp.nombres, ' ', cp.Primer_Apellido, ' ', cp.Segundo_Apellido),
            ce.razon_social
        ) AS nombre_cliente,
        COALESCE(cp.numerodoc, ce.ruc) AS documento_cliente,
        COALESCE(cp.telefono, ce.telefono) AS telefono_cliente,
        COALESCE(cp.direccion, ce.direccion) AS direccion_cliente,

        -- Datos del equipo
        de.iddetequipo,
        de.modelo,
        de.numserie,
        de.condicionentrada,
        de.descripcionentrada,
        de.fechaentrega,
        de.condicionsalida,

        -- Marca, subcategoría y categoría
        m.Nombre_Marca,
        sc.Nombre_SubCategoria,
        cat.NombreCategoria,

        -- Características concatenadas
        GROUP_CONCAT(CONCAT(es.especificacion, ': ', ca.valor) SEPARATOR ', ') AS caracteristicas,

        -- Ruta de la evidencia de entrada
        ee.ruta_Evidencia_Entrada

    FROM 
        orden_de_servicios os
    INNER JOIN clientes c ON os.idcliente = c.idcliente
    LEFT JOIN personas cp ON c.idpersona = cp.idpersona
    LEFT JOIN empresas ce ON c.idempresa = ce.idempresa
    LEFT JOIN usuarios u ON os.idusuario_crea = u.idusuario
    LEFT JOIN contratos con ON u.idcontrato = con.idcontrato
    LEFT JOIN personas up ON con.idpersona = up.idpersona

    INNER JOIN detequipos de ON de.idorden_servicio = os.idorden_Servicio
    LEFT JOIN marcasasoc ma ON de.idmarcasoc = ma.idmarcasoc
    LEFT JOIN marcas m ON ma.id_marca = m.id_marca
    LEFT JOIN subcategoria sc ON ma.id_subcategoria = sc.id_subcategoria
    LEFT JOIN categorias cat ON sc.id_categoria = cat.id_categoria
    LEFT JOIN evidencias_entrada ee ON de.idEvidencia = ee.idEvidencia

    LEFT JOIN caracteristicas ca ON ca.iddetequipo = de.iddetequipo
    LEFT JOIN especificaciones es ON ca.id_especificacion = es.id_especificacion

    WHERE os.idorden_Servicio = orden_id

    GROUP BY de.iddetequipo;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerOrdenServicio` (IN `orden_id` INT)   BEGIN
    -- Obtener detalles de la orden de servicio
    SELECT 
        os.idorden_Servicio,
        os.fecha_recepcion,
        CONCAT(up.nombres, ' ', up.Primer_Apellido, ' ', up.Segundo_Apellido) AS usuario_creador,
        COALESCE(
            CONCAT(cp.nombres, ' ', cp.Primer_Apellido, ' ', cp.Segundo_Apellido),
            ce.razon_social
        ) AS nombre_cliente,
        COALESCE(cp.numerodoc, ce.ruc) AS documento_cliente,
        COALESCE(cp.telefono, ce.telefono) AS telefono_cliente,
        COALESCE(cp.direccion, ce.direccion) AS direccion_cliente  -- Dirección del cliente
    FROM 
        orden_de_servicios os
    INNER JOIN clientes c ON os.idcliente = c.idcliente
    LEFT JOIN personas cp ON c.idpersona = cp.idpersona
    LEFT JOIN empresas ce ON c.idempresa = ce.idempresa
    LEFT JOIN usuarios u ON os.idusuario_crea = u.idusuario
    LEFT JOIN contratos con ON u.idcontrato = con.idcontrato
    LEFT JOIN personas up ON con.idpersona = up.idpersona
    WHERE os.idorden_Servicio = orden_id;

    -- Obtener los equipos asociados a la orden de servicio
    SELECT 
        de.idequipo,
        de.nombre_equipo,
        de.modelo,
        de.serial,
        de.estado
    FROM 
        detequipos de
    WHERE de.idorden_servicio = orden_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ObtenerResumenTareasTecnicos` ()   BEGIN
    SELECT 
        u.idusuario,
        CONCAT(p.nombres, ' ', p.Primer_Apellido) AS nombre_tecnico,
        COUNT(ds.iddetservicio) AS tareas_actuales
    FROM usuarios u
    JOIN contratos ct ON u.idcontrato = ct.idcontrato
    JOIN personas p ON ct.idpersona = p.idpersona
    LEFT JOIN detalle_servicios ds ON ds.idusuario_soporte = u.idusuario
        AND ds.fechahorafin IS NULL -- tareas no finalizadas
        AND (ds.observaciones IS NULL OR TRIM(ds.observaciones) = '')
    GROUP BY u.idusuario, nombre_tecnico
    ORDER BY tareas_actuales DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `obtener_detalles_por_tecnico` (IN `p_idusuario_soporte` INT)   BEGIN
    SELECT 
        ds.iddetservicio,
        ds.iddetequipo,
        ds.idpersona_soporte,
        ds.idservicio,
        ds.observaciones,
        ds.precio_servicio,
        ds.fechahorainicio,
        ds.fechahorafin
    FROM detalle_servicios ds
    WHERE ds.idpersona_soporte = p_idpersona_soporte;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `obtener_especificaciones_por_categoria` (IN `NombreCategoria` VARCHAR(50))   BEGIN
    SELECT * 
    FROM especificaciones
    WHERE id_especificacion IN (
        -- Especificaciones para Laptop o PC
        CASE WHEN NombreCategoria IN ('Laptop', 'PC') THEN 1 ELSE NULL END,
        CASE WHEN NombreCategoria IN ('Laptop', 'PC') THEN 2 ELSE NULL END,
        CASE WHEN NombreCategoria IN ('Laptop', 'PC') THEN 3 ELSE NULL END,
        CASE WHEN NombreCategoria IN ('Laptop', 'PC') THEN 4 ELSE NULL END,
        CASE WHEN NombreCategoria IN ('Laptop', 'PC') THEN 10 ELSE NULL END,
        CASE WHEN NombreCategoria IN ('Laptop', 'PC') THEN 13 ELSE NULL END,
        -- Especificaciones para Impresora
        CASE WHEN NombreCategoria = 'Impresora' THEN 4 ELSE NULL END,
        CASE WHEN NombreCategoria = 'Impresora' THEN 7 ELSE NULL END
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `obtener_servicios_por_persona` (IN `p_idpersona` INT)   SELECT 
    ds.iddetservicio,
    ds.iddetequipo,
    ds.idusuario_soporte,
    ds.observaciones,
    ds.precio_servicio,
    ds.fechahorainicio,
    ds.fechahorafin,
    de.descripcionentrada,
    de.condicionentrada,   -- <--- Agregado aquí
    m.Nombre_Marca,
    s.Nombre_SubCategoria,
    c.NombreCategoria
FROM detalle_servicios ds
JOIN usuarios u ON ds.idusuario_soporte = u.idusuario
JOIN detequipos de ON ds.iddetequipo = de.iddetequipo
JOIN marcasasoc masoc ON de.idmarcasoc = masoc.idmarcasoc
JOIN marcas m ON masoc.id_marca = m.id_marca
JOIN subcategoria s ON masoc.id_subcategoria = s.id_subcategoria
JOIN categorias c ON s.id_categoria = c.id_categoria
WHERE u.idcontrato IN (
    SELECT idcontrato FROM contratos WHERE idpersona = p_idpersona
)
AND (ds.observaciones IS NULL OR TRIM(ds.observaciones) = '')
ORDER BY ds.iddetservicio DESC$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `obtener_subcategorias` (IN `categoria_id` INT)   BEGIN
    SELECT id_subcategoria, Nombre_SubCategoria
    FROM subcategoria
    WHERE id_categoria = categoria_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `RegistrarEquipo` (IN `p_idorden_servicio` INT, IN `p_idcategoria` INT, IN `p_idsubcategoria` INT, IN `p_idmarca` INT, IN `p_modelo` VARCHAR(100), IN `p_numserie` VARCHAR(100), IN `p_descripcionentrada` TEXT, IN `p_condicionentrada` TEXT, IN `p_fecha_entrega` DATETIME)   BEGIN
    DECLARE v_idmarcasoc INT;

    -- Insertar marca-subcategoría en marcasasoc
    INSERT INTO marcasasoc (id_marca, id_subcategoria)
    VALUES (p_idmarca, p_idsubcategoria);

    -- Obtener el ID insertado
    SET v_idmarcasoc = LAST_INSERT_ID();

    -- Insertar el equipo
    INSERT INTO detequipos (
        idorden_servicio, 
        idmarcasoc, 
        modelo, 
        numserie, 
        descripcionentrada, 
        condicionentrada, 
        fechaentrega
    )
    VALUES (
        p_idorden_servicio, 
        v_idmarcasoc, 
        p_modelo, 
        p_numserie, 
        p_descripcionentrada, 
        p_condicionentrada, 
        p_fecha_entrega
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_actualizar_empresa` (IN `p_idempresa` INT, IN `p_ruc` VARCHAR(20), IN `p_razon_social` VARCHAR(255), IN `p_telefono` VARCHAR(20), IN `p_email` VARCHAR(100), IN `p_direccion` TEXT, IN `p_iddistrito` INT, IN `p_modificado_por` INT)   BEGIN
    UPDATE empresas
    SET 
        ruc = p_ruc,
        razon_social = p_razon_social,
        telefono = p_telefono,
        email = p_email,
        direccion = p_direccion,
        iddistrito = p_iddistrito,
        modificado_por = p_modificado_por,
        fecha_modificacion = NOW() 
    WHERE idempresa = p_idempresa;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_agregar_empresa` (IN `p_ruc` VARCHAR(20), IN `p_razon_social` VARCHAR(255), IN `p_telefono` VARCHAR(20), IN `p_email` VARCHAR(100), IN `p_direccion` TEXT, IN `p_usuario_id` INT, IN `p_iddistrito` INT)   BEGIN
    INSERT INTO empresas (
        ruc, 
        razon_social, 
        telefono, 
        email, 
        direccion, 
        fecha_creacion, 
        iddistrito, 
        estado,
        fecha_modificacion,
        creado_por
    ) VALUES (
        p_ruc, 
        p_razon_social, 
        p_telefono, 
        p_email, 
        p_direccion, 
        NOW(),
        p_iddistrito, 
        1,
        NOW(),
        p_usuario_id
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertar_equipo` (IN `p_idorden_servicio` INT, IN `p_id_marca` INT, IN `p_id_subcategoria` INT, IN `p_modelo` VARCHAR(100), IN `p_numserie` VARCHAR(100), IN `p_descripcionentrada` TEXT, IN `p_fechaentrega` DATE, IN `p_idEvidencia` TEXT)   BEGIN
    DECLARE v_idmarcasoc INT;

    -- Buscar idmarcasoc en la tabla marcasasoc
    SELECT idmarcasoc INTO v_idmarcasoc
    FROM marcasasoc
    WHERE id_marca = p_id_marca AND id_subcategoria = p_id_subcategoria
    LIMIT 1;

    -- Insertar en detequipos
    INSERT INTO detequipos (
        idorden_servicio,
        idmarcasoc,
        modelo,
        numserie,
        descripcionentrada,
        fechaentrega,
        idEvidencia
    )
    VALUES (
        p_idorden_servicio,
        v_idmarcasoc,
        p_modelo,
        p_numserie,
        p_descripcionentrada,
        p_fechaentrega,
        p_idEvidencia
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertar_persona` (IN `p_nombres` VARCHAR(255), IN `p_primer_apellido` VARCHAR(255), IN `p_segundo_apellido` VARCHAR(50), IN `p_telefono` VARCHAR(20), IN `p_tipodoc` ENUM('DNI','Pasaporte','Carnet de Extranjería'), IN `p_numerodoc` VARCHAR(50), IN `p_correo` VARCHAR(100), IN `p_direccion` TEXT, IN `p_iddistrito` INT, IN `p_estado` TINYINT(4), IN `p_usuario_id` INT)   BEGIN
    INSERT INTO personas (
        nombres, 
        Primer_Apellido,  -- Cambié a 'Primer_Apellido' según tu estructura
        Segundo_Apellido, -- Cambié a 'Segundo_Apellido' según tu estructura
        telefono, 
        tipodoc, 
        numerodoc, 
        correo, 
        direccion, 
        iddistrito, 
        estado, 
        creado_por
    ) 
    VALUES (
        p_nombres, 
        p_primer_apellido,  -- Asegúrate de que el nombre coincida
        p_segundo_apellido, -- Asegúrate de que el nombre coincida
        p_telefono, 
        p_tipodoc, 
        p_numerodoc, 
        p_correo, 
        p_direccion, 
        p_iddistrito, 
        p_estado, 
        p_usuario_id
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertar_rol` (IN `p_rol` VARCHAR(100), IN `p_descripcion` TEXT)   BEGIN
    INSERT INTO roles (rol, descripcion)
    VALUES (p_rol, p_descripcion);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_ListarOrdenesServicio` ()   BEGIN
    SELECT 
        os.idorden_Servicio AS '#Orden',
        COALESCE(p.numerodoc, e.ruc) AS 'DNI/RUC',
        COALESCE(CONCAT(p.nombres, ' ', p.apellidoP, ' ', p.apellidoM), e.razonsocial) AS 'nombre_cliente',
        COALESCE(p.telefono, e.telefono) AS 'telefono',
        COALESCE(p.direccion, e.direccion) AS 'direccion',
        os.modelo,
        os.serie,
        os.descripcion AS 'estado_equipo',
        os.condicionentrada AS 'diagnostico',
        os.idequipo,
        st.dateCreated AS 'fecha_creacion'
    FROM orden_de_servicios os
    JOIN serviciotecnico st ON os.idservicio = st.idservicio
    JOIN clientes c ON st.idcliente = c.idcliente
    LEFT JOIN personas p ON c.idpersona = p.idpersona
    LEFT JOIN empresas e ON c.idempresa = e.idempresa;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_mostrar_distritos` ()   BEGIN
    SELECT iddistrito, nombre, provincia, departamento 
    FROM distritos;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_mostrar_empresas` ()   BEGIN
    SELECT 
        e.idempresa,
        e.ruc,
        e.razon_social,
        e.telefono,
        e.email,
        e.direccion,
        e.fecha_creacion,
        e.fecha_modificacion,
        u.namuser AS usuario_creador,
        u2.namuser AS usuario_modificador,
        d.nombre AS distrito,
        e.estado
    FROM empresas e
    LEFT JOIN usuarios u ON e.creado_por = u.idusuario
    LEFT JOIN usuarios u2 ON e.modificado_por = u2.idusuario
    LEFT JOIN distritos d ON e.iddistrito = d.iddistrito; -- Claves correctas

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_mostrar_personas` ()   BEGIN
    SELECT 
        p.idpersona,
        p.nombres,
        p.Primer_Apellido,  -- Mostramos el primer apellido
        p.Segundo_Apellido, -- Mostramos el segundo apellido
        p.telefono,
        p.tipodoc,
        p.numerodoc,
        p.direccion,
        d.nombre AS nombre_distrito,
        p.estado -- Cambio 'status' por 'estado' según la estructura actual
    FROM 
        personas p
    LEFT JOIN 
        distritos d ON p.iddistrito = d.iddistrito;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_obtener_personas_con_distritos` ()   BEGIN
    SELECT 
        p.idpersona, 
        p.nombres, 
        p.Primer_Apellido, 
        p.Segundo_Apellido, 
        p.telefono, 
        p.tipodoc, 
        p.numerodoc, 
        p.correo, 
        p.direccion, 
        p.estado, 
        d.nombre AS distrito
    FROM personas p
    LEFT JOIN distritos d ON p.iddistrito = d.iddistrito
    ORDER BY p.Primer_Apellido ASC;  -- Ordenar alfabéticamente por el primer apellido
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `v` (IN `orden_id` INT)   BEGIN
    -- Obtener detalles de la orden de servicio
    SELECT 
        os.idorden_Servicio,
        os.fecha_recepcion,
        CONCAT(up.nombres, ' ', up.Primer_Apellido, ' ', up.Segundo_Apellido) AS usuario_creador,
        COALESCE(
            CONCAT(cp.nombres, ' ', cp.Primer_Apellido, ' ', cp.Segundo_Apellido),
            ce.razon_social
        ) AS nombre_cliente,
        COALESCE(cp.numerodoc, ce.ruc) AS documento_cliente,
        COALESCE(cp.telefono, ce.telefono) AS telefono_cliente,
        COALESCE(cp.direccion, ce.direccion) AS direccion_cliente
    FROM 
        orden_de_servicios os
    INNER JOIN clientes c ON os.idcliente = c.idcliente
    LEFT JOIN personas cp ON c.idpersona = cp.idpersona
    LEFT JOIN empresas ce ON c.idempresa = ce.idempresa
    LEFT JOIN usuarios u ON os.idusuario_crea = u.idusuario
    LEFT JOIN contratos con ON u.idcontrato = con.idcontrato
    LEFT JOIN personas up ON con.idpersona = up.idpersona
    WHERE os.idorden_Servicio = orden_id;

    -- Obtener todos los equipos asociados a esa orden
    SELECT 
        de.iddetequipo,
        de.idmarcasoc,
        de.modelo,
        de.numserie,
        de.condicionentrada,
        de.descripcionentrada,
        de.fechaentrega,
        de.condicionsalida
    FROM 
        detequipos de
    WHERE 
        de.idorden_servicio = orden_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `VerEquipoConFKPorIdEquipo` (IN `detequipo_id` INT)   BEGIN
    SELECT 
        d.iddetequipo,
        d.modelo,
        d.numserie,
        d.condicionentrada,
        d.descripcionentrada,
        d.fechaentrega,
        d.condicionsalida,
        
        e.ruta_Evidencia_Entrada,
        e.fecha_creacion AS evidencia_fecha_creacion,

        -- Características (combinando especificaciones y valores)
        GROUP_CONCAT(DISTINCT CONCAT(esp.especificacion, ': ', c.valor) SEPARATOR ' | ') AS caracteristicas,

        -- Servicios separados en columnas individuales
        GROUP_CONCAT(DISTINCT s.nombre_servicio SEPARATOR ' | ') AS nombres_servicios,
        GROUP_CONCAT(DISTINCT s.precio_sugerido SEPARATOR ' | ') AS precios_sugeridos,

        -- Evidencia técnica (separada en columnas)
        GROUP_CONCAT(DISTINCT et.imagen_tecnico SEPARATOR ' | ') AS imagenes_tecnicas,
        GROUP_CONCAT(DISTINCT et.comentarios SEPARATOR ' | ') AS comentarios_tecnicos,

        -- Diagnóstico (separado en columnas)
        GROUP_CONCAT(DISTINCT ds.observaciones SEPARATOR ' | ') AS observaciones_diagnostico,
        GROUP_CONCAT(DISTINCT ds.precio_servicio SEPARATOR ' | ') AS precios_servicio,
        GROUP_CONCAT(DISTINCT ds.fechahorainicio SEPARATOR ' | ') AS fechas_inicio,
        GROUP_CONCAT(DISTINCT ds.fechahorafin SEPARATOR ' | ') AS fechas_fin,
        GROUP_CONCAT(DISTINCT u.namuser SEPARATOR ' | ') AS tecnicos_soporte,

        o.fecha_recepcion AS orden_servicio_fecha,
        m.Nombre_Marca AS marca_nombre,
        sc.Nombre_SubCategoria AS subcategoria_nombre,
        cat.NombreCategoria AS categoria_nombre,
        COALESCE(p.nombres, es.razon_social) AS cliente_nombre,
        COALESCE(p.Primer_Apellido, '') AS cliente_primer_apellido,
        COALESCE(p.Segundo_Apellido, '') AS cliente_segundo_apellido,
        COALESCE(p.telefono, es.telefono) AS cliente_telefono,
        COALESCE(p.tipodoc, '') AS cliente_tipodoc,
        COALESCE(p.numerodoc, es.ruc) AS cliente_documento,
        COALESCE(p.correo, es.email) AS cliente_correo,
        COALESCE(p.direccion, es.direccion) AS cliente_direccion

    FROM 
        detequipos d
    LEFT JOIN 
        evidencias_entrada e ON d.idEvidencia = e.idEvidencia
    LEFT JOIN 
        detalle_servicios ds ON d.iddetequipo = ds.iddetequipo
    LEFT JOIN 
        servicios s ON ds.iddetservicio = s.iddetservicio
    LEFT JOIN 
        evidencia_tecnica et ON ds.iddetservicio = et.iddetservicio
    LEFT JOIN 
        usuarios u ON ds.idusuario_soporte = u.idusuario
    LEFT JOIN 
        orden_de_servicios o ON d.idorden_servicio = o.idorden_Servicio
    LEFT JOIN 
        clientes cl ON o.idcliente = cl.idcliente
    LEFT JOIN 
        personas p ON cl.idpersona = p.idpersona
    LEFT JOIN 
        empresas es ON cl.idempresa = es.idempresa
    LEFT JOIN 
        marcasasoc ma ON d.idmarcasoc = ma.idmarcasoc
    LEFT JOIN 
        marcas m ON ma.id_marca = m.id_marca
    LEFT JOIN 
        subcategoria sc ON ma.id_subcategoria = sc.id_subcategoria
    LEFT JOIN 
        categorias cat ON sc.id_categoria = cat.id_categoria
    LEFT JOIN 
        caracteristicas c ON d.iddetequipo = c.iddetequipo
    LEFT JOIN 
        especificaciones esp ON c.id_especificacion = esp.id_especificacion

    WHERE
        d.iddetequipo = detequipo_id

    GROUP BY
        d.iddetequipo;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `VerEquipos` ()   BEGIN
    SELECT 
        iddetequipo,
        idmarcasoc,
        modelo,
        numserie,
        condicionentrada,
        descripcionentrada,
        fechaentrega,
        condicionsalida,
        idEvidencia,
        id_caracteristica,
        idorden_servicio
    FROM detequipos;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `VerEquiposConFK` ()   BEGIN
    SELECT 
        d.iddetequipo, 
        d.modelo, 
        d.numserie, 
        d.condicionentrada, 
        d.descripcionentrada, 
        d.fechaentrega, 
        d.condicionsalida,
        e.ruta_Evidencia_Entrada,
        e.fecha_creacion AS evidencia_fecha_creacion,
        c.valor AS caracteristica_valor,
        o.fecha_recepcion AS orden_servicio_fecha,

        -- Campos unificados para cliente
        CASE 
            WHEN cl.idpersona IS NOT NULL THEN CONCAT(p.nombres, ' ', p.Primer_Apellido, ' ', p.Segundo_Apellido)
            ELSE es.razon_social
        END AS cliente_nombre,
        CASE 
            WHEN cl.idpersona IS NOT NULL THEN p.numerodoc
            ELSE es.ruc
        END AS cliente_documento,
        CASE 
            WHEN cl.idpersona IS NOT NULL THEN p.telefono
            ELSE es.telefono
        END AS cliente_telefono,
        CASE 
            WHEN cl.idpersona IS NOT NULL THEN p.direccion
            ELSE es.direccion
        END AS cliente_direccion,
        CASE 
            WHEN cl.idpersona IS NOT NULL THEN p.correo
            ELSE es.email
        END AS cliente_correo,

        -- Datos de clasificación del equipo
        m.Nombre_Marca AS marca_nombre,
        s.Nombre_SubCategoria AS subcategoria_nombre,
        cat.NombreCategoria AS categoria_nombre,
        sp.especificacion AS especificacion_nombre

    FROM 
        detequipos d
    LEFT JOIN 
        evidencias_entrada e ON d.idEvidencia = e.idEvidencia
    LEFT JOIN 
        caracteristicas c ON d.iddetequipo = c.iddetequipo
    LEFT JOIN 
        especificaciones sp ON c.id_especificacion = sp.id_especificacion
    LEFT JOIN 
        orden_de_servicios o ON d.idorden_servicio = o.idorden_Servicio
    LEFT JOIN 
        clientes cl ON o.idcliente = cl.idcliente
    LEFT JOIN 
        personas p ON cl.idpersona = p.idpersona
    LEFT JOIN 
        empresas es ON cl.idempresa = es.idempresa
    LEFT JOIN 
        marcasasoc ma ON d.idmarcasoc = ma.idmarcasoc
    LEFT JOIN 
        marcas m ON ma.id_marca = m.id_marca
    LEFT JOIN 
        subcategoria s ON ma.id_subcategoria = s.id_subcategoria
    LEFT JOIN 
        categorias cat ON s.id_categoria = cat.id_categoria;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `VerEquiposConFKPorOrden` (IN `orden_id` INT)   BEGIN
    SELECT 
        d.iddetequipo, 
        d.modelo, 
        d.numserie, 
        d.condicionentrada, 
        d.descripcionentrada, 
        d.fechaentrega, 
        d.condicionsalida,
        
        e.ruta_Evidencia_Entrada,
        e.fecha_creacion AS evidencia_fecha_creacion,

        -- CaracterÃ­sticas agrupadas
        GROUP_CONCAT(DISTINCT CONCAT(sp.especificacion, ': ', c.valor) SEPARATOR ', ') AS caracteristicas,

        -- Servicios realizados agrupados
        GROUP_CONCAT(DISTINCT CONCAT(sv.nombre_servicio, ' (S/ ', sv.precio_sugerido, ')') SEPARATOR ', ') AS servicios_realizados,

        o.fecha_recepcion AS orden_servicio_fecha,

        m.Nombre_Marca AS marca_nombre,
        s.Nombre_SubCategoria AS subcategoria_nombre,
        cat.NombreCategoria AS categoria_nombre,

        COALESCE(p.nombres, es.razon_social) AS cliente_nombre,
        COALESCE(p.Primer_Apellido, '') AS cliente_primer_apellido,
        COALESCE(p.Segundo_Apellido, '') AS cliente_segundo_apellido,
        COALESCE(p.telefono, es.telefono) AS cliente_telefono,
        COALESCE(p.tipodoc, '') AS cliente_tipodoc,
        COALESCE(p.numerodoc, es.ruc) AS cliente_documento,
        COALESCE(p.correo, es.email) AS cliente_correo,
        COALESCE(p.direccion, es.direccion) AS cliente_direccion

    FROM 
        detequipos d
    LEFT JOIN 
        evidencias_entrada e ON d.idEvidencia = e.idEvidencia
    LEFT JOIN 
        caracteristicas c ON d.iddetequipo = c.iddetequipo
    LEFT JOIN 
        especificaciones sp ON c.id_especificacion = sp.id_especificacion
    LEFT JOIN 
        detalle_servicios ds ON d.iddetequipo = ds.iddetequipo
    LEFT JOIN 
        servicios sv ON sv.iddetservicio = ds.iddetservicio
    LEFT JOIN 
        orden_de_servicios o ON d.idorden_servicio = o.idorden_Servicio
    LEFT JOIN 
        clientes cl ON o.idcliente = cl.idcliente
    LEFT JOIN 
        personas p ON cl.idpersona = p.idpersona
    LEFT JOIN 
        empresas es ON cl.idempresa = es.idempresa
    LEFT JOIN 
        marcasasoc ma ON d.idmarcasoc = ma.idmarcasoc
    LEFT JOIN 
        marcas m ON ma.id_marca = m.id_marca
    LEFT JOIN 
        subcategoria s ON ma.id_subcategoria = s.id_subcategoria
    LEFT JOIN 
        categorias cat ON s.id_categoria = cat.id_categoria

    WHERE
        o.idorden_Servicio = orden_id

    GROUP BY
        d.iddetequipo;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caracteristicas`
--

CREATE TABLE `caracteristicas` (
  `id_caracteristica` int(11) NOT NULL,
  `id_especificacion` int(11) NOT NULL,
  `valor` varchar(200) NOT NULL,
  `iddetequipo` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `caracteristicas`
--

INSERT INTO `caracteristicas` (`id_caracteristica`, `id_especificacion`, `valor`, `iddetequipo`) VALUES
(47, 3, 'Intel Core i5', 125),
(48, 2, '8 GB', 125),
(49, 1, '256 GB SSD', 125),
(50, 10, 'Windows 10', 125),
(51, 13, '14\"', 125),
(52, 2, '500GB', 161),
(53, 3, 'core i5', 161),
(54, 3, 'Corei5 12gen', 166),
(55, 2, '800GB', 166),
(56, 10, 'windown 10', 166),
(57, 4, 'negro', 166),
(58, 4, 'BLANCO', 167),
(59, 7, '110V', 167),
(60, 1, '4 GB', 166),
(61, 1, '4 GB', 161),
(62, 1, '4gb', 170),
(63, 3, 'corei 5 6ta gnr 1570i', 170),
(64, 1, '4gb', 169),
(65, 2, '500gb', 169),
(66, 3, 'corei 5 6ta gnr 564654', 169),
(67, 1, '4GB', 172),
(68, 2, '500GB', 172),
(69, 4, 'NEGRO', 174),
(70, 3, 'Corei DUO 2', 176),
(71, 1, '2 GB', 176);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `NombreCategoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `NombreCategoria`) VALUES
(1, 'PC'),
(2, 'Laptop'),
(3, 'Impresora');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `idcliente` int(11) NOT NULL,
  `idpersona` int(11) DEFAULT NULL,
  `idempresa` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`idcliente`, `idpersona`, `idempresa`) VALUES
(1, 1, NULL),
(2, 2, NULL),
(3, 3, NULL),
(11, NULL, 1),
(27, NULL, 5),
(28, 33, NULL),
(29, 34, NULL),
(30, 35, NULL),
(32, 38, NULL),
(35, 42, NULL),
(36, 43, NULL),
(37, 44, NULL),
(38, 45, NULL),
(40, 47, NULL),
(43, 49, NULL),
(45, 52, NULL),
(47, 59, NULL),
(48, 61, NULL),
(53, 91, NULL),
(54, 93, NULL),
(55, 160, NULL),
(56, 214, NULL),
(57, NULL, 7),
(58, 216, NULL),
(59, 220, NULL),
(60, NULL, 9),
(61, NULL, 10),
(62, NULL, 11),
(63, NULL, 12),
(64, NULL, 13),
(65, NULL, 14),
(66, NULL, 15),
(67, NULL, 16),
(68, NULL, 17),
(69, NULL, 18),
(70, NULL, 19),
(71, NULL, 20),
(72, NULL, 21),
(73, NULL, 22),
(74, NULL, 23),
(75, NULL, 24),
(76, NULL, 25),
(80, 271, NULL),
(81, 273, NULL),
(82, 274, NULL),
(83, 276, NULL),
(84, 277, NULL),
(85, 278, NULL),
(86, 279, NULL),
(87, 280, NULL),
(88, NULL, 26),
(89, NULL, 27),
(90, 281, NULL),
(91, NULL, 28),
(92, 282, NULL),
(93, 283, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contratos`
--

CREATE TABLE `contratos` (
  `idcontrato` int(11) NOT NULL,
  `idpersona` int(11) DEFAULT NULL,
  `idrol` int(11) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `contratos`
--

INSERT INTO `contratos` (`idcontrato`, `idpersona`, `idrol`, `fecha_inicio`, `fecha_fin`, `observaciones`, `fecha_creacion`) VALUES
(1, 1, 1, '2024-01-01', NULL, 'Super Administrador con acceso total al sistema.', NULL),
(2, 2, 2, '2024-02-01', '2025-02-01', 'Administrador encargado de la gestión de usuarios y configuraciones.', NULL),
(3, 3, 3, '2024-03-01', '2025-03-01', 'Técnico especializado en mantenimiento de equipos y soporte.', NULL),
(4, 4, 4, '2024-04-01', '2025-04-01', 'Encargado de la admisión y registro de clientes.', NULL),
(8, 49, 3, NULL, NULL, 'Contrato de ejemplo', '2025-04-28 15:15:23'),
(9, 34, 4, '2025-05-15', '2025-05-16', 'practicante', '2025-05-15 20:02:10'),
(17, 42, 2, '2025-05-22', '2025-05-30', 'practicante', '2025-05-22 15:11:44'),
(18, 4, 3, '2025-05-29', '2025-05-31', 'practicante', '2025-05-28 15:34:52'),
(19, 278, 3, '2025-05-29', '2025-05-31', 'practicante ', '2025-05-28 15:54:19'),
(20, 279, 3, '2025-05-29', '2025-05-31', 'especialista en reparación de pc', '2025-05-29 16:29:24'),
(21, 282, 3, '2025-06-11', '2025-12-17', 'ESPECIALISTA EN IMPRESORAS', '2025-06-11 14:46:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_servicios`
--

CREATE TABLE `detalle_servicios` (
  `iddetservicio` int(11) NOT NULL,
  `iddetequipo` int(11) DEFAULT NULL,
  `idusuario_soporte` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `precio_servicio` decimal(10,2) DEFAULT NULL,
  `fechahorainicio` datetime DEFAULT NULL,
  `fechahorafin` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_servicios`
--

INSERT INTO `detalle_servicios` (`iddetservicio`, `iddetequipo`, `idusuario_soporte`, `observaciones`, `precio_servicio`, `fechahorainicio`, `fechahorafin`) VALUES
(4, 123, 3, NULL, NULL, '2025-05-29 16:06:08', NULL),
(5, 125, 5, NULL, NULL, '2025-05-29 15:52:27', NULL),
(6, 127, 3, NULL, NULL, NULL, NULL),
(7, 128, 3, NULL, NULL, NULL, NULL),
(8, 130, 5, NULL, NULL, '2025-05-29 15:53:08', NULL),
(9, 131, 5, NULL, NULL, '2025-05-29 15:56:47', NULL),
(10, 133, 5, NULL, NULL, '2025-05-29 15:59:20', NULL),
(11, 134, 5, NULL, NULL, '2025-05-22 19:55:29', NULL),
(12, 81, 3, NULL, NULL, NULL, NULL),
(13, 82, 5, NULL, NULL, '2025-05-29 15:59:41', NULL),
(14, 137, 5, NULL, NULL, '2025-05-29 16:01:56', NULL),
(15, 135, 3, NULL, NULL, '2025-05-29 16:16:56', NULL),
(16, 143, 3, NULL, NULL, NULL, NULL),
(17, 145, 5, 'equipo reparado correctamente', NULL, '2025-05-29 16:03:00', '2025-05-29 18:27:03'),
(18, 159, 5, NULL, NULL, '2025-05-14 19:24:23', '2025-05-29 19:32:55'),
(19, 160, 3, NULL, NULL, NULL, NULL),
(20, 161, 3, NULL, NULL, NULL, NULL),
(21, 166, 3, NULL, NULL, NULL, NULL),
(22, 167, 3, 'reparación exitosa', NULL, '2025-05-29 16:04:07', '2025-06-02 15:21:20'),
(23, 168, 3, NULL, NULL, '2025-05-29 16:03:48', NULL),
(24, 169, 19, NULL, NULL, '2025-06-06 11:11:55', NULL),
(25, 170, 19, NULL, NULL, '2025-05-29 16:45:45', NULL),
(26, 171, 19, 'Se deja el equipo operativo', NULL, '2025-05-29 16:33:10', '2025-05-29 17:21:41'),
(27, 172, 19, 'laptop queda operativo para el uso', NULL, '2025-05-29 21:13:41', '2025-05-29 21:14:57'),
(28, 173, 5, NULL, NULL, NULL, NULL),
(29, 174, 19, 'se realizo el cambio de algunos componentes', NULL, '2025-06-06 11:04:45', '2025-06-06 11:17:51'),
(30, 175, 5, NULL, NULL, NULL, NULL),
(31, 176, 20, 'QUEDA OPERATIVO EL EQUIPO', NULL, '2025-06-11 15:20:55', '2025-06-11 17:11:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detequipos`
--

CREATE TABLE `detequipos` (
  `iddetequipo` int(11) NOT NULL,
  `idmarcasoc` int(11) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `numserie` varchar(100) DEFAULT NULL,
  `condicionentrada` text DEFAULT NULL,
  `descripcionentrada` text DEFAULT NULL,
  `fechaentrega` datetime DEFAULT current_timestamp(),
  `condicionsalida` text DEFAULT NULL,
  `idEvidencia` int(11) DEFAULT NULL,
  `idorden_servicio` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detequipos`
--

INSERT INTO `detequipos` (`iddetequipo`, `idmarcasoc`, `modelo`, `numserie`, `condicionentrada`, `descripcionentrada`, `fechaentrega`, `condicionsalida`, `idEvidencia`, `idorden_servicio`) VALUES
(81, 117, 'S/N', 'S/N', 'mantenimiento', 'la laptop aveces los botones escriben y no se ve muy bien la pantalla ', '2025-04-12 18:37:00', 'en proceso', 11, 72),
(82, 118, 'adrthjk', 'asdfghj', 'sdfghj', 'asdfgh', '2025-04-11 23:06:00', 'en proceso', 12, 72),
(83, 119, 'GX85322', 'X5646547', 'latop  no enciende ', 'la laptop tiene un lagunas en la pantalla ', '2025-04-15 08:51:00', 'en proceso', 13, 73),
(84, 133, 'L520', 'xasdfgfasfhg72', 'no imprime', 'trae la tapa rota ', '2025-04-14 11:05:00', 'en proceso', 31, 80),
(123, 172, 'adssad', 'asdasd', NULL, 'NO ENCIENDE', '2025-04-25 00:00:00', NULL, 63, 71),
(125, 174, 'ktinker', 'XF786125456', NULL, 'NO ENCIENDE', '2025-05-07 00:00:00', NULL, 73, 71),
(126, 175, 'ktinker', 'XF786125456', NULL, 'NO ENCIENDE', '2025-04-30 00:00:00', NULL, 74, 71),
(127, 176, 'ktinker', 'XF786125456', NULL, 'NO ENCIENDE', '2025-04-23 00:00:00', NULL, 77, 71),
(128, 177, 'ktinker', 'XF786125456', NULL, 'NO ENCIENDE', '2025-04-29 00:00:00', NULL, 110, 71),
(129, 178, 'ktinker', 'XF786125456', NULL, 'NO ENCIENDE', '2025-04-30 00:00:00', NULL, 115, 71),
(130, 179, 'G30', 'XY608287', NULL, 'NO ENCIENDE', '2025-05-02 00:00:00', NULL, 116, 71),
(131, 180, 'Gk800', '8asdas65d7468512', NULL, 'NO ENCIENDE', '2025-05-02 00:00:00', NULL, 118, 93),
(133, 182, 'L500', 'XF78612ASD56', NULL, 'ATASCO DE PAPEL', '2025-05-05 00:00:00', NULL, 119, 91),
(134, 183, 'Gs50x', 'XF78asdasdASD56', NULL, 'SE APAGA POR MOMENTO LUEGO DE 30 MINUTOS', '2025-05-06 00:00:00', NULL, NULL, 91),
(135, 184, 'GAMING 20', 'xj8956412x', NULL, 'ENCIENDE Y LUEGO DE \r\n30 MINUTOS SE APAGA', '2025-05-14 00:00:00', NULL, 120, 95),
(136, 185, 'GAMING20', 'xj8956412x', NULL, 'ENCIENDE Y LUEGO DE \r\n30 MINUTOS SE APAGA', '2025-05-14 00:00:00', NULL, NULL, 95),
(137, 186, 'GAMING20', 'xj8956412x', NULL, 'ENCIENDE Y LUEGO DE \r\n30 MINUTOS SE APAGA', '2025-05-14 00:00:00', NULL, NULL, 95),
(138, 187, 'G20', 'XAS4564', NULL, 'MANTENIMIENTO', '2025-05-14 00:00:00', NULL, NULL, 95),
(139, 188, 'SDSAD', '13268451', NULL, 'SAD', '2025-05-21 00:00:00', NULL, NULL, 95),
(140, 189, '894651', 'X846312', NULL, 'NO ENCIENDE', '2025-05-14 00:00:00', NULL, NULL, 95),
(141, 190, 'g203', 'X45546S', NULL, 'NO ENCIENDE', '2025-05-21 00:00:00', NULL, NULL, 95),
(142, 191, 'SADASD', 'ASDAS', NULL, 'ASDAS', '2025-05-14 00:00:00', NULL, NULL, 95),
(143, 192, 'AAA', 'AAA', NULL, 'AAAA', '2025-05-22 00:00:00', NULL, NULL, 95),
(144, 193, 'AAAAA', 'BBB', NULL, 'BBBBB', '2025-05-27 00:00:00', NULL, NULL, 95),
(145, 194, 'BBB', 'BBB', NULL, 'BBB', '2025-05-14 00:00:00', NULL, NULL, 95),
(146, 195, 'asdsad', 'asdxas2', NULL, 'asdasxx', '2025-05-14 00:00:00', NULL, NULL, 95),
(147, 196, 'gdsdfgfhh', 'x65432s', NULL, 'sadasd', '2025-05-21 00:00:00', NULL, NULL, 95),
(148, 197, 'adasdx', 'asdsada', NULL, 'zzzzz', '2025-05-15 00:00:00', NULL, NULL, 95),
(149, 198, 'adasd', 'xx12323', NULL, 'vvvvv', '2025-05-21 00:00:00', NULL, NULL, 95),
(150, 199, 'vxx', 'asdas', NULL, 'ttttttt', '2025-05-21 00:00:00', NULL, NULL, 95),
(151, 200, 'mmm', 'mmmm', NULL, 'mmm', '2025-05-15 00:00:00', NULL, NULL, 95),
(152, 201, 'mmm', 'mmmm', NULL, 'hhhh', '2025-05-15 00:00:00', NULL, NULL, 95),
(153, 202, 'mmm', 'mmmm', NULL, 'kkkkk', '2025-05-15 00:00:00', NULL, NULL, 95),
(154, 203, 'ktinker', 'x485121326565', NULL, 'dgfndh', '2025-05-14 00:00:00', NULL, NULL, 95),
(155, 204, 'ktinker', 'x485121326565', NULL, 'dgfndh', '2025-05-14 00:00:00', NULL, NULL, 95),
(156, 205, 'gxd10', 'x485121326565', NULL, 'no enciende', '2025-05-14 00:00:00', NULL, NULL, 95),
(157, 206, 'G20240', 'x545sd54', NULL, 'asdassdd845345', '2025-05-14 00:00:00', NULL, NULL, 71),
(158, 207, 'G20g50x', 'x56160d', NULL, 'se apaga solo el equipo', '2025-05-16 00:00:00', NULL, NULL, 96),
(159, 208, 'Z5775D', 'XD5465487D', NULL, 'EL EQUIPO SE RECALIENTA POR MOMENTOS', '2025-05-15 00:00:00', NULL, 121, 97),
(160, 209, 'L3250', 'x54dsadsd', NULL, 'constante atasco de papel', '2025-05-17 00:00:00', NULL, 122, 98),
(161, 210, 'Gd564', 'xasd54', NULL, 'PANTALLA ROTA', '2025-05-17 00:00:00', NULL, NULL, 98),
(162, 211, 'G4567XXAS', 'ASDAX6546987', NULL, 'NO ENCIENDE', '2025-05-16 00:00:00', NULL, NULL, 98),
(163, 212, 'G4567XXAS', 'ASDAX6546987', NULL, 'NO ENCIENDE', '2025-05-17 00:00:00', NULL, NULL, 98),
(164, 213, 'G4567XXAS', 'ASDAX6546987', NULL, 'NO ENCIENDE', '2025-05-17 00:00:00', NULL, NULL, 98),
(165, 214, 'G4567XXAS', 'ASDAX6546987', NULL, 'MANTENIMIENTO ', '2025-05-17 00:00:00', NULL, NULL, 98),
(166, 217, 'ASUS ROG Strix G15', '5CD9216XYZ', NULL, 'EL EQUIPO NO ENCIENDE', '2025-05-20 00:00:00', NULL, 124, 99),
(167, 216, 'L3110', 'XD56A41SDSAD', NULL, 'ATASCO DE PAPEL Y NO PINTA A COLORES', '2025-05-17 00:00:00', NULL, NULL, 99),
(168, 218, 'DC5040', 'XASAS15421', NULL, 'NO ENCIENDE', '2025-05-22 00:00:00', NULL, 125, 106),
(169, 219, 'DC5040', 'XASAS15421', NULL, 'no enciende', '2025-05-22 00:00:00', NULL, 126, 114),
(170, 222, 'model01', 'serie0102', NULL, 'NO ENCIENDE', '2025-05-30 00:00:00', NULL, NULL, 115),
(171, 221, 'modelo01', 'seria 01', NULL, 'problema 01', '2025-05-31 00:00:00', NULL, NULL, 115),
(172, 223, 'XD4', 'XASDASD54654', NULL, 'NO ENCIENDE', '2025-05-31 00:00:00', NULL, 127, 116),
(173, 224, 'KTINKER500', 'XGSD56454XS', 'MATENIMIENTO\r\nREPARACIÓN DE DISIPADOR', 'NO ENCIENDE', '2025-06-13 00:00:00', NULL, NULL, 116),
(174, 225, 'L200', 'XS574258', 'REVISIÓN DE CABEZAL\r\nMANTENIMIENTO GENERAL\r\n', 'IMPRIME CON RAYAS', '2025-06-10 00:00:00', NULL, 128, 117),
(175, 226, 'GFA54', 'ASDAX65465', 'MANTENIMIENTO GENERAL', 'NO ENCIENDE', '2025-06-19 00:00:00', NULL, NULL, 118),
(176, 227, 'E47335O300', 'LA00472277', 'SERVICIO A MIGRACION A WINDOWS XP A WINDOWS 7.\r\nCON PROBLAMAS BASICO \r\nANTIVIRUS AVAST LICENCIA PREMIU ', 'NO PUEDE APAGARSE', '2025-06-11 00:00:00', NULL, 131, 119);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `distritos`
--

CREATE TABLE `distritos` (
  `iddistrito` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `provincia` varchar(100) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `distritos`
--

INSERT INTO `distritos` (`iddistrito`, `nombre`, `provincia`, `departamento`) VALUES
(1, 'Chincha Alta', 'Chincha', 'Ica'),
(2, 'Chincha Baja', 'Chincha', 'Ica'),
(3, 'El Carmen', 'Chincha', 'Ica'),
(4, 'Grocio Prado', 'Chincha', 'Ica'),
(5, 'San Juan de Yanac', 'Chincha', 'Ica'),
(6, 'San Pedro de Huacarpana', 'Chincha', 'Ica'),
(7, 'Sunampe', 'Chincha', 'Ica'),
(8, 'Tambo de Mora', 'Chincha', 'Ica'),
(9, 'Alto Larán', 'Chincha', 'Ica'),
(10, 'Pueblo Nuevo', 'Chincha', 'Ica'),
(18, 'aaaaa', 'aaaaaaaaaa', 'aaaaaaaaaaaaaa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `idempresa` int(11) NOT NULL,
  `ruc` varchar(20) DEFAULT NULL,
  `razon_social` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT NULL,
  `estado` tinyint(4) DEFAULT NULL,
  `iddistrito` int(11) DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `modificado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresas`
--

INSERT INTO `empresas` (`idempresa`, `ruc`, `razon_social`, `telefono`, `email`, `direccion`, `fecha_creacion`, `fecha_modificacion`, `estado`, `iddistrito`, `creado_por`, `modificado_por`) VALUES
(1, '20547896541', 'Tecnologías Avanzadas S.A.C.', '987654326', 'contacto@techavanzadas.com', 'Av. Tecnológica 123, Chincha Alta', '2025-03-14 14:30:29', '2025-04-07 20:10:17', 1, 1, 1, 1),
(2, '20658974132', 'Soluciones Empresariales E.I.R.L.', '986532147', 'info@solucionesemp.com', 'Calle Innovación 456, Chincha Baja', '2025-03-14 14:30:29', '2025-03-14 14:30:29', 1, 2, 2, 2),
(3, '20789654123', 'Consultora Global S.A.', '985478965', 'consultas@global.com', 'Jr. Desarrollo 789, El Carmen', '2025-03-14 14:30:29', '2025-03-14 14:30:29', 1, 3, 3, 3),
(4, '123456789', 'Empresa Test', '987654321', 'empresa@test.com', 'Direccion de prueba', '2025-03-19 15:56:24', '2025-03-19 15:56:24', 1, 1, 1, 1),
(5, '1', 'a', '1', 'solesdsal99@gmail.com', 'de', '2025-03-19 15:58:24', '2025-03-19 15:58:24', 1, 2, 1, 1),
(6, '85963247123', 'tecnoperi', '939791194', 'solesal99@gmail.com', 'chavalina', '2025-04-02 11:21:20', '2025-04-02 11:21:20', 1, 10, 1, 1),
(7, '20494513009', 'tecnologiaSac', '963258747', '1305068@senati.pe', 'prolongacion chavalina #383', '2025-04-07 20:02:39', '2025-04-07 20:02:39', 1, 1, 1, NULL),
(9, '20494513006', 'tecnologiasSac', '939791194', 'solesal@gmail.com', 'prolongacion chavalina #383', NULL, '2025-06-06 12:40:11', NULL, 1, NULL, 2),
(10, '20494513008', 'tecnologiasSacAArrr', '939791197', 'solesal@gmail.com', 'prolongacion chavalina #383', NULL, NULL, NULL, 1, NULL, NULL),
(11, '20494513010', 'tecnologiasSacAArg', '939791197', 'solesal@gmail.com', 'prolongacion chavalina #383', NULL, NULL, NULL, 1, NULL, NULL),
(12, '85963247123', 'tecnoperú', '939791197', 'solesal@gmail.com', 'prolongacion chavalina #383', NULL, NULL, NULL, 1, NULL, NULL),
(13, '85963247125', 'tecnoperúaaaa', '956786325', 'solesal@gmail.com', 'prolongacion chavalina #383', NULL, NULL, NULL, 7, NULL, NULL),
(14, '85963247125', 'tecnoperúaaaa', '956786325', 'solesal@gmail.com', 'prolongacion chavalina #383', NULL, NULL, NULL, 7, NULL, NULL),
(15, '85963247125', 'tecnoperúaaaa', '956786325', 'solesal@gmail.com', 'prolongacion chavalina #383', NULL, NULL, NULL, 7, NULL, NULL),
(16, '85963247125', 'tecnoperúaaaa', '956786325', 'solesal@gmail.com', 'prolongacion chavalina #383', NULL, NULL, NULL, 7, NULL, NULL),
(17, '85963247125', 'tecnoperúaaaa', '956786325', 'solesal@gmail.com', 'prolongacion chavalina #383', NULL, NULL, NULL, 7, NULL, NULL),
(18, '85963247125', 'tecnoperúaaaa', '956786325', 'solesal@gmail.com', 'prolongacion chavalina #383', NULL, NULL, NULL, 7, NULL, NULL),
(19, '85963247125', 'tecnoperúaaaa', '956786325', 'solesal@gmail.com', 'prolongacion chavalina #383', NULL, NULL, NULL, 7, NULL, NULL),
(20, '85963247125', 'tecnoperúaaaa', '956786325', 'solesal@gmail.com', 'prolongacion chavalina #383', NULL, NULL, NULL, 7, NULL, NULL),
(21, '85963247125', 'tecnoperúaaaa', '956786325', 'solesal@gmail.com', 'prolongacion chavalina #383', NULL, NULL, NULL, 7, NULL, NULL),
(22, '85963247125', 'tecnoperúaaaa', '956786325', 'solesal@gmail.com', 'prolongacion chavalina #383', NULL, NULL, NULL, 7, NULL, NULL),
(23, '85963247125', 'tecnoperúaaaa', '956786325', 'solesal@gmail.com', 'prolongacion chavalina #383', NULL, NULL, NULL, 7, NULL, NULL),
(24, '85963247125', 'tecnoperúaaaa', '956786325', 'solesal@gmail.com', 'prolongacion chavalina #383', NULL, NULL, NULL, 7, NULL, NULL),
(25, '85963247125', 'tecnoperúaaaa', '956786325', 'solesal@gmail.com', 'prolongacion chavalina #383', NULL, NULL, NULL, 7, NULL, NULL),
(26, '20494513019', 'tecnologiasSacAAA89', '112233446', '1305067@senati.pe', 'prolongacion chavalina #383', '2025-06-02 19:33:49', '2025-06-02 19:33:49', 1, 1, 1, NULL),
(27, '20494513019', 'tecnologiasSacAAA89', '112233446', '1305067@senati.pe', 'prolongacion chavalina #383', '2025-06-02 19:49:31', '2025-06-02 19:49:31', 1, 1, 1, NULL),
(28, '20131376503', 'SERVIC NAC DE ADIESTRAM EN TRABAJ INDUST', '966889987', 'ANGELICA@gmail.com', 'AV. ALFREDO MENDIOLA NRO 3520 ', '2025-06-06 12:34:13', '2025-06-06 12:34:13', 1, 2, 1, NULL);

--
-- Disparadores `empresas`
--
DELIMITER $$
CREATE TRIGGER `after_insert_empresa` AFTER INSERT ON `empresas` FOR EACH ROW BEGIN
    INSERT INTO clientes (idpersona, idempresa) 
    VALUES (NULL, NEW.idempresa);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades`
--

CREATE TABLE `especialidades` (
  `idEspecialidad` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `especialidades`
--

INSERT INTO `especialidades` (`idEspecialidad`, `nombre`, `descripcion`) VALUES
(1, 'Experto en Impresoras', 'Diagnóstico, reparación y mantenimiento de impresoras de tinta y láser.'),
(2, 'Experto en Laptops', 'Reparación y mantenimiento de hardware y software en laptops.'),
(3, 'Experto en Computadoras', 'Diagnóstico, reparación y optimización de equipos de escritorio.'),
(4, 'Soporte Técnico', 'Asistencia y solución de problemas en software y hardware.'),
(5, 'Administración de Redes', 'Configuración y mantenimiento de redes informáticas.'),
(6, 'Seguridad Informática', 'Protección de datos y eliminación de amenazas cibernéticas.'),
(7, 'Recuperación de Datos', 'Recuperación de archivos eliminados o dañados en discos duros y SSD.'),
(8, 'Actualización de Equipos', 'Mejoras en hardware y software para optimizar el rendimiento.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especialidades_tecnicos`
--

CREATE TABLE `especialidades_tecnicos` (
  `idhabilidad_de_usuario` int(11) NOT NULL,
  `idusuario` int(11) DEFAULT NULL,
  `idEspecialidad` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `especialidades_tecnicos`
--

INSERT INTO `especialidades_tecnicos` (`idhabilidad_de_usuario`, `idusuario`, `idEspecialidad`) VALUES
(1, 3, 1),
(2, 3, 2),
(3, 3, 3),
(4, 3, 4),
(5, 3, 5),
(6, 3, 6),
(7, 3, 7),
(8, 3, 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especificaciones`
--

CREATE TABLE `especificaciones` (
  `id_especificacion` int(11) NOT NULL,
  `especificacion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `especificaciones`
--

INSERT INTO `especificaciones` (`id_especificacion`, `especificacion`) VALUES
(1, 'Memoria Ram'),
(2, 'Disco Duro'),
(3, 'Procesador'),
(4, 'Color'),
(7, 'Voltaje'),
(10, 'Sistema Operativo'),
(13, 'Pantalla');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evidencias_entrada`
--

CREATE TABLE `evidencias_entrada` (
  `idEvidencia` int(11) NOT NULL,
  `ruta_Evidencia_Entrada` varchar(255) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `evidencias_entrada`
--

INSERT INTO `evidencias_entrada` (`idEvidencia`, `ruta_Evidencia_Entrada`, `fecha_creacion`) VALUES
(1, 'path/to/evidence.jpg', '2025-04-07 20:12:12'),
(2, 'https://storage.googleapis.com/evidencia_entrada/imagenes/capturas.png', '2025-04-07 20:15:30'),
(3, 'https://storage.googleapis.com/evidencia_entrada/imagenes/maxito_hermoso.png', '2025-04-09 14:46:02'),
(4, 'https://storage.googleapis.com/evidencia_entrada/imagenes/asdfg.png', '2025-04-09 14:58:07'),
(5, 'https://storage.googleapis.com/evidencia_entrada/imagenes/el_logo.jpeg', '2025-04-09 15:02:58'),
(6, 'https://storage.googleapis.com/evidencias_general/imagenes/asdfgh.png', '2025-04-09 15:32:01'),
(7, 'https://storage.googleapis.com/evidencias_general/imagenes/Pc_torre_de_vidrio.png', '2025-04-09 16:30:33'),
(8, 'https://storage.googleapis.com/evidencias_general/imagenes/Pc_de_vidrio.png', '2025-04-09 17:03:31'),
(9, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/maxito.png', '2025-04-09 22:10:49'),
(10, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/Test_max.png', '2025-04-10 14:40:41'),
(11, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/laptop_lenovo.png', '2025-04-10 18:35:52'),
(12, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdfghj.png', '2025-04-10 23:06:10'),
(13, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdfghjfgfsdfgh.png', '2025-04-11 08:50:15'),
(14, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/sdfghj.png', '2025-04-11 09:22:06'),
(15, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/max.png', '2025-04-11 09:28:29'),
(16, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/wertyui.png', '2025-04-11 09:33:57'),
(17, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdfgh.png', '2025-04-11 09:39:08'),
(18, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/max.png', '2025-04-11 09:43:22'),
(19, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/max_rojas.png', '2025-04-11 09:47:03'),
(20, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/logo.png', '2025-04-11 09:48:03'),
(21, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/max.png', '2025-04-11 09:53:34'),
(22, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/dfghjk.png', '2025-04-11 09:54:01'),
(23, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/ghjk.png', '2025-04-11 09:56:53'),
(24, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdfg.png', '2025-04-11 10:00:45'),
(25, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/andrea.png', '2025-04-11 10:10:08'),
(26, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/andrea.png', '2025-04-11 10:10:10'),
(27, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/jhon.png', '2025-04-11 10:10:49'),
(28, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/gaaaaa.png', '2025-04-11 10:11:44'),
(29, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/hola_soy_max.png', '2025-04-11 10:27:05'),
(30, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/randon.png', '2025-04-11 10:28:11'),
(31, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/impresora.jpeg', '2025-04-11 11:03:34'),
(32, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/impresora.jpeg', '2025-04-19 12:04:45'),
(33, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/impresoraaaa.jpeg', '2025-04-19 12:05:18'),
(34, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/ultimo_logo.png', '2025-04-19 12:06:53'),
(35, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/impresora.jpeg', '2025-04-19 12:11:29'),
(36, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/loguito.png', '2025-04-19 12:14:08'),
(37, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/Alumnos.jpeg', '2025-04-19 12:17:54'),
(38, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdas.jpeg', '2025-04-19 12:21:08'),
(39, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/foto.jpeg', '2025-04-19 12:23:41'),
(40, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/impre.jpeg', '2025-04-19 12:24:47'),
(41, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasd.jpeg', '2025-04-19 12:28:37'),
(42, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/fo.jpeg', '2025-04-19 12:32:03'),
(43, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/go.jpeg', '2025-04-19 12:34:27'),
(44, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/z.jpeg', '2025-04-19 12:41:14'),
(45, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/j.jpeg', '2025-04-19 12:43:35'),
(46, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdfgbnm.jpeg', '2025-04-19 12:45:50'),
(47, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/francia.png', '2025-04-24 16:52:19'),
(48, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/impreosra.jpeg', '2025-04-24 17:00:40'),
(49, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/s.jpeg', '2025-04-24 17:03:27'),
(50, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/franchute.png', '2025-04-24 17:08:00'),
(51, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/hola.jpeg', '2025-04-24 17:12:22'),
(52, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/nopo.png', '2025-04-24 17:13:29'),
(53, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/jose.png', '2025-04-24 17:15:21'),
(54, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/hola_1745533288_0.jpeg', '2025-04-24 17:21:29'),
(55, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/francia_1745533310_0.jpeg', '2025-04-24 17:21:52'),
(56, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/francia_1745533312_1.png', '2025-04-24 17:21:53'),
(57, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/sdasdsad_1745533664_0.jpeg', '2025-04-24 17:27:45'),
(58, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/xffg.jpeg', '2025-04-24 17:33:20'),
(59, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasdsa.jpeg', '2025-04-24 17:33:42'),
(60, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/dfgfdsfg.jpeg', '2025-04-24 17:41:13'),
(61, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasd.jpeg', '2025-04-24 17:55:04'),
(62, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/hola.jpeg', '2025-04-24 18:03:24'),
(63, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/ompreosra.jpeg', '2025-04-24 18:03:45'),
(64, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/dfdsfdsf.jpeg', '2025-04-24 18:05:05'),
(65, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasd.jpeg', '2025-04-24 18:23:40'),
(66, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/dfgdfgdfg.jpeg', '2025-04-24 18:24:15'),
(67, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasd.jpeg', '2025-04-24 18:25:25'),
(68, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdsad.jpeg', '2025-04-24 18:25:45'),
(69, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/dfdfd.jpeg', '2025-04-24 18:28:37'),
(70, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/dfdfd.jpeg', '2025-04-24 18:34:37'),
(71, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/dfdf.jpeg', '2025-04-24 18:35:03'),
(72, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/fdfdf.jpeg', '2025-04-24 18:38:01'),
(73, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/impresora.jpeg', '2025-04-24 18:41:27'),
(74, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/impresora_epson.jpeg', '2025-04-24 18:42:49'),
(75, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/max_y_jose.png', '2025-04-24 18:45:01'),
(76, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasd.png', '2025-04-24 18:46:25'),
(77, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasd.png', '2025-04-24 18:46:31'),
(78, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/impresora.jpeg', '2025-04-24 18:47:04'),
(79, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdsa.jpeg', '2025-04-24 18:47:19'),
(80, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdsada.jpeg', '2025-04-24 18:48:17'),
(81, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasd.jpeg', '2025-04-24 18:48:33'),
(82, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasd.jpeg', '2025-04-24 18:50:04'),
(83, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/xdsds.jpeg', '2025-04-24 18:50:19'),
(84, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/adssad.png', '2025-04-24 18:50:38'),
(85, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/sdsd.jpeg', '2025-04-24 18:51:21'),
(86, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasdasd.png', '2025-04-24 18:52:09'),
(87, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasd.png', '2025-04-24 18:52:41'),
(88, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasd.png', '2025-04-24 18:53:50'),
(89, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasdasd.png', '2025-04-24 18:54:41'),
(90, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasd.png', '2025-04-24 18:54:59'),
(91, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasd.png', '2025-04-24 18:55:18'),
(92, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/adasdsd.png', '2025-04-24 18:55:34'),
(93, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/sdsds.jpeg', '2025-04-24 18:56:03'),
(94, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/sddsd.jpeg', '2025-04-24 18:57:10'),
(95, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdsadasd.png', '2025-04-24 18:58:06'),
(96, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/sdsd.jpeg', '2025-04-24 18:59:02'),
(97, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasd.jpeg', '2025-04-24 18:59:53'),
(98, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdsada.jpeg', '2025-04-24 19:00:12'),
(99, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasd.jpeg', '2025-04-24 19:01:31'),
(100, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasdsa.jpeg', '2025-04-24 19:03:00'),
(101, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasd.jpeg', '2025-04-24 19:04:50'),
(102, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdsad.jpeg', '2025-04-24 19:05:51'),
(103, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasda.jpeg', '2025-04-24 19:07:29'),
(104, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/sadasdasda.png', '2025-04-24 19:08:39'),
(105, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasd.jpeg', '2025-04-24 19:10:23'),
(106, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasdas.png', '2025-04-24 19:11:15'),
(107, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasdasd.jpeg', '2025-04-24 19:12:38'),
(108, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasdsad.png', '2025-04-24 19:12:56'),
(109, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/sdsdsd.jpeg', '2025-04-24 19:15:31'),
(110, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasd.png', '2025-04-24 19:16:30'),
(111, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/dasdasd.jpeg', '2025-04-24 19:18:10'),
(112, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/dasdasd.jpeg', '2025-04-24 19:18:14'),
(113, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdsad.jpeg', '2025-04-24 19:18:33'),
(114, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/sadasdsad.jpeg', '2025-04-24 19:18:45'),
(115, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/sdsd.jpeg', '2025-04-24 19:20:25'),
(116, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/PC__GAMING.jpeg', '2025-04-28 14:27:51'),
(117, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdasd.jpeg', '2025-04-28 16:21:33'),
(118, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/sdfsdfds.jpeg', '2025-04-28 16:23:15'),
(119, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/haklhsdlashdj.png', '2025-05-02 16:19:59'),
(120, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/gatito.png', '2025-05-12 15:40:52'),
(121, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/EVIDENCIA_DE_ENTRADA.jpeg', '2025-05-14 16:23:34'),
(122, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/EVIDENCIA_1.jpeg', '2025-05-14 17:07:09'),
(123, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/laptop_no_enciende.jpeg', '2025-05-17 10:17:53'),
(124, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/kalkshdaskjgd.png', '2025-05-17 10:21:23'),
(125, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/pc_malogrado.jpeg', '2025-05-21 14:12:06'),
(126, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/laptop_para_reparar.jpeg', '2025-05-21 14:47:30'),
(127, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/LAPTOP_QUE_NO_ENCIENDE.jpeg', '2025-05-29 21:12:04'),
(128, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdad.jpeg', '2025-06-06 11:22:56'),
(129, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/pc_intel_no_se_puede_apagar.jpeg', '2025-06-11 15:33:49'),
(130, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/ds.jpeg', '2025-06-11 15:36:47'),
(131, 'https://storage.googleapis.com/evidencias_general/evidencia_entrada/asdas.jpeg', '2025-06-11 17:08:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evidencia_tecnica`
--

CREATE TABLE `evidencia_tecnica` (
  `idEvidencia_Tecnica` int(11) NOT NULL,
  `imagen_tecnico` text DEFAULT NULL,
  `comentarios` text DEFAULT NULL,
  `iddetservicio` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `evidencia_tecnica`
--

INSERT INTO `evidencia_tecnica` (`idEvidencia_Tecnica`, `imagen_tecnico`, `comentarios`, `iddetservicio`) VALUES
(4, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/lpl.jpeg', NULL, 8),
(5, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/asdsad.jpeg', NULL, 10),
(6, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/dffd.jpeg', NULL, 8),
(7, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/fgkiu.jpeg', NULL, 8),
(8, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/gfdfghg.png', NULL, 9),
(9, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/sadasdsad1.jpeg', NULL, 5),
(10, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/Diggui.jpeg', NULL, 5),
(11, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/vbnvnvcbncnb.jpeg', NULL, 5),
(12, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/fhjkl8i.jpeg', NULL, 5),
(13, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/gatito.png', NULL, 13),
(14, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/asdasd.png', NULL, 5),
(15, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/primero_evidencia.jpeg', NULL, 18),
(16, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/se_encontre_dentro_de_hdiadjaos_obejto_quek_hasd.jpeg', NULL, 19),
(17, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/esta_laptop_esta_rota_la_pantalla_con_lagunas_negras.jpeg', NULL, 20),
(18, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/aca_esta_roto_el_teclado.jpeg', NULL, 20),
(19, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/Xd.jpeg', NULL, 5),
(20, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/antes.jpeg', NULL, 22),
(21, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/despues.jpeg', NULL, 22),
(22, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/laptop_rota.jpeg', NULL, 23),
(23, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/mantenimiento.jpeg', NULL, 26),
(24, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/mantenimiento_del_equipo.jpeg', NULL, 25),
(25, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/mantenimiento.jpeg', NULL, 17),
(26, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/reparaci__n.jpeg', NULL, 14),
(27, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/instalaci__n_de_sistema_operativo_win_11.jpeg', NULL, 27),
(28, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/aletorio.jpeg', NULL, 29),
(29, 'https://storage.googleapis.com/evidencias_general/evidencia_salida/MANTENIMIENTO.jpeg', NULL, 31);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcas`
--

CREATE TABLE `marcas` (
  `id_marca` int(11) NOT NULL,
  `Nombre_Marca` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marcas`
--

INSERT INTO `marcas` (`id_marca`, `Nombre_Marca`) VALUES
(1, 'HP'),
(2, 'Dell'),
(3, 'Lenovo'),
(4, 'Asus'),
(5, 'Acer'),
(6, 'Canon'),
(7, 'Epson'),
(8, 'Brother'),
(9, 'Samsung');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcasasoc`
--

CREATE TABLE `marcasasoc` (
  `idmarcasoc` int(11) NOT NULL,
  `id_marca` int(11) NOT NULL,
  `id_subcategoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marcasasoc`
--

INSERT INTO `marcasasoc` (`idmarcasoc`, `id_marca`, `id_subcategoria`) VALUES
(2, 1, 2),
(3, 1, 3),
(4, 1, 7),
(5, 1, 8),
(6, 1, 9),
(7, 1, 15),
(8, 1, 16),
(9, 2, 3),
(10, 2, 7),
(11, 2, 8),
(12, 2, 9),
(13, 2, 10),
(14, 2, 15),
(15, 2, 16),
(16, 3, 7),
(17, 3, 8),
(18, 3, 9),
(19, 3, 10),
(20, 3, 15),
(21, 3, 16),
(22, 4, 7),
(23, 4, 8),
(24, 4, 9),
(25, 4, 15),
(26, 4, 16),
(27, 5, 7),
(28, 5, 8),
(29, 5, 9),
(30, 5, 15),
(31, 5, 16),
(32, 1, 7),
(33, 1, 8),
(34, 1, 9),
(35, 1, 15),
(36, 1, 16),
(37, 2, 3),
(38, 2, 7),
(39, 2, 8),
(40, 2, 9),
(41, 2, 10),
(42, 2, 15),
(43, 2, 16),
(44, 3, 7),
(45, 3, 8),
(46, 3, 9),
(47, 3, 10),
(48, 3, 15),
(49, 3, 16),
(50, 4, 7),
(51, 4, 8),
(52, 4, 9),
(53, 4, 15),
(54, 4, 16),
(55, 5, 7),
(56, 5, 8),
(57, 5, 9),
(58, 5, 15),
(59, 5, 16),
(60, 6, 2),
(61, 6, 3),
(62, 6, 8),
(63, 6, 4),
(64, 7, 2),
(65, 7, 3),
(67, 7, 4),
(68, 8, 2),
(69, 8, 3),
(70, 8, 8),
(71, 9, 2),
(72, 9, 3),
(73, 9, 7),
(74, 9, 8),
(75, 9, 15),
(76, 1, 7),
(77, 1, 7),
(78, 1, 7),
(79, 2, 7),
(80, 1, 7),
(81, 1, 7),
(82, 1, 7),
(83, 1, 7),
(84, 1, 7),
(85, 1, 7),
(86, 1, 7),
(87, 1, 7),
(88, 1, 7),
(89, 1, 7),
(90, 1, 8),
(91, 2, 9),
(92, 2, 9),
(93, 2, 7),
(94, 2, 7),
(95, 2, 7),
(96, 1, 7),
(97, 1, 7),
(98, 1, 8),
(99, 1, 7),
(100, 1, 7),
(101, 1, 7),
(102, 1, 7),
(103, 1, 7),
(104, 2, 8),
(105, 2, 8),
(106, 2, 8),
(107, 1, 7),
(108, 2, 8),
(109, 2, 7),
(110, 7, 7),
(111, 1, 7),
(112, 2, 8),
(113, 1, 7),
(114, 3, 8),
(115, 4, 8),
(116, 5, 17),
(117, 3, 20),
(118, 5, 9),
(119, 1, 17),
(120, 4, 8),
(121, 5, 8),
(122, 2, 15),
(123, 4, 8),
(124, 4, 7),
(125, 5, 7),
(126, 3, 8),
(127, 3, 15),
(128, 2, 15),
(129, 2, 15),
(130, 4, 7),
(131, 1, 9),
(132, 4, 7),
(133, 1, 1),
(134, 4, 3),
(135, 2, 8),
(136, 7, 3),
(137, 9, 6),
(138, 4, 7),
(139, 4, 7),
(140, 4, 7),
(141, 4, 7),
(142, 4, 7),
(143, 4, 7),
(144, 4, 7),
(145, 4, 7),
(146, 4, 7),
(147, 2, 9),
(148, 3, 16),
(149, 8, 3),
(150, 4, 16),
(151, 1, 2),
(152, 5, 10),
(153, 3, 9),
(154, 2, 16),
(155, 7, 8),
(156, 7, 8),
(157, 3, 8),
(158, 3, 17),
(159, 4, 8),
(160, 4, 8),
(161, 3, 9),
(162, 3, 15),
(163, 2, 15),
(164, 2, 15),
(165, 3, 17),
(166, 3, 17),
(167, 3, 16),
(168, 3, 16),
(169, 9, 13),
(170, 4, 16),
(171, 3, 8),
(172, 2, 15),
(173, 1, 8),
(174, 2, 8),
(175, 4, 16),
(176, 7, 1),
(177, 3, 8),
(178, 2, 16),
(179, 1, 11),
(180, 4, 17),
(181, 3, 17),
(182, 7, 1),
(183, 3, 11),
(184, 1, 11),
(185, 1, 11),
(186, 1, 11),
(187, 2, 11),
(188, 2, 9),
(189, 3, 16),
(190, 7, 2),
(191, 3, 15),
(192, 3, 14),
(193, 3, 9),
(194, 4, 18),
(195, 3, 9),
(196, 2, 15),
(197, 5, 16),
(198, 2, 15),
(199, 2, 15),
(200, 4, 9),
(201, 2, 14),
(202, 1, 15),
(203, 3, 14),
(204, 3, 14),
(205, 2, 9),
(206, 1, 15),
(207, 3, 11),
(208, 3, 11),
(209, 7, 1),
(210, 2, 15),
(211, 2, 8),
(212, 1, 8),
(213, 1, 8),
(214, 3, 7),
(215, 1, 17),
(216, 7, 6),
(217, 1, 17),
(218, 2, 7),
(219, 3, 9),
(220, 1, 11),
(221, 2, 8),
(222, 1, 11),
(223, 3, 17),
(224, 1, 7),
(225, 7, 1),
(226, 1, 8),
(227, 1, 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_de_servicios`
--

CREATE TABLE `orden_de_servicios` (
  `idorden_Servicio` int(11) NOT NULL,
  `fecha_recepcion` datetime DEFAULT NULL,
  `idusuario_crea` int(11) DEFAULT NULL,
  `idcliente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `orden_de_servicios`
--

INSERT INTO `orden_de_servicios` (`idorden_Servicio`, `fecha_recepcion`, `idusuario_crea`, `idcliente`) VALUES
(71, '2025-04-09 22:19:33', 2, 43),
(72, '2025-04-10 18:33:55', 2, 29),
(73, '2025-04-11 08:48:44', 2, 57),
(80, '2025-04-11 10:57:32', 2, 48),
(91, '2025-04-18 13:12:00', 1, 32),
(93, '2025-04-18 16:29:00', 2, 43),
(95, '2025-05-12 15:19:00', 2, 43),
(96, '2025-05-14 15:01:00', 2, 43),
(97, '2025-05-14 16:20:00', 2, 80),
(98, '2025-05-14 17:03:00', 2, 83),
(99, '2025-05-17 10:08:00', 2, 84),
(105, '2025-05-21 14:03:00', 2, 43),
(106, '2025-05-21 14:06:00', 4, 43),
(108, '2025-05-21 14:32:00', 4, 43),
(110, '2025-05-21 14:37:00', NULL, 43),
(111, '2025-05-21 14:38:00', NULL, 43),
(112, '2025-05-21 14:38:00', NULL, 43),
(113, '2025-05-21 14:41:00', NULL, 43),
(114, '2025-05-21 14:46:00', 7, 43),
(115, '2025-05-22 18:47:00', 2, 43),
(116, '2025-05-29 21:09:00', 2, 87),
(117, '2025-06-06 10:54:00', 4, 90),
(118, '2025-06-06 12:34:00', 2, 91),
(119, '2025-06-11 15:04:00', 2, 93);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas`
--

CREATE TABLE `personas` (
  `idpersona` int(11) NOT NULL,
  `nombres` varchar(255) DEFAULT NULL,
  `Primer_Apellido` varchar(255) DEFAULT NULL,
  `Segundo_Apellido` varchar(50) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `tipodoc` enum('DNI','Pasaporte','Carnet de Extranjería') NOT NULL,
  `numerodoc` varchar(50) NOT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `creado_por` int(11) DEFAULT NULL,
  `modificado_por` int(11) DEFAULT NULL,
  `estado` tinyint(4) DEFAULT 1,
  `iddistrito` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personas`
--

INSERT INTO `personas` (`idpersona`, `nombres`, `Primer_Apellido`, `Segundo_Apellido`, `telefono`, `tipodoc`, `numerodoc`, `correo`, `direccion`, `fecha_creacion`, `fecha_modificacion`, `creado_por`, `modificado_por`, `estado`, `iddistrito`) VALUES
(1, 'Carlos', 'Gómez', '', '987654321', 'DNI', '12345678', NULL, 'Av. Los Álamos 123', '2025-03-14 14:30:29', NULL, NULL, NULL, 1, 1),
(2, 'María', 'López', '', '986532147', 'DNI', '23456789', NULL, 'Calle Las Flores 456', '2025-03-14 14:30:29', NULL, NULL, NULL, 1, 2),
(3, 'Jorge', 'Martínez', '', '985478965', 'DNI', '34567890', NULL, 'Jr. San Martín 789', '2025-03-14 14:30:29', NULL, NULL, NULL, 1, 3),
(4, 'Lucía', 'Torres', '', '984125693', 'DNI', '45678901', NULL, 'Pasaje El Sol 321', '2025-03-14 14:30:29', NULL, NULL, NULL, 1, 4),
(5, 'Fernando', 'Rojas', '', '983654789', 'DNI', '56789012', NULL, 'Av. Principal 654', '2025-03-14 14:30:29', NULL, NULL, NULL, 1, 5),
(6, 'Ana', 'Castro', '', '982365478', 'DNI', '67890123', NULL, 'Calle Central 987', '2025-03-14 14:30:29', NULL, NULL, NULL, 1, 6),
(7, 'José', 'Ramírez', '', '981475236', 'Pasaporte', 'AB1234567', NULL, 'Jr. Independencia 852', '2025-03-14 14:30:29', NULL, NULL, NULL, 1, 7),
(8, 'Rosa', 'Santos', '', '980214785', 'Carnet de Extranjería', 'CE7654321', NULL, 'Urb. Los Pinos Mz A Lt 10', '2025-03-14 14:30:29', NULL, NULL, NULL, 1, 8),
(9, 'Pedro', 'Vargas', '', '979654123', 'DNI', '78901234', NULL, 'Callejón Los Cedros 963', '2025-03-14 14:30:29', NULL, NULL, NULL, 1, 9),
(10, 'Elena', 'Mendoza', '', '978321654', 'Pasaporte', 'XY9876543', NULL, 'Jr. Amazonas 159', '2025-03-14 14:30:29', NULL, NULL, NULL, 1, 10),
(13, 'PRUEBA', 'Pérez', '', '987634321', 'DNI', '12344678', NULL, 'Av. Siempre Viva 123', '2025-03-14 15:01:34', NULL, NULL, NULL, 1, 1),
(15, 'Nombre', 'ApellidoP', '', '999999999', 'DNI', '12345648', NULL, 'Dirección', '2025-03-15 09:13:54', NULL, NULL, NULL, 1, 1),
(16, 'asdasd', 'asdasdsad', '', 'asdasd', 'DNI', '2323232', NULL, 'asdsadsadasd', '2025-03-15 09:37:08', NULL, NULL, NULL, 1, NULL),
(17, 'max', 'max', '', '9966695', 'DNI', '165465', NULL, 'asdsadas', '2025-03-15 09:39:05', NULL, NULL, NULL, 1, NULL),
(18, 'max', 'max', '', '234234234', 'DNI', '234324324', NULL, 'asdadasdsdasda', '2025-03-15 09:41:06', NULL, NULL, NULL, 1, NULL),
(19, 'asdasd', 'asdsad', '', '2321313213123', 'Pasaporte', '231231', NULL, 'asdasdsadas', '2025-03-15 09:41:46', NULL, NULL, NULL, 1, NULL),
(21, 'Patriciaaa', 'Ortizz', '', '966778844', 'DNI', '6886655887', NULL, 'en su casa vive ', '2025-03-15 09:44:18', NULL, NULL, NULL, 1, 7),
(22, 'nombresito', 'apellisito', '', '988778843', 'DNI', '2232313', NULL, 'en su otra casa ', '2025-03-15 09:47:14', NULL, NULL, NULL, 1, 1),
(23, 'hjhjgj', 'yytutyut', '', '565656565', 'DNI', '76676767', NULL, 'uyuyuyuyu', '2025-03-15 09:55:19', NULL, NULL, NULL, 1, 7),
(28, 'uuuu', 'uuuuu', '', '78978978', 'Pasaporte', '78787878787', NULL, 'casa de la casa ', '2025-03-15 10:39:07', NULL, NULL, NULL, 1, 7),
(30, 'asdasdsad', 'wqdsadsad', '', '23232323231', 'Pasaporte', '5667789787', NULL, 'ghfgdfsfghh', '2025-03-15 10:53:09', NULL, NULL, NULL, 1, 8),
(32, 'holaaa ', 'holaaa', '', '87946545', 'DNI', '4565466754', NULL, 'sdsadasdsadsaffgghhjkjjhgf', '2025-03-15 10:54:26', NULL, NULL, NULL, 1, 7),
(33, 'UltimoCliente', 'Ultimo', '', '966886629', 'DNI', '12689574', NULL, 'en su casa vive p', '2025-03-26 17:31:47', NULL, NULL, NULL, 1, 7),
(34, 'ANDREA ESTEFANIA', 'ORTIZ ', '', '935395441', 'DNI', '74848004', NULL, 'CHABALINA', '2025-03-26 20:24:46', NULL, NULL, NULL, 1, 1),
(35, 'maria', 'perez', '', '9596465', 'DNI', '785465455', NULL, 'CHABALINA', '2025-03-27 16:47:16', NULL, NULL, NULL, 1, 3),
(36, 'arturo', 'dsadsad', '', '85858585', 'DNI', '8544454545', NULL, 'CHABALINA', '2025-03-28 12:31:42', NULL, NULL, NULL, 1, 4),
(38, 'Juan', 'Pérez Gómez', '', '987654321', 'DNI', '78635924', NULL, 'Av. Principal 123', '2025-04-02 10:46:11', NULL, NULL, NULL, 1, 1),
(42, 'Patricia Soledad', NULL, '', '939791194', 'DNI', '72806361', NULL, 'prolongacion chavalina #383', '2025-04-02 10:59:46', NULL, NULL, NULL, 1, 1),
(43, 'Patricia Soledad', NULL, '', '939791194', 'DNI', '72806362', NULL, 'prolongacion chavalina #383', '2025-04-02 11:00:31', NULL, NULL, NULL, 1, 10),
(44, 'Patricia Soledad', 'Ortiz Salvador Ortiz Salvador', '', '939791194', 'DNI', '72806363', NULL, 'prolongacion chavalina #383', '2025-04-02 11:02:13', NULL, NULL, NULL, 1, 8),
(45, 'Patricia Soledad', 'Ortiz Salvador Ortiz Salvador', '', '939791194', 'Pasaporte', '72806365', NULL, 'prolongacion chavalina #383', '2025-04-02 11:07:26', NULL, NULL, NULL, 1, 2),
(46, 'Patricia Katerine', NULL, '', '939791194', 'DNI', '72807365', NULL, 'prolongacion chavalina #383', '2025-04-02 11:10:28', NULL, NULL, NULL, 1, 4),
(47, 'Patricia Katerine', NULL, '', '939791194', 'DNI', '72807366', NULL, 'prolongacion chavalina #383', '2025-04-02 11:14:46', NULL, NULL, NULL, 1, 7),
(48, 'Patricia Kateri', 'Ortiz sanchez', '', '939791194', 'DNI', '72807367', NULL, 'prolongacion chavalina #383', '2025-04-02 11:16:40', NULL, NULL, NULL, 1, 2),
(49, 'Max', 'Rojas', 'Huarcaya', '928209520', 'DNI', '72672071', 'jmaxrh@gmail.com', 'miguel grau mz g lt 10', '2025-04-02 17:22:30', '2025-05-21 15:46:00', 1, 0, 1, 6),
(50, 'z', 'z', 'z', '988998874', 'DNI', '7458789521', 'jmaxrh@gmail.com', 'miguel grau mz g lt 10', '2025-04-02 17:24:15', NULL, 0, 0, 1, 7),
(52, 'Juan Carlos', 'Pérez', 'Gómez', '987654321', 'DNI', '87654321', 'juan.perez@gmail.com', 'Av. Siempre Viva 123', '2025-04-02 22:53:37', NULL, 2, NULL, 1, 1),
(53, 'v', 'v', 'v', '966886621', 'DNI', '9874561845', 'jmaxrh@gmail.com', 'miguel grau mz g lt 10', '2025-04-02 22:56:48', NULL, 2, NULL, 1, 4),
(59, 'Juan Perez', 'Artega', 'Rojas', '966886687', 'DNI', '72672154', 'jmaxrh@gmail.com', 'Cal. San Vicente Ferrer Nro. S/n (Alt. Cervec. Backus a 250 M. Capilla Svf)', '2025-04-04 12:30:39', NULL, 2, NULL, 1, 6),
(61, 'jhon', 'c', 'c', '7548745', 'DNI', '7845745', 'jmaxrh@gmail.com', 'Cal. San Vicente Ferrer Nro. S/n (Alt. Cervec. Backus a 250 M. Capilla Svf)', '2025-04-04 13:36:35', NULL, 2, NULL, 1, 4),
(63, 'pedro', 'gomez', 'lara', '966886637', 'DNI', '72672077', 'jmaxrh@gmail.com', 'AV. MARISCAL CASTILLA NRO 785', '2025-04-07 14:34:11', NULL, 2, NULL, 1, 7),
(67, 'pedrito', 'gomez', 'lara', '966886637', 'DNI', '72672070', 'jmaxrh@gmail.com', 'AV. MARISCAL CASTILLA NRO 785', '2025-04-07 14:39:04', NULL, 2, NULL, 1, 6),
(71, 'Juan Perez', 'Artega', 'Rojas', '966886684', 'Pasaporte', '852575441', 'g@gmail.com', 'Cal. San Vicente Ferrer Nro. S/n (Alt. Cervec. Backus a 250 M. Capilla Svf)', '2025-04-07 14:42:55', NULL, 2, NULL, 1, 7),
(89, 'Juancito', 'ORTIZ', 'SAYRITUPAC', '935395440', 'DNI', '72672075', 'jmaxrh@gmail.com', 'CHABALINA', '2025-04-07 14:53:19', NULL, 2, NULL, 1, 9),
(91, 'JuancitOOO', 'ORTIZ', 'SAYRITUPAC', '935395440', 'DNI', '72672074', 'jmaxrh@gmail.com', 'CHABALINA', '2025-04-07 14:54:47', NULL, 2, NULL, 1, 5),
(93, 'JuancitOOOOOOOOOOOOOO', 'ORTIZ', 'SAYRITUPAC', '935395440', 'DNI', '72672079', 'jmaxrh@gmail.com', 'CHABALINA', '2025-04-07 14:58:21', NULL, 2, NULL, 1, 6),
(160, 'test', 'ORTIZ', 'SAYRITUPAC', '935395440', 'Pasaporte', '72672055', 'jmaxrh@gmail.com', 'CHABALINA', '2025-04-07 15:31:10', NULL, 2, NULL, 1, 7),
(214, 'ANDREA ESTEFANIA', 'ORTIZ', 'SAYRITUPAC', '935395441', 'DNI', '22672074', 'jmaxrh@gmail.com', 'CHABALINA', '2025-04-07 15:52:20', NULL, 2, NULL, 1, 5),
(216, 'Patricia Soledad', 'Ortiz', 'Salvador', '939791194', 'DNI', '72806360', 'solesal@gmail.com', 'prolongacion chavalina #383', '2025-04-14 16:07:30', NULL, 1, NULL, 1, 1),
(220, 'Patricia Soledad', 'Ortiz', 'Salvador', '', 'DNI', '72806390', NULL, '', '2025-04-16 18:50:43', NULL, NULL, NULL, 1, 1),
(227, 'pepe', 'pepe', 'pepe', '966886875', 'Pasaporte', '72672989', 'jmaxrh@gmail.com', 'AV. MARISCAL CASTILLA NRO 785', '2025-04-25 16:21:38', NULL, 2, NULL, 1, 6),
(228, 'Richard', 'Barrios', 'huarcaya', '966882478', 'DNI', '95876049', 'rojas_rojitas71@hotmail.com', 'miguel grau mz g lt 10', '2025-05-14 15:09:17', NULL, 2, NULL, 1, 7),
(236, 'pepito', 'garraban', 'gatito', '988779986', 'DNI', '42657287', '', 'A.H MIGUEL GRAU MZ \"G\" LT \"10\"', '2025-05-14 15:20:15', NULL, 2, NULL, 1, 10),
(271, 'JOSUE ISAI', 'PILPE', 'YATACO', '966886625', 'DNI', '71882015', 'jmaxrh@gmail.com', 'A.H MIGUEL GRAU MZ \"G\" LT \"10\"', '2025-05-14 16:17:34', NULL, 2, NULL, 1, 2),
(273, 'KAROL YELARIE', 'PILPE', 'YATACO', '966886625', 'DNI', '71882014', 'jmaxrh@gmail.com', 'A.H MIGUEL GRAU MZ \"G\" LT \"10\"', '2025-05-14 16:19:55', NULL, 2, NULL, 1, 5),
(274, 'VICTOR JEAN PIERRE', 'TORRES', 'SARAVIA', '966886625', 'DNI', '71882010', 'jmaxrh@gmail.com', 'A.H MIGUEL GRAU MZ \"G\" LT \"10\"', '2025-05-14 16:20:44', NULL, 2, NULL, 1, 2),
(276, 'RICHARD JHONSON', 'BARRIOS', 'QUISPE', '966886625', 'DNI', '42282116', 'jmaxrh@gmail.com', 'miguel grau mz g lt 10', '2025-05-14 17:03:28', NULL, 2, NULL, 1, 1),
(277, 'GLADYS', 'ROJAS', 'VEGA', '978392050', 'DNI', '47322010', 'jmaxrh@gmail.com', 'miguel grau mz g lt 10', '2025-05-17 10:07:24', NULL, 2, NULL, 1, 10),
(278, 'DIGGY TONY JESUS', 'FELIX', 'TIPACTI', '967356695', 'DNI', '70073736', 'Diggy@gmail.com', 'Cal. San Vicente Ferrer Nro. S/n (Alt. Cervec. Backus a 250 M. Capilla Svf)', '2025-05-28 15:53:46', NULL, 2, NULL, 1, 7),
(279, 'JOSE MANUEL', 'HERNANDEZ', 'SARAVIA', '941895694', 'DNI', '72015783', '', 'Cal. San Vicente Ferrer Nro. S/n (Alt. Cervec. Backus a 250 M. Capilla Svf)', '2025-05-29 16:28:38', NULL, 2, NULL, 1, 10),
(280, 'YENNY JACKELINE', 'SARAVIA', 'RAMOS', '928209520', 'DNI', '47308843', '', 'miguel grau mz g lt 10', '2025-05-29 21:09:32', NULL, 2, NULL, 1, 2),
(281, 'VERONICA ZORAIDA', 'CHOQUEHUANCA', 'JUSCA', '966886635', 'DNI', '42965749', '', 'A.v Junin 520', '2025-06-06 10:53:49', NULL, 4, NULL, 1, 10),
(282, 'FABRIZIO PAUL', 'YATACO', 'MARTINEZ', '925832334', 'DNI', '75187082', '', 'LOMO LARGO SUMANPE N|145', '2025-06-11 14:45:20', NULL, 2, NULL, 1, 7),
(283, 'KAROL VANESSA', 'APARCANA', 'MENESES', '934887483', 'DNI', '42041031', '', 'CHABALINA', '2025-06-11 15:04:41', NULL, 2, NULL, 1, 1);

--
-- Disparadores `personas`
--
DELIMITER $$
CREATE TRIGGER `after_insert_persona` AFTER INSERT ON `personas` FOR EACH ROW BEGIN
    INSERT INTO clientes (idpersona, idempresa) 
    VALUES (NEW.idpersona, NULL);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `idrol` int(11) NOT NULL,
  `rol` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`idrol`, `rol`, `descripcion`) VALUES
(1, 'Super Administrador', 'Acceso total sin restricciones, puede gestionar cualquier módulo del sistema.'),
(2, 'Administrador', 'Gestiona usuarios, configuraciones y supervisa el sistema.'),
(3, 'Técnico', 'Encargado del soporte técnico y mantenimiento del sistema.'),
(4, 'Admisión', 'Encargado de gestionar los ingresos y la documentación de nuevos usuarios.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `idservicio` int(11) NOT NULL,
  `nombre_servicio` varchar(255) DEFAULT NULL,
  `precio_sugerido` decimal(10,2) DEFAULT NULL,
  `iddetservicio` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`idservicio`, `nombre_servicio`, `precio_sugerido`, `iddetservicio`) VALUES
(30, 'MANTENIMIENTO PREVENTIVO', 10.00, 10),
(35, 'MANTENIMIENTO PREVENTIVO', 10.00, 5),
(36, 'FORMATEO  DE SISTEMA OPERATIVO A WINDOWNS 11', 30.00, 5),
(37, 'MANTENIMIENTO PREVENTIVO', 20.00, 18),
(38, 'MANTENIMIENTO PREVENTIVO', 20.00, 19),
(39, 'MANTENIMIENTO PREVENTIVO', 10.00, 19),
(41, 'reparación de visagra', 70.00, 20),
(42, 'REPARACION DE VISAGRAS', 170.00, 18),
(43, 'MANTENIMIENTO', 40.00, 22),
(44, 'repracion de visagra', 0.00, 22),
(45, 'MANTENIMIENTO PREVENTIVO', 40.00, 26),
(46, 'MANTENIMIENTO PREVENTIVO', 40.00, 25),
(47, 'MANTENIMIENTO PREVENTIVO', 40.00, 17),
(48, 'MANTENIMIENTO PREVENTIVO', 40.00, 14),
(49, 'MANTENIMIENTO PREVENTIVO', 40.00, 27),
(50, 'FORMATEO  DE SISTEMA OPERATIVO A WINDOWNS 11', 40.00, 27),
(51, 'MANTENIMIENTO', 40.00, 29),
(52, 'REPARACION DE CABEZAL', 20.00, 29),
(53, 'MANTENIMIENTO PREVENTIVO', 40.00, 31);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcategoria`
--

CREATE TABLE `subcategoria` (
  `id_subcategoria` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `Nombre_SubCategoria` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `subcategoria`
--

INSERT INTO `subcategoria` (`id_subcategoria`, `id_categoria`, `Nombre_SubCategoria`) VALUES
(1, 3, 'Tinta continua'),
(2, 3, 'Cartucho de tinta'),
(3, 3, 'Láser (Tóner)'),
(4, 3, 'Matriz de punto'),
(5, 3, 'Sublimación'),
(6, 3, 'Térmica'),
(7, 1, 'Escritorio'),
(8, 1, 'All-in-One'),
(9, 1, 'Workstation'),
(10, 1, 'Servidor'),
(11, 1, 'Gaming'),
(12, 1, 'Ofimática'),
(13, 1, 'Mini PC'),
(14, 2, 'Ultrabook'),
(15, 2, 'Notebook'),
(16, 2, 'Convertible 2 en 1'),
(17, 2, 'Gaming'),
(18, 2, 'Workstation'),
(19, 2, 'Chromebook'),
(20, 2, 'Básica para oficina');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `idusuario` int(11) NOT NULL,
  `idcontrato` int(11) DEFAULT NULL,
  `namuser` varchar(255) DEFAULT NULL,
  `passuser` varchar(255) DEFAULT NULL,
  `estado` tinyint(4) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idusuario`, `idcontrato`, `namuser`, `passuser`, `estado`, `fecha_creacion`, `creado_por`) VALUES
(1, 1, 'superadmin', 'superadmin123', 1, '2025-03-14 14:30:29', 0),
(2, 2, 'admin', 'admin123', 1, '2025-03-14 14:30:29', 2),
(3, 3, 'tecnico', 'tecnico', 1, '2025-03-14 14:30:29', 0),
(4, 4, 'admision', 'admision', 1, '2025-03-14 14:30:29', 0),
(5, 8, 'max', 'max123', 1, '2025-04-28 15:17:39', 5),
(7, 9, 'andrea', 'andrea123', 1, '2025-05-17 20:04:00', 2),
(17, 18, 'lucia', 'lucia123', 1, '2025-05-28 15:35:00', 2),
(18, 19, 'diggy', 'diggy', 1, '2025-05-28 15:54:00', 18),
(19, 20, 'josemanuel', '1234', 1, '2025-05-29 16:29:00', 19),
(20, 21, 'FABRIZIO', 'FABRIZIO123', 1, '2025-06-11 14:46:00', 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `caracteristicas`
--
ALTER TABLE `caracteristicas`
  ADD PRIMARY KEY (`id_caracteristica`),
  ADD KEY `fk_caracteristica_especificacion` (`id_especificacion`),
  ADD KEY `fk_caracteristicas_detequipos` (`iddetequipo`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`idcliente`),
  ADD KEY `idpersona` (`idpersona`),
  ADD KEY `idempresa` (`idempresa`);

--
-- Indices de la tabla `contratos`
--
ALTER TABLE `contratos`
  ADD PRIMARY KEY (`idcontrato`),
  ADD KEY `idpersona` (`idpersona`),
  ADD KEY `idrol` (`idrol`);

--
-- Indices de la tabla `detalle_servicios`
--
ALTER TABLE `detalle_servicios`
  ADD PRIMARY KEY (`iddetservicio`),
  ADD KEY `iddetequipo` (`iddetequipo`),
  ADD KEY `idusuario_soporte` (`idusuario_soporte`);

--
-- Indices de la tabla `detequipos`
--
ALTER TABLE `detequipos`
  ADD PRIMARY KEY (`iddetequipo`),
  ADD UNIQUE KEY `idmarcasoc` (`idmarcasoc`),
  ADD KEY `fk_idEvidencia` (`idEvidencia`),
  ADD KEY `fk_idorden_servicio` (`idorden_servicio`);

--
-- Indices de la tabla `distritos`
--
ALTER TABLE `distritos`
  ADD PRIMARY KEY (`iddistrito`);

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`idempresa`),
  ADD KEY `iddistritos` (`iddistrito`),
  ADD KEY `createBy` (`creado_por`),
  ADD KEY `modifiedBy` (`modificado_por`);

--
-- Indices de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  ADD PRIMARY KEY (`idEspecialidad`);

--
-- Indices de la tabla `especialidades_tecnicos`
--
ALTER TABLE `especialidades_tecnicos`
  ADD PRIMARY KEY (`idhabilidad_de_usuario`),
  ADD KEY `idusuario` (`idusuario`),
  ADD KEY `idSkills` (`idEspecialidad`);

--
-- Indices de la tabla `especificaciones`
--
ALTER TABLE `especificaciones`
  ADD PRIMARY KEY (`id_especificacion`);

--
-- Indices de la tabla `evidencias_entrada`
--
ALTER TABLE `evidencias_entrada`
  ADD PRIMARY KEY (`idEvidencia`);

--
-- Indices de la tabla `evidencia_tecnica`
--
ALTER TABLE `evidencia_tecnica`
  ADD PRIMARY KEY (`idEvidencia_Tecnica`),
  ADD KEY `fk_iddetservicio` (`iddetservicio`);

--
-- Indices de la tabla `marcas`
--
ALTER TABLE `marcas`
  ADD PRIMARY KEY (`id_marca`);

--
-- Indices de la tabla `marcasasoc`
--
ALTER TABLE `marcasasoc`
  ADD PRIMARY KEY (`idmarcasoc`),
  ADD KEY `id_marca` (`id_marca`),
  ADD KEY `id_subcategoria` (`id_subcategoria`);

--
-- Indices de la tabla `orden_de_servicios`
--
ALTER TABLE `orden_de_servicios`
  ADD PRIMARY KEY (`idorden_Servicio`),
  ADD KEY `fk_idusuario_crea` (`idusuario_crea`),
  ADD KEY `fk_idcliente` (`idcliente`);

--
-- Indices de la tabla `personas`
--
ALTER TABLE `personas`
  ADD PRIMARY KEY (`idpersona`),
  ADD UNIQUE KEY `numerodoc` (`numerodoc`),
  ADD KEY `iddistritos` (`iddistrito`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`idrol`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`idservicio`),
  ADD KEY `fk_iddetalleservicio` (`iddetservicio`);

--
-- Indices de la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD PRIMARY KEY (`id_subcategoria`),
  ADD KEY `fk_categoria` (`id_categoria`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`idusuario`),
  ADD KEY `idcontrato` (`idcontrato`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `caracteristicas`
--
ALTER TABLE `caracteristicas`
  MODIFY `id_caracteristica` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `idcliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;

--
-- AUTO_INCREMENT de la tabla `contratos`
--
ALTER TABLE `contratos`
  MODIFY `idcontrato` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `detalle_servicios`
--
ALTER TABLE `detalle_servicios`
  MODIFY `iddetservicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `detequipos`
--
ALTER TABLE `detequipos`
  MODIFY `iddetequipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- AUTO_INCREMENT de la tabla `distritos`
--
ALTER TABLE `distritos`
  MODIFY `iddistrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `idempresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `especialidades`
--
ALTER TABLE `especialidades`
  MODIFY `idEspecialidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `especialidades_tecnicos`
--
ALTER TABLE `especialidades_tecnicos`
  MODIFY `idhabilidad_de_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `especificaciones`
--
ALTER TABLE `especificaciones`
  MODIFY `id_especificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `evidencias_entrada`
--
ALTER TABLE `evidencias_entrada`
  MODIFY `idEvidencia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT de la tabla `evidencia_tecnica`
--
ALTER TABLE `evidencia_tecnica`
  MODIFY `idEvidencia_Tecnica` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `marcas`
--
ALTER TABLE `marcas`
  MODIFY `id_marca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `marcasasoc`
--
ALTER TABLE `marcasasoc`
  MODIFY `idmarcasoc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=228;

--
-- AUTO_INCREMENT de la tabla `orden_de_servicios`
--
ALTER TABLE `orden_de_servicios`
  MODIFY `idorden_Servicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT de la tabla `personas`
--
ALTER TABLE `personas`
  MODIFY `idpersona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=284;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `idrol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `idservicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT de la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  MODIFY `id_subcategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `idusuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `caracteristicas`
--
ALTER TABLE `caracteristicas`
  ADD CONSTRAINT `fk_caracteristica_especificacion` FOREIGN KEY (`id_especificacion`) REFERENCES `especificaciones` (`id_especificacion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_caracteristicas_detequipos` FOREIGN KEY (`iddetequipo`) REFERENCES `detequipos` (`iddetequipo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`idpersona`) REFERENCES `personas` (`idpersona`),
  ADD CONSTRAINT `clientes_ibfk_2` FOREIGN KEY (`idempresa`) REFERENCES `empresas` (`idempresa`);

--
-- Filtros para la tabla `contratos`
--
ALTER TABLE `contratos`
  ADD CONSTRAINT `contratos_ibfk_1` FOREIGN KEY (`idpersona`) REFERENCES `personas` (`idpersona`),
  ADD CONSTRAINT `contratos_ibfk_2` FOREIGN KEY (`idrol`) REFERENCES `roles` (`idrol`);

--
-- Filtros para la tabla `detalle_servicios`
--
ALTER TABLE `detalle_servicios`
  ADD CONSTRAINT `detalle_servicios_ibfk_1` FOREIGN KEY (`iddetequipo`) REFERENCES `detequipos` (`iddetequipo`),
  ADD CONSTRAINT `detalle_servicios_ibfk_2` FOREIGN KEY (`idusuario_soporte`) REFERENCES `usuarios` (`idusuario`);

--
-- Filtros para la tabla `detequipos`
--
ALTER TABLE `detequipos`
  ADD CONSTRAINT `fk_detequipos_marcasoc` FOREIGN KEY (`idmarcasoc`) REFERENCES `marcasasoc` (`idmarcasoc`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_idEvidencia` FOREIGN KEY (`idEvidencia`) REFERENCES `evidencias_entrada` (`idEvidencia`),
  ADD CONSTRAINT `fk_idorden_servicio` FOREIGN KEY (`idorden_servicio`) REFERENCES `orden_de_servicios` (`idorden_Servicio`);

--
-- Filtros para la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD CONSTRAINT `empresas_ibfk_1` FOREIGN KEY (`iddistrito`) REFERENCES `distritos` (`iddistrito`),
  ADD CONSTRAINT `empresas_ibfk_2` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`idusuario`),
  ADD CONSTRAINT `empresas_ibfk_3` FOREIGN KEY (`modificado_por`) REFERENCES `usuarios` (`idusuario`);

--
-- Filtros para la tabla `especialidades_tecnicos`
--
ALTER TABLE `especialidades_tecnicos`
  ADD CONSTRAINT `especialidades_tecnicos_ibfk_1` FOREIGN KEY (`idusuario`) REFERENCES `usuarios` (`idusuario`),
  ADD CONSTRAINT `especialidades_tecnicos_ibfk_2` FOREIGN KEY (`idEspecialidad`) REFERENCES `especialidades` (`idEspecialidad`);

--
-- Filtros para la tabla `evidencia_tecnica`
--
ALTER TABLE `evidencia_tecnica`
  ADD CONSTRAINT `fk_iddetservicio` FOREIGN KEY (`iddetservicio`) REFERENCES `detalle_servicios` (`iddetservicio`);

--
-- Filtros para la tabla `marcasasoc`
--
ALTER TABLE `marcasasoc`
  ADD CONSTRAINT `marcasasoc_ibfk_1` FOREIGN KEY (`id_marca`) REFERENCES `marcas` (`id_marca`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `marcasasoc_ibfk_2` FOREIGN KEY (`id_subcategoria`) REFERENCES `subcategoria` (`id_subcategoria`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `orden_de_servicios`
--
ALTER TABLE `orden_de_servicios`
  ADD CONSTRAINT `fk_idcliente` FOREIGN KEY (`idcliente`) REFERENCES `clientes` (`idcliente`),
  ADD CONSTRAINT `fk_idusuario_crea` FOREIGN KEY (`idusuario_crea`) REFERENCES `usuarios` (`idusuario`);

--
-- Filtros para la tabla `personas`
--
ALTER TABLE `personas`
  ADD CONSTRAINT `personas_ibfk_1` FOREIGN KEY (`iddistrito`) REFERENCES `distritos` (`iddistrito`);

--
-- Filtros para la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD CONSTRAINT `fk_iddetalleservicio` FOREIGN KEY (`iddetservicio`) REFERENCES `detalle_servicios` (`iddetservicio`);

--
-- Filtros para la tabla `subcategoria`
--
ALTER TABLE `subcategoria`
  ADD CONSTRAINT `fk_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`idcontrato`) REFERENCES `contratos` (`idcontrato`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
