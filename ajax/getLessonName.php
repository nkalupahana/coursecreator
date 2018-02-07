<?php
session_start();
require "../database.php";
require "../tablefunctions.php";

$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;

$conn = connect("coursecreator");

$lessonID = $_SESSION["lessonid"];

$nameOfLesson = queryRow($conn, "SELECT description AS description FROM $table WHERE id=$lessonID", "description");

echo stripslashes($nameOfLesson);
exit;

?>