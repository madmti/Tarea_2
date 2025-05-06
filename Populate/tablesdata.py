import json

def python_to_psql(value: str | int | bool | dict | None) -> str:
    if isinstance(value, str):
        return f"'{value}'"
    elif isinstance(value, int):
        return str(value)
    elif isinstance(value, bool):
        return 'TRUE' if value else 'FALSE'
    elif isinstance(value, dict):
        return f"'{json.dumps(value)}'"
    elif value is None:
        return 'NULL'
    else:
        return ''

class TableData:
    table: str
    columns: list[str]
    values: list[str | int | bool | dict | None]

    def get(self, col:str) -> str | None:
        try:
            index = self.columns.index(col)
            return self.values[index]
        except ValueError:
            return None
    
    def to_psql_insert(self) -> str:
        columns = ', '.join(self.columns)
        values = ', '.join([python_to_psql(val) for val in self.values])
        return f"INSERT INTO {self.table} ({columns}) VALUES ({values});"

class Categoria(TableData):
    def __init__(self, id_categoria:int, nombre:str) -> None:
        self.table = 'categoria'
        self.columns = ['id_categoria', 'nombre']
        self.values = [id_categoria, nombre]

class Articulo(TableData):
    def __init__(self, id_articulo:int, titulo:str, resumen:str, fecha_envio:str, aprobado:bool | None) -> None:
        self.table = 'articulo'
        self.columns = ['id_articulo', 'titulo', 'resumen', 'fecha_envio', 'aprobado']
        self.values = [id_articulo, titulo, resumen, fecha_envio, aprobado]

class Usuario(TableData):
    def __init__(self, id_usuario:int, rut:str, nombre:str, email:str, contrasena:str, tipo:str) -> None:
        self.table = 'usuario'
        self.columns = ['id_usuario', 'rut', 'nombre', 'email', 'contrasena', 'tipo']
        self.values = [id_usuario, rut, nombre, email, contrasena, tipo]

class Autor(Usuario):
    def __init__(self, id_autor:int, rut:str, nombre:str, email:str, contrasena:str) -> None:
        super().__init__(id_autor, rut, nombre, email, contrasena, "AUT")

class Revisor(Usuario):
    def __init__(self, id_revisor:int, rut:str, nombre:str, email:str, contrasena:str) -> None:
        super().__init__(id_revisor, rut, nombre, email, contrasena, "REV")

class Administrador(Usuario):
    def __init__(self, id_administrador:int, rut:str, nombre:str, email:str, contrasena:str) -> None:
        super().__init__(id_administrador, rut, nombre, email, contrasena, "ADM")

class Topico(TableData):
    def __init__(self, id_categoria:int, id_articulo:int) -> None:
        self.table = 'topico'
        self.columns = ['id_categoria', 'id_articulo']
        self.values = [id_categoria, id_articulo]

class Especialidad(TableData):
    def __init__(self, id_categoria:int, id_revisor:int) -> None:
        self.table = 'especialidad'
        self.columns = ['id_categoria', 'id_revisor']
        self.values = [id_categoria, id_revisor]

class Propiedad(TableData):
    def __init__(self, id_articulo:int, id_autor:int, es_contacto:bool) -> None:
        self.table = 'propiedad'
        self.columns = ['id_articulo', 'id_autor', 'es_contacto']
        self.values = [id_articulo, id_autor, es_contacto]

class Revision(TableData):
    def __init__(self, id_articulo:int, id_revisor:int, estado:bool | None, calidad_tecnica:int | None, originalidad:int | None, valoracion_global:int | None, fecha_emision:str | None, argumentos:dict | None) -> None:
        self.table = 'revision'
        self.columns = ['id_articulo', 'id_revisor', 'estado', 'calidad_tecnica', 'originalidad', 'valoracion_global', 'fecha_emision', 'argumentos']
        self.values = [
            id_articulo,
            id_revisor,
            estado,
            calidad_tecnica,
            originalidad,
            valoracion_global,
            fecha_emision,
            argumentos,
        ]
