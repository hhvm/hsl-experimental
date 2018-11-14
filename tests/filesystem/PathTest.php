<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */


use namespace HH\Lib\Experimental\Filesystem;

use function Facebook\FBExpect\expect;
use type Facebook\HackTest\HackTest; // @oss-enable
use type HH\InvariantException as InvalidRegexException; // @oss-enable
// @oss-disable: use type HackTest;

// @oss-disable: <<Oncalls('hack')>>
final class PathTest extends HackTest {
  public function testAbsolute(): void {
    $relative_path = new Filesystem\Path('foo/bar/baz');
    expect($relative_path->isAbsolute())->toBeFalse();
    expect($relative_path->isRelative())->toBeTrue();

    $absolute_path = new Filesystem\Path('/foo/bar/baz');
    expect($absolute_path->isAbsolute())->toBeTrue();
    expect($absolute_path->isRelative())->toBeFalse();
  }

  public static function provideTestGetBaseName(): varray<(string, string)> {
    return varray[
      tuple('foo', 'foo'),
      tuple('/foo/bar/baz', 'baz'),
      tuple('/foo/bar/baz.php', 'baz.php'),
      tuple('/a/b/c.php/d', 'd'),
      tuple('a/b/c/d', 'd'),
      tuple('a/b/c/d/', 'd'),
      tuple('a/b/c/d//', 'd'),
      tuple('/a/b/c/d.php/e.php', 'e.php'),
    ];
  }

  <<DataProvider('provideTestGetBaseName')>>
  public function testGetBaseName(string $path, string $name): void {
    $path = new Filesystem\Path($path);
    expect($path->getBaseName())->toBeSame($name);
  }

  public static function provideTestGetParent(): varray<(string, string)> {
    return varray[
      tuple('', ''),
      tuple('foo', '.'),
      tuple('/foo/bar/baz', '/foo/bar'),
      tuple('/foo/bar/baz.php', '/foo/bar'),
      tuple('/a/b/c.php/d', '/a/b/c.php'),
      tuple('a/b/c/d', 'a/b/c'),
      tuple('a/b/c/d/', 'a/b/c'),
      tuple('a/b/c/d//', 'a/b/c'),
      tuple('/a/b/c/d.php/e.php', '/a/b/c/d.php'),
    ];
  }

  <<DataProvider('provideTestGetParent')>>
  public function testGetParent(string $path, string $parent): void {
    expect((new Filesystem\Path($path))->getParent()->toString())->toBeSame(
      $parent,
    );
  }

  public static function provideTestWithExtension(
  ): varray<(string, string, string)> {
    return varray[
      tuple('/foo/bar', 'php', '/foo/bar.php'),
      tuple('/foo/bar.txt', 'php', '/foo/bar.php'),
      tuple('/foo/bar.txt.md', 'php', '/foo/bar.txt.php'),
      tuple('/foo/bar', '.php', '/foo/bar.php'),
      tuple('a/b/c.txt', '.md', 'a/b/c.md'),
      tuple('a/b/c.txt.php', '.md', 'a/b/c.txt.md'),
      tuple('a/b/c.', '.txt', 'a/b/c..txt'),
      tuple('a/b/c.', 'txt', 'a/b/c..txt'),
    ];
  }

  <<DataProvider('provideTestWithExtension')>>
  public function testWithExtension(
    string $path,
    string $extension,
    string $expected,
  ): void {
    expect((new Filesystem\Path($path))->withExtension($extension)->toString())
      ->toBeSame($expected);
  }

  public static function provideTestGetParts(): varray<(string, vec<string>)> {
    return varray[
      tuple('/foo/bar', vec['foo', 'bar']),
      tuple('foo/bar/baz', vec['foo', 'bar', 'baz']),
      tuple('foo/bar/baz/', vec['foo', 'bar', 'baz']),
      tuple('foo/bar//baz', vec['foo', 'bar', 'baz']),
      tuple('/a/b/c/d.php/e.php', vec['a', 'b', 'c', 'd.php', 'e.php']),
    ];
  }

  <<DataProvider('provideTestGetParts')>>
  public function testGetParts(string $path, vec<string> $parts): void {
    expect((new Filesystem\Path($path))->getParts())->toBeSame($parts);
  }
}
