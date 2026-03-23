# InnovaWeb API — Guía de Instalación en Laragon 6.0

## Prerequisitos

| Requisito | Versión | Notas |
|---|---|---|
| Laragon | 6.0 | No usar versiones superiores (tienen ads/licencia) |
| PHP | 8.1 - 8.3 | Incluido en Laragon 6.0 |
| Apache | 2.4+ | Incluido en Laragon 6.0 |
| SQL Server | 2012+ / Express | El mismo que usa el ERP Clarion |
| Microsoft ODBC Driver | 17 o 18 | **Instalar ANTES que la extensión PHP** |
| Composer | 2.x | Gestor de dependencias PHP |

---

## PASO 1 — Instalar Microsoft ODBC Driver for SQL Server

La extensión `sqlsrv` de PHP necesita este driver para comunicarse con SQL Server.

1. Descarga el driver desde Microsoft:
   - **ODBC Driver 18** (recomendado): https://go.microsoft.com/fwlink/?linkid=2249004
   - **ODBC Driver 17** (si usas SQL Server 2012-2016): https://go.microsoft.com/fwlink/?linkid=2187214

2. Instala el driver. Selecciona la versión de 64 bits si tu Windows es 64 bits.

3. Verifica la instalación:
   ```
   Inicio > ODBC Data Sources (64-bit) > Drivers
   ```
   Debe aparecer `ODBC Driver 17/18 for SQL Server` en la lista.

---

## PASO 2 — Instalar la extensión sqlsrv en Laragon 6.0

### Opción A — Script automático (PowerShell)

1. Abre **PowerShell como Administrador**
2. Navega a la carpeta del proyecto:
   ```powershell
   cd C:\laragon\www\innovaweb\backend\scripts
   ```
3. Ejecuta el script:
   ```powershell
   Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope Process
   .\instalar-sqlsrv-laragon6.ps1
   ```

### Opción B — Instalación manual

1. **Identifica tu versión de PHP en Laragon:**
   - Abre Laragon > Click en PHP > Ver la versión activa
   - Ej: `PHP 8.3.x NTS x64`

2. **Descarga las DLLs desde PECL:**
   - Ve a: https://pecl.php.net/package/sqlsrv/5.12.0/windows
   - Descarga el archivo que corresponda a tu PHP:
     - PHP 8.3 NTS 64bit: `php_sqlsrv-5.12.0-8.3-nts-Win32-vs16-x64.zip`
     - PHP 8.2 NTS 64bit: `php_sqlsrv-5.12.0-8.2-nts-Win32-vs16-x64.zip`
     - PHP 8.1 NTS 64bit: `php_sqlsrv-5.12.0-8.1-nts-Win32-vs16-x64.zip`

3. **Copia las DLLs a la carpeta de extensiones:**
   - Extrae el ZIP
   - Copia `php_sqlsrv.dll` y `php_pdo_sqlsrv.dll` a:
     ```
     C:\laragon\bin\php\php-8.x.x-nts-Win32-vs16-x64\ext\
     ```

4. **Edita el `php.ini` de Laragon:**
   - En Laragon: Click derecho > PHP > php.ini
   - Al final del archivo agrega:
     ```ini
     ; SQL Server (necesario para InnovaWeb y el ERP Clarion)
     extension=php_sqlsrv.dll
     extension=php_pdo_sqlsrv.dll

     ; SOAP (necesario para Facturacion Electronica DGI)
     extension=soap
     ```

5. **Reinicia Apache en Laragon** (Stop All > Start All)

6. **Verifica la instalación:**
   ```
   http://localhost/?info  (o crea un phpinfo.php en www/)
   ```
   Busca "sqlsrv" en la página — debe aparecer la sección de la extensión.

---

## PASO 3 — Configurar el Virtual Host en Apache

1. Copia el archivo de configuración del Virtual Host:
   ```
   backend\scripts\apache\innovaweb-api.test.conf
   ```
   A la carpeta:
   ```
   C:\laragon\etc\apache2\sites-enabled\
   ```

2. El archivo asume que el proyecto está en:
   ```
   C:\laragon\www\innovaweb\
   ```
   Si está en otra ruta, edita `DocumentRoot` en el archivo `.conf`.

3. **Reinicia Apache** en Laragon (Stop All > Start All).

4. Laragon asigna automáticamente el dominio `innovaweb-api.test` via DNS local.
   No es necesario editar el archivo `hosts` de Windows.

5. Verifica que el Virtual Host funciona:
   ```
   http://innovaweb-api.test/
   ```
   Debe mostrar la pantalla de bienvenida de Laravel.

---

## PASO 4 — Configurar el proyecto Laravel

1. Copia el archivo de entorno:
   ```bash
   cp .env.example .env
   ```

2. Edita `.env` con los datos reales de tu SQL Server:
   ```env
   # Para instancia nombrada SQL Server Express:
   DB_HOST=NOMBRE_DE_TU_PC\SQLEXPRESS
   DB_PORT=
   DB_DATABASE=NOMBRE_TU_BASE_DATOS
   DB_USERNAME=tu_usuario_sql
   DB_PASSWORD=tu_clave_sql

   # Si SQL Server usa autenticacion de Windows (no recomendado para API):
   # Usa SQL Server Authentication en su lugar
   ```

   > **Nota importante:** Si el ERP Clarion usa `DESKTOP-46U0RK7\SQLEXPRESS`,
   > ese es el valor de `DB_HOST`. El `DB_PORT` debe quedar **vacío** cuando
   > se usa nombre de instancia.

3. Genera la application key:
   ```bash
   php artisan key:generate
   ```

4. Crea los directorios de almacenamiento requeridos por Laravel:
   ```powershell
   mkdir storage\framework\views, storage\framework\sessions -Force
   ```
   > Estos directorios están excluidos del repositorio (`.gitignore`) y deben
   > crearse manualmente. Sin ellos Laravel lanza el error
   > `Please provide a valid cache path`.

5. Instala las dependencias (si no se hicieron ya):
   ```bash
   composer install
   ```

6. Publica las configuraciones de Sanctum:
   ```bash
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   ```

7. Ejecuta las migraciones:
   ```bash
   php artisan migrate
   ```
   > Esto crea las tablas de Laravel en SQL Server (migrations, sessions, etc.)
   > Las tablas del ERP NO se tocan.

8. Verifica la conexión a SQL Server:
   ```bash
   php artisan tinker
   >>> DB::select('SELECT @@VERSION')
   ```

---

## PASO 5 — Verificar la instalación completa

```bash
# Listar rutas disponibles
php artisan route:list

# Verificar que no hay errores de configuracion
php artisan config:clear
php artisan cache:clear

# Test de conexion a SQL Server
php artisan tinker --execute="DB::select('SELECT TOP 1 * FROM BASEUSUARIOS')"
```

---

## Estructura de rutas esperada en Laragon

```
C:\laragon\
├── www\
│   └── innovaweb\                    ← Raiz del repositorio
│       ├── backend\                  ← Proyecto Laravel 11 (API)
│       │   ├── public\               ← DocumentRoot del Virtual Host
│       │   ├── app\
│       │   ├── .env                  ← Variables de entorno (NO en git)
│       │   └── ...
│       │
│       ├── ajax\                     ← Monolito PHP existente (sigue vivo)
│       ├── clientes.php
│       └── ...
│
├── bin\
│   ├── php\php-8.x.x-nts\
│   │   ├── php.exe
│   │   ├── php.ini                   ← Aqui van las extensiones
│   │   └── ext\
│   │       ├── php_sqlsrv.dll        ← Instalado en Paso 2
│   │       └── php_pdo_sqlsrv.dll   ← Instalado en Paso 2
│   └── apache\
│
└── etc\
    └── apache2\
        └── sites-enabled\
            └── innovaweb-api.test.conf  ← Instalado en Paso 3
```

---

## Solución de problemas comunes

### Error: "Please provide a valid cache path"
- Laravel no encuentra los directorios `storage/framework/views` o `storage/framework/sessions`
- Crea los directorios manualmente (están excluidos del repositorio):
  ```powershell
  mkdir storage\framework\views, storage\framework\sessions -Force
  ```

### Error: "could not find driver"
- La extension `pdo_sqlsrv` no está activa
- Verifica el `php.ini` y reinicia Apache

### Error: "ODBC Driver not found"
- Instala Microsoft ODBC Driver 17 o 18 (ver Paso 1)
- Reinicia Windows después de instalar el driver

### Error: "SSL connection required"
- Agrega en `.env`: `DB_TRUST_SERVER_CERTIFICATE=true`
- Esto es normal con SQL Server Express local

### Error: "Named pipes provider... could not open a connection"
- SQL Server Express no está iniciado
- Ve a: Servicios de Windows > SQL Server (SQLEXPRESS) > Iniciar
- Verifica que `SQL Server Browser` también esté corriendo

### Error: "Login failed for user"
- Verifica las credenciales en `.env`
- SQL Server debe tener habilitada la autenticación de SQL Server:
  Management Studio > Click derecho en servidor > Properties > Security >
  SQL Server and Windows Authentication mode

### El Virtual Host muestra el proyecto incorrecto
- Asegúrate de que `DocumentRoot` apunta a `backend/public` (no a `backend/`)
- Recarga Apache (no solo reiniciar Laragon)

---

## Comandos útiles durante el desarrollo

```bash
# Limpiar todos los caches
php artisan optimize:clear

# Ver queries SQL en tiempo real
php artisan tinker
>>> DB::enableQueryLog()
>>> // Ejecuta algo...
>>> DB::getQueryLog()

# Crear un nuevo modelo con migración, controlador y resource
php artisan make:model Cliente -mcr

# Listar todas las rutas con sus middlewares
php artisan route:list --columns=method,uri,name,middleware
```

---

## Registro de errores resueltos durante la implementación

Esta sección documenta los problemas reales que aparecieron al levantar el
entorno por primera vez, con su causa y solución, para no repetirlos.

---

### Error 1 — `Please provide a valid cache path`

**Síntoma:** Laravel lanza esta excepción al hacer cualquier request.

**Causa:** El archivo `config/view.php` no existía en el repositorio (fue
omitido al crear el proyecto), y los directorios `storage/framework/views` y
`storage/framework/sessions` estaban excluidos por `.gitignore`.

**Solución:**
1. Se creó el archivo `backend/config/view.php` con la configuración estándar
   de Laravel.
2. Se crean los directorios manualmente al clonar el repo:
   ```powershell
   mkdir storage\framework\views, storage\framework\sessions -Force
   ```

---

### Error 2 — Incompatibilidad Vite 8 con `vite-plugin-pwa`

**Síntoma:** `npm install` falla con conflicto de peer dependencies entre
`vite-plugin-pwa@1.2.0` (soporta Vite ^3–^7) y `@vitejs/plugin-react@6`
(requiere Vite ^8).

**Causa:** Se instaló Vite 8 (Rolldown) que aún no tiene soporte oficial en
`vite-plugin-pwa`.

**Solución:** Downgrade en `package.json`:
```json
"vite": "^7.0.0",
"@vitejs/plugin-react": "^5.0.0"
```

---

### Error 3 — Axios apuntaba al frontend en lugar del backend

**Síntoma:** Las peticiones API llegaban a `localhost:5173` en vez de al
backend Laravel.

**Causa:** El `baseURL` de Axios estaba hardcodeado como `'/api/v1'` (ruta
relativa), por lo que el browser resolvía contra el origen del frontend.

**Solución:** Se cambió a `import.meta.env.VITE_API_URL` en
`frontend/src/lib/axios.ts`, y se definió `VITE_API_URL=http://innovaweb.test`
en el `.env` del frontend.

---

### Error 4 — Typo en el dominio del proxy de Vite

**Síntoma:** Las peticiones a `/api/*` no llegaban al backend aunque el
`baseURL` estaba correcto.

**Causa:** El proxy en `vite.config.ts` y los patrones de caché de Workbox
apuntaban al dominio erróneo: `innovanew.test` en lugar de `innovaweb.test`.

**Solución:** Se corrigió el typo en `frontend/vite.config.ts`:
```ts
target: 'http://innovaweb.test',  // era: innovanew.test
```

---

### Error 5 — CORS: preflight OPTIONS bloqueado

**Síntoma:** El browser rechazaba todas las peticiones con error CORS. Las
requests `OPTIONS` no recibían los headers `Access-Control-*`.

**Causa (en dos pasos):**
1. Faltaba registrar `HandleCors` como primer middleware global en Laravel.
2. Apache interceptaba el preflight antes de que llegara a PHP.

**Solución (en dos pasos):**
1. Se creó `backend/config/cors.php` y se registró `HandleCors` como primer
   middleware en `backend/bootstrap/app.php`.
2. Se agregó en `backend/public/.htaccess` un bloque para responder `204` a
   `OPTIONS` antes del rewrite de Laravel.

> **Nota:** este enfoque fue luego **revertido** (ver Error 6).

---

### Error 6 — `.htaccess` roto por bloque `mod_headers` inválido

**Síntoma:** Apache devolvía 500 en todas las rutas tras el fix del Error 5.

**Causa:** El bloque `mod_headers` con `RewriteEngine` dentro de `.htaccess`
es inválido en el contexto de un `.htaccess` de Laravel.

**Solución definitiva:** Se revirtió `.htaccess` al estado original de Laravel
y se adoptó la estrategia correcta para CORS en desarrollo: usar el **proxy de
Vite** (`/api → http://innovaweb.test`) para que el browser nunca haga
peticiones cross-origin. El middleware CORS de Laravel queda activo solo para
producción.

Cambios en `frontend/src/lib/axios.ts`:
```ts
baseURL: '/api/v1'  // relativo → pasa por el proxy de Vite
```

---

### Error 7 — Login falla con `Invalid column name 'INTEGRADO'`

**Síntoma:** Al hacer login, SQL Server devuelve
`SQLSTATE[42S22]: Invalid column name 'INTEGRADO'`.

**Causa:** La tabla `BASEUSUARIOS` del ERP Clarion no tiene columna
`INTEGRADO`. La condición `AND INTEGRADO = 0` fue añadida por analogía con
otras tablas del ERP que sí la tienen.

**Archivo afectado:** `backend/app/Http/Controllers/Api/V1/AuthController.php`

**Solución:** Se eliminó `AND INTEGRADO = 0` del `WHERE` en la query de login.
La tabla `BASEUSUARIOS` no usa ese campo de control; la autenticación se
verifica comparando `CLAVE` / `CLAVEWEB`.

```php
// Antes
"SELECT ... FROM BASEUSUARIOS WHERE CODUSER = ? AND INTEGRADO = 0"

// Después
"SELECT ... FROM BASEUSUARIOS WHERE CODUSER = ?"
```
