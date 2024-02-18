<?php

namespace AKlump\JsonSchema\Validation\Tests\Unit;

use AKlump\JsonSchema\Validation\GetPropertyDefaults;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\JsonSchema\Validation\GetPropertyDefaults
 */
class GetPropertyDefaultsTest extends TestCase {

  public function testDefaultsNotProvided() {
    $json = '{"$id":"http://example.com/example.json","type":"object","properties":{"stringProperty":{"type":"string"},"integerProperty":{"type":"integer"},"numberProperty":{"type":"number"},"objectProperty":{"type":"object"},"arrayProperty":{"type":"array"},"booleanProperty":{"type":"boolean"},"nullProperty":{"type":"null"}},"required":["stringProperty","integerProperty","numberProperty","objectProperty","arrayProperty","booleanProperty","nullProperty"]}';
    $defaults = (new GetPropertyDefaults())($json);
    $this->assertSame('', $defaults['stringProperty']);
    $this->assertSame(0, $defaults['integerProperty']);
    $this->assertSame(0, $defaults['numberProperty']);
    $this->assertEquals(new \stdClass(), $defaults['objectProperty']);
    $this->assertSame([], $defaults['arrayProperty']);
    $this->assertSame(FALSE, $defaults['booleanProperty']);
    $this->assertSame(NULL, $defaults['nullProperty']);
  }

  public function testIvokeWithNestedProperties() {
    $json = '{"$schema":"http://json-schema.org/draft-07/schema","type":"object","properties":{"apple":{"type":"object","properties":{"banana":{"type":"string","default":"yellow"},"cherry":{"type":"object","properties":{"date":{"type":"object","properties":{"elderberry":{"type":"string","default":"purple"},"fig":{"type":"object","properties":{"grape":{"type":"object","properties":{"huckleberry":{"type":"string","default":"blue"},"indianPlum":{"type":"string","default":"red"}},"required":["huckleberry","indianPlum"]}},"required":["grape"]}},"required":["elderberry","fig"]}},"required":["date"]}},"required":["banana","cherry"]}},"required":["apple"]}';
    $defaults = (new GetPropertyDefaults())($json);
    $this->assertSame('yellow', $defaults['apple']['banana']);
    $this->assertSame('purple', $defaults['apple']['cherry']['date']['elderberry']);
    $this->assertSame('blue', $defaults['apple']['cherry']['date']['fig']['grape']['huckleberry']);
    $this->assertSame('red', $defaults['apple']['cherry']['date']['fig']['grape']['indianPlum']);
  }

  public function testAssertParentsKeyDoesNotBleedThrough() {
    $json = '{"$schema":"http://json-schema.org/draft-07/schema#","type":"object","properties":{"name":{"type":"string","default":"John"},"favorites":{"type":"object","properties":{"animals":{"type":"array","default":["dog","cat"]}}}}}';
    $defaults = (new GetPropertyDefaults())($json);
    $this->assertArrayNotHasKey('parents', $defaults);
  }

  public function testInvoke() {
    $json = '{"type":"object","properties":{"prop1":{"type":"string","default":"test"},"prop2":{"type":"string","default":"sunlight"}}}';
    $defaults = (new GetPropertyDefaults())($json);
    $this->assertSame('test', $defaults['prop1']);
    $this->assertSame('sunlight', $defaults['prop2']);
  }

}
