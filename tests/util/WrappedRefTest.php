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
use namespace HH\Lib\Util\_Private;
use type Facebook\HackTest\DataProvider; // @oss-enable
use function Facebook\FBExpect\expect; // @oss-enable
// @oss-disable: use InvariantViolationException as InvariantException;

final class WrappedRefTest extends \Facebook\HackTest\HackTest {

  public static function nineWarning(
    _Private\ProjectBoundRef<int> $project,
  ): void {
    expect($project->v)->toNotBeSame(
      9,
      'This test must modify the value to 9, so you can not have the value set to 9 here',
    );
  }

  public static function getProjectBoundRef(
    int $value,
  ): _Private\ProjectBoundRef<int> {
    return new _Private\ProjectBoundRef($value);
  }

  public static function modifyWrappedRef<T>(
    Util\IRef<T> $ref,
    T $value,
  ): void {
    $ref->setValue($value);
  }

  public static function modifyProjectBoundRef<T>(
    _Private\ProjectBoundRef<T> $ref,
    T $value,
  ): void {
    $ref->v = $value;
  }

  public function provideWrappedRef(
  ): vec<(Util\IRef<int>, _Private\ProjectBoundRef<int>)> {
    $project = self::getProjectBoundRef(3);
    return vec[tuple(
      new Util\WrappedRef(
        $project,
        $ref ==> /*HH_FIXME[4110] dynamic to T implicit cast*/$ref->v,
        ($ref, $value) ==> $ref->v = $value,
      ),
      $project,
    )];
  }

  public function testRefConstructorAdoptsValue(): void {
    $wrapped = new Util\WrappedRef(
      self::getProjectBoundRef(3),
      $ref ==> /*HH_FIXME[4110]*/$ref->v,
      ($ref, $value) ==> $ref->v = $value,
    );
    expect($wrapped->getValue())->toBeSame(
      3,
      'The constructor does not adopt the value from _Private\ProjectBoundRef<T>!',
    );
  }

  <<DataProvider('provideWrappedRef')>>
  public function testGetValueReadsTheValueFromProjectBoundRef(
    Util\IRef<int> $ref,
    _Private\ProjectBoundRef<int> $project,
  ): void {
    expect($ref->getValue())->toBeSame(
      $project->v,
      'WrappedRef::getValue() does not return the value of $project->v!',
    );
  }

  <<DataProvider('provideWrappedRef')>>
  public function testSetValueReturnsTheAssignedValue(
    Util\IRef<int> $ref,
    _Private\ProjectBoundRef<int> $project,
  ): void {
    self::nineWarning($project);
    expect($ref->setValue(9))->toBeSame(
      9,
      'WrappedRef::setValue() does not return the first argument to WrappedRef::setValue()',
    );
  }

  <<DataProvider('provideWrappedRef')>>
  public function testModifyingTheProjectBoundRefInADifferentScope(
    Util\IRef<int> $ref,
    _Private\ProjectBoundRef<int> $project,
  ): void {
    self::nineWarning($project);
    self::modifyProjectBoundRef($project, 9);
    expect($project->v)->toBeSame(
      $ref->getValue(),
      'Modifying the $project->v in a different scope should update $ref->getValue()',
    );
    expect($project->v)->toBeSame(9, 'Helper method failure!');
  }

  <<DataProvider('provideWrappedRef')>>
  public function testModifyingTheWrappedRefInADifferentScope(
    Util\IRef<int> $ref,
    _Private\ProjectBoundRef<int> $project,
  ): void {
    self::nineWarning($project);
    self::modifyWrappedRef($ref, 9);
    expect($project->v)->toBeSame(
      $ref->getValue(),
      'Modifying the WrappedRef<T> in a different scope should update $project->v',
    );
    expect($ref->getValue())->toBeSame(9, 'Helper method failure!');
  }
}
