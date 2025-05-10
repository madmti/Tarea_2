
CREATE VIEW articulos_aprobados AS
SELECT *
FROM articulo
WHERE aprobado = TRUE;

CREATE VIEW articulos_pendientes AS
SELECT *
FROM articulo
WHERE aprobado IS NULL;

CREATE VIEW articulos_asignacion_incompleta AS
SELECT art.*
FROM articulo art
LEFT JOIN revision r ON art.id_articulo = r.id_articulo
GROUP BY art.id_articulo
HAVING COUNT(r.id_revisor) < 3;

CREATE VIEW usuarios_publico AS
SELECT id_usuario, rut, email, nombre, tipo
FROM usuario;

CREATE VIEW propietarios AS
SELECT DISTINCT u.id_usuario, u.rut, u.email, u.nombre, u.tipo
FROM usuario u
INNER JOIN propiedad p ON u.id_usuario = p.id_autor;

CREATE VIEW revisores_especialidades AS
SELECT DISTINCT u.id_usuario, u.rut, u.email, u.nombre, c.id_categoria, c.nombre AS nombre_categoria
FROM usuario u
INNER JOIN especialidad e ON u.id_usuario = e.id_revisor
INNER JOIN categoria c ON e.id_categoria = c.id_categoria
WHERE u.tipo = 'REV'
ORDER BY u.id_usuario, c.id_categoria;

CREATE VIEW articulos_autores_topicos_revisores AS
SELECT DISTINCT 
    a.id_articulo,
    a.titulo,
    aut.nombre AS nombre_autor,
    c.id_categoria,
    c.nombre AS nombre_categoria,
    rev.id_usuario AS id_usuario,
    r.id_revisor AS id_revisor,
    rev.nombre AS nombre_revisor
FROM articulos_pendientes a
INNER JOIN propiedad p ON a.id_articulo = p.id_articulo
INNER JOIN usuario aut ON p.id_autor = aut.id_usuario
INNER JOIN topico t ON a.id_articulo = t.id_articulo
INNER JOIN categoria c ON t.id_categoria = c.id_categoria
LEFT JOIN revision r ON a.id_articulo = r.id_articulo
LEFT JOIN usuario rev ON r.id_revisor = rev.id_usuario
WHERE aut.tipo = 'AUT'
ORDER BY a.id_articulo, aut.nombre, c.nombre, rev.id_usuario;

CREATE VIEW revisores_topicos_articulos AS
SELECT 
    r.id_usuario,
    r.nombre,
    c.id_categoria AS id_categoria,
    c.nombre AS nombre_especialidad,
    a.id_articulo,
    a.titulo,
    rev.estado
FROM usuario r
INNER JOIN especialidad e ON r.id_usuario = e.id_revisor
INNER JOIN categoria c ON e.id_categoria = c.id_categoria
LEFT JOIN revision rev ON r.id_usuario = rev.id_revisor
LEFT JOIN articulo a ON rev.id_articulo = a.id_articulo
WHERE r.tipo = 'REV'
ORDER BY r.id_usuario, c.id_categoria, a.id_articulo;