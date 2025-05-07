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
     * ================================================================
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
}