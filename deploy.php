<?php
session_start();
require "database.php";
require "filewriter.php";
$conn = connect("coursecreator");

$cid = $_GET["id"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;
$_SESSION["editcourse"] = $cid;

$course = queryArray($conn, "SELECT * FROM createdCourses WHERE id=$id AND cid=$cid", false, "db")[0];
$courseName = str_replace(" ", "_", strtolower($course["name"]));

$dependenciesArray = explode(",", $course["dependencies"]);

$dependencyNameArray = array();

$sqlCalls = array();

$output = shell_exec(escapeshellcmd("rm -rf /var/course_export/{$table}/"));
$output = shell_exec(escapeshellcmd("rm -rf /var/course_export/{$table}.zip"));

foreach ($dependenciesArray as $dependency) {
    if ($dependency == "0") {
        continue;
    }

    $pieces = explode("-", $dependency);
    $name = queryRow($conn, "SELECT name AS name FROM createdCourses WHERE id={$pieces[0]} AND cid={$pieces[1]}", "name");
    array_push($dependencyNameArray, $name);
}

$allDependencies = implode("<|>", $dependencyNameArray);

if ($allDependencies == "") {
    $allDependencies = "0";
}

array_push($sqlCalls, "INSERT INTO course VALUES ('<|>NEXT_COURSE_NUM<|>', '{$course['name']}', '{$course['description']}', '{$course['longDescription']}', '$allDependencies', '{$course['structured']}', '{$course['fc_description']}');");

$course = queryArray($conn, "SELECT * FROM $table ORDER BY rowOrder", false, "db");


array_push($sqlCalls, "CREATE TABLE `$courseName` (`id` int(11) NOT NULL AUTO_INCREMENT, `type` text, `connection` text, `maxpoints` int(11) DEFAULT NULL, `block` int(11) NOT NULL, `description` text NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

writeFile("name.txt", "/var/course_export/{$table}/", array($courseName));

foreach ($course as &$lesson) {
    $connection = "N/A";
    if ($lesson["type"] == "v") {
        $connection = '<script src="coursejs/' . $courseName . '/' . $lesson["rowOrder"] . '.js"></script><script>var videosrc="coursevideo/' . $courseName . '/' . $lesson["rowOrder"] . '.mp4";</script>';
    }

    if ($lesson["id"] == 9999) {
        $max = count($course);
        $lesson["rowOrder"] = $course[$max - 2]["rowOrder"] + 1;
        $lesson["block"] = $course[$max - 2]["block"] + 1;
    }

    array_push($sqlCalls, "INSERT INTO $courseName VALUES ({$lesson["rowOrder"]}, '{$lesson["type"]}', '$connection', 0, {$lesson["block"]}, '{$lesson["description"]}');");
}

$max = count($course);
$finalRowOrder = $course[$max - 2]["rowOrder"] + 2;
$finalBlock = $course[$max - 2]["block"] + 2;

array_push($sqlCalls, "INSERT INTO $courseName VALUES ($finalRowOrder, 'd', 'N/A', 0, $finalBlock, 'All done!');");

array_push($sqlCalls, "CREATE TABLE `{$courseName}activity` (`id` int(11) DEFAULT NULL, `section` int(11) DEFAULT NULL, `question` text, `graphic` text, `inputcode` text, `answer` text, `topic` text, `type` text, `explanation` text) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$course = queryArray($conn, "SELECT * FROM {$table}_activity", false, "db");

foreach ($course as $data) {
    array_push($sqlCalls, "INSERT INTO {$courseName}activity VALUES ({$data['rowOrder']}, {$data['section']}, '{$data['question']}', '{$data['graphic']}', '{$data['inputcode']}', '{$data['answer']}', '{$data['topic']}', '{$data['type']}', '{$data['explanation']}');");
}

$course = queryArray($conn, "SELECT * FROM $table WHERE type='v'", false, "db");

writeFile("execute.sql", "/var/course_export/{$table}/", $sqlCalls);

foreach ($course as $video) {
    $videoFile = array();

    $data = queryArray($conn, "SELECT * FROM {$table}_js WHERE id={$video['id']}", true, "js"); // SWITCH TO FALSE FOR PRODUCTION
    array_push($videoFile, "var potato = 50;");
    array_push($videoFile, "var currentInterval = 0;");

    $questionData = "";
    $timestampData = "";
    $typeData = "";
    $questionCodeData = "";
    $questionSysData = "";
    $explanationData = "";

    foreach ($data as $question) {
        if ($questionData != "") {
            $questionData .= ", ";
            $timestampData .= ", ";
            $typeData .= ", ";
            $questionCodeData .= ", ";
            $questionSysData .= ", ";
            $explanationData .= ", ";
        }

        $questionData .= "'{$question["question"]}'";
        $timestampData .= "'{$question["timestamp"]}'";
        $typeData .= "'{$question["type"]}'";

        if ($question["type"] == "c") {
            $answers = explode("|", $question["questionCode"]);
            $questionCodeData .= "['{$answers[0]}', '{$answers[1]}', '{$answers[2]}', '{$answers[3]}']";
        } else {
            $questionCodeData .= "'N/A'";
        }

        if ($question["type"] == "fib") {
            $correctAnswers = explode("|", $question["answer"]);
            $valueToBeAdded = "";
            foreach ($correctAnswers as $answerVal) {
                if ($valueToBeAdded != "") {
                    $valueToBeAdded .= ",";
                }

                $valueToBeAdded .= "'{$answerVal}'";
            }

            $questionSysData .= "[" . $valueToBeAdded . "]";
        } else {
            $questionSysData .= "'{$question["answer"]}'";
        }

        $explanationData .= "'{$question["explanation"]}'";

    }

    array_push($videoFile, "var question = [{$questionData}];");
    array_push($videoFile, "var timestamp = [{$timestampData}];");
    array_push($videoFile, "var type = [{$typeData}];");
    array_push($videoFile, "var questionCode = [{$questionCodeData}];");
    array_push($videoFile, "var questionSys = [{$questionSysData}];");
    array_push($videoFile, "var explanation = [{$explanationData}];");

    writeFile("{$video["rowOrder"]}.js", "/var/course_export/{$table}/coursejs/{$table}/", $videoFile);

}

mkdir("/var/course_export/{$table}/coursevideo/", 0777, true);
$output = shell_exec(escapeshellcmd("cp -rf /var/www/coursecreator/video/{$table}/ /var/course_export/{$table}/coursevideo/"));

?>

<html>
<head>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="css/materialize.min.css">
    <script src="js/sortable.min.js"></script>
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

    </style>
</head>
<body>
    <div style="height: 19vh;">
        <p></p>
    </div>

    <div class="center-align">
    <div class="preloader-wrapper big active">
        <div class="spinner-layer spinner-blue-only">
          <div class="circle-clipper left">
            <div class="circle"></div>
          </div><div class="gap-patch">
            <div class="circle"></div>
          </div><div class="circle-clipper right">
            <div class="circle"></div>
          </div>
        </div>
      </div>
      <br><br>
      <h5>Packaging your course...</h5>
      <p>This may take a few minutes.</p>
  </div>

    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/materialize.min.js"></script>
    <script src="js/sweetalert2.min.js"></script>
    <script>
        $.ajaxSetup({ cache: false });
        $.ajax({
            url: "ajax/zipforrelease.php",
            success: function(result) {
                $(".center-align").css('display', 'none');
                try {
                    if (result.indexOf("zip error:") == -1) {
                        swal("You're good!", "Your course has been packaged and sent off! You should expect a confirmation of deployment email in a few days.", "success")
                        .then((value) => {
                            window.location.href = "dash";
                        });
                    } else {
                        swal("Ahh!", "The automagic packager failed. Reload this page and try again a few times, and if you don't get a success message, send an email to hello@codengine.io, and we'll sort you out.", "error")
                        .then((value) => {
                            window.location.href = "dash";
                        });
                    }
                }

                catch (err) {
                    console.log(err);
                    console.log(result);
                    console.log("User tried to work with nonexistent items.");
                    window.location.reload();
                }

            }
        });
    </script>
</body>
</html>

</body>
</html>
