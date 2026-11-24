[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [ValidateNotNullOrEmpty()]
    [string] $Destino,

    [string] $GpgPath
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

function Assert-FileName {
    param([string] $Name)

    if ([string]::IsNullOrWhiteSpace($Name) -or
        $Name -in @('.', '..') -or
        $Name.IndexOfAny([System.IO.Path]::GetInvalidFileNameChars()) -ge 0 -or
        $Name.Contains('/') -or $Name.Contains('\')) {
        throw 'El manifiesto contiene un nombre de archivo no valido.'
    }
}

function Assert-Sha256 {
    param([string] $Hash)

    if ($Hash -notmatch '^[0-9a-fA-F]{64}$') {
        throw 'El manifiesto contiene un SHA256 no valido.'
    }
}

function Assert-EmptyDestination {
    param([string] $Path)

    if (Test-Path -LiteralPath $Path) {
        $item = Get-Item -LiteralPath $Path -Force
        if (-not $item.PSIsContainer) {
            throw 'Destino debe ser una carpeta nueva o una carpeta vacia.'
        }
        if (($item.Attributes -band [System.IO.FileAttributes]::ReparsePoint) -ne 0) {
            throw 'Destino debe ser una carpeta normal, sin enlaces ni uniones de directorio.'
        }
        if (@(Get-ChildItem -LiteralPath $Path -Force | Select-Object -First 1).Count -ne 0) {
            throw 'Destino ya contiene archivos. Elige una carpeta nueva o vacia.'
        }
    }
}

function Resolve-GpgExecutable {
    param([string] $RequestedPath)

    if (-not [string]::IsNullOrWhiteSpace($RequestedPath)) {
        $item = Get-Item -LiteralPath $RequestedPath -Force
        if ($item.PSIsContainer) {
            throw 'GpgPath debe apuntar al ejecutable de GPG.'
        }
        return $item.FullName
    }

    $command = Get-Command gpg.exe -CommandType Application -ErrorAction SilentlyContinue |
        Select-Object -First 1
    if ($null -ne $command) {
        return $command.Source
    }

    $gitGpg = Join-Path $env:ProgramFiles 'Git\usr\bin\gpg.exe'
    if (Test-Path -LiteralPath $gitGpg -PathType Leaf) {
        return $gitGpg
    }

    throw 'No se encuentra GPG. Instala Git para Windows o indica -GpgPath con su ejecutable.'
}

function Convert-GpgPath {
    param(
        [string] $Path,
        [string] $GpgExecutable
    )

    $normalized = $Path.Replace('\', '/')
    $cygpath = Join-Path ([System.IO.Path]::GetDirectoryName($GpgExecutable)) 'cygpath.exe'
    if (-not (Test-Path -LiteralPath $cygpath -PathType Leaf)) {
        return $normalized
    }

    $converter = New-Object System.Diagnostics.Process
    try {
        $converter.StartInfo.FileName = $cygpath
        $converter.StartInfo.Arguments = '-u -- "' + $normalized + '"'
        $converter.StartInfo.UseShellExecute = $false
        $converter.StartInfo.CreateNoWindow = $true
        $converter.StartInfo.RedirectStandardOutput = $true
        $converter.StartInfo.RedirectStandardError = $true
        $converter.StartInfo.StandardOutputEncoding = [System.Text.UTF8Encoding]::new($false)
        $converter.Start() | Out-Null
        $stdout = $converter.StandardOutput.ReadToEndAsync()
        $stderr = $converter.StandardError.ReadToEndAsync()
        $converter.WaitForExit()
        $converted = $stdout.GetAwaiter().GetResult().TrimEnd([char[]]@([char] 13, [char] 10))
        $conversionError = $stderr.GetAwaiter().GetResult()
        if ($converter.ExitCode -ne 0 -or -not $converted.StartsWith('/') -or
            $converted.Contains([char] 13) -or $converted.Contains([char] 10)) {
            throw ('No se pudo convertir una ruta para GPG de Git. ' + $conversionError.Trim())
        }
        return $converted
    }
    finally {
        $converter.Dispose()
    }
}

function Assert-SafeZip {
    param(
        [string] $ZipPath,
        [string] $TargetPath,
        [long] $ExpectedFiles
    )

    $separator = [System.IO.Path]::DirectorySeparatorChar
    $root = $TargetPath.TrimEnd([char[]]@('\', '/')) + $separator
    $paths = New-Object 'System.Collections.Generic.HashSet[string]' ([System.StringComparer]::OrdinalIgnoreCase)
    $archive = [System.IO.Compression.ZipFile]::OpenRead($ZipPath)
    try {
        $fileCount = 0L
        foreach ($entry in $archive.Entries) {
            $name = $entry.FullName.Replace('/', [string] $separator).Replace('\', [string] $separator)
            if ([string]::IsNullOrEmpty($name) -or [System.IO.Path]::IsPathRooted($name)) {
                throw 'El ZIP contiene una ruta absoluta o vacia.'
            }
            $isDirectory = $name.EndsWith([string] $separator)
            $relative = $name.TrimEnd($separator)
            foreach ($segment in $relative.Split($separator)) {
                if ([string]::IsNullOrEmpty($segment) -or $segment -in @('.', '..') -or
                    $segment.Contains(':') -or $segment.EndsWith(' ') -or $segment.EndsWith('.') -or
                    $segment -match '^(?i:CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])(?:\.|$)') {
                    throw 'El ZIP contiene un segmento de ruta no seguro para Windows.'
                }
            }

            $resolved = [System.IO.Path]::GetFullPath([System.IO.Path]::Combine($root, $relative))
            if (-not $resolved.StartsWith($root, [System.StringComparison]::OrdinalIgnoreCase)) {
                throw 'El ZIP intenta escribir fuera de Destino.'
            }
            if (-not $paths.Add($resolved)) {
                throw 'El ZIP contiene rutas duplicadas.'
            }

            $unixType = ($entry.ExternalAttributes -shr 16) -band 0xF000
            $windowsReparsePoint = ($entry.ExternalAttributes -band 0x400) -ne 0
            if ($unixType -eq 0xA000 -or $windowsReparsePoint) {
                throw 'El ZIP contiene un enlace simbolico o un punto de analisis.'
            }
            if (-not $isDirectory) {
                $fileCount++
            }
        }
        if ($fileCount -ne $ExpectedFiles) {
            throw "El ZIP contiene $fileCount archivos y el manifiesto declara $ExpectedFiles."
        }
    }
    finally {
        $archive.Dispose()
    }
}

$temporaryDirectory = $null
$cipherPath = $null
$zipPath = $null
$process = $null
$passwordWriter = $null
$password = $null
$passwordPointer = [IntPtr]::Zero

try {
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    $targetPath = [System.IO.Path]::GetFullPath($Destino)
    Assert-EmptyDestination $targetPath
    $gpgExecutable = Resolve-GpgExecutable $GpgPath

    $manifestPath = Join-Path $PSScriptRoot 'manifest.json'
    $manifest = Get-Content -Raw -LiteralPath $manifestPath -Encoding UTF8 | ConvertFrom-Json
    Assert-FileName $manifest.archive_name
    Assert-Sha256 $manifest.archive_sha256
    Assert-Sha256 $manifest.zip_sha256
    $expectedFiles = 0L
    if (-not [long]::TryParse([string] $manifest.files, [ref] $expectedFiles) -or $expectedFiles -lt 1) {
        throw 'El manifiesto debe declarar un numero positivo de archivos.'
    }
    $parts = @($manifest.parts)
    if ($parts.Count -eq 0) {
        throw 'El manifiesto no contiene partes del respaldo.'
    }

    $partNames = New-Object 'System.Collections.Generic.HashSet[string]' ([System.StringComparer]::OrdinalIgnoreCase)
    foreach ($part in $parts) {
        Assert-FileName $part.file
        Assert-Sha256 $part.sha256
        if (-not $partNames.Add($part.file)) {
            throw 'El manifiesto repite una parte del respaldo.'
        }
        $expectedBytes = 0L
        if (-not [long]::TryParse([string] $part.bytes, [ref] $expectedBytes) -or $expectedBytes -lt 1) {
            throw 'El manifiesto contiene un tamano de parte no valido.'
        }
        $partPath = Join-Path $PSScriptRoot $part.file
        $partItem = Get-Item -LiteralPath $partPath -Force
        if ($partItem.PSIsContainer -or $partItem.Length -ne $expectedBytes) {
            throw "Falta contenido o el tamano es incorrecto en $($part.file)."
        }
        if ((Get-FileHash -LiteralPath $partPath -Algorithm SHA256).Hash -ne $part.sha256) {
            throw "El SHA256 es incorrecto en $($part.file)."
        }
    }

    $temporaryDirectory = Join-Path ([System.IO.Path]::GetTempPath()) ('Mythos-Restauracion-' + [Guid]::NewGuid().ToString('N'))
    [System.IO.Directory]::CreateDirectory($temporaryDirectory) | Out-Null
    $cipherPath = Join-Path $temporaryDirectory 'respaldo.zip.gpg'
    $zipPath = Join-Path $temporaryDirectory 'respaldo.zip'

    Write-Host 'Partes verificadas. Reconstruyendo el respaldo cifrado...'
    $outputStream = [System.IO.File]::Open($cipherPath, [System.IO.FileMode]::CreateNew)
    try {
        foreach ($part in $parts) {
            $inputStream = [System.IO.File]::OpenRead((Join-Path $PSScriptRoot $part.file))
            try {
                $inputStream.CopyTo($outputStream)
            }
            finally {
                $inputStream.Dispose()
            }
        }
    }
    finally {
        $outputStream.Dispose()
    }
    if ((Get-FileHash -LiteralPath $cipherPath -Algorithm SHA256).Hash -ne $manifest.archive_sha256) {
        throw 'El SHA256 del respaldo reconstruido es incorrecto.'
    }

    $password = Read-Host 'Contrasena del respaldo' -AsSecureString
    if ($password.Length -eq 0) {
        throw 'La contrasena esta vacia.'
    }

    $startInfo = New-Object System.Diagnostics.ProcessStartInfo
    $startInfo.FileName = $gpgExecutable
    $gpgZipPath = Convert-GpgPath $zipPath $gpgExecutable
    $gpgCipherPath = Convert-GpgPath $cipherPath $gpgExecutable
    $startInfo.Arguments = '--no-options --no-keyring --no-random-seed-file --batch --pinentry-mode loopback --no-symkey-cache --passphrase-fd 0 --output "' + $gpgZipPath + '" --decrypt "' + $gpgCipherPath + '"'
    $startInfo.UseShellExecute = $false
    $startInfo.CreateNoWindow = $true
    $startInfo.RedirectStandardInput = $true
    $startInfo.RedirectStandardOutput = $true
    $startInfo.RedirectStandardError = $true
    $process = New-Object System.Diagnostics.Process
    $process.StartInfo = $startInfo
    $process.Start() | Out-Null
    $stdoutTask = $process.StandardOutput.ReadToEndAsync()
    $stderrTask = $process.StandardError.ReadToEndAsync()
    try {
        $passwordWriter = [System.IO.StreamWriter]::new(
            $process.StandardInput.BaseStream,
            [System.Text.UTF8Encoding]::new($false)
        )
        $passwordPointer = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($password)
        for ($index = 0; $index -lt $password.Length; $index++) {
            $character = [char] ([System.Runtime.InteropServices.Marshal]::ReadInt16($passwordPointer, $index * 2) -band 0xFFFF)
            $passwordWriter.Write($character)
        }
        $passwordWriter.Write([char] 10)
        $passwordWriter.Dispose()
        $passwordWriter = $null
    }
    finally {
        if ($passwordPointer -ne [IntPtr]::Zero) {
            [System.Runtime.InteropServices.Marshal]::ZeroFreeBSTR($passwordPointer)
            $passwordPointer = [IntPtr]::Zero
        }
        $character = [char] 0
        $password.Dispose()
        $password = $null
    }
    $process.WaitForExit()
    $null = $stdoutTask.GetAwaiter().GetResult()
    $gpgError = $stderrTask.GetAwaiter().GetResult()
    if ($process.ExitCode -ne 0) {
        throw ('GPG no pudo descifrar el respaldo. Comprueba la contrasena. ' + $gpgError.Trim())
    }
    if ((Get-FileHash -LiteralPath $zipPath -Algorithm SHA256).Hash -ne $manifest.zip_sha256) {
        throw 'El SHA256 del ZIP descifrado es incorrecto.'
    }

    Assert-SafeZip $zipPath $targetPath $expectedFiles
    Assert-EmptyDestination $targetPath
    [System.IO.Directory]::CreateDirectory($targetPath) | Out-Null
    Write-Host 'ZIP verificado. Extrayendo el proyecto completo...'
    [System.IO.Compression.ZipFile]::ExtractToDirectory($zipPath, $targetPath)
    Write-Host "Proyecto restaurado en $targetPath"
}
catch {
    Write-Error ('No se completo la restauracion. ' + $_.Exception.Message) -ErrorAction Continue
    exit 1
}
finally {
    if ($null -ne $passwordWriter) {
        try {
            $passwordWriter.Dispose()
        }
        catch {
            Write-Warning 'No se pudo comprobar el cierre de la entrada de GPG.'
        }
    }
    if ($passwordPointer -ne [IntPtr]::Zero) {
        [System.Runtime.InteropServices.Marshal]::ZeroFreeBSTR($passwordPointer)
    }
    if ($null -ne $password) {
        $password.Dispose()
    }
    if ($null -ne $process) {
        try {
            if (-not $process.HasExited) {
                $process.Kill()
                $process.WaitForExit()
            }
        }
        catch {
            Write-Warning 'No se pudo comprobar el cierre de GPG.'
        }
        $process.Dispose()
    }
    foreach ($temporaryFile in @($zipPath, $cipherPath)) {
        if ($null -ne $temporaryFile -and [System.IO.File]::Exists($temporaryFile)) {
            try {
                [System.IO.File]::Delete($temporaryFile)
            }
            catch {
                Write-Warning "No se pudo eliminar el temporal $temporaryFile."
            }
        }
    }
    if ($null -ne $temporaryDirectory -and [System.IO.Directory]::Exists($temporaryDirectory)) {
        try {
            [System.IO.Directory]::Delete($temporaryDirectory, $false)
        }
        catch {
            Write-Warning "No se pudo eliminar la carpeta temporal $temporaryDirectory."
        }
    }
}
