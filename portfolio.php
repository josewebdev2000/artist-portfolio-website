<?php require_once("helpers.php");
// PHP Code for Portfolio Page
// Grab POST request from user, It will contain an integer
// Based on that integer, return an associative array of data

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

function main()
{
    // Grab JSON from the user
    $user_json = get_json_request();

    // Process the JSON from the user and prepare a response
    $response_json_info = get_portfolio_details_json_result($user_json);

    // Send the response
    if ($response_json_info["success"])
    {
        send_json_response($response_json_info["json"]);
    }

    else
    {
        send_json_error_response($response_json_info["json"], 500);
    }
    
}

function convert_sql_data_to_human_date($portfolio_project)
{
    // Grab the date
    $sqlDate = $portfolio_project["date"];
    
    // Convert SQL date to a Unix timestamp
    $timestamp = strtotime($sqlDate);

    // Format the Unix timestamp to the desired format
    $customDate = date('d F, Y', $timestamp);

    return $customDate;
}

function get_portfolio_details_by_id($id ,$db_conn)
{
    // Get portfolio details by id from the DB
    
    // Build the SQL query to do so
    $sql_query_for_portfolio_details = "SELECT title, category, company, date, description ";
    $sql_query_for_portfolio_details .= "FROM portfolio_projects WHERE id='$id'";

    // Send the Query to the DB
    $portfolio_projects_result_set = mysqli_query($db_conn, $sql_query_for_portfolio_details);

    // Confirm the Query to the DB was successful
    confirm_result_set($portfolio_projects_result_set);

    // Convert the info grabbed from the DB to an associative array
    $portfolio_project = mysqli_fetch_assoc($portfolio_projects_result_set);

    // Release the memory from DB as soon as we grab DB data into a PHP native data structure
    mysqli_free_result($portfolio_projects_result_set);

    // Change the date to a human date
    $portfolio_project["date"] = convert_sql_data_to_human_date($portfolio_project);

    // Return the portfolio project data
    return $portfolio_project;

}

function get_portfolio_details_json_result($decoded_json)
{
    $error_free = true;
    $json_response = null;

    // Parse the decoded json request the user send
    if ($decoded_json != null)
    {
        if (is_valid_id_param($decoded_json["id"]))
        {
            // Start out a DB connection here
            $db_conn = get_db_conn();

            // Grab the associative array that contains the same type as the JSON
            $portfolio_details_requested = get_portfolio_details_by_id(urldecode($decoded_json["id"]), $db_conn);

            // Disconnect from DB
            disconnect_db_conn($db_conn);

            if ($portfolio_details_requested == null)
            {
                $error_free = false;
            }

            // Encode it in JSON
            $json_data_response = json_encode($portfolio_details_requested);
            $json_response = $json_data_response;

        }

        else
        {
            $error_free = false;
        }
    }

    else
    {
        $error_free = false;
    }

    if(!$error_free)
    {
        $error_msg = [
            "error" => "There was an error processing your data"
        ];

        $error_json_response = json_encode($error_msg);
        $json_response = $error_json_response;

    }

    // Build associative array with json_response and whether it's free or not
    $response_info = [
        "json" => $json_response,
        "success" => $error_free
    ];

    return $response_info;
}
main();
?>