<?php

namespace AKlump\JsonSchema;

use AKlump\DefaultValue\DefaultValue;

class Defaults {

  public function __invoke(string $schema_json): array {
    $value = json_decode($schema_json, TRUE);

    return $this->foo($value);
  }

  public function foo($value, array &$context = []) {
    foreach (($value['properties'] ?? []) as $name => $property) {
      if (!empty($property['default'])) {
        $context[$name] = $property['default'];
      }
      else {
        $type = $property['type'] ?? 'null';
        if (is_array($type)) {
          $type = array_shift($type);
        }
        $context[$name] = DefaultValue::get($type);
      }
    }

    return $context;
  }

}
