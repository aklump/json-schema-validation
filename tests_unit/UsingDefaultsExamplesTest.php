<?php

namespace AKlump\JsonSchema\Tests\Unit;

use PHPUnit\Framework\TestCase;

final class UsingDefaultsExamplesTest extends TestCase {

  /**
   * @coversNothing
   */
  public function testDocumentationExamples() {
    $defaults = [
      'greeting' => 'Hello, World!',
    ];

    $dataset = [];
    $dataset = array_replace_recursive($defaults, $dataset);
    $this->assertSame('Hello, World!', $dataset['greeting']);

    $dataset = ['greeting' => 'Yo, dude!'];
    $dataset = array_replace_recursive($defaults, $dataset);
    $this->assertSame('Yo, dude!', $dataset['greeting']);

    $dataset = ['greeting' => ''];
    $dataset = array_replace_recursive($defaults, $dataset);
    $this->assertSame('', $dataset['greeting']);
  }

}
