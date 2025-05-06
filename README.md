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

## Rutas y Endpoints
### API
| Método | Endpoint                               | Descripción                     |
|--------|----------------------------------------|---------------------------------|
| GET    |[/api/](http://localhost:8080/api/users)| Entrada Test                    |

### FRONTEND
| Página           | URL                       | Descripción                       |
|------------------|---------------------------|-----------------------------------|
| Inicio           |[/](http://localhost:8080/)| Página principal de la aplicación |
