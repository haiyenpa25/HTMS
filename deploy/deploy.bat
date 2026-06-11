@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

:: ============================================================
::  HTMS DEPLOY - Build + Push Git + Pull ve tat ca sites
::  Doc danh sach sites tu deploy\sites.conf
::
::  LAN DAU: chay deploy\setup-ssh.bat truoc (1 lan duy nhat)
::  SAU DO  : chi can chay file nay moi lan deploy
:: ============================================================

:: -- Xac dinh duong dan tuyet doi (lam viec du chay tu dau) --
set "DEPLOY_DIR=%~dp0"
set "SITES_FILE=%DEPLOY_DIR%sites.conf"

:: -- Chuyen ve thu muc goc project (cha cua deploy\) --
cd /d "%DEPLOY_DIR%.."
set "PROJECT_ROOT=%CD%"

set "PHP_REMOTE=/usr/local/lsws/lsphp83/bin/php"

echo.
echo  ============================================================
echo   HTMS AUTO DEPLOY  -  %date%  %time:~0,8%
echo   Project: %PROJECT_ROOT%
echo  ============================================================
echo.

:: -- Kiem tra sites.conf --
if not exist "%SITES_FILE%" (
    echo  [LOI] Khong tim thay: %SITES_FILE%
    echo  Hay tao file sites.conf trong thu muc deploy\
    echo.
    pause
    exit /b 1
)
echo  [OK] Doc sites.conf: %SITES_FILE%
echo.

:: -- Buoc 1: Build frontend --
echo  [1/3] Build Frontend...
echo  -----------------------------------------------------------
call npm run build
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo  [LOI] Build that bai! Xem loi Vite o tren.
    pause
    exit /b 1
)
echo  [OK] Build xong.
echo.

:: -- Buoc 2: Git commit + push --
echo  [2/3] Git commit va push...
echo  -----------------------------------------------------------
git add .

git diff --cached --quiet
if %ERRORLEVEL% EQU 0 (
    echo  [INFO] Khong co thay doi moi - bo qua commit.
) else (
    set "COMMIT_MSG=Deploy: %date% %time:~0,8%"
    git commit -m "!COMMIT_MSG!"
    if !ERRORLEVEL! NEQ 0 (
        echo  [LOI] Commit that bai!
        pause
        exit /b 1
    )
    echo  [OK] Da commit.
)

git push origin main
if %ERRORLEVEL% NEQ 0 (
    echo  [LOI] Push that bai! Kiem tra ket noi GitHub.
    pause
    exit /b 1
)
echo  [OK] Push len GitHub thanh cong.
echo.

:: -- Buoc 3: Pull ve tung server --
echo  [3/3] Deploy len cac Server...
echo  -----------------------------------------------------------

set /a SITE_NUM=0
set /a SITE_OK=0
set /a SITE_FAIL=0

for /F "usebackq tokens=1,2,3,4,5 delims=|" %%A in ("%SITES_FILE%") do (
    set "RAW=%%A"
    set "RAW=!RAW: =!"
    if "!RAW:~0,1!" NEQ "#" if "!RAW!" NEQ "" (
        set /a SITE_NUM+=1

        set "S_LABEL=%%A"
        set "S_USER=%%B"
        set "S_HOST=%%C"
        set "S_DIR=%%D"
        set "S_PORT=%%E"

        set "S_USER=!S_USER: =!"
        set "S_HOST=!S_HOST: =!"
        set "S_DIR=!S_DIR: =!"
        set "S_PORT=!S_PORT: =!"
        if "!S_PORT!"=="" set "S_PORT=22"

        for /F "tokens=*" %%X in ("!S_LABEL!") do set "S_LABEL=%%X"

        echo.
        echo  [!SITE_NUM!] !S_LABEL! - !S_USER!@!S_HOST!:!S_PORT!
        echo  -----------------------------------------------------------

        ssh -p !S_PORT! -o StrictHostKeyChecking=no -o ConnectTimeout=20 !S_USER!@!S_HOST! "cd !S_DIR! && git checkout -- index/ 2>/dev/null; git pull origin main && mkdir -p ~/bin && ln -sf /usr/local/lsws/lsphp83/bin/php ~/bin/php && export PATH=$HOME/bin:$PATH && composer install --no-dev --optimize-autoloader --ignore-platform-reqs && php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache && echo DEPLOY_OK"

        if !ERRORLEVEL! EQU 0 (
            echo  [OK] !S_LABEL! - THANH CONG
            set /a SITE_OK+=1
        ) else (
            echo  [LOI] !S_LABEL! - THAT BAI
            echo       -- Da chay setup-ssh.bat chua?
            echo       -- Kiem tra SSH_PORT trong sites.conf
            set /a SITE_FAIL+=1
        )
    )
)

:: -- Ket qua --
echo.
echo  ============================================================
echo   KET QUA: !SITE_OK!/!SITE_NUM! sites deploy thanh cong
echo   Hoan thanh luc: %time:~0,8%
echo  ============================================================
echo.
if !SITE_FAIL! GTR 0 (
    echo  [!] !SITE_FAIL! site that bai - xem ghi chu o tren.
    echo.
)
pause
