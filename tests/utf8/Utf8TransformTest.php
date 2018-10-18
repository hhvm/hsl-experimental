<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\Experimental\Str\Utf8;
use function Facebook\FBExpect\expect;
use type HH\Lib\Experimental\Str\Encoding;
use type Facebook\HackTest\DataProvider; // @oss-enable
// @oss-disable: use InvariantViolationException as InvariantException;

final class Utf8TransformTest extends \Facebook\HackTest\HackTest {

  public static function provideLowercase(): vec<(string, string)> {
    return vec[
      tuple('', ''),
      tuple('hello world', 'hello world'),
      tuple('Hello World', 'hello world'),
      tuple('Jenny: (???)-867-5309', 'jenny: (???)-867-5309'),
      tuple('HÉLLÖ Wôrld', 'héllö wôrld'),
    ];
  }

  <<DataProvider('provideLowercase')>>
  public function testLowercase(string $string, string $expected): void {
    expect(Utf8\lowercase($string))->toBeSame($expected);
  }

  public static function provideUppercase(): vec<(string, string)> {
    return vec[
      tuple('', ''),
      tuple('hello world', 'HELLO WORLD'),
      tuple('Hello World', 'HELLO WORLD'),
      tuple('Jenny: (???)-867-5309', 'JENNY: (???)-867-5309'),
      tuple('héllö wôrld', 'HÉLLÖ WÔRLD'),
    ];
  }

  <<DataProvider('provideUppercase')>>
  public function testUppercase(string $string, string $expected): void {
    expect(Utf8\uppercase($string))->toBeSame($expected);
  }

  public static function provideSplice(
  ): vec<(string, string, int, ?int, string)> {
    return vec[
      tuple('', '', 0, null, ''),
      tuple('héllö wôrld', 'darkness', 6, null, 'héllö darkness'),
      tuple('héllö wôrld', ' crüel ', 5, 1, 'héllö crüel wôrld'),
      tuple('héllö wôrld', ' crüel ', -6, 1, 'héllö crüel wôrld'),
      tuple('héllö wôrld', ' crüel', 5, 0, 'héllö crüel wôrld'),
      tuple('héllö ', 'darkness', 6, null, 'héllö darkness'),
      tuple('héllö wôrld', 'darkness', 6, 100, 'héllö darkness'),
      tuple('héllö wôrld', 'darkness', 6, 11, 'héllö darkness'),
    ];
  }

  <<DataProvider('provideSplice')>>
  public function testSplice(
    string $string,
    string $replacement,
    int $offset,
    ?int $length,
    string $expected,
  ): void {
    expect(Utf8\splice($string, $replacement, $offset, $length))
      ->toBeSame($expected);
  }

  public function testSpliceExceptions(): void {
    expect(() ==> Utf8\splice('héllö wôrld', ' crüel ', -12, 1))
      ->toThrow(InvariantException::class);
    expect(() ==> Utf8\splice('héllö wôrld', ' crüel ', 100, 1))
      ->toThrow(InvariantException::class);
  }

  public static function provideConvertKana(): varray<mixed> {
    return varray[
      tuple('', ''),
      tuple(
        '開発第１-ローカライゼーション',
        '開発第1-ﾛｰｶﾗｲｾﾞｰｼｮﾝ',
      ),
      tuple(
        '開発第1-ﾛｰｶﾗｲｾﾞｰｼｮﾝ',
        '開発第1-ﾛｰｶﾗｲｾﾞｰｼｮﾝ',
      ),
      tuple('hello world', 'hello world'),
      tuple('héllö wôrld', 'héllö wôrld'),
    ];
  }

  <<DataProvider('provideConvertKana')>>
  public function testConvertKana(string $string, string $expected): void {
    expect(Utf8\convert_kana($string, shape('k' => true, 'a' => true)))
      ->toBeSame($expected);
  }

  public static function provideEncoding(): varray<mixed> {
    return varray[
      tuple('', Encoding::ASCII, ''),
      tuple(
        "\006E\0061\006-\006(\006'\000 \0069\006'\006D\006E",
        Encoding::UCS2,
        'مرحبا عالم',
      ),
      tuple("h\351ll\366 w\364rld", Encoding::ISO_8859_1, 'héllö wôrld'),
      tuple('hello world', Encoding::ASCII, 'hello world'),
      tuple('héllö wôrld', Encoding::UTF8, 'héllö wôrld'),
    ];
  }

  <<DataProvider('provideEncoding')>>
  public function testFromEncoding(
    string $string,
    Encoding $encoding,
    string $expected,
  ): void {
    expect(Utf8\from_encoding($string, $encoding))->toBeSame($expected);
  }

  <<DataProvider('provideEncoding')>>
  public function testToEncoding(
    string $expected,
    Encoding $encoding,
    string $string,
  ): void {
    expect(Utf8\to_encoding($string, $encoding))->toBeSame($expected);
  }

}
