<?php

require_once('TextRazor.php');

$api_key= $YOUR_API_KEY_HERE;
$text = 'Barclays misled shareholders and the public about one of the biggest investments in the banks history, a BBC Panorama investigation has found.';

$textrazor = new TextRazor($api_key);

$textrazor->addExtractor('entities');

$response = $textrazor->analyze($text);

if (isset($response['response']['entities'])) {
	foreach ($response['response']['entities'] as $entity) {
		print($entity['entityId']);
		print(PHP_EOL);
	}
}


