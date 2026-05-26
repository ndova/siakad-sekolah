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
        return "ADA (size: $size bytes)"
    } catch {
        return "TIDAK ADA: $_"
    }
}

Write-Output "=== Cek autoload.php ==="
Test-FtpFile "public_html/vendor/autoload.php"

Write-Output "`n=== Cek .env ==="
Test-FtpFile "public_html/.env"

Write-Output "`n=== Cek bootstrap/cache ==="
$listReq = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost/public_html/bootstrap/cache/")
$listReq.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
$listReq.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
$listReq.UsePassive = $true
$listRes = $listReq.GetResponse()
$listReader = New-Object System.IO.StreamReader($listRes.GetResponseStream())
$list = $listReader.ReadToEnd()
$listReader.Close()
$listRes.Close()
Write-Output $list

Write-Output "`n=== index.php ==="
Get-FtpFile "public_html/index.php"

Write-Output "`n=== .htaccess ==="
Get-FtpFile "public_html/.htaccess"
