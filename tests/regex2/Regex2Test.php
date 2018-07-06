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

use namespace HH\Lib\{C, Experimental\Regex2, Str, Vec};

use function Facebook\FBExpect\expect;
use type HH\InvariantException as InvalidRegexException; // @oss-enable
use type HH\InvariantException as InvariantViolationException; // @oss-enable

final class Regex2Test extends PHPUnit_Framework_TestCase {

  public static function checkThrowsOnInvalid<T>(
    (function (string, Regex2\Pattern): T) $fn,
  ): void {
    expect(() ==> $fn('foo', Regex2\re('I am not a regular expression')))
      ->toThrow(
        InvalidRegexException::class,
        null,
        'Invalid regex should throw an exception',
      );
  }

  public function testThrowsOnInvalid(): void {
    self::checkThrowsOnInvalid(($a, $b) ==> Regex2\match($a, Regex2\re($b)));
    self::checkThrowsOnInvalid(($a, $b) ==> Regex2\matches($a, Regex2\re($b)));
    self::checkThrowsOnInvalid(
      ($a, $b) ==> self::vecFromGenerator(Regex2\match_all($a, Regex2\re($b))));
    self::checkThrowsOnInvalid(($a, $b) ==> Regex2\replace($a, Regex2\re($b), $a));
    self::checkThrowsOnInvalid(($a, $b) ==> Regex2\split($a, Regex2\re($b)));
  }

  public function testMatch(): void {
    $captures = Regex2\match('a', Regex2\re('/abc(.?)e(.*)/'));
    expect($captures)->toBeNull();

    $captures = Regex2\match('abce', Regex2\re('/abc(.?)e(.*)/'));
    $captures = expect($captures)->toNotBeNull();
    expect($captures)->toBeSame(darray[
      0 => 'abce',
      1 => '',
      2 => '',
    ]);

    $captures = Regex2\match('abcdef', Regex2\re('/abc(.?)e([fg])/'));
    $captures = expect($captures)->toNotBeNull();
    expect($captures)->toBeSame(darray[
      0 => 'abcdef',
      1 => 'd',
      2 => 'f',
    ]);

    $captures = Regex2\match('abcdef', Regex2\re('/abc(?P<name>def)/'));
    $captures = expect($captures)->toNotBeNull();
    expect($captures[0])->toBeSame('abcdef');
    expect($captures['name'])->toBeSame('def');
    expect($captures[1])->toBeSame('def');

    $captures = Regex2\match('abcdef', Regex2\re('/abc/'), 1);
    expect($captures)->toBeNull();

    $captures = Regex2\match('abcdef', Regex2\re('/def/'), 1);
    $captures = expect($captures)->toNotBeNull();
    expect($captures)->toBeSame(darray[
      0 => 'def',
    ]);
  }

  public function testRecursion(): void {
    expect(() ==> Regex2\match(Str\repeat('a', 10000).'b', Regex2\re('/a*a*a*a*a$/')))
      ->toThrow(
        InvalidRegexException::class,
        'Backtrack limit error',
        'Should reach backtrack limit',
      );
  }

  public static function provideMatches(): varray<mixed> {
    return varray[
      tuple('a', '/abc(.?)e(.*)/', 0, false),
      tuple('abce', '/abc(.?)e(.*)/', 0, true),
      tuple('abcdef', '/abc(.?)e([fg])/', 0, true),
      tuple('abcdef', '/abc/', 1, false),
      tuple('abcdef', '/def/', 1, true),
    ];
  }

  /** @dataProvider provideMatches */
  public function testMatches(
    string $haystack,
    string $pattern_string,
    int $offset,
    bool $expected,
  ): void {
    expect(Regex2\matches($haystack, Regex2\re($pattern_string), $offset))
      ->toBeSame($expected);
  }

  public static function provideMatchAll(): varray<mixed> {
    return varray[
      tuple('t1e2s3t', '/[a-z]/', 0, vec[
        dict[0 => 't'],
        dict[0 => 'e'],
        dict[0 => 's'],
        dict[0 => 't'],
      ]),
      tuple('t1e2s3t', '/[a-z](\d)?/', 0, vec[
        dict[0 => 't1', 1 => '1'],
        dict[0 => 'e2', 1 => '2'],
        dict[0 => 's3', 1 => '3'],
        dict[0 => 't'],
      ]),
      tuple('t1e2s3t', '/[a-z](?P<digit>\d)?/', 0, vec[
        dict[0 => 't1', 'digit' => '1', 1 => '1'],
        dict[0 => 'e2', 'digit' => '2', 1 => '2'],
        dict[0 => 's3', 'digit' => '3', 1 => '3'],
        dict[0 => 't'],
      ]),
      tuple('test', '/a/', 0, vec[]),
      tuple('t1e2s3t', '/[a-z]/', 3, vec[
        dict[0 => 's'],
        dict[0 => 't'],
      ]),
    ];
  }

  public static function vecFromGenerator(
    \Generator<int, Regex2\Match, void> $generator
  ): vec<dict<arraykey, mixed>> {
    return Vec\map($generator, $match ==> Shapes::toDict($match));
  }

  /** @dataProvider provideMatchAll */
  public function testMatchAll(
    string $haystack,
    string $pattern_string,
    int $offset,
    vec<dict<arraykey, mixed>> $expected,
  ): void {
    expect(self::vecFromGenerator(
      Regex2\match_all($haystack, Regex2\re($pattern_string), $offset)))
      ->toBeSame($expected);
  }

// TODO(T19708752): Add backreferencing test case after implementing, e.g.
// expect(Regex2\replace('abcd6', Regex2\re('#d(\d)#'), '\1'))->toBeSame('abc6');
  public static function provideReplace(): varray<mixed> {
    return varray[
      tuple('abc', '#d#', '', 0, 'abc'),
      tuple('abcd', '#d#', 'e', 0, 'abce'),
      tuple('abcdcbabcdcbabcdcba', '#d#', 'D', 4, 'abcdcbabcDcbabcDcba'),
    ];
  }

  /** @dataProvider provideReplace */
  public function testReplace(
    string $haystack,
    string $pattern_string,
    string $replacement,
    int $offset,
    string $expected,
  ): void {
    expect(Regex2\replace($haystack, Regex2\re($pattern_string), $replacement, $offset))
      ->toBeSame($expected);
  }

  public static function provideReplaceWith(): varray<mixed> {
    return varray[
      tuple('abc', '#d#', $x ==> $x[0], 0, 'abc'),
      tuple('abcd', '#d#', $x ==> 'xyz', 0, 'abcxyz'),
      tuple('abcdcbabcdcbabcdcba', '#d#', $x ==> 'D', 0, 'abcDcbabcDcbabcDcba'),
      tuple('hellodev42.prn3.facebook.com',
        '/dev(\d+)\.prn3(?<domain>\.facebook\.com)?/',
        $x ==> $x[1] . $x['domain'], 4,
        'hello42.facebook.com'),
      tuple('hellodev42.prn3.facebook.com',
        '/dev(\d+)\.prn3(?<domain>\.facebook\.com)?/',
        $x ==> $x[1] . $x['domain'], 6,
        'hellodev42.prn3.facebook.com'),
    ];
  }

  /** @dataProvider provideReplaceWith */
  public function testReplaceWith(
    string $haystack,
    string $pattern_string,
    (function(Regex2\Match): string) $replace_func,
    int $offset,
    string $expected,
  ): void {
    expect(Regex2\replace_with($haystack, Regex2\re($pattern_string), $replace_func, $offset))
      ->toBeSame($expected);
  }

  public static function provideSplit(): varray<mixed> {
    return varray[
      tuple('', '/x/', null, vec['']),
      tuple('hello world', '/x/', null, vec['hello world']),
      tuple('hello world', '/\s+/', null, vec['hello', 'world']),
      tuple('  hello world  ', '/\s+/', null, vec['', 'hello', 'world', '']),
      tuple('  hello world  ', '/\s+/', 2, vec['', 'hello world  ']),
      tuple('  hello world  ', '/\s+/', 3, vec['', 'hello', 'world  ']),
    ];
  }

  /** @dataProvider provideSplit */
  public function testSplit(
    string $haystack,
    string $pattern_string,
    ?int $limit,
    vec<string> $expected,
  ): void {
    expect(Regex2\split($haystack, Regex2\re($pattern_string), $limit))
      ->toBeSame($expected);
  }

  public function testSplitInvalidLimit(): void {
    expect(() ==> Regex2\split('hello world', Regex2\re('/x/'), 1))
      ->toThrow(\InvariantViolationException::class);
  }
}
