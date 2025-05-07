
CREATE VIEW articulos_aprobados AS
SELECT titulo, resumen, fecha_envio
FROM articulo
WHERE aprobado = TRUE;
