<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private;

use namespace HH\Lib\Experimental\{IO, File};

<<__ConsistentConstruct>>
abstract class NonDisposableFileHandle
  extends LegacyPHPResourceHandle
  implements File\Handle, IO\NonDisposableHandle {
  protected string $filename;

  final protected function __construct(string $path, string $mode) {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
  // fopen indicates errors by returning false and raising a warning; log
  // the warning and convert to an exception
  using $errors = new PHPErrorLogger(/* suppress = */ true);
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
  /* HH_IGNORE_ERROR[4107] __PHPStdLib */
  $f = \fopen($path, $mode);
    if ($f === false) {
      throw new File\OpenException(
        'Failed to open file: '.$errors->getLastErrorx()['message'],
      );
    }
    $this->filename = $path;
    parent::__construct($f);
  }

  final public static function __createInstance_IMPLEMENTATION_DETAIL_DO_NOT_USE(
    string $path,
    string $mode,
  ): this {
    return new static($path, $mode);
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
  final public function lock(
    File\LockType $type,
  ): File\Lock {
    return new File\Lock($this, $type);
  }

  final public async function seekAsync(int $offset): Awaitable<void> {
    await $this->queuedAsync(async () ==> {
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      \fseek($this->impl, $offset);
    });
  }

  final public function __getResource_DO_NOT_USE(): resource {
    return $this->impl;
  }
}
