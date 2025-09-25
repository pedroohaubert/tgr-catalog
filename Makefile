up:
	docker compose up --build

down:
	docker compose down

shell:
	docker compose exec app sh

artisan:
	docker compose exec app php artisan $(CMD)

migrate:
	docker compose exec app php artisan migrate

seed:
	docker compose exec app php artisan db:seed

fresh:
	docker compose exec app php artisan migrate:fresh --seed

test:
	docker compose exec app php artisan test tests/Feature/Feature/

tinker:
	docker compose exec app php artisan tinker

reset-db: down
	rm -f storage/app/.bootstrapped database/database.sqlite
	$(MAKE) up

fresh-install: down
	rm -rf vendor node_modules storage/app/.bootstrapped database/database.sqlite
	docker compose build --no-cache
	$(MAKE) up

.PHONY: up down shell artisan migrate seed fresh test tinker reset-db fresh-install
