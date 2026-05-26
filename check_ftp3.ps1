$ftpHost = 'ptpjml.com'
$ftpUser = 'ptpjmlco'
$ftpPass = 'Xo$c458^7Y~y'

function Test-FtpFile($remotePath) {
    try {
        $request = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost/$remotePath")
        $request.Method = [System.Net.WebRequestMethods+Ftp]::GetFileSize
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
        $request.UsePassive = $true
        $response = $request.GetResponse()
        $size = $response.ContentLength
        $response.Close()
        return "ADA ($size bytes)"
    } catch {
        return "TIDAK ADA: $_"
    }
}

function Get-FtpFile($remotePath) {
    $request = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost/$remotePath")
    $request.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
    $request.UsePassive = $true
    $response = $request.GetResponse()
    $reader = New-Object System.IO.StreamReader($response.GetResponseStream())
    $content = $reader.ReadToEnd()
    $reader.Close()
    $response.Close()
    return $content
}

function List-FtpDir($remotePath) {
    $request = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost/$remotePath")
    $request.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
    $request.UsePassive = $true
    $response = $request.GetResponse()
    $reader = New-Object System.IO.StreamReader($response.GetResponseStream())
    $list = $reader.ReadToEnd()
    $reader.Close()
    $response.Close()
    return $list
}

Write-Output "=== Cek vendor/composer/autoload_real.php ==="
Test-FtpFile "public_html/vendor/composer/autoload_real.php"

Write-Output "`n=== Isi vendor/composer/ ==="
List-FtpDir "public_html/vendor/composer/"

Write-Output "`n=== Cek composer.lock (10 baris pertama) ==="
$lock = Get-FtpFile "public_html/composer.lock"
$lockLines = $lock -split "`n"
$lockLines[0..9] -join "`n"

Write-Output "`n=== Cek .env ==="
Get-FtpFile "public_html/.env"

Write-Output "`n=== Cek composer.json (30 baris pertama) ==="
$json = Get-FtpFile "public_html/composer.json"
$jsonLines = $json -split "`n"
$jsonLines[0..29] -join "`n"
