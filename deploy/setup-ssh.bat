@echo off
chcp 65001 >nul
setlocal EnableDelayedExpansion

:: ============================================================
::  HTMS SETUP SSH KEY
::  Chay 1 lan duy nhat de khong can nhap mat khau moi lan deploy
::  Doc danh sach server tu deploy\sites.conf
:: ============================================================

:: -- Xac dinh duong dan tuyet doi --
set DEPLOY_DIR=%~dp0
set SITES_FILE=%DEPLOY_DIR%sites.conf
:: Chuyen ve thu muc goc project
cd /d "%DEPLOY_DIR%.."

set SSH_KEY=%USERPROFILE%\.ssh\id_rsa
set SSH_PUB=%USERPROFILE%\.ssh\id_rsa.pub

echo.
echo  ====================================================
echo   HTMS SETUP SSH KEY - Chi chay 1 lan duy nhat
echo  ====================================================
echo.

:: -- Kiem tra sites.conf --
if not exist "%SITES_FILE%" (
    echo  [LOI] Khong tim thay: %SITES_FILE%
    echo  Hay tao file sites.conf truoc.
    echo.
    pause
    exit /b 1
)

:: -- Kiem tra ssh-keygen co san khong --
where ssh-keygen >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo  [LOI] Khong tim thay ssh-keygen.
    echo  Vui long cai Git for Windows: https://git-scm.com/download/win
    echo.
    pause
    exit /b 1
)

:: -- Tao SSH key neu chua co --
echo  [*] Kiem tra SSH key...
if not exist "%SSH_KEY%" (
    echo  [*] Chua co SSH key. Dang tao moi ^(RSA 4096-bit^)...
    ssh-keygen -t rsa -b 4096 -f "%SSH_KEY%" -N "" -C "HTMS-Deploy"
    if not exist "%SSH_KEY%" (
        echo  [LOI] Khong tao duoc SSH key!
        pause
        exit /b 1
    )
    echo  [OK] Da tao SSH key.
) else (
    echo  [OK] Da co SSH key: %SSH_KEY%
)
echo.

:: -- Doc noi dung public key --
set PUBKEY=
for /F "usebackq delims=" %%L in ("%SSH_PUB%") do set PUBKEY=%%L

echo  Public key cua ban:
echo  %PUBKEY%
echo.

:: -- Loop tung server trong sites.conf --
echo  ====================================================
echo   Dang copy key len cac server...
echo   (Se hoi mat khau SSH tung server - chi lan nay thoi)
echo  ====================================================
echo.

set SITE_NUM=0
set SITE_OK=0
set SITE_FAIL=0

for /F "usebackq tokens=1,2,3,4,5 delims=|" %%A in ("%SITES_FILE%") do (
    set RAWLINE=%%A
    set RAWLINE=!RAWLINE: =!
    if "!RAWLINE:~0,1!" NEQ "#" (
        if "!RAWLINE!" NEQ "" (
            set /a SITE_NUM+=1

            set LABEL=%%A
            set SSH_USER=%%B
            set SSH_HOST=%%C
            set REMOTE_DIR=%%D
            set SSH_PORT=%%E

            :: Xoa khoang trang
            set SSH_USER=!SSH_USER: =!
            set SSH_HOST=!SSH_HOST: =!
            set SSH_PORT=!SSH_PORT: =!
            set LABEL=!LABEL:  = !
            for /F "tokens=*" %%X in ("!LABEL!") do set LABEL=%%X

            :: Port mac dinh neu khong co trong config
            if "!SSH_PORT!"=="" set SSH_PORT=22

            echo  [!SITE_NUM!] Server: !LABEL!
            echo      Host: !SSH_USER!@!SSH_HOST! ^(port !SSH_PORT!^)
            echo      Nhap mat khau SSH khi duoc hoi...
            echo.

            :: Copy public key len server
            ssh -p !SSH_PORT! -o StrictHostKeyChecking=no -o ConnectTimeout=15 !SSH_USER!@!SSH_HOST! "mkdir -p ~/.ssh && chmod 700 ~/.ssh && echo %PUBKEY% >> ~/.ssh/authorized_keys && chmod 600 ~/.ssh/authorized_keys && echo OK"

            if !ERRORLEVEL! EQU 0 (
                echo.
                echo  [OK] !LABEL! - Da copy SSH key thanh cong!
                set /a SITE_OK+=1
            ) else (
                echo.
                echo  [LOI] !LABEL! - That bai. Kiem tra host, port va mat khau.
                set /a SITE_FAIL+=1
            )
            echo.
        )
    )
)

echo  ====================================================
echo   KET QUA: %SITE_OK%/%SITE_NUM% server da setup thanh cong
echo  ====================================================
echo.

if %SITE_OK% GTR 0 (
    echo  Tu nay deploy.bat se KHONG can nhap mat khau nua!
    echo.
    echo  Thu nghiem ket noi:
    echo    ssh SSH_USER@SSH_HOST "echo OK"
)
if %SITE_FAIL% GTR 0 (
    echo  [!] Con %SITE_FAIL% server that bai.
    echo      Kiem tra lai host, username trong sites.conf.
)

echo.
pause
