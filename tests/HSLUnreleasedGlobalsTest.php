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
use type Facebook\HackTest\{DataProvider, HackTest}; // @oss-enable
// @oss-disable: use type HackTestCase as HackTest;

// @oss-disable: <<Oncalls('hack')>>
final class HSLUnreleasedGlobalsTest extends HackTest {

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
}
