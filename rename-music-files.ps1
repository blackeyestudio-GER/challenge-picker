# PowerShell script to reorganize music files and folders
# - Moves date to front of folder names
# - Renames files to numbered sequence (1.mp3, 2.mp3, etc.)
# - Organizes loose files into date-based folders

param(
    [Parameter(Mandatory=$false)]
    [string]$Path = (Get-Location).Path,
    
    [Parameter(Mandatory=$false)]
    [switch]$WhatIf = $false
)

Write-Host "Starting file reorganization..." -ForegroundColor Cyan
Write-Host "Working directory: $Path" -ForegroundColor Gray
if ($WhatIf) {
    Write-Host "DRY RUN MODE - No changes will be made" -ForegroundColor Yellow
}
Write-Host ""

# Function to extract date from string (YYYY_MM_DD format)
function Extract-Date {
    param([string]$InputString)
    
    # Try to find date pattern YYYY_MM_DD or YYYY-MM-DD
    if ($InputString -match '(\d{4})[_-](\d{2})[_-](\d{2})') {
        $year = $matches[1]
        $month = $matches[2]
        $day = $matches[3]
        return "$year`_$month`_$day"
    }
    return $null
}

# Function to remove date from string
function Remove-Date {
    param([string]$InputString)
    
    # Remove date pattern YYYY_MM_DD or YYYY-MM-DD
    $result = $InputString -replace '\d{4}[_-]\d{2}[_-]\d{2}', ''
    # Clean up leading/trailing underscores and hyphens
    $result = $result -replace '^[_-]+|[_-]+$', ''
    $result = $result -replace '[_-]+', '_'
    return $result
}

# Function to process folders
function Process-Folders {
    param([string]$RootPath)
    
    $folders = Get-ChildItem -Path $RootPath -Directory
    
    foreach ($folder in $folders) {
        $folderName = $folder.Name
        $date = Extract-Date -InputString $folderName
        
        if ($date) {
            # Check if date is already at the front
            if ($folderName -match "^$date") {
                Write-Host "Folder already has date at front: $folderName" -ForegroundColor Gray
                $newFolderName = $folderName
            } else {
                # Move date to front
                $stylePart = Remove-Date -InputString $folderName
                if ($stylePart) {
                    $newFolderName = "${date}_${stylePart}"
                } else {
                    $newFolderName = $date
                }
                
                Write-Host "Renaming folder: $folderName -> $newFolderName" -ForegroundColor Yellow
                
                if (-not $WhatIf) {
                    $newFolderPath = Join-Path $RootPath $newFolderName
                    if (Test-Path $newFolderPath) {
                        Write-Host "  WARNING: Target folder already exists, skipping..." -ForegroundColor Red
                        continue
                    }
                    Rename-Item -Path $folder.FullName -NewName $newFolderName
                    $folder = Get-Item (Join-Path $RootPath $newFolderName)
                }
            }
            
            # Process files in folder
            Process-FilesInFolder -FolderPath $folder.FullName -FolderName $newFolderName
        } else {
            Write-Host "No date found in folder: $folderName" -ForegroundColor DarkYellow
        }
    }
}

# Function to process files within a folder
function Process-FilesInFolder {
    param(
        [string]$FolderPath,
        [string]$FolderName
    )
    
    $files = Get-ChildItem -Path $FolderPath -File -Filter "*.mp3" | Sort-Object Name
    
    if ($files.Count -eq 0) {
        Write-Host "  No MP3 files found in folder" -ForegroundColor Gray
        return
    }
    
    Write-Host "  Processing $($files.Count) file(s) in folder..." -ForegroundColor Cyan
    
    $counter = 1
    foreach ($file in $files) {
        $newFileName = "$counter.mp3"
        $newFilePath = Join-Path $FolderPath $newFileName
        
        # Skip if already correctly named
        if ($file.Name -eq $newFileName) {
            Write-Host "    File already correctly named: $($file.Name)" -ForegroundColor Gray
            $counter++
            continue
        }
        
        # Check if target name already exists
        if (Test-Path $newFilePath) {
            Write-Host "    WARNING: Target file already exists: $newFileName, skipping..." -ForegroundColor Red
            $counter++
            continue
        }
        
        Write-Host "    Renaming: $($file.Name) -> $newFileName" -ForegroundColor Yellow
        
        if (-not $WhatIf) {
            Rename-Item -Path $file.FullName -NewName $newFileName
        }
        
        $counter++
    }
}

# Function to process loose files (not in folders)
function Process-LooseFiles {
    param([string]$RootPath)
    
    $looseFiles = Get-ChildItem -Path $RootPath -File -Filter "*.mp3"
    
    if ($looseFiles.Count -eq 0) {
        Write-Host "No loose MP3 files found in root directory" -ForegroundColor Gray
        return
    }
    
    Write-Host "Processing $($looseFiles.Count) loose file(s)..." -ForegroundColor Cyan
    
    foreach ($file in $looseFiles) {
        $fileName = $file.BaseName
        $date = Extract-Date -InputString $fileName
        
        if ($date) {
            # Create folder with date
            $stylePart = Remove-Date -InputString $fileName
            if ($stylePart) {
                $folderName = "${date}_${stylePart}"
            } else {
                $folderName = $date
            }
            
            $folderPath = Join-Path $RootPath $folderName
            
            Write-Host "Processing loose file: $($file.Name)" -ForegroundColor Yellow
            Write-Host "  Creating folder: $folderName" -ForegroundColor Yellow
            
            if (-not $WhatIf) {
                # Create folder if it doesn't exist
                if (-not (Test-Path $folderPath)) {
                    New-Item -ItemType Directory -Path $folderPath | Out-Null
                }
                
                # Move file to folder
                $newFilePath = Join-Path $folderPath $file.Name
                Write-Host "  Moving file to folder..." -ForegroundColor Yellow
                Move-Item -Path $file.FullName -Destination $newFilePath
                
                # Rename file to 1.mp3
                $finalPath = Join-Path $folderPath "1.mp3"
                if (-not (Test-Path $finalPath)) {
                    Rename-Item -Path $newFilePath -NewName "1.mp3"
                } else {
                    Write-Host "  WARNING: 1.mp3 already exists in folder, keeping original name" -ForegroundColor Red
                }
            }
        } else {
            Write-Host "No date found in loose file: $($file.Name)" -ForegroundColor DarkYellow
        }
    }
}

# Main execution
try {
    if (-not (Test-Path $Path)) {
        Write-Host "Error: Path does not exist: $Path" -ForegroundColor Red
        exit 1
    }
    
    Write-Host "Step 1: Processing folders..." -ForegroundColor Green
    Write-Host ""
    Process-Folders -RootPath $Path
    
    Write-Host ""
    Write-Host "Step 2: Processing loose files..." -ForegroundColor Green
    Write-Host ""
    Process-LooseFiles -RootPath $Path
    
    Write-Host ""
    Write-Host "Reorganization complete!" -ForegroundColor Green
    
    if ($WhatIf) {
        Write-Host ""
        Write-Host "This was a dry run. Run without -WhatIf to apply changes." -ForegroundColor Yellow
    }
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
    exit 1
}

