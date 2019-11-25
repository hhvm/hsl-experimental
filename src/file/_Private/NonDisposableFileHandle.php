<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\File\_Private;

use namespace HH\Lib\Str;
use namespace HH\Lib\Experimental\{IO, File, OS};
use type HH\Lib\_Private\PHPWarningSuppressor;

<<__ConsistentConstruct>>
abstract class NonDisposableFileHandle
  extends IO\_Private\LegacyPHPResourceHandle
  implements File\Handle, IO\NonDisposableHandle {
  use IO\_Private\LegacyPHPResourceSeekableHandleTrait;

  protected string $filename;

  final public function __construct(string $path, string $mode) {
    using new PHPWarningSuppressor();
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $f = \fopen($path, $mode);
    /* HH_IGNORE_ERROR[2049] PHPStdLib */
    /* HH_IGNORE_ERROR[4107] PHPStdLib */
    $errno = \posix_get_last_error() as int;
    if ($f === false) {
      OS\_Private\throw_errno($errno, 'fopen');
    }
    $this->filename = $path;
    parent::__construct($f);
  }

  <<__Memoize>>
  final public function getPath(): File\Path {
    return new File\Path($this->filename);
  }

  final public function getSize(): int {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    return \filesize($this->filename);
  }

  <<__ReturnDisposable>>
  final public function lock(File\LockType $type): File\Lock {
    $impl = $this->__getResource_DO_NOT_USE();
    $would_block = false;
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $success = \flock($impl, $type, inout $would_block);
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $errno = \posix_get_last_error();
    if ($success) {
      return new File\Lock($impl);
    }
    OS\_Private\throw_errno($errno as int, 'flock');
  }

  <<__ReturnDisposable>>
  final public function tryLockx(File\LockType $type): File\Lock {
    $impl = $this->__getResource_DO_NOT_USE();
    $would_block = false;
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $success = \flock($impl, $type | \LOCK_NB, inout $would_block);
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $errno = \posix_get_last_error();
    if ($success) {
      return new File\Lock($impl);
    }
    if ($would_block) {
      throw new File\AlreadyLockedException();
    }
    OS\_Private\throw_errno($errno as int, 'flock');
  }


  final public function __getResource_DO_NOT_USE(): resource {
    return $this->impl;
  }
}
