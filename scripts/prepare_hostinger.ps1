# Script: prepare_hostinger.ps1
# Description: Prepares the project for secure Hostinger deployment by separating Public and Core files.

$ErrorActionPreference = "Stop"

$sourceDir = Get-Location
$distDir = "$sourceDir\dist_hostinger"

Write-Host "Preparing Deployment Package in: $distDir" -ForegroundColor Cyan

# 1. Clean previous build
if (Test-Path $distDir) {
    Remove-Item -Path $distDir -Recurse -Force
    Write-Host "   Deleted old build folder." -ForegroundColor Gray
}

# 2. Create Structure
$coreDir = "$distDir\erapor_core"
$publicDir = "$distDir\public_html"

New-Item -ItemType Directory -Path $coreDir -Force | Out-Null
New-Item -ItemType Directory -Path $publicDir -Force | Out-Null

Write-Host "Created directory structure." -ForegroundColor Green

# 3. Copy Core Files (Excluding heavy/unnecessary folders)
# We exclude 'public' because its contents will go to public_html
# Also exclude debug/temp files for Clean Production Build
$exclude = @(
    "node_modules", ".git", ".gemini", "dist_hostinger", "public", "tests", "storage\logs", ".github",
    "debug_archive", "acuan", "docs",
    "*.md", "*.yml", "*.xml", "composer.phar", "composer-setup.php",
    "check_*.php", "debug_*.php", "verify_*.php", "fix_*.php", "diagnose_*.php"
)

Write-Host "Copying Core Files (This may take a minute)..." -ForegroundColor Yellow

Get-ChildItem -Path $sourceDir -Exclude $exclude | ForEach-Object {
    $target = Join-Path $coreDir $_.Name
    Copy-Item -Path $_.FullName -Destination $target -Recurse -Force
}

Write-Host "Core files copied." -ForegroundColor Green

# 4. Copy Public Files to public_html
Write-Host "Copying Public Assets..." -ForegroundColor Yellow
Copy-Item -Path "$sourceDir\public\*" -Destination $publicDir -Recurse -Force

# 5. Modify index.php in public_html
$indexFile = "$publicDir\index.php"
$indexContent = Get-Content $indexFile

# Regex patterns for safer replacement
$requireAutoload = "require __DIR__\.'/../vendor/autoload.php';"
$requireApp = "\$app = require_once __DIR__\.'/../bootstrap/app.php';"

$replaceAutoload = "require __DIR__.'/../erapor_core/vendor/autoload.php';"
$replaceApp = "\$app = require_once __DIR__.'/../erapor_core/bootstrap/app.php';"

$newContent = $indexContent -replace [regex]::Escape($requireAutoload), $replaceAutoload
$newContent = $newContent -replace [regex]::Escape($requireApp), $replaceApp

$newContent | Set-Content $indexFile

Write-Host "Patched public_html/index.php for correct paths." -ForegroundColor Green

# 6. Copy .env.example as .env
Copy-Item -Path "$sourceDir\.env.example" -Destination "$coreDir\.env"
Write-Host "Created .env from .env.example (Remember to configure it on server!)" -ForegroundColor Cyan

Write-Host "--------------------------------------------------------" -ForegroundColor White
Write-Host "BUILD COMPLETE!" -ForegroundColor Green
Write-Host "1. Upload contents of $coreDir to a folder named erapor_core on Hostinger."
Write-Host "2. Upload contents of $publicDir to public_html on Hostinger."
Write-Host "3. Edit erapor_core/.env on the server."
Write-Host "--------------------------------------------------------" -ForegroundColor White
