<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private\_IO;

use namespace HH\Lib\{IO, OS, Str};
use namespace HH\Lib\_Private\_OS;
use type HH\Lib\_Private\PHPWarningSuppressor;

abstract class LegacyPHPResourceHandle implements IO\CloseableHandle {
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


  final protected async function selectAsync(
    int $flags,
    ?float $timeout_seconds,
  ): Awaitable<void> {
    $this->checkIsValid();
    if (!$this->isAwaitable) {
      return;
    }
    if ($this->isEndOfFile()) {
      return;
    }
    $timeout_seconds ??= 0.0;
    try {
      // 1ms is the minimum due to
      // https://github.com/facebook/hhvm/blob/91be13e14afb076330bfc10ca179aae773921b9e/hphp/runtime/base/file-await.cpp#L30
      if ($timeout_seconds > 0.0 && $timeout_seconds < 0.001) {
        $timeout_seconds = 0.001;
      }
      /* HH_FIXME[2049] *not* PHP stdlib */
      /* HH_FIXME[4107] *not* PHP stdlib */
      $res = await \stream_await($this->impl, $flags, $timeout_seconds);
      if ($res === \STREAM_AWAIT_ERROR) {
        _OS\throw_errno(
          OS\Errno::EBADF,
          "Awaiting resource failed"
        );
      }
    } catch (\InvalidOperationException $_) {
      // e.g. real files on Linux when using epoll
      $this->isAwaitable = false;
    }
  }

  final protected function checkIsValid(): void {
    /* HH_FIXME[2049] PHP StdLib */
    /* HH_FIXME[4107] PHP StdLib */
    if (!\is_resource($this->impl)) {
      _OS\throw_errno(OS\Errno::EBADF, "Resource is not valid (already closed?)");
    }
  }

  final public function isEndOfFile(): bool {
    $this->checkIsValid();
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    return \feof($this->impl);
  }

  final public async function closeAsync(): Awaitable<void> {
    $this->checkIsValid();
    if ($this is IO\WriteHandle) {
      await $this->queuedAsync(async () ==> {
        using new PHPWarningSuppressor();
        /* HH_IGNORE_ERROR[2049] __PHPStdLib */
        /* HH_IGNORE_ERROR[4107] __PHPStdLib */
        \fflush($this->impl);
      });
    }

    using new PHPWarningSuppressor();
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    \fclose($this->impl);
  }
}
