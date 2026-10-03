<?php
require_once __DIR__ . '/includes/auth.php';

if (isset($_SESSION['user_id'])) {
    $user = currentUser();
    if (($user['rol'] ?? '') === 'empleado') {
        redirect('empleado.php');
    }
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!loginUser($username, $password)) {
        $error = 'Usuario o contraseña incorrectos.';
    } else {
        $user = currentUser();
        if (($user['rol'] ?? '') === 'empleado') {
            redirect('empleado.php');
        }
        redirect('dashboard.php');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Registro de Egreso</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-wrap">
        <div class="login-card">
            <h1>Registro de Egreso</h1>
            <p class="muted">Sistema de control y seguimiento de salidas</p>

            <?php if (isset($_GET['expired'])): ?>
                <div class="alert alert-error">La sesión expiró por inactividad.</div>
            <?php endif; ?>

            <?php if (isset($_GET['logout'])): ?>
                <div class="alert alert-success">Sesión cerrada correctamente.</div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post" action="index.php">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input type="text" id="username" name="username" placeholder="admin o empleado" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Ingrese su contraseña" required>
                </div>

                <button type="submit">Iniciar sesión</button>
            </form>

            <p class="muted" style="margin-top:1rem; text-align:center;">Demo: admin / 123456 | empleado / 123456</p>
        </div>
    </div>
</body>
</html>
