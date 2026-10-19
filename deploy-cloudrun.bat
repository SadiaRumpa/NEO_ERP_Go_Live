@echo off
title NeoERP - Google Cloud Run Automated Deployment
echo =================================================================
echo    NeoERP Enterprise - Google Cloud Run Automated Deployment
echo =================================================================

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0deploy-cloudrun.ps1" %*

pause
