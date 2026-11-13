#!/bin/sh
echo ==========================================
echo Welcome to the Viagem PHP Project
echo Initializing docker containers...
echo ==========================================

docker compose up -d --build

sleep 10

echo ==========================================
echo Importing data into the database...
echo ==========================================

docker compose exec php php ./bin/import.php

echo ==========================================
echo Data import completed successfully!
echo Web application is now running at http://localhost:8080
echo ==========================================