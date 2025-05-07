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
        GROUP_CONCAT(DISTINCT c.nombre SEPARATOR ', ') AS topicos
    FROM articulo a
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
    GROUP BY a.id_articulo;
END$$

DELIMITER ;
