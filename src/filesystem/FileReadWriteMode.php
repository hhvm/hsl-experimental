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

enum FileReadWriteMode : string as string {
  /**
   * Open for reading and writing; otherwise it has the same behavior as READ.
   */
  OPEN_EXISTING = 'r+';

  /**
   * Open for reading and write, and truncate the file contents.
   */
  TRUNCATE = 'w+';

  /**
   * Open for reading and writing; place the file pointer at the end of the
   * file. If the file does not exist, attempt to create it. In this mode,
   * seeking only affects the reading position, writes are always appended.
   */
  APPEND = 'a+';

  /**
   * Open the file for reading and writing; otherwise it has the same behavior
   * as WRITE_CREATE.
   */
  OPEN_OR_CREATE = 'c+';

  /**
   * Create and open for reading and writing; otherwise it has the same
   * behavior as CREATE.
   */
  MUST_CREATE = 'x+';
}
