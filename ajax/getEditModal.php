<?php
session_start();
require "../database.php";
require "../tablefunctions.php";

$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;
$timestamp = escape($_GET["t"]);
$lessonID = $_SESSION["lessonid"];

$conn = connect("coursecreator");

$cardStart = "<h4 class='getEditTimestamp' id='TIMESTAMP'>Edit Question</h4> <p>In-video questions are useful not only useful for keeping the student engaged and to check performance, but also to clear up common misconceptions and problems. Make sure you get your timing right - don't just put them all at the end (although you can group a few at the end). Remember, don't go too hard - that's for activities.</p> <div class='row'> <div class='input-field col s12'> <input value='QUESTION_HERE' placeholder='Example question: How is dramatic irony different from irony? (displayed near the beginning of a lesson about dramatic irony)' id='questionText' type='text' class='validate'> <label for='questionText'>Question</label> </div> </div> <p>Question Type:</p> <p> <input onclick='showChoice(0)' MC_IS_CHECKED name='qt' type='radio' id='qt1' /> <label for='qt1'>Multiple Choice (radio buttons)</label> </p> <p> <input onclick='showChoice(1)' FIB_IS_CHECKED name='qt' type='radio' id='qt2' /> <label for='qt2'>Short (1-3) Word Answer (text box)</label> </p> <div class='divider'></div>";

$entry = queryArray($conn, "SELECT * FROM $table" . "_js WHERE id=$lessonID AND timestamp=$timestamp")[0];

$cardStart = str_replace("QUESTION_HERE", $entry["question"], $cardStart);
$cardStart = str_replace("TIMESTAMP", $entry["timestamp"], $cardStart);

$cardStart = $cardStart . "<div DISPLAY_PREF_FIB id='fibbox'> <p>Note: Answer checking is case and space-insensitive.</p> <div class='row' style='margin-bottom: 0px;'> <div class='col s12'> <div style='margin-bottom: 5px;' class='chips chips-initial'></div> </div> </div> </div> <div DISPLAY_PREF_MC id='mcbox'> <p>Click on the answer choice text to edit it, and select the correct answer by clicking on the bubble on the left.</p> <input class='browser-default' 1_CHECKED name='qgroup' type='radio' id='qa1' style='opacity:1; pointer-events: auto; position: relative;' /> <p id='q1' contentEditable style='display: inline; padding-left: 6px;'>MC_TEXT_1</p><br> <input class='browser-default' name='qgroup' 2_CHECKED type='radio' id='qa2' style='opacity:1; pointer-events: auto; position: relative;' /> <p id='q2' contentEditable style='display: inline; padding-left: 6px;'>MC_TEXT_2</p><br> <input 3_CHECKED class='browser-default' name='qgroup' type='radio' id='qa3' style='opacity:1; pointer-events: auto; position: relative;' /> <p id='q3' contentEditable style='display: inline; padding-left: 6px;'>MC_TEXT_3</p><br> <input class='browser-default' name='qgroup' 4_CHECKED type='radio' id='qa4' style='opacity:1; pointer-events: auto; position: relative;' /> <p id='q4' contentEditable style='display: inline; padding-left: 6px;'>MC_TEXT_4</p><br> </div><br>";

$chips = [];

if ($entry["type"] == "c") {
    $cardStart = str_replace("FIB_IS_CHECKED", "", $cardStart);
    $cardStart = str_replace("MC_IS_CHECKED", "checked", $cardStart);
    
    $cardStart = str_replace("DISPLAY_PREF_FIB", "style='display: none;'", $cardStart);
    $cardStart = str_replace("DISPLAY_PREF_MC", "", $cardStart);
    
    $answers = explode("|", $entry["questionCode"]);
    $cardStart = str_replace("MC_TEXT_1", $answers[0], $cardStart);
    $cardStart = str_replace("MC_TEXT_2", $answers[1], $cardStart);
    $cardStart = str_replace("MC_TEXT_3", $answers[2], $cardStart);
    $cardStart = str_replace("MC_TEXT_4", $answers[3], $cardStart);
    
    $checkedArr = ["", "", "", ""];
    
    $checkedArr[$entry["answer"]-1] = "checked";
    
    $i = 1;
    foreach ($checkedArr as $checkedItem) {
        $cardStart = str_replace($i . "_CHECKED", $checkedArr[$i-1], $cardStart);
        $i += 1;
    }
    
} else {
    $cardStart = str_replace("FIB_IS_CHECKED", "checked", $cardStart);
    $cardStart = str_replace("MC_IS_CHECKED", "", $cardStart);
    
    $cardStart = str_replace("DISPLAY_PREF_FIB", "", $cardStart);
    $cardStart = str_replace("DISPLAY_PREF_MC", "style='display: none;'", $cardStart);
    
    $cardStart = str_replace("MC_TEXT_1", "Click to edit", $cardStart);
    $cardStart = str_replace("MC_TEXT_2", "Click to edit", $cardStart);
    $cardStart = str_replace("MC_TEXT_3", "Click to edit", $cardStart);
    $cardStart = str_replace("MC_TEXT_4", "Click to edit", $cardStart);
    
    $answers = explode("|", $entry["answer"]);
    
    foreach ($answers as $answer) {
        array_push($chips, array('tag'=> $answer));
    }
    
    $checkedArr = ["", "", "", ""];
    
    foreach ($checkedArr as $_checkedItem) {
        $cardStart = str_replace($i . "_CHECKED", "", $cardStart);
        $i += 1;
    }
    
}

$cardStart = $cardStart . "<div id='xbox'> <div class='row'> <div class='input-field col s12' style='margin-top: 0px;'> <input value='EXPLANATION_HERE' id='qexplanation' type='text' class='validate'> <label id='qexplanation' for='questionText'>Answer Explanation</label> </div> </div> </div>";


$cardStart = str_replace("EXPLANATION_HERE", $entry["explanation"], $cardStart);

$cardStart = str_replace("<", "&#60;", $cardStart);
$cardStart = str_replace(">", "&#62;", $cardStart);

$sendback = array(
	'card' => $cardStart,
	'chips' => $chips
);

echo json_encode($sendback);
exit;


?>