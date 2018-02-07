<?php

session_start();
require "../database.php";
require "../tablefunctions.php";

$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;

$moduleName = escape($_GET["m"]);
$videoName = escape($_GET["v"]);

$conn = connect("coursecreator");

$max = queryRow($conn, "SELECT MAX(id) AS id FROM $table WHERE id<9999", "id");
$max += 1;
$maxBlock = queryRow($conn, "SELECT MAX(block) AS block FROM $table WHERE id<9999", "block");
$maxBlock += 1;
$description = $moduleName . "|N/A|" . $videoName;

$statement = "INSERT INTO $table VALUES ($max, 'v', 'N/A', 0, $maxBlock, '$description', $max)";
modifyDatabase($conn, $statement);

$sqldec = "SELECT * FROM " . $table . " WHERE NOT EXISTS (SELECT * FROM " . $table . " t WHERE t.block = " . $table . ".block AND t.rowOrder = " . $table . ".rowOrder - 1) AND id < 9999 ORDER BY rowOrder";

$limitedFront = queryArray($conn, $sqldec, true);
$max = queryRow($conn, "SELECT MAX(id) AS id FROM $table WHERE id != 9999", "id");

$tableString = returnAccordian($conn, $limitedFront, $max, $table);

$tableString = str_replace("<", "&#60;", $tableString);
$tableString = str_replace(">", "&#62;", $tableString);

echo json_encode($tableString);
exit;

?>