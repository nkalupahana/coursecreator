<?php
session_start();

require "database.php";

$conn = connect("coursecreator");

$checkboxIDs = $_SESSION["dependencyCount"];
$dependencies = "";

foreach ($checkboxIDs as $checkboxID) {
    echo $checkboxID;
    if (isset($_POST[$checkboxID])) {
        echo "in";
        $dependencies = $dependencies . substr($checkboxID . ",", 1);
        echo $dependencies;
    }
}

$dependencies = mb_substr($dependencies, 0, -1, 'utf-8');

if ($dependencies == "" || is_null($dependencies) || empty($dependencies)) {
    $dependencies = "0";
}


if (!isset($_POST["cname"]) || !isset($_POST["cdesc"]) || !isset($_POST["clongdesc"]) || !isset($_POST["structure"])) {
    header("Location: dash?info=1");
    exit;
}

$name = escape($_POST["cname"]);
$desc = escape($_POST["cdesc"]);
$longdesc = escape($_POST["clongdesc"]);
$structure = escape($_POST["structure"]);

$statement = "SELECT COUNT(*) AS count FROM createdCourses WHERE id=" . $_SESSION["userid"];
$result = queryRow($conn, $statement, "count");

$insertAt = 0;

if ($result == "0" || $result == 0) {
    $insertAt = 1;
} else {
    $statement = "SELECT MAX(cid) AS cid FROM createdCourses WHERE id=" . $_SESSION["userid"];
    $result = queryRow($conn, $statement, "cid");
    $insertAt = $result + 1;
}

$tableName = $_SESSION["userid"] . "_" . $insertAt;

$comma = "','";
$statement = "INSERT INTO createdCourses VALUES ('" . $_SESSION["userid"] . $comma . $insertAt . $comma . $name . $comma . $comma . $desc . $comma . $longdesc . $comma . $dependencies . $comma . $structure . $comma . "0" . $comma . "')";

modifyDatabase($conn, $statement);

$sql1 = "CREATE TABLE `" . $tableName . "` ( `id` int(11) NOT NULL AUTO_INCREMENT, `type` text, `connection` text, `maxpoints` int(11) DEFAULT NULL, `block` int(11) NOT NULL, `description` text NOT NULL, `rowOrder` int(11) NOT NULL, KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$sql2 = "CREATE TABLE `" . $tableName . "_js` ( `id` int(11) NOT NULL, `question` text, `timestamp` int(11) DEFAULT NULL, `questionCode` text, `answer` text, `type` text, `explanation` text, KEY `id` (`id`), CONSTRAINT `" . $tableName . "_js_ibfk_1` FOREIGN KEY (`id`) REFERENCES `" . $tableName . "` (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$sql3 = "CREATE TABLE `" . $tableName . "_activity` ( `uid` int(11) NOT NULL, `id` int(11) NOT NULL, `section` int(11) NOT NULL, `question` text, `graphic` text, `inputcode` text, `answer` text, `topic` text, `type` text, `explanation` text, KEY `id` (`id`), KEY `uid` (`uid`), CONSTRAINT `" . $tableName . "_activity_ibfk_1` FOREIGN KEY (`id`) REFERENCES `" . $tableName . "` (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

modifyDatabase($conn, $sql1);
modifyDatabase($conn, $sql2);
modifyDatabase($conn, $sql3);

modifyDatabase($conn, "INSERT INTO $tableName VALUES (9999, 'f','N/A',0,9999,'Final Challenge',9999)");

header("Location: dash?info=2")

?>