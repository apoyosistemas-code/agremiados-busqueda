# Sistema de Gestión de Agremiados e Intranet Administrativa - ICAJ

Plataforma web integral para la gestión del padrón de agremiados del **Colegio de Abogados de Junín**, control de usuarios, auditoría y automatización de saludos de cumpleaños.

> **Desarrollado por:** [EKUBYTE](https://ekubyte.com/)  
> *Innovación y Tecnología para tu institución.*

---

## 🚀 Novedades y Características Recientes

### 🎂 Módulo de Cumpleaños (Mejorado)
El sistema de notificaciones ahora incluye integración directa con WhatsApp y herramientas de portapapeles:
* **Envío por Correo:** Plantillas HTML elegantes con pie de página personalizado ("Powered by EKUBYTE") y tarjeta adjunta.
* **Integración WhatsApp:**
    * **Botón Copiar Texto:** Copia un saludo formal al portapapeles con un clic.
    * **Botón WhatsApp Directo:** Abre la app o web de WhatsApp con el chat del agremiado y el mensaje ya redactado.
* **Copiado Rápido de Imagen:** Nuevo botón flotante sobre la vista previa de la tarjeta para copiar la imagen generada y pegarla directamente (Ctrl+V) en chats.

---

## 🛠️ Stack Tecnológico

* **Backend:** PHP Nativo (Sin frameworks, alto rendimiento).
* **Base de Datos:** MySQL / MariaDB.
* **Frontend:** Bootstrap 5 + JavaScript (Fetch API) + CSS Personalizado.
* **Librerías:**
    * `PHPMailer`: Envío SMTP seguro.
    * `GD Library`: Generación dinámica de imágenes (tarjetas).

---

## 📂 Estructura del Proyecto

### 1. Núcleo
* **`config.php`**: Credenciales de BD (Mantener fuera del público en producción).
* **`conexion.php`**: Gestión de conexión `mysqli` y sesiones globales.
* **`motor_envios.php`**: **Microservicio de Notificaciones**.
    * Genera la tarjeta JPG combinando `plantilla.jpg` + Texto dinámico.
    * Envía el correo HTML con enlace al desarrollador (EKUBYTE).
    * Limpia archivos temporales automáticamente.

### 2. Módulos Principales
* **`cumpleanos.php`**: Panel de control para gestionar los saludos del día/mes.
* **`api_editor.php`**: CRUD de agremiados con auditoría de cambios.
* **`api_sugerencias.php`**: Buscador inteligente (Fulltext Search).
* **`api_usuarios.php`**: Gestión de usuarios administradores (Rol Master).

### 3. Utilidades de Prueba
* **`test_envio.php`**: Script para diagnosticar la conexión SMTP y previsualizar el diseño final del correo (incluyendo el footer de EKUBYTE) sin afectar a los agremiados reales.

---

## ⚙️ Instalación y Configuración

1.  **Base de Datos:**
    Importar `agremiados_db.sql`. Esto creará las tablas y triggers necesarios para la limpieza de datos.

2.  **Conexión:**
    Editar `config.php` con tus credenciales locales o del servidor:
    ```php
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', 'tu_clave');
    ```

3.  **Correo SMTP:**
    En `motor_envios.php`, configurar las credenciales de Gmail/SMTP:
    ```php
    $mail->Username = 'tu_correo@gmail.com';
    $mail->Password = 'tu_contraseña_de_aplicacion';
    ```

4.  **Permisos:**
    Asegurar escritura en `assets_cumple/temp/` para la generación de tarjetas.

---

## 🛡️ Seguridad

* **Protección SQL:** Uso estricto de Sentencias Preparadas en todo el sistema.
* **Bloqueo de Archivos:** `.htaccess` configurado para impedir la descarga de `.sql`, `.git` o `config.php`.
* **Auditoría Forense:** Registro inmutable de acciones (Login, Edición, Creación) con IP y usuario responsable.

---

## ✒️ Créditos

Este sistema ha sido diseñado y programado por el equipo de **EKUBYTE**.
* **Web:** [ekubyte.com](https://ekubyte.com/)
* **Soporte:** Contactar al área de desarrollo.

*© 2024-2026 Ilustre Colegio de Abogados de Junín*