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
use type HH\InvariantException as InvariantViolationException; // @oss-enable

final class Regex2Test extends PHPUnit_Framework_TestCase {

  public static function checkThrowsOnInvalidRegex<T>(
    (function (string, Regex2\Pattern): T) $fn,
  ): void {
    expect(() ==> $fn('foo', Regex2\re('I am not a regular expression')))
      ->toThrow(
        Regex2\Exception::class,
        null,
        'Invalid regex should throw an exception',
      );
  }

  public function testThrowsOnInvalidRegex(): void {
    self::checkThrowsOnInvalidRegex(($a, $b) ==> Regex2\match($a, Regex2\re($b)));
    self::checkThrowsOnInvalidRegex(($a, $b) ==> Regex2\matches($a, Regex2\re($b)));
    self::checkThrowsOnInvalidRegex(
      ($a, $b) ==> self::vecFromGenerator(Regex2\match_all($a, Regex2\re($b))));
    self::checkThrowsOnInvalidRegex(($a, $b) ==> Regex2\replace($a, Regex2\re($b), $a));
    self::checkThrowsOnInvalidRegex(($a, $b) ==> Regex2\split($a, Regex2\re($b)));
  }

  public static function checkThrowsOnInvalidOffset<T>(
    (function (string, Regex2\Pattern, int): T) $fn,
  ): void {
    expect(() ==> $fn('Hello', Regex2\re("/Hello/"), 5))->notToThrow();
    expect(() ==> $fn('Hello', Regex2\re("/Hello/"), -5))->notToThrow();
    expect(() ==> $fn('Hello', Regex2\re("/Hello/"), 6))->
      toThrow(
        InvariantViolationException::class,
        null,
        'Invalid offset should throw an exception',
      );
    expect(() ==> $fn('Hello', Regex2\re("/Hello/"), -6))->
      toThrow(
        InvariantViolationException::class,
        null,
        'Invalid offset should throw an exception',
      );
  }

  public function testThrowsOnInvalidOffset(): void {
    self::checkThrowsOnInvalidOffset(($a, $b, $i) ==> Regex2\match($a, Regex2\re($b), $i));
    self::checkThrowsOnInvalidOffset(($a, $b, $i) ==> Regex2\matches($a, Regex2\re($b), $i));
    self::checkThrowsOnInvalidOffset(
      ($a, $b, $i) ==> self::vecFromGenerator(Regex2\match_all($a, Regex2\re($b), $i)));
    self::checkThrowsOnInvalidOffset(($a, $b, $i) ==> Regex2\replace($a, Regex2\re($b), $a, $i));
  }

  public static function provideMatch(): varray<mixed> {
    return varray[
      tuple('abce', "/abc(.?)e(.*)/", 0, darray[
        0 => 'abce',
        1 => '',
        2 => '',
      ]),
      tuple('abcdef', "/abc(.?)e([fg])/", 0, darray[
        0 => 'abcdef',
        1 => 'd',
        2 => 'f',
      ]),
      tuple('abcdef', "/abc(?P<name>def)/", 0, darray[
        0 => 'abcdef',
        'name' => 'def',
        1 => 'def',
      ]),
      tuple('abcdef', "/def/", 1, darray[
        0 => 'def',
      ]),
      tuple('hello', "/(.?)/", 0, darray[
        0 => 'h',
        1 => 'h',
      ]),
      tuple('hello', "//", 0, darray[
        0 => '',
      ]),
      tuple('', "/(.?)/", 0, darray[
        0 => '',
        1 => '',
      ]),
      tuple('', "//", 0, darray[
        0 => '',
      ]),
    ];
  }

  /** @dataProvider provideMatch */
  public function testMatch(
    string $haystack,
    string $pattern_string,
    int $offset,
    darray<arraykey, string> $expected,
  ): void {
    $captures = Regex2\match($haystack, Regex2\re($pattern_string));
    $captures = expect($captures)->toNotBeNull();
    expect($captures)->toBeSame($expected);
  }

  public static function provideMatchNull(): varray<mixed> {
    return varray[
      tuple('a', "/abc(.?)e(.*)/", 0),
      tuple('abcdef', "/abc/", 1),
      tuple('', "/abc(.?)e(.*)/", 0),
    ];
  }

  /** @dataProvider provideMatchNull */
  public function testMatchNull(
    string $haystack,
    string $pattern_string,
    int $offset,
  ): void {
    expect(Regex2\match($haystack, Regex2\re($pattern_string), $offset))
      ->toBeNull();
  }

  public function testRecursion(): void {
    expect(() ==> Regex2\match(Str\repeat('a', 10000).'b', Regex2\re("/a*a*a*a*a$/")))
      ->toThrow(
        Regex2\Exception::class,
        'Backtrack limit error',
        'Should reach backtrack limit',
      );
  }

  public static function provideMatches(): varray<mixed> {
    return varray[
      tuple('a', "/abc(.?)e(.*)/", 0, false),
      tuple('', "/abc(.?)e(.*)/", 0, false),
      tuple('abce', "/abc(.?)e(.*)/", 0, true),
      tuple('abcdef', "/abc(.?)e([fg])/", 0, true),
      tuple('abcdef', "/abc/", 1, false),
      tuple('abcdef', "/def/", 1, true),
      tuple('Things that are equal in PHP', "/php/i", 2, true),
      tuple('is the web scripting', "/\\bweb\\b/i", 0, true),
      tuple('is the interwebz scripting', "/\\bweb\\b/i", 0, false),
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
      tuple('t1e2s3t', "/[a-z]/", 0, vec[
        dict[0 => 't'],
        dict[0 => 'e'],
        dict[0 => 's'],
        dict[0 => 't'],
      ]),
      tuple('t1e2s3t', "/[a-z](\d)?/", 0, vec[
        dict[0 => 't1', 1 => '1'],
        dict[0 => 'e2', 1 => '2'],
        dict[0 => 's3', 1 => '3'],
        dict[0 => 't'],
      ]),
      tuple('t1e2s3t', "/[a-z](?P<digit>\d)?/", 0, vec[
        dict[0 => 't1', 'digit' => '1', 1 => '1'],
        dict[0 => 'e2', 'digit' => '2', 1 => '2'],
        dict[0 => 's3', 'digit' => '3', 1 => '3'],
        dict[0 => 't'],
      ]),
      tuple('test', "/a/", 0, vec[]),
      tuple('t1e2s3t', "/[a-z]/", 3, vec[
        dict[0 => 's'],
        dict[0 => 't'],
      ]),
      tuple('', "//", 0, vec[
        dict[0 => ''],
      ]),
      tuple('', "/(.?)/", 0, vec[
        dict[0 => '', 1 => ''],
      ]),
      tuple('hello', "//", 0, vec[
        dict[0 => ''],
        dict[0 => ''],
        dict[0 => ''],
        dict[0 => ''],
        dict[0 => ''],
        dict[0 => ''],
      ]),
      tuple('hello', "/.?/", 0, vec[
        dict[0 => 'h'],
        dict[0 => 'e'],
        dict[0 => 'l'],
        dict[0 => 'l'],
        dict[0 => 'o'],
        dict[0 => ''],
      ]),
      tuple('hello', "//", 2, vec[
        dict[0 => ''],
        dict[0 => ''],
        dict[0 => ''],
        dict[0 => ''],
      ]),
      tuple('hello', "/.?/", 2, vec[
        dict[0 => 'l'],
        dict[0 => 'l'],
        dict[0 => 'o'],
        dict[0 => ''],
      ]),
      tuple("<b>bold text</b><a href=howdy.html>click me</a>", "/(<([\\w]+)[^>]*>)(.*)(<\\/\\2>)/",
        0, vec[
          dict[
            0 => "<b>bold text</b>",
            1 => "<b>",
            2 => "b",
            3 => "bold text",
            4 => "</b>",
          ],
          dict[
            0 => "<a href=howdy.html>click me</a>",
            1 => "<a href=howdy.html>",
            2 => "a",
            3 => "click me",
            4 => "</a>",
          ],
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

  public static function provideReplace(): varray<mixed> {
    return varray[
      tuple('abc', "#d#", '', 0, 'abc'),
      tuple('abcd', "#d#", 'e', 0, 'abce'),
      tuple('abcdcbabcdcbabcdcba', "#d#", 'D', 4, 'abcdcbabcDcbabcDcba'),
      tuple('abcdcbabcdcbabcdcba', "#d#", 'D', 19, 'abcdcbabcdcbabcdcba'),
      tuple('abcdcbabcdcbabcdcba', "#d#", 'D', -19, 'abcDcbabcDcbabcDcba'),
      tuple('abcdefghi', "#\D#", 'Z', -3, 'abcdefZZZ'),
      tuple('abcd6', "#d(\d)#", '\1', 0, 'abc6'),
      tuple('', "/(.?)/", 'A', 0, 'A'),
      tuple('', "//", 'A', 0, 'A'),
      tuple('hello', "/(.?)/", 'A', 0, 'AAAAAA'),
      tuple('hello', "//", 'A', 0, 'AhAeAlAlAoA'),
      tuple('hello', "//", 'A', 2, 'heAlAlAoA'),
      tuple('hello', "//", 'A', -3, 'heAlAlAoA'),
      tuple(
        'April 15, 2003',
        "/(\\w+) (\\d+), (\\d+)/i",
        '\${1}1,\$3',
        0,
        '${1}1,$3',
      ),
      tuple(
        'April 15, 2003',
        "/(\\w+) (\\d+), (\\d+)/i",
        "\${1}1,\$3",
        0,
        'April1,2003',
      ),
      tuple(
        Regex2\replace(
          "{startDate} = 1999-5-27",
          Regex2\re("/(19|20)(\\d{2})-(\\d{1,2})-(\\d{1,2})/"),
          "\\3/\\4/\\1\\2",
          0,
        ),
        "/^\\s*{(\\w+)}\\s*=/",
        "$\\1 =",
        0,
        "\$startDate = 5/27/1999",
      ),
      tuple('ooooo', "/.*/", 'a', 0, 'aa'),
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
      tuple('abc', "#d#", $x ==> $x[0], 0, 'abc'),
      tuple('abcd', "#d#", $x ==> 'xyz', 0, 'abcxyz'),
      tuple('abcdcbabcdcbabcdcba', "#d#", $x ==> 'D', 0, 'abcDcbabcDcbabcDcba'),
      tuple('hellodev42.prn3.facebook.com',
        "/dev(\d+)\.prn3(?<domain>\.facebook\.com)?/",
        $x ==> $x[1] . $x['domain'], 4,
        'hello42.facebook.com'),
      tuple('hellodev42.prn3.facebook.com',
        "/dev(\d+)\.prn3(?<domain>\.facebook\.com)?/",
        $x ==> $x[1] . $x['domain'], 6,
        'hellodev42.prn3.facebook.com'),
      tuple('<table ><table >', '@<table(\s+.*?)?>@s', $x ==> $x[1], 8, '<table > '),
      tuple('', "/(.?)/", $x ==> $x[1].'A', 0, 'A'),
      tuple('', "//", $x ==> $x[0].'A', 0, 'A'),
      tuple('hello', "/(.?)/", $x ==> $x[1].'A', 0, 'hAeAlAlAoAA'), // unintuitive, but consistent with preg_replace_callback
      tuple('hello', "//", $x ==> $x[0].'A', 0, 'AhAeAlAlAoA'),
      tuple('@[12345:67890:janedoe]', "/@\[(\d*?):(\d*?):([^]]*?)\]/",
        ($x ==> Str\repeat(' ', 4 + Str\length($x[1]) + Str\length($x[2])) . $x[3] . ' '),
        0, '              janedoe '),
      tuple('ooooo', "/.*/", $x ==> 'a', 0, 'aa'),
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
      tuple('', "/x/", null, vec['']),
      tuple('hello world', "/x/", null, vec['hello world']),
      tuple('hello world', "/x/", 2, vec['hello world']),
      tuple('hello world', "/\s+/", null, vec['hello', 'world']),
      tuple('  hello world  ', "/\s+/", null, vec['', 'hello', 'world', '']),
      tuple('  hello world  ', "/\s+/", 2, vec['', 'hello world  ']),
      tuple('  hello world  ', "/\s+/", 3, vec['', 'hello', 'world  ']),
      tuple('', "/(.?)/", null, vec['', '']),
      tuple('', "//", null, vec['', '']),
      tuple("string", "/(.?)/", null, vec['', '', '', '', '', '', '', '']),
      tuple("string", "//", null, vec['', 's', 't', 'r', 'i', 'n', 'g', '']),
      tuple("string", "/(.?)/", 3, vec['', '', 'ring']),
      tuple("string", "//", 3, vec['', 's', 'tring']),
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
    expect(() ==> Regex2\split('hello world', Regex2\re("/x/"), 1))
      ->toThrow(InvariantViolationException::class);
  }
}
