<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Time;

use namespace HH\Lib\{Math, Str};

/**
 * Returns an oqaque resource representing the timezone, or throws if the
 * offset is invalid.
 */
<<__Rx>>
function timezone_data_from_offset(int $utc_offset): resource {
  invariant_violation('future builtin');
}

/**
 * Returns an oqaque resource representing the timezone, or throws if the
 * name is invalid.
 */
<<__Rx>>
function timezone_data_from_name(string $name): resource {
  invariant_violation('future builtin');
}


// <<__Const>>
abstract class Timezone {

  /**
   * Returns a Timezone representing the given name, e.g. 'America/Los_Angeles'.
   */
  <<__Memoize>>
  final public static function fromName(
    string $name,
  ): NamedTimezone {
    return new NamedTimezone(
      timezone_data_from_name($name),
      $name,
    );
  }

  /**
   * Returns a Timezone representing the given offset from UTC, in minutes.
   *
   * Note that this can lead to unexpected results: for example, the same offset
   * may or may not refer to a particular timezone due to daylight savings time.
   */
  <<__Memoize>>
  final public static function fromRawOffset(
    int $offset_minutes,
  ): OffsetTimezone {
    return new OffsetTimezone(
      timezone_data_from_offset($offset_minutes),
      $offset_minutes,
    );
  }

  /**
   * Returns a Timezone representing the given UTC offset string.
   *
   * Note that this can lead to unexpected results: for example, the same offset
   * may or may not refer to a particular timezone due to daylight savings time.
   */
  final public static function fromRawOffsetString(
    string $offset_string,
  ): OffsetTimezone {
    $matches = dict[];
    $did_match = \preg_match(
      '/^(?P<sign>[+-])?(?P<hours>\d{2})(:?(?P<minutes>\d{2}))?$/',
      $offset_string,
      &$matches,
      \PREG_HACK_ARR,
    );
    invariant(
      $did_match === 1,
      'Malformed offset string: %s',
      $offset_string,
    );
    $hours = (int)$matches['hours'];
    $minutes = (int)idx($matches, 'minutes', '00');
    invariant(
      $minutes >= 0 && $minutes <= 59,
      'Expected minute value within [0, 59], got %d',
      $minutes,
    );
    $sign = idx($matches, 'sign') === '-' ? -1 : 1;
    $offset_minutes = $sign * ($hours * 60 + $minutes);
    invariant(
      $offset_minutes >= -840 && $offset_minutes <= 840,
      'Expected offset between -14:00 and +14:00, got %s',
      $offset_string,
    );
    return self::fromRawOffset($offset_minutes);
  }

  /**
   * Returns the Timezone representing UTC.
   */
  <<__Memoize>>
  final public static function UTC(): Timezone {
    return self::fromName('UTC');
  }

  /**
   * Returns the current system Timezone.
   *
   * Note that this may change during the request if the system's timezone is
   * overridden via `date_default_timezone_set()`.
   */
  final public static function System(): Timezone {
    return self::fromName(\date_default_timezone_get());
  }

  /**
   * Returns the string representation of this Timezone.
   */
  abstract public function toString(): string;

  protected function __construct(
    private resource $data,
  ): void {}

  public function __getData(): resource {
    return $this->data;
  }
}

// <<__Const>>
final class NamedTimezone extends Timezone {

  <<__Override>>
  protected function __construct(
    resource $data,
    private string $name,
  ): void {
    parent::__construct($data);
  }

  /**
   * Returns this Timezone's name.
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * Returns the string representation of this Timezone, i.e. its name.
   */
  public function toString(): string {
    return $this->getName();
  }
}

// <<__Const>>
final class OffsetTimezone extends Timezone {

  <<__Override>>
  protected function __construct(
    resource $data,
    private int $offsetMinutes,
  ): void {
    parent::__construct($data);
  }

  /**
   * Returns this Timezone's UTC offset, in minutes.
   */
  public function getOffsetMinutes(): int {
    return $this->offsetMinutes;
  }

  /**
   * Returns the string representation of this Timezone, i.e. its UTC offset
   * string.
   */
  public function toString(): string {
    $abs_minutes = Math\abs($this->offsetMinutes);
    return Str\format(
      '%s%02d:%02d',
      $this->offsetMinutes < 0 ? '-' : '+',
      Math\int_div($abs_minutes, 60),
      $abs_minutes % 60,
    );
  }
}
