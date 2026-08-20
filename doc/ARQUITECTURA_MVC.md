# ARQUITECTURA MVC - SISTEMA DE GESTIÓN DE LABORATORIO

## 1. ESTRUCTURA GENERAL DEL PROYECTO

```
public-WEB/
├── index.html                      # Página de inicio
├── .env                            # Configuración de base de datos
├── BaseDatos.sql                   # Script SQL
├── composer.json                   # Dependencias PHP
│
├── Models/                         # CAPA DE MODELOS
│   ├── Database.php                # Singleton para conexión BD
│   ├── Usuario.php                 # Modelo de usuarios
│   ├── Equipo.php                  # Modelo de equipos/PCs
│   ├── Registro.php                # Modelo de registros
│   ├── Laboratorio.php             # Modelo de laboratorios
│   ├── TipoUsuario.php             # Tipos de usuario
│   └── auth.php                    # Autenticación y 2FA
│
├── Controllers/                    # CAPA DE CONTROLADORES
│   ├── loginController.php         # Login y 2FA
│   ├── EquipoController.php        # CRUD equipos
│   ├── RegistroController.php      # Gestión registros
│   ├── usuariosController.php      # CRUD usuarios
│   └── githubAuthController.php    # OAuth GitHub
│
├── views/                          # CAPA DE VISTAS
│   ├── login_usuario.html          # Página de login
│   ├── menu_admin.php              # Panel administrador
│   ├── menu_docente.php            # Panel docentes
│   ├── menu_estudiante.php         # Panel estudiantes
│   ├── equipos.php                 # Gestión de equipos
│   └── usuarios.php                # Gestión de usuarios
│
└── Public/                         # RECURSOS ESTÁTICOS
    ├── css/                        # Estilos
    ├── js/                         # JavaScript
    └── img/                        # Imágenes
```

---

## 2. TABLAS DE BASE DE DATOS (bddsglab6)

### Tabla: Usuarios
