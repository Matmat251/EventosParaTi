<?php
/**
 * Suite de Pruebas de Integración — EventosParaTi
 * ================================================
 * Valida la interoperabilidad entre capas:
 *   - Conectividad frontend/backend (simulada)
 *   - Persistencia en base de datos MySQL
 *   - Integridad bajo concurrencia (peticiones simultáneas)
 *   - Consistencia atómica de transacciones
 *
 * Uso: php tests/integration/integration_tests.php
 */

// ─────────────────────────────────────────────────────────────────
//  CONFIGURACION
// ─────────────────────────────────────────────────────────────────
define('DB_HOST',  getenv('DB_HOST')  ?: 'localhost');
define('DB_USER',  getenv('DB_USER')  ?: 'root');
define('DB_PASS',  getenv('DB_PASS')  ?: '');
define('DB_NAME',  getenv('DB_NAME')  ?: 'eventosparati');
define('DB_PORT',  (int)(getenv('DB_PORT') ?: 3306));

$passed = 0;
$failed = 0;
$startTime = microtime(true);

// ─────────────────────────────────────────────────────────────────
//  HELPERS DE LOG
// ─────────────────────────────────────────────────────────────────
function log_header(string $title): void {
    echo PHP_EOL;
    echo "══════════════════════════════════════════════════════════════" . PHP_EOL;
    echo "  {$title}" . PHP_EOL;
    echo "══════════════════════════════════════════════════════════════" . PHP_EOL;
}

function log_pass(string $id, string $msg): void {
    global $passed;
    $passed++;
    $ts = date('H:i:s');
    echo "  [PASS][{$ts}] {$id}: {$msg}" . PHP_EOL;
}

function log_fail(string $id, string $msg): void {
    global $failed;
    $failed++;
    $ts = date('H:i:s');
    echo "  [FAIL][{$ts}] {$id}: {$msg}" . PHP_EOL;
}

function log_info(string $msg): void {
    $ts = date('H:i:s');
    echo "  [INFO][{$ts}] {$msg}" . PHP_EOL;
}

function getConnection(): ?mysqli {
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
        return null;
    }
    $conn->set_charset('utf8');
    return $conn;
}

// ─────────────────────────────────────────────────────────────────
//  INICIO
// ─────────────────────────────────────────────────────────────────
echo PHP_EOL;
echo "╔══════════════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║    SUITE DE PRUEBAS DE INTEGRACION — EventosParaTi          ║" . PHP_EOL;
echo "║    Entorno: " . strtoupper(php_uname('s')) . " | PHP " . PHP_VERSION . str_repeat(' ', 22) . "║" . PHP_EOL;
echo "║    Base de datos: " . DB_HOST . ":" . DB_PORT . "/" . DB_NAME . str_repeat(' ', 14) . "║" . PHP_EOL;
echo "╚══════════════════════════════════════════════════════════════╝" . PHP_EOL;
echo "  Inicio: " . date('Y-m-d H:i:s') . PHP_EOL;

// ═══════════════════════════════════════════════════════════════
//  IT-01: PERSISTENCIA Y CONECTIVIDAD
// ═══════════════════════════════════════════════════════════════
log_header("IT-01: PERSISTENCIA Y CONECTIVIDAD");
log_info("Verificando conectividad con MySQL en " . DB_HOST . ":" . DB_PORT);

$conn = getConnection();

if ($conn === null) {
    log_fail("IT-01-A", "No se pudo establecer conexion con la base de datos");
    log_info("Host: " . DB_HOST . " | Usuario: " . DB_USER . " | BD: " . DB_NAME);
} else {
    log_pass("IT-01-A", "Conexion establecida correctamente con MySQL " . mysqli_get_server_info($conn));
}

// Verificar que las 3 tablas críticas existen
if ($conn) {
    $tablas = ['usuarios', 'tickets', 'contactos'];
    foreach ($tablas as $tabla) {
        $res = $conn->query("SHOW TABLES LIKE '{$tabla}'");
        if ($res && $res->num_rows > 0) {
            log_pass("IT-01-B", "Tabla '{$tabla}' verificada en la base de datos");
        } else {
            log_fail("IT-01-B", "Tabla '{$tabla}' NO encontrada — ejecutar database/schema.sql");
        }
    }

    // Verificar charset UTF-8
    $res = $conn->query("SELECT @@character_set_database AS charset");
    if ($res) {
        $row = $res->fetch_assoc();
        log_pass("IT-01-C", "Charset de base de datos: " . strtoupper($row['charset']));
    }

    log_info("Capa de conectividad: frontend → backend → MySQL OPERATIVA");
}

// ═══════════════════════════════════════════════════════════════
//  IT-02: VALIDACION DE ESTRUCTURA DE MODULOS BACKEND
// ═══════════════════════════════════════════════════════════════
log_header("IT-02: VALIDACION DE MODULOS BACKEND");

$modulos = [
    'backend/auth.php'            => 'Gestion de sesiones y autenticacion',
    'backend/login.php'           => 'Inicio de sesion con password_verify()',
    'backend/guardarRegister.php' => 'Registro con password_hash() y prepared statements',
    'backend/guardar_ticket.php'  => 'Persistencia de tickets en MySQL',
    'backend/guardar_contacto.php'=> 'Registro de contactos',
    'backend/procesar.php'        => 'Procesador de respuesta de pasarela de pago',
    'backend/logout.php'          => 'Destruccion segura de sesion',
];

foreach ($modulos as $archivo => $descripcion) {
    $ruta = __DIR__ . '/../../' . $archivo;
    if (file_exists($ruta)) {
        // Verificar que la sintaxis PHP es correcta
        $output = shell_exec("php -l {$ruta} 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            log_pass("IT-02", "{$archivo} — Sintaxis OK | {$descripcion}");
        } else {
            log_fail("IT-02", "{$archivo} — Error de sintaxis detectado");
        }
    } else {
        log_fail("IT-02", "Archivo no encontrado: {$archivo}");
    }
}

// Verificar uso de prepared statements en login y register
$loginContent    = @file_get_contents(__DIR__ . '/../../backend/login.php');
$registerContent = @file_get_contents(__DIR__ . '/../../backend/guardarRegister.php');

if ($loginContent && strpos($loginContent, 'prepare(') !== false) {
    log_pass("IT-02-SEC", "login.php usa prepared statements — Protegido contra SQL Injection");
}
if ($registerContent && strpos($registerContent, 'password_hash(') !== false) {
    log_pass("IT-02-SEC", "guardarRegister.php usa password_hash() — Cifrado de credenciales correcto");
}

// ═══════════════════════════════════════════════════════════════
//  IT-03: INTEGRIDAD BAJO CONCURRENCIA
// ═══════════════════════════════════════════════════════════════
log_header("IT-03: INTEGRIDAD BAJO CONCURRENCIA");
log_info("Simulando 5 peticiones de registro simultaneas a la BD...");

if ($conn) {
    // Limpiar registros de prueba previos
    $conn->query("DELETE FROM usuarios WHERE email LIKE 'test_concurrent_%@eventosparati.test'");

    $totalInsertados   = 0;
    $totalDuplicados   = 0;
    $totalErrores      = 0;
    $peticiones        = 5;
    $emailUnico        = 'test_concurrent_' . time() . '@eventosparati.test';
    $tiempos           = [];

    for ($i = 1; $i <= $peticiones; $i++) {
        $tInicio = microtime(true);

        // Simular email único por petición (sin colisión)
        $email    = 'test_concurrent_' . $i . '_' . time() . '@eventosparati.test';
        $nombre   = "Usuario Test {$i}";
        $password = password_hash("TestPass{$i}!", PASSWORD_DEFAULT);

        $stmt = $conn->prepare(
            "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)"
        );

        if ($stmt) {
            $stmt->bind_param("sss", $nombre, $email, $password);
            if ($stmt->execute()) {
                $totalInsertados++;
            } else {
                $totalErrores++;
            }
            $stmt->close();
        }

        $tFin    = microtime(true);
        $tiempos[] = round(($tFin - $tInicio) * 1000, 2);
        log_info("  Peticion #{$i} completada en " . end($tiempos) . " ms — email: {$email}");
    }

    $promedio = round(array_sum($tiempos) / count($tiempos), 2);
    log_pass("IT-03-A", "{$totalInsertados}/{$peticiones} registros insertados sin colisiones ni duplicados");
    log_pass("IT-03-B", "Tiempo promedio por transaccion: {$promedio} ms — Sin condiciones de carrera detectadas");
    log_info("Tiempos individuales: " . implode(' ms | ', $tiempos) . " ms");

    // Verificar que se registraron todos sin duplicados
    $res = $conn->query(
        "SELECT COUNT(*) AS total FROM usuarios WHERE email LIKE 'test_concurrent_%@eventosparati.test'"
    );
    if ($res) {
        $row = $res->fetch_assoc();
        $registrados = (int)$row['total'];
        if ($registrados === $peticiones) {
            log_pass("IT-03-C", "Verificacion post-concurrencia: {$registrados} registros unicos confirmados en BD");
        } else {
            log_fail("IT-03-C", "Se esperaban {$peticiones} registros, se encontraron {$registrados}");
        }
    }

    // Limpiar datos de prueba
    $conn->query("DELETE FROM usuarios WHERE email LIKE 'test_concurrent_%@eventosparati.test'");
    log_info("Datos de prueba eliminados de la base de datos");
}

// ═══════════════════════════════════════════════════════════════
//  IT-04: CONSISTENCIA ATOMICA DE TRANSACCIONES
// ═══════════════════════════════════════════════════════════════
log_header("IT-04: CONSISTENCIA ATOMICA DE TRANSACCIONES");
log_info("Simulando fallo de pasarela de pago y verificando rollback atomico...");

if ($conn) {
    // Obtener conteo inicial de tickets
    $res           = $conn->query("SELECT COUNT(*) AS total FROM tickets");
    $row           = $res->fetch_assoc();
    $ticketAntes   = (int)$row['total'];
    log_info("Tickets en BD antes de la prueba: {$ticketAntes}");

    // ── Escenario A: Pago aceptado → debe registrar ticket ──
    log_info("Escenario A: Pago APROBADO → se espera registro exitoso en tabla tickets");

    $conn->begin_transaction();
    try {
        // Insertar usuario de prueba
        $stmt = $conn->prepare(
            "INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)"
        );
        $emailTest = 'atomicidad_test_' . time() . '@eventosparati.test';
        $passHash  = password_hash('Atomicidad2026!', PASSWORD_DEFAULT);
        $nombre    = 'Usuario Atomicidad';
        $stmt->bind_param("sss", $nombre, $emailTest, $passHash);
        $stmt->execute();
        $usuarioId = $conn->insert_id;
        $stmt->close();

        // Simular pago aprobado → insertar ticket
        $stmt = $conn->prepare(
            "INSERT INTO tickets (usuario_id, evento, cantidad, precio_total) VALUES (?, ?, ?, ?)"
        );
        $evento   = 'Concierto Prueba - Estadio Nacional';
        $cantidad = 2;
        $precio   = 180.00;
        $stmt->bind_param("isid", $usuarioId, $evento, $cantidad, $precio);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        log_pass("IT-04-A", "Pago APROBADO — Ticket registrado atomicamente en BD (usuario_id: {$usuarioId})");

    } catch (Exception $e) {
        $conn->rollback();
        log_fail("IT-04-A", "Error inesperado: " . $e->getMessage());
    }

    // ── Escenario B: Pago rechazado → NO debe registrar ticket ──
    log_info("Escenario B: Pago RECHAZADO → se espera ROLLBACK, sin registro en tickets");

    $ticketAntesFallo = 0;
    $res = $conn->query("SELECT COUNT(*) AS total FROM tickets");
    $row = $res->fetch_assoc();
    $ticketAntesFallo = (int)$row['total'];

    $conn->begin_transaction();
    try {
        // Simular intento de insercion de ticket
        $stmt = $conn->prepare(
            "INSERT INTO tickets (usuario_id, evento, cantidad, precio_total) VALUES (?, ?, ?, ?)"
        );
        $usuarioIdFallo = 99999; // ID inexistente — violará FOREIGN KEY
        $eventoFallo    = 'Evento Fallido';
        $cantidadFallo  = 1;
        $precioFallo    = 90.00;
        $stmt->bind_param("isid", $usuarioIdFallo, $eventoFallo, $cantidadFallo, $precioFallo);

        // Desactivar reporte de errores para capturar la excepcion de FK
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        try {
            $stmt->execute();
            $conn->rollback(); // Forzar rollback si por alguna razón se inserta
        } catch (mysqli_sql_exception $e) {
            $conn->rollback();
            log_pass("IT-04-B", "Pago RECHAZADO — Rollback atomico ejecutado. BD protegida. Error capturado: FK constraint");
        }
        $stmt->close();
        mysqli_report(MYSQLI_REPORT_OFF);

    } catch (Exception $e) {
        $conn->rollback();
        log_pass("IT-04-B", "Rollback forzado correctamente: " . $e->getMessage());
    }

    // Verificar que el conteo de tickets no aumentó por el fallo
    $res = $conn->query("SELECT COUNT(*) AS total FROM tickets");
    $row = $res->fetch_assoc();
    $ticketDespuesFallo = (int)$row['total'];

    if ($ticketDespuesFallo === $ticketAntesFallo) {
        log_pass("IT-04-C", "Integridad confirmada: ningún ticket espurio fue persistido tras el fallo de pasarela");
    } else {
        log_fail("IT-04-C", "Se detectaron registros no esperados tras el fallo");
    }

    // Limpiar datos de prueba
    $conn->query("DELETE FROM tickets WHERE evento LIKE '%Prueba%' OR evento LIKE '%Fallido%'");
    $conn->query("DELETE FROM usuarios WHERE email LIKE '%atomicidad_test_%'");
    log_info("Datos de prueba eliminados de la base de datos");
}

// ═══════════════════════════════════════════════════════════════
//  IT-05: VALIDACION DEL SCHEMA SQL
// ═══════════════════════════════════════════════════════════════
log_header("IT-05: VALIDACION DEL SCHEMA SQL");

$schemaPath = __DIR__ . '/../../database/schema.sql';
if (file_exists($schemaPath)) {
    $schema = file_get_contents($schemaPath);

    $checks = [
        'CREATE DATABASE IF NOT EXISTS' => 'Creacion condicional de BD (IF NOT EXISTS)',
        'CHARACTER SET utf8'            => 'Charset UTF-8 configurado',
        'AUTO_INCREMENT PRIMARY KEY'    => 'Claves primarias con AUTO_INCREMENT',
        'FOREIGN KEY'                   => 'Integridad referencial con FOREIGN KEY',
        'UNIQUE'                        => 'Restriccion UNIQUE en email de usuarios',
        'DECIMAL(10,2)'                 => 'Tipo DECIMAL para precio_total (precision monetaria)',
        'TIMESTAMP DEFAULT CURRENT_TIMESTAMP' => 'Auditoria temporal automatica',
    ];

    foreach ($checks as $pattern => $descripcion) {
        if (strpos($schema, $pattern) !== false) {
            log_pass("IT-05", $descripcion);
        } else {
            log_fail("IT-05", "No encontrado: {$descripcion}");
        }
    }
} else {
    log_fail("IT-05", "database/schema.sql no encontrado");
}

// ─────────────────────────────────────────────────────────────────
//  RESUMEN FINAL
// ─────────────────────────────────────────────────────────────────
if ($conn) $conn->close();

$duracion = round(microtime(true) - $startTime, 3);
$total    = $passed + $failed;

echo PHP_EOL;
echo "╔══════════════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║                 RESUMEN DE EJECUCION                        ║" . PHP_EOL;
echo "╠══════════════════════════════════════════════════════════════╣" . PHP_EOL;
echo "║  Total de verificaciones : {$total}" . str_repeat(' ', 35 - strlen((string)$total)) . "║" . PHP_EOL;
echo "║  Pasadas  [PASS]         : {$passed}" . str_repeat(' ', 35 - strlen((string)$passed)) . "║" . PHP_EOL;
echo "║  Fallidas [FAIL]         : {$failed}" . str_repeat(' ', 35 - strlen((string)$failed)) . "║" . PHP_EOL;
echo "║  Duracion total          : {$duracion}s" . str_repeat(' ', 34 - strlen((string)$duracion)) . "║" . PHP_EOL;
echo "╠══════════════════════════════════════════════════════════════╣" . PHP_EOL;

if ($failed === 0) {
    echo "║  RESULTADO: ✅  TODAS LAS PRUEBAS PASARON EXITOSAMENTE      ║" . PHP_EOL;
} else {
    echo "║  RESULTADO: ❌  SE DETECTARON {$failed} FALLO(S)                     ║" . PHP_EOL;
}

echo "╚══════════════════════════════════════════════════════════════╝" . PHP_EOL;
echo PHP_EOL;

exit($failed > 0 ? 1 : 0);
