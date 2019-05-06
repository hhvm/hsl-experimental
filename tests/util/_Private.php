<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Util\_Private;

/**
 * This class belongs to WrappedRefTest.
 * It is meant to closely mimic the HHVM-team's Ref<T>,
 * but it may stand in for any implementation.
 */
final class ProjectBoundRef<T> {
  public function __construct(public T $v) {}
}
