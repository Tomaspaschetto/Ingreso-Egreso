<?php

session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

const SESSION_TIMEOUT_SECONDS = 5;

function touchSession(): void
{
    $_SESSION['last_activity'] = time();
}

function checkSessionTimeout(): void
{
    if (!isset($_SESSION['user_id'])) {
        return;
    }

    if (strtolower((string) ($_SESSION['user_rol'] ?? '')) === 'admin') {
        touchSession();
        return;
    }

    $lastActivity = $_SESSION['last_activity'] ?? time();
    if ((time() - (int) $lastActivity) > SESSION_TIMEOUT_SECONDS) {
        logoutUser();
        header('Location: index.php?expired=1');
        exit;
    }

    touchSession();
}

function requireLogin(): void
{
    checkSessionTimeout();

    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}

function requireRole(string $role): void
{
    requireLogin();

    $user = currentUser();
    if (!$user || strtolower((string) $user['rol']) !== strtolower($role)) {
        header('Location: index.php');
        exit;
    }
}

function currentUser(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id');
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    return $user ?: null;
}

function loginUser(string $username, string $password): bool
{
    $pdo = getDbConnection();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => trim($username)]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = $user['nombre'];
    $_SESSION['user_rol'] = $user['rol'];
    touchSession();
    return true;
}

function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}
