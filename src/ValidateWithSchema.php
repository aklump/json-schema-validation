<?php

namespace AKlump\JsonSchema;

use InvalidArgumentException;
use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Exceptions\SchemaException;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Validator;

final class ValidateWithSchema {

  protected array $problems = [];

  protected array $schemaDirs = [];

  protected string $schema;

  private ValidationResult $result;

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

  public function __invoke($data): array {
    $validator = $this->getValidator();
    try {
      $this->result = $validator->validate($data, $this->schema);
      if ($this->result->hasError()) {
        $error = $this->result->error();
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

  /**
   * @return \Opis\JsonSchema\ValidationResult
   */
  public function getResult(): ValidationResult {
    return $this->result;
  }

  /**
   * Retrieves the Validator instance, creating it if necessary.
   *
   * This method initializes the Validator, configures it to resolve relative paths
   * for schemas using registered schema directories, and ensures the instance
   * is reused for subsequent invocations.  The validator instance is lazy
   * initialized and cached for performance.
   *
   * @return Validator The Validator instance used for JSON schema validation.
   *   You may manipulate the validator before calling __invoke() if desired.
   */

  public function getValidator(): Validator {
    if (!isset($this->validator)) {
      // @url https://opis.io/json-schema/2.x/php-validator.html
      $this->validator = new Validator();
      // This is so we can resolve relative paths, like this:
      // "$ref": "./_foo.schema.json#/lorem/ipsum
      foreach ($this->schemaDirs as $schema_dir) {
        $this->validator->resolver()->registerPrefix('schema:///', $schema_dir);
      }
    }

    return $this->validator;
  }

  protected function addSchemaDirectory(string $dir): void {
    if (!file_exists($dir)) {
      throw new InvalidArgumentException(sprintf('$dir does not exist: %s', $dir));
    }
    $this->schemaDirs[] = $dir;
  }

}
