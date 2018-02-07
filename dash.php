<?php
session_start();
require "database.php";
$conn = connect("coursecreator");

$statement = "SELECT * FROM createdCourses WHERE id=" . $_SESSION["userid"];
$userData = queryArray($conn, $statement, true);

if ($userData == "") {
    echo "<script>var courses=false; </script>";
    $finalcards = "";
} else {
    echo "<script>var courses=true; </script>";

    $colors = ["red", "deep-purple", "green", "yellow", "deep-orange", "blue-grey", "pink", "indigo", "cyan", "amber", "purple", "blue", "teal", "lime", "orange"];

    $i = 0;
    $cards = [];

    foreach ($userData as $data) {
    	$card = "<div class='col s12 l6'> <div style='border-radius: 10px;' class='card COLOR_PLACE lighten-5 hoverable'> <div class='card-content'> <span class='card-title'>COURSE_TITLE</span> <br> <p>Click 'Edit' to edit your course.</p><p>Click 'Deploy' to review and submit your course to LearnSimply for deployment.</p> </div><div class='card-action' style='padding-bottom: 40px !important;'> <a href='DEPLOY_LINK' class='right' style='margin-right: 5px;'>Deploy</a> <a href='EDIT_LINK' class='right'>Edit</a> </div> </div></div>";

    	$colorValue = rand(0, 14);

    	$card = str_replace("COLOR_PLACE", $colors[$colorValue], $card);
        $card = str_replace("COURSE_TITLE", $data["name"], $card);
        $card = str_replace("DEPLOY_LINK", "deploy?id=" . $data["cid"], $card);
        $card = str_replace("EDIT_LINK", "editmain?id=" . $data["cid"], $card);

        $courseCount = count($userData);

    	if ($courseCount == 1) {
    		$card = "<div class='row'>" . $card . "</div>";
    	} else if ($courseCount % 2 == 0) {
    		if ($i % 2 == 0) {
    			$card = "<div class='row'>" . $card;
    		} else {
    			$card = $card . "</div>";
    		}
    	} else {
    		if ($i == ($courseCount - 1)) {
    			$card = "<div class='row'>" . $card . "</div>";
    		} else if ($i % 2 == 0) {
    			$card = "<div class='row'>" . $card;
    		} else {
    			$card = $card . "</div>";
    		}
    	}

        $cards[$i] = $card;

        $i = $i + 1;
    }

    $finalcards = implode("", $cards);
    
    if ($_GET["info"] == 1 || $_GET["info"] == "1") {
        echo "<script> var error = 1; </script>";
    } else if ($_GET["info"] == 2 || $_GET["info"] == "2") {
        echo "<script> var error = 2; </script>";
    }

}

?>

<html>
<head>
    <!--Import materialize.css-->
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css">
    <link type="text/css" rel="stylesheet" href="css/sweetalert.css">
    <style>
      @font-face {
        font-family: Rainbow;
        src: url(fonts/OvertheRainbow.ttf);
      }
    
      .modal-footer {
          max-height: 48px !important;
      }
    
      .row {
        margin-bottom: 0px !important;
      }
    
      .container {
        margin: 0 auto;
        max-width: none !important;
        width: 93% !important
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
    </style>
    
<body>
    
    <br>
    
    <div class="container">
        <center>
          <h1 style="font-family: Rainbow, Georgia, serif; color: #3FE1B5; font-size: 80px;">Your courses</h1>
        </center>
        <br>
        <center id="no-course" hidden>
          <h4 style="font-family: Roboto-Light, Roboto, serif;">You haven't started making any courses yet!</h2>
        </center>
        <div class="section">
            <?php echo $finalcards; ?>
        </div>
        <div class="divider"></div>
        <br><br>
        <div style="text-align: center;"><a onclick="newCourse()" class="waves-effect waves-light btn">Create a new course</a></div>
        
    </div>
    
    <div id="courseCreatorModal" class="modal modal-fixed-footer">
      <div class="modal-content" id="courseCreatorContent">
     
      </div>
      <div class="modal-footer">
        <a onclick="createCourse()" class="waves-effect waves-green btn-flat" style="margin-top: 0px;">Create</a>
      </div>
    </div>
    <br>
    <br>
    <br>
    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/materialize.min.js"></script>
    <script type="text/javascript" src="js/alertify.js"></script>
    <script type="text/javascript" src="js/sweetalert.min.js"></script>
    
    <script>
        if (courses == true) {
            document.getElementById("no-course").style.display = "none";
        } else {
            document.getElementById("no-course").style.display = "block";
        }
        
        function newCourse() {
            $(".modal").modal();
            document.getElementById("courseCreatorContent").innerHTML = "<div class='preloader-wrapper big active'> <div class='spinner-layer spinner-blue-only'> <div class='circle-clipper left'> <div class='circle'></div> </div><div class='gap-patch'> <div class='circle'></div> </div><div class='circle-clipper right'> <div class='circle'></div> </div> </div> </div>";
            
            $('#courseCreatorModal').modal('open');
            $.ajax({url: "ajax/getCourseModal.php", success: function(result){
                var arr = JSON.parse(result);
                document.getElementById("courseCreatorContent").innerHTML = arr;
                Materialize.updateTextFields();
             }});
            
        }
        
        function createCourse() {
            if (document.getElementById("cname").value == "" || document.getElementById("cdesc").value == "" || document.getElementById("clongdesc").value == "" || $('input[type=radio].structure:checked').length == 0) {
                swal("Wait!", "You need to fill out all fields before submitting.", "error");
            } else {
                $('#courseCreatorModal').modal('close');
                $('form#createform').submit();
            }
        }
        
        function errorHandler() {
            if (typeof error !== 'undefined') {
                if (error == 1) {
                    alertify.error("When creating a course, you need to fill out all fields.");
                } else if (error == 2) {
                    alertify.success("You have successfully made a course! Click on the Edit button to start working on it.");
                }
            }
        }
        
        errorHandler();
        
    </script>
    
</body>
</html>