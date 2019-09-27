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

use namespace HH\Lib\{_Private, Experimental\IO};

/**
 * A File Lock, which is unlocked as a disposable. To acquire one, call `lock`
 * on a FileBase object.
 *
 * Note that in some cases, such as the non-blocking lock types, we may throw
 * an `FileLockAcquisitionException` instead of acquiring the lock. If this
 * is not desired behavior it should be guarded against.
 */
final class FileLock implements \IDisposable {
  private resource $resource;

  public function __construct(
    <<__AcceptDisposable>>FileHandle $handle,
    FileLockType $lock_type,
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
      throw new FileLockAcquisitionException();
    }
  }

  final public function __dispose(): void {
    $_wouldblock = null;
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    \flock($this->resource, \LOCK_UN, inout $_wouldblock);
  }
}
