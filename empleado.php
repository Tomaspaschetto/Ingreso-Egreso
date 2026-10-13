<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('empleado');

$error = '';
$success = '';
$user = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion !== 'Ingreso' && $accion !== 'Salida') {
        $error = 'Selecciona una acción válida.';
    } else {
        $personaId = getOrCreatePersonaForUser($user ?? ['nombre' => 'Empleado', 'username' => 'empleado']);

        $payload = [
            'persona_id' => $personaId,
            'tipo' => $accion,
            'motivo' => 'Registro rápido',
            'destino' => 'Trabajo',
            'fecha_salida' => date('Y-m-d'),
            'hora_salida' => date('H:i'),
            'observaciones' => '',
        ];

        try {
            if (saveNewRegistro($payload, (int) $_SESSION['user_id'])) {
                if ($accion === 'Salida') {
                    logoutUser();
                    header('Location: index.php?logout=1');
                    exit;
                }

                $success = 'Ingreso registrado correctamente.';
            }
        } catch (Throwable $e) {
            $error = 'No se pudo guardar el registro: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel empleado | Registro de Egreso</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div>
            <a href="empleado.php">Ingreso / Salida</a>
        </div>
        <div>
            <span>Empleado: <?php echo htmlspecialchars($user['nombre'] ?? 'Usuario'); ?></span>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </nav>

    <script>
        const SESSION_TIMEOUT_MS = 5000;
        let inactivityTimer = setTimeout(() => {
            window.location.href = 'logout.php';
        }, SESSION_TIMEOUT_MS);

        ['click', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(function (eventName) {
            document.addEventListener(eventName, function () {
                clearTimeout(inactivityTimer);
                inactivityTimer = setTimeout(() => {
                    window.location.href = 'logout.php';
                }, SESSION_TIMEOUT_MS);
            }, { passive: true });
        });
    </script>

    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Panel de empleado</h1>
                <p class="muted">Presiona el botón según tu acción.</p>
            </div>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="post" action="empleado.php" class="employee-buttons">
                <button type="submit" name="accion" value="Ingreso" class="btn btn-success btn-large">Registrar ingreso</button>
                <button type="submit" name="accion" value="Salida" class="btn btn-secondary btn-large">Registrar salida</button>
            </form>
        </div>
    </div>
</body>
</html>
