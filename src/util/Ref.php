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
 *
 * It is recommended to use HH\Lib\Util\IRef<T> for type declarations.
 * Doing so allows your code to be flexible if a non HH\Lib Ref class
 * were to be introduced in your codebase.
 *
 * Extending this class is not recommended, however we understand that
 * this may be easier during the transition away from your own Ref<T> type.
 */
class Ref<T> implements IRef<T> {
  public function __construct(public T $value) {}
  public function getValue(): T {
    return $this->value;
  }
  public function setValue(T $value): T {
    return $this->value = $value;
  }
}
