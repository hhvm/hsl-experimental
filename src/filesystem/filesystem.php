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

final class FileCreateException extends \Exception {}
final class FileOpenException extends \Exception {}

/**
 * A wrapper around a file resource that can close and unlock the file as a
 * disposable
 */
abstract class FileBase implements \IDisposable {

  private resource $handle;

  public function __construct(
    private string $filename,
    private FileMode $mode,
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
    return new FileLock($this->handle, $lock_type);
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

/**
 * Create a temporary file that gets removed as a disposable.
 */
final class TemporaryFile extends FileBase {
  public function __construct(
    private FileMode $mode,
  ) {
    parent::__construct(_Private\make_temporary_file(), $mode);
  }

  <<__Override>>
  protected function dispose(): void {
    namespace\remove_file($this->getName());
  }
}

enum FileLockType : int as int {
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


/**
 * An exception thrown when a file lock was not successfully acquired.
 */
final class FileLockAcquisitionException extends \Exception {}

/**
 * A File Lock, which is unlocked as a disposable. To acquire one, call `lock`
 * on a FileBase object.
 *
 * Note that in some cases, such as the non-blocking lock types, we may throw
 * an `FileLockAcquisitionException` instead of acquiring the lock. If this
 * is not desired behavior it should be guarded against.
 */
final class FileLock implements \IDisposable {
  public function __construct(
    private resource $handle,
    FileLockType $lock_type,
  ) {
    if (!\flock($this->handle, $lock_type)) {
      throw new FileLockAcquisitionException();
    }
  }

  final public function __dispose(): void {
    \flock($this->handle, \LOCK_UN);
  }
}

/**
 * Open a file in the given mode, returning a File object.
 */
<<__ReturnDisposable, __RxLocal>>
function open_file(string $filename, FileMode $mode): File {
  return new File($filename, $mode);
}

/**
 * Remove a file
 */
function remove_file(string $filename): void {
  if (!\file_exists($filename)) {
    return;
  }

  invariant(\unlink($filename), 'Unable to remove %s', $filename);
}
