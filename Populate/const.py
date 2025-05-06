from faker import Faker

CATEGORIAS = [
    "Gestion de proyectos", "Desarrollo de Software", "Ciencia de datos", "Operaciones", "Operaciones de TI",
    "Seguridad de la información", "Inteligencia artificial", "Redes y telecomunicaciones", "Sistemas de información", "Operaciones de red",
]

DIGITOS_VERIFICADORES = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'K']

DOMINIOS = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'live.com', 'icloud.com', 'zoho.com', 'protonmail.com', 'tutanota.com', 'yandex.com']

TITULO_INICIO = [
    "Cómo", "Cuándo", "Dónde", "Por qué", "Objetivos de", "Operar y",
    "Implementar y", "Ordenar y", "Gestionar y", "Crear y", "Desarrollar y",
]
TITULO_VERBOS = [
    "aprender", "usar", "crear", "mejorar", "entender", "desarrollar",
    "implementar", "optimizar", "gestionar", "analizar", "configurar",
    "proteger", "evaluar", "diseñar", "mantener", "administrar",
    "instalar", "programar", "depurar", "probar", "documentar",
]
TITULO_OBJETOS = [
    "Software en Python", "bases de datos", "IA en Software", "algoritmos", "aplicaciones web", "seguridad informática",
    "redes neuronales", "machine learning", "Gestion de big data", "cloud computing", "programación funcional", "Gestion en POO",
    "Software y Hardware", "internet de las cosas", "blockchain", "sistemas distribuidos", "sistemas operativos", "redes de computadoras",
]

FAKER = Faker('es_CL')