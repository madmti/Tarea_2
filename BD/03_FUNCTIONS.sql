DELIMITER $$

CREATE FUNCTION calcular_estado_articulo(p_id_articulo INT)
RETURNS BOOLEAN
DETERMINISTIC
BEGIN
    DECLARE total INT DEFAULT 0;
    DECLARE aprobadas INT DEFAULT 0;
    DECLARE rechazadas INT DEFAULT 0;

    SELECT COUNT(*) INTO total
    FROM revision
    WHERE id_articulo = p_id_articulo;

    SELECT COUNT(*) INTO aprobadas
    FROM revision
    WHERE id_articulo = p_id_articulo AND estado = TRUE;

    SELECT COUNT(*) INTO rechazadas
    FROM revision
    WHERE id_articulo = p_id_articulo AND estado = FALSE;

    IF total >= 3 THEN
        IF aprobadas >= 3 THEN
            RETURN TRUE; -- Aprobado
        ELSEIF rechazadas > 0 THEN
            RETURN FALSE; -- Rechazado
        ELSE
            RETURN NULL; -- Pendiente
        END IF;
    ELSE
        RETURN NULL; -- Aun no hay 3 revisiones
    END IF;
END$$

CREATE FUNCTION es_autor_contacto(p_id_articulo INT, p_id_autor INT)
RETURNS BOOLEAN
DETERMINISTIC
BEGIN
    DECLARE resultado BOOLEAN;

    SELECT EXISTS (
        SELECT 1
        FROM propiedad
        WHERE id_articulo = p_id_articulo
          AND id_autor = p_id_autor
          AND es_contacto = TRUE
    ) INTO resultado;

    RETURN resultado;
END$$

DELIMITER ;
