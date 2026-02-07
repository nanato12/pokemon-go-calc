# PokemonGoCalc\IvExtractorClient\DefaultApi



All URIs are relative to https://pokemon-go-calc-4xmmm5azxa-an.a.run.app, except if the operation defines another base path.

| Method | HTTP request | Description |
| ------------- | ------------- | ------------- |
| [**extractIv()**](DefaultApi.md#extractIv) | **POST** /extract | スクリーンショットからポケモン名と個体値を抽出 |
| [**healthCheck()**](DefaultApi.md#healthCheck) | **GET** / | ヘルスチェック |


## `extractIv()`

```php
extractIv($image): \PokemonGoCalc\IvExtractorClient\Model\ExtractResponse
```

スクリーンショットからポケモン名と個体値を抽出

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new PokemonGoCalc\IvExtractorClient\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);
$image = '/path/to/file.txt'; // \SplFileObject | Pokemon GO スクリーンショット画像

try {
    $result = $apiInstance->extractIv($image);
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->extractIv: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

| Name | Type | Description  | Notes |
| ------------- | ------------- | ------------- | ------------- |
| **image** | **\SplFileObject****\SplFileObject**| Pokemon GO スクリーンショット画像 | |

### Return type

[**\PokemonGoCalc\IvExtractorClient\Model\ExtractResponse**](../Model/ExtractResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: `multipart/form-data`
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)

## `healthCheck()`

```php
healthCheck(): \PokemonGoCalc\IvExtractorClient\Model\HealthResponse
```

ヘルスチェック

### Example

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');



$apiInstance = new PokemonGoCalc\IvExtractorClient\Api\DefaultApi(
    // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
    // This is optional, `GuzzleHttp\Client` will be used as default.
    new GuzzleHttp\Client()
);

try {
    $result = $apiInstance->healthCheck();
    print_r($result);
} catch (Exception $e) {
    echo 'Exception when calling DefaultApi->healthCheck: ', $e->getMessage(), PHP_EOL;
}
```

### Parameters

This endpoint does not need any parameter.

### Return type

[**\PokemonGoCalc\IvExtractorClient\Model\HealthResponse**](../Model/HealthResponse.md)

### Authorization

No authorization required

### HTTP request headers

- **Content-Type**: Not defined
- **Accept**: `application/json`

[[Back to top]](#) [[Back to API list]](../../README.md#endpoints)
[[Back to Model list]](../../README.md#models)
[[Back to README]](../../README.md)
