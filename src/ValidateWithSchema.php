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
   * @param string $schema_file_parent_dir
   *   The file directory that contains the JSON schema file, if the schema
   *   exists as a file.  This is used to resolved relative $ref paths in
   *   $schema_json.  It is optional and can be omitted if there are no relative
   *   paths used in $ref (references).
   */
  public function __construct(string $schema_json, string $schema_file_parent_dir = '') {
    $this->schema = $schema_json;
    if ($schema_file_parent_dir) {
      $this->addSchemaDirectory($schema_file_parent_dir);
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
