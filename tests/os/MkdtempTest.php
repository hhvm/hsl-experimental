<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Staging\OS;
use namespace HH\Lib\{IO, Str};

use function Facebook\FBExpect\expect; // @oss-enable
use type Facebook\HackTest\HackTest; // @oss-enable
// @oss-disable: use type HackTest;

// @oss-disable: <<Oncalls('hack')>>
final class MkdtempTest extends HackTest {
  public function testBasicUsage(): void {
    /* HH_FIXME[2049] PHP stdlib */
    /* HH_FIXME[4107] PHP stdlib */
    $pattern = Str\strip_suffix(sys_get_temp_dir(), '/').'/hsl-test-XXXXXX';
    $tempdir = OS\mkdtemp($pattern);
    expect($tempdir)->toNotEqual(
      $pattern,
      'expected literal `X` to be replaced',
    );
    $prefix = Str\strip_suffix($pattern, 'XXXXXX');
    expect(Str\starts_with($tempdir, $prefix))->toBeTrue(
      'tempdir and pattern do not share a prefix',
    );
    /* HH_FIXME[2049] PHP stdlib */
    /* HH_FIXME[4107] PHP stdlib */
    expect(is_dir($tempdir))->toBeTrue();
    /* HH_FIXME[2049] PHP stdlib */
    /* HH_FIXME[4107] PHP stdlib */
    expect((stat($tempdir)['mode']) & 0777)->toEqual(0700);
    /* HH_FIXME[2049] PHP stdlib */
    /* HH_FIXME[4107] PHP stdlib */
    rmdir($tempdir);
  }

  public function testTooFewPlaceholders(): void {
    /* HH_FIXME[2049] PHP stdlib */
    /* HH_FIXME[4107] PHP stdlib */
    $pattern = Str\strip_suffix(sys_get_temp_dir(), '/').'/hsl-test-XXX';
    $ex = expect(() ==> OS\mkdtemp($pattern))->toThrow(
      OS\ErrnoException::class,
    );
    expect($ex->getErrno())->toEqual(OS\Errno::EINVAL);
  }

  public function testNoParentDirectory(): void {
    $pattern = '/idonotexist/foo.XXXXXX';
    expect(() ==> OS\mkdtemp($pattern))->toThrow(OS\NotFoundException::class);
  }
}
