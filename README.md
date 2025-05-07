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


## Rutas FRONTEND
