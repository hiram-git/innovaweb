# ============================================================================
# instalar-sqlsrv-laragon6.ps1
# Script de instalacion de la extension SQL Server para PHP en Laragon 6.0
#
# INSTRUCCIONES:
#   1. Ejecutar PowerShell como Administrador
#   2. Navegar a esta carpeta
#   3. Ejecutar: .\instalar-sqlsrv-laragon6.ps1
#
# Requisitos previos:
#   - Laragon 6.0 instalado (con PHP 8.x)
#   - Microsoft SQL Server o SQL Server Express instalado
#   - Microsoft ODBC Driver 17 o 18 instalado
# ============================================================================

# --- CONFIGURACION ---------------------------------------------------------
# Ajustar si Laragon esta instalado en otra ruta
$LaragonPath  = "C:\laragon"
$LaragonBin   = "$LaragonPath\bin"

# Detecta automaticamente la version de PHP activa en Laragon
$PhpDir = Get-ChildItem "$LaragonBin\php" | Sort-Object Name -Descending | Select-Object -First 1
if (-not $PhpDir) {
    Write-Host "[ERROR] No se encontro PHP en $LaragonBin\php" -ForegroundColor Red
    Write-Host "        Verifica que Laragon 6.0 este instalado correctamente." -ForegroundColor Yellow
    exit 1
}

$PhpPath     = $PhpDir.FullName
$PhpExe      = "$PhpPath\php.exe"
$PhpIni      = "$PhpPath\php.ini"
$ExtDir      = "$PhpPath\ext"

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  Instalador extension SQL Server para Laragon 6.0" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "[INFO] PHP detectado: $PhpPath" -ForegroundColor Green

# --- DETECTAR VERSION PHP --------------------------------------------------
$PhpVersionFull = & $PhpExe -r "echo PHP_VERSION;"
$PhpVersion     = $PhpVersionFull.Substring(0, 3)   # ej: "8.3"
$PhpVersionNum  = $PhpVersion -replace "\.", ""      # ej: "83"

# Detectar si es Thread Safe (TS) o Non-Thread Safe (NTS)
$PhpInfo = & $PhpExe -r "echo PHP_ZTS;"
if ($PhpInfo -eq "1") {
    $ThreadSafety = "ts"
} else {
    $ThreadSafety = "nts"
}

Write-Host "[INFO] Version PHP: $PhpVersionFull ($ThreadSafety)" -ForegroundColor Green

# --- VERIFICAR ODBC DRIVER -------------------------------------------------
Write-Host ""
Write-Host "[PASO 1] Verificando Microsoft ODBC Driver for SQL Server..." -ForegroundColor Yellow

$OdbcDrivers = Get-OdbcDriver | Where-Object { $_.Name -like "*SQL Server*" }
if ($OdbcDrivers) {
    Write-Host "[OK] ODBC Driver encontrado:" -ForegroundColor Green
    $OdbcDrivers | ForEach-Object { Write-Host "     - $($_.Name)" }
} else {
    Write-Host "[ADVERTENCIA] No se encontro Microsoft ODBC Driver for SQL Server." -ForegroundColor Red
    Write-Host ""
    Write-Host "  Descarga e instala uno de estos drivers ANTES de continuar:" -ForegroundColor Yellow
    Write-Host "  ODBC Driver 18 (recomendado):" -ForegroundColor White
    Write-Host "  https://go.microsoft.com/fwlink/?linkid=2249004" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "  ODBC Driver 17 (compatible con SQL Server 2012+):" -ForegroundColor White
    Write-Host "  https://go.microsoft.com/fwlink/?linkid=2187214" -ForegroundColor Cyan
    Write-Host ""
    $Continuar = Read-Host "  ¿Deseas continuar de todas formas? (s/N)"
    if ($Continuar -ne "s" -and $Continuar -ne "S") { exit 1 }
}

# --- DESCARGAR EXTENSIONES SQLSRV ------------------------------------------
Write-Host ""
Write-Host "[PASO 2] Descargando extensiones sqlsrv y pdo_sqlsrv..." -ForegroundColor Yellow

# Las DLLs estan disponibles en PECL para Windows
# URL segun version PHP: https://pecl.php.net/package/sqlsrv
# Ejemplo para PHP 8.3 NTS x64:
# php_sqlsrv-5.12.0-8.3-nts-Win32-vs16-x64.dll

$PeclBaseUrl   = "https://windows.php.net/downloads/pecl/releases/sqlsrv"
$SqlsrvVersion = "5.12.0"  # Version estable compatible con PHP 8.x

# Determinar arquitectura
$Arch = if ([Environment]::Is64BitOperatingSystem) { "x64" } else { "x86" }

# Determinar compilador Visual Studio (Laragon 6 usa VS16 para PHP 8.x)
$Compiler = "vs16"

$DllSuffix   = "$PhpVersionNum-$ThreadSafety-Win32-$Compiler-$Arch"
$DownloadDir = "$env:TEMP\sqlsrv_install"
$ZipFile     = "$DownloadDir\php_sqlsrv_pdo_sqlsrv.zip"
$DownloadUrl = "$PeclBaseUrl/$SqlsrvVersion/php_sqlsrv-$SqlsrvVersion-$DllSuffix.zip"

Write-Host "[INFO] Descargando desde:" -ForegroundColor Gray
Write-Host "       $DownloadUrl" -ForegroundColor Gray

if (-not (Test-Path $DownloadDir)) { New-Item -ItemType Directory -Path $DownloadDir | Out-Null }

try {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    Invoke-WebRequest -Uri $DownloadUrl -OutFile $ZipFile -UseBasicParsing
    Write-Host "[OK] Descarga completada." -ForegroundColor Green
}
catch {
    Write-Host "[ERROR] No se pudo descargar el archivo." -ForegroundColor Red
    Write-Host "        URL: $DownloadUrl" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "  Descarga manual desde:" -ForegroundColor White
    Write-Host "  https://pecl.php.net/package/sqlsrv/$SqlsrvVersion/windows" -ForegroundColor Cyan
    Write-Host "  Busca el archivo para PHP $PhpVersion $ThreadSafety $Arch" -ForegroundColor White
    Write-Host "  Copia php_sqlsrv.dll y php_pdo_sqlsrv.dll a:" -ForegroundColor White
    Write-Host "  $ExtDir" -ForegroundColor Cyan
    exit 1
}

# --- EXTRAER E INSTALAR DLLs -----------------------------------------------
Write-Host ""
Write-Host "[PASO 3] Extrayendo DLLs a la carpeta de extensiones PHP..." -ForegroundColor Yellow

Expand-Archive -Path $ZipFile -DestinationPath $DownloadDir -Force

$DllsFound = Get-ChildItem "$DownloadDir" -Filter "*.dll"
foreach ($Dll in $DllsFound) {
    $Dest = "$ExtDir\$($Dll.Name)"
    Copy-Item -Path $Dll.FullName -Destination $Dest -Force
    Write-Host "[OK] Copiado: $($Dll.Name) -> $ExtDir" -ForegroundColor Green
}

# --- MODIFICAR PHP.INI -----------------------------------------------------
Write-Host ""
Write-Host "[PASO 4] Configurando php.ini..." -ForegroundColor Yellow

if (-not (Test-Path $PhpIni)) {
    # Laragon a veces usa php.ini-development como base
    $PhpIniDev = "$PhpPath\php.ini-development"
    if (Test-Path $PhpIniDev) {
        Copy-Item $PhpIniDev $PhpIni
        Write-Host "[INFO] Creado php.ini desde php.ini-development" -ForegroundColor Gray
    } else {
        Write-Host "[ERROR] No se encontro php.ini en $PhpPath" -ForegroundColor Red
        exit 1
    }
}

$PhpIniContent = Get-Content $PhpIni -Raw

# Verificar si ya estan habilitadas
$SqlsrvEnabled    = $PhpIniContent -match "extension=php_sqlsrv\.dll"
$PdoSqlsrvEnabled = $PhpIniContent -match "extension=php_pdo_sqlsrv\.dll"

if (-not $SqlsrvEnabled) {
    Add-Content $PhpIni "`n; SQL Server Extension (agregado por instalar-sqlsrv-laragon6.ps1)"
    Add-Content $PhpIni "extension=php_sqlsrv.dll"
    Write-Host "[OK] Agregado: extension=php_sqlsrv.dll" -ForegroundColor Green
} else {
    Write-Host "[INFO] extension=php_sqlsrv.dll ya estaba configurada." -ForegroundColor Gray
}

if (-not $PdoSqlsrvEnabled) {
    Add-Content $PhpIni "extension=php_pdo_sqlsrv.dll"
    Write-Host "[OK] Agregado: extension=php_pdo_sqlsrv.dll" -ForegroundColor Green
} else {
    Write-Host "[INFO] extension=php_pdo_sqlsrv.dll ya estaba configurada." -ForegroundColor Gray
}

# Asegurar que extension_dir este configurado
if ($PhpIniContent -notmatch "^extension_dir\s*=") {
    Add-Content $PhpIni "`nextension_dir = `"ext`""
    Write-Host "[OK] Configurado: extension_dir = ext" -ForegroundColor Green
}

# Habilitar extension soap (necesaria para FEL/DGI)
if ($PhpIniContent -notmatch "^extension=soap") {
    $PhpIniContent = $PhpIniContent -replace ";extension=soap", "extension=soap"
    Set-Content $PhpIni $PhpIniContent
    Write-Host "[OK] Habilitado: extension=soap (necesario para Facturacion Electronica)" -ForegroundColor Green
}

# --- VERIFICAR INSTALACION -------------------------------------------------
Write-Host ""
Write-Host "[PASO 5] Verificando instalacion..." -ForegroundColor Yellow

$TestResult = & $PhpExe -r "echo extension_loaded('sqlsrv') ? 'OK' : 'FALLO';"
if ($TestResult -eq "OK") {
    Write-Host "[OK] extension sqlsrv cargada correctamente." -ForegroundColor Green
} else {
    Write-Host "[ERROR] La extension sqlsrv no se cargo. Revisa los pasos anteriores." -ForegroundColor Red
}

$TestPdo = & $PhpExe -r "echo extension_loaded('pdo_sqlsrv') ? 'OK' : 'FALLO';"
if ($TestPdo -eq "OK") {
    Write-Host "[OK] extension pdo_sqlsrv cargada correctamente." -ForegroundColor Green
} else {
    Write-Host "[ERROR] La extension pdo_sqlsrv no se cargo." -ForegroundColor Red
}

$TestSoap = & $PhpExe -r "echo extension_loaded('soap') ? 'OK' : 'FALLO';"
if ($TestSoap -eq "OK") {
    Write-Host "[OK] extension soap cargada correctamente." -ForegroundColor Green
} else {
    Write-Host "[ADVERTENCIA] extension soap no esta activa (necesaria para FEL)." -ForegroundColor Yellow
}

# --- REINICIAR LARAGON -----------------------------------------------------
Write-Host ""
Write-Host "[PASO 6] Reiniciando Apache en Laragon..." -ForegroundColor Yellow

$LaragonExe = "$LaragonPath\laragon.exe"
if (Test-Path $LaragonExe) {
    # Detener Apache
    $ApacheProc = Get-Process "httpd" -ErrorAction SilentlyContinue
    if ($ApacheProc) {
        $ApacheProc | Stop-Process -Force
        Start-Sleep -Seconds 2
    }
    Write-Host "[INFO] Reinicia Laragon manualmente haciendo clic en 'Reload' o 'Start All'." -ForegroundColor Yellow
} else {
    Write-Host "[INFO] Abre Laragon y haz clic en 'Reload' para aplicar los cambios." -ForegroundColor Yellow
}

# --- LIMPIAR TEMPORALES ----------------------------------------------------
Remove-Item $DownloadDir -Recurse -Force -ErrorAction SilentlyContinue

# --- RESUMEN ---------------------------------------------------------------
Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  INSTALACION COMPLETADA" -ForegroundColor Green
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "  PHP:           $PhpVersionFull ($ThreadSafety $Arch)" -ForegroundColor White
Write-Host "  php.ini:       $PhpIni" -ForegroundColor White
Write-Host "  Extensiones:   $ExtDir" -ForegroundColor White
Write-Host ""
Write-Host "  Proximos pasos:" -ForegroundColor Yellow
Write-Host "  1. Reinicia Laragon (Stop All > Start All)" -ForegroundColor White
Write-Host "  2. Crea el Virtual Host para innovaweb-api.test (ver SETUP.md)" -ForegroundColor White
Write-Host "  3. Ejecuta: cd backend && php artisan migrate" -ForegroundColor White
Write-Host ""
