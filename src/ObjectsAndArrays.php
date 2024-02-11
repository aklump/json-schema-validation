<?php

namespace AKlump\JsonSchema;

/**
 * Recursively convert associative arrays to objects and keep indexed arrays.
 *
 * This class is necessary to sort out the disparity between PHP associative
 * arrays and the counterpart in Javascript which is an object.  Since JSON
 * Schema is expecting the latter this class makes resolving that effortless.
 *
 * @code
 * $data = json_decode($json);
 * $data = (new \AKlump\JsonSchema\ObjectsAndArrays())($data);
 * @endcode
 *
 * @url https://opis.io/json-schema/2.x/php-validator.html#data-types
 */
class ObjectsAndArrays {

  /**
   * @param array|object $data
   *   The data to normalize into a mixed object/array.
   *
   * @return bool|float|int|mixed|object|string
   */
  public function __invoke($data) {
    return self::_normalize($data);
  }

  private static function _normalize($value, bool $init = NULL) {
    if (is_null($init)) {
      $init = TRUE;
      $value = json_decode(json_encode($value), TRUE);
    }
    if (is_scalar($value)) {
      return $value;
    }
    elseif (is_array($value)) {
      $keys = array_keys($value);
      if ($keys !== array_keys($keys)) {
        $value = (object) $value;
      }
    }
    if (is_iterable($value) || $value instanceof \stdClass) {
      foreach ($value as &$v) {
        $v = self::_normalize($v, $init);
      }
    }

    return $value;
  }
}
