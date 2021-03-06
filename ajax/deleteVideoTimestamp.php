<?php
session_start();
require "../database.php";
require "../tablefunctions.php";

$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;
$timestamp = escape($_GET["t"]);
$lessonID = $_SESSION["lessonid"];

$conn = connect("coursecreator");

modifyDatabase($conn, "DELETE FROM $table" . "_js WHERE id=$lessonID AND timestamp=$timestamp");

$questions = queryArray($conn, "SELECT * FROM $table" . "_js" . " WHERE id=$lessonID ORDER BY timestamp", true);
$tableContent = returnQuestionTable($questions);

$tableContent = str_replace("<", "&#60;", $tableContent);
$tableContent = str_replace(">", "&#62;", $tableContent);

echo json_encode($tableContent);
exit;

?>