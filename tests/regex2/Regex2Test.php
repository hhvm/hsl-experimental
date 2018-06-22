<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

/**
 * @emails oncall+hack
 */

use namespace HH\Lib\{C, Experimental\Regex2, Str};

use function Facebook\FBExpect\expect;
use type HH\InvariantException as InvalidRegexException; // @oss-enable

final class Regex2Test extends PHPUnit_Framework_TestCase {

  public static function checkThrowsOnInvalid<T>(
    (function (string, string): T) $fn,
  ): void {
    expect(() ==> $fn('foo', 'I am not a regular expression'))
      ->toThrow(
        InvalidRegexException::class,
        null,
        'Invalid regex should throw an exception',
      );
  }
}
