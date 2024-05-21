<?php

namespace AKlump\JsonSchema\Tests\Unit;

use AKlump\JsonSchema\JsonDecodeLossless;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\JsonSchema\JsonDecodeLossless
 */
class JsonDecodeLosslessTest extends TestCase {

  public function testReadMeExampleCodeSnippet() {
    $data = ['foo' => [1, 2, 3]];
    $prepared_data = (new \AKlump\JsonSchema\JsonDecodeLossless())(json_encode($data));
    $this->assertIsObject($prepared_data);
    $this->assertSame(1, $prepared_data->foo[0]);
    $this->assertSame(2, $prepared_data->foo[1]);
    $this->assertSame(3, $prepared_data->foo[2]);
  }

  public function dataFortestInvokeProvider() {
    $tests = [];
    $tests[] = ['{"foo":{"bar":{}}}'];

    $tests[] = ['{}'];
    $tests[] = ['[]'];
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
