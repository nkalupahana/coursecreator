<?php
session_start();
require "../database.php";
require "../tablefunctions.php";

$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;
$lessonID = $_SESSION["lessonid"];

$conn = connect("coursecreator");

$questions = queryArray($conn, "SELECT * FROM $table" . "_activity WHERE id=$lessonID ORDER BY section", true);
$topic;

if ($questions != "") {
    $topic = htmlspecialchars(stripslashes(queryArray($conn, "SELECT * FROM $table" . "_activity WHERE id=$lessonID", true)[0]["topic"]), ENT_QUOTES);
    echo json_encode("&#60;div class='row'&#62; &#60;div class='input-field col s12'&#62; &#60;input value='$topic' id='topic' type='text' class='validate'&#62; &#60;label for='topic'&#62;Lesson Displayed Name (e.g., HTML Basics Quiz)&#60;/label&#62; &#60;/div&#62; &#60;/div&#62;");
    exit;
} else {
    echo json_encode("");
    exit;
}


?>