<?php

function writeFile($name, $location, $data, $mode = "w") {
    mkdir($location, 0777, true);
    
    if (!$handle = fopen($location . $name, $mode)) {
         die("Creation/Opening of file $name failed!");
    }
    
    foreach ($data as $line) {
        if (fwrite($handle, $line . "\n") === FALSE) {
            die("Writing to file $name failed!");
        }
    }
    
    fclose($handle);
    return;
}

?>