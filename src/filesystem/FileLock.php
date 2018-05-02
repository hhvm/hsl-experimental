<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\Filesystem;

/**
 * A File Lock, which is unlocked as a disposable. To acquire one, call `lock`
 * on a FileBase object.
 *
 * Note that in some cases, such as the non-blocking lock types, we may throw
 * an `FileLockAcquisitionException` instead of acquiring the lock. If this
 * is not desired behavior it should be guarded against.
 */
final class FileLock implements \IDisposable {
  public function __construct(
    private resource $handle,
    FileLockType $lock_type,
  ) {
    if (!\flock($this->handle, $lock_type)) {
      throw new FileLockAcquisitionException();
    }
  }

  final public function __dispose(): void {
    \flock($this->handle, \LOCK_UN);
  }
}
