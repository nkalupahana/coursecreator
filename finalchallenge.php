<?php
session_start();
require "database.php";
require "tablefunctions.php";

$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;

$conn = connect("coursecreator");

$description = queryRow($conn, "SELECT fc_description AS description FROM createdCourses WHERE id=$id AND cid=$cid", "description");

echo "<script>var toSet = `$description`;</script>";

?>

<html>

<head>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css">
</head>

<body>
    <div class="container">
        <br>
        <center><h4>Final Challenge Editor</h4></center>
        
        <p>Here's a blurb on what Final Challenges are.</p>
        <p>Here's a quick example.</p>
        
        <div class="row">
            <div class="col s12">
                <div class="row">
                    <div class="input-field col s12">
                        <form id="mainform" action="ajax/setFinalChallenge.php" method="post">
                            <textarea name="textarea-main" id="textarea-main" class="materialize-textarea"></textarea>
                            <label for="textarea-main">Final Challenge Criteria</label>
                        </form>
                    </div>
                    <a class="waves-effect waves-light btn" onclick="updateDB()" style="left: 11px !important;">Submit</a>
                </div>
            </div>
        </div>
    </div>
    
    <a class="btn-floating btn-large waves-effect waves-light red" style="position: absolute; top: 25px; left: 20px;" onclick="navBack()"><i class="material-icons">arrow_back</i></a>

    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/materialize.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js" integrity="sha384-FzT3vTVGXqf7wRfy8k4BiyzvbNfeYjK+frTVqZeNDFl8woCbF0CYG6g2fMEFFo/i" crossorigin="anonymous"></script>

    <script>
        $(document).ready(function() {
            $("#textarea-main").val(toSet);
            Materialize.updateTextFields();
            $('#textarea-main').trigger('autoresize');
        });
        
        function updateDB() {
            $('#mainform').ajaxSubmit({
                success: function(result) {
                    Materialize.toast("Saved!", 2500);
                }
            });

        }
        
        function navBack() {
            window.location.href = window.location.origin = "/coursecreator/editmain?id=<?php echo $cid; ?>";
        }
        
        
    </script>

</body>

</html>