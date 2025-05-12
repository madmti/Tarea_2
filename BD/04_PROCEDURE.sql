DELIMITER $$

CREATE PROCEDURE filtrar_articulos (
    IN p_id_autor INT,
    IN p_fecha_inicio DATE,
    IN p_fecha_fin DATE,
    IN p_id_categoria INT,
    IN p_id_revisor INT,
    IN p_titulo VARCHAR(50)
)
BEGIN
    SELECT DISTINCT
        a.titulo,
        a.resumen,
        a.fecha_envio,
        a.id_articulo,
        GROUP_CONCAT(DISTINCT c.nombre SEPARATOR ', ') AS topicos
    FROM articulos_aprobados a
    LEFT JOIN propiedad p ON a.id_articulo = p.id_articulo
    LEFT JOIN topico t ON a.id_articulo = t.id_articulo
    LEFT JOIN categoria c ON t.id_categoria = c.id_categoria
    LEFT JOIN revision r ON a.id_articulo = r.id_articulo
    WHERE
        (p_id_autor IS NULL OR p.id_autor = p_id_autor)
        AND (p_fecha_inicio IS NULL OR a.fecha_envio >= p_fecha_inicio)
        AND (p_fecha_fin IS NULL OR a.fecha_envio <= p_fecha_fin)
        AND (p_id_categoria IS NULL OR c.id_categoria = p_id_categoria)
        AND (p_id_revisor IS NULL OR r.id_revisor = p_id_revisor)
        AND (p_titulo IS NULL OR a.titulo LIKE CONCAT('%', p_titulo, '%'))
    GROUP BY a.id_articulo
    ORDER BY a.fecha_envio DESC;
END$$


CREATE PROCEDURE insertar_articulo(
    IN p_titulo VARCHAR(50),
    IN p_resumen VARCHAR(150),
    OUT p_id_articulo INT
)
BEGIN
    INSERT INTO articulo (titulo, resumen, fecha_envio, aprobado)
    VALUES (p_titulo, p_resumen, DATE_ADD(CURDATE(), INTERVAL 3 MONTH), NULL);
    SET p_id_articulo = LAST_INSERT_ID();
END$$


CREATE PROCEDURE mis_articulos(
    IN p_id_autor INT
)
BEGIN
    SELECT 
        a.id_articulo,
        a.titulo,
        a.fecha_envio,
        a.aprobado
    FROM articulo a
    JOIN propiedad p ON a.id_articulo = p.id_articulo
    WHERE p.id_autor = p_id_autor AND p.es_contacto = TRUE
    ORDER BY a.fecha_envio DESC;
END$$


CREATE PROCEDURE actualizar_usuario(
    IN p_id_usuario INT,
    IN p_nombre VARCHAR(85),
    IN p_email VARCHAR(95),
    IN p_contrasena VARCHAR(60)
)
BEGIN
    UPDATE usuario
    SET 
        nombre = COALESCE(p_nombre, nombre),
        email = COALESCE(p_email, email),
        contrasena = COALESCE(p_contrasena, contrasena)
    WHERE id_usuario = p_id_usuario;
END$$


CREATE PROCEDURE obtener_articulos_full(
    IN p_id_articulo INT
)
BEGIN
    SELECT
        a.id_articulo,
        a.titulo,
        a.resumen,
        a.fecha_envio,
        GROUP_CONCAT(DISTINCT cat.nombre SEPARATOR ', ') AS topicos,
        GROUP_CONCAT(DISTINCT aut.nombre SEPARATOR ', ') AS autores
    FROM articulos_aprobados a
    LEFT JOIN topico t ON a.id_articulo = t.id_articulo
    LEFT JOIN categoria cat ON t.id_categoria = cat.id_categoria
    LEFT JOIN propiedad p ON a.id_articulo = p.id_articulo
    LEFT JOIN usuario aut ON p.id_autor = aut.id_usuario
    WHERE a.id_articulo = p_id_articulo
    GROUP BY a.id_articulo;
END$$


CREATE PROCEDURE obtener_revisiones_revisor(
    IN p_id_revisor INT
)
BEGIN
    SELECT 
        r.id_articulo,
        a.titulo AS titulo_articulo,
        r.fecha_emision,
        a.fecha_envio,
        r.estado
    FROM revision r
    JOIN articulo a ON r.id_articulo = a.id_articulo
    WHERE r.id_revisor = p_id_revisor AND r.estado IS NULL
    ORDER BY a.fecha_envio DESC, r.fecha_emision DESC;
END$$


CREATE PROCEDURE obtener_revision_full(
    IN p_id_revisor INT,
    IN p_id_articulo INT
)
BEGIN
    SELECT 
        r.*,
        a.titulo AS titulo_articulo,
        a.resumen AS resumen_articulo,
        a.fecha_envio,
        GROUP_CONCAT(DISTINCT CONCAT(aut.nombre, ' (', aut.email, ')') SEPARATOR ', ') AS autores
    FROM revision r
    JOIN articulo a ON r.id_articulo = a.id_articulo
    LEFT JOIN propiedad p ON a.id_articulo = p.id_articulo
    LEFT JOIN usuario aut ON p.id_autor = aut.id_usuario
    WHERE r.id_revisor = p_id_revisor AND a.id_articulo = p_id_articulo
    GROUP BY r.id_revisor, r.id_articulo, a.titulo, a.resumen, a.fecha_envio, r.fecha_emision, r.estado;
END$$


CREATE PROCEDURE actualizar_revision(
    IN p_id_articulo INT,
    IN p_id_revisor INT,
    IN p_calidad_tecnica INT,
    IN p_originalidad INT,
    IN p_valoracion_global INT,
    IN p_estado BOOLEAN,
    IN p_argumentos TEXT
)
BEGIN
    UPDATE revision
    SET 
        calidad_tecnica = p_calidad_tecnica,
        originalidad = p_originalidad,
        valoracion_global = p_valoracion_global,
        estado = p_estado,
        argumentos = CAST(p_argumentos AS JSON),
        fecha_emision = NOW()
    WHERE id_articulo = p_id_articulo
      AND id_revisor = p_id_revisor
      AND estado IS NULL;
END$$


CREATE PROCEDURE obtener_detalles_articulo(
    IN p_id_articulo INT
)
BEGIN
    SELECT 
        u.nombre AS nombre_revisor,
        u.email AS email_revisor,
        r.fecha_emision,
        r.calidad_tecnica,
        r.originalidad,
        r.valoracion_global,
        r.estado,
        r.argumentos
    FROM revision r
    JOIN usuario u ON r.id_revisor = u.id_usuario
    WHERE r.id_articulo = p_id_articulo
    ORDER BY r.fecha_emision DESC;
END$$


CREATE PROCEDURE obtener_autores_asociados(
    IN p_id_articulo INT
)
BEGIN
    SELECT 
        u.id_usuario AS id_usuario,
        u.nombre AS nombre,
        u.email AS email,
        p.es_contacto AS es_contacto
    FROM propiedad p
    JOIN usuario u ON p.id_autor = u.id_usuario
    WHERE p.id_articulo = p_id_articulo;
END$$


CREATE PROCEDURE es_propietario(
    IN p_id_usuario INT
)
BEGIN
    SELECT
        COUNT(*) > 0 AS es_propietario
    FROM propietarios
    WHERE id_usuario = p_id_usuario;
END$$


CREATE PROCEDURE asignar_revisores(
    IN p_id_articulo INT
)
BEGIN
    -- Asignar las revisiones para los 3 revisores seleccionados
    INSERT INTO revision (id_articulo, id_revisor)
    SELECT 
        p_id_articulo,
        revisores_seleccionados.id_usuario
    FROM (
        -- Seleccionar TOP 3 revisores que están mas desocupados (coincidiendo topicos-especialidades)
        SELECT r.id_usuario
        FROM usuario r
        LEFT JOIN revision rev ON r.id_usuario = rev.id_revisor AND rev.estado IS NULL
        -- Descartar revisores que son propietarios del articulo
        WHERE r.tipo = 'REV'
          AND r.id_usuario NOT IN (
              -- Propietarios del articulo
              SELECT p.id_autor
              FROM propiedad p
              WHERE p.id_articulo = p_id_articulo
          )
          AND EXISTS (
              -- Hacer coincidir topicos-especialidades
              SELECT 1
              FROM topico t
              JOIN especialidad e ON t.id_categoria = e.id_categoria
              WHERE t.id_articulo = p_id_articulo
                AND e.id_revisor = r.id_usuario
          )
        GROUP BY r.id_usuario
        ORDER BY COUNT(rev.id_articulo) ASC
        LIMIT 3
    ) AS revisores_seleccionados;
END$$


CREATE PROCEDURE obtener_revisor_categoria(
    IN p_id_usuario INT
)
BEGIN
    SELECT 
        u.id_usuario,
        u.nombre,
        u.email,
        c.id_categoria,
        c.nombre AS nombre_categoria
    FROM usuario u
    JOIN especialidad e ON u.id_usuario = e.id_revisor
    JOIN categoria c ON e.id_categoria = c.id_categoria
    WHERE u.id_usuario = p_id_usuario AND u.tipo = 'REV';
END$$


CREATE PROCEDURE eliminar_articulo(
    IN p_id_articulo INT,
    IN p_id_autor INT
)
BEGIN
    IF es_autor_contacto(p_id_articulo, p_id_autor) THEN
        DELETE FROM articulo
        WHERE id_articulo = p_id_articulo;
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El autor no es el contacto del artículo o el artículo no existe.';
    END IF;
END$$

DELIMITER ;