<!--
id: readme
tags: ''
-->

# JSON Schema Validation

![Validation](../../images/validation.jpg)

Provides a suite of classes to help with JSON Schema validation.

## Validate Data Using JSON Schema

```php
$schema_directory = '/foo/bar/json_schema';
$schema_json = file_get_contents($schema_directory . '/foo.schema.json');

// Create the validator for this schema.
$validate = new \AKlump\JsonSchema\ValidateWithSchema($schema_json, [$schema_directory]);

// Validate some data against the schema.
$errors = $validate(["lorem", "ipsum"]);

$is_valid = (bool) $errors;

// Display any errors.
if (!$is_valid) {
  foreach ($errors as $erro) {
    print $erro . PHP_EOL;
  }
}
```

## Preparing Data for Validation

In most cases you should prepare data using `\AKlump\JsonSchema\JsonDecodeLossless`. This has the result of casting to mixed objects and arrays. This is a quirk of using PHP with JSON Schema that doesn't think in terms of associative arrays, but objects--like Javascript.

```php
$data = ['foo' => [1, 2, 3]];
$data_to_validate = (new \AKlump\JsonSchema\JsonDecodeLossless())(json_encode($data));
// $data_to_validate->foo[0] === 1
```

## Extract Default Values from JSON Schema

```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "object",
  "properties": {
    "greeting": {
      "type": "string",
      "default": "Hello, World!"
    }
  }
}
```

```php
$schema_json = file_get_contents('greeting.schema.json')
$defaults = (new \AKlump\JsonSchema\GetPropertyDefaults())($schema_json);
// 'Hello, World!' === $defaults['greeting'];
```

{{ composer_install|raw }}
