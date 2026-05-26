$ftpHost = 'ptpjml.com'
$ftpUser = 'ptpjmlco'
$ftpPass = 'Xo$c458^7Y~y'

function Delete-FtpFile($remotePath) {
    try {
        $request = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost/$remotePath")
        $request.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
        $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
        $request.UsePassive = $true
        $response = $request.GetResponse()
        $response.Close()
        return "DELETED: $remotePath"
    } catch {
        return "GAGAL: $remotePath - $_"
    }
}

Write-Output "=== Menghapus file cache bootstrap ==="

# Hapus file cache di bootstrap/cache/
$files = @('services.php', 'packages.php', 'config.php')
foreach ($file in $files) {
    $result = Delete-FtpFile "public_html/bootstrap/cache/$file"
    Write-Output $result
}

Write-Output "`n=== Cek isi storage/framework/cache/ ==="
$request = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost/public_html/storage/framework/cache/")
$request.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
$request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
$request.UsePassive = $true
$response = $request.GetResponse()
$reader = New-Object System.IO.StreamReader($response.GetResponseStream())
$list = $reader.ReadToEnd()
$reader.Close()
$response.Close()
Write-Output $list

Write-Output "`n=== Cek isi storage/framework/views/ ==="
$request2 = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost/public_html/storage/framework/views/")
$request2.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
$request2.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
$request2.UsePassive = $true
$response2 = $request2.GetResponse()
$reader2 = New-Object System.IO.StreamReader($response2.GetResponseStream())
$list2 = $reader2.ReadToEnd()
$reader2.Close()
$response2.Close()
Write-Output $list2
