<?php
session_start();
require "../database.php";
require "../tablefunctions.php";

$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;
$lessonID = $_SESSION["lessonid"];

$conn = connect("coursecreator");

$newName = trim(escape($_GET["n"]));
$newTopic = trim(escape($_GET["t"]));

$insert;

if ($newName != "") {
    $oldName = queryRow($conn, "SELECT description AS description FROM $table WHERE id=$lessonID", "description");
    $namePieces = explode("|", $oldName);
    if (count($namePieces) == 3) {
        $namePieces[2] = $newName;
        $setNewName = implode("|", $namePieces);
    }

    modifyDatabase($conn, "UPDATE $table SET description='$setNewName' WHERE id=$lessonID");

}

if ($newTopic != "" && !is_null($newName)) {
    modifyDatabase($conn, "UPDATE $table" . "_activity SET topic='$newTopic' WHERE id=$lessonID");
    
    $topic = htmlspecialchars(stripslashes(queryArray($conn, "SELECT * FROM $table" . "_activity WHERE id=$lessonID", true)[0]["topic"]), ENT_QUOTES);
    $insert = "&#60;div class='row'&#62; &#60;div class='input-field col s12'&#62; &#60;input value='$topic' id='topic' type='text' class='validate'&#62; &#60;label for='topic'&#62;Lesson Displayed Name (e.g., HTML Basics Quiz)&#60;/label&#62; &#60;/div&#62; &#60;/div&#62;";
    
}


$sendback = array(
	'lesson' => stripslashes($newName),
	'topic'=> $insert
);

echo json_encode($sendback);
exit;




?>