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

Write-Output "=== public/index.php ==="
Get-FtpFile "public_html/public/index.php"

Write-Output "`n=== vendor/autoload.php ==="
Get-FtpFile "public_html/vendor/autoload.php"

Write-Output "`n=== bootstrap/app.php ==="
Get-FtpFile "public_html/bootstrap/app.php"

Write-Output "`n=== config/session.php (20 baris pertama) ==="
$session = Get-FtpFile "public_html/config/session.php"
$lines = $session -split "`n"
$lines[0..25] -join "`n"
