# Gestión de Prácticas FCT - IES Emili Darder

Este proyecto permite a un centro educativo gestionar las solicitudes de prácticas FCT de las empresas, así como la asignación de alumnos según perfil, promoción, curso y convocatoria. Está basado en PHP, MySQL y Bootstrap, y se ejecuta en un entorno Docker.

---

## 1. Puesta en marcha del entorno Docker

### Estructura de ficheros
```
/project
│
├── docker-compose.yml
├── Dockerfile
├── .env
├── init.sql
├── /html
│   ├── login.php
│   ├── logout.php
│   ├── dashEmpresa.php
│   ├── dashIES.php
│   ├── tramitar.php
│   └── ...
```

### 1.1 Dockerfile
```Dockerfile
FROM php:8.2-apache
RUN docker-php-ext-install mysqli
COPY html/ /var/www/html/
EXPOSE 80
```

### 1.2 docker-compose.yml
```yaml
version: '3.8'

services:
  web:
    build: .
    ports:
      - "8081:80"
    volumes:
      - ./html:/var/www/html
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: practicas
      MYSQL_USER: usuario
      MYSQL_PASSWORD: clave123
      MYSQL_ROOT_PASSWORD: root123
    volumes:
      - db_data:/var/lib/mysql
      - ./init.sql:/docker-entrypoint-initdb.d/init.sql

volumes:
  db_data:
```

### 1.3 .env (ejemplo, si se usa configuración externa)
```
MYSQL_DATABASE=practicas
MYSQL_USER=usuario
MYSQL_PASSWORD=clave123
MYSQL_ROOT_PASSWORD=root123
```

---

## 2. Fichero SQL - init.sql

```sql
DROP DATABASE IF EXISTS practicas;
CREATE DATABASE practicas;
USE practicas;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_empresa VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    rol ENUM('empresa', 'profesor') NOT NULL DEFAULT 'empresa'
);

CREATE TABLE alumnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255),
    apellidos VARCHAR(255),
    grado ENUM('SMX','ASIX','DAW'),
    curso INT,
    promocion YEAR,
    convocatoria ENUM('Junio','Septiembre')
);

CREATE TABLE solicitudes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    convocatoria ENUM('Junio','Septiembre') NOT NULL,
    promocion YEAR NOT NULL,
    curso INT NOT NULL,
    grado ENUM('SMX','ASIX','DAW') NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('Pendiente','Tramitando','Aceptada','Denegada') DEFAULT 'Pendiente',
    numero_solicitud VARCHAR(100) UNIQUE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE asignaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_id INT NOT NULL,
    alumno_id INT NOT NULL,
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id),
    FOREIGN KEY (alumno_id) REFERENCES alumnos(id)
);
```

---

## 3. Ficheros PHP y su función

### `login.php`
Formulario y proceso de autenticación de usuarios (empresas y profesor).

### `logout.php`
Cierra la sesión actual del usuario.

### `dashEmpresa.php`
Dashboard de la empresa. Permite:
- Crear solicitudes nuevas por grado (SMX, ASIX, DAW).
- Ver solicitudes activas.
- Eliminar solicitudes erróneas.

### `dashIES.php`
Dashboard del profesor. Permite:
- Ver resumen de empresas con solicitudes pendientes.
- Acceder a tramitación de solicitudes.
- Añadir nuevos alumnos con todos sus campos.

### `tramitar.php`
Pantalla para tramitar solicitudes de una empresa:
- Selección de solicitud específica.
- Listado de alumnos compatibles.
- Posibilidad de aceptar o denegar solicitudes.

---

## Autoría
IES Emili Darder — Proyecto de gestión de prácticas FCT  
Basado en PHP + MySQL + Bootstrap + Docker  
Junio 2025
