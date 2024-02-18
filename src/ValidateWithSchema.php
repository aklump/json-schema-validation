<?php

namespace AKlump\JsonSchema;

use InvalidArgumentException;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Exceptions\SchemaException;

final class ValidateWithSchema {

  protected array $problems = [];

  protected array $schemaDirs = [];

  protected string $schema;

  /**
   * Constructs a new instance of the class.
   *
   * @param string $schema_json The JSON schema file path or content.
   * @param array $schema_directories
   *   An array of filepaths where schemas are located.  This is used for
   *   resolving $ref references to partial schemas.
   */
  public function __construct(string $schema_json, array $schema_directories = []) {
    $this->schema = $schema_json;
    foreach ($schema_directories as $schema_directory) {
      $this->addSchemaDirectory($schema_directory);
    }
  }

  protected function getValidator(): \Opis\JsonSchema\Validator {
    // @url https://opis.io/json-schema/2.x/php-validator.html
    $validator = new \Opis\JsonSchema\Validator();

    // This is so we can resolve relative paths, like this:
    // "$ref": "./_foo.schema.json#/lorem/ipsum
    foreach ($this->schemaDirs as $schema_dir) {
      $validator->resolver()->registerPrefix('schema:///', $schema_dir);
    }

    //    $validator->setMaxErrors(5);
    return $validator;
  }

  protected function addSchemaDirectory(string $dir): void {
    if (!file_exists($dir)) {
      throw new InvalidArgumentException(sprintf('$dir does not exist: %s', $dir));
    }
    $this->schemaDirs[] = $dir;
  }

  public function __invoke($data): array {
    $validator = $this->getValidator();
    try {
      $result = $validator->validate($data, $this->schema);
      if ($result->hasError()) {
        $error = $result->error();
        $formatter = new ErrorFormatter();
        foreach ($formatter->format($error) as $path => $comments) {
          foreach ($comments as $comment) {
            $this->problems[] = sprintf('"%s" -- %s', $path, $comment);
          }
        }
      }
    }
    catch (SchemaException $exception) {
      $this->problems = [$exception->getMessage() . ' > ' . json_encode(json_decode($this->schema))];
    }

    return $this->problems ?? [];
  }

}
