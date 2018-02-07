<?php
session_start();
require "database.php";
require "tablefunctions.php";
$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;

$lessonID = escape($_GET["lesson"]);
$_SESSION["lessonid"] = $lessonID;

$conn = connect("coursecreator");

$ds = DIRECTORY_SEPARATOR;
$storeFolder = 'video';
$targetPath = dirname( __FILE__ ) . $ds. $storeFolder . $ds . $table . $ds . $lessonID . ".mp4";

$videoExists = file_exists($targetPath);

$questions = queryArray($conn, "SELECT * FROM $table" . "_js" . " WHERE id=$lessonID ORDER BY timestamp", true);

$tableContent = returnQuestionTable($questions);

$nameOfLesson = queryRow($conn, "SELECT description AS description FROM $table WHERE id=$lessonID", "description");
$lessonPieces = explode("|", $nameOfLesson);
if (count($lessonPieces) == 3) {
    $nameOfLesson = $lessonPieces[2];
}

?>
<html>

<head>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css">
    <script src="js/sortable.min.js"></script>
    <script src="js/dropzone.js"></script>
    <link type="text/css" rel="stylesheet" href="css/dropzone.css">
    <style>
        .sortable-ghost {
            opacity: .6;
        }
        
        .container {
            margin: 0 auto;
            max-width: none !important;
            width: 95% !important
        }
        
        @media only screen and (min-width: 601px) {
            .container {
                width: 92% !important
            }
        }
        
        @media only screen and (min-width: 993px) {
            .container {
                width: 90% !important
            }
        }
        
        .row .col .chips .input {
            width: 100% !important;
        }
    </style>
</head>

<body>
    <br>
    <div class="container">
        <div class="row">
            <div class="col l6 offset-l3 s12">
                <?php if($videoExists) : ?>
                <ul class="tabs">
                    <li class="tab col s4" onclick="clearPreview()"><a href="#upload">Upload Video</a></li>
                    <li class="tab col s4" onclick="clearPreview()" id="addQuestionTab"><a class="active" href="#add">Add Questions</a></li>
                    <li class="tab col s4" id="previewTab" onclick="loadPreview()"><a href="#preview">Preview</a></li>
                </ul>
                <?php else : ?>
                <ul class="tabs">
                    <li class="tab col s4" onclick="clearPreview()"><a class="active" href="#upload">Upload Video</a></li>
                    <li class="tab col s4 disabled" id="addQuestionTab" onclick="clearPreview()"><a href="#add">Add Questions</a></li>
                    <li class="tab col s4 disabled" id="previewTab" onclick="loadPreview()"><a href="#preview">Preview</a></li>
                </ul>
                <?php endif; ?>
            </div>

            <div id="upload" class="col s12">
                <br>
                <center>
                    <h3>Upload</h3>
                </center>
                <br>
                <form action="upload.php" class="dropzone" id="dropzone" style="font-family: 'Raleway', sans-serif;font-size: 20px;">
                </form>
                <p>If you have already uploaded a video, but would like to replace it, just re-upload a new one. It will be automatically replaced.</p>
                <br>
                <br>
                <center>
                    <h3>Update Lesson Data</h3>
                </center>
                <div class="row">
                    <div class="input-field col s12">
                        <input value='<?php echo htmlspecialchars(stripslashes(escape($nameOfLesson)), ENT_QUOTES); ?>' id="lessonName" type="text" class="validate">
                        <label for="lessonName">Lesson Name</label>
                    </div>
                </div>
                <a class="waves-effect waves-light btn" onclick="updateName()">Update</a>
            </div>
            <div id="add" class="col s12">
                <br>
                <center>
                    <h3>Add Questions</h3>
                </center>
                <br>
                <div class="container">
                    <center>
                        <video id="lessonVideo" class="responsive-video" controls>
                      <source src="video/<?php echo $table . "/" . $lessonID ?>.mp4" type="video/mp4">
                    </video>
                        <br><br>
                        <p>Play your video. When you come to a spot where you want to insert a question, pause the video and click on the button below. A 10-minute video will generally have around 7 questions.</p>
                        <br>
                        <a onclick="createQuestion()" class="waves-effect waves-light btn">Add Question Here</a>
                    </center>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Timestamp</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody id="questionT">
                        <?php echo $tableContent; ?>
                    </tbody>
                </table>
                <br><br><br><br><br>
            </div>
            <div id="preview" class="col s12">
                <br>

                <center>
                    <h3>Preview</h3>
                </center>
                <br>
                <div class="container">
                    <center>
                        <video id="previewVideo" class="responsive-video" controls>
                            <source src="video/<?php echo $table . "/" . $lessonID ?>.mp4" type="video/mp4">
                    </video>
                    </center>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Structure -->
    <div id="createQuest" class="modal modal-fixed-footer">
        <div class="modal-content" id="createContent">

        </div>
        <div class="modal-footer">
            <a href="#!" class="modal-action modal-close waves-effect waves-red btn-flat ">Cancel</a>
            <a id="saveQuestionBtn" onclick="saveQuestion()" class="waves-effect waves-green btn-flat ">Save</a>
        </div>
    </div>

    <form id="mcForm" action="ajax/createVideoQuestion.php" method="post" style="display: none; opacity: 0; pointer-events: none;">
        <input type="text" name="type">
        <input type="text" name="question">
        <input type="text" name="explanation">
        <input type="text" name="selected">
        <input type="text" name="answers">
        <input type="text" name="timestamp">
        <input type="text" name="override">
    </form>

    <form id="fibForm" action="ajax/createVideoQuestion.php" method="post" style="display: none; opacity: 0; pointer-events: none;">
        <input type="text" name="type">
        <input type="text" name="question">
        <input type="text" name="explanation">
        <input type="text" name="answers">
        <input type="text" name="timestamp">
        <input type="text" name="override">
    </form>

    
    <a class="btn-floating btn-large waves-effect waves-light red" style="position: absolute; top: 25px; left: 20px;" onclick="navBack()"><i class="material-icons">arrow_back</i></a>
    
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/materialize.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js" integrity="sha384-FzT3vTVGXqf7wRfy8k4BiyzvbNfeYjK+frTVqZeNDFl8woCbF0CYG6g2fMEFFo/i" crossorigin="anonymous"></script>

    <script>
        function createQuestion() {
            var video = document.getElementById("lessonVideo");
            video.pause();
            
            if (video.currentTime == 0) {
                Materialize.toast("You can't have a question at 0 seconds! If it has to be at the beginning, put it at one second.", 5000);
                return;
            }
            
            $("#createQuest").modal();
            document.getElementById("createContent").innerHTML = "<h4>Create a Question</h4> <p>In-video questions are useful not only useful for keeping the student engaged and to check performance, but also to clear up common misconceptions and problems. Make sure you get your timing right - don't just put them all at the end (although you can group a few at the end). Remember, don't go too hard - that's for activities.</p> <div class='row'> <div class='input-field col s12'> <input placeholder='Example question: How is dramatic irony different from irony? (displayed near the beginning of a lesson about dramatic irony)' id='questionText' type='text' class='validate'> <label for='questionText'>Question</label> </div> </div> <p>Question Type:</p> <p> <input onclick='showChoice(0)' name='qt' type='radio' id='qt1' /> <label for='qt1'>Multiple Choice (radio buttons)</label> </p> <p> <input onclick='showChoice(1)' name='qt' type='radio' id='qt2' /> <label for='qt2'>Short (1-3) Word Answer (text box)</label> </p> <div class='divider'></div> <div id='fibbox' style='display: none;'> <p>Note: Answer checking is case and space-insensitive.</p> <div class='row' style='margin-bottom: 0px;'> <div class='col s12'> <div style='margin-bottom: 5px;' class='chips chips-placeholder'></div> </div> </div> </div> <div id='mcbox' style='display: none;'> <p>Click on the answer choice text to edit it, and select the correct answer by clicking on the bubble on the left.</p> <input class='browser-default' name='qgroup' type='radio' id='qa1' style='opacity:1; pointer-events: auto; position: relative;' /> <p id='q1' contentEditable style='display: inline; padding-left: 6px;'>Click to edit</p><br> <input class='browser-default' name='qgroup' type='radio' id='qa2' style='opacity:1; pointer-events: auto; position: relative;' /> <p id='q2' contentEditable style='display: inline; padding-left: 6px;'>Click to edit</p><br> <input class='browser-default' name='qgroup' type='radio' id='qa3' style='opacity:1; pointer-events: auto; position: relative;' /> <p id='q3' contentEditable style='display: inline; padding-left: 6px;'>Click to edit</p><br> <input class='browser-default' name='qgroup' type='radio' id='qa4' style='opacity:1; pointer-events: auto; position: relative;' /> <p id='q4' contentEditable style='display: inline; padding-left: 6px;'>Click to edit</p><br> </div> <br> <div id='xbox' style='display: none;'> <div class='row'> <div class='input-field col s12' style='margin-top: 0px;'> <input id='qexplanation' type='text' class='validate'> <label id='qexplanation' for='questionText'>Answer Explanation</label> </div> </div> </div>";
            Materialize.updateTextFields();
            $("#saveQuestionBtn").attr("onclick", "saveQuestion()");
            $("#createQuest").modal("open");
            Materialize.updateTextFields();
            Materialize.updateTextFields();
        }

        function showChoice(thisValue) {
            if (thisValue == 0) {
                document.getElementById("mcbox").style.display = "block";
                document.getElementById("fibbox").style.display = "none";
                document.getElementById("xbox").style.display = "block";
            }
            else {
                document.getElementById("mcbox").style.display = "none";
                document.getElementById("fibbox").style.display = "block";
                document.getElementById("xbox").style.display = "block";
                $('.chips-placeholder').material_chip({
                    placeholder: 'Enter acceptable answers, pressing "enter" after entering each one.',
                    secondaryPlaceholder: '+Answer',
                });
            }
        }

        function saveQuestion(override) {
            var video = document.getElementById("lessonVideo");
            
            if (override == "t") {
                document.querySelector('#mcForm input[name = "override"]').value = "t";
                document.querySelector('#fibForm input[name = "override"]').value = "t";
                document.querySelector('#mcForm input[name = "timestamp"]').value = $(".getEditTimestamp").attr("id");
                document.querySelector('#fibForm input[name = "timestamp"]').value = $(".getEditTimestamp").attr("id");
            }
            else {
                document.querySelector('#mcForm input[name = "override"]').value = "f";
                document.querySelector('#fibForm input[name = "override"]').value = "f";
                document.querySelector('#mcForm input[name = "timestamp"]').value = video.currentTime;
                document.querySelector('#fibForm input[name = "timestamp"]').value = video.currentTime;
            }
            
            var questionText = document.getElementById("questionText").value;
            var explanation = document.getElementById("qexplanation").value;

            if (questionText == "" || explanation == "") {
                Materialize.toast("You forgot to fill something out!", 4000);
                return;
            }

            if (document.querySelector('input[name = "qt"]:checked').id == "qt1") {
                var answers = [document.getElementById("q1").innerHTML, document.getElementById("q2").innerHTML, document.getElementById("q3").innerHTML, document.getElementById("q4").innerHTML];
                var selected;
                try {
                    selected = document.querySelector('input[name = "qgroup"]:checked').id;
                }
                catch (err) {
                    Materialize.toast("You forgot to fill something out!", 4000);
                    return;
                }
                document.querySelector('#mcForm input[name = "type"]').value = "c";
                document.querySelector('#mcForm input[name = "question"]').value = questionText;
                document.querySelector('#mcForm input[name = "explanation"]').value = explanation;
                document.querySelector('#mcForm input[name = "selected"]').value = selected;
                document.querySelector('#mcForm input[name = "answers"]').value = answers.join("<|>");

                $('#mcForm').ajaxSubmit({
                    success: function(result) {
                        try {
                            var arr = JSON.parse(result);
                            arr = arr.replace(/&#60;/g, "<");
                            arr = arr.replace(/&#62;/g, ">");
                            arr = arr.replace(/\\/g, '');
                            if (arr == "ERR1") {
                                Materialize.toast("A question at that time already exists. Move to a different time.", 5000);
                                return;
                            }
                            document.getElementById("questionT").innerHTML = arr;
                        }
                        catch (err) {
                            console.log(result);
                            console.log(err);
                            console.log("User tried to work with nonexistent items.");
                            window.location.reload();
                        }
                    }
                });

                $("#createQuest").modal("close");

            }
            else if (document.querySelector('input[name = "qt"]:checked').id == "qt2") {
                var chips = document.getElementsByClassName("chips")[0].children;
                var chipData = [];
                for (var i = 0; i < (chips.length - 1); i++) {
                    var node = chips[i].childNodes[0];
                    chipData.push(node.textContent ? node.textContent : node.innerText);
                }

                if (chipData.length == 0) {
                    Materialize.toast("You forgot to fill something out!", 4000);
                    return;
                }

                document.querySelector('#fibForm input[name = "type"]').value = "fib";
                document.querySelector('#fibForm input[name = "question"]').value = questionText;
                document.querySelector('#fibForm input[name = "explanation"]').value = explanation;
                document.querySelector('#fibForm input[name = "answers"]').value = chipData.join("<|>");

                $('#fibForm').ajaxSubmit({
                    success: function(result) {
                        try {
                            var arr = JSON.parse(result);
                            arr = arr.replace(/&#60;/g, "<");
                            arr = arr.replace(/&#62;/g, ">");
                            arr = arr.replace(/\\/g, '');
                            if (arr == "ERR1") {
                                Materialize.toast("A question at that time already exists. Move to a different time.", 5000);
                                return;
                            }
                            document.getElementById("questionT").innerHTML = arr;
                        }
                        catch (err) {
                            console.log(err);
                            console.log("User tried to work with nonexistent items.");
                            window.location.reload();
                        }
                    }
                });

                $("#createQuest").modal("close");
            }
            else {
                Materialize.toast("You forgot to fill something out!", 4000);
                return;
            }

        }

        function editQuestion(timestamp) {
            document.getElementById("createContent").innerHTML = "<div class='preloader-wrapper active'> <div class='spinner-layer spinner-blue-only'> <div class='circle-clipper left'> <div class='circle'></div> </div><div class='gap-patch'> <div class='circle'></div> </div><div class='circle-clipper right'> <div class='circle'></div> </div> </div> </div>";
            $("#createQuest").modal();
            $("#createQuest").modal("open");

            $.ajax({
                url: "ajax/getEditModal.php?t=" + timestamp,
                success: function(result) {
                    try {
                        $thing = JSON.parse(result);
                        var arr = $thing.card;
                        arr = arr.replace(/&#60;/g, "<");
                        arr = arr.replace(/&#62;/g, ">");
                        arr = arr.replace(/\\/g, '');
                        document.getElementById("createContent").innerHTML = arr;
                        Materialize.updateTextFields();
                        Materialize.updateTextFields();
                        Materialize.updateTextFields();
                        console.log($thing.chips);
                        $('.chips-initial').material_chip({
                            data: $thing.chips,
                        });
                        $("#saveQuestionBtn").attr("onclick", "saveQuestion('t')");
                    }
                    catch (err) {
                        console.log(result);
                        console.log(err);
                        console.log("User tried to work with nonexistent items.");
                        window.location.reload();
                    }

                }
            });

        }

        function deleteRow(timestamp) {
            var okay = confirm("Are you sure you want to delete this question? You can't undo this.");
            if (okay) {
                $.ajax({
                    url: "ajax/deleteVideoTimestamp.php?t=" + timestamp,
                    success: function(result) {
                        try {
                            var arr = JSON.parse(result);
                            arr = arr.replace(/&#60;/g, "<");
                            arr = arr.replace(/&#62;/g, ">");
                            arr = arr.replace(/\\/g, '');
                            document.getElementById("questionT").innerHTML = arr;
                        }
                        catch (err) {
                            console.log(result);
                            console.log("User tried to work with nonexistent items.");
                            window.location.reload();
                        }

                    }
                });
            }
        }

        function loadPreview() {
            $.ajax({
                url: "ajax/getVideoPreviewData.php",
                success: function(result) {
                    try {
                        $response = JSON.parse(result);
                        $continueRun = true;
                        recurse();
                    }
                    catch (err) {
                        console.log(result);
                        console.log("User tried to work with nonexistent items.");
                        window.location.reload();
                    }

                }
            });
            
        }
        
        function recurse() {
            var player = document.getElementById("previewVideo");
            if ($continueRun == false) {
                return;
            }
            console.log("running");
            for (var i=0; i<$response.question.length; i++) {
                if ($response.timestamp[i] == Math.floor(player.currentTime)) {
                    player.pause();
                    if ($response.type[i] == "c") {
                        alert("Question! \nQuestion: " + $response.question[i] + " \nType: Multiple Choice \nChoices: " + $response.questionCode[i] + " \nCorrect Question Index (1-4): " + $response.questionSys[i] + " \nExplanation: " + $response.explanation[i]);
                        player.play();
                    } else {
                        alert("Question! \nQuestion: " + $response.question[i] + " \nType: Short (1-3) Word Free Response \nAccepted Answers: " + $response.questionSys[i] + " \nExplanation: " + $response.explanation[i]);
                        player.play();
                    }
                }
            }
            
            setTimeout(recurse, 950);
        }

        function clearPreview() {
            document.getElementById("previewVideo").pause();
            $continueRun = false;
            getLessonName();
        }
        
        function isInt(value) {
          return !isNaN(value) &&
                 parseInt(Number(value)) == value &&
                 !isNaN(parseInt(value, 10));
        }
        
        function editTimestamp(timestamp) {
            var new_timestamp = prompt("What would you like the new timestamp to be? Please enter the number of seconds since the start of the video (e.g., 92), not any other time notation.");
            
            if (new_timestamp == "") {
                Materialize.toast("You have to enter something here!", 4000);
                return;
            }
            
            if (!isInt(new_timestamp)) {
                Materialize.toast("That input wasn't in the right format! Enter a whole number of senconds since the start of the video (e.g., 83.)", 5000);
                return;
            }
            
            if (new_timestamp == 0) {
                Materialize.toast("You can't have a question at 0 seconds! If it has to be at the beginning, put it at one second.", 5000);
                return;
            }
            
            $.ajax({
                    url: "ajax/changeVideoTimestamp.php?u=" + timestamp + "&t=" + new_timestamp,
                    success: function(result) {
                        try {
                            var arr = JSON.parse(result);
                            arr = arr.replace(/&#60;/g, "<");
                            arr = arr.replace(/&#62;/g, ">");
                            arr = arr.replace(/\\/g, '');
                            document.getElementById("questionT").innerHTML = arr;
                        }
                        catch (err) {
                            console.log(result);
                            console.log("User tried to work with nonexistent items.");
                            window.location.reload();
                        }

                    }
                });
        }
        
        function getLessonName() {
            Materialize.updateTextFields();
            $.ajax({
                    url: "ajax/getLessonName.php",
                    success: function(result) {
                        try {
                            document.getElementById("lessonName").value = result;
                            Materialize.updateTextFields();
                            Materialize.updateTextFields();
                        }
                        catch (err) {
                            console.log(result);
                            console.log("User tried to work with nonexistent items.");
                            window.location.reload();
                        }

                    }
                });
        }
        
        function updateName() {
            var lessonName = document.getElementById("lessonName").value;
            if (lessonName.trim() == "") {
                Materialize.toast("This can't be empty!", 4000);
                return;
            }
            
            $.ajax({
                    url: "ajax/setLessonName.php?n=" + lessonName,
                    success: function(result) {
                        try {
                            $thing = JSON.parse(result);
                            document.getElementById("lessonName").value = $thing.lesson;
                            Materialize.updateTextFields();
                            Materialize.updateTextFields();
                            Materialize.toast("Done!", 3000);
                        }
                        catch (err) {
                            console.log(result);
                            console.log("User tried to work with nonexistent items.");
                            window.location.reload();
                        }

                    }
                });
        }
        
        function navBack() {
            window.location.href = window.location.origin = "/coursecreator/editmain?id=<?php echo $cid; ?>";
        }
        
        $( document ).ready(function() {
          Materialize.updateTextFields();
        });
        
    </script>
</body>
</html>