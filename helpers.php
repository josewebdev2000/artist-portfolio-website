<?php require_once("config.php");

function is_post_request()
{
    // Determine if a given request is a POST request
    return $_SERVER["REQUEST_METHOD"] == "POST";
}

function is_valid_id_param($id_param)
{
    // Return true if the given id parameter is valid
    return isset($id_param) && is_numeric($id_param);
}

function get_json_request()
{
    // Grab a JSON request from the front-end
    if (is_post_request())
    {
        // Grab JSON data from the front-end as a file
        $jsonData = file_get_contents("php://input");

        // Decode the JSON data
        $decodedData = json_decode($jsonData, true);

        return $decodedData;
    }
}

function send_json_response($json_response)
{
    // Set the header to send a JSON response
    header("Content-Type: application/json");

    // Send it with echo
    echo $json_response;
}

function send_json_error_response($json_error_response, $error_http_code)
{
    // Send a JSON error to tell the client what went wrong
    http_response_code($error_http_code);

    // Set the header to send a JSON response
    header("Content-Type: application/json");

    echo $json_error_response;
}

function get_db_conn()
{
    // Establish a server connection with the database
    $conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

    // Check connection was successful
    confirm_db_conn($conn);

    return $conn;
}

function confirm_db_conn($conn)
{
    // Confirm the given db connection was successful
    if (mysqli_connect_errno())
    {
        $msg = "Database connection failed";
        $msg .= mysqli_connect_error();
        $msg .= "( " . mysqli_connect_errno() . ")";
        exit($msg); // Cancel any further PHP execution
    }
}

function disconnect_db_conn($conn)
{
    // Remove connection from the database
    if (isset($conn))
    {
        mysqli_close($conn);
    }
}

function confirm_result_set($result_set)
{
    // Execute in case DB query fails
    if (!isset($result_set))
    {
        exit("Database query failed");
    }
}
?>