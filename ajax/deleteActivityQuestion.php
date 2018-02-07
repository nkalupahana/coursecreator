<?php

session_start();
require "../database.php";
require "../tablefunctions.php";
$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;

$lessonID = $_SESSION["lessonid"];  
$section = escape($_GET["q"]);  

$conn = connect("coursecreator");

modifyDatabase($conn, "DELETE FROM $table" . "_activity WHERE section=$section AND id=$lessonID");

$questions = queryArray($conn, "SELECT * FROM $table" . "_activity WHERE id=$lessonID ORDER BY section", true);

$i = 1;
foreach ($questions as $question) {
    modifyDatabase($conn, "UPDATE $table" . "_activity SET section=$i WHERE uid=" . $question["uid"]);    
    $i++;
}

$questions = queryArray($conn, "SELECT * FROM $table" . "_activity WHERE id=$lessonID ORDER BY section", true);

$tableBody = returnActivityTable($questions);

echo json_encode($tableBody);
exit;

?>