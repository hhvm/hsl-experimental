<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private;

use namespace HH\Lib\C;

function make_temporary_file(): string {
  $temp_dir = \sys_get_temp_dir();
  invariant($temp_dir, 'Unable to determine system temporary directory');
  $file = \tempnam($temp_dir, \get_current_user());
  invariant($file !== false, 'Failed to create temporary file');
  return $file;
}

final class PHPErrorLogger implements \IDisposable {
  const type TError = shape(
    'level' => int,
    'message' => string,
  );
  private vec<this::TError> $errors = vec[];

  private int $oldLevel;
  private mixed $oldHandler = null;

  public function __construct(private bool $suppress) {
    $this->oldLevel = \error_reporting(\PHP_INT_MAX);
    $this->oldHandler = \set_error_handler(
      (int $level, string $message) ==> $this->handleError($level, $message),
    );
  }

  private function handleError(int $level , string $message): bool {
    $this->errors[] = shape('level' => $level, 'message' => $message);
    return $this->suppress;
  }

  public function getErorrs(): vec<this::TError> {
    return $this->errors;
  }

  public function getLastErrorx(): this::TError {
    return C\lastx($this->errors);
  }

  public function __dispose(): void {
    \error_reporting($this->oldLevel);
    \set_error_handler($this->oldHandler);
  }
}
