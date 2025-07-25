<?php

namespace AKlump\JsonSchema\Tests\Unit;

use AKlump\JsonSchema\JsonDecodeLossless;
use AKlump\JsonSchema\Tests\Unit\TestingTraits\TestWithFilesTrait;
use AKlump\JsonSchema\ValidateWithSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\JsonSchema\ValidateWithSchema
 * @uses   \AKlump\JsonSchema\JsonDecodeLossless
 */
final class ValidateWithSchemaTest extends TestCase {

  use TestWithFilesTrait;

  public function testCanManipulateValidator() {
    $schema = '{"type":"string","minLength":3}';
    $validate = new ValidateWithSchema($schema);
    $validator = $validate->getValidator();
    $max_errors = $validator->getMaxErrors();
    $validator->setMaxErrors($max_errors + 1);
    $this->assertSame($validator, $validate->getValidator());
    $this->assertSame($max_errors + 1, $validator->getMaxErrors());
  }

  public function testInvalidSchemaReturnsProblem() {
    $invalid_json = '{"title":"Nursery Rhyme","type":"bogus"}';
    $validate = new ValidateWithSchema($invalid_json);
    $problems = $validate(['foo']);
    $this->assertCount(1, $problems);
    $this->assertSame('type contains invalid json type: bogus > {"title":"Nursery Rhyme","type":"bogus"}', $problems[0]);
  }

  public function testNonExistentDirectoryThrows() {
    $directory = $this->getTestFileFilepath('bogus/');
    $this->expectException(\InvalidArgumentException::class);
    new ValidateWithSchema('', $directory);
  }

  public function testGetResultReturnsExpectedErrors() {
    $schema_path = $this->getTestFileFilepath('poem.schema.json');
    $schema_json = file_get_contents($schema_path);
    $directory = $this->getTestFilesDirectory();
    $validate = new ValidateWithSchema($schema_json, $directory);
    $json = '{"verseCount":2,"rhymeScheme":"AABB","originCountry":"England"}';
    $data = (new JsonDecodeLossless())($json);
    $validate($data);
    $result = $validate->getResult();
    $this->assertTrue($result->hasError());
    $this->assertFalse($result->isValid());
  }

  public function testInvalidDataReturnsExpectedErrors() {
    $schema_path = $this->getTestFileFilepath('poem.schema.json');
    $schema_json = file_get_contents($schema_path);
    $directory = $this->getTestFilesDirectory();
    $validate = new ValidateWithSchema($schema_json, $directory);
    $json = '{"verseCount":2,"rhymeScheme":"AABB","originCountry":"England"}';
    $data = (new JsonDecodeLossless())($json);
    $problems = $validate($data);
    $this->assertCount(1, $problems);
    $this->assertStringContainsString('required properties (title) are missing', $problems[0]);
  }

  public function testValidDataInvokeNoProblems() {
    $schema_path = $this->getTestFileFilepath('poem.schema.json');
    $schema_json = file_get_contents($schema_path);
    $directory = $this->getTestFilesDirectory();
    $validate = new ValidateWithSchema($schema_json, $directory);
    $json = '{"title":"Twinkle Twinkle Little Star","verseCount":2,"rhymeScheme":"AABB","originCountry":"England"}';
    $data = (new JsonDecodeLossless())($json);
    $problems = $validate($data);
    $this->assertCount(0, $problems);
    $result = $validate->getResult();
    $this->assertFalse($result->hasError());
    $this->assertTrue($result->isValid());
  }
}
