<?php
session_start();
$cid = $_SESSION["editcourse"];
$id = $_SESSION["userid"];
$table = $id . "_" . $cid;

$output = shell_exec(escapeshellcmd("/var/course_export/package $table"));

echo $output;

$output = shell_exec(escapeshellcmd("rm -r /var/course_export/{$table}/"));

echo $output;

exit;

?>