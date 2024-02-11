<?php

namespace AKlump\JsonSchema;

use Opis\JsonSchema\Exceptions\SchemaException;

final class Validator {

  private $problems = [];

  private $schemaDirs = [];

  /**
   * @param string $schema_json
   * @param string $basepath
   *   The directory for resolving relative path $ref values.
   */
  public function __construct(string $schema_json, string $directory = '') {
    $this->schema = $schema_json;
    $this->addSchemaDirectory($directory);
  }

  public function getValidator(): \Opis\JsonSchema\Validator {
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

  /**
   * Adds a directory in order to resolves relative schema path $refs.
   *
   * @param string $dir The directory path to be added.
   *
   * @return self Returns an instance of the object to allow for method chaining.
   * @throws \InvalidArgumentException If the directory does not exist.
   *
   * @code
   * "$ref": "./_foo.schema.json#/lorem/ipsum
   * @endcode
   *
   */
  private function addSchemaDirectory(string $dir): self {
    if (!file_exists($dir)) {
      throw new \InvalidArgumentException(sprintf('$dir does not exist: %s', $dir));
    }
    $this->schemaDirs[] = $dir;

    return $this;
  }

  public function isValid($data): bool {

    $validator = $this->getValidator();
    try {
      $result = $validator->validate($data, $this->schema);
      if ($result->hasError()) {
        $error = $result->error();
        $formatter = new \Opis\JsonSchema\Errors\ErrorFormatter();
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

    return empty($this->problems);
  }

  public function getProblems(): array {
    return $this->problems ?? [];
  }

}
