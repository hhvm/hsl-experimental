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

use namespace HH\Lib\_Private;

/**
 * A wrapper around a file resource that can close and unlock the file as a
 * disposable
 */
abstract class FileBase implements \IDisposable {
  private resource $handle;

  public function __construct(
    private string $filename,
    FileMode $mode,
  ) {
    $handle = @\fopen($filename, $mode);

    if (
      (
        $mode === FileMode::WRITE_EXCLUSIVE_CREATE ||
        $mode === FileMode::READ_WRITE_EXCLUSIVE_CREATE
      ) &&
      $handle === false
    ) {
      throw new FileCreateException();
    } else if ($handle === false) {
      throw new FileOpenException();
    }

    $this->handle = $handle;
  }

  /**
   * Get a lock on this file. If the lock type is non blocking, this will
   * return immediately even if the lock was not acquired. Returns if the lock
   * was acquired or not.
   */
  <<__ReturnDisposable>>
  final public function lock(FileLockType $lock_type): FileLock {
    return new FileLock(
      _Private\io_handle_from_resource(
        static::class,
        $this->handle,
      ),
      $lock_type,
    );
  }

  /**
   * Get the name of this file.
   */
  final public function getName(): string {
    return $this->filename;
  }

  /**
   * Write the provided contents to the file at the current seek offset.
   */
  final public function write(string $contents): void {
    \fwrite($this->handle, $contents);
  }

  /**
   * Read the file contents as lines.
   */
  final public function readLines(): \Iterator<string> {
    while (($line = \fgets($this->handle)) !== false) {
      yield $line;
    }
  }

  /**
   * Read the file contents as chunks.
   */
  final public function readChunks(int $chunk_size): \Iterator<string> {
    while (!\feof($this->handle)) {
      yield \fread($this->handle, $chunk_size);
    }
  }

  /**
   * Get the size of the file.
   */
  final public function getSize(): int {
    return \filesize($this->filename);
  }

  /**
   * Override this if you want to do extra work after the default dispose
   * behavior has completed.
   */
  protected function dispose(): void {}

  final public function __dispose(): void {
    \fclose($this->handle);
    $this->dispose();
  }
}

/**
 * A regular file.
 */
final class File extends FileBase {}
