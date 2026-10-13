<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$pdo = getDbConnection();
$departamentos = $pdo->query(
    'SELECT departamento, COUNT(*) AS total FROM personas GROUP BY departamento ORDER BY total DESC'
)->fetchAll();

$registros = getAttendanceReport();
$salidasHoy = (int) $pdo->query('SELECT COUNT(*) FROM registros WHERE fecha_salida = CURDATE()')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes | Registro de Egreso</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                <h1 class="page-title">Reportes</h1>
                <p class="muted">Estadísticas útiles del sistema.</p>
            </div>
        </div>

        <div class="row two-col">
            <div class="card">
                <h3>Por departamento</h3>
                <?php if (empty($departamentos)): ?>
                    <p class="muted">No hay departamentos registrados.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($departamentos as $departamento): ?>
                            <li><?php echo htmlspecialchars($departamento['departamento']); ?> - <?php echo (int) $departamento['total']; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>Resumen</h3>
                <p><strong>Personas registradas:</strong> <?php echo count(getAllPersonas()); ?></p>
                <p><strong>Jornadas registradas:</strong> <?php echo count($registros); ?></p>
                <p><strong>Salidas de hoy:</strong> <?php echo $salidasHoy; ?></p>
            </div>
        </div>

        <div class="card" style="margin-top:1.5rem;">
            <div class="page-header">
                <h3>Asistencia por día</h3>
                <a class="btn btn-primary" href="reportes_pdf.php">Descargar PDF</a>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Persona</th>
                            <th>Departamento</th>
                            <th>Fecha</th>
                            <th>Hora de ingreso</th>
                            <th>Hora de salida</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registros)): ?>
                            <tr>
                                <td colspan="5">No hay registros de ingreso o salida para mostrar.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($registros as $registro): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($registro['persona_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($registro['departamento']); ?></td>
                                    <td><?php echo formatDate($registro['fecha']); ?></td>
                                    <td><?php echo htmlspecialchars($registro['hora_ingreso'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($registro['hora_salida'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
