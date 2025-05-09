<?php
namespace API;

class Functions {
    /**
     * ================================================================
     *                              CREATE
     * ================================================================
     */
    /**
     * Data: titulo, resumen.
     */
    public static function insertarArticulo(\PDO $pdo, mixed $data): ?int {
        try {
            $stmt = $pdo->prepare("CALL insertar_articulo(:titulo, :resumen, @id_articulo)");
            $stmt->execute([
                'titulo' => $data['titulo'],
                'resumen' => $data['resumen']
            ]);
            $stmt = $pdo->query("SELECT @id_articulo AS id_articulo");
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error al insertar artículo con procedimiento: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Topicos: array de IDs de topicos.
     */
    public static function insertarTopicos(\PDO $pdo, int $idArticulo, array $topicos): bool {
        try {
            $stmt = $pdo->prepare("INSERT INTO topico (id_categoria, id_articulo) VALUES (:id_categoria, :id_articulo)");
            foreach ($topicos as $topico) {
                $stmt->execute([
                    'id_categoria' => $topico,
                    'id_articulo' => $idArticulo
                ]);
            }
            return true;
        } catch (PDOException $e) {
            error_log("Error al insertar tópicos: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Autores: array de IDs de autores.
     */
    public static function insertarAutores(\PDO $pdo, int $idArticulo, array $autores): bool {
        try {
            $stmt = $pdo->prepare("INSERT INTO propiedad (id_articulo, id_autor, es_contacto) VALUES (:id_articulo, :id_autor, :es_contacto)");
            foreach ($autores as $index => $autor) {
                $stmt->execute([
                    'id_articulo' => $idArticulo,
                    'id_autor' => $autor,
                    'es_contacto' => $index === 0 ? 1 : 0
                ]);
            }
            return true;
        } catch (PDOException $e) {
            error_log("Error al insertar autores: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Registrar un usuario autor.
     * Data: rut, email, nombre, contrasena, tipo
     */
    public static function registrarUsuarioAutor(\PDO $pdo, array $data): ?int {
        try {
            $stmt = $pdo->prepare("INSERT INTO usuario (rut, email, nombre, contrasena, tipo) VALUES (:rut, :email, :nombre, :contrasena, :tipo)");
            $stmt->execute([
                'rut' => $data['rut'],
                'email' => $data['email'],
                'nombre' => $data['nombre'],
                'contrasena' => password_hash($data['contrasena'], PASSWORD_BCRYPT),
                'tipo' => 'AUT',
            ]);
            return (int) $pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Error al registrar usuario: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Registrar un usuario cualquiera.
     * Data: rut, email, nombre, contrasena, tipo
     */
    public static function registrarUsuario(\PDO $pdo, array $data): ?int {
        try {
            $stmt = $pdo->prepare("INSERT INTO usuario (rut, email, nombre, contrasena, tipo) VALUES (:rut, :email, :nombre, :contrasena, :tipo)");
            $stmt->execute([
                'rut' => $data['rut'],
                'email' => $data['email'],
                'nombre' => $data['nombre'],
                'contrasena' => password_hash($data['contrasena'], PASSWORD_BCRYPT),
                'tipo' => $data['tipo'],
            ]);
            return (int) $pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Error al registrar usuario: " . $e->getMessage());
            return null;
        }
    }
    /**
     * fehca_envio + 2 semanas.
     * Data: titulo, resumen, contacto.
     */
    public static function insertarArticuloConFecha(\PDO $pdo, array $data): ?int {
        try {
            $fechaEnvio = (new \DateTime())->modify('+2 weeks')->format('Y-m-d');
            $stmt = $pdo->prepare("INSERT INTO articulo (titulo, resumen, fecha_envio) VALUES (:titulo, :resumen, :fecha_envio)");
            $stmt->execute([
                'titulo' => $data['titulo'],
                'resumen' => $data['resumen'],
                'fecha_envio' => $fechaEnvio
            ]);
            return (int) $pdo->lastInsertId();
        } catch (\PDOException $e) {
            error_log("Error al insertar artículo con fecha: " . $e->getMessage());
            return null;
        }
    }
    public static function insertarTopicosConFecha(\PDO $pdo, int $idArticulo, array $topicos): bool {
        try {
            $stmt = $pdo->prepare("INSERT INTO topico (id_categoria, id_articulo) VALUES (:id_categoria, :id_articulo)");
            foreach ($topicos as $topico) {
                $stmt->execute([
                    'id_categoria' => $topico,
                    'id_articulo' => $idArticulo
                ]);
            }
            return true;
        } catch (\PDOException $e) {
            error_log("Error al insertar tópicos con fecha: " . $e->getMessage());
            return false;
        }
    }
    public static function insertarPropiedad(\PDO $pdo, int $idArticulo, array $autores, int $contacto): bool {
        try {
            $stmt = $pdo->prepare("INSERT INTO propiedad (id_articulo, id_autor, es_contacto) VALUES (:id_articulo, :id_autor, :es_contacto)");
            foreach ($autores as $index => $autor) {
                $stmt->execute([
                    'id_articulo' => $idArticulo,
                    'id_autor' => $autor,
                    'es_contacto' => $autor == $contacto ? 1 : 0
                ]);
            }
            return true;
        } catch (\PDOException $e) {
            error_log("Error al insertar propiedad: " . $e->getMessage());
            return false;
        }
    }
    
     /* ================================================================
     *                              READ
     * ================================================================
     */
    /**
     * Busqueda avanzada de articulos.
     */
    public static function filtrarArticulos(\PDO $pdo, array $queryParams): ?array {
        try {
            $stmt = $pdo->prepare("CALL filtrar_articulos(:autor, :fecha_ini, :fecha_fin, :categoria, :revisor, :titulo)");
            $stmt->execute([
                'autor' => $queryParams['autor'] ?? null,               // ID autor
                'fecha_ini' => $queryParams['desde'] ?? null,           // DATE
                'fecha_fin' => $queryParams['hasta'] ?? null,           // DATE
                'categoria' => $queryParams['categoria_id'] ?? null,    // ID categoria
                'revisor' => $queryParams['revisor_id'] ?? null,        // ID revisor
                'titulo' => $queryParams['titulo'] ?? null              // STRING
            ]);
            $articulos = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $articulos;
        } catch (\PDOException $e) {
            error_log("Error al filtrar artículos: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Obtener artículos por autor de contacto.
     */
    public static function obtenerArticulosPorAutor(\PDO $pdo, int $idAutor): ?array {
        try {
            $stmt = $pdo->prepare("CALL mis_articulos(:id_autor)");
            $stmt->execute(['id_autor' => $idAutor]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener artículos por autor: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Obtener todas las categorias.
     */
    public static function obtenerCategorias(\PDO $pdo): ?array {
        try {
            $stmt = $pdo->query("SELECT * FROM categoria");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener categorías: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Obtener todos los autores.
     */
    public static function obtenerAutores(\PDO $pdo): ?array {
        try {
            $stmt = $pdo->query("SELECT * FROM propietarios");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener autores: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Obtener todos los revisores.
     */
    public static function obtenerRevisores(\PDO $pdo): ?array {
        try {
            $stmt = $pdo->query("SELECT * FROM usuarios_publico WHERE tipo = 'REV'");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener revisores: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Obtener información completa de un artículo por su ID.
     */
    public static function obtenerArticuloFull(\PDO $pdo, int $idArticulo): ?array {
        try {
            $stmt = $pdo->prepare("CALL obtener_articulos_full(:id_articulo)");
            $stmt->execute(['id_articulo' => $idArticulo]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener información completa del artículo: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Obtener revisiones asignadas a un revisor.
     */
    public static function obtenerMisRevisiones(\PDO $pdo, int $idRevisor): ?array {
        try {
            $stmt = $pdo->prepare("CALL obtener_revisiones_revisor(:id_revisor)");
            $stmt->execute(['id_revisor' => $idRevisor]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener revisiones del revisor: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Obtener información completa de una revisión por revisor y artículo.
     */
    public static function obtenerRevisionFull(\PDO $pdo, int $idRevisor, int $idArticulo): ?array {
        try {
            $stmt = $pdo->prepare("CALL obtener_revision_full(:id_revisor, :id_articulo)");
            $stmt->execute([
                'id_revisor' => $idRevisor,
                'id_articulo' => $idArticulo
            ]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener información completa de la revisión: " . $e->getMessage());
            return null;
        }
    }
    public static function obtenerDetallesArticulo(\PDO $pdo, int $idArticulo): ?array {
        try {
            $stmt = $pdo->prepare("CALL obtener_detalles_articulo(:id_articulo)");
            $stmt->execute(['id_articulo' => $idArticulo]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener detalles del artículo: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Obtener todos los datos de un artículo por su ID.
     */
    public static function obtenerArticuloCompleto(\PDO $pdo, int $idArticulo): ?array {
        try {
            $stmt = $pdo->prepare("SELECT * FROM articulo WHERE id_articulo = :id_articulo");
            $stmt->execute(['id_articulo' => $idArticulo]);
            return $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener todos los datos del artículo: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Obtener tópicos asociados a un artículo.
     */
    public static function obtenerTopicos(\PDO $pdo, int $idArticulo): ?array {
        try {
            $stmt = $pdo->prepare("SELECT id_categoria FROM topico WHERE id_articulo = :id_articulo");
            $stmt->execute(['id_articulo' => $idArticulo]);
            return $stmt->fetchAll(\PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            error_log("Error al obtener tópicos: " . $e->getMessage());
            return null;
        }
    }
    /**
     * Obtener autores asociados a un artículo.
     */
    public static function obtenerAutoresAsociados(\PDO $pdo, int $idArticulo): ?array {
        try {
            $stmt = $pdo->prepare("CALL obtener_autores_asociados(:id_articulo)");
            $stmt->execute(['id_articulo' => $idArticulo]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error al obtener autores asociados: " . $e->getMessage());
            return null;
        }
    }
    /**
     * ================================================================
     *                              UPDATE
     * ================================================================
     */
    /**
     * Data: titulo, resumen.
     */
    public static function actualizarArticulo(\PDO $pdo, int $idArticulo, array $data): bool {
        try {
            $stmt = $pdo->prepare("UPDATE articulo SET titulo = :titulo, resumen = :resumen WHERE id_articulo = :id_articulo");
            $stmt->execute([
                'titulo' => $data['titulo'],
                'resumen' => $data['resumen'],
                'id_articulo' => $idArticulo
            ]);
            return true;
        } catch (\PDOException $e) {
            error_log("Error al actualizar artículo: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Actualizar una revisión utilizando un procedimiento almacenado.
     * Data: calidad_tecnica, originalidad, valoracion_global, estado, argumentos
     */
    public static function actualizarRevision(\PDO $pdo, int $idArticulo, int $idRevisor, array $data): bool {
        try {
            $stmt = $pdo->prepare("CALL actualizar_revision(:id_articulo, :id_revisor, :calidad_tecnica, :originalidad, :valoracion_global, :estado, :argumentos)");
            $stmt->execute([
                'id_articulo' => $idArticulo,
                'id_revisor' => $idRevisor,
                'calidad_tecnica' => $data['calidad_tecnica'],
                'originalidad' => $data['originalidad'],
                'valoracion_global' => $data['valoracion_global'],
                'estado' => $data['estado'],
                'argumentos' => json_encode($data['argumentos'])
            ]);
            return true;
        } catch (\PDOException $e) {
            error_log("Error al actualizar revisión: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Actualizar un usuario.
     * Data: rut, email, nombre, contrasena
     */
    public static function actualizarUsuario(\PDO $pdo, int $idUsuario, array $data): bool {
        try {
            $hash = $data['contrasena'] ? password_hash($data['contrasena'], PASSWORD_BCRYPT) : null;
            $stmt = $pdo->prepare("CALL actualizar_usuario(:id_usuario, :nombre, :email, :contrasena)");
            $stmt->execute([
                'id_usuario' => $idUsuario,
                'nombre' => $data['nombre'],
                'email' => $data['email'],
                'contrasena' => $hash
            ]);
            return true;
        } catch (\PDOException $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Actualizar tópicos asociados a un artículo.
     * Data: array de IDs de tópicos.
     */
    public static function actualizarTopicos(\PDO $pdo, int $idArticulo, array $topicos): bool {
        try {
            $stmt = $pdo->prepare("DELETE FROM topico WHERE id_articulo = :id_articulo");
            $stmt->execute(['id_articulo' => $idArticulo]);

            $stmt = $pdo->prepare("INSERT INTO topico (id_categoria, id_articulo) VALUES (:id_categoria, :id_articulo)");
            foreach ($topicos as $topico) {
                $stmt->execute([
                    'id_categoria' => $topico,
                    'id_articulo' => $idArticulo
                ]);
            }
            return true;
        } catch (\PDOException $e) {
            error_log("Error al actualizar tópicos: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Actualizar propiedad de un artículo.
     * Data: array de IDs de autores, contacto (ID de autor).
     */
    public static function actualizarPropiedad(\PDO $pdo, int $idArticulo, array $autores, int $contacto): bool {
        try {
            $stmt = $pdo->prepare("DELETE FROM propiedad WHERE id_articulo = :id_articulo");
            $stmt->execute(['id_articulo' => $idArticulo]);

            $stmt = $pdo->prepare("INSERT INTO propiedad (id_articulo, id_autor, es_contacto) VALUES (:id_articulo, :id_autor, :es_contacto)");
            foreach ($autores as $autor) {
                $stmt->execute([
                    'id_articulo' => $idArticulo,
                    'id_autor' => $autor,
                    'es_contacto' => $autor === $contacto ? 1 : 0
                ]);
            }
            return true;
        } catch (\PDOException $e) {
            error_log("Error al actualizar propiedad: " . $e->getMessage());
            return false;
        }
    }
    /**
     * ================================================================
     *                              DELETE
     * ================================================================
     */
    /**
     * Eliminar un artículo por su ID.
     */
    public static function eliminarArticulo(\PDO $pdo, int $idArticulo): bool {
        try {
            $stmt = $pdo->prepare("DELETE FROM articulo WHERE id_articulo = :id_articulo");
            $stmt->execute([
                'id_articulo' => $idArticulo
            ]);
            return true;
        } catch (\PDOException $e) {
            error_log("Error al eliminar artículo: " . $e->getMessage());
            return false;
        }
    }
    /**
     * ================================================================
     *                             MISC
     * ================================================================
     */
    /**
     * Login de usuario cualquiera.
     * Devuelve un token si las credenciales son correctas.
     */
    public static function loginUsuario(\PDO $pdo, string $email, string $contrasena): ?string {
        try {
            $stmt = $pdo->prepare("SELECT * FROM usuario WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
    
            if ($user && password_verify($contrasena, $user['contrasena'])) {
                return JwtHelper::generarToken((int)$user['id_usuario'], $user['tipo'], $user['nombre'], $user['email']);
            }
            return null;
        } catch (\PDOException $e) {
            error_log("Error al iniciar sesión: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verificar autenticación de un usuario.
     */
    public static function verificarAuthUsuario(?string $token): array|bool {
        if (!$token) return false;
        try {
            return JwtHelper::verificarToken($token);
        } catch (\Exception $e) {
            error_log("Error al verificar autenticación del usuario: " . $e->getMessage());
            return false;
        }
    }
    public static function ObtenerToken(\Slim\Psr7\Request $request): ?string {
        $cookie = $request->getCookieParams()['token'] ?? '';
        if (preg_match('/Bearer\s(\S+)/', $cookie, $matches)) {
            return $matches[1];
        }
        return null;
    }
    public static function confirmarContrasena(\PDO $pdo, int $idUsuario, string $contrasena): bool {
        try {
            $stmt = $pdo->prepare("SELECT contrasena FROM usuario WHERE id_usuario = :id_usuario");
            $stmt->execute(['id_usuario' => $idUsuario]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
    
            return $user && password_verify($contrasena, $user['contrasena']);
        } catch (\PDOException $e) {
            error_log("Error al confirmar contraseña: " . $e->getMessage());
            return false;
        }
    }
    public static function esPropietario(\PDO $pdo, int $idUsuario): bool {
        try {
            $stmt = $pdo->prepare("CALL es_propietario(:id_usuario)");
            $stmt->execute(['id_usuario' => $idUsuario]);
            return (bool) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            error_log("Error al verificar propietario: " . $e->getMessage());
            return false;
        }
    }
    public static function asignarRevisores(\PDO $pdo, int $idArticulo): bool {
        try {
            $stmt = $pdo->prepare("CALL asignar_revisores(:id_articulo)");
            $stmt->execute(['id_articulo' => $idArticulo]);
            return true;
        } catch (\PDOException $e) {
            error_log("Error al asignar revisores: " . $e->getMessage());
            return false;
        }
    }
}