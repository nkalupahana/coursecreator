<?php
session_start();
$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;

$lessonID = $_SESSION["lessonid"];

$ds = DIRECTORY_SEPARATOR;

$storeFolder = 'video';

if (!empty($_FILES)) {

    $tempFile = $_FILES['file']['tmp_name'];
    $targetPath = dirname( __FILE__ ) . $ds. $storeFolder . $ds . $table . $ds;
    
    $pieces = explode(".", $_FILES['file']['name']);
    $fileName = $lessonID . "." . end($pieces);
    $targetFile =  $targetPath . $fileName;
    
    mkdir($targetPath, 0777, true);
    exec("rm $targetPath" . $lessonID . ".*");
    move_uploaded_file($tempFile,$targetFile);
}

?>