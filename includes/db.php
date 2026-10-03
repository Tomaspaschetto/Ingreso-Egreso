<?php

declare(strict_types=1);

function getDbConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = 'localhost';
    $db = 'registro_egreso';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        die('Error de conexión MySQL: ' . $e->getMessage());
    }

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            nombre VARCHAR(150) NOT NULL,
            rol VARCHAR(50) NOT NULL DEFAULT 'admin',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS personas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(150) NOT NULL,
            cargo VARCHAR(150) NOT NULL,
            departamento VARCHAR(150) NOT NULL,
            documento VARCHAR(50) NOT NULL UNIQUE,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS registros (
            id INT AUTO_INCREMENT PRIMARY KEY,
            persona_id INT NOT NULL,
            tipo VARCHAR(100) NOT NULL,
            motivo VARCHAR(255) NOT NULL,
            destino VARCHAR(255) NOT NULL,
            fecha_salida DATE NOT NULL,
            hora_salida TIME NOT NULL,
            observaciones TEXT DEFAULT NULL,
            usuario_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (persona_id) REFERENCES personas(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");

    $defaultPassword = '123456';
    $defaultUsers = [
        ['admin', 'Administrador', 'admin'],
        ['empleado', 'Empleado', 'empleado'],
        ['juan.perez', 'Juan Perez', 'empleado'],
        ['maria.lopez', 'Maria Lopez', 'empleado'],
        ['carlos.garcia', 'Carlos Garcia', 'empleado'],
        ['ana.morales', 'Ana Morales', 'empleado'],
        ['diego.rivera', 'Diego Rivera', 'empleado'],
        ['sofia.navarro', 'Sofia Navarro', 'empleado'],
    ];

    foreach ($defaultUsers as [$username, $nombre, $rol]) {
        $stmt = $pdo->prepare('SELECT id, password_hash, nombre, rol FROM users WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if (!$user) {
            $stmt = $pdo->prepare(
                'INSERT INTO users (username, password_hash, nombre, rol) VALUES (:username, :password_hash, :nombre, :rol)'
            );
            $stmt->execute([
                ':username' => $username,
                ':password_hash' => password_hash($defaultPassword, PASSWORD_DEFAULT),
                ':nombre' => $nombre,
                ':rol' => $rol,
            ]);
            continue;
        }

        $needsPasswordReset = !password_verify($defaultPassword, (string) $user['password_hash']);
        $needsProfileUpdate = trim((string) $user['nombre']) !== $nombre || strtolower((string) $user['rol']) !== strtolower($rol);

        if ($needsPasswordReset || $needsProfileUpdate) {
            $stmt = $pdo->prepare(
                'UPDATE users SET password_hash = :password_hash, nombre = :nombre, rol = :rol WHERE username = :username'
            );
            $stmt->execute([
                ':username' => $username,
                ':password_hash' => password_hash($defaultPassword, PASSWORD_DEFAULT),
                ':nombre' => $nombre,
                ':rol' => $rol,
            ]);
        }
    }

    return $pdo;
}

function getAllPersonas(): array
{
    $pdo = getDbConnection();
    $stmt = $pdo->query('SELECT * FROM personas ORDER BY nombre ASC');
    return $stmt->fetchAll();
}

function getAllRegistros(): array
{
    $pdo = getDbConnection();
    $stmt = $pdo->query(
        'SELECT r.*, p.nombre AS persona_nombre, p.departamento, u.nombre AS usuario_nombre
         FROM registros r
         INNER JOIN personas p ON p.id = r.persona_id
         INNER JOIN users u ON u.id = r.usuario_id
         ORDER BY r.fecha_salida DESC, r.hora_salida DESC'
    );

    return $stmt->fetchAll();
}

function getAttendanceReport(): array
{
    $pdo = getDbConnection();
    $stmt = $pdo->query(
        "SELECT p.id AS persona_id, p.nombre AS persona_nombre, p.departamento,
                r.fecha_salida AS fecha,
                MIN(CASE WHEN LOWER(TRIM(r.tipo)) = 'ingreso' THEN r.hora_salida END) AS hora_ingreso,
                MAX(CASE WHEN LOWER(TRIM(r.tipo)) = 'salida' THEN r.hora_salida END) AS hora_salida
         FROM registros r
         INNER JOIN personas p ON p.id = r.persona_id
         WHERE LOWER(TRIM(r.tipo)) IN ('ingreso', 'salida')
         GROUP BY p.id, p.nombre, p.departamento, r.fecha_salida
         ORDER BY r.fecha_salida DESC, p.nombre ASC"
    );

    return $stmt->fetchAll();
}

function getDashboardStats(): array
{
    $pdo = getDbConnection();

    $totalSalidas = (int) $pdo->query('SELECT COUNT(*) FROM registros')->fetchColumn();
    $personasActivas = (int) $pdo->query('SELECT COUNT(*) FROM personas WHERE activo = 1')->fetchColumn();
    $salidasHoy = (int) $pdo->query('SELECT COUNT(*) FROM registros WHERE fecha_salida = CURDATE()')->fetchColumn();
    $motivos = $pdo->query(
        'SELECT motivo, COUNT(*) AS total FROM registros GROUP BY motivo ORDER BY total DESC LIMIT 5'
    )->fetchAll();

    return [
        'total_salidas' => $totalSalidas,
        'personas_activas' => $personasActivas,
        'salidas_hoy' => $salidasHoy,
        'motivos' => $motivos,
    ];
}

function getOrCreatePersonaForUser(array $user): int
{
    $pdo = getDbConnection();
    $nombre = trim((string) ($user['nombre'] ?? $user['username'] ?? 'Empleado'));
    $username = trim((string) ($user['username'] ?? ''));

    $stmt = $pdo->prepare('SELECT id FROM personas WHERE nombre = :nombre LIMIT 1');
    $stmt->execute([':nombre' => $nombre]);
    $persona = $stmt->fetch();

    if ($persona) {
        return (int) $persona['id'];
    }

    $documento = $username !== '' ? $username : 'EMP-' . uniqid();
    $stmt = $pdo->prepare(
        'INSERT INTO personas (nombre, cargo, departamento, documento, activo) VALUES (:nombre, :cargo, :departamento, :documento, 1)'
    );
    $stmt->execute([
        ':nombre' => $nombre,
        ':cargo' => 'Empleado',
        ':departamento' => 'General',
        ':documento' => $documento,
    ]);

    return (int) $pdo->lastInsertId();
}

function saveNewPersona(array $data): bool
{
    $pdo = getDbConnection();

    $stmt = $pdo->prepare(
        'INSERT INTO personas (nombre, cargo, departamento, documento, activo) VALUES (:nombre, :cargo, :departamento, :documento, 1)'
    );

    return $stmt->execute([
        ':nombre' => trim($data['nombre']),
        ':cargo' => trim($data['cargo']),
        ':departamento' => trim($data['departamento']),
        ':documento' => trim($data['documento']),
    ]);
}

function saveNewRegistro(array $data, int $userId): bool
{
    $pdo = getDbConnection();
    $stmt = $pdo->prepare(
        'INSERT INTO registros (persona_id, tipo, motivo, destino, fecha_salida, hora_salida, observaciones, usuario_id)
         VALUES (:persona_id, :tipo, :motivo, :destino, :fecha_salida, :hora_salida, :observaciones, :usuario_id)'
    );

    return $stmt->execute([
        ':persona_id' => (int) $data['persona_id'],
        ':tipo' => trim($data['tipo']),
        ':motivo' => trim($data['motivo']),
        ':destino' => trim($data['destino']),
        ':fecha_salida' => $data['fecha_salida'] ?? date('Y-m-d'),
        ':hora_salida' => $data['hora_salida'] ?? date('H:i'),
        ':observaciones' => trim($data['observaciones'] ?? ''),
        ':usuario_id' => $userId,
    ]);
}
