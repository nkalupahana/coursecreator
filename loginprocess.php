<?php
session_start();
require "database.php";

$id = escape($_POST["idnum"]);
$_SESSION["userid"] = $id;

$conn = connect("coursecreator");

$checkForUser = queryRow($conn, "SELECT COUNT(*) AS count FROM main WHERE id=$id", "count");

if ($checkForUser == "0") {
    die("Whoa! This user ID does not exist. Please add it manually.");
}

header("Location: dash");

?>