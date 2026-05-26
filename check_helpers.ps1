$ftpHost = 'ptpjml.com'
$ftpUser = 'ptpjmlco'
$ftpPass = 'Xo$c458^7Y~y'

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

Write-Output "=== Cek Laravel helpers.php ==="
Test-FtpFile "public_html/vendor/laravel/framework/src/Illuminate/Foundation/helpers.php"

Write-Output "`n=== Cek autoload_files.php ==="
Get-FtpFile "public_html/vendor/composer/autoload_files.php"

Write-Output "`n=== Cek autoload_static.php (50 baris pertama) ==="
$static = Get-FtpFile "public_html/vendor/composer/autoload_static.php"
$lines = $static -split "`n"
$lines[0..49] -join "`n"
