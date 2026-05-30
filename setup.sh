#!/bin/bash

echo "========================================================"
echo " Iniciando automatizacion del entorno de desarrollo"
echo "========================================================"

echo "[+] Verificando instalacion de Docker..."
if ! command -v docker &> /dev/null
then
    echo "ERROR: Docker no esta instalado. Por favor instala Docker Desktop primero."
    exit 1
fi

echo "[+] Levantando contenedores de base de datos y aplicacion..."
docker-compose up -d --build

echo "[+] Esperando a que la base de datos se inicialice..."
sleep 10

echo "========================================================"
echo " Setup completado con exito."
echo " La aplicacion esta corriendo en: http://localhost:8000"
echo "========================================================"
