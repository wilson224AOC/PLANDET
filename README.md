"# PLANDET - Sistema de Gestión de Reuniones

Sistema de calendario para solicitar, aprobar y programar reuniones. Permite a usuarios registrar solicitudes y a administradores gestionar las reuniones con notificaciones por correo electrónico.

## 🎯 Características Principales

✅ **Solicitud de Reuniones**: Usuarios pueden registrar solicitudes con sus datos personales  
✅ **Panel Administrativo**: Gestión de solicitudes (aprobación, rechazo, programación)  
✅ **Notificaciones por Correo**: Correos automáticos en cada cambio de estado  
✅ **Calendario de Reuniones**: Vista de reuniones aprobadas programadas  
✅ **Seguimiento**: Módulo de consulta por código o DNI  
✅ **Gestión de Dispositivos**: Control de acceso por dispositivo  

## 📋 Requisitos

- **PHP** >= 7.4
- **MySQL** >= 5.7 o **MariaDB**
- **Servidor Web** (Apache, Nginx, etc.)

## ⚙️ Instalación

### 1. Clonar o Descargar el Proyecto

```bash
git clone <url-del-repo> PLANDET
cd PLANDET
```

### 2. Configurar Base de Datos

1. **Edita `config/db.php`** con tus credenciales:
```php
$host = "127.0.0.1";      // Tu host MySQL
$port = "3307";           // Tu puerto MySQL
$db_name = "meeting_system";
$username = "root";       // Tu usuario
$password = "";           // Tu contraseña
```

2. **Ejecuta el script SQL** para crear las tablas:
```bash
mysql -u root -p meeting_system < database.sql
```

### 3. Configurar SMTP para Correos

1. **Edita `config/smtp.php`** (ya está preconfigurado para Gmail):
```php
'enabled' => true,
'host' => 'smtp.gmail.com',
'port' => 587,
'username' => 'walonsozprah@gmail.com',
'password' => 'TU_CONTRASEÑA_DE_APLICACIÓN', // Reemplaza esto
```

2. **Generá contraseña de aplicación Gmail**:
   - Ve a https://myaccount.google.com/
   - Seguridad → Contraseñas de aplicación
   - Selecciona: Correo / Windows-Linux-Mac
   - Copia la contraseña generada

3. **Prueba la configuración**: 
   - Abre `http://localhost/PLANDET/public/test_smtp.php`
   - Envía un correo de prueba

### 4. Crear Usuario Admin

La tabla `admins` se crea automáticamente con el script SQL. Inserta un usuario:

```sql
INSERT INTO admins (username, password) VALUES (
  'admin',
  '$2y$10$9GlDYwF.2hEGPLR1bvIL/OjqyBL4AjsCPwUuKFCMkn1TKvE2Aq1oC'
);
-- Contraseña: admin123
```

O usa el script `public/check_setup.php` que lo crea automáticamente.

## 🚀 Uso del Sistema

### Para Usuarios Finales

1. **Acceder**: `http://localhost/PLANDET/public/index.php`
2. **Solicitar Reunión**: Llenar el formulario con datos personales y motivo
3. **Rastrear**: Usar código o DNI en el módulo de seguimiento

### Para Administradores

1. **Login**: `http://localhost/PLANDET/public/index.php?controller=auth&action=login`
   - Usuario: `admin`
   - Contraseña: `admin123`

2. **Panel**: Ver todas las solicitudes pendientes
3. **Acciones**:
   - **Programar**: Seleccionar fecha y hora (detecta conflictos automáticamente)
   - **Rechazar**: Indicar razón (enviará al usuario)

## 📧 Notificaciones por Correo

El sistema envía correos automáticos en los siguientes eventos:

### ✉️ Solicitud Recibida
Cuando el usuario registra una nueva solicitud:
- Confirmación de recepción
- Código de seguimiento
- Invitación a rastrear el estado

### ✉️ Solicitud Aprobada
Cuando el administrador programa la reunión:
- Confirmación de aprobación
- **Fecha y hora exactas**
- Instrucciones de presentación

### ✉️ Solicitud Rechazada
Cuando el administrador rechaza:
- Notificación de rechazo
- **Motivo explicado**
- Opción de registrar nueva solicitud

## 🗂️ Estructura de Directorios

```
PLANDET/
├── app/
│   ├── controllers/         # Lógica de negocio
│   ├── models/             # Modelos de datos
│   ├── services/           # Servicios (SMTP, WhatsApp, etc)
│   └── views/              # Templates HTML
├── config/
│   ├── db.php              # Configuración MySQL
│   ├── smtp.php            # Configuración Correos
│   └── whatsapp.php        # Configuración WhatsApp (opcional)
├── public/
│   ├── index.php           # Punto de entrada
│   ├── check_setup.php     # Diagnóstico del sistema
│   ├── test_smtp.php       # Prueba de correos
│   └── css/                # Estilos
└── database.sql            # Script de creación de BD
```

## 🔍 Diagnóstico y Troubleshooting

### Verificar Instalación
Abre: `http://localhost/PLANDET/public/check_setup.php`

Verifica:
- ✅ Conexión a Base de Datos
- ✅ Usuario Admin
- ✅ Contraseña Admin

### Problemas con Correos

**Los correos no se envían:**
1. Verifica `config/smtp.php` esté correctamente configurado
2. Prueba con `public/test_smtp.php`
3. Revisa logs en tabla `meeting_notifications`
4. Comprueba conexión a internet del servidor

**Error: "Falta host, username, password":**
- Edita `config/smtp.php` e ingresa los valores faltantes

**Error: "SMTP deshabilitado":**
- Cambia `'enabled' => false` a `'enabled' => true` en `config/smtp.php`

## 🔐 Seguridad

### Recomendaciones

- 🔒 **Cambia la contraseña del admin** en producción
- 🔗 **Usa HTTPS** en producción
- 🛡️ **Protege `config/`** del acceso web
- 📋 **Valida entrada** de usuarios en frontend/backend
- 🔑 **Usa contraseñas de aplicación** para Gmail (no la contraseña principal)

### Gestión de Sesiones

Las sesiones administrador son protegidas:
- Validación en cada acción
- Redirección a login si no autenticado
- Token de dispositivo para módulo de calendario

## 📚 Documentación

- `SMTP_CONFIG.md` - Guía detallada de configuración de correos
- `database.sql` - Esquema de base de datos
- Comentarios en código

## 🐛 Reportar Bugs

Para reportar errores:
1. Reproduce el problema
2. Abre `public/check_setup.php` para diagnóstico
3. Revisa los logs de error en servidor
4. Documenta pasos para reproducir

## 📝 Changelog

### v1.0 - Inicial
- ✅ Sistema base de solicitudes
- ✅ Panel administrativo
- ✅ Notificaciones por correo
- ✅ Módulo de seguimiento
- ✅ Gestión de dispositivos

## 📄 Licencia

Este proyecto es privado y está destinado para uso interno.

---

**Última actualización**: Abril 2026  
**Versión**: 1.0  
**Estado**: En producción" 
