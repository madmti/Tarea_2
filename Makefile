COMPOSE_FILE=docker-compose.yml

up:
	docker-compose -f $(COMPOSE_FILE) up --build -d

down:
	docker-compose -f $(COMPOSE_FILE) down

logs:
	docker-compose -f $(COMPOSE_FILE) logs -f

ps:
	docker-compose -f $(COMPOSE_FILE) ps -a

php:
	docker exec -it tarea_2-php-1 bash

mysql:
	docker exec -it tarea_2-mysql-1 mysql -u$$MYSQL_USER -p$$MYSQL_PASSWORD $$MYSQL_DATABASE

restart:
	docker-compose -f $(COMPOSE_FILE) down && docker-compose -f $(COMPOSE_FILE) up --build -d

restart-mysql:
	docker-compose -f $(COMPOSE_FILE) down mysql
	docker-compose -f $(COMPOSE_FILE) up --build -d mysql

populate:
	python -u Populate/main.py