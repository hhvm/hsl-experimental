<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\Experimental\OS;

use namespace HH\Lib\Keyset;
use function Facebook\FBExpect\expect; // @oss-enable
use type Facebook\HackTest\HackTest; // @oss-enable
use type Facebook\HackTest\DataProvider; // @oss-enable
// @oss-disable: use type HackTest;

// @oss-disable: <<Oncalls('hf')>>
final class HSLOSEnumSubsetsTest extends HackTest {
  public function testGAIErrorContainsAllPlatforms(): void {
    $mac = OS\_Private\MacOS_GAIError::getNames();
    $gnu = OS\_Private\GNU_GAIError::getNames();

    $combined = Keyset\sort(Keyset\flatten(vec[$mac, $gnu]));

    expect($combined)->toEqual(Keyset\sort(OS\_Private\GAIError::getNames()));
  }

  public function testEnumNamesEqualValues(): void {
    expect(keyset(OS\ErrorCode::getNames()))->toEqual(
      keyset(OS\ErrorCode::getValues()),
    );
    expect(keyset(OS\_Private\GAIError::getNames()))->toEqual(
      keyset(OS\_Private\GAIError::getValues()),
    );
  }

  public function testErrorCodeContainsEverything(): void {
    $gai = OS\_Private\GAIError::getNames();

    $errno = OS\_Private\Errno::getNames();
    $herror = Keyset\map(
      OS\_Private\HError::getNames(),
      $name ==> 'HERROR_'.$name,
    );

    // EAI_SYSTEM means that `errno` contains the cause, so
    // shouldn't be returned as an ErrorCode
    unset($gai[OS\_Private\GAIError::EAI_SYSTEM]);

    expect(Keyset\sort(keyset(OS\ErrorCode::getNames())))->toEqual(
      Keyset\sort(Keyset\flatten(vec[$errno, $herror, $gai])),
    );
  }
}
