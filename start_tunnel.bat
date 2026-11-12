@echo off
title Outdoor Forum - Cloudflare Tunnel
echo ========================================================
echo   DANG KHOI DONG SERVER VA CLOUDFLARE TUNNEL...
echo ========================================================

:: 1. Khoi dong PHP Development Server (cong 8080)
start /B "" "d:\xampp\php\php.exe" -S 127.0.0.1:8080 -t public

echo Server noi bo dang chay tai: http://127.0.0.1:8080
echo Dang tao duong link HTTPS cong khai qua Cloudflare...
echo.
echo Link truy cap toan cau (co HTTPS) se hien thi ngay ben duoi:
echo ========================================================
.\cloudflared.exe tunnel --url http://127.0.0.1:8080
pause
