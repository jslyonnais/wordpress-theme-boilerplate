<?php require_once($_SERVER["DOCUMENT_ROOT"] . "/wp-config.php");
# Include the Autoloader (see "Libraries" for install instructions)
require '../vendor/autoload.php';
use Mailgun\Mailgun;


function requestDemo($post) {
    if (!empty($post["email"])) { // Honeypot validation
        echo "<p class='alert alert--error'>" . "You have triggered the spam trap. Please refresh the page and ensure to use the form without any 3rd party program." . "</p>";
    } else {
        saveToDatabase($post);
        sendEmail($post);
    }
}


/*
 * Save data to database
 */
function saveToDatabase($post) {

    $db_host     = DB_HOST;
    $db_name     = DB_NAME;
    $db_username = DB_USER;
    $db_password = DB_PASSWORD;
    $db_table    = "request_demo";
    $connection  = new mysqli($db_host, $db_username, $db_password, $db_name);

    $firstname   = $post['firstname'];
    $lastname    = $post['lastname'];
    $phone       = $post['phone'];
    $email       = $post['noemail'];
    $company     = $post['company'];
    $job         = $post['job'];
    $date        = date("Y-m-d H:i:s");

    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }
    $sql = "INSERT INTO $db_table (id, date, firstname, lastname, phone, email, company, job) VALUES (NULL, '$date', '$firstname', '$lastname', '$phone', '$email', '$company', '$job')";

    if (!$connection->query($sql) === TRUE) {
        echo "Error: " . $sql . "<br>" . $connection->error;
    }
    $connection->close();
}

/*
 * Send data by email
 */

function sendEmail($post) {
    try {
        $mandrill = new Mandrill(MANDRILL_APIKEY);
        $lang     = $post["lang"];

        $html  = "<html><body>";
        $html .= "<table style='border-color: #666666' cellpadding='10'>";
        $html .= "<tr style='background: #eeeeee;'><td><strong>Name:</strong> </td><td>" . $post["firstname"] . $post["lastname"] . "</td></tr>";
        $html .= "<tr><td><strong>Company:</strong> </td><td>" . $post["company"] . "</td></tr>";
        $html .= "<tr><td><strong>Position:</strong> </td><td>" . $post["job"] . "</td></tr>";
        $html .= "<tr><td><strong>Email:</strong> </td><td>" . $post["noemail"] . "</td></tr>";
        $html .= "<tr><td><strong>Phone:</strong> </td><td>" . $post["phone"] . "</td></tr>";
        $html .= "</table>";
        $html .= "</body></html>";

        $message = array(
            "html"         => $html,
            "subject"      => "Request a demo - Simetryk",
            "from_email"   => "noreply@simetryk.com",
            "from_name"    => $post["firstname"] . " " . $post["lastname"],
            "to"           => array(
                array(
                    "email" => EMAIL,
                    "name"  => "Simetryk",
                    "type"  => "to"
                )
            ),
            "headers"      => array("Reply-To" => $post["noemail"]),
        );

        $async = false;
        $result = $mandrill->messages->send($message, $async);
        foreach($result as $key => $value) {
            $status = $value["status"];

            return $status;
        }
    } catch(Mandrill_Error $e) {
        // Mandrill errors are thrown as exceptions
        echo "<p class='alert alert--error'>" . get_class($e) . $e->getMessage() . "</p>";
        // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
        throw $e;
    } finally {
        if ($status == "sent") {
            if ($lang == "fr_CA") {
                $form_message = "Votre message a été envoyée avec succès! <br>Nous vous contacterons très bientôt.";
            } else {
                $form_message = "Your message has been successfully sent! <br>We'll get back to you very soon.";
            }
            echo "<p class='alert alert--success'>" . $form_message . "</p>";
        } else {
            if ($lang == "fr_CA") {
                $form_message = "Échec de l'envoi";
            } else {
                $form_message = "Fail to send message";
            }
            echo "<p class='alert alert--error'>" . $form_message . "</p>";
        }
    }
}

?>
