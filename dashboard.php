<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireRole('admin');
$stats = getDashboardStats();
$registros = getAllRegistros();
$user = currentUser();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Registro de Egreso</title>
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
            <span>Bienvenido, <?php echo htmlspecialchars($user['nombre'] ?? 'Usuario'); ?></span>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="muted">Resumen del sistema de egreso</p>
            </div>
        </div>

        <div class="grid grid-3">
            <div class="card">
                <div class="stat">
                    <div>
                        <div class="muted">Total de salidas</div>
                        <div class="stat-number"><?php echo $stats['total_salidas']; ?></div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="stat">
                    <div>
                        <div class="muted">Personas activas</div>
                        <div class="stat-number"><?php echo $stats['personas_activas']; ?></div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="stat">
                    <div>
                        <div class="muted">Salidas hoy</div>
                        <div class="stat-number"><?php echo $stats['salidas_hoy']; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row two-col" style="margin-top:1.5rem;">
            <div class="card">
                <h3>Motivos más frecuentes</h3>
                <?php if (empty($stats['motivos'])): ?>
                    <p class="muted">Todavía no hay registros.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($stats['motivos'] as $motivo): ?>
                            <li><?php echo htmlspecialchars($motivo['motivo']); ?> - <?php echo (int) $motivo['total']; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>Acciones rápidas</h3>
                <div class="row">
                    <a href="registrar_salida.php" class="btn btn-primary" style="display:inline-block; text-decoration:none; padding:0.8rem 1rem; background:#123c69; color:#fff; border-radius:10px;">Registrar salida</a>
                    <a href="personas.php" class="btn btn-secondary" style="display:inline-block; text-decoration:none; padding:0.8rem 1rem; background:#5a6e7d; color:#fff; border-radius:10px;">Gestionar personas</a>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top:1.5rem;">
            <h3>Últimos registros</h3>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Persona</th>
                            <th>Tipo</th>
                            <th>Motivo</th>
                            <th>Destino</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($registros)): ?>
                            <tr>
                                <td colspan="6">No hay registros en el sistema.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($registros as $registro): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($registro['persona_nombre']); ?></td>
                                    <td><span class="badge badge-<?php echo getStatusBadge($registro['tipo']); ?>"><?php echo htmlspecialchars($registro['tipo']); ?></span></td>
                                    <td><?php echo htmlspecialchars($registro['motivo']); ?></td>
                                    <td><?php echo htmlspecialchars($registro['destino']); ?></td>
                                    <td><?php echo formatDate($registro['fecha_salida']); ?></td>
                                    <td><?php echo htmlspecialchars($registro['hora_salida']); ?></td>
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
