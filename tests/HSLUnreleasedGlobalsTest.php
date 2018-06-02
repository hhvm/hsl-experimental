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

/**
 * @emails oncall+hack
 */
final class HSLUnreleasedGlobalsTest extends PHPUnit_Framework_TestCase {

  public static function providesIsHackArray(): array<mixed> {
    /* HH_FIXME[2083]  */
    return array(
      /* HH_FIXME[2083]  */
      array(null, false),
      /* HH_FIXME[2083]  */
      array(true, false),
      /* HH_FIXME[2083]  */
      array(false, false),
      /* HH_FIXME[2083]  */
      array(42, false),
      /* HH_FIXME[2083]  */
      array('foo', false),
      /* HH_FIXME[2083]  */
      array(array(), false),
      /* HH_FIXME[2083]  */
      array(array('foo'), false),
      /* HH_FIXME[2083]  */
      array(Map {}, false),
      /* HH_FIXME[2083]  */
      array(Set {'foo'}, false),

      /* HH_FIXME[2083]  */
      array(dict[], true),
      /* HH_FIXME[2083]  */
      array(vec[], true),
      /* HH_FIXME[2083]  */
      array(keyset[], true),

      /* HH_FIXME[2083]  */
      array(dict['foo' => 'bar'], true),
      /* HH_FIXME[2083]  */
      array(vec[42], true),
      /* HH_FIXME[2083]  */
      array(keyset['foobar'], true),
    );
  }

  /** @dataProvider providesIsHackArray */
  public function testIsHackArray(
    mixed $candidate,
    bool $expected,
  ): void {
    expect(is_hack_array($candidate))->toEqual($expected);
  }

  public static function providesIsAnyArray(): array<mixed> {
    /* HH_FIXME[2083]  */
    return array(
      tuple(null, false),
      tuple(true, false),
      tuple(false, false),
      tuple(42, false),
      tuple('foo', false),
      /* HH_FIXME[2083]  */
      tuple(array(), true),
      /* HH_FIXME[2083]  */
      tuple(array('foo'), true),
      tuple(Map {}, false),
      tuple(Set {'foo'}, false),

      tuple(dict[], true),
      tuple(vec[], true),
      tuple(keyset[], true),

      tuple(dict['foo' => 'bar'], true),
      tuple(vec[42], true),
      tuple(keyset['foobar'], true),
    );
  }

  /** @dataProvider providesIsAnyArray */
  public function testIsAnyArray(
    mixed $val,
    bool $expected,
  ): void {
    expect(is_any_array($val))->toEqual($expected);
  }
}
