@echo off

echo ==========================================
echo Importing data into the database...
echo Warning This will overwritte whole database!
echo ==========================================

docker compose exec php php ./bin/import.php