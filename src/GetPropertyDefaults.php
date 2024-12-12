<?php

namespace AKlump\JsonSchema;

use AKlump\DefaultValue\DefaultValue;
use InvalidArgumentException;

class GetPropertyDefaults {

  /**
   * Extract default values for all properties in a JSON schema.
   *
   * This works on nested properties as well.
   *
   * @param string $schema_json The JSON representation of the schema.
   *
   * @return array The defaults extracted from the schema.  The keys are in JSON
   * pointer format e.g., "/foo" and "/foo/bar".
   */
  public function __invoke(string $schema_json): array {
    $schema = json_decode($schema_json, TRUE);
    if (!is_array($schema)) {
      throw new InvalidArgumentException('$schema_json cannot be parsed as valid JSON schema.');
    }

    $defaults = [];
    $this->getDefaults($schema, $defaults);

    return $defaults;
  }

  private function getDefaults($value, array &$defaults = [], array &$parents = []) {
    if (!array_key_exists('properties', $value)) {
      if (array_key_exists('default', $value)) {
        $value_to_assign = $value['default'];
      }
      else {
        $type = $value['type'] ?? 'null';
        $value_to_assign = DefaultValue::get($type);
      }
      $array = $this->createAssociativeArray($parents, $value_to_assign);
      $defaults = array_merge_recursive($defaults, $array);
    }
    else {
      foreach (($value['properties'] ?? []) as $name => $property) {
        $parents[] = $name;
        $this->getDefaults($property, $defaults, $parents);
        array_pop($parents);
      }
    }
  }

  private function createAssociativeArray($keys, $value = NULL) {
    if (empty($keys)) {
      return [];
    }
    elseif (count($keys) === 1) {
      return [$keys[0] => $value];
    }
    else {
      $key = array_shift($keys);

      return [$key => $this->createAssociativeArray($keys, $value)];
    }
  }

}
