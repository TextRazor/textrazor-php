textrazor-php
=============

PHP SDK for the TextRazor Text Analytics API. 

TextRazor offers state-of-the-art natural language processing tools through a simple API, allowing you to build semantic technology into your applications in minutes.  

Hundreds of applications rely on TextRazor to understand unstructured text across a range of verticals, with use cases including social media monitoring, enterprise search, recommendation systems and ad targetting.  

Read more about the TextRazor API at [https://www.textrazor.com](https://www.textrazor.com).

Getting Started
===============

- Get a free API key from [https://www.textrazor.com](https://www.textrazor.com).

- Copy the file TextRazor.php into your project.

- Create an instance of the TextRazor object and start analyzing your text.

	```php
	require_once('TextRazor.php');
	
	TextRazorSettings::setApiKey($YOUR_API_KEY);

	$text = 'Barclays misled shareholders and the public about one of the biggest investments in the banks history, a BBC Panorama investigation has found.';

	$textrazor = new TextRazor();

	$textrazor->addExtractor('entities');

	$response = $textrazor->analyze($text);
	if (isset($response['response']['entities'])) {
		foreach ($response['response']['entities'] as $entity) {
			print($entity['entityId']);
			print(PHP_EOL);
		}
	}
	```

Error Handling
==============

The TextRazor PHP SDK throws an exception with a helpful error message in the case of bad inputs, TextRazor errors, or network errors.

Encoding
========

TextRazor expects all text to be encoded as UTF-8.  Please make sure all your content is encoded to valid UTF-8 before calling the "analyze" method, or the service will return an error.

Response
========

PHP makes it really easy to manipulate the JSON response from the server.  You can find more information about the various fields at [https://www.textrazor.com/documentation_rest](https://www.textrazor.com/documentation_rest).

If you have any questions please get in touch at support@textrazor.com
