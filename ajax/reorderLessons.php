<?php
session_start();
require "../database.php";
require "../tablefunctions.php";

$sorted = $_GET["s"];
$neworder = $_GET["q"];
$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;

$conn = connect("coursecreator");

$courseLessons = queryArray($conn, "SELECT * FROM $table ORDER BY id", true);

if ($neworder == "") {
    return;
}

if ($sorted == "u") {
    $neworder = explode("<|>", $neworder);
    $rowArray = [];
    $moduleArray = [];
    
    $module = 0;
    $toAdd = "";
    foreach ($neworder as $lesson) {
        $part1 = substr($lesson, 0, 1);
        $part2 = substr($lesson, 1);
        
        if ($part1 != "t" && !is_numeric($part2)) {
            $toAdd = $lesson;
            $module += 1;
            continue;
        }
        
        $i = (int) substr($lesson,1);
        
        if ($toAdd != "") {
            $pieces = explode("|", $courseLessons[$i-1]["description"]);
            $option = "";
            
            if (count($pieces) == 3) {
                $option = $pieces[2];
            } else {
                $option = $courseLessons[$i-1]["description"];
            }
            
            $courseLessons[$i-1]["description"] = $toAdd . "|" . $option;
            $toAdd = "";
        } else {
            $pieces = explode("|", $courseLessons[$i-1]["description"]);

            if (count($pieces) == 3) {
                $courseLessons[$i-1]["description"] = $pieces[2];
            }
        }
        
        array_push($rowArray, $courseLessons[$i - 1]);
        array_push($moduleArray, $module);
    }
        
    $i = 1;
    foreach ($rowArray as $lesson) {
        $statement = "UPDATE $table SET rowOrder=" . $i . ", block=" . $moduleArray[$i - 1] . ", description='" . unsafe_escape($lesson['description']) . "' WHERE id=" . $lesson['id'];
        modifyDatabase($conn, $statement);
        $i += 1;
    }
    
    $sqldec = "SELECT * FROM " . $table . " WHERE NOT EXISTS (SELECT * FROM " . $table . " t WHERE t.block = " . $table . ".block AND t.rowOrder = " . $table . ".rowOrder - 1) AND id < 9999 ORDER BY rowOrder";
    
    $limitedFront = queryArray($conn, $sqldec, true);
    $max = queryRow($conn, "SELECT MAX(id) AS id FROM $table WHERE id != 9999", "id");
    
    $tableString = returnAccordian($conn, $limitedFront, $max, $table);
    
    $tableString = str_replace("<", "&#60;", $tableString);
    $tableString = str_replace(">", "&#62;", $tableString);
    
    echo json_encode($tableString);
    exit;
    
    
} else {
    $newarray = [];

    $neworder = explode(",", $neworder);
    
    foreach ($neworder as $index) {
        $i = (int) substr($index,1);
        array_push($newarray, $courseLessons[$i - 1]);
    }


    $i = 1;
    foreach ($newarray as $lesson) {
        $statement = "UPDATE $table SET rowOrder=$i WHERE id=" . $lesson['id'];
        modifyDatabase($conn, $statement);
        $i += 1;
    }

    $courseLessons = queryArray($conn, "SELECT * FROM $table ORDER BY rowOrder", true);

    $tableInsert = returnMainTable($courseLessons);

    $tableInsert = str_replace("<", "&#60;", $tableInsert);
    $tableInsert = str_replace(">", "&#62;", $tableInsert);

    echo json_encode($tableInsert);
    exit;

}

?>>