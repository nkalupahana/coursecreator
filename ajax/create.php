<?php
session_start();
require "../database.php";
require "../tablefunctions.php";

$name = escape($_GET["q"]);
$lessonType = escape($_GET["e"]);
$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;

$conn = connect("coursecreator");

$statement = "SELECT MAX(id) AS max FROM $table WHERE id < 9999";
$max = queryRow($conn, $statement, "max");
$max = $max + 1;

if (isset($_GET["m"])) {
    $moduleStart = substr(escape($_GET["m"]), 1);
    
    $block = queryRow($conn, "SELECT block AS block FROM $table WHERE rowOrder=$moduleStart", "block");
    $maxRowOfBlock = queryRow($conn, "SELECT MAX(rowOrder) AS rowOrder FROM $table WHERE block=$block", "rowOrder");
    $allLessons = queryArray($conn, "SELECT * FROM $table WHERE rowOrder>$maxRowOfBlock", true);
    
    foreach ($allLessons as $lesson) {
        $lesson['rowOrder'] += 1;
        $statement = "UPDATE $table SET rowOrder=" . $lesson['rowOrder'] . " WHERE id=" . $lesson['id'];
        modifyDatabase($conn, $statement);
    }
    
    $maxRowOfBlock += 1;
    $statement = "INSERT INTO $table VALUES ($max, '$lessonType', '', 0, $block, '$name', $maxRowOfBlock)";
    modifyDatabase($conn, $statement);
    
    $sqldec = "SELECT * FROM " . $table . " WHERE NOT EXISTS (SELECT * FROM " . $table . " t WHERE t.block = " . $table . ".block AND t.rowOrder = " . $table . ".rowOrder - 1) AND id < 9999 ORDER BY rowOrder";

    $limitedFront = queryArray($conn, $sqldec, true);
    $max = queryRow($conn, "SELECT MAX(id) AS id FROM $table WHERE id < 9999", "id");
    
    $tableString = returnAccordian($conn, $limitedFront, $max, $table);
    
    $tableString = str_replace("<", "&#60;", $tableString);
    $tableString = str_replace(">", "&#62;", $tableString);
    
    echo json_encode($tableString);
    exit;
    
} else {
    $statement = "INSERT INTO $table VALUES ($max, '$lessonType', '', 0, $max, '$name', $max)";
    modifyDatabase($conn, $statement);
    
    $courseLessons = queryArray($conn, "SELECT * FROM $table ORDER BY rowOrder", true);
    $tableInsert = returnMainTable($courseLessons);
    
    $tableInsert = str_replace("<", "&#60;", $tableInsert);
    $tableInsert = str_replace(">", "&#62;", $tableInsert);
    
    echo json_encode($tableInsert);
    exit;

}

?>