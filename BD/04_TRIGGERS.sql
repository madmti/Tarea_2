
-- TRIGGER para la columna calculada articulo.aprobado

DELIMITER $$

CREATE TRIGGER trigger_calcular_aprobado_insert
AFTER INSERT ON revision
FOR EACH ROW
BEGIN
    DECLARE total_revisiones INT;
    DECLARE revisiones_aprobadas INT;
    DECLARE revisiones_rechazadas INT;

    -- Lógica común
    SELECT COUNT(*) INTO total_revisiones
    FROM revision
    WHERE id_articulo = NEW.id_articulo;

    SELECT COUNT(*) INTO revisiones_aprobadas
    FROM revision
    WHERE id_articulo = NEW.id_articulo AND estado = TRUE;

    SELECT COUNT(*) INTO revisiones_rechazadas
    FROM revision
    WHERE id_articulo = NEW.id_articulo AND estado = FALSE;

    IF total_revisiones = 3 THEN
        IF revisiones_aprobadas = total_revisiones THEN
            UPDATE articulo SET aprobado = TRUE WHERE id_articulo = NEW.id_articulo;
        ELSEIF revisiones_rechazadas > 0 THEN
            UPDATE articulo SET aprobado = FALSE WHERE id_articulo = NEW.id_articulo;
        ELSE
            UPDATE articulo SET aprobado = NULL WHERE id_articulo = NEW.id_articulo;
        END IF;
    ELSE
        UPDATE articulo SET aprobado = NULL WHERE id_articulo = NEW.id_articulo;
    END IF;
END$$

CREATE TRIGGER trigger_calcular_aprobado_update
AFTER UPDATE ON revision
FOR EACH ROW
BEGIN
    DECLARE total_revisiones INT;
    DECLARE revisiones_aprobadas INT;
    DECLARE revisiones_rechazadas INT;

    -- Lógica común
    SELECT COUNT(*) INTO total_revisiones
    FROM revision
    WHERE id_articulo = NEW.id_articulo;

    SELECT COUNT(*) INTO revisiones_aprobadas
    FROM revision
    WHERE id_articulo = NEW.id_articulo AND estado = TRUE;

    SELECT COUNT(*) INTO revisiones_rechazadas
    FROM revision
    WHERE id_articulo = NEW.id_articulo AND estado = FALSE;

    IF total_revisiones = 3 THEN
        IF revisiones_aprobadas = total_revisiones THEN
            UPDATE articulo SET aprobado = TRUE WHERE id_articulo = NEW.id_articulo;
        ELSEIF revisiones_rechazadas > 0 THEN
            UPDATE articulo SET aprobado = FALSE WHERE id_articulo = NEW.id_articulo;
        ELSE
            UPDATE articulo SET aprobado = NULL WHERE id_articulo = NEW.id_articulo;
        END IF;
    ELSE
        UPDATE articulo SET aprobado = NULL WHERE id_articulo = NEW.id_articulo;
    END IF;
END$$

DELIMITER ;

-- TODO calcular_aprobado_delete !!!!!!!!!!!!!!!!!!!!!!!!!!!!!<------------------------------------

-- TRIIGER para verificar que el revisor no sea autor del articulo

DELIMITER $$

CREATE TRIGGER trigger_verificar_revisor_no_autor_insert
BEFORE INSERT ON revision
FOR EACH ROW
BEGIN
    IF EXISTS (
        SELECT 1 FROM propiedad
        WHERE id_articulo = NEW.id_articulo
          AND id_autor = NEW.id_revisor
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El revisor no puede ser autor del artículo';
    END IF;
END$$

CREATE TRIGGER trigger_verificar_revisor_no_autor_update
BEFORE UPDATE ON revision
FOR EACH ROW
BEGIN
    IF EXISTS (
        SELECT 1 FROM propiedad
        WHERE id_articulo = NEW.id_articulo
          AND id_autor = NEW.id_revisor
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El revisor no puede ser autor del artículo';
    END IF;
END$$

DELIMITER ;
