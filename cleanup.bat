@echo off
REM ============================================================
REM MANG-CV Auto Cleanup
REM Setup: schtasks /create /sc hourly /tn "MANG-CV Cleanup" /tr "F:\laragon\www\MANG-CV\cleanup.bat" /st 00:00 /f
REM ============================================================

cd /d F:\laragon\www\MANG-CV
php spark cv:cleanup --force

echo.
echo Cleanup finished at %date% %time%
pause
