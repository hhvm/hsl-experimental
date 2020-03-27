<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\OS;

use namespace HH\Lib\Keyset;
use namespace HH\Lib\_Private\_OS;
use function Facebook\FBExpect\expect; // @oss-enable
use type Facebook\HackTest\HackTest; // @oss-enable
use type Facebook\HackTest\DataProvider; // @oss-enable
// @oss-disable: use type HackTest;

// @oss-disable: <<Oncalls('hf')>>
final class HSLOSEnumSubsetsTest extends HackTest {
  public function testGAIErrorContainsAllPlatforms(): void {
    $mac = _OS\MacOS_GAIError::getNames();
    $gnu = _OS\GNU_GAIError::getNames();

    $combined = Keyset\sort(Keyset\flatten(vec[$mac, $gnu]));

    expect($combined)->toEqual(Keyset\sort(_OS\GAIError::getNames()));
  }

  public function testEnumNamesEqualValues(): void {
    expect(keyset(OS\ErrorCode::getNames()))->toEqual(
      keyset(OS\ErrorCode::getValues()),
    );
    expect(keyset(_OS\GAIError::getNames()))->toEqual(
      keyset(_OS\GAIError::getValues()),
    );
  }

  public function testErrorCodeContainsEverything(): void {
    $gai = _OS\GAIError::getNames();

    $errno = _OS\Errno::getNames();
    $herror = Keyset\map(
      _OS\HError::getNames(),
      $name ==> 'HERROR_'.$name,
    );

    // EAI_SYSTEM means that `errno` contains the cause, so
    // shouldn't be returned as an ErrorCode
    unset($gai[_OS\GAIError::EAI_SYSTEM]);

    expect(Keyset\sort(keyset(OS\ErrorCode::getNames())))->toEqual(
      Keyset\sort(Keyset\flatten(vec[$errno, $herror, $gai])),
    );
  }
}
