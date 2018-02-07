<?php
session_start();
require "../database.php";
require "../tablefunctions.php";

$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;
$lessonID = $_SESSION["lessonid"];

$conn = connect("coursecreator");

$questions = queryArray($conn, "SELECT * FROM $table" . "_js WHERE id=$lessonID", true);

$timestamp = [];
$type = [];
$questionCode = [];
$questionSys = [];
$explanation = [];
$questionData = [];

foreach ($questions as $question) {
    array_push($timestamp, $question["timestamp"]);
    array_push($type, $question["type"]);
    array_push($questionCode, implode(", ", explode("|", $question["questionCode"])));
    array_push($questionData, $question["question"]);
    array_push($questionSys, implode(", ", explode("|", $question["answer"])));
    array_push($explanation, $question["explanation"]);
}

$src = "video/$table" . "/$lessonID" .  ".mp4";


$sendback = array(
	'timestamp' => $timestamp,
	'type' => $type,
	'questionCode' => $questionCode,
	'questionSys' => $questionSys,
	'explanation' => $explanation,
	'question' => $questionData,
	'src'=> $src
);

echo json_encode($sendback);
exit;

?>