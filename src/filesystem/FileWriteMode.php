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

enum FileWriteMode: string {
  /**
   * Open the file for writing only; place the file pointer at the beginning of
   * the file.
   *
   * If the file exits, it is not truncated (as with `TRUNCATE`), and the call
   * suceeds (unlike `EXCLUSIVE_CREATE`).
   */
  OPEN_OR_CREATE = 'cb';

  /**
   * Open for writing only; place the file pointer at the beginning of the
   * file and truncate the file to zero length. If the file does not exist,
   * attempt to create it.
   */
  TRUNCATE = 'wb';

  /**
   * Open for writing only; place the file pointer at the end of the file. If
   * the file does not exist, attempt to create it. In this mode, seeking has
   * no effect, writes are always appended.
   */
  APPEND = 'ab';

  /**
   * Create and open for writing only; place the file pointer at the beginning
   * of the file. If the file already exists, the filesystem call will throw an
   * exception. If the file does not exist, attempt to create it.
   */
  MUST_CREATE = 'xb';
}
