pipeline {
    agent any

    environment {
        // Configuracion por defecto para las pruebas de integracion
        DB_HOST = '127.0.0.1'
        DB_USER = 'root'
        DB_PASS = 'root'
        DB_NAME = 'eventosparati'
        DB_PORT = '3306'
    }

    stages {
        stage('Preparar Entorno') {
            steps {
                echo 'Descargando codigo y preparando espacio de trabajo...'
                bat 'php -v'
                bat 'composer --version'
            }
        }

        stage('Instalar Dependencias') {
            steps {
                echo 'Instalando dependencias de Composer (PHPStan y PHPUnit)...'
                bat 'composer install --no-interaction --prefer-dist --optimize-autoloader'
            }
        }

        stage('Linter PHP') {
            steps {
                echo 'Validando sintaxis de archivos PHP en backend/...'
                bat '''
                    @echo off
                    setlocal enabledelayedexpansion
                    set "ERRORS=0"
                    for /r backend %%f in (*.php) do (
                        php -l "%%f"
                        if !errorlevel! neq 0 set "ERRORS=1"
                    )
                    if !ERRORS! equ 1 exit /b 1
                '''
            }
        }

        stage('Analisis Estatico (PHPStan)') {
            steps {
                echo 'Ejecutando analisis estatico con PHPStan (nivel 3)...'
                bat 'call vendor\\bin\\phpstan.bat analyse --configuration=phpstan.neon --no-progress'
            }
        }

        stage('Pruebas de Integracion') {
            steps {
                echo 'Ejecutando suite de pruebas de integracion contra base de datos...'
                bat 'php tests\\integration\\integration_tests.php'
            }
        }

        stage('Docker Build') {
            steps {
                echo 'Validando la construccion de la imagen Docker de la aplicacion...'
                bat '''
                    @echo off
                    where docker >nul 2>nul
                    if %errorlevel% equ 0 (
                        docker build -t "eventosparati/app:jenkins-%BUILD_NUMBER%" .
                    ) else (
                        echo   [INFO] Docker no esta instalado en el host. Omitiendo build de imagen.
                    )
                '''
            }
        }
    }

    post {
        always {
            echo 'Limpiando espacio de trabajo...'
        }
        success {
            echo 'Pipeline de Jenkins completado exitosamente. Todo en verde.'
        }
        failure {
            echo 'Se detectaron errores en el pipeline de Jenkins. Revisar logs superiores.'
        }
    }
}
