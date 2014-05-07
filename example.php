<?php

require_once('TextRazor.php');

$api_key= "$YOUR_API_KEY_HERE";
$text = 'LONDON - Barclays misled shareholders and the public about one of the biggest investments in the banks history, a BBC Panorama investigation has found.';

$textrazor = new TextRazor($api_key);

$textrazor->addExtractor('entities');
$textrazor->addExtractor('words');

$textrazor->addEnrichmentQuery("fbase:/location/location/geolocation>/location/geocode/latitude");
$textrazor->addEnrichmentQuery("fbase:/location/location/geolocation>/location/geocode/longitude");

$response = $textrazor->analyze($text);

if (isset($response['response']['entities'])) {
	foreach ($response['response']['entities'] as $entity) {
		print("Entity ID: " . $entity['entityId']);

		$entity_data = $entity['data'];

		if (!is_null($entity_data)) {
			print(PHP_EOL);
			print("Entity Latitude: " . $entity_data["fbase:/location/location/geolocation>/location/geocode/latitude"][0]);
			print(PHP_EOL);
			print("Entity Longitude: " . $entity_data["fbase:/location/location/geolocation>/location/geocode/longitude"][0]);
		}

		print(PHP_EOL);
	}
}
