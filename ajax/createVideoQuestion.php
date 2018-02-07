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
$timestamp = escape($_POST["timestamp"]);
$override = escape($_POST["override"]);
$lessonID = $_SESSION["lessonid"];

$statement = "SELECT COUNT(*) AS count FROM $table" . "_js WHERE id=$lessonID AND timestamp=$timestamp";
$count = queryRow($conn, $statement, "count");
if ($count > 0 and $override == "f") {
    echo json_encode("ERR1");
    exit;
} else if ($count > 0 and $override == "t") {
    modifyDatabase($conn, "DELETE FROM $table" . "_js WHERE id=$lessonID AND timestamp=$timestamp");
}

if (escape($_POST["type"]) == "c") {
    $selected = substr(escape($_POST["selected"]), 2);
    $answers = explode("<->", escape($_POST["answers"]));
    $answers = implode("|", $answers);

    $statement = "INSERT INTO $table" . "_js VALUES ($lessonID, '$question', $timestamp, '$answers', '$selected', 'c', '$explanation')";
    modifyDatabase($conn, $statement);
    
} else if (escape($_POST["type"]) == "fib") {
    $answers = explode("<->", escape($_POST["answers"]));
    $answers = implode("|", $answers);
    
    $statement = "INSERT INTO $table" . "_js VALUES ($lessonID, '$question', $timestamp, 'N/A', '$answers', 'fib', '$explanation')";
    modifyDatabase($conn, $statement);
}

$questions = queryArray($conn, "SELECT * FROM $table" . "_js" . " WHERE id=$lessonID ORDER BY timestamp", true);
$tableContent = returnQuestionTable($questions);

$tableContent = str_replace("<", "&#60;", $tableContent);
$tableContent = str_replace(">", "&#62;", $tableContent);

echo json_encode($tableContent);
exit;

?>