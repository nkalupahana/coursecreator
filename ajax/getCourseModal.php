<?php

session_start();

$info = "<h4>Create a course</h4> <form id='createform' action='createcourse.php' method='post'> <div class='row'> <div class='input-field col s12'> <input name='cname' id='cname' type='text'> <label for='cname'>Course Name</label> </div> </div> <div class='row'> <div class='input-field col s12'> <input name='cdesc' placeholder='Example: HTML is a tag-based language that gives web browsers the general layout of a website, as well as non-changing (static) content.' id='cdesc' type='text'> <label for='cdesc'>Short Course Description (1-2 short sentences)</label> </div> </div> <div class='row'> <div class='input-field col s12'> <textarea placeholder='Example: Literary devices make stories more interesting, and help create vivid images in readers\' minds. From basic imagery to dramatic irony, this course will teach you the ins and outs of similes, metaphors, irony, coincidence, imagery, and more, and will help you become a better writer.' name='clongdesc' id='clongdesc' class='materialize-textarea'></textarea> <label for='clongdesc'>Long Course Description (2-6 detailed sentences, summarize the topic)</label> </div> </div> <p><b>IMPORTANT: Course Structure.</b> You can create an unstructured or structured course. A structured course forces students to go from topic to topic in the order you set. They can't skip around. This is good for topics like programming, where it's important that students don't skip concepts. An unstructured course allows you to create topic modules, composed of videos and exercises on a smaller topic. Students can skip around between topics, but can't skip around within topics (which are usually a few exercises and videos). A good course to make unstrucutured could be one on literary devices. A student doesn't need to learn literary devices in any particular order. <b>Your entire course structure depends on this. It can NOT be changed later. Choose carefully.</b></p> <p style='margin-bottom: 5px;'> <input class='structure' name='structure' type='radio' id='s1' value='1' /> <label for='s1'>Structured</label> </p> <p style='margin-top: 5px;'> <input name='structure' class='structure' value='0' type='radio' id='s2' /> <label for='s2'>Unstructured</label> </p> <br> <p><b>Dependencies:</b> If your course is dependent on another course (for example, to learn how to style a website (CSS programming language), you need to first learn how to create a website (HTML programming language)), please select the dependency below. Courses will only show up here if they have been deployed and approved. Don't worry, you can add dependencies later.</p>";

$checkboxCode = "<p> <input type='checkbox' name='VALUE_HERE' id='VALUE_HERE' /> <label for='VALUE_HERE'>DATA_HERE</label> </p>";

session_start();
require "../database.php";

$conn = connect("coursecreator");

$courses = queryArray($conn, "SELECT * FROM createdCourses WHERE deployed=1", true);


$checkboxIDs = [];
$addedCheckboxCode = "";

foreach ($courses as $data) {
    $name = queryRow($conn, "SELECT name AS name FROM main WHERE id=" . $data["id"], "name");
    
    $code = str_replace("DATA_HERE", $data["name"] . " - " . "Made by " . $name, $checkboxCode);
    $code = str_replace("VALUE_HERE", "c" . $data["id"] . "-" . $data["cid"], $code);
    
    array_push($checkboxIDs, "c" . $data["id"] . "-" . $data["cid"]);
    
    $addedCheckboxCode = $addedCheckboxCode . $code;
    
}

$_SESSION["dependencyCount"] = $checkboxIDs;

$info = $info . $addedCheckboxCode . "</form>";

echo json_encode($info);


?>