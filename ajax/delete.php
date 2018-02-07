<?php
session_start();
require "../database.php";
require "../tablefunctions.php";

$markedRow = escape($_GET["q"]);
$type = escape($_GET["t"]);
$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;

$conn = connect("coursecreator");

modifyDatabase($conn, "DELETE FROM $table" . "_js WHERE id=$markedRow");
modifyDatabase($conn, "DELETE FROM $table" . "_activity WHERE id=$markedRow");

$rowData = queryArray($conn, "SELECT * FROM $table WHERE id=$markedRow")[0];
$pieces = explode("|", $rowData["description"]);

if (count($pieces) == 3) {
    $newMark = $rowData["rowOrder"] + 1;
    $nextRowData = queryArray($conn, "SELECT * FROM $table WHERE rowOrder=$newMark", true)[0];
    
    if ($nextRowData == "") {
        echo json_encode("ERR_1");
        exit;
    }
    
    if ($nextRowData["block"] != $rowData["block"]) {
        echo json_encode("ERR_1");
        exit;
    } else {
        $pieces[2] = $nextRowData["description"];
        $newDescription = implode($pieces, "|");
        modifyDatabase($conn, "UPDATE $table SET description='$newDescription' WHERE rowOrder=$newMark");
    }
}


modifyDatabase($conn, "DELETE FROM $table WHERE id=$markedRow");

$newRows = queryArray($conn, "SELECT * FROM $table WHERE id<9999 ORDER BY rowOrder");

$i = 1;
foreach ($newRows as $row) {
    modifyDatabase($conn, "UPDATE $table SET rowOrder=$i WHERE id=" . $row['id']);
    $i += 1;
}

if ($type == "s") {

    $courseLessons = queryArray($conn, "SELECT * FROM $table ORDER BY rowOrder", true);
    $tableInsert = returnMainTable($courseLessons);
    
    $tableInsert = str_replace("<", "&#60;", $tableInsert);
    $tableInsert = str_replace(">", "&#62;", $tableInsert);
    
    echo json_encode($tableInsert);
    exit;
} else {
    $sqldec = "SELECT * FROM " . $table . " WHERE NOT EXISTS (SELECT * FROM " . $table . " t WHERE t.block = " . $table . ".block AND t.rowOrder = " . $table . ".rowOrder - 1) AND id < 9999 ORDER BY rowOrder";

    $limitedFront = queryArray($conn, $sqldec, true);
    $max = queryRow($conn, "SELECT MAX(id) AS id FROM $table WHERE id != 9999", "id");
    
    $tableString = returnAccordian($conn, $limitedFront, $max, $table);
    
    $tableString = str_replace("<", "&#60;", $tableString);
    $tableString = str_replace(">", "&#62;", $tableString);
    
    echo json_encode($tableString);
    exit;
}

?>