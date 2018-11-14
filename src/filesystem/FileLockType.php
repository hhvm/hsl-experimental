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

enum FileLockType: int as int {
  /**
   * Any number of processes may have a shared lock simultaneously. It is
   * commonly called a reader lock. The creation of a FileLock will block until
   * the lock is acquired.
   */
  SHARED = \LOCK_SH;

  /**
   * Like a shared lock, but the creation of a FileLock will throw a
   * `FileLockAcquisitionException` if the lock was not acquired instead of
   * blocking.
   */
  SHARED_NON_BLOCKING = \LOCK_SH | \LOCK_NB;

  /**
   * Only a single process may possess an exclusive lock to a given file at a
   * time. The creation of a FileLock will block until the lock is acquired.
   */
  EXCLUSIVE = \LOCK_EX;

  /**
   * Like an exclusive lock, but the creation of a FileLock will throw a
   * `FileLockAcquisitionException` if the lock was not acquired instead of
   * blocking.
   */
  EXCLUSIVE_NON_BLOCKING = \LOCK_EX | \LOCK_NB;
}
