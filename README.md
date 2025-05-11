## Autores

| ROL         | Nombre          |
|-------------|-----------------|
| 202373037-8 | Matias Peñaloza |
| 202373020-3 | Hans González   |

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

### Suposiciones
-no permitimos cambio de rut, ya que asi nos aseguramos de tener ruts unicos, y ademas, si es que lo ponemos en un contexto "laboral", no tiene sentido que un usuario cambie su rut ya que una persona normalmente no puede modificar su rut
-No implementamos el registro de autores al momento de publicar, por que asi mantenemos un orden y simplicidad a la pagina
-Se uso coomo identificador de sesion al correo ya que, el rut al ser unico, en caso de que se filtre facilita la suplantacion de identidad, por lo que el correo al poder llevar caracteres y combinaciones mas complejas es mas dificil su suplantacion