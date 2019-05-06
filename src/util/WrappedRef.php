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
 * A bridge between a the HSL `IRef<T>`
 * and a similar class that does not implement `IRef<T>`.
 * 
 * When dealing with another project that does not implement `IRef<T>`,
 * you might still need to use their project-bound `Ref<T>`.
 * This class acts like `IRef<T>` where T is the internal T from the other Ref.
 *
 * THIS DOES PREVENT THE GARBAGE COLLECTOR FROM DELETING THE UNDERLYING
 * PROJECT-BOUND REF<T> IF YOU ARE STILL HOLDING A REFERENCE TO IT.
 * IF YOU ARE ON HHVM3.30 OR LOWER AND THE PROJECT-BOUND REF<T> HAS A
 * __DESTRUCT METHOD, YOU'LL CHANGE THE BEHAVIOR OF THE PROGRAM.
 */
final class WrappedRef<T> implements IRef<T> {
  /**
   * @param dynamic $wrapped The project bound `Ref<T>`
   * @param (function(mixed): T) $read a callback for reading the value
   * @param (function(mixed, T): T) $write a callback for writing the value
   *
   * Please note that there is no typesafety on interactions with $wrapped.
   * You will be required to use `HH_FIXME[4110] when returning from $read.
   */
  public function __construct(
    private dynamic $wrapped,
    private (function(dynamic): T) $read,
    private (function(dynamic, T): T) $write,
  ) {}
  public function getValue(): T {
    $read = $this->read;
    return $read($this->wrapped);
  }
  public function setValue(T $value): T {
    $write = $this->write;
    return $write($this->wrapped, $value);
  }
}
