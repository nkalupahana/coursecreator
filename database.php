<?php

// Activate to show all errors in code

/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

function unsafe_escape($inp) {
    if (is_array($inp)) {
        return array_map(__METHOD__, $inp);
    }

    if (!empty($inp) && is_string($inp)) {
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
    }

    return $inp;
}

function escape($inp) {
    if (is_array($inp)) {
        return array_map(__METHOD__, $inp);
    }

    if (!empty($inp) && is_string($inp)) {
        return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a", "|", "<->"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z', "-", "|"), $inp);
    }

    return $inp;
}

function unsafe_escape_tofile_db($inp) {
    if (is_array($inp)) {
        return array_map(__METHOD__, $inp);
    }

    if (!empty($inp) && is_string($inp)) {
        return str_replace(array("'", '"'), array("\\'", '\\"'), $inp);
    }

    return $inp;
}

function unsafe_escape_tofile_js($inp) {
    if (is_array($inp)) {
        return array_map(__METHOD__, $inp);
    }

    if (!empty($inp) && is_string($inp)) {
        return str_replace(array("'"), array("\\'"), $inp);
    }

    return $inp;
}

function connect($dbname) {
    // Set SQL login parameters
    $servername = "localhost";
    $username = "root";
    $password = "DB_PASSWORD";
    $dbname = $dbname;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
    	die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

function queryRow($conn, $statement, $rowName, $allowZeroResults = false) {
    $result = $conn->query($statement);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        return $row[$rowName];
    } else {
        if ($allowZeroResults) {
            return "";
        } else {
            die("You have an error in your inputted SQL statement. Try running the following statement on your client: " . $statement);
        }
    }
}

function queryArray($conn, $statement, $allowZeroResults = false, $escapeResults = "") {
    $data = [];
    $result = $conn->query($statement);

    $counter = 0;
    settype($counter, "integer");

    if ($result->num_rows > 0) {
         while($row = $result->fetch_assoc()) {
             if ($escapeResults == "db") {
                $data[$counter] = unsafe_escape_tofile_db($row);
             } else if ($escapeResults == "js") {
                $data[$counter] = unsafe_escape_tofile_js($row);
             } else {
                 $data[$counter] = $row;
             }

             $counter = $counter + 1;
         }

         return $data;
    } else {
        if ($allowZeroResults) {
            return "";
        } else {
            die("You have an error in your inputted SQL statement. Try running the following statement on your client: " . $statement);
        }
    }
}

function queryArrayAndIsolate($conn, $statement, $rowName, $allowZeroResults = false) {
    $data = [];
    $result = $conn->query($statement);

    $counter = 0;
    settype($counter, "integer");

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $data[$counter] = $row;
            $counter = $counter + 1;
        }

        foreach ($data as &$item) {
            $item = $item[$rowName];
        }

        return $data;
    } else {
        if ($allowZeroResults) {
            return "";
        } else {
            die("You have an error in your inputted SQL statement. Try running the following statement on your client: " . $statement);
        }
    }
}

function modifyDatabase($conn, $statement) {
    if ($conn->query($statement) !== TRUE) {
        die("SQL Error: " . $statement . "<br>" . $conn->error);
    }
}


?>
