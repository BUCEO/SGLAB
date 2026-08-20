# 🏫 Sistema de Gestión de Laboratorios ITI-UTU

Sistema web para la gestión de laboratorios de computación, equipos y registros de estado.

## 📋 Tabla de Contenidos

- [Características](#características)
- [Tecnologías](#tecnologías)
- [Requisitos Previos](#requisitos-previos)
- [Instalación con Docker](#instalación-con-docker)
- [Instalación con XAMPP](#instalación-con-xampp)
- [Configuración](#configuración)
- [Uso](#uso)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Contribuir](#contribuir)

---

## ✨ Características

- 🔐 **Autenticación multi-rol**: Administrador, Docente, Estudiante
- 🔑 **2FA (TOTP)**: Autenticación de dos factores para administradores
- 🐙 **GitHub OAuth**: Login para estudiantes con GitHub
- 📊 **Gestión de laboratorios**: Vista completa de equipos y registros
- 💬 **Comentarios**: Los docentes pueden comentar sobre el estado de los laboratorios
- 📈 **Reportes**: Registro de estado de equipos
- 🔒 **Seguridad**: PDO prepared statements, bcrypt, CSRF protection

---

## 🛠️ Tecnologías

- **Backend**: PHP 8.3
- **Base de Datos**: MySQL 8.0
- **Frontend**: HTML5, CSS3, JavaScript (jQuery)
- **Framework CSS**: Bootstrap 5
- **Autenticación**: TOTP (Google Authenticator), GitHub OAuth
- **Containerización**: Docker & Docker Compose

---

## 📦 Requisitos Previos

### Para Docker (Recomendado):
- [Docker Desktop](https://www.docker.com/products/docker-desktop) instalado
- [Git](https://git-scm.com/) instalado

### Para XAMPP (Desarrollo local):
- [XAMPP](https://www.apachefriends.org/) con PHP 8.3+
- [Composer](https://getcomposer.org/) instalado
- MySQL/MariaDB

---

## 🐳 Instalación con Docker (Recomendado)

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/sistema-gestion-laboratorios.git
cd sistema-gestion-laboratorios
```

### 2. Configurar variables de entorno

```bash
# Copiar el archivo de configuración para Docker
cp .env.docker .env

# Editar .env y configurar tus credenciales de GitHub OAuth
# nano .env
```

**Importante**: Configura tu GitHub OAuth App en https://github.com/settings/developers

- **Homepage URL**: `http://localhost:8080`
- **Authorization callback URL**: `http://localhost:8080/Controllers/githubAuthController.php`

### 3. Construir y levantar los contenedores

```bash
# Construir imágenes y levantar servicios
docker-compose up -d --build

# Ver logs en tiempo real (opcional)
docker-compose logs -f
```

### 4. Acceder a la aplicación

Una vez que los contenedores estén ejecutándose:

- **Aplicación web**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
  - Usuario: `root`
  - Contraseña: `root_password_123`

### 5. Usuarios de prueba

El sistema viene con usuarios pre-configurados:

| Rol | Usuario | Contraseña | Características |
|-----|---------|------------|----------------|
| **Administrador** | `admin` | `admin123` | 2FA obligatorio (Google Authenticator) |
| **Docente** | `docente1` | `docente123` | Acceso a gestión de laboratorios |
| **Estudiante** | - | - | Login con GitHub OAuth |

---

## 📂 Comandos Docker Útiles

```bash
# Levantar servicios
docker-compose up -d

# Detener servicios
docker-compose down

# Ver logs
docker-compose logs -f web
docker-compose logs -f db

# Reconstruir imágenes
docker-compose up -d --build

# Ejecutar composer install manualmente
docker-compose exec web composer install

# Acceder al contenedor web
docker-compose exec web bash

# Acceder al contenedor de base de datos
docker-compose exec db mysql -uroot -proot_password_123 bddsglab6

# Eliminar volúmenes (CUIDADO: borra todos los datos)
docker-compose down -v

# Ver estado de los servicios
docker-compose ps
```

---

## 💻 Instalación con XAMPP (Alternativa)

### 1. Clonar el repositorio

```bash
git clone https://github.com/tu-usuario/sistema-gestion-laboratorios.git
cd public-WEB
```

### 2. Instalar dependencias con Composer

```bash
composer install
```

### 3. Configurar base de datos

1. Iniciar XAMPP (Apache + MySQL)
2. Acceder a phpMyAdmin: http://localhost/phpmyadmin
3. Crear base de datos: `bddsglab6`
4. Importar el archivo: `BaseDatos.sql`

### 4. Configurar variables de entorno

```bash
# Copiar plantilla
cp .env.example .env

# Editar .env con tus configuraciones
# Asegurarse de que DB_HOST=localhost
```

### 5. Acceder a la aplicación

http://localhost/public-WEB/views/login_usuario.html

---

## ⚙️ Configuración

### Archivo `.env`

```env
# Base de datos
DB_HOST=db                     # "db" para Docker, "localhost" para XAMPP
DB_NAME=bddsglab6

# Usuarios de base de datos
DB_USER_ADMIN=sgapp_admin
DB_PASSWORD_ADMIN=AdministradorITI!

# GitHub OAuth
GITHUB_CLIENT_ID=tu_client_id
GITHUB_CLIENT_SECRET=tu_client_secret
GITHUB_REDIRECT_URI=http://localhost:8080/Controllers/githubAuthController.php
```

### GitHub OAuth

1. Ir a https://github.com/settings/developers
2. Click en "New OAuth App"
3. Configurar:
   - **Application name**: Sistema Gestión Laboratorios
   - **Homepage URL**: `http://localhost:8080`
   - **Callback URL**: `http://localhost:8080/Controllers/githubAuthController.php`
4. Copiar Client ID y Client Secret a `.env`

---

## 🚀 Uso

### Login

#### Administrador
1. Acceder a http://localhost:8080
2. Ingresar usuario y contraseña
3. **Primera vez**: Escanear código QR con Google Authenticator
4. Ingresar código de 6 dígitos

#### Docente
1. Acceder a http://localhost:8080
2. Ingresar usuario y contraseña
3. Acceso directo al panel docente

#### Estudiante
1. Acceder a http://localhost:8080
2. Click en "Continuar con GitHub"
3. Autorizar aplicación en GitHub
4. **Importante**: El email de GitHub debe coincidir con el email registrado en el sistema

### Panel Docente - Gestión de Laboratorios

1. Login como docente
2. Ir a "Laboratorios"
3. Ver todos los laboratorios con sus equipos
4. Ver último registro de cada equipo
5. Agregar comentarios sobre el estado de cada laboratorio

---

## 📁 Estructura del Proyecto

```
public-WEB/
├── Controllers/           # Controladores MVC
│   ├── loginController.php
│   ├── DocenteController.php
│   ├── EquipoController.php
│   └── ...
├── Models/                # Modelos de datos
│   ├── Database.php
│   ├── Usuario.php
│   ├── Equipo.php
│   ├── LaboratorioDocente.php
│   └── ...
├── views/                 # Vistas
│   ├── login_usuario.html
│   ├── menu_admin.php
│   ├── menu_docente.php
│   ├── panel_docente_laboratorios.php
│   └── ...
├── Public/                # Recursos estáticos
│   ├── css/
│   ├── js/
│   └── img/
├── docker/                # Configuración Docker
│   ├── php.ini
│   └── init-db.sh
├── vendor/                # Dependencias Composer (no en Git)
├── .env                   # Variables de entorno (no en Git)
├── .env.example           # Plantilla de configuración
├── .env.docker            # Configuración para Docker
├── docker-compose.yml     # Orquestación de servicios
├── Dockerfile             # Imagen Docker PHP+Apache
├── composer.json          # Dependencias PHP
├── BaseDatos.sql          # Script SQL de base de datos
└── README.md              # Este archivo
```

---

## 🐛 Solución de Problemas

### Error de conexión a base de datos en Docker

```bash
# Verificar que el servicio db esté corriendo
docker-compose ps

# Ver logs del servicio de base de datos
docker-compose logs db

# Reiniciar servicios
docker-compose restart
```

### Composer no encuentra dependencias

```bash
# Instalar dependencias manualmente
docker-compose exec web composer install

# O reconstruir la imagen
docker-compose up -d --build
```

### Error de permisos

```bash
# Dar permisos a www-data en el contenedor
docker-compose exec web chown -R www-data:www-data /var/www/html
```

### GitHub OAuth no funciona

1. Verificar que las URLs en GitHub OAuth App coincidan exactamente
2. Verificar que `GITHUB_REDIRECT_URI` en `.env` sea correcta
3. Revisar logs: `docker-compose logs -f web`

---

## 🤝 Contribuir

1. Fork el proyecto
2. Crear una rama: `git checkout -b feature/nueva-funcionalidad`
3. Commit cambios: `git commit -am 'Agregar nueva funcionalidad'`
4. Push a la rama: `git push origin feature/nueva-funcionalidad`
5. Crear Pull Request

---

## 📝 Notas Importantes

### Sobre vendor/ y Git

⚠️ **vendor/ NO se sube a Git**

La carpeta `vendor/` contiene las dependencias de Composer y NO debe subirse al repositorio.

**¿Por qué?**
- Es pesada (muchos archivos)
- Se puede regenerar con `composer install`
- Puede causar conflictos entre sistemas

**¿Cómo se instala?**
- **Con Docker**: Automáticamente al construir la imagen
- **Con XAMPP**: Ejecutar `composer install` manualmente

### Sobre .env y Git

⚠️ **.env NO se sube a Git** (contiene credenciales sensibles)

- Usa `.env.example` como plantilla
- Cada desarrollador crea su propio `.env`

---

## 📄 Licencia

Este proyecto es de código abierto para fines educativos.

---

## 👥 Autores

- **Equipo de Desarrollo** - ITI-UTU

---

## 🙏 Agradecimientos

- Instituto Tecnológico Industrial (ITI)
- Universidad del Trabajo del Uruguay (UTU)
- Comunidad de código abierto

---

**¿Preguntas o problemas?** Abre un [Issue](https://github.com/tu-usuario/sistema-gestion-laboratorios/issues)
