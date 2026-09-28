$ErrorActionPreference = "Stop"

$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
$gitCommand = Get-Command git.exe -ErrorAction SilentlyContinue

if ($null -eq $gitCommand) {
    $gitCandidates = @(
        "C:\Program Files\Git\cmd\git.exe",
        "C:\Program Files (x86)\Git\cmd\git.exe"
    )
    $gitPath = $gitCandidates | Where-Object { Test-Path $_ } | Select-Object -First 1
} else {
    $gitPath = $gitCommand.Source
}

if (-not $gitPath) {
    throw "Git was not found. Install Git for Windows or add git.exe to PATH."
}

$gitDirectory = Join-Path $repoRoot ".git"
$watcher = [System.IO.FileSystemWatcher]::new($repoRoot, "*")
$watcher.IncludeSubdirectories = $true
$watcher.NotifyFilter = [System.IO.NotifyFilters]"FileName, LastWrite, DirectoryName"
$watcher.EnableRaisingEvents = $true

$eventPrefix = "GitHubAutoSync-$PID"
foreach ($eventName in @("Changed", "Created", "Deleted", "Renamed")) {
    Register-ObjectEvent -InputObject $watcher -EventName $eventName -SourceIdentifier "$eventPrefix-$eventName" | Out-Null
}

Write-Host "GitHub auto-sync watcher started. Saves are grouped for 5 seconds."
$hasPendingFiles = $false
$pendingPush = $false
$lastFileChange = [datetime]::MinValue
$nextPushAttempt = [datetime]::MinValue

while ($true) {
    $fsEvent = Wait-Event -Timeout 1
    if ($null -ne $fsEvent) {
        $changedPath = $fsEvent.SourceEventArgs.FullPath
        Remove-Event -EventIdentifier $fsEvent.EventIdentifier

        $isGitMetadata = $changedPath.Equals($gitDirectory, [System.StringComparison]::OrdinalIgnoreCase) -or
            $changedPath.StartsWith(($gitDirectory + [System.IO.Path]::DirectorySeparatorChar), [System.StringComparison]::OrdinalIgnoreCase)
        if (-not $isGitMetadata) {
            $hasPendingFiles = $true
            $lastFileChange = Get-Date
        }
    }

    $now = Get-Date
    if ($hasPendingFiles -and ($now - $lastFileChange).TotalSeconds -ge 5) {
        & $gitPath -C $repoRoot add --all
        if ($LASTEXITCODE -ne 0) {
            Write-Warning "Auto-sync could not stage changes; it will retry."
            $lastFileChange = $now
            continue
        }

        & $gitPath -C $repoRoot diff --cached --quiet
        $diffExitCode = $LASTEXITCODE
        if ($diffExitCode -eq 1) {
            $commitMessage = "Auto-sync: " + $now.ToString("yyyy-MM-dd HH:mm:ss")
            & $gitPath -C $repoRoot commit -m $commitMessage
            if ($LASTEXITCODE -eq 0) {
                $pendingPush = $true
                $nextPushAttempt = [datetime]::MinValue
            } else {
                Write-Warning "Auto-sync could not commit changes; it will retry."
                $lastFileChange = $now
                continue
            }
        } elseif ($diffExitCode -ne 0) {
            Write-Warning "Auto-sync could not inspect staged changes; it will retry."
            $lastFileChange = $now
            continue
        }

        $hasPendingFiles = $false
    }

    if ($pendingPush -and $now -ge $nextPushAttempt) {
        & $gitPath -C $repoRoot push origin main
        if ($LASTEXITCODE -eq 0) {
            Write-Host "GitHub auto-sync push completed."
            $pendingPush = $false
        } else {
            Write-Warning "Auto-sync push failed; it will retry in 30 seconds."
            $nextPushAttempt = $now.AddSeconds(30)
        }
    }
}