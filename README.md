## Autores

| ROL         | Nombre          |
|-------------|-----------------|
| 202373037-8 | Matias Peñaloza |
| Rol2        | Nombre2         |

## Antes de empezar

## Instrucciones de Ejecucion
Generar archivo de poblacion
```bash
sudo make populate
```

Levantar los servicios
```bash
sudo make up
```

Finalmente los servicios estaran expuestos en las siguientes URL's
- Aplicación web: [http://localhost:8080](http://localhost:8080)
- phpMyAdmin: [http://localhost:8081](http://localhost:8081)

### Detencion
Para detener los servicios
```bash
sudo make down
```

## Endpoints
Para todos los endpoints en caso de fallo se entrega la siguiente respuesta, junto al codigo de error correspondiente:
```json
{
    "message": "Mensaje describiendo el error.."
}
```
### GET /api/public/articulos
**Query Parameters:**

- `autor`: ID del autor (int, opcional)
- `fecha_ini`: Fecha inicial (opcional)
- `fecha_fin`: Fecha final (opcional)
- `categoria`: ID de la categoría (int, opcional)
- `revisor`: ID del revisor (int, opcional)
- `titulo`: Título del artículo (string, opcional)

**Respuesta:**
```json
{
    message: "Artículos obtenidos exitosamente.",
    total: 238,
    articulos: [
        {
            titulo: "Como analizar Software y Hardware",
            resumen: "Cumque excepturi...",
            fecha_envio: "2005-08-08",
            topicos: "Gestion de proyectos, Redes y ..."
        },
        ...
    ]
}
```

### POST /api/public/articulos
**Body Parameters:**
- `titulo`: Titulo del articulo
- `resumen`: Resumen del articulo
- `topicos`: array de las IDs de los topicos
- `autores`: array de las IDs de los autores (el primer autor es de contacto)

**Respuesta:**
```json
{
    message: "Artículo creado exitosamente.",
    id_articulo: 41
}
```

## Rutas FRONTEND
