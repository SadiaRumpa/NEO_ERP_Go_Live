@echo off
setlocal
title NeoERP - Trust SSL Certificate for 8.228.59.10
echo ==============================================================================
echo        NeoERP Enterprise — One-Click SSL Certificate Installer
echo ==============================================================================
echo.
echo Target URL: https://8.228.59.10
echo.
echo Installing NeoERP Root Certificate Authority into Windows Trust Store...
echo.

set "CERT_FILE=%~dp0public\neoerp-root-ca.crt"

if not exist "%CERT_FILE%" (
    set "CERT_FILE=%~dp0docker\nginx\ssl\neoerp-root-ca.crt"
)

if not exist "%CERT_FILE%" (
    echo [ERROR] Could not find neoerp-root-ca.crt in public\ or docker\nginx\ssl\
    echo Please make sure the repository files are complete.
    echo.
    pause
    exit /b 1
)

echo Certificate file: %CERT_FILE%
echo.
echo A Windows Security Warning dialog will appear asking you to confirm.
echo Please click [Yes] to add the certificate to your Trusted Root Store.
echo.

certutil -addstore -user Root "%CERT_FILE%"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ==============================================================================
    echo  [SUCCESS] NeoERP SSL Certificate has been successfully installed and trusted!
    echo ==============================================================================
    echo.
    echo  1. Restart Google Chrome / Microsoft Edge (or close all open browser windows).
    echo  2. Open: https://8.228.59.10
    echo  3. The 'Not Secure' warning is GONE and the connection is SECURE.
    echo.
) else (
    echo.
    echo [NOTE] If Windows prompted and you cancelled, or if an error occurred,
    echo you can also install it manually:
    echo  1. Double-click: public\neoerp-root-ca.crt
    echo  2. Click "Install Certificate..."
    echo  3. Select "Current User" -> Next
    echo  4. Select "Place all certificates in the following store" -> Browse
    echo  5. Choose "Trusted Root Certification Authorities" -> OK -> Next -> Finish -> Yes.
    echo.
)

pause
