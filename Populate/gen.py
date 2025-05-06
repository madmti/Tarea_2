import random
from tablesdata import *
from const import *

class AutoInit:
    def __init__(self, **kwargs) -> None:
        for key, value in kwargs.items():
            setattr(self, key, value)

class DataGenerator(AutoInit):
    n_articulos:int = 100
    n_revisores:int = 50
    n_autores:int = 50
    n_administradores:int = 5
    n_categorias:int = 10
    current_users:int = 0
    __generate_order:list[str] = [
        'categorias',
        'articulos',
        'administradores',
        'autores',
        'revisores',
        'topicos',
        'especialidades',
        'propiedades',
        'revisiones',
    ]
    __data:dict[str, list['TableData']] = { key:[] for key in __generate_order }

    def __init__(self, **kwargs) -> None:
        super().__init__(**kwargs)

    def __generate_categorias(self) -> None:
        self.__data['categorias'] = [Categoria(i, nombre) for i, nombre in enumerate(CATEGORIAS)]

    def __generate_articulos(self) -> None:
        self.__data['articulos'] = [
            Articulo(
                id_articulo=i,
                titulo=FieldGenerator.generate_unique_titulo(),
                resumen=FieldGenerator.generate_resumen(),
                fecha_envio=FieldGenerator.generate_fecha(),
                aprobado= None,
            ) for i in range(self.n_articulos)
        ]

    def __generate_administradores(self) -> None:
        for i in range(self.n_administradores):
            rut = FieldGenerator.generate_rut()
            nombre = FieldGenerator.generate_unique_nombre()
            email = FieldGenerator.generate_email(nombre, rut)
            self.__data['administradores'].append(Administrador(
                id_administrador=self.current_users,
                rut=rut,
                nombre=nombre,
                email=email,
                contrasena=FieldGenerator.generate_password(),
            ))
            self.current_users += 1

    def __generate_autores(self) -> None:
        for i in range(self.n_autores):
            rut = FieldGenerator.generate_rut()
            nombre = FieldGenerator.generate_unique_nombre()
            email = FieldGenerator.generate_email(nombre, rut)
            self.__data['autores'].append(Autor(
                id_autor=self.current_users,
                rut=rut,
                nombre=nombre,
                email=email,
                contrasena=FieldGenerator.generate_password(),
            ))
            self.current_users += 1

    def __generate_revisores(self) -> None:
        for i in range(self.n_revisores):
            rut = FieldGenerator.generate_rut()
            nombre = FieldGenerator.generate_unique_nombre()
            email = FieldGenerator.generate_email(nombre, rut)
            self.__data['revisores'].append(Revisor(
                id_revisor=self.current_users,
                rut=rut,
                nombre=nombre,
                email=email,
                contrasena=FieldGenerator.generate_password(),
            ))
            self.current_users += 1

    def __generate_topicos(self) -> None:
        for articulo in self.__data['articulos']:
            n_topicos = random.randint(1, 3)
            topicos = random.sample(range(self.n_categorias), n_topicos)
            for topico in topicos:
                self.__data['topicos'].append(Topico(
                    id_categoria=topico,
                    id_articulo=articulo.get('id_articulo'),
                ))

    def __generate_especialidades(self) -> None:
        for revisor in self.__data['revisores']:
            n_especialidades = random.randint(1, 3)
            especialidades = random.sample(range(self.n_categorias), n_especialidades)
            for especialidad in especialidades:
                self.__data['especialidades'].append(Especialidad(
                    id_categoria=especialidad,
                    id_revisor=revisor.get('id_usuario'),
                ))

    def __generate_propiedades(self) -> None:
        for articulo in self.__data['articulos']:
            n_dueños = random.randint(1, 3)
            dueños = random.sample(self.__data['autores'], n_dueños)
            for i, dueño in enumerate(dueños):
                self.__data['propiedades'].append(Propiedad(
                    id_articulo=articulo.get('id_articulo'),
                    id_autor=dueño.get('id_usuario'),
                    es_contacto=(i == 0),
                ))

    def __get_revisores_disponibles(self, topico:int, autores_articulo:list[int], revisores_asignados:set[int]) -> list[int]:
        revisores_disponibles = []
        for revisor in self.__data['revisores']:
            id_revisor = revisor.get('id_usuario')
            especialidades = [
                especialidad.get('id_categoria')
                for especialidad in self.__data['especialidades']
                if especialidad.get('id_revisor') == revisor.get('id_usuario')
            ]
            if id_revisor not in revisores_asignados and \
               id_revisor not in autores_articulo and \
               topico in especialidades:
                revisores_disponibles.append(revisor.get('id_usuario'))
        return revisores_disponibles

    def __generate_revisiones(self) -> None:
        for articulo in self.__data['articulos']:
            topicos_articulo = [
                topico.get('id_categoria')
                for topico in self.__data['topicos']
                if topico.get('id_articulo') == articulo.get('id_articulo')
            ]

            autores_articulo = [
                propiedad.get('id_autor')
                for propiedad in self.__data['propiedades']
                if propiedad.get('id_articulo') == articulo.get('id_articulo')
            ]
            revisores_asignados = set()
            for topico in topicos_articulo:
                revisores_disponibles = self.__get_revisores_disponibles(topico, autores_articulo, revisores_asignados)
                seleccionados = random.sample(revisores_disponibles, min(3, len(revisores_disponibles)))

                for revisor in seleccionados:
                    estado = random.choice([True, False, None])
                    calidad_tecnica = None if estado is None else FieldGenerator.generate_calificacion()
                    originalidad = None if estado is None else FieldGenerator.generate_calificacion()
                    valoracion_global = None if estado is None else FieldGenerator.generate_calificacion()
                    fecha_emision = None if estado is None else FieldGenerator.generate_fecha_antes_de(articulo.get('fecha_envio'))
                    argumentos = None if estado is None else FieldGenerator.generate_argumentos()
                    self.__data['revisiones'].append(Revision(
                        id_articulo=articulo.get('id_articulo'),
                        id_revisor=revisor,
                        estado=estado,
                        calidad_tecnica=calidad_tecnica,
                        originalidad=originalidad,
                        valoracion_global=valoracion_global,
                        fecha_emision=fecha_emision,
                        argumentos=argumentos,
                    ))
                    revisores_asignados.add(revisor)

    def generate(self) -> None:
        for method in self.__generate_order:
            getattr(self, f'_DataGenerator__generate_{method}')()

    def get_data(self) -> dict[str, list['TableData']]:
        return self.__data

class FieldGenerator:
    __used_nombres: set[str] = set()
    __used_titulos: set[str] = set()

    @staticmethod
    def generate_rut() -> str:
        numero = random.randint(10000000, 99999999)
        digito_verificador = random.choice(DIGITOS_VERIFICADORES)
        return f"{numero}-{digito_verificador}"
    
    @staticmethod
    def generate_email(nombre:str, rut:str) -> str:
        return f"{nombre.replace(' ', '.')}{rut.split('-')[-1]}@{random.choice(DOMINIOS)}"

    @staticmethod
    def generate_unique_nombre() -> str:
        while True:
            nombre = FAKER.name()
            if nombre not in FieldGenerator.__used_nombres:
                FieldGenerator.__used_nombres.add(nombre)
                return nombre

    @staticmethod
    def generate_fecha() -> str:
        return f"{random.randint(2000, 2023)}-{random.randint(1, 12):02}-{random.randint(1, 28):02}"

    @staticmethod
    def generate_unique_titulo() -> str:
        while True:
            titulo = f"{random.choice(TITULO_INICIO)} {random.choice(TITULO_VERBOS)} {random.choice(TITULO_OBJETOS)}"
            if titulo not in FieldGenerator.__used_titulos:
                FieldGenerator.__used_titulos.add(titulo)
                return titulo

    @staticmethod
    def generate_password() -> str:
        return FAKER.password(length=30, special_chars=True, digits=True, upper_case=True, lower_case=True)

    @staticmethod
    def generate_resumen() -> str:
        return FAKER.text(max_nb_chars=150)
    
    @staticmethod
    def generate_calificacion() -> int:
        return random.randint(1, 10)

    @staticmethod
    def generate_fecha_antes_de(fecha:str) -> str:
        year, month, day = map(int, fecha.split('-'))
        dia = random.randint(1, day)
        mes = random.randint(1, month)
        anio = random.randint(2000, year)
        return f"{anio}-{mes:02}-{dia:02}"

    @staticmethod
    def generate_argumentos() -> dict[str, str]:
        return {
            'valoracion_global': FAKER.sentence(nb_words=10),
            'para_autores': FAKER.sentence(nb_words=10),
            'recomendacion': FAKER.sentence(nb_words=10),
        }
