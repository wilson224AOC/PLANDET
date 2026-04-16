# Configuración SMTP para Envío de Correos - PLANDET

## Instrucciones para Configurar Gmail

El sistema está configurado para enviar correos usando **walonsozprah@gmail.com**. Para que funcione correctamente, necesitas generar una **contraseña de aplicación** desde tu cuenta de Google.

### Pasos para Generar Contraseña de Aplicación:

1. **Ir a tu cuenta Google**
   - Abre https://myaccount.google.com/
   - Inicia sesión con walonsozprah@gmail.com

2. **Habilitar Autenticación de Dos Factores** (si no lo has hecho)
   - Ve a "Seguridad" en el menú izquierdo
   - Busca "Verificación de 2 pasos"
   - Actívalo siguiendo las instrucciones

3. **Generar Contraseña de Aplicación**
   - Ve a "Seguridad" nuevamente
   - Desplázate hacia abajo hasta encontrar "Contraseñas de aplicación"
   - Selecciona: Aplicación = "Correo" y Dispositivo = "Windows / Linux / Mac"
   - Google generará una contraseña de 16 caracteres

4. **Copiar la Contraseña Generada**
   - Google te proporcionará algo como: `abcd efgh ijkl mnop` (sin espacios es: `abcdefghijklmnop`)

### Actualizar el Archivo de Configuración:

5. **Editar `config/smtp.php`**
   ```php
   'password' => 'abcdefghijklmnop', // Reemplaza con tu contraseña de aplicación
   ```

### Prueba:

Una vez configurado, el sistema enviará automáticamente:

- **Correo de Confirmación**: Cuando el usuario registra una solicitud de reunión
- **Correo de Aprobación**: Cuando el administrador aprueba y programa la reunión
  - Incluye la fecha y hora de la reunión
- **Correo de Rechazo**: Cuando el administrador rechaza la solicitud
  - Incluye el motivo del rechazo

### Formato de Correos:

Todos los correos incluyen:
- **Código de seguimiento** de la reunión
- **Estado actual** del trámite
- **Detalles de fecha y hora** (si aplica)
- **Motivo de rechazo** (si fue rechazado)
- Formato profesional y responsivo

### Troubleshooting:

Si los correos no se envían:

1. **Verifica la contraseña** esté correctamente copiada en `config/smtp.php`
2. **Revisa los logs** en la base de datos tabla `meeting_notifications` para ver el error
3. **Comprueba que SMTP está habilitado** en `config/smtp.php`: `'enabled' => true`
4. **Verifica la conexión a internet** del servidor
5. **Desactiva "Aplicaciones menos seguras"** si Google lo sugiere (aunque no debería ser necesario con contraseña de aplicación)

### Configuración Actual:

- **Host**: smtp.gmail.com
- **Puerto**: 587
- **Encriptación**: TLS
- **Usuario**: walonsozprah@gmail.com
- **Remitente**: walonsozprah@gmail.com
- **Nombre**: PLANDET - Sistema de Reuniones

---

**Nota**: Las contraseñas de aplicación expiran si no las usas durante 30 días. Si dejas de recibir correos, regénera una nueva.
