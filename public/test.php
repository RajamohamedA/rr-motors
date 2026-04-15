<?php

echo "<h2>Apache PHP Test</h2>";

echo "SAPI: " . php_sapi_name() . "<br>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Loaded php.ini: " . php_ini_loaded_file() . "<br>";
echo "Temp Directory: " . sys_get_temp_dir() . "<br>";

$file = tempnam(sys_get_temp_dir(), 'rr_');

if ($file === false) {
    echo "<span style='color:red'>tempnam() FAILED</span>";
} else {
    echo "<span style='color:green'>tempnam() OK</span><br>";
    echo "Created: " . $file;
    unlink($file);
}