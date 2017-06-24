# TextRazor/textrazor-php

**PHP SDK for the TextRazor Text Analytics API.** 

TextRazor offers state-of-the-art natural language processing tools through a simple API, allowing you to build semantic technology into your applications in minutes.  

Hundreds of applications rely on TextRazor to understand unstructured text across a range of verticals, with use cases including social media monitoring, enterprise search, recommendation systems and ad targeting.  

Read more about the TextRazor API at [https://www.textrazor.com](https://www.textrazor.com).

## Getting Started

- Get a free API key from [https://www.textrazor.com](https://www.textrazor.com/signup).

### The classic way

- Copy the file `TextRazor.php` into your project and load the class via `require_once 'TextRazor.php';`.

### The Composer way

```
composer require textrazor/textrazor-php
```

### Example

- Create an instance of the TextRazor object and start analyzing your text.

```php
    <?php
    require_once 'TextRazor.php'; // This is only required if you are **NOT** using Composer!
    
    TextRazorSettings::setApiKey('YOUR_API_KEY_HERE');
    
    $text = 'Barclays misled shareholders and the public about one of the biggest investments in the banks history, a BBC Panorama investigation has found.';
    
    $textrazor = new TextRazor();
    
    $textrazor->addExtractor('entities');
    
    $response = $textrazor->analyze($text);
    if (isset($response['response']['entities'])) {
        foreach ($response['response']['entities'] as $entity) {
            print_r($entity['entityId'] . PHP_EOL);
        }
    }
```

## Documentation

Please visit the [TextRazor PHP Reference](https://www.textrazor.com/docs/php).

## Error Handling

The TextRazor PHP SDK throws an exception with a helpful error message in the case of bad inputs, TextRazor errors, or network errors.

## Encoding

TextRazor expects all text to be encoded as UTF-8. Please make sure all your content is encoded to valid UTF-8 before calling the `analyze` method, or the service will return an error.

## Response

PHP makes it really easy to manipulate the JSON response from the server. You can find more information about the various fields at [https://www.textrazor.com/docs/rest]( https://www.textrazor.com/docs/rest).

## Appendix

If you have any queries please contact us at support@textrazor.com and we will get back to you promptly. We’d also love to hear from you if you have any ideas for improving the API or documentation.
