# 🚀 Guía Rápida - Docker

## ⚡ Inicio Rápido (3 pasos)

### 1️⃣ Configurar variables de entorno

```bash
# Copiar configuración para Docker
cp .env.docker .env
```

**⚠️ IMPORTANTE**: Edita `.env` y configura tu **GitHub OAuth**:
- Client ID y Client Secret de https://github.com/settings/developers
- Callback URL: `http://localhost:8080/Controllers/githubAuthController.php`

### 2️⃣ Levantar los contenedores

```bash
docker-compose up -d
```

### 3️⃣ Acceder a la aplicación

- **Web**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
  - Usuario: `root`
  - Contraseña: `root_password_123`

---

## 👥 Usuarios de Prueba

| Rol | Usuario | Contraseña | 2FA |
|-----|---------|------------|-----|
| **Admin** | `admin` | `admin123` | ✅ Obligatorio (Google Authenticator) |
| **Docente** | `docente1` | `docente123` | ❌ No requiere |
| **Estudiante** | - | - | GitHub OAuth |

---

## 📝 Comandos Útiles

```bash
# Ver logs en tiempo real
docker-compose logs -f

# Detener servicios
docker-compose down

# Reiniciar servicios
docker-compose restart

# Reconstruir (si cambias código)
docker-compose up -d --build

# Ver estado
docker-compose ps
```

---

## 🔧 Troubleshooting

### ❌ Error: "Port is already allocated"

```bash
# Ver qué está usando el puerto
netstat -ano | findstr :8080    # Windows
lsof -i :8080                   # Mac/Linux

# Cambiar puerto en docker-compose.yml
# Cambiar "8080:80" por "8888:80" (ejemplo)
```

### ❌ Error: "Cannot connect to database"

```bash
# Esperar a que MySQL inicie completamente
docker-compose logs db

# Reiniciar servicios
docker-compose restart
```

### ❌ Cambié código pero no se refleja

```bash
# Reconstruir imagen
docker-compose up -d --build
```

---

## 🛑 Limpiar Todo (RESET)

```bash
# Detener y eliminar todo (incluyendo base de datos)
docker-compose down -v

# Volver a empezar
docker-compose up -d
```

---

## 📚 Documentación Completa

Ver [README.md](README.md) para documentación detallada.
