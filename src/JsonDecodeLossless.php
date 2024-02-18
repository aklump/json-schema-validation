<?php

namespace AKlump\JsonSchema;

/**
 * Class JsonDecodeLossless
 *
 * A class for decoding JSON strings and preserving all data types.  Normally
 * json_decode converts to all objects or all arrays.  This class preserves the
 * data types as they would be in Javascript so that the code sample below is
 * always true.
 *
 * @code
 * $decoded = (new JsonDecodeLossless())($json);
 * json_encode($decoded) === $json
 * @endcode
 */
class JsonDecodeLossless {

  public function __invoke(string $json) {
    return $this->decode(json_decode($json));
  }

  private function decode($value, bool $init = NULL) {
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
        $v = $this->decode($v, $init);
      }
    }

    return $value;
  }

}
