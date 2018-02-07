<?php
session_start();
require "../database.php";
require "../tablefunctions.php";

$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;

$conn = connect("coursecreator");

$data = escape($_POST["textarea-main"]);

modifyDatabase($conn, "UPDATE createdCourses SET fc_description='$data' WHERE id=$id AND cid=$cid");


?>