<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireLogin();

$error = '';
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $pdo = getDbConnection();
    $pdo->beginTransaction();
    $pdo->prepare('DELETE FROM registros WHERE persona_id = :id')->execute([':id' => $id]);
    $pdo->prepare('DELETE FROM personas WHERE id = :id')->execute([':id' => $id]);
    $pdo->commit();
    redirect('personas.php?success=deleted');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = [
        'nombre' => $_POST['nombre'] ?? '',
        'cargo' => $_POST['cargo'] ?? '',
        'departamento' => $_POST['departamento'] ?? '',
        'documento' => $_POST['documento'] ?? '',
    ];

    if (empty($payload['nombre']) || empty($payload['cargo']) || empty($payload['departamento']) || empty($payload['documento'])) {
        $error = 'Debe completar todos los campos para registrar la persona.';
    } else {
        try {
            if (saveNewPersona($payload)) {
                redirect('personas.php?success=1');
            }
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $error = 'No se pudo registrar: ya existe una persona con ese DNI o nombre de usuario.';
            } else {
                $error = 'No se pudo registrar la persona: ' . $e->getMessage();
            }
        } catch (Throwable $e) {
            $error = 'No se pudo registrar la persona: ' . $e->getMessage();
        }
    }
}

$personas = getAllPersonas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personas | Registro de Egreso</title>
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
                <h1 class="page-title">Personas</h1>
                <p class="muted">Administración del personal registrado.</p>
            </div>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Operación realizada correctamente.</div>
        <?php endif; ?>

        <div class="row two-col">
            <div class="card">
                <h3>Agregar persona</h3>
                <form method="post" action="personas.php">
                    <div class="form-group">
                        <label for="nombre">Nombre completo</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>

                    <div class="form-group">
                        <label for="cargo">Cargo</label>
                        <input type="text" id="cargo" name="cargo" required>
                    </div>

                    <div class="form-group">
                        <label for="departamento">Departamento</label>
                        <input type="text" id="departamento" name="departamento" required>
                    </div>

                    <div class="form-group">
                        <label for="documento">DNI (también será la contraseña)</label>
                        <input type="text" id="documento" name="documento" required>
                    </div>

                    <button type="submit">Registrar persona y usuario</button>
                </form>
            </div>

            <div class="card">
                <h3>Listado de personas</h3>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Cargo</th>
                                <th>Departamento</th>
                                <th>Documento</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($personas)): ?>
                                <tr>
                                    <td colspan="5">No hay personas registradas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($personas as $persona): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($persona['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['cargo']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['departamento']); ?></td>
                                        <td><?php echo htmlspecialchars($persona['documento']); ?></td>
                                        <td class="actions">
                                            <a href="personas.php?delete=<?php echo (int) $persona['id']; ?>" onclick="return confirm('¿Eliminar esta persona?')">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
