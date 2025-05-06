from os import path
from gen import DataGenerator
from tablesdata import TableData

def get_path(file_name:str) -> str:
    return path.join(path.dirname(path.dirname(__file__)), "BD", file_name)

def data_to_file(data:dict[str, list['TableData']], file_name:str) -> None:
    file_path = get_path(file_name)
    with open(file_path, 'w', encoding='utf-8') as file:
        for table_name, rows in data.items():
            print(f'Generando {len(rows)} {table_name}')
            for row in rows:
                query = row.to_psql_insert()
                file.write(query + '\n')
        file.close()

def main() -> None:
    gen = DataGenerator(
        n_articulos=400,
        n_autores=50,
        n_revisores=50,
    )
    gen.generate()
    data = gen.get_data()
    data_to_file(data, '99_INSERT.sql')


if __name__ == "__main__":
    main()
