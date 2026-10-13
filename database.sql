CREATE DATABASE IF NOT EXISTS registro_egreso CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE registro_egreso;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(150) NOT NULL,
    rol VARCHAR(50) NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    cargo VARCHAR(150) NOT NULL,
    departamento VARCHAR(150) NOT NULL,
    documento VARCHAR(50) NOT NULL UNIQUE,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

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
);

-- El usuario admin y los usuarios demo se crean automáticamente desde PHP.
-- Si se quiere regenerar la base desde cero, se recomienda no insertar usuarios aquí.
-- Usuarios demo (la contraseña para todos es 123456):
-- admin / 123456
-- empleado / 123456
-- juan.perez / 123456
-- maria.lopez / 123456
-- carlos.garcia / 123456
-- ana.morales / 123456
-- diego.rivera / 123456
-- sofia.navarro / 123456
    