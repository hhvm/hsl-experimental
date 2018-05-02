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

enum FileMode : string as string {
  /**
   * Open for reading only; place the file pointer at the beginning of the file.
   */
  READ = 'r';

  /**
   * Open for reading and writing; otherwise it has the same behavior as READ.
   */
  READ_WRITE = 'r+';

  /**
   * Open for writing only; place the file pointer at the beginning of the
   * file and truncate the file to zero length. If the file does not exist,
   * attempt to create it.
   */
  WRITE_TRUNCATE = 'w';

  /**
   * Open for reading and writing; otherwise it has the same behavior as
   * WRITE_TRUNCATE.
   */
  READ_WRITE_TRUNCATE = 'w+';

  /**
   * Open for writing only; place the file pointer at the end of the file. If
   * the file does not exist, attempt to create it. In this mode, seeking has
   * no effect, writes are always appended.
   */
  WRITE_APPEND = 'a';

  /**
   * Open for reading and writing; place the file pointer at the end of the
   * file. If the file does not exist, attempt to create it. In this mode,
   * seeking only affects the reading position, writes are always appended.
   */
  READ_WRITE_APPEND = 'a+';

  /**
   * Open the file for writing only. If the file does not exist, it is created.
   * If it exists, it is neither truncated (as opposed to WRITE_TRUNCATE/
   * READ_WRITE_TRUNCATE), nor will the filesystem call fail (as is the case
   * with WRITE_EXCLUSIVE_CREATE/ READ_WRITE_EXCLUSIVE_CREATE). The file
   * pointer is positioned on the beginning of the file. This may be useful if
   * it's desired to get a lock on the file before attempting to modify the
   * file, as using WRITE_TRUNCATE/ READ_WRITE_TRUNCATE could truncate the file
   * before the lock was obtained
   */
  WRITE_CREATE = 'c';

  /**
   * Open the file for reading and writing; otherwise it has the same behavior
   * as WRITE_CREATE.
   */
  READ_WRITE_CREATE = 'c+';

  /**
   * Create and open for writing only; place the file pointer at the beginning
   * of the file. If the file already exists, the filesystem call will throw an
   * exception. If the file does not exist, attempt to create it.
   */
  WRITE_EXCLUSIVE_CREATE = 'x';

  /**
   * Create and open for reading and writing; otherwise it has the same
   * behavior as CREATE.
   */
  READ_WRITE_EXCLUSIVE_CREATE = 'x+';
}
