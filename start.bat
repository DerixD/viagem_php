@echo off

echo ==========================================
echo Welcome to the Viagem PHP Project
echo Initializing docker containers...
echo ==========================================

docker compose up -d --build

timeout /t 10 /nobreak >nul

echo ==========================================
echo Importing data into the database...
echo ==========================================

docker compose exec php php ./bin/import.php

echo ==========================================
echo Data import completed successfully!
echo Web application is now running at http://localhost:8080
echo ==========================================

start http://localhost:8080
pause