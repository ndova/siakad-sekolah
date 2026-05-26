$ftpHost = 'ptpjml.com'
$ftpUser = 'ptpjmlco'
$ftpPass = 'Xo$c458^7Y~y'

function Upload-FtpFile($localPath, $remotePath) {
    $request = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost/$remotePath")
    $request.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $request.Credentials = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)
    $request.UsePassive = $true
    $content = [System.IO.File]::ReadAllBytes($localPath)
    $request.ContentLength = $content.Length
    $stream = $request.GetRequestStream()
    $stream.Write($content, 0, $content.Length)
    $stream.Close()
    $response = $request.GetResponse()
    $response.Close()
    return "UPLOADED: $remotePath"
}

# Buat file debug PHP
$debugCode = @'
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Debug ptpjml.com - 500 Error</h2>";

echo "<h3>1. PHP Version</h3>";
echo "PHP Version: " . phpversion() . "<br>";

echo "<h3>2. File Structure</h3>";
echo "vendor/autoload.php exists: " . (file_exists(__DIR__.'/vendor/autoload.php') ? 'YES' : 'NO') . "<br>";
echo "public/index.php exists: " . (file_exists(__DIR__.'/public/index.php') ? 'YES' : 'NO') . "<br>";
echo "bootstrap/app.php exists: " . (file_exists(__DIR__.'/bootstrap/app.php') ? 'YES' : 'NO') . "<br>";

echo "<h3>3. Test Require vendor/autoload.php</h3>";
try {
    require __DIR__.'/vendor/autoload.php';
    echo "SUCCESS: autoload.php loaded<br>";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "<br>";
}

echo "<h3>4. Check if env() exists</h3>";
echo "function_exists('env'): " . (function_exists('env') ? 'YES' : 'NO') . "<br>";

if (function_exists('env')) {
    echo "env() test: " . env('APP_NAME', 'FALLBACK') . "<br>";
}

echo "<h3>5. Registered Autoload Files</h3>";
$autoloadFiles = require __DIR__.'/vendor/composer/autoload_files.php';
foreach ($autoloadFiles as $file) {
    echo "- $file<br>";
}

echo "<h3>6. Try Bootstrap App</h3>";
try {
    $app = require_once __DIR__.'/bootstrap/app.php';
    echo "SUCCESS: App bootstrapped, class: " . get_class($app) . "<br>";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "<h3>7. Check Error Log (last 5 lines)</h3>";
$logFile = __DIR__.'/error_log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $last5 = array_slice($lines, -5);
    foreach ($last5 as $line) {
        echo htmlspecialchars($line) . "<br>";
    }
}
'@

$debugFile = 'c:\temp_ptpjml_debug.php'
[System.IO.File]::WriteAllText($debugFile, $debugCode)

$result = Upload-FtpFile $debugFile 'public_html/debug_check.php'
Write-Output $result
Write-Output "`nFile debug telah diupload. Akses: https://ptpjml.com/debug_check.php"
