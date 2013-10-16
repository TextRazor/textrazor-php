<?php
require_once('TextRazor.php');

$api_key='DEMO';

$textrazor = new TextRazor($api_key);

$textrazor_params=array(
	'extractors'=>'words,entities',
	'apiKey'=>'DEMO',
        'text'=>'Barclays misled shareholders and the public about one of the biggest investments in the banks history, a BBC Panorama investigation has found.'
);

$json_reply = $textrazor->sendPOST($textrazor_params);
print($json_reply);

foreach ($json_reply['response']['entities'] as $entity) {
	print($entity['entityId']);
	print(PHP_EOL);
}

foreach ($json_reply['response']['entities'] as &$entity) {
	print($entity['entityId']);
	foreach ($json_reply['response']['entities'] as $entity2) {
	$entity['other'] = &$entity2;
	}

	print(PHP_EOL);
}

print('JO');

foreach ($json_reply['response']['entities'] as $entity) {
print('ENT2'); print(PHP_EOL);
print($entity['other']); print(PHP_EOL);

}


