
CREATE VIEW articulos_aprobados AS
SELECT *
FROM articulo
WHERE aprobado = TRUE;


CREATE VIEW usuarios_publico AS
SELECT id_usuario, rut, email, nombre, tipo
FROM usuario;
