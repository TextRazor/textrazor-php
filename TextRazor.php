<?php

// http_build_query has its own ideas about how to serialize different types, so we use our own version here.
class TextRazorQueryBuilder {
    private $params = array();

    public function add($key, $value) {
			if (is_null($value)) {
				return;
			}
			else if (is_array($value)) {
				foreach ($value as $listItem) {
					$this->add($key, $listItem);
				}
			}
			else if (is_bool($value)) {
				$this->add($key, $value ? "true" : "false");
			}
			else {
				$this->params[] = urlencode($key) . '=' . urlencode($value);
			}
    }

    public function build() {
        return implode("&", $this->params);
    }
}

/**
* Represents global settings common to all TextRazor operations. Settings can be
* enabled here, or with each request.
*/
class TextRazorSettings {
    private static $apiKey;
    private static $endPoint = 'http://api.textrazor.com/';
    private static $secureEndPoint = 'https://api.textrazor.com/';
    private static $enableEncryption = true;
    private static $enableCompression = true;

    public static function setApiKey($apiKey) {
        if(!is_string($apiKey)) {
            throw new Exception('TextRazor Error: Invalid API Key');
        }

        self::$apiKey = $apiKey;
    }

    public static function getApiKey() {
        return self::$apiKey;
    }

    public static function setEndPoint($endPoint) {
        if(!is_string($endPoint)) {
            throw new Exception('TextRazor Error: Invalid HTTP Endpoint');
        }

        self::$endPoint = $endPoint;
    }

    public static function getEndPoint() {
        return self::$endPoint;
    }

    public static function setSecureEndPoint($endPoint) {
        if(!is_string($endPoint)) {
            throw new Exception('TextRazor Error: Invalid HTTPS Endpoint');
        }

        self::$secureEndPoint = $endPoint;
    }

    public static function getSecureEndPoint() {
        return self::$secureEndPoint;
    }

    public static function setEnableCompression($enableCompression) {
        if(!is_bool($enableCompression)) {
            throw new Exception('TextRazor Error: enableCompression must be a bool');
        }

        self::$enableCompression = $enableCompression;
    }

    public static function getEnableCompression() {
        return self::$enableCompression;
    }

    public static function setEnableEncryption($enableEncryption) {
        if(!is_bool($enableEncryption)) {
            throw new Exception('TextRazor Error: enableEncryption must be a bool');
        }

        self::$enableEncryption = $enableEncryption;
    }

    public static function getEnableEncryption() {
        return self::$enableEncryption;
    }
}

class TextRazorConnection {
    private $apiKey;
    private $endPoint;
    private $secureEndPoint;
    private $enableEncryption;
    private $enableCompression;

    function __construct($apiKey) {
        $this->apiKey = TextRazorSettings::getApiKey();
        $this->endPoint = TextRazorSettings::getEndPoint();
        $this->secureEndPoint = TextRazorSettings::getSecureEndpoint();
        $this->enableEncryption = TextRazorSettings::getEnableEncryption();
        $this->enableCompression = TextRazorSettings::getEnableCompression();

        if (isset($apiKey)) {
            $this->apiKey = $apiKey;
        }

        if(!is_string($this->apiKey)) {
			throw new Exception('TextRazor Error: Invalid API key');
		}

        if(!function_exists('curl_version')) {
            throw new Exception('TextRazor Error: TextRazor requires cURL support to be enabled on your PHP installation');
        }
    }

    public function setAPIKey($apiKey) {
        if(!is_string($apiKey)) {
            throw new Exception('TextRazor Error: Invalid API key');
        }

        $this->apiKey = $apiKey;
    }

    public function setEndPoint($endPoint) {
        if(!is_string($endPoint)) {
            throw new Exception('TextRazor Error: Invalid HTTP Endpoint');
        }

        $this->endPoint = $endPoint;
    }

    public function setSecureEndPoint($endPoint) {
        if(!is_string($endPoint)) {
            throw new Exception('TextRazor Error: Invalid HTTPS Endpoint');
        }

        $this->secureEndPoint = $endPoint;
    }

    public function setEnableCompression($enableCompression) {
        if(!is_bool($enableCompression)) {
            throw new Exception('TextRazor Error: enableCompression must be a bool');
        }

        $this->enableCompression = $enableCompression;
    }

    public function setEnableEncryption($enableEncryption) {
        if(!is_bool($enableEncryption)) {
            throw new Exception('TextRazor Error: enableEncryption must be a bool');
        }

        $this->enableEncryption = $enableEncryption;
    }

    public function sendRequest($textrazorParams, $path = '', $method = 'POST', $contentType = NULL) {
		$ch = curl_init();

        if ($this->enableEncryption) {
			curl_setopt($ch, CURLOPT_URL, $this->secureEndPoint . $path);
		}
		else {
			curl_setopt($ch, CURLOPT_URL, $this->endPoint . $path);
		}

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		if ($this->enableCompression) {
			curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
		}

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method );
		curl_setopt($ch, CURLOPT_POSTFIELDS, $textrazorParams);

        $headers = array();
        $headers[] = 'X-TextRazor-Key: ' . trim($this->apiKey);

        if ($contentType) {
            $headers[] = 'Content-Type: ' . $contentType;
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$reply = curl_exec ($ch);

		$rc = curl_errno($ch);
		if (0 != $rc) {
			throw new Exception('TextRazor Error: Network problem connecting to TextRazor. CURL Error Code:' . $rc);
		}

		$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (200 != $httpStatus) {
			throw new Exception('TextRazor Error: TextRazor returned HTTP code: ' . $httpStatus . ' Message:' . $reply);
		}

		curl_close ($ch);
		unset($ch);

		$jsonReply = json_decode($reply,true);

		return $jsonReply;
	}
}

class TextRazor extends TextRazorConnection {
	private $extractors = array();

	private $rules = NULL;

	private $cleanupHTML = false;
	private $languageOverride = NULL;

	private $dbpediaTypeFilters = array();
	private $freebaseTypeFilters = array();
    private $enrichmentQueries = array();
    private $allowOverlap = true;
    private $entityDictionaries = array();

    private $cleanupMode = NULL;
	private $cleanupReturnCleaned = false;
    private $cleanupReturnRaw = false;
    private $cleanupUseMetadata = false;

    private $downloadUserAgent = NULL;

    private $classifiers = array();

	public function __construct($apiKey = NULL) {
        parent::__construct($apiKey);
	}

	public function setExtractors($extractors) {
		if(!is_array($extractors)) {
			throw new Exception('TextRazor Error: extractors must be an array of strings');
		}

		$this->extractors = $extractors;
	}

	public function addExtractor($extractor) {
		if(!is_string($extractor)) {
			throw new Exception('TextRazor Error: extractor must be a string');
		}

		array_push($this->extractors, $extractor);
	}

    public function setClassifiers($classifiers) {
        if(!is_array($classifiers)) {
            throw new Exception('TextRazor Error: $classifiers must be an array of strings');
        }

        $this->classifiers = $classifiers;
    }

    public function addClassifier($classifier) {
        if(!is_string($classifier)) {
            throw new Exception('TextRazor Error: $classifier must be a string');
        }

        array_push($this->classifiers, $classifier);
    }

	public function setRules($rules) {
		if(!is_string($rules)) {
			throw new Exception('TextRazor Error: rules must be a string');
		}

		$this->rules = $rules;
	}

	public function setCleanupHTML($cleanupHTML) {
		if(!is_bool($cleanupHTML)) {
			throw new Exception('TextRazor Error: cleanupHTML must be a bool');
		}

		$this->cleanupHTML = $cleanupHTML;
	}

	public function setLanguageOverride($languageOverride) {
		if(!is_string($languageOverride)) {
			throw new Exception('TextRazor Error: languageOverride must be a string');
		}

		$this->languageOverride = $languageOverride;
	}

	public function setAllowOverlap($allowOverlap) {
		if (!is_bool($allowOverlap)) {
			throw new Exception('TextRazor Error: allowOverlap must be a bool');
		}

		$this->allowOverlap = $allowOverlap;
	}

    public function addEntityDictionary($dictionaryId) {
		if(!is_string($dictionaryId)) {
			throw new Exception('TextRazor Error: dictionaryId must be a string');
		}

		array_push($this->entityDictionaries, $dictionaryId);
	}

	public function addDbpediaTypeFilter($filter) {
		if(!is_string($filter)) {
			throw new Exception('TextRazor Error: filter must be a string');
		}

		array_push($this->dbpediaTypeFilters, $filter);
	}

	public function addFreebaseTypeFilter($filter) {
		if(!is_string($filter)) {
			throw new Exception('TextRazor Error: filter must be a string');
		}

		array_push($this->freebaseTypeFilters, $filter);
	}

	public function addEnrichmentQuery($query) {
		if(!is_string($query)) {
			throw new Exception('TextRazor Error: query must be a string');
		}

		array_push($this->enrichmentQueries, $query);
	}

    public function setCleanupMode($cleanupMode) {
        if(!is_string($cleanupMode)) {
            throw new Exception('TextRazor Error: Invalid Cleanup Mode');
        }

        $this->cleanupMode = $cleanupMode;
    }

    public function setCleanupReturnCleaned($cleanupReturnCleaned) {
        if(!is_bool($cleanupReturnCleaned)) {
            throw new Exception('TextRazor Error: cleanupReturnCleaned must be a bool');
        }

        $this->cleanupReturnCleaned = $cleanupReturnCleaned;
    }

    public function setCleanupReturnRaw($cleanupReturnRaw) {
        if(!is_bool($cleanupReturnRaw)) {
            throw new Exception('TextRazor Error: cleanupReturnRaw must be a bool');
        }

        $this->cleanupReturnRaw = $cleanupReturnRaw;
    }

    public function setCleanupUseMetadata($cleanupUseMetadata) {
        if(!is_bool($cleanupUseMetadata)) {
            throw new Exception('TextRazor Error: cleanupUseMetadata must be a bool');
        }

        $this->cleanupUseMetadata = $cleanupUseMetadata;
    }

    public function setDownloadUserAgent($downloadUserAgent) {
        if(!is_string($downloadUserAgent)) {
            throw new Exception('TextRazor Error: Invalid downloadUserAgent');
        }

        $this->downloadUserAgent = $downloadUserAgent;
    }

    private function buildRequest() {
		$builder = new TextRazorQueryBuilder();

		$builder->add('extractors', $this->extractors);
		$builder->add('cleanupHTML', $this->cleanupHTML);
		$builder->add('extractors', $this->extractors);
		$builder->add('rules', $this->rules);
		$builder->add('languageOverride', $this->languageOverride);

		$builder->add('entities.allowOverlap', $this->allowOverlap);
		$builder->add('entities.filterDbpediaTypes', $this->dbpediaTypeFilters);
		$builder->add('entities.filterFreebaseTypes', $this->freebaseTypeFilters);
		$builder->add('entities.enrichmentQueries', $this->enrichmentQueries);
        $builder->add('entities.dictionaries', $this->entityDictionaries);

        $builder->add('classifiers', $this->classifiers);

        $builder->add('cleanup.mode', $this->cleanupMode);
        $builder->add('cleanup.returnCleaned', $this->cleanupReturnCleaned);
        $builder->add('cleanup.returnRaw', $this->cleanupReturnRaw);
        $builder->add('cleanup.useMetadata', $this->cleanupUseMetadata);

        $builder->add('download.userAgent', $this->downloadUserAgent);

        return $builder;
    }

    public function analyzeUrl($url) {
		if(!is_string($url)) {
			throw new Exception('TextRazor Error: url must be a UTF8 encoded string');
		}

        $builder = $this->buildRequest();
        $builder->add('url', $url);

		return $this->sendRequest($builder->build());
	}

	public function analyze($text) {
		if(!is_string($text)) {
			throw new Exception('TextRazor Error: text must be a UTF8 encoded string');
		}

        $builder = $this->buildRequest();
        $builder->add('text', $text);

		return $this->sendRequest($builder->build());
	}
}

class DictionaryManager extends TextRazorConnection {
    public function __construct($apiKey = NULL) {
        parent::__construct($apiKey);
	}

    /**
    * Creates a new dictionary using properties provided in the dict $dictionaryProperties.
    * See the properties of class Dictionary for valid options.
    */
    public function createDictionary($id, $matchType=NULL, $caseInsensitive=NULL, $language=NULL) {
        $request = array();

        if(!is_string($id)) {
			throw new Exception('TextRazor Error: Custom Entity Dictionaries must have an ID.');
		}

        if (isset($matchType)) { $request["matchType"] = $matchType; }
        if (isset($caseInsensitive)) { $request["caseInsensitive"] = $caseInsensitive; }
        if (isset($language)) { $request["language"] = $language; }

        $encodedRequest = empty($request) ? "{}" : json_encode($request);

        return $this->sendRequest($encodedRequest, "/entities/" . $id, "PUT");
    }

    public function allDictionaries() {
        return $this->sendRequest("", "/entities/", "GET");
    }

    public function deleteDictionary($id) {
        if(!is_string($id)) {
            throw new Exception('TextRazor Error: Custom Entity Dictionaries must have an ID.');
        }

        return $this->sendRequest("", "/entities/" . $id, "DELETE");
    }

    public function getDictionary($id) {
        if(!is_string($id)) {
            throw new Exception('TextRazor Error: Custom Entity Dictionaries must have an ID.');
        }

        return $this->sendRequest("", "/entities/" . $id, "GET");
    }

    public function allEntries($id, $limit = NULL, $offset = NULL) {
        if(!is_string($id)) {
            throw new Exception('TextRazor Error: Custom Entity Dictionaries must have an ID.');
        }

        $url_params = array();

        if (isset($limit)) {
            $url_params["limit"] = $limit;
        }

        if (isset($offset)) {
            $url_params["offset"] = $offset;
        }

        return $this->sendRequest("", "/entities/" . $id . "/_all?" . http_build_query($url_params), "GET");
    }

    public function addEntries($id, $entries) {
        if(!is_array($entries)) {
            throw new Exception('TextRazor Error: Entries must be a List of dicts corresponding to properties of the new DictionaryEntry objects.');
        }

        if (empty($entries)) {
            throw new Exception('TextRazor Error: Array of new entries cannot be empty.');
        }

        return $this->sendRequest(json_encode($entries), "/entities/" . $id . "/", "POST");
    }

    public function getEntry($dictionary_id, $entry_id) {
        if(!is_string($dictionary_id)) {
            throw new Exception('TextRazor Error: Custom Entity Dictionaries must have an ID.');
        }

        if(!is_string($entry_id)) {
            throw new Exception('TextRazor Error: Custom Entity Dictionary Entries can only be retrieved by ID.');
        }

        return $this->sendRequest("", "/entities/" . $dictionary_id . "/" . $entry_id, "GET");
    }

    public function deleteEntry($dictionary_id, $entry_id) {
        if(!is_string($dictionary_id)) {
            throw new Exception('TextRazor Error: Custom Entity Dictionaries must have an ID.');
        }

        if(!is_string($entry_id)) {
            throw new Exception('TextRazor Error: Custom Entity Dictionary Entries can only be deleted by ID.');
        }

        return $this->sendRequest("", "/entities/" . $dictionary_id . "/" . $entry_id, "DELETE");
    }
}

class ClassifierManager extends TextRazorConnection {
    public function __construct($apiKey = NULL) {
        parent::__construct($apiKey);
	}

    public function createClassifier($classifierID, $categories) {
        $request = array();

        if(!is_string($classifierID)) {
			throw new Exception('TextRazor Error: Classifiers must have an ID.');
		}

        if(!is_array($categories)) {
            throw new Exception('TextRazor Error: $categories must be a List of dicts corresponding to properties of the new Category objects.');
        }

        if (empty($categories)) {
            throw new Exception('TextRazor Error: Array of new categories cannot be empty.');
        }

        return $this->sendRequest(json_encode($categories), "/categories/" . $classifierID, "PUT", "application/json");
    }

    public function createClassifierWithCSV($classifierID, $categoriesCSV) {
        $request = array();

        if(!is_string($classifierID)) {
			throw new Exception('TextRazor Error: Classifiers must have an ID.');
		}

        if(!is_string($categoriesCSV)) {
            throw new Exception('TextRazor Error: $categoriesCSV must be a String containing the contents of a csv file that defines a new classifier.');
        }

        return $this->sendRequest($categoriesCSV, "/categories/" . $classifierID, "PUT", "application/csv");
    }

    public function deleteClassifier($classifierID) {
        return $this->sendRequest("", "/categories/" . $classifierID, "DELETE");
    }

    public function allCategories($classifierID, $limit = NULL, $offset = NULL) {
        if(!is_string($classifierID)) {
            throw new Exception('TextRazor Error: Classifiers must have an ID.');
        }

        $url_params = array();

        if (isset($limit)) {
            $url_params["limit"] = $limit;
        }

        if (isset($offset)) {
            $url_params["offset"] = $offset;
        }

        return $this->sendRequest("", "/categories/" . $classifierID . "/_all?" . http_build_query($url_params), "GET");
    }

    public function deleteCategory($classifierID, $categoryID) {
        return $this->sendRequest("", "/categories/" . $classifierID . "/" . $categoryID, "DELETE");
    }

    public function getCategory($classifierID, $categoryID) {
        return $this->sendRequest("", "/categories/" . $classifierID . "/" . $categoryID, "GET");
    }
}
