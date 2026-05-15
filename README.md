Product Badges Module - PrestaShop 1.7
Módulo desarrollado para la prueba técnica de Blinders Group. Permite gestionar etiquetas visuales (badges) personalizadas y asignarlas a productos del catálogo con visualización optimizada sobre la imagen del producto.

🛠️ Especificaciones Técnicas (Entorno de Prueba)
PrestaShop: 1.7.8.11

PHP: 8.1

MySQL: 8.0

Entorno: Docker Compose

Arquitectura: Basada en ObjectModel para persistencia y ModuleAdminController para la gestión en el Back Office.

🚀 Inicio Rápido (Docker)
Este proyecto incluye una configuración de Docker para un despliegue inmediato.

Levantar el entorno:

En la terminal del proyecto:
"docker compose up -d"

Acceso al Back Office:

URL: http://localhost:8080/admin247a49dnw

Usuario: admin@test.com
Contraseña: Admin1234!

Configuración de IDE: Se incluye la carpeta .vscode/settings.json configurada para silenciar errores de clases no definidas de PrestaShop y mejorar el autocompletado mediante intelephense.

📦 Instalación del módulo

Dentro de http://localhost:8080/admin247a49dnw entrar en la sección "Modules", "Modules Manager" y bajar hasta encontrar "Design and Navigation" y descargar el modulo "Product Badges"

Para confirmar que esta instalado dirigirse a "Catalog" y debe aparecer "Product Badges" dentro de esa sección estara el modulo listo para crear nuevas badges