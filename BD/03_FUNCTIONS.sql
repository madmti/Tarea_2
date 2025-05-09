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
DELIMITER ;

DELIMITER $$
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
DELIMITER ;

DELIMITER $$

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

DELIMITER ;


DELIMITER $$

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

DELIMITER ;

DELIMITER $$

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
DELIMITER ;