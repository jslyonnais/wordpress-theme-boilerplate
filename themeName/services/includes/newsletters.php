<?php require_once($_SERVER["DOCUMENT_ROOT"] . "/wp-config.php");

function subscribe() {
    $apiKey = MAILCHIMP_APIKEY;
    $listId = MAILCHIMP_LIST;

    $memberId = md5(strtolower($_POST['noemail']));
    $dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
    $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listId . '/members/' . $memberId;

    $json = json_encode([
        'email_address' => $_POST['noemail'],
        'status'        => isset($_POST['status']) ? $_POST['status'] : "subscribed",
    ]);

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode;
}

function mailchimpReturn($httpCode) {
    $lang = $_POST['lang'];

    if ($httpCode == "200") {
        if ($lang == "fr_CA") {
            $message = "Merci de votre inscription.";
        } else {
            $message = "We successfully received your registration.";
        }
        echo "<p class='alert alert--success'>" . $message . "</p>";
    } else {
        if ($lang == "fr_CA") {
            $message = "Une erreur est survenue";
        } else {
            $message = "An error occurred";
        }
        echo "<p class='alert alert--error'>" . $message . "</p>";
    }
}
?>
