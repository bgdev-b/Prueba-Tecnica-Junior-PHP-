# Prueba Técnica Junior PHP

Este proyecto es una **prueba técnica** desarrollada en PHP puro como parte de un proceso de selección para desarrollador junior.

## Stack

- PHP (sin frameworks)
- MySQL / MariaDB
- Bootstrap 5 + SweetAlert2
- Vercel (deploy) + Railway (base de datos)

## Funcionalidades

- Registro e inicio de sesión de usuarios
- Crear, completar y eliminar tareas
- Filtro por estado (todas / pendientes / completadas)
- Descripción opcional por tarea
- Cache local con `localStorage`

## Estructura

```
app/
  config/db.php          # Conexión a base de datos
  middleware/auth.php    # Guard de sesión
auth/                    # Controladores de autenticación
tasks/                   # Endpoints API de tareas
assets/
  css/style.css
  js/script.js
index.php                # Login / Registro
dashboard.php            # Vista principal
```

## Variables de entorno

Configurar en Vercel → **Project Settings → Environment Variables**:
`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`
