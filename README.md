# Sistema de Gestión de Agremiados e Intranet Administrativa - ICAJ

Este proyecto es una plataforma web integral desarrollada para el **Colegio de Abogados de Junín (ICAJ)**. Permite la gestión del padrón de agremiados, control de usuarios administrativos, auditoría de seguridad y un sistema automatizado para el envío de saludos de cumpleaños.

## 🛠️ Stack Tecnológico

* **Lenguaje:** PHP (Nativo, sin frameworks pesados).
* **Base de Datos:** MySQL / MariaDB (Motor InnoDB).
* **Frontend:** HTML5, CSS3 (Variables nativas), JavaScript (Fetch API).
* **Diseño:** Bootstrap 5 (Grilla y Componentes) + CSS personalizado (`style.css`).
* **Librerías Externas:**
    * **PHPMailer:** Para el envío de correos SMTP.
    * **GD Library:** Para la generación dinámica de tarjetas de cumpleaños (imágenes).
    * **FontAwesome:** Iconografía.

---

## 📂 Estructura del Proyecto

El sistema está modularizado en **Backend (APIs)**, **Frontend (Vistas)** y **Núcleo (Configuración)**.

### 1. Núcleo y Configuración
Archivos esenciales para el funcionamiento del servidor y la base de datos.

* **`config.php`**: Define las credenciales de la base de datos (`DB_HOST`, `DB_USER`, etc.). **Importante:** En producción, este archivo debe protegerse o moverse fuera del directorio público.
* **`conexion.php`**:
    * Establece la conexión `mysqli` con manejo de errores (`try-catch`).
    * Inicia la sesión (`session_start`) de forma global.
    * Contiene la función `registrar_auditoria()` para guardar logs de actividad en la BD.
* **`.htaccess`**: Capa de seguridad del servidor Apache.
    * Bloquea el listado de directorios.
    * Protege archivos sensibles (`.sql`, `.git`, `config.php`) de descargas directas.
    * Configura cabeceras de seguridad (`X-Frame-Options`, `X-Content-Type-Options`).

### 2. Autenticación y Seguridad
* **`login.php`**: Formulario de acceso. Verifica contraseña encriptada (`password_verify`) y asigna variables de sesión (`$_SESSION`).
* **`auth.php`**: Middleware de seguridad. Se incluye al inicio de archivos protegidos para verificar si el usuario está logueado; si no, redirige al login.
* **`logout.php`**: Cierra la sesión y redirige al inicio.

### 3. Módulos y APIs (Backend)
Estos archivos procesan la lógica de negocio y devuelven respuestas en formato JSON.

* **`api_editor.php`**:
    * Maneja el CRUD (Crear, Leer, Actualizar) de agremiados.
    * Utiliza una *whitelist* (`get_columns`) para asegurar que solo se editen campos válidos.
    * Registra auditoría automática tras cada inserción o cambio.
* **`api_sugerencias.php`**:
    * Motor de búsqueda para el autocompletado.
    * Implementa lógica *Fulltext Boolean Mode* para búsquedas inteligentes (ej: encuentra "Juan Perez" aunque escribas "Perez Juan").
* **`api_usuarios.php`**:
    * Gestión de usuarios administradores (solo accesible para rol `MASTER`).
    * Impide eliminar al Super Admin (ID 1) o al propio usuario logueado.
* **`api_cumpleanos.php`**:
    * Filtra agremiados que cumplen años en el mes/día seleccionado.
    * Normaliza nombres (ej: convierte "GARCIA" a "García") para una mejor presentación.
* **`motor_envios.php`**: **Microservicio de Notificaciones**.
    * **Generación de Imagen:** Crea una tarjeta JPG personalizada usando `plantilla.jpg` y una fuente TTF, centrando el nombre del agremiado dinámicamente.
    * **Envío de Correo:** Usa PHPMailer para enviar un email HTML con la tarjeta adjunta vía SMTP (Gmail).
    * **Limpieza:** Borra imágenes temporales antiguas (> 2 horas).

### 4. Vistas (Frontend)
Interfaces de usuario que consumen las APIs.

* **`index.php`**: Página de inicio. Muestra el buscador principal y la barra de usuario.
* **`buscar.php`**: Muestra los resultados de búsqueda de agremiados en tarjetas o lista.
* **`editor.php`**: Formulario modal para agregar o editar la información de un agremiado.
* **`cumpleanos.php`**: Dashboard para ver los cumpleaños del día, generar tarjetas masivas y enviarlas por correo.
* **`usuarios.php`**: Panel de control para crear y eliminar usuarios del sistema (Admin/Master).
* **`auditoria.php`**: Tabla visual de logs que muestra quién hizo qué, cuándo y desde qué IP.

---

## 🚀 Instalación y Despliegue

### Requisitos Previos
* Servidor Web (Apache/Nginx).
* PHP 7.4 o superior (extensiones necesarias: `mysqli`, `gd`, `mbstring`).
* MySQL o MariaDB.

### Pasos
1.  **Base de Datos:**
    * Importar el archivo `agremiados_db.sql` en tu gestor de base de datos.
    * Esto creará las tablas: `agremiados`, `usuarios`, `auditoria` y los triggers necesarios.

2.  **Configuración:**
    * Editar `config.php` con las credenciales de tu servidor local o producción:
        ```php
        define('DB_HOST', 'localhost');
        define('DB_USER', 'tu_usuario');
        define('DB_PASS', 'tu_password');
        ```

3.  **Configuración de Correo (SMTP):**
    * Abrir `motor_envios.php`.
    * Actualizar las credenciales de Gmail (`Username`, `Password`) en la sección de PHPMailer.
    * *Nota: Si usas Gmail, asegúrate de generar una "Contraseña de Aplicación".*

4.  **Permisos:**
    * Asegurar permisos de escritura en la carpeta `assets_cumple/temp/` para la generación de imágenes temporales.

---

## 🛡️ Seguridad Implementada

1.  **Inyección SQL:** Todo el sistema utiliza **Sentencias Preparadas** (`$stmt->prepare`) para prevenir ataques.
2.  **XSS (Cross-Site Scripting):** Los datos mostrados en HTML se escapan usando `htmlspecialchars()`.
3.  **Control de Sesiones:** Validación estricta en `auth.php`.
4.  **Protección de Archivos:** Reglas en `.htaccess` para evitar el robo de código fuente o bases de datos.
5.  **Auditoría Forense:** Registro inmutable de IPs y usuarios en cada acción crítica.

---

## 📧 Módulo de Cumpleaños (Funcionamiento)

1.  El sistema detecta la fecha actual.
2.  El administrador selecciona los cumpleañeros en `cumpleanos.php`.
3.  Al hacer clic en "Procesar":
    * Se genera una imagen `JPG` única con el nombre del abogado.
    * Se envía un correo elegante con la imagen adjunta.
    * El sistema prioriza `CORREO_GMAIL`; si no existe, usa `CORREO` institucional.

---

**© 2024-2026 Ilustre Colegio de Abogados de Junín**