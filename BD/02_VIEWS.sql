
CREATE VIEW articulos_aprobados AS
SELECT *
FROM articulo
WHERE aprobado = TRUE;


CREATE VIEW usuarios_publico AS
SELECT id_usuario, rut, email, nombre, tipo
FROM usuario;

CREATE VIEW propietarios AS
SELECT DISTINCT u.id_usuario, u.rut, u.email, u.nombre, u.tipo
FROM usuario u
INNER JOIN propiedad p ON u.id_usuario = p.id_autor;
