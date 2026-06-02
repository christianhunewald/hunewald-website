$webRoot = "C:\inetpub\wwwroot"
$gateRoot = "C:\inetpub\gate"
$logFile = "C:\website-update.log"

# Files to sync to wwwroot (excluding posts.json and smtp_password.txt)
$wwwFiles = @(
    "index.html",
    "style.css",
    "blog.html",
    "admin.html",
    "save_posts.php",
    "contact.php",
    "web.config",
    "portrait.jpg",
    "gate-card.html",
    "save.php",
    "log.php",
    "delete.php"
)

# Files to sync to gate folder (subset of above)
$gateFiles = @(
    "gate-card.html",
    "save.php",
    "log.php",
    "delete.php"
)

$repoUrl = "https://raw.githubusercontent.com/christianhunewald/hunewald-website/main"

function Log-Message {
    param($msg)
    $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
    "$timestamp - $msg" | Out-File -FilePath $logFile -Append -Encoding UTF8
}

Log-Message "=== Update started ==="
$updated = 0

foreach ($file in $wwwFiles) {
    try {
        $url = "$repoUrl/$file"
        $localPath = Join-Path $webRoot $file
        $tempPath = "$localPath.new"

        Invoke-WebRequest -Uri $url -OutFile $tempPath -UseBasicParsing

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
            Log-Message "Updated wwwroot: $file"
            $updated++

            # If this file also belongs in gate folder, copy it there too
            if ($gateFiles -contains $file) {
                $gatePath = Join-Path $gateRoot $file
                Copy-Item -Path $localPath -Destination $gatePath -Force
                Log-Message "Synced to gate: $file"
            }
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
