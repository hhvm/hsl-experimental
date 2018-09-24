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

use namespace HH\Lib\Str;

final class TemporaryFile implements \IAsyncDisposable {
  private string $path;
  private FileReadWriteHandle $handle;

  /** Create a new temporary file, with an optional path pattern.
   *
   * @param $pattern a `Str\format`-style string, containing a single `%s`
   *   placeholder. The `%s` will be replaced with an implementation-defined
   *   number of random path-safe characters. Defaults to `/tmp/%s`, or the
   *   appropriate equivalent on the local platform.
   */
  public function __construct(?string $pattern = null) {
    if ($pattern === null) {
      $pattern = \sys_get_temp_dir().'/%s';
    }
    /* HH_FIXME[4027] unsafe - need something like Regex\Match<T> for format
     * strings */
    /* HH_FIXME[4110] unsafe */
    $path = Str\format($pattern, \bin2hex(\random_bytes(16)));
    $this->path = $path;

    $this->handle = open_read_write($path, FileReadWriteMode::MUST_CREATE);
  }

  public function getHandle(): FileReadWriteHandle {
    return $this->handle;
  }

  public async function __disposeAsync(): Awaitable<void> {
    await $this->handle->closeAsync();
    \unlink($this->path);
  }
}
