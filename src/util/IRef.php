<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Util;

/**
 * A replacement for PHP style references.
 * If your project already defined a Ref class,
 * you may implement this interface so both HH\Lib\Ref<T>
 * and Your\Project\Ref<T> would typecheck.
 */
interface IRef<T> {
  public function __construct(T $value);
  public function getValue(): T;
  public function setValue(T $value): T;
}
