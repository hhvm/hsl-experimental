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

use namespace HH\Lib\{Experimental\IO, Str};

abstract class NativeHandle implements IO\NonDisposableHandle {
  protected bool $isAwaitable = true;
  protected function __construct(protected resource $impl) {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    \stream_set_blocking($impl, false);
  }

  private ?Awaitable<mixed> $lastOperation;
  final protected function queuedAsync<T>(
    (function(): Awaitable<T>) $next,
  ): Awaitable<T> {
    $last = $this->lastOperation;
    $queue = async {
      await $last;
      return await $next();
    };
    $this->lastOperation = $queue;
    return $queue;
  }


  final protected async function selectAsync(int $flags): Awaitable<void> {
    if (!$this->isAwaitable) {
      return;
    }
    if ($this->isEndOfFile()) {
      return;
    }
    try {
      /* HH_FIXME[2049] *not* PHP stdlib */
      /* HH_FIXME[4107] *not* PHP stdlib */
      await \stream_await($this->impl, $flags);
    } catch (\InvalidOperationException $_) {
      // e.g. real files on Linux when using epoll
      $this->isAwaitable = false;
    }
  }

  final public function isEndOfFile(): bool {
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    return \feof($this->impl);
  }

  final public async function closeAsync(): Awaitable<void> {
    if ($this is IO\WriteHandle) {
      await $this->flushAsync();
    }
    using new PHPWarningSuppressor();
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    \fclose($this->impl);
  }
}
