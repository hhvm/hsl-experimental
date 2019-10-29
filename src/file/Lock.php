<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\File;

use namespace HH\Lib\Experimental\{IO, OS};

/**
 * A File Lock, which is unlocked as a disposable. To acquire one, call `lock`
 * on a Base object.
 *
 * Note that in some cases, such as the non-blocking lock types, we may throw
 * an `LockAcquisitionException` instead of acquiring the lock. If this
 * is not desired behavior it should be guarded against.
 */
final class Lock implements \IDisposable {
  private resource $resource;

  public function __construct(
    <<__AcceptDisposable>> Handle $handle,
    LockType $lock_type,
  ) {
    $this->resource =
      /* HH_IGNORE_ERROR[4179] doing dodgy things to disposables */
      /* HH_IGNORE_ERROR[4188] doing dodgy things to disposables */
      ($handle as _Private\NonDisposableFileHandle)->__getResource_DO_NOT_USE();
    $_wouldblock = null;
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $flock_result = \flock($this->resource, $lock_type, inout $_wouldblock);
    if (!$flock_result) {
      OS\_Private\throw_errno(OS\_Private\errno() as nonnull, "flock failed");
    }
  }

  final public function __dispose(): void {
    $_wouldblock = null;
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    \flock($this->resource, \LOCK_UN, inout $_wouldblock);
  }
}
