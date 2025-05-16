
-- TRIGGER para la columna calculada articulo.aprobado
DELIMITER $$

CREATE TRIGGER trigger_calcular_aprobado_insert
AFTER INSERT ON revision
FOR EACH ROW
BEGIN
    UPDATE articulo 
    SET aprobado = calcular_estado_articulo(NEW.id_articulo)
    WHERE id_articulo = NEW.id_articulo;
END$$

CREATE TRIGGER trigger_calcular_aprobado_update
AFTER UPDATE ON revision
FOR EACH ROW
BEGIN
    UPDATE articulo 
    SET aprobado = calcular_estado_articulo(NEW.id_articulo)
    WHERE id_articulo = NEW.id_articulo;
END$$

CREATE TRIGGER trigger_calcular_aprobado_delete
AFTER DELETE ON revision
FOR EACH ROW
BEGIN
    UPDATE articulo 
    SET aprobado = calcular_estado_articulo(OLD.id_articulo)
    WHERE id_articulo = OLD.id_articulo;
END$$


-- TRIIGER para verificar que el revisor no sea autor del articulo

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

CREATE TRIGGER trigger_limitar_revisiones_insert
BEFORE INSERT ON revision
FOR EACH ROW
BEGIN
    DECLARE total_revisiones INT;

    SELECT COUNT(*) INTO total_revisiones
    FROM revision
    WHERE id_articulo = NEW.id_articulo;

    IF total_revisiones >= 3 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'No se pueden agregar más de 3 revisiones a un artículo';
    END IF;
END$$

DELIMITER ;