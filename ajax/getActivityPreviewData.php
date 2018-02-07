<?php
session_start();
require "../database.php";
require "../tablefunctions.php";

$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;
$lessonID = $_SESSION["lessonid"];

$conn = connect("coursecreator");

$questions = queryArray($conn, "SELECT * FROM $table" . "_activity WHERE id=$lessonID ORDER BY section", true);

$question_send = [];
$graphic = [];
$topic = [];
$explanation = [];
$code = [];
$type = [];
$answer = [];

foreach ($questions as $question) {
    array_push($question_send, $question["question"]);
    array_push($topic, $question["topic"]);
    $_graphic = $question["graphic"];
    $_graphic = str_replace("<", "&#60;", $_graphic);
    $_graphic = str_replace(">", "&#62;", $_graphic);
    
    array_push($graphic, $_graphic);
    array_push($explanation, $question["explanation"]);
    array_push($type, $question["type"]);
    
    $inputcode = "";
    
	if ($question["type"] == "c") {
		$modelAnswer = " <p> <input name='maingroup' value='LABEL_CONTENT' type='radio' id='testINPUT_NUMBER' /> <label for='testINPUT_NUMBER'>LABEL_CONTENT</label> </p>";
		$_answers = explode('|', $question["inputcode"]);
		$_code = [];
		$i = 0;
		foreach($_answers as $_answer) {
			$newAnswer = str_replace("LABEL_CONTENT", $_answer, $modelAnswer);
			$newAnswer = str_replace("INPUT_NUMBER", $i, $newAnswer);
			array_push($_code, $newAnswer);
			$i = $i + 1;
		}

		$inputcode = join("", $_code);
		
		 array_push($answer, str_replace("|", ", ", $question["answer"]));
	} else if (strstr($question["type"], 'fib') !== false) {
        $inputcode = "<div class='input-field'> <input id='maingroup' type='text' class='validate' autocomplete='off'> <label for='maingroup'>Click here to enter your answer</label> </div>";
        
        $sensitive = "";
        
        if (explode('|', $question["type"])[1] == 's') {
            $sensitive = " | Case-Sensitive";
        } else {
            $sensitive = " | Case-Insensitive";
        }
        
	    array_push($answer, str_replace("|", ", ", $question["answer"]) . $sensitive);
	    
	}
    
    array_push($code, $inputcode);
    array_push($type, $question["type"]);
}

$sendback = array(
	'question' => $question_send,
	'graphic' => $graphic,
	'topic' => $topic,
	'explanation' => $explanation,
	'code' => $code,
	'type' => $type,
	'answer'=> $answer,
    'test' => $graphic[0]
);

echo json_encode($sendback);
exit;

?>