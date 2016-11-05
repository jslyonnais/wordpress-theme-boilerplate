<?php
// Include wordpress config
require_once ( $_SERVER["DOCUMENT_ROOT"] . "/wp-config.php" );

// Include all services files

foreach ( glob('./includes/*.php') as $file ) {
    include $file;
}

// Resigster to newsletter form
if ( $_GET["action"] == "subscribe" ) {
	$postBackSuccess = subscribe();
    $message = mailchimpReturn($postBackSuccess);

	print json_encode(array("isSuccess" => true));
}

else if ( $_GET["action"] == "demo" ) {
    $register = requestDemo($_POST);
	print json_encode(array("isSuccess" => true));
}


else {
	return die();
} ?>
