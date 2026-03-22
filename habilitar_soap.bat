@echo off
set "php_ini_path=C:\xampp73\php\php.ini"
echo Descomentando la extension SOAP en %php_ini_path%...
powershell -Command "$content = Get-Content '%php_ini_path%'; $content -replace ';extension=soap', 'extension=soap' | Set-Content '%php_ini_path%'"
echo La extension SOAP ha sido habilitada en %php_ini_path%.
