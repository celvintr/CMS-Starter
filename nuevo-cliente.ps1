<#
    Clona el CMS base para un cliente nuevo y lo deja listo para usar.

    Uso:
      .\nuevo-cliente.ps1 -Slug "farmacia-lopez" -Nombre "Farmacia Lopez" -Email "admin@farmacia.com" -Password "claveSegura"

    El -Password es opcional; si no lo pasas, se genera uno.
#>
param(
    [Parameter(Mandatory = $true)][string]$Slug,
    [Parameter(Mandatory = $true)][string]$Nombre,
    [Parameter(Mandatory = $true)][string]$Email,
    [string]$Password
)

$ErrorActionPreference = 'Stop'

# --- Rutas ---
$php     = 'C:\laragon\bin\php\php-8.2.22-nts-Win32-vs16-x64\php.exe'
$www     = 'C:\laragon\www'
$origen  = Join-Path $www 'cms-starter'
$destino = Join-Path $www $Slug

# --- Validaciones ---
if (-not (Test-Path $php))     { Write-Error "No se encontro PHP 8.2 en $php"; exit 1 }
if (-not (Test-Path $origen))  { Write-Error "No existe el proyecto base en $origen"; exit 1 }
if (Test-Path $destino)        { Write-Error "Ya existe una carpeta en $destino. Elige otro slug."; exit 1 }
if (-not $Password) {
    $Password = -join ((48..57) + (65..90) + (97..122) | Get-Random -Count 10 | ForEach-Object { [char]$_ })
}

Write-Host "==> Copiando proyecto a $destino ..." -ForegroundColor Cyan
robocopy $origen $destino /E /NFL /NDL /NJH /NJS /NP /XD .git node_modules .playwright-mcp /XF database.sqlite | Out-Null
if ($LASTEXITCODE -ge 8) { Write-Error "Error copiando archivos (robocopy $LASTEXITCODE)"; exit 1 }

Push-Location $destino
try {
    Write-Host "==> Limpiando cache ..." -ForegroundColor Cyan
    & $php artisan optimize:clear | Out-Null

    Write-Host "==> Preparando base de datos y .env ..." -ForegroundColor Cyan
    $dbFile = Join-Path $destino 'database\database.sqlite'
    if (-not (Test-Path $dbFile)) { New-Item -ItemType File -Path $dbFile | Out-Null }

    $envPath = Join-Path $destino '.env'
    if (-not (Test-Path $envPath)) { Copy-Item (Join-Path $destino '.env.example') $envPath }
    $envRaw = Get-Content $envPath -Raw
    $envRaw = $envRaw -replace '(?m)^APP_NAME=.*$', ('APP_NAME="' + $Nombre + '"')
    $envRaw = $envRaw -replace '(?m)^APP_URL=.*$',  ('APP_URL=http://' + $Slug + '.test')
    Set-Content -Path $envPath -Value $envRaw -Encoding utf8

    Write-Host "==> Generando clave de la app ..." -ForegroundColor Cyan
    & $php artisan key:generate --force | Out-Null

    Write-Host "==> Migrando y cargando contenido base ..." -ForegroundColor Cyan
    & $php artisan migrate:fresh --seed --seeder=Database\Seeders\DemoContentSeeder --force | Out-Null
    if ($LASTEXITCODE -ne 0) { Write-Error "Fallo la migracion. Revisa el proyecto."; exit 1 }

    Write-Host "==> Enlazando storage ..." -ForegroundColor Cyan
    $publicLink = Join-Path $destino 'public\storage'
    if (Test-Path $publicLink) { Remove-Item $publicLink -Force -Recurse -ErrorAction SilentlyContinue }
    & $php artisan storage:link | Out-Null

    Write-Host "==> Configurando sitio y administrador ..." -ForegroundColor Cyan
    & $php artisan cms:configurar-cliente $Nombre $Email $Password
}
finally {
    Pop-Location
}

Write-Host ""
Write-Host "==================================================" -ForegroundColor Green
Write-Host " Sitio '$Nombre' listo" -ForegroundColor Green
Write-Host "==================================================" -ForegroundColor Green
Write-Host " Carpeta : $destino"
Write-Host " Sitio   : http://$Slug.test    (o corre: php artisan serve)"
Write-Host " Panel   : http://$Slug.test/admin"
Write-Host " Admin   : $Email"
Write-Host " Clave   : $Password"
Write-Host "--------------------------------------------------"
Write-Host " Si usas Laragon con Apache, dale 'Reload' para activar el dominio .test." -ForegroundColor Yellow
Write-Host ""
