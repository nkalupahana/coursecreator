<?php
session_start();
require "../database.php";
require "../tablefunctions.php";
$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;

$lessonID = $_SESSION["lessonid"];  
$questionID = escape($_GET["q"]);  

$conn = connect("coursecreator");

$questionData = queryArray($conn, "SELECT * FROM $table" . "_activity WHERE uid=$questionID")[0];

$question = $questionData["question"];
$graphic = $questionData["graphic"];
$explanation = $questionData["explanation"];


$modal = "<h4>Add a Question</h4> <p>Questions in activities are much more powerful. They can have customizable graphics, more question customization, and more.</p> <div class='row' style='margin-bottom: 8px;'> <div class='input-field col s12'> <input value='$question' id='questionInput' type='text' style='margin-bottom: 0px;' class='validate'> <label for='questionInput'>Question</label> </div> </div> <div class='row'> <div class='input-field col s12'> <input value='$graphic' id='graphicHtml' type='text' class='validate'> <label for='graphicHtml'>HTML of graphic (if applicable)</label> </div> </div> <p>Question Type:</p> <p> <input onclick='showChoice(0)' CHECK_MC name='qt' type='radio' id='qt1' /> <label for='qt1'>Multiple Choice (radio buttons)</label> </p> <p> <input onclick='showChoice(1)' CHECK_FIB name='qt' type='radio' id='qt2' /> <label for='qt2'>Short (1-3) Word Answer (text box)</label> </p> <div id='mcbox' style='DISPLAY_MC_BOX'> <div class='divider'></div> <p>Type in your multiple choice options below, pressing 'enter' after each one. Then, click on the correct answer.</p> <div class='row' style='margin-bottom: 0px;'> <div class='col s12'> <div style='margin-bottom: 5px;' class='chips chips-initial-1'></div> </div> </div> </div> <div id='fibbox' style='DISPLAY_FIB_BOX'> <div class='divider'></div> <br> <div class='row' style='margin-bottom: 0px;'> <div class='col s12'> <div style='margin-bottom: 5px;' class='chips chips-initial-2'></div> </div> </div> <div class='row'> <div class='col s12'> <p> <input type='checkbox' IS_CASE_SENSITIVE id='isCase' /> <label for='isCase'>Case-Sensitive?</label> </p> </div> </div> </div> <div class='row' style='' id='explanation-box'><div class='input-field col s12'><input id='explanation' value='$explanation' type='text' class='validate'><label for='explanation'>Answer Explanation</label></div></div><input type='text' style='display: none;' name='override-old' value='$questionID'>";

$chips = [];

if (explode("|", $questionData["type"])[0] == "c") {
    $modal = str_replace("CHECK_MC", "checked", $modal);
    $modal = str_replace("CHECK_FIB", "", $modal);
    $modal = str_replace("DISPLAY_MC_BOX", "", $modal);
    $modal = str_replace("DISPLAY_FIB_BOX", "display: none;", $modal);
    $modal = str_replace("IS_CASE_SENSISTIVE", "", $modal);
    
    $answers = explode("|", $questionData["inputcode"]);

    foreach ($answers as $answer) {
        array_push($chips, array('tag'=> $answer));
    }
    
    $modal = str_replace("<", "&#60;", $modal);
    $modal = str_replace(">", "&#62;", $modal);
    
    $sendback = array(
    	'card' => $modal,
        'type' => "c",
    	'chips' => $chips,
        'correct' => $questionData["answer"]
    );
    
    echo json_encode($sendback);
    exit;
    
} else {
    $modal = str_replace("CHECK_MC", "", $modal);
    $modal = str_replace("CHECK_FIB", "checked", $modal);
    $modal = str_replace("DISPLAY_MC_BOX", "display: none;", $modal);
    $modal = str_replace("DISPLAY_FIB_BOX", "", $modal);
    if (explode("|", $questionData["type"])[1] == "i") {
        $modal = str_replace("IS_CASE_SENSISTIVE", "", $modal);
    } else {
        $modal = str_replace("IS_CASE_SENSISTIVE", "checked", $modal);
    }
    
    $answers = explode("|", $questionData["answer"]);

    foreach ($answers as $answer) {
        array_push($chips, array('tag'=> $answer));
    }
    
    $modal = str_replace("<", "&#60;", $modal);
    $modal = str_replace(">", "&#62;", $modal);
    
    $sendback = array(
    	'card' => $modal,
        'type' => "fib",
    	'chips' => $chips
    );
    
    echo json_encode($sendback);
    exit;
    
}




?>