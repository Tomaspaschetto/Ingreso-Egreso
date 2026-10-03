<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$error = '';
$success = '';
$personas = getAllPersonas();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'persona_id' => $_POST['persona_id'] ?? '',
        'tipo' => $_POST['tipo'] ?? '',
        'motivo' => $_POST['motivo'] ?? '',
        'destino' => $_POST['destino'] ?? '',
        'fecha_salida' => $_POST['fecha_salida'] ?? date('Y-m-d'),
        'hora_salida' => $_POST['hora_salida'] ?? date('H:i'),
        'observaciones' => $_POST['observaciones'] ?? '',
    ];

    if (empty($payload['persona_id']) || empty($payload['tipo']) || empty($payload['motivo']) || empty($payload['destino'])) {
        $error = 'Debe completar todos los campos obligatorios.';
    } else {
        try {
            if (saveNewRegistro($payload, (int) $_SESSION['user_id'])) {
                redirect('dashboard.php?success=1');
            }
        } catch (Throwable $e) {
            $error = 'No se pudo guardar la salida: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar salida | Registro de Egreso</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/js/app.js"></script>
</head>
<body>
    <nav class="navbar">
        <div>
            <a href="dashboard.php">Inicio</a>
            <a href="registrar_salida.php">Registrar salida</a>
            <a href="personas.php">Personas</a>
            <a href="reportes.php">Reportes</a>
        </div>
        <div>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Registrar salida</h1>
                <p class="muted">Capture la salida de una persona del área.</p>
            </div>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="post" action="registrar_salida.php">
                <div class="row two-col">
                    <div class="form-group">
                        <label for="persona_id">Persona</label>
                        <select id="persona_id" name="persona_id" required>
                            <option value="">Seleccione una persona</option>
                            <?php foreach ($personas as $persona): ?>
                                <option value="<?php echo (int) $persona['id']; ?>"><?php echo htmlspecialchars($persona['nombre']); ?> - <?php echo htmlspecialchars($persona['departamento']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="tipo">Tipo de salida</label>
                        <select id="tipo" name="tipo" required>
                            <option value="Salida normal">Salida normal</option>
                            <option value="Salida por permiso">Salida por permiso</option>
                            <option value="Emergencia">Emergencia</option>
                            <option value="Visita">Visita</option>
                        </select>
                    </div>
                </div>

                <div class="row two-col">
                    <div class="form-group">
                        <label for="fecha_salida">Fecha</label>
                        <input type="date" id="fecha_salida" name="fecha_salida" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="hora_salida">Hora</label>
                        <input type="time" id="hora_salida" name="hora_salida" value="<?php echo date('H:i'); ?>" required>
                    </div>
                </div>

                <div class="row two-col">
                    <div class="form-group">
                        <label for="motivo">Motivo</label>
                        <input type="text" id="motivo" name="motivo" placeholder="Ejemplo: Reunión externa" required>
                    </div>

                    <div class="form-group">
                        <label for="destino">Destino</label>
                        <input type="text" id="destino" name="destino" placeholder="Ejemplo: Sucursal Norte" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="4" placeholder="Detalles extra importantes..."></textarea>
                </div>

                <button type="submit">Guardar salida</button>
            </form>
        </div>
    </div>
</body>
</html>
