<?php

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function formatDate(string $date): string
{
    if ($date === '' || $date === null) {
        return 'Sin fecha';
    }

    try {
        return date('d/m/Y', strtotime($date));
    } catch (Throwable $e) {
        return $date;
    }
}

function formatDateTime(?string $dateTime): string
{
    if (empty($dateTime)) {
        return 'Sin información';
    }

    try {
        return date('d/m/Y H:i', strtotime($dateTime));
    } catch (Throwable $e) {
        return $dateTime;
    }
}

function getStatusBadge(string $tipo): string
{
    $map = [
        'Ingreso' => 'primary',
        'Salida normal' => 'success',
        'Salida por permiso' => 'info',
        'Emergencia' => 'danger',
        'Visita' => 'warning',
        'Salida' => 'secondary',
    ];

    return $map[$tipo] ?? 'secondary';
}
