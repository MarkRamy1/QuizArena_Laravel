@echo off
chcp 65001 >nul
title QuizArena LMS Server
color 0B
echo ========================================================
echo               QuizArena - تشغيل المشروع
echo ========================================================
echo.

cd /d "%~dp0"

echo [1/3] التحقق من تشغيل قاعدة البيانات MySQL...
tasklist /fi "imagename eq mysqld.exe" 2>NUL | find /i "mysqld.exe" >NUL
if "%ERRORLEVEL%"=="0" (
    echo [✓] قاعدة البيانات MySQL قيد التشغيل بالفعل.
) else (
    echo [*] جاري تشغيل MySQL من XAMPP...
    start /b "" "c:\xampp2\mysql\bin\mysqld.exe" --defaults-file="c:\xampp2\mysql\bin\my.ini" --standalone
    timeout /t 3 /nobreak >nul
    echo [✓] تم تشغيل MySQL بنجاح.
)

echo.
echo [2/3] فتح المتصفح على رابط المنصة:
echo http://127.0.0.1:8000
start http://127.0.0.1:8000

echo.
echo ========================================================
echo                روابط وبيانات الدخول المعتمدة:
echo  - حساب المشرف الإداري الوحيد:
echo    البريد: admin@quizarena.com
echo    كلمة السر: Admin123!
echo.
echo  - رابط تسجيل الدخول المباشر:
echo    http://127.0.0.1:8000/login
echo.
echo  - رابط إنشاء حساب جديد (معلم أو طالب):
echo    http://127.0.0.1:8000/register
echo ========================================================
echo.
echo [3/3] جاري تشغيل سيرفر Laravel...
echo اضغط Ctrl+C لإيقاف السيرفر في أي وقت.
echo.

"c:\xampp2\php\php.exe" artisan serve --host=0.0.0.0 --port=8000

pause
