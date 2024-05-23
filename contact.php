<?php 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once("helpers.php");
require_once("vendor/autoload.php");
// PHP Code for Contact Page

// Import PHP Mailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Define global contants
define("name_regex", "/^[A-Za-z\s]+$/");
define("email_regex", "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/");
define("expected_json_params", ["name", "email", "subject", "message"]);

function main()
{
    // Grab JSON from the user
    $user_json = get_json_request();

    // Validate and sanitize JSON data
    $validated_and_sanitized_json_data = validate_and_sanitize_json_data($user_json);

    // Check if an error happened
    if (array_key_exists("error", $validated_and_sanitized_json_data))
    {
        // Return an erroneous JSON message with 400
        send_json_error_response(json_encode($validated_and_sanitized_json_data), 400);
        exit();
    }

    // Otherwise, build the email template
    $json_response = build_email_templates($validated_and_sanitized_json_data);

    // If an error happened, send a json_error_response
    if (array_key_exists("error", $json_response))
    {
        send_json_error_response(json_encode($json_response), 400);
        exit();
    }

    // Otherwise send successful json response
    else
    {
        send_json_response(json_encode($json_response));
    }
}

function build_email_templates($email_data)
{
    /* Build two email templates: One for the website admin and the other for the user*/

    // Grab content from Admin HTML template and User HTML Template
    $admin_html_template = file_get_contents(EMAIL_ADMIN_TEMPLATE);
    $user_html_template = file_get_contents(EMAIL_USER_TEMPLATE);

    // Assoc that represents the JSON response to send to the front-end
    $json_response = [];

    // Grab name, email, and message to inject to admin template
    $admin_html_template = str_replace("{{name}}", $email_data["name"], $admin_html_template);
    $admin_html_template = str_replace("{{email}}", $email_data["email"], $admin_html_template);
    $admin_html_template = str_replace("{{message}}", $email_data["message"], $admin_html_template);

    // Grab name to inject in user template
    $user_html_template = str_replace("{{name}}", $email_data["name"], $user_html_template);

    // Send HTML email to admin
    $was_admin_email_successful = send_html_email($admin_html_template, $email_data, true);

    // Send HTML email to user
    $was_user_email_successful = send_html_email($user_html_template, $email_data);

    // Only send error message when email could not be sent to the admin
    if (!$was_user_email_successful)
    {
        $json_response["error"] = "Email could not be sent";
    }

    else
    {
        $json_response["success"] = "Email could be sent";
    }

    return $json_response;
}

function send_html_email($html_template, $email_data, $is_admin = false)
{
    /* Send an html email */

    // Generate a new PHP Mailer instance
    $mailer = new PHPMailer(true);

    try
    {
        $mailer->SMTPDebug = 0;
        $mailer-> isSMTP();
        $mailer->Host = SMTP_EMAIL_SERVER;
        $mailer->SMTPAuth = true;
        $mailer->Username = SMTP_EMAIL;
        $mailer->Password = SMTP_PASS;
        $mailer->Port = 587;

        // Set the email of the sender as the SMTP email
        $mailer->setFrom(SMTP_EMAIL, "No Reply Adriana Morales Email Automatic Sender");
        
        $recipient = "";
        $subject = "";

        // Set the address of the recipient depending on who will receive this email
        if ($is_admin)
        {
            $recipient = EMAIL_ADMIN;
            $subject = $email_data["name"] . " sent a message - " . $email_data["subject"];
        }

        else
        {
            $recipient = $email_data["email"];
            $subject = "Thank you, " . $email_data["name"] . " for contacting me";
        }

        $mailer->addAddress($recipient);

        // Add email content
        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $html_template;

        // Send the email
        $mailer->send();
    }

    catch (Exception $e)
    {
        return false;
    }

    return true;

}

function has_exact_keys($assoc_arr, $expected_keys)
{
    // Return true if the assoc array has the expected arrays
    // Get the keys of the array
    $array_keys = array_keys($assoc_arr);

    // Sort both arrays to ensure order doesn't matter
    sort($array_keys);
    sort($expected_keys);

    // Compare the sorted arrays
    return $array_keys === $expected_keys;
}

function check_valid_json_param($json_param_type, $json_param)
{
    $assoc_to_return = [
        "json_param" => ""
    ];

    if ($json_param_type == "email")
    {
        if (isset($json_param) && preg_match(email_regex, $json_param))
        {
            $json_param = filter_var($json_param, FILTER_SANITIZE_EMAIL);
            $assoc_to_return["json_param"] = $json_param;
        }
    }

    else if ($json_param_type == "name")
    {
        if (isset($json_param) && preg_match(name_regex, $json_param))
        {
            $json_param = filter_var($json_param, FILTER_SANITIZE_STRING);
            $assoc_to_return["json_param"] = $json_param;
        }
    }

    else
    {
        if (isset($json_param) && !empty($json_param))
        {
            $json_param = filter_var($json_param, FILTER_SANITIZE_STRING);
            $assoc_to_return["json_param"] = $json_param;
        }
    
    }

    return $assoc_to_return;
}

function validate_and_sanitize_json_data($json_data_as_assoc_arr)
{
    /* Validate and sanitize JSON data */
    $error_free = true;
    $final_data = null;

    // Make sure JSON has exactly the same keys
    if (!has_exact_keys($json_data_as_assoc_arr, expected_json_params))
    {
        $error_free = false;
    }

    // Grab values for each field
    $name = $json_data_as_assoc_arr["name"];
    $email = $json_data_as_assoc_arr["email"];
    $subject = $json_data_as_assoc_arr["subject"];
    $message = $json_data_as_assoc_arr["message"];

    // Grab validateed and sanitized values for each field
    $name = check_valid_json_param("name", $name)["json_param"];
    $email = check_valid_json_param("email", $email)["json_param"];
    $subject = check_valid_json_param("subject", $subject)["json_param"];
    $message = check_valid_json_param("message", $message)["json_param"];

    // If any of the sanitized parameters is empty, then return false for error free
    if (empty($name) || empty($email) || empty($subject) || empty($message))
    {
        $error_free = false;
    }

    // Return final data depending if we're error free or not
    if ($error_free)
    {
        $final_data = [
            "name" => $name,
            "email" => $email,
            "subject" => $subject,
            "message" => $message
        ];
    }

    else
    {
        $final_data = [
            "error" => "The JSON data the client sent is malformed"
        ];
    }

    // Return data required to build the email
    return $final_data;

}

main();
?>