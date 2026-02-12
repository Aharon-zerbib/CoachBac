<?php
$tmp = sys_get_temp_dir();
echo "Temp dir: $tmp
";
$file = $tmp . DIRECTORY_SEPARATOR . 'test_herd.txt';
if (@file_put_contents($file, 'hello')) {
    echo "SUCCESS: Wrote to $file
";
    unlink($file);
} else {
    echo "FAILURE: Cannot write to $tmp
";
}
