<?php
session_start();
require "../database.php";
require "../tablefunctions.php";

$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;

$conn = connect("coursecreator");

$question = escape($_POST["question"]);
$explanation = escape($_POST["explanation"]);
$type = escape($_POST["type"]);
$graphic = escape($_POST["graphic"]);
$answer = escape($_POST["answer"]);
$options = escape($_POST["options"]);
$override = escape($_POST["override"]);
$lessonID = $_SESSION["lessonid"];

if ($question == "" || $explanation == "" || $type == "" || $answer == "" || $lessonID == "" || $answer == "undefined") {
    echo json_encode("ERR1");
    exit;
}

$uid = queryRow($conn, "SELECT MAX(uid) AS max FROM $table" . "_activity", "max");
$section = queryRow($conn, "SELECT MAX(section) AS section FROM $table" . "_activity WHERE id=$lessonID", "section");

$section = $section + 1;
$uid = $uid + 1;

$topic = "";
$inputcode = $options;

$questionArray = queryArray($conn, "SELECT * FROM $table" . "_activity WHERE id=$lessonID", true);
if ($questionArray != "") {
    $topic = $questionArray[0]["topic"];
} else {
    $topic = queryRow($conn, "SELECT description FROM $table WHERE id=$lessonID", "description");
}

if ($type == "fib") {
    $type = $type . "|" . $options;
    $inputcode = "";
}

if ($override != "") {
    $uid = (int) $override;
    $section = (int) queryRow($conn, "SELECT section AS section FROM $table" . "_activity WHERE uid=$uid", "section");
    modifyDatabase($conn, "DELETE FROM $table" . "_activity WHERE uid=$uid");
}

modifyDatabase($conn, "INSERT INTO $table" . "_activity VALUES ($uid, $lessonID, $section, '$question', '$graphic', '$inputcode', '$answer', '$topic', '$type', '$explanation')");

$questions = queryArray($conn, "SELECT * FROM $table" . "_activity WHERE id=$lessonID ORDER BY section", true);
$tableBody = returnActivityTable($questions);

echo json_encode($tableBody);
exit;

?>