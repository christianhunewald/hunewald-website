# ── Auto-Update Script for hunewald.de ──
# This script downloads the latest website files from GitHub
# and updates the live site. Runs every 5 minutes via Task Scheduler.

$ErrorActionPreference = "Stop"
$repoUrl = "https://raw.githubusercontent.com/christianhunewald/hunewald-website/main"
$webRoot = "C:\inetpub\wwwroot"
$logFile = "C:\website-update.log"

# Files to sync (excluding posts.json - that's user content)
$files = @(
    "index.html",
    "style.css",
    "blog.html",
    "admin.html",
    "save_posts.php",
    "web.config",
    "contact.php"
)

function Log-Message {
    param($msg)
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    "$timestamp - $msg" | Out-File -FilePath $logFile -Append -Encoding UTF8
}

Log-Message "=== Update started ==="

$updated = 0
foreach ($file in $files) {
    try {
        $url = "$repoUrl/$file"
        $localPath = Join-Path $webRoot $file
        
        # Download to temp first
        $tempPath = "$localPath.new"
        Invoke-WebRequest -Uri $url -OutFile $tempPath -UseBasicParsing
        
        # Compare with existing file
        $needsUpdate = $true
        if (Test-Path $localPath) {
            $oldHash = (Get-FileHash $localPath -Algorithm MD5).Hash
            $newHash = (Get-FileHash $tempPath -Algorithm MD5).Hash
            if ($oldHash -eq $newHash) {
                $needsUpdate = $false
                Remove-Item $tempPath
            }
        }
        
        if ($needsUpdate) {
            Move-Item -Path $tempPath -Destination $localPath -Force
            Log-Message "Updated: $file"
            $updated++
        }
    }
    catch {
        Log-Message "ERROR updating $file : $_"
        if (Test-Path $tempPath) { Remove-Item $tempPath -ErrorAction SilentlyContinue }
    }
}

if ($updated -gt 0) {
    Log-Message "Updated $updated file(s)"
} else {
    Log-Message "No changes"
}

Log-Message "=== Update finished ==="
