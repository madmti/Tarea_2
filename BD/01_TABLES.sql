-- Categoría
CREATE TABLE IF NOT EXISTS categoria (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(85) NOT NULL
);

-- Artículo
CREATE TABLE IF NOT EXISTS articulo (
    id_articulo INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(50) NOT NULL,
    resumen VARCHAR(150) NOT NULL,
    fecha_envio DATE NOT NULL,
    aprobado BOOLEAN NULL DEFAULT NULL
);

-- Usuario
CREATE TABLE IF NOT EXISTS usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    rut CHAR(10) UNIQUE NOT NULL,
    email VARCHAR(95) UNIQUE NOT NULL,
    nombre VARCHAR(85) UNIQUE NOT NULL,
    contrasena VARCHAR(30) NOT NULL,
    tipo CHAR(3) NOT NULL,
    CHECK (tipo IN ('AUT', 'REV', 'ADM'))
);

-- Tópico (relaciona Artículo y Categoría)
CREATE TABLE IF NOT EXISTS topico (
    id_categoria INT,
    id_articulo INT,
    PRIMARY KEY (id_categoria, id_articulo),
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE CASCADE, -- Si se elimina una categoría, se eliminan los tópicos asociados
    FOREIGN KEY (id_articulo) REFERENCES articulo(id_articulo) ON DELETE CASCADE     -- Si se elimina un artículo, se eliminan los tópicos asociados
);

-- Especialidad (Relaciona Revisor y Categoría)
CREATE TABLE IF NOT EXISTS especialidad (
    id_categoria INT,
    id_revisor INT,
    PRIMARY KEY (id_categoria, id_revisor),
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria) ON DELETE CASCADE, -- Si se elimina una categoría, se eliminan las especialidades asociadas
    FOREIGN KEY (id_revisor) REFERENCES usuario(id_usuario) ON DELETE CASCADE        -- Si se elimina un revisor, se eliminan las especialidades asociadas
);

-- Propiedad (relaciona Autor y Artículo)
CREATE TABLE IF NOT EXISTS propiedad (
    id_articulo INT,
    id_autor INT,
    es_contacto BOOLEAN NOT NULL,
    PRIMARY KEY (id_articulo, id_autor),
    FOREIGN KEY (id_articulo) REFERENCES articulo(id_articulo),
    FOREIGN KEY (id_autor) REFERENCES usuario(id_usuario) ON DELETE RESTRICT         -- No se pueden eliminar autores con artículos asignados
);

-- Revisión (relaciona Artículo y Revisor)
CREATE TABLE IF NOT EXISTS revision (
    id_articulo INT,
    id_revisor INT,
    estado BOOLEAN NULL,

    calidad_tecnica INT NULL,
    originalidad INT NULL,
    valoracion_global INT NULL,
    fecha_emision DATE NULL,
    argumentos JSON NULL,

    PRIMARY KEY (id_articulo, id_revisor),

    FOREIGN KEY (id_articulo) REFERENCES articulo(id_articulo),
    FOREIGN KEY (id_revisor) REFERENCES usuario(id_usuario) ON DELETE RESTRICT,      -- No se pueden eliminar revisores con revisiones asignadas

    CONSTRAINT todo_o_nada
    CHECK (
        (
            calidad_tecnica IS NOT NULL 
            AND originalidad IS NOT NULL 
            AND valoracion_global IS NOT NULL 
            AND fecha_emision IS NOT NULL 
            AND argumentos IS NOT NULL
            AND estado IS NOT NULL
        )
        OR
        (
            calidad_tecnica IS NULL 
            AND originalidad IS NULL 
            AND valoracion_global IS NULL 
            AND fecha_emision IS NULL 
            AND argumentos IS NULL
            AND estado IS NULL
        )
    ) -- Asegura que al enviar todos los campos sean ingresados, y sino se quede en estado pendiente sin completar campos.
);
