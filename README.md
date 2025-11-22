# 📦 Sistema de Inventario Inteligente con Chatbot IA

Bienvenido al **Sistema de Inventario Inteligente**, una solución moderna y completa para la gestión de pequeños y medianos negocios. Este proyecto combina una gestión de inventario robusta con un asistente virtual impulsado por Inteligencia Artificial (Google Gemini) para facilitar las operaciones diarias.

![Dashboard Preview](https://via.placeholder.com/800x400?text=Dashboard+Preview)

## ✨ Características Principales

### 🚀 Gestión de Inventario
- **CRUD Completo:** Agregar, editar, eliminar y listar productos.
- **Control de Stock:** Alertas visuales de stock bajo y crítico.
- **Categorización:** Organización eficiente de productos.
- **Proveedores:** Gestión de base de datos de proveedores.

### 🤖 Asistente IA (Chatbot)
- **Consultas Naturales:** Pregunta "¿Qué producto se está acabando?" o "¿Cuánto vendí hoy?".
- **Contexto Inteligente:** El bot recuerda tu conversación anterior.
- **Acciones Rápidas:** Registra ventas o compras directamente desde el chat.
- **Sugerencias Proactivas:** Análisis de ventas y recomendaciones de reabastecimiento.

### 📊 Dashboard y Reportes
- **Visualización de Datos:** Gráficos interactivos de ventas semanales y productos top.
- **Reportes Financieros:** Historial detallado de ventas y compras.
- **Recibos Digitales:** Generación de comprobantes de venta.

### 🛡️ Seguridad y Roles
- **Control de Acceso (RBAC):**
    - **Admin:** Acceso total a configuración y gestión.
    - **Empleado:** Acceso limitado a ventas y consultas básicas.
- **Protección:** Rutas protegidas y validación de sesiones.

### 🎨 Experiencia de Usuario (UX)
- **Modo Oscuro:** Interfaz adaptable con detección automática de preferencia.
- **Diseño Responsivo:** Funciona perfectamente en móviles, tablets y escritorio.
- **Interfaz Moderna:** Construida con Tailwind CSS para una estética limpia y profesional.

---

## 🛠️ Requisitos del Sistema

- **Servidor Web:** Apache (XAMPP/WAMP/Laragon recomendado).
- **PHP:** Versión 7.4 o superior.
- **Base de Datos:** MySQL / MariaDB.
- **Navegador:** Chrome, Firefox, Edge (versiones recientes).

---

## 📥 Instalación Paso a Paso

1.  **Clonar/Descargar:**
    Descarga el código fuente y colócalo en tu carpeta `htdocs` (ej: `C:\xampp\htdocs\Inventario`).

2.  **Base de Datos:**
    - Abre phpMyAdmin (`http://localhost/phpmyadmin`).
    - Crea una nueva base de datos llamada `inventario`.
    - Importa el archivo SQL ubicado en `database/inventario.sql`.

3.  **Configuración:**
    - Asegúrate de que el archivo `includes/config.php` tenga las credenciales correctas de tu base de datos.
    - (Opcional) Configura tu API Key de Gemini en `chatbot/chatbot_api_simple.php` para activar la IA.

4.  **Iniciar:**
    - Abre tu navegador y ve a `http://localhost/Inventario`.

---

## 🔑 Credenciales por Defecto

| Rol | Usuario | Contraseña |
| :--- | :--- | :--- |
| **Administrador** | `admin` | `admin123` |
| **Empleado** | `empleado` | `empleado123` |

> **Nota:** Se recomienda cambiar estas contraseñas inmediatamente después del primer inicio de sesión.

---

## 🤝 Contribución

Este proyecto fue desarrollado con un enfoque en la calidad de código y la experiencia de usuario. Si deseas contribuir, por favor:

1.  Haz un Fork del repositorio.
2.  Crea una rama para tu funcionalidad (`git checkout -b feature/NuevaFuncionalidad`).
3.  Haz Commit de tus cambios (`git commit -m 'Agregada nueva funcionalidad'`).
4.  Haz Push a la rama (`git push origin feature/NuevaFuncionalidad`).
5.  Abre un Pull Request.

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Eres libre de usarlo y modificarlo para tus propios fines.

---

Hecho con ❤️ y mucha ☕ para la gestión eficiente de tu negocio.
