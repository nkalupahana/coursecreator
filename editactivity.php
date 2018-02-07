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

$nameOfLesson = queryRow($conn, "SELECT description AS description FROM $table WHERE id=$lessonID", "description");

$questions = queryArray($conn, "SELECT * FROM $table" . "_activity WHERE id=$lessonID ORDER BY section", true);
$tableBody = returnActivityTable($questions);

$topic;
$insert = "";

if ($questions != "") {
    $topic = htmlspecialchars(stripslashes(queryArray($conn, "SELECT * FROM $table" . "_activity WHERE id=$lessonID", true)[0]["topic"]), ENT_QUOTES);
    $insert = "<div class='row'> <div class='input-field col s12'> <input value='$topic' id='topic' type='text' class='validate'> <label for='topic'>Lesson Displayed Name (e.g., HTML Basics Quiz)</label> </div> </div>";
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
    <div class="container">
        <br>
        <div class="row">
            <div class="col s12 l6 offset-l3">
                <ul class="tabs">
                    <li class="tab col s6"><a href="#edit" onclick="updateName()">Edit</a></li>
                    <li class="tab col s6"><a href="#preview" onclick="loadPreview()">Preview</a></li>
                </ul>
            </div>
            <div id="edit" class="col s12">
                <br>
                <center>
                    <h3>Edit Questions</h3>
                </center>
                <br>
                <br>
                <table>
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody id="questionT">
                        <?php echo $tableBody; ?>
                    </tbody>
                </table>
                <br><br>
                <center>
                    <a onclick="createQuestion()" class="waves-effect waves-light btn">Add question</a>
                    <a onclick="reorder()" id="reorderButton" class="waves-effect waves-light btn">Reorder</a>
                    <a onclick="window.location.reload()" id="cancelButton" class="waves-effect waves-light btn" style="display: none;">Cancel Reorder</a>
                </center>
                <br><br><br>
                <center>
                    <h3>Update Lesson Data</h3>
                </center>
                <div class="row">
                    <div class="input-field col s12">
                        <input value='<?php echo htmlspecialchars(stripslashes(escape($nameOfLesson)), ENT_QUOTES); ?>' id="lessonName" type="text" class="validate">
                        <label for="lessonName">Lesson Name (e.g., What is HTML? (activity))</label>
                    </div>
                </div>
                <div id="displayTopicInput"><?php echo $insert; ?></div>
                
                <a class="waves-effect waves-light btn" onclick="setName()">Update</a>

            </div>
            <div id="preview" class="col s12">
                <div id="mainContent">
                    <center>
                        <h2 id="topicc"></h2>
                    </center>
                    <div class="row">
                        <div class="col s12 l5 offset-l1" style="height: 50%; border-right-style: inset; boder-color: black; clear: both; overflow: auto; padding-right: 20px;">
                            <div style="position: relative; top: 50%; transform: translateY(-75%);">
                                <center>
                                    <p style="text-align: center;" class="flow-text" id="questionn"></p>
                                </center>
                                <div style="max-height: 40%;" id="graphicc"></div>
                            </div>
                        </div>
                        <div class="col s12 l5" style="height: 50%; padding-left: 20px;">
                            <div style="position: relative; top: 50%; transform: translateY(-50%);">
                                <div id="questioncodee"></div>
                            </div>
                        </div>
                        <div class="col s12 l1">
                            <p> </p>
                        </div>
                    </div>


                    <br><br>
                    <div style="text-align:center;">
                        <a onClick="preview()" class="waves-effect waves-light btn">Next</a>
                    </div>
                    <br><br><br>
                    <center><p id="answerr"></p></center>
                    <center><p id="explanationn"></p></center>
                </div>
            </div>
        </div>
    </div>

    <div id="addQuestion" class="modal modal-fixed-footer">
        <div class="modal-content" id="modal-addQuestion-content">
            
        </div>
        <div class="modal-footer">
            <a href="#!" class="modal-action modal-close waves-effect waves-red btn-flat ">Cancel</a>
            <a href="#!" class="waves-effect waves-green btn-flat" onclick="submitQuestion()">Create</a>
        </div>
    </div>

    <form id="allForm" action="ajax/createActivityQuestion.php" method="post" style="display: none; opacity: 0; pointer-events: none;">
        <input type="text" name="type">
        <input type="text" name="question">
        <input type="text" name="explanation">
        <input type="text" name="graphic">
        <input type="text" name="answer">
        <input type="text" name="options">
        <input type="text" name="override">
    </form>

    <a class="btn-floating btn-large waves-effect waves-light red" style="position: absolute; top: 25px; left: 20px;" onclick="navBack()"><i class="material-icons">arrow_back</i></a>

    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/materialize.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js" integrity="sha384-FzT3vTVGXqf7wRfy8k4BiyzvbNfeYjK+frTVqZeNDFl8woCbF0CYG6g2fMEFFo/i" crossorigin="anonymous"></script>
    <script>
        var sortable;

        $(document).ready(function() {
            $('ul.tabs').tabs();
            sortable = Sortable.create(questionT, { group: 'questions', animation: 100, disabled: true, });
            $("#addQuestion").modal();
            Materialize.updateTextFields();
        });

        function reorder() {
            sortable.option("disabled", !sortable.option("disabled"));

            if (!sortable.option("disabled")) {
                document.getElementById("cancelButton").style.display = "inline-block";
                Materialize.toast("Click and drag any row in the lessons table to order your lessons. Click on the 'Save Order' button to save.", 10000);
                document.getElementById("reorderButton").innerHTML = "Save Order";
            }
            else {
                Materialize.Toast.removeAll();
                document.getElementById("cancelButton").style.display = "none";
                var newIDArray = [];
                var itemCollection = document.getElementById("questionT").children;

                console.log(itemCollection);

                for (var i = 0; i < itemCollection.length; i++) {
                    newIDArray.push(itemCollection[i].id.substring(1, 2));
                }

                var parameter = newIDArray.join(",");

                document.getElementById("questionT").innerHTML = "<div class='preloader-wrapper active'> <div class='spinner-layer spinner-blue-only'> <div class='circle-clipper left'> <div class='circle'></div> </div><div class='gap-patch'> <div class='circle'></div> </div><div class='circle-clipper right'> <div class='circle'></div> </div> </div> </div>";

                $.ajax({
                    url: "ajax/reorderActivityQuestions.php?q=" + parameter,
                    success: function(result) {
                        try {
                            var arr = JSON.parse(result);
                            arr = arr.replace(/&#60;/g, "<");
                            arr = arr.replace(/&#62;/g, ">");
                            arr = arr.replace(/\\/g, '');
                            document.getElementById("questionT").innerHTML = arr;
                            document.getElementById("reorderButton").innerHTML = "Reorder";
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

        function deleteRow(rowId) {
            var okay = confirm("Are you sure you want to delete this question? You can't undo this.");
            if (okay) {

                document.getElementById("questionT").innerHTML = "<div class='preloader-wrapper active'> <div class='spinner-layer spinner-blue-only'> <div class='circle-clipper left'> <div class='circle'></div> </div><div class='gap-patch'> <div class='circle'></div> </div><div class='circle-clipper right'> <div class='circle'></div> </div> </div> </div>";

                $.ajax({
                    url: "ajax/deleteActivityQuestion.php?q=" + rowId,
                    success: function(result) {
                        try {
                            var arr = JSON.parse(result);
                            arr = arr.replace(/&#60;/g, "<");
                            arr = arr.replace(/&#62;/g, ">");
                            arr = arr.replace(/\\/g, '');
                            document.getElementById("questionT").innerHTML = arr;
                            updateTopic();
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
        
        function createQuestion() {
            document.getElementById("modal-addQuestion-content").innerHTML = "<h4>Add a Question</h4> <p>Questions in activities are much more powerful. They can have customizable graphics, more question customization, and more.</p> <div class='row' style='margin-bottom: 8px;'> <div class='input-field col s12'> <input id='questionInput' type='text' style='margin-bottom: 0px;' class='validate'> <label for='questionInput'>Question</label> </div> </div> <div class='row'> <div class='input-field col s12'> <input id='graphicHtml' type='text' class='validate'> <label for='graphicHtml'>HTML of graphic (if applicable)</label> </div> </div> <p>Question Type:</p> <p> <input onclick='showChoice(0)' name='qt' type='radio' id='qt1' /> <label for='qt1'>Multiple Choice (radio buttons)</label> </p> <p> <input onclick='showChoice(1)' name='qt' type='radio' id='qt2' /> <label for='qt2'>Short (1-3) Word Answer (text box)</label> </p> <div id='mcbox' style='display: none;'> <div class='divider'></div> <p>Type in your multiple choice options below, pressing 'enter' after each one. Then, click on the correct answer.</p> <div class='row' style='margin-bottom: 0px;'> <div class='col s12'> <div style='margin-bottom: 5px;' class='chips chips-placeholder-1'></div> </div> </div> </div> <div id='fibbox' style='display: none;'> <div class='divider'></div> <br> <div class='row' style='margin-bottom: 0px;'> <div class='col s12'> <div style='margin-bottom: 5px;' class='chips chips-placeholder-2'></div> </div> </div> <div class='row'> <div class='col s12'> <p> <input type='checkbox' id='isCase' /> <label for='isCase'>Case-Sensitive?</label> </p> </div> </div> </div> <div class='row' style='display: none;' id='explanation-box'><div class='input-field col s12'><input id='explanation' type='text' class='validate'><label for='explanation'>Answer Explanation</label></div></div><input type='text' style='display: none;' name='override-old' value=''>";
            $("#addQuestion").modal("open");
            $('.chips-placeholder-1').material_chip({
                placeholder: 'Enter options, pressing "enter" after entering each one.',
                secondaryPlaceholder: '+Answer',
            });
            $('.chips-placeholder-2').material_chip({
                placeholder: 'Enter acceptable answers, pressing "enter" after entering each one.',
                secondaryPlaceholder: '+Answer',
            });
        }
        
        function showChoice(value) {
            document.getElementById("explanation-box").style.display = "block";
            if (value == 0) {
                document.getElementById("mcbox").style.display = "block";
                document.getElementById("fibbox").style.display = "none";
            } else {
                document.getElementById("mcbox").style.display = "none";
                document.getElementById("fibbox").style.display = "block";
            }
        }
        
        function submitQuestion() {
            var question = $("#questionInput").val();
            var explanation = $("#explanation").val();
            var graphic = $("#graphicHtml").val();
            var type;
            var options;
            var answer;
            
            try {
                console.log($("input[name='qt']:checked")[0].id);
            } catch (err) {
                Materialize.toast("You forgot to fill something out!", 4000);
                return;
            }
            
            
            if ($("input[name='qt']:checked")[0].id == "qt1") {
                var type = "c";
                var chips = document.getElementsByClassName("chips")[0].children;
                var chipData = [];
                for (var i = 0; i < (chips.length - 1); i++) {
                    var node = chips[i].childNodes[0];
                    chipData.push(node.textContent ? node.textContent : node.innerText);
                    if (chips[i].classList.value == "chip selected") {
                        answer = node.textContent ? node.textContent : node.innerText;
                    }
                }

                if (chipData.length == 0 || answer == "") {
                    Materialize.toast("You forgot to fill something out!", 4000);
                    return;
                }
                
                options = chipData.join("<->");
                
                
            } else if ($("input[name='qt']:checked")[0].id == "qt2") {
                var type = "fib";
                var chips = document.getElementsByClassName("chips")[1].children;
                var chipData = [];
                for (var i = 0; i < (chips.length - 1); i++) {
                    var node = chips[i].childNodes[0];
                    chipData.push(node.textContent ? node.textContent : node.innerText);
                }

                if (chipData.length == 0) {
                    Materialize.toast("You forgot to fill something out!", 4000);
                    return;
                }
                
                if ($("#isCase:checked").val() == "on") {
                    options = "s";
                } else {
                    options = "i";
                }
                
                answer = chipData.join("<->");
            }
            
            if (question == "" || explanation == "" || options == "" || answer == "" || answer == undefined || answer == "undefined") {
                Materialize.toast("You forgot to fill something out!", 4000);
                return;
            }
            
            document.querySelector('#allForm input[name = "type"]').value = type;
            document.querySelector('#allForm input[name = "question"]').value = question;
            document.querySelector('#allForm input[name = "explanation"]').value = explanation;
            document.querySelector('#allForm input[name = "graphic"]').value = graphic;
            document.querySelector('#allForm input[name = "answer"]').value = answer;
            document.querySelector('#allForm input[name = "options"]').value = options;
            document.querySelector('#allForm input[name = "override"]').value = document.querySelector('input[name = "override-old"]').value;
            
            document.getElementById("questionT").innerHTML = "<div class='preloader-wrapper active'> <div class='spinner-layer spinner-blue-only'> <div class='circle-clipper left'> <div class='circle'></div> </div><div class='gap-patch'> <div class='circle'></div> </div><div class='circle-clipper right'> <div class='circle'></div> </div> </div> </div>";
            
            $('#allForm').ajaxSubmit({
                success: function(result) {
                    try {
                        var arr = JSON.parse(result);
                        if (arr == "ERR1") {
                            Materialize.toast("You forgot to fill something out!", 4000);
                            return;
                        }
                        arr = arr.replace(/&#60;/g, "<");
                        arr = arr.replace(/&#62;/g, ">");
                        arr = arr.replace(/\\/g, '');
                        $("#addQuestion").modal("close");
                        Materialize.Toast.removeAll();
                        document.getElementById("questionT").innerHTML = arr;
                        updateTopic();
                    }
                    catch (err) {
                        console.log(err);
                        console.log(result);
                        console.log("User tried to work with nonexistent items.");
                        window.location.reload();
                    }
                }
            });
            
        }
        
        function editQuestion(id) {
            $("#addQuestion").modal("open");
            document.getElementById("modal-addQuestion-content").innerHTML = "<div class='preloader-wrapper active'> <div class='spinner-layer spinner-blue-only'> <div class='circle-clipper left'> <div class='circle'></div> </div><div class='gap-patch'> <div class='circle'></div> </div><div class='circle-clipper right'> <div class='circle'></div> </div> </div> </div>";
            
            $.ajax({
                url: "ajax/getActivityModal.php?q=" + id,
                success: function(result) {
                    try {
                        $thing = JSON.parse(result);
                        var arr = $thing.card;
                        arr = arr.replace(/&#60;/g, "<");
                        arr = arr.replace(/&#62;/g, ">");
                        arr = arr.replace(/\\/g, '');
                        document.getElementById("modal-addQuestion-content").innerHTML = arr;
                        Materialize.updateTextFields();
                        Materialize.updateTextFields();
                        Materialize.updateTextFields();
                        console.log($thing.chips);
                        if ($thing.type == "c") {
                            $('.chips-initial-1').material_chip({
                                data: $thing.chips,
                            });
                            
                            $('.chips-initial-2').material_chip({
                                placeholder: 'Enter acceptable answers, pressing "enter" after entering each one.',
                                secondaryPlaceholder: '+Answer',
                            });
                            
                            var chips = document.querySelector(".chips-initial-1").children;
                            for (var i = 0; i < (chips.length - 2); i++) {
                                if (chips[i].childNodes[0].data == $thing.correct) {
                                    chips[i].className = "chip selected";
                                }
                            }
                            
                        } else {
                            $('.chips-initial-1').material_chip({
                                placeholder: 'Enter options, pressing "enter" after entering each one.',
                                secondaryPlaceholder: '+Answer',
                            });
                            
                            $('.chips-initial-2').material_chip({
                                data: $thing.chips,
                            });
                        }
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

        function navBack() {
            window.location.href = window.location.origin = "/coursecreator/editmain?id=<?php echo $cid; ?>"
        }
        
        function updateName() {
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
                
                updateTopic();
        }
        
        function setName() {
            var lessonName = document.getElementById("lessonName").value;
            if (lessonName.trim() == "") {
                Materialize.toast("This can't be empty!", 4000);
                return;
            }
            
            var topicObject = document.getElementById("topic");
            
            if (topicObject == null) {
                $.ajax({
                        url: "ajax/setLessonName.php?n=" + lessonName,
                        success: function(result) {
                            try {
                                $thing = JSON.parse(result);
                                document.getElementById("lessonName").value = $thing.lesson;
                                var arr = $thing.topic.replace(/&#60;/g, "<");
                                arr = arr.replace(/&#62;/g, ">");
                                document.getElementById("displayTopicInput").innerHTML = arr;
                                Materialize.updateTextFields();
                                Materialize.updateTextFields();
                                Materialize.toast("Done!", 3000);
                            }
                            catch (err) {
                                console.log(result);
                                console.log(err);
                                console.log("User tried to work with nonexistent items.");
                                //window.location.reload();
                            }

                        }
                    });
            } else {
                var topicName = topicObject.value;
                
                if (topicName.trim() == "") {
                    Materialize.toast("This can't be empty!", 4000);
                    return;
                }
                
                $.ajax({
                        url: "ajax/setLessonName.php?n=" + lessonName + "&t=" + topicName,
                        success: function(result) {
                            try {
                                $thing = JSON.parse(result);
                                document.getElementById("lessonName").value = $thing.lesson;
                                var arr = $thing.topic.replace(/&#60;/g, "<");
                                arr = arr.replace(/&#62;/g, ">");
                                document.getElementById("displayTopicInput").innerHTML = arr;
                                Materialize.updateTextFields();
                                Materialize.updateTextFields();
                                Materialize.toast("Done!", 3000);
                            }
                            catch (err) {
                                console.log(result);
                                console.log(err);
                                console.log("User tried to work with nonexistent items.");
                                //window.location.reload();
                            }

                        }
                    });
                
                }
        }
        
        function updateTopic() {
            Materialize.updateTextFields();
            $.ajax({
                    url: "ajax/getActivityTopic.php",
                    success: function(result) {
                        try {
                            var arr = JSON.parse(result);
                            arr = arr.replace(/&#60;/g, "<");
                            arr = arr.replace(/&#62;/g, ">");
                            document.getElementById("displayTopicInput").innerHTML = arr;
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
        
        function loadPreview() {
            $.ajax({
                    url: "ajax/getActivityPreviewData.php",
                    success: function(result) {
                        try {
                            $response = JSON.parse(result);
                            $step = 0;
                            preview();
                        }
                        catch (err) {
                            console.log(err);
                            console.log(result);
                            console.log("User tried to work with nonexistent items.");
                            //window.location.reload();
                        }

                    }
                });
        }
        
        function preview() {
            var question = $response.question[$step];
            var graphic = $response.graphic[$step];
            var inputcode = $response.code[$step];
            var type = $response.type[$step];
            var explanation = $response.explanation[$step];
            var topic = $response.topic[$step];
            var answer = $response.answer[$step];
            
            graphic = graphic.replace(/&#60;/g, "<");
            graphic = graphic.replace(/&#62;/g, ">");
            graphic = graphic.replace(/\\/g, '');
            inputcode = inputcode.replace(/&#60;/g, "<");
            inputcode = inputcode.replace(/&#62;/g, ">");
            inputcode = inputcode.replace(/\\/g, '');

            document.getElementById("topicc").innerHTML = topic;
            document.getElementById("questionn").innerHTML = question;
            document.getElementById("graphicc").innerHTML = graphic;
            document.getElementById("questioncodee").innerHTML = inputcode;
            document.getElementById("answerr").innerHTML = "Accepted answer(s): " + answer;
            document.getElementById("explanationn").innerHTML = "Explanation of answer (displayed in popup normally): " + explanation;
            
            $step = $step + 1;
            
            if ($response.question[$step] == undefined || $response.question[$step] == null) {
                $step = 0;
            }
            
        }
        
        
    </script>
</body>

</html>>