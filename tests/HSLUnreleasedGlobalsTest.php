<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use function Facebook\FBExpect\expect;
use type Facebook\HackTest\HackTestCase; // @oss-enable

/**
 * @emails oncall+hack
 */
final class HSLUnreleasedGlobalsTest extends HackTestCase {

  public static function providesIsHackArray(): varray<mixed> {
    return varray[
      varray[null, false],
      varray[true, false],
      varray[false, false],
      varray[42, false],
      varray['foo', false],
      varray[varray[], false],
      varray[varray['foo'], false],
      varray[Map {}, false],
      varray[Set {'foo'}, false],

      varray[dict[], true],
      varray[vec[], true],
      varray[keyset[], true],

      varray[dict['foo' => 'bar'], true],
      varray[vec[42], true],
      varray[keyset['foobar'], true],
    ];
  }

  <<DataProvider('providesIsHackArray')>>
  public function testIsHackArray(
    mixed $candidate,
    bool $expected,
  ): void {
    expect(is_hack_array($candidate))->toBeSame($expected);
  }

  public static function providesIsAnyArray(): varray<mixed> {
    return varray[
      tuple(null, false),
      tuple(true, false),
      tuple(false, false),
      tuple(42, false),
      tuple('foo', false),
      tuple(varray[], true),
      tuple(varray['foo'], true),
      tuple(Map {}, false),
      tuple(Set {'foo'}, false),

      tuple(dict[], true),
      tuple(vec[], true),
      tuple(keyset[], true),

      tuple(dict['foo' => 'bar'], true),
      tuple(vec[42], true),
      tuple(keyset['foobar'], true),
    ];
  }

  <<DataProvider('providesIsAnyArray')>>
  public function testIsAnyArray(
    mixed $val,
    bool $expected,
  ): void {
    expect(is_any_array($val))->toBeSame($expected);
  }
}
