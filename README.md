# JSON Schema Validation

![Validation](images/validation.jpg)

![](https://badgen.net/packagist/php/aklump/dom-testing-selectors) ![](https://badgen.net/github/license/aklump/json-schema-validation)

Provides a suite of classes to help with JSON Schema validation.

## Validate Data Using JSON Schema

```php
$path_to_schema = 'json_schema/foo.schema.json';
$schema_json = file_get_contents($path_to_schema);

// Create the validator for this schema.
$validate = new \AKlump\JsonSchema\ValidateWithSchema($schema_json, dirname($path_to_schema));

// Validate some data against the schema.
$errors = $validate(["lorem", "ipsum"]);

$is_valid = empty($errors);

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
$schema_json = file_get_contents('greeting.schema.json');
$defaults = (new \AKlump\JsonSchema\GetPropertyDefaults())($schema_json);
// 'Hello, World!' === $defaults['greeting'];
```

### Using Default Values

In these examples, `$dataset` is meant to represent user-provided values, which may need to be filled in with default values.  **Be sure to heed the argument order for `array_replace_recursive()`.**

```php
/** @var array $dataset An empty dataset example */
$dataset = [];
$dataset = array_replace_recursive($defaults, $dataset);
// 'greeting' gets the default.
$dataset['greeting'] === 'Hello, World!';

/** @var array $dataset Greeting is already set. */
$dataset = ['greeting' => 'Yo, dude!']
$dataset = array_replace_recursive($defaults, $dataset);
// 'greeting keeps the non-default value.
$dataset['greeting'] === 'Yo, dude!';

/** @var array $dataset Greeting is an empty string, will not be replaced. */
$dataset = ['greeting' => '']
$dataset = array_replace_recursive($defaults, $dataset);
// 'greeting' is still an empty string.
$dataset['greeting'] === '';
```

## Relative Path $ref

```text
.
└── json_schema
    ├── _definitions.schema.json
    └── foo.schema.json
```

_\_definitions.schema.json_

```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "$defs": {
    "title": {
      "type": "string"
    }
  }
}
```

_foo.schema.json_

```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "object",
  "properties": {
    "title": {
      "$ref": "./_definitions.schema.json#/$defs/title"
    }
  }
}
```

Relative path references as shown above in _foo.schema.json_ for the _title_ property, are resolved based on the second argument to `ValidateWithSchema`:

```php
$path_to_schema = 'json_schema/foo.schema.json';
ValidateWithSchema(file_get_contents($path_to_schema), dirname($path_to_schema);
```

As you might expect these are equivalent:

```
"$ref": "_definitions.schema.json#/$defs/title"
"$ref": "./_definitions.schema.json#/$defs/title"
```

## Install with Composer

1. Because this is an unpublished package, you must define it's repository in
   your project's _composer.json_ file. Add the following to _composer.json_ in
   the `repositories` array:
   
    ```json
    {
     "type": "github",
     "url": "https://github.com/aklump/json-schema-validation"
    }
    ```
1. Require this package:
   
    ```
    composer require aklump/json-schema-validation:^0.0
    ```
