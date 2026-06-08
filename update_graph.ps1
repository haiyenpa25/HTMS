param(
    [switch]$OpenUI,
    [switch]$WatchMode
)

# Root directory = noi dat script
$Root = $PSScriptRoot
Set-Location $Root

# ===================================================
# HELPER: Console output
# ===================================================
function Write-Step([string]$num, [string]$msg) {
    Write-Host ""
    Write-Host "  [$num]" -ForegroundColor Cyan -NoNewline
    Write-Host " $msg" -ForegroundColor White
}

function Write-Ok([string]$msg) {
    Write-Host "  [OK] $msg" -ForegroundColor Green
}

function Write-Fail([string]$msg) {
    Write-Host "  [FAIL] $msg" -ForegroundColor Red
}

function Write-Info([string]$msg) {
    Write-Host "  -->  $msg" -ForegroundColor DarkGray
}

function Get-ElapsedSec([datetime]$start) {
    $elapsed = (Get-Date) - $start
    return "$([math]::Round($elapsed.TotalSeconds, 1))s"
}

# ===================================================
# PRE-FLIGHT: Tim Python
# ===================================================
function Find-Python {
    foreach ($cmd in @("python", "python3", "py")) {
        try {
            $ver = & $cmd --version 2>&1
            if ($ver -match "Python 3") {
                return $cmd
            }
        } catch {}
    }
    return $null
}

# ===================================================
# BANNER
# ===================================================
Write-Host ""
Write-Host "  =============================================" -ForegroundColor Magenta
Write-Host "   HTMS VibeCode Graph Updater  v2.0" -ForegroundColor Magenta
Write-Host "   Knowledge Graph | Enrichment | Code Index" -ForegroundColor Magenta
Write-Host "  =============================================" -ForegroundColor Magenta
Write-Host "  Root: $Root" -ForegroundColor DarkGray
Write-Host "  Time: $(Get-Date -Format 'HH:mm:ss  dd/MM/yyyy')" -ForegroundColor DarkGray
Write-Host ""

# ===================================================
# ENV: Fix Unicode encoding tren Windows (cli.py dung emoji)
# ===================================================
$env:PYTHONIOENCODING = "utf-8"
$env:PYTHONUTF8 = "1"

# ===================================================
# CHECK PYTHON
# ===================================================
Write-Host "  Kiem tra moi truong..." -ForegroundColor DarkGray
$tStart = Get-Date
$python = Find-Python

if (-not $python) {
    Write-Fail "Python 3 khong tim thay! Cai tai https://python.org"
    exit 1
}

$pyVer = & $python --version 2>&1
Write-Info "Python: $pyVer"

# Check vibecode/enricher.py ton tai
if (-not (Test-Path "$Root\vibecode\enricher.py")) {
    Write-Fail "Khong tim thay vibecode\enricher.py - Kiem tra lai cau truc project!"
    exit 1
}

# ===================================================
# CHECK + AUTO-INSTALL vibecode deps
# ===================================================
$reqFile = "$Root\vibecode\requirements.txt"
if (Test-Path $reqFile) {
    $checkImport = & $python -c "import pathspec, typer, tree_sitter" 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Info "Cai dat vibecode dependencies tu vibecode\requirements.txt..."
        & $python -m pip install -r $reqFile -q
        if ($LASTEXITCODE -ne 0) {
            Write-Fail "Khong the cai dat dependencies!"
            exit 1
        }
        Write-Ok "Dependencies da duoc cai dat"
    }
}

# ===================================================
# WATCH MODE
# ===================================================
if ($WatchMode) {
    Write-Step "WATCH" "Auto-update khi luu file (Ctrl+C de dung)"
    & $python -m vibecode watch
    exit 0
}

# ===================================================
# STEP 1: ENRICH
# Sinh: vibecode_enrichment.json + docs/KNOWLEDGE_GRAPH.md
# NOTE: enrich command khong can argument, mac dinh root_dir='.'
# ===================================================
Write-Step "1/2" "Enrich → vibecode_enrichment.json + docs/KNOWLEDGE_GRAPH.md"
$t1 = Get-Date

$enrichOut = & $python -m vibecode enrich 2>&1
$enrichExit = $LASTEXITCODE

if ($enrichExit -eq 0) {
    Write-Ok "Enrichment hoan tat trong $(Get-ElapsedSec $t1)"

    $jsonPath = "$Root\vibecode_enrichment.json"
    if (Test-Path $jsonPath) {
        $jsonSize = [math]::Round((Get-Item $jsonPath).Length / 1KB, 1)
        Write-Info "vibecode_enrichment.json: $jsonSize KB"
    }

    $mdPath = "$Root\docs\KNOWLEDGE_GRAPH.md"
    if (Test-Path $mdPath) {
        $mdLines = (Get-Content $mdPath | Measure-Object -Line).Lines
        Write-Info "docs/KNOWLEDGE_GRAPH.md: $mdLines dong"
    }
} else {
    Write-Fail "Enrich that bai (exit code: $enrichExit)"
    Write-Host ($enrichOut -join "`n") -ForegroundColor DarkRed
    exit 1
}

# ===================================================
# STEP 2: SCAN
# NOTE: scan command khong can argument, mac dinh root_dir='.'
# ===================================================
Write-Step "2/2" "Scan → cap nhat vibecode.db (SQLite graph index)"
$t2 = Get-Date

$scanOut = & $python -m vibecode scan 2>&1
$scanExit = $LASTEXITCODE

if ($scanExit -eq 0) {
    Write-Ok "Graph index cap nhat trong $(Get-ElapsedSec $t2)"
    foreach ($line in $scanOut) {
        if ($line -match "\d+\s*file") {
            Write-Info $line
            break
        }
    }
} else {
    Write-Fail "Scan that bai (exit code: $scanExit)"
    Write-Host ($scanOut -join "`n") -ForegroundColor DarkRed
    exit 1
}


# ===================================================
# SUMMARY
# ===================================================
$totalTime = Get-ElapsedSec $tStart  # $tStart duoc dinh nghia o dau script
Write-Host ""
Write-Host "  ---------------------------------------------" -ForegroundColor DarkGray
Write-Host "  [DONE] Graph da duoc cap nhat!" -ForegroundColor Green
Write-Host ""
Write-Host "  Lenh tiep theo:" -ForegroundColor DarkGray
Write-Host "    python -m vibecode ui          → Mo Visual Graph tren browser" -ForegroundColor Cyan
Write-Host "    python -m vibecode context app/Http/Controllers/... → Xem context" -ForegroundColor Cyan
Write-Host "    .\update_graph.ps1 -WatchMode  → Auto-update khi sua file" -ForegroundColor Cyan
Write-Host "    .\update_graph.ps1 -OpenUI     → Cap nhat + mo UI ngay" -ForegroundColor Cyan
Write-Host ""

# Mo UI neu co flag
if ($OpenUI) {
    Write-Step "UI" "Mo Visual Graph tren browser..."
    & $python -m vibecode ui
}
