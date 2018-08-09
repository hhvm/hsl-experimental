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

use namespace HH\Lib\{C, Experimental\Regex2, Regex, Str, Vec};

use function Facebook\FBExpect\expect;
use type HH\InvariantException as InvariantViolationException; // @oss-enable

final class Regex2Test extends PHPUnit_Framework_TestCase {

  public static function checkThrowsOnInvalidRegex<T>(
    (function (string, Regex\Pattern<shape(...)>): T) $fn,
  ): void {
    expect(() ==> $fn('foo', re"I am not a regular expression"))
      ->toThrow(
        Regex2\Exception::class,
        null,
        'Invalid regex should throw an exception',
      );
  }

  public function testThrowsOnInvalidRegex(): void {
    self::checkThrowsOnInvalidRegex(($a, $b) ==> Regex2\match($a, $b));
    self::checkThrowsOnInvalidRegex(($a, $b) ==> Regex2\matches($a, $b));
    self::checkThrowsOnInvalidRegex(
      ($a, $b) ==> self::vecFromGenerator(Regex2\match_all($a, $b)));
    self::checkThrowsOnInvalidRegex(($a, $b) ==> Regex2\replace($a, $b, $a));
    self::checkThrowsOnInvalidRegex(($a, $b) ==> Regex2\split($a, $b));
  }

  public static function checkThrowsOnInvalidOffset<T>(
    (function (string, Regex\Pattern<shape(...)>, int): T) $fn,
  ): void {
    expect(() ==> $fn('Hello', re"/Hello/", 5))->notToThrow();
    expect(() ==> $fn('Hello', re"/Hello/", -5))->notToThrow();
    expect(() ==> $fn('Hello', re"/Hello/", 6))->
      toThrow(
        InvariantViolationException::class,
        null,
        'Invalid offset should throw an exception',
      );
    expect(() ==> $fn('Hello', re"/Hello/", -6))->
      toThrow(
        InvariantViolationException::class,
        null,
        'Invalid offset should throw an exception',
      );
  }

  public function testThrowsOnInvalidOffset(): void {
    self::checkThrowsOnInvalidOffset(($a, $b, $i) ==> Regex2\match($a, $b, $i));
    self::checkThrowsOnInvalidOffset(($a, $b, $i) ==> Regex2\matches($a, $b, $i));
    self::checkThrowsOnInvalidOffset(
      ($a, $b, $i) ==> self::vecFromGenerator(Regex2\match_all($a, $b, $i)));
    self::checkThrowsOnInvalidOffset(($a, $b, $i) ==> Regex2\replace($a, $b, $a, $i));
  }

  public static function provideMatch(): varray<(string, Regex\Pattern<shape(...)>, int, darray<arraykey, string>)> {
    return varray[
      tuple('abce', re"/abc(.?)e(.*)/", 0, darray[
        0 => 'abce',
        1 => '',
        2 => '',
      ]),
      tuple('abcdef', re"/abc(.?)e([fg])/", 0, darray[
        0 => 'abcdef',
        1 => 'd',
        2 => 'f',
      ]),
      tuple('abcdef', re"/abc(?P<name>def)/", 0, darray[
        0 => 'abcdef',
        'name' => 'def',
        1 => 'def',
      ]),
      tuple('abcdef', re"/def/", 1, darray[
        0 => 'def',
      ]),
      // TODO(T30675218): Uncomment and test once no longer a parser error
      // tuple('hello', re"/(.?)/", 0, darray[
      //   0 => 'h',
      //   1 => 'h',
      // ]),
      // tuple('hello', re"//", 0, darray[
      //   0 => '',
      // ]),
      // tuple('', re"/(.?)/", 0, darray[
      //   0 => '',
      //   1 => '',
      // ]),
      // tuple('', re"//", 0, darray[
      //   0 => '',
      // ]),
    ];
  }

  /** @dataProvider provideMatch */
  public function testMatch(
    string $haystack,
    Regex\Pattern<shape(...)> $pattern,
    int $offset,
    darray<arraykey, string> $expected,
  ): void {
    $captures = Regex2\match($haystack, $pattern);
    $captures = expect($captures)->toNotBeNull();
    expect($captures)->toBeSame($expected);
  }

  public static function provideMatchNull(): varray<(string, Regex\Pattern<shape(...)>, int)> {
    return varray[
      tuple('a', re"/abc(.?)e(.*)/", 0),
      tuple('abcdef', re"/abc/", 1),
      tuple('', re"/abc(.?)e(.*)/", 0),
    ];
  }

  /** @dataProvider provideMatchNull */
  public function testMatchNull(
    string $haystack,
    Regex\Pattern<shape(...)> $pattern,
    int $offset,
  ): void {
    expect(Regex2\match($haystack, $pattern, $offset))
      ->toBeNull();
  }

  public function testRecursion(): void {
    expect(() ==> Regex2\match(Str\repeat('a', 10000).'b', re"/a*a*a*a*a$/"))
      ->toThrow(
        Regex2\Exception::class,
        'Backtrack limit error',
        'Should reach backtrack limit',
      );
  }

  public static function provideMatches(): varray<(string, Regex\Pattern<shape(...)>, int, bool)> {
    return varray[
      tuple('a', re"/abc(.?)e(.*)/", 0, false),
      tuple('', re"/abc(.?)e(.*)/", 0, false),
      tuple('abce', re"/abc(.?)e(.*)/", 0, true),
      tuple('abcdef', re"/abc(.?)e([fg])/", 0, true),
      tuple('abcdef', re"/abc/", 1, false),
      tuple('abcdef', re"/def/", 1, true),
      tuple('Things that are equal in PHP', re"/php/i", 2, true),
      tuple('is the web scripting', re"/\\bweb\\b/i", 0, true),
      tuple('is the interwebz scripting', re"/\\bweb\\b/i", 0, false),
    ];
  }

  /** @dataProvider provideMatches */
  public function testMatches(
    string $haystack,
    Regex\Pattern<shape(...)> $pattern,
    int $offset,
    bool $expected,
  ): void {
    expect(Regex2\matches($haystack, $pattern, $offset))
      ->toBeSame($expected);
  }

  public static function provideMatchAll(): varray<(string, Regex\Pattern<shape(...)>, int, vec<dict<arraykey, string>>)> {
    return varray[
      tuple('t1e2s3t', re"/[a-z]/", 0, vec[
        dict[0 => 't'],
        dict[0 => 'e'],
        dict[0 => 's'],
        dict[0 => 't'],
      ]),
      tuple('t1e2s3t', re"/[a-z](\d)?/", 0, vec[
        dict[0 => 't1', 1 => '1'],
        dict[0 => 'e2', 1 => '2'],
        dict[0 => 's3', 1 => '3'],
        dict[0 => 't'],
      ]),
      tuple('t1e2s3t', re"/[a-z](?P<digit>\d)?/", 0, vec[
        dict[0 => 't1', 'digit' => '1', 1 => '1'],
        dict[0 => 'e2', 'digit' => '2', 1 => '2'],
        dict[0 => 's3', 'digit' => '3', 1 => '3'],
        dict[0 => 't'],
      ]),
      tuple('test', re"/a/", 0, vec[]),
      tuple('t1e2s3t', re"/[a-z]/", 3, vec[
        dict[0 => 's'],
        dict[0 => 't'],
      ]),
      // TODO(T30675218): Uncomment and test once no longer a parser error
      // tuple('', re"//", 0, vec[
      //   dict[0 => ''],
      // ]),
      // tuple('', re"/(.?)/", 0, vec[
      //   dict[0 => '', 1 => ''],
      // ]),
      // tuple('hello', re"//", 0, vec[
      //   dict[0 => ''],
      //   dict[0 => ''],
      //   dict[0 => ''],
      //   dict[0 => ''],
      //   dict[0 => ''],
      //   dict[0 => ''],
      // ]),
      // tuple('hello', re"/.?/", 0, vec[
      //   dict[0 => 'h'],
      //   dict[0 => 'e'],
      //   dict[0 => 'l'],
      //   dict[0 => 'l'],
      //   dict[0 => 'o'],
      //   dict[0 => ''],
      // ]),
      // tuple('hello', re"//", 2, vec[
      //   dict[0 => ''],
      //   dict[0 => ''],
      //   dict[0 => ''],
      //   dict[0 => ''],
      // ]),
      // tuple('hello', re"/.?/", 2, vec[
      //   dict[0 => 'l'],
      //   dict[0 => 'l'],
      //   dict[0 => 'o'],
      //   dict[0 => ''],
      // ]),
      tuple("<b>bold text</b><a href=howdy.html>click me</a>", re"/(<([\\w]+)[^>]*>)(.*)(<\\/\\2>)/",
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
    \Generator<int, Regex\Match, void> $generator
  ): vec<dict<arraykey, mixed>> {
    return Vec\map($generator, $match ==> Shapes::toDict($match));
  }

  /** @dataProvider provideMatchAll */
  public function testMatchAll(
    string $haystack,
    Regex\Pattern<shape(...)> $pattern,
    int $offset,
    vec<dict<arraykey, string>> $expected,
  ): void {
    expect(self::vecFromGenerator(
      Regex2\match_all($haystack, $pattern, $offset)))
      ->toBeSame($expected);
  }

  public static function provideReplace(): varray<(string, Regex\Pattern<shape(...)>, string, int, string)> {
    return varray[
      tuple('abc', re"#d#", '', 0, 'abc'),
      tuple('abcd', re"#d#", 'e', 0, 'abce'),
      tuple('abcdcbabcdcbabcdcba', re"#d#", 'D', 4, 'abcdcbabcDcbabcDcba'),
      tuple('abcdcbabcdcbabcdcba', re"#d#", 'D', 19, 'abcdcbabcdcbabcdcba'),
      tuple('abcdcbabcdcbabcdcba', re"#d#", 'D', -19, 'abcDcbabcDcbabcDcba'),
      tuple('abcdefghi', re"#\D#", 'Z', -3, 'abcdefZZZ'),
      tuple('abcd6', re"#d(\d)#", '\1', 0, 'abc6'),
      // TODO(T30675218): Uncomment and test once no longer a parser error
      // tuple('', re"/(.?)/", 'A', 0, 'A'),
      // tuple('', re"//", 'A', 0, 'A'),
      // tuple('hello', re"/(.?)/", 'A', 0, 'AAAAAA'),
      // tuple('hello', re"//", 'A', 0, 'AhAeAlAlAoA'),
      // tuple('hello', re"//", 'A', 2, 'heAlAlAoA'),
      // tuple('hello', re"//", 'A', -3, 'heAlAlAoA'),
      tuple(
        'April 15, 2003',
        re"/(\\w+) (\\d+), (\\d+)/i",
        '\${1}1,\$3',
        0,
        '${1}1,$3',
      ),
      tuple(
        'April 15, 2003',
        re"/(\\w+) (\\d+), (\\d+)/i",
        "\${1}1,\$3",
        0,
        'April1,2003',
      ),
      tuple(
        Regex2\replace(
          "{startDate} = 1999-5-27",
          re"/(19|20)(\\d{2})-(\\d{1,2})-(\\d{1,2})/",
          "\\3/\\4/\\1\\2",
          0,
        ),
        re"/^\\s*{(\\w+)}\\s*=/",
        "$\\1 =",
        0,
        "\$startDate = 5/27/1999",
      ),
      // TODO(T30675218): Uncomment and test once no longer a parser error
      // tuple('ooooo', re"/.*/", 'a', 0, 'aa'),
    ];
  }

  /** @dataProvider provideReplace */
  public function testReplace(
    string $haystack,
    Regex\Pattern<shape(...)> $pattern,
    string $replacement,
    int $offset,
    string $expected,
  ): void {
    expect(Regex2\replace($haystack, $pattern, $replacement, $offset))
      ->toBeSame($expected);
  }

  public static function provideReplaceWith(): varray<mixed> {
    return varray[
      tuple('abc', re"#d#", $x ==> $x[0], 0, 'abc'),
      tuple('abcd', re"#d#", $x ==> 'xyz', 0, 'abcxyz'),
      tuple('abcdcbabcdcbabcdcba', re"#d#", $x ==> 'D', 0, 'abcDcbabcDcbabcDcba'),
      tuple('hellodev42.prn3.facebook.com',
        re"/dev(\d+)\.prn3(?<domain>\.facebook\.com)?/",
        $x ==> $x[1] . $x['domain'], 4,
        'hello42.facebook.com'),
      tuple('hellodev42.prn3.facebook.com',
        re"/dev(\d+)\.prn3(?<domain>\.facebook\.com)?/",
        $x ==> $x[1] . $x['domain'], 6,
        'hellodev42.prn3.facebook.com'),
      tuple('<table ><table >', re"@<table(\s+.*?)?>@s", $x ==> $x[1], 8, '<table > '),
      // TODO(T30675218): Uncomment and test once no longer a parser error
      // tuple('', re"/(.?)/", $x ==> $x[1].'A', 0, 'A'),
      // tuple('', re"//", $x ==> $x[0].'A', 0, 'A'),
      // tuple('hello', re"/(.?)/", $x ==> $x[1].'A', 0, 'hAeAlAlAoAA'), // unintuitive, but consistent with preg_replace_callback
      // tuple('hello', re"//", $x ==> $x[0].'A', 0, 'AhAeAlAlAoA'),
      tuple('@[12345:67890:janedoe]', re"/@\[(\d*?):(\d*?):([^]]*?)\]/",
        ($x ==> Str\repeat(' ', 4 + Str\length($x[1]) + Str\length($x[2])) . $x[3] . ' '),
        0, '              janedoe '),
      // TODO(T30675218): Uncomment and test once no longer a parser error
      // tuple('ooooo', re"/.*/", $x ==> 'a', 0, 'aa'),
    ];
  }

  /** @dataProvider provideReplaceWith */
  public function testReplaceWith(
    string $haystack,
    Regex\Pattern<shape(...)> $pattern,
    (function(Regex\Match): string) $replace_func,
    int $offset,
    string $expected,
  ): void {
    expect(Regex2\replace_with($haystack, $pattern, $replace_func, $offset))
      ->toBeSame($expected);
  }

  public static function provideSplit(): varray<(string, Regex\Pattern<shape(...)>, ?int, vec<string>)> {
    return varray[
      tuple('', re"/x/", null, vec['']),
      tuple('hello world', re"/x/", null, vec['hello world']),
      tuple('hello world', re"/x/", 2, vec['hello world']),
      tuple('hello world', re"/\s+/", null, vec['hello', 'world']),
      tuple('  hello world  ', re"/\s+/", null, vec['', 'hello', 'world', '']),
      tuple('  hello world  ', re"/\s+/", 2, vec['', 'hello world  ']),
      tuple('  hello world  ', re"/\s+/", 3, vec['', 'hello', 'world  ']),
      // TODO(T30675218): Uncomment and test once no longer a parser error
      // tuple('', re"/(.?)/", null, vec['', '']),
      // tuple('', re"//", null, vec['', '']),
      // tuple("string", re"/(.?)/", null, vec['', '', '', '', '', '', '', '']),
      // tuple("string", re"//", null, vec['', 's', 't', 'r', 'i', 'n', 'g', '']),
      // tuple("string", re"/(.?)/", 3, vec['', '', 'ring']),
      // tuple("string", re"//", 3, vec['', 's', 'tring']),
    ];
  }

  /** @dataProvider provideSplit */
  public function testSplit(
    string $haystack,
    Regex\Pattern<shape(...)> $pattern,
    ?int $limit,
    vec<string> $expected,
  ): void {
    expect(Regex2\split($haystack, $pattern, $limit))
      ->toBeSame($expected);
  }

  public function testSplitInvalidLimit(): void {
    expect(() ==> Regex2\split('hello world', re"/x/", 1))
      ->toThrow(InvariantViolationException::class);
  }
}
