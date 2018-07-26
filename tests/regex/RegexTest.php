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

use namespace HH\Lib\{C, Experimental\Regex, Str};

use function Facebook\FBExpect\expect;
use type HH\InvariantException as InvalidRegexException; // @oss-enable

final class RegexTest extends PHPUnit_Framework_TestCase {

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

  public function testMatch(): void {
    $captures = Regex\match('a', '/abc(.?)e(.*)/');
    expect($captures)->toBeNull();

    $captures = Regex\match('abce', '/abc(.?)e(.*)/');
    expect($captures)->toNotBeNull();
    invariant($captures !== null, 'For Hack');
    expect(C\count($captures))->toBeSame(3);
    expect($captures[0])->toBeSame('abce');
    expect($captures[1])->toBeSame('');
    expect($captures[2])->toBeSame('');

    $captures = Regex\match('abcdef', '/abc(.?)e([fg])/');
    expect($captures)->toNotBeNull();
    invariant($captures !== null, 'For Hack');
    expect(C\count($captures))->toBeSame(3);
    expect($captures[0])->toBeSame('abcdef');
    expect($captures[1])->toBeSame('d');
    expect($captures[2])->toBeSame('f');

    $captures = Regex\match('abcdef', '/abc(?P<name>def)/');
    expect($captures)->toNotBeNull();
    invariant($captures !== null, 'For Hack');
    expect(C\count($captures))->toBeSame(3);
    expect($captures[0])->toBeSame('abcdef');
    expect($captures[1])->toBeSame('def');
    expect($captures['name'])->toBeSame('def');

    $captures = Regex\match('abcdef', '/abc/', 1);
    expect($captures)->toBeNull();

    $captures = Regex\match('abcdef', '/def/', 1);
    expect($captures)->toNotBeNull();
    invariant($captures !== null, 'For Hack');
    expect(C\count($captures))->toBeSame(1);
    expect($captures[0])->toBeSame('def');

    self::checkThrowsOnInvalid(($a, $b) ==> Regex\match($a, $b));
  }

  public function testRecursion(): void {
    expect(() ==> Regex\match(Str\repeat('a', 10000).'b', '/a*a*a*a*a$/'))
      ->toThrow(
        InvalidRegexException::class,
        'Backtrack limit error',
        'Should reach backtrack limit',
      );
  }

  public function testIsMatch(): void {
    expect(Regex\is_match('a', '/abc(.?)e(.*)/'))->toBeFalse();
    expect(Regex\is_match('abce', '/abc(.?)e(.*)/'))->toBeTrue();
    expect(Regex\is_match('abcdef', '/abc(.?)e([fg])/'))->toBeTrue();
    expect(Regex\is_match('abcdef', '/abc/', 1))->toBeFalse();
    expect(Regex\is_match('abcdef', '/def/', 1))->toBeTrue();

    self::checkThrowsOnInvalid(($a, $b) ==> Regex\is_match($a, $b));
  }

  public function testMatchAll(): void {
    expect(Regex\match_all('t1e2s3t', '/[a-z]/'))->toBeSame(vec[
      dict[0 => 't'],
        dict[0 => 'e'],
        dict[0 => 's'],
        dict[0 => 't'],
    ]);
    expect(Regex\match_all('t1e2s3t', '/[a-z](\d)?/'))->toBeSame(vec[
      dict[0 => 't1', 1 => '1'],
        dict[0 => 'e2', 1 => '2'],
        dict[0 => 's3', 1 => '3'],
        dict[0 => 't'],
    ]);
    expect(Regex\match_all('t1e2s3t', '/[a-z](?P<digit>\d)?/'))->toBeSame(vec[
      dict[0 => 't1', 'digit' => '1', 1 => '1'],
        dict[0 => 'e2', 'digit' => '2', 1 => '2'],
        dict[0 => 's3', 'digit' => '3', 1 => '3'],
        dict[0 => 't'],
    ]);
    expect(Regex\match_all('test', '/a/'))->toBeSame(vec[]);
    expect(Regex\match_all('t1e2s3t', '/[a-z]/', 3))->toBeSame(vec[
      dict[0 => 's'],
        dict[0 => 't'],
    ]);
    self::checkThrowsOnInvalid(($a, $b) ==> Regex\match_all($a, $b));
  }

  public function testSplit(): void {
    expect(Regex\split('', '/x/'))->toBeSame(vec['']);

    expect(Regex\split('hello world', '/x/'))->toBeSame(vec['hello world']);

    expect(Regex\split('hello world', '/\s+/'))->toBeSame(
      vec['hello', 'world'],
    );

    expect(Regex\split('  hello world  ', '/\s+/'))->toBeSame(
      vec['', 'hello', 'world', ''],
    );

    self::checkThrowsOnInvalid(($a, $b) ==> Regex\split($a, $b));
  }

  public function testReplace(): void {
    expect(Regex\replace('abc', '#d#', ''))->toBeSame('abc');
    expect(Regex\replace('abcd', '#d#', 'e'))->toBeSame('abce');
    expect(Regex\replace('abcd6', '#d(\d)#', '\1'))->toBeSame('abc6');
  }
}
