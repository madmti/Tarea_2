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

### ✅ `GET /api/public/articulos`

Obtiene una lista de artículos, con opción de aplicar filtros.

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
  "message": "Artículos obtenidos exitosamente.",
  "total": 238,
  "articulos": [
    {
      "titulo": "Como analizar Software y Hardware",
      "resumen": "Cumque excepturi...",
      "fecha_envio": "2005-08-08",
      "topicos": "Gestión de proyectos, Redes y ..."
    },
    ...
  ]
}
```

> 🔓 Ruta pública

---

### ✅ `POST /api/public/articulos`

Crea un nuevo artículo, asociando tópicos y autores.

**Body Parameters:**

- `titulo`: Título del artículo (máx. 50 caracteres)
- `resumen`: Resumen del artículo (máx. 150 caracteres)
- `topicos`: Array con IDs de tópicos
- `autores`: Array con IDs de autores (el primero será el autor de contacto)

**Respuesta:**
```json
{
  "message": "Artículo creado exitosamente.",
  "id_articulo": 41
}
```

> 🔓 Ruta pública

---

### ✅ `PUT /api/protected/articulos/{id}`

Actualiza un artículo existente por su ID.

**Path Parameter:**
- `id`: ID del artículo

**Body Parameters:**

- `titulo`: Nuevo título (máx. 50 caracteres)
- `resumen`: Nuevo resumen (máx. 150 caracteres)

**Respuesta:**
```json
{
  "message": "Artículo actualizado exitosamente.",
  "id_articulo": 41
}
```

> 🔒 Ruta protegida (requiere JWT con rol `CONTACTO` o superior)

---

### ✅ `DELETE /api/protected/articulos/{id}`

Elimina un artículo por su ID.

**Path Parameter:**
- `id`: ID del artículo

**Respuesta:**
```json
{
  "message": "Artículo eliminado exitosamente."
}
```

> 🔒 Ruta protegida (requiere JWT con rol `CONTACTO` o superior)

---

### ✅ `POST /api/public/registro`

Registra un nuevo usuario tipo autor (`AUT`) y retorna un token JWT.

**Body Parameters:**

- `rut`: RUT del usuario (máx. 10 caracteres)
- `email`: Email (máx. 95 caracteres)
- `nombre`: Nombre completo (máx. 85 caracteres)
- `contrasena`: Contraseña (máx. 30 caracteres)

**Respuesta:**
```json
{
  "message": "Usuario registrado exitosamente.",
  "id_usuario": 42
}
```

> 🔓 Ruta pública — retorna el JWT en la cabecera `Authorization: Bearer <token>`

---

### ✅ `POST /api/private/registro`

Registra un usuario con cualquier tipo de rol (`AUT`, `REV`, `ADM`).

**Body Parameters:**

- `rut`, `email`, `nombre`, `contrasena` → igual que el registro público
- `tipo`: Tipo de usuario (`AUT`, `REV`, `ADM`)

**Respuesta:**
```json
{
  "message": "Usuario registrado exitosamente.",
  "id_usuario": 17
}
```

> 🔒 Ruta privada (requiere JWT con rol `ADMINISTRADOR`)  
> Retorna el JWT del nuevo usuario registrado en `Authorization: Bearer <token>`

---

### ✅ `POST /api/public/login`

Inicia sesión y retorna un token JWT si las credenciales son válidas.

**Body Parameters:**

- `email`: Email del usuario
- `contrasena`: Contraseña

**Respuesta:**
```json
{
  "message": "Inicio de sesión exitoso."
}
```

> 🔓 Ruta pública — retorna el JWT en la cabecera `Authorization: Bearer <token>`


## Rutas FRONTEND
