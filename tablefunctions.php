<?php

function returnMainTable($courseLessons) {
    $tableInsert = "";

    foreach ($courseLessons as $lesson) {
        $entry = "<tr id='ID_HERE' class='respect_item'> <td>TITLE_HERE</td> <td>TYPE_HERE</td> <td><a href='INSERT_LINK_EDIT'>Edit</a> <a onclick='deleteRow(ROW_ID)'>Delete</a></td> </tr>";
    
        $entry = str_replace("ROW_ID", $lesson['id'], $entry);
        $entry = str_replace("ID_HERE", "t" . $lesson['id'], $entry);
        $entry = str_replace("TITLE_HERE", $lesson["description"], $entry);
    
        $linkInsert = "?lesson=" . $lesson['id'];
    
        if ($lesson["type"] == "v") {
            $entry = str_replace("TYPE_HERE", "Video", $entry);
            $entry = str_replace("INSERT_LINK_EDIT", "editvideo" . $linkInsert, $entry);
        } else if ($lesson["type"] == "a") {
            $entry = str_replace("TYPE_HERE", "Exercise", $entry);
            $entry = str_replace("INSERT_LINK_EDIT", "editactivity" . $linkInsert, $entry);
        } else {
            continue;
        }
    
        $entry = str_replace("INSERT_LINK_DELETE", "delete" . $linkInsert, $entry);
    
        $tableInsert = $tableInsert . $entry;
    }
    
    return $tableInsert;
}

function returnAccordian($conn, $limitedFront, $max, $table) {
    $blockIDs = [];
    
    foreach ($limitedFront as $front) {
        array_push($blockIDs, $front["rowOrder"]);
    }
    
    $tableString = "<ul id='moduleMachine' class='collapsible popout' data-collapsible='expandable'>";
    
    $i = 0;
    $exitBlock = false;
    
    foreach ($blockIDs as $blockid) {
        $card = "<li> <div class='collapsible-header'><p style='width: 100%; margin-top: 6px; margin-bottom: 6px;'><span>TITLE_HERE</span><a id='MODULE_ID_HERE' onclick='editModule(this)' style='float: right;'> Edit Module Title + Description </a></p></div> <div class='collapsible-body'>CONTENT_HERE <br><div style='text-align: center;'><a class='waves-effect waves-light btn' onclick='deleteModule()'>Delete Module</a> <a class='waves-effect waves-light btn video-btn' onclick='resetFieldsAndOpen(this)'>Create Video</a> <a class='waves-effect waves-light btn activity-btn' onclick='resetFieldsAndOpen(this)'>Create Activity</a></div></div></li>";
        $card = str_replace("MODULE_ID_HERE", "m" . $blockid, $card);
        
        if (!isset($blockIDs[$i + 1])) {
            array_push($blockIDs, ($max + 1));
            $exitBlock = true;
        }
        
        $limitedEntries = queryArray($conn, "SELECT * FROM $table WHERE rowOrder >= $blockid AND rowOrder <= " . ($blockIDs[$i + 1] - 1) . " ORDER BY rowOrder");
        
        $pieces = explode("|", $limitedEntries[0]["description"]);
        $limitedEntries[0]["description"] = $pieces[2];
        $card = str_replace("TITLE_HERE", $pieces[0], $card);
        
        $limitedTableBlock = returnMainTable($limitedEntries);
        $limitedTable = "<h3 style='margin-top: 0px;'>TITLE_HERE</h3><p>DESCRIPTION_HERE</p><div class='divider'></div><h5>Lessons in Module</h5><table><thead><tr><th>Lesson Name</th><th>Type</th><th>Actions</th></tr></thead><tbody id='a" . $blockid . "'>TABLE_HERE</tbody></table>";
        $limitedTable = str_replace("TITLE_HERE", $pieces[0], $limitedTable);
        $limitedTable = str_replace("DESCRIPTION_HERE", $pieces[1], $limitedTable);
        $limitedTable = str_replace("TITLE_HERE", $pieces[0], $limitedTable);
        $limitedTable = str_replace("TABLE_HERE", $limitedTableBlock, $limitedTable);
        
        $card = str_replace("CONTENT_HERE", $limitedTable, $card);
        $tableString = $tableString . $card;
        
        if ($exitBlock) {
            break;
        }
        
        $i += 1;
    }
    
    $tableString = $tableString . "</ul>";
    
    return $tableString;
}

function returnQuestionTable($questions) {
    $tableContent = "";
    $i = 0;
    foreach ($questions as $question) {
        $questionText = $question["question"];
        
        $timestamp = $question["timestamp"];
        $minutes = sprintf("%02d", floor(($timestamp / 60) % 60));
        $seconds = sprintf("%02d", $timestamp % 60);
        $timestamp_old = $timestamp;
        $timestamp = "$minutes:$seconds";
        
        $card = "<tr> <td> $questionText </td> <td> $timestamp </td> <td><a onclick='editQuestion(ROW_ID)' style='padding-right: 10px;'>Edit </a> &#32; &#32; <a onclick='editTimestamp(ROW_ID)'> Change Timestamp </a> &#32; &#32; <a onclick='deleteRow(ROW_ID)' style='padding-left: 10px;'> Delete </a></td> </tr>";
        
        $card = str_replace("ROW_ID", $timestamp_old, $card);
        
        $i += 1;
        $tableContent = $tableContent . $card;
    }
    
    return $tableContent;
    
}

function returnActivityTable($questions) {
    $tableBody = "";
    
    foreach ($questions as $question) {
        $tableBody .= "<tr class='respect_item' id='q" . $question["uid"] . "'><td>" . $question["question"] . "</td><td> <a onclick='editQuestion(" . $question["uid"] . ")'> Edit </a> &#32; &#32; <a onclick='deleteRow(" . $question["section"] . ")' style='padding-left: 10px;'> Delete </a></td></tr>";
    }
    
    return $tableBody;
}

    
?>