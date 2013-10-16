<?php

class TextRazor {
    private $apiKey;
    private $endPoint;
    private $secureEndPoint;

    private $enableEncryption;
    private $enableCompression;

    private $extractors;

    private $rules;

    private $cleanupHTML;

    private $languageOverride;

    private $dbpediaTypeFilters;
    private $freebaseTypeFilters;

    public function __construct($apiKey) {
        if(!is_string($apiKey)) {
            throw new Exception('TextRazor Error: Invalid API key');
        }

        if(!function_exists('curl_version')) {
            throw new Exception('TextRazor Error: TextRazor requires cURL support to be enabled on your PHP installation');
        }

	$this->apiKey = $apiKey;
	$this->endPoint = 'http://api.textrazor.com/';
	$this->secureEndPoint = 'https://api.textrazor.com/';

	$this->enableCompression = true; 
	$this->enableEncryption = false;  

        $this->extractors = array();
	$this->rules = '';
 	$this->cleanupHTML = false;
	$this->languageOverride = NULL;
    }

    public function setAPIKey($apiKey) {
	$this->apiKey = $apiKey;
    }

    public function setEndPoint($endPoint) {
	$this->endPoint = $endPoint;
    }

    public function setSecureEndPoint($endPoint) {
	$this->secureEndPoint = $endPoint;
    }

    public function setEnableCompression($enableCompression) {
	$this->enableCompression = $enableCompression;
    }

    public function setEnableEncryption($enableEncryption) {
	$this->enableEncryption = $enableEncryption;
    }

    public function setExtractors($extractors) {
	$this->extractors = $extractors;
    } 

    public function addExtractor($extractor) {
	array_push($extractors, $extractor);
    }

    public function setRules($rules) {
	$this->rules = $rules;
    }

    public function setCleanupHTML($cleanupHTML) {
	$this->cleanupHTML = $cleanupHTML;
    }

    public function setLanguageOverride($languageOverride) {
	$this->languageOverride = $languageOverride;
    }

    public function analyze($text) {
	$textRazorParams = array('extractors' => $extractors,
				'apiKey' => $apiKey,
 				'text' => $text);

	return sendPOST($textRazorParams);
    }

    public function sendPOST($textrazorParams) {
	$ch = curl_init();

	if ($enableEncryption) {
	    curl_setopt($ch, CURLOPT_URL, $this->secureEndPoint);
	}
	else {
	    curl_setopt($ch, CURLOPT_URL, $this->endPoint);
	}

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	if ($enableCompression) {
	    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
	}

	curl_setopt($ch, CURLOPT_POST, true );
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($textrazorParams));

	$reply = curl_exec ($ch);

	$rc = curl_errno($ch);
	if (0 != $rc) {
	    throw new Exception('TextRazor Error: Network problem connecting to TextRazor. CURL Error Code:' . $rc);
	}

	$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if (200 != $httpStatus) {
	    throw new Exception('TextRazor Error: TextRazor could not process this request. Message:' . $reply);	
	}

	curl_close ($ch);
	unset($ch);

	$jsonReply = json_decode($reply,true);

	return $jsonReply;
    }

}

