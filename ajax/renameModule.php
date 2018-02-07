<?php

session_start();
require "../database.php";
require "../tablefunctions.php";

$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;

$description = escape($_GET["d"]);
$title = escape($_GET["t"]);
$module = escape($_GET["m"]);
$module = substr($module, 1);

$conn = connect("coursecreator");

$currentValue = queryRow($conn, "SELECT description AS descr FROM $table WHERE rowOrder=$module", "descr");

$pieces = explode("|", $currentValue);
$pieces[0] = $title;
$pieces[1] = $description;

$newValue = implode("|", $pieces);
modifyDatabase($conn, "UPDATE $table SET description='$newValue' WHERE rowOrder=$module");

$sqldec = "SELECT * FROM " . $table . " WHERE NOT EXISTS (SELECT * FROM " . $table . " t WHERE t.block = " . $table . ".block AND t.rowOrder = " . $table . ".rowOrder - 1) AND id < 9999 ORDER BY rowOrder";

$limitedFront = queryArray($conn, $sqldec, true);
$max = queryRow($conn, "SELECT MAX(id) AS id FROM $table WHERE id != 9999", "id");

$tableString = returnAccordian($conn, $limitedFront, $max, $table);

$tableString = str_replace("<", "&#60;", $tableString);
$tableString = str_replace(">", "&#62;", $tableString);

echo json_encode($tableString);
exit;
?>