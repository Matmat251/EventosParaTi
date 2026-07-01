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
                sh 'php -v'
                sh 'composer --version'
            }
        }

        stage('Instalar Dependencias') {
            steps {
                echo 'Instalando dependencias de Composer (PHPStan y PHPUnit)...'
                sh 'composer install --no-interaction --prefer-dist --optimize-autoloader'
            }
        }

        stage('Linter PHP') {
            steps {
                echo 'Validando sintaxis de archivos PHP en backend/...'
                sh 'find backend/ -type f -name "*.php" -exec php -l {} \\;'
            }
        }

        stage('Analisis Estatico (PHPStan)') {
            steps {
                echo 'Ejecutando analisis estatico con PHPStan (nivel 3)...'
                sh 'vendor/bin/phpstan analyse --configuration=phpstan.neon --no-progress'
            }
        }

        stage('Pruebas de Integracion') {
            steps {
                echo 'Ejecutando suite de pruebas de integracion contra base de datos...'
                // Se ejecuta el script de integracion local. Si no hay una base de datos activa,
                // el script arrojara advertencias o fallos controlados.
                sh 'php tests/integration/integration_tests.php'
            }
        }

        stage('Docker Build') {
            steps {
                echo 'Validando la construccion de la imagen Docker de la aplicacion...'
                sh '''
                    if command -v docker >/dev/null 2>&1; then
                        docker build -t "eventosparati/app:jenkins-${BUILD_NUMBER}" .
                    else
                        echo "  [INFO] Docker no esta instalado en el host. Omitiendo build de imagen."
                    fi
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
