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

$order = explode(",", escape($_GET["q"]));

$i = 1;
foreach ($order as $item) {
    modifyDatabase($conn, "UPDATE $table" . "_activity SET section=$i WHERE uid=$item AND id=$lessonID");
    $i++;
}

$questions = queryArray($conn, "SELECT * FROM $table" . "_activity WHERE id=$lessonID ORDER BY section", true);

$tableBody = returnActivityTable($questions);

echo json_encode($tableBody);
exit;


    
?>