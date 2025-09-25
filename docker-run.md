# 1) Build & start
docker compose up -d --build

# 2) Install PHP deps
docker compose exec app bash -lc "composer install"

# 3) Migrate (optional)
docker compose exec app php artisan migrate

# 4) Database Seed (optional)
docker compose exec app php artisan db:seed

# 5) Node package install
docker compose exec node bash -lc "npm i"

# 6) Run dev server
docker compose exec node bash -lc "npm run dev -- --host 0.0.0.0 --port 5173"
