## Autores

| ROL         | Nombre          |
|-------------|-----------------|
| 202373037-8 | Matias Peñaloza |
| 202373020-3 | Hans González   |

## Antes de empezar
Se deben aclarar los siguientes puntos:

### ¿No puedo cambiar mi RUT?
El RUT es un identificador que no cambia en una misma persona, por lo que en caso de querer cambiar el RUT se debera crear otra cuenta.

### ¿Porque se puede registrar un autor sin publicar un articulo?
Aun que al publicar un articulo se manden las credenciales de usuario por email, es mas sensato crear una cuenta antes de publicar un articulo, ya que debiese de existir la entidad autor antes que el articulo del cual es propietario y para evitar registros de usuario en masa.

### ¿Porque iniciar sesion con email y no RUT o id_usuario?
Iniciar sesion con email tiene mas sentido ya que apesar de no ser el maximo distintivo de una persona, en general es el dato proporcionado en las distintas vistas (para guest, autor, revisor y admin) que permite identificar a un usuario y entrar en conctacto con el.

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
