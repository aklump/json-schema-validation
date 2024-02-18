<?php

namespace AKlump\JsonSchema\Validation\Tests\Unit;

use AKlump\JsonSchema\Validation\JsonDecodeLossless;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\JsonSchema\Validation\JsonDecodeLossless
 */
class JsonDecodeMixedTest extends TestCase {

  public function dataFortestInvokeProvider() {
    $tests = [];
    $tests[] = ['null'];
    $tests[] = ['true'];
    $tests[] = ['99'];
    $tests[] = ['99.9'];
    $tests[] = ['"lorem"'];
    $tests[] = ['["foo","bar"]'];
    $tests[] = ['{"foo":"bar"}'];
    $tests[] = ['{"foo":[{"alpha":123}],"bar":[{"bravo":"123"}]}'];
    $tests[] = ['{"foo":[{"alpha":"null"}],"bar":[{"bravo":"true"}]}'];
    $tests[] = ['{"foo":[{"alpha":"false"}],"bar":[{"bravo":99.9}]}'];

    return $tests;
  }

  /**
   * @dataProvider dataFortestInvokeProvider
   */
  public function testInvoke(string $json) {
    $decoded = (new JsonDecodeLossless())($json);
    $this->assertSame($json, json_encode($decoded));
  }
}
