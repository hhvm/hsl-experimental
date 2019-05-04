<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\Util;
use type Facebook\HackTest\DataProvider; // @oss-enable
use function Facebook\FBExpect\expect; // @oss-enable
// @oss-disable: use InvariantViolationException as InvariantException;

final class RefTest extends \Facebook\HackTest\HackTest {

  public function provideRef(): vec<(Util\Ref<int>)> {
    return vec[tuple(new Util\Ref(3))];
  }

  public function testRefConstructorAssigns(): void {
    $ref = new Util\Ref(3);
    expect($ref->value)->toBeSame(
      3,
      'The constructor does not assign the first argument to Ref::value!',
    );
  }

  <<DataProvider('provideRef')>>
  public function testSetValueSetsValue(Util\Ref<int> $ref): void {
    $ref->setValue(4);
    expect($ref->value)->toBeSame(
      4,
      'Ref::setValue() does not assign the first argument to Ref::value!',
    );
  }

  <<DataProvider('provideRef')>>
  public function testGetValueGetsValue(Util\Ref<int> $ref): void {
    expect($ref->getValue())->toBeSame(
      $ref->value,
      'Ref::getValue() does not return Ref::value!',
    );
  }

  <<DataProvider('provideRef')>>
  public function testSetValueReturnsTheAssignedValue(
    Util\Ref<int> $ref,
  ): void {
    expect($ref->setValue(4))->toBeSame(
      4,
      'Ref::setValue() does not return the first argument to Ref::setValue()',
    );
  }

}
