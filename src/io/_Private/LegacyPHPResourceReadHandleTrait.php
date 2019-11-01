<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\IO\_Private;

use namespace HH\Lib\Str;
use namespace HH\Lib\Experimental\{IO, OS};
use type HH\Lib\_Private\PHPWarningSuppressor;

trait LegacyPHPResourceReadHandleTrait implements IO\ReadHandle {
  require extends LegacyPHPResourceHandle;

  final public function rawReadBlocking(?int $max_bytes = null): string {
    using new PHPWarningSuppressor();
    if ($max_bytes is int && $max_bytes < 0) {
      throw new \InvalidArgumentException('$max_bytes must be null, or >= 0');
    }
    if ($max_bytes === 0) {
      return '';
    }
    /* HH_IGNORE_ERROR[2049] __PHPStdLib */
    /* HH_IGNORE_ERROR[4107] __PHPStdLib */
    $result = \stream_get_contents($this->impl, $max_bytes ?? -1);
    if ($result === false) {
      OS\_Private\throw_errno(
        OS\_Private\errnox('stream_get_contents'),
        'stream_get_contents() failed',
      );
    }
    return $result as string;
  }

  final public async function readAsync(
    ?int $max_bytes = null,
  ): Awaitable<string> {
    if ($max_bytes is int && $max_bytes < 0) {
      throw new \InvalidArgumentException('$max_bytes must be null, or >= 0');
    }

    $data = '';
    while (($max_bytes === null || $max_bytes > 0) && !$this->isEndOfFile()) {
      $chunk = $this->rawReadBlocking($max_bytes);
      $data .= $chunk;
      if ($max_bytes !== null) {
        $max_bytes -= Str\length($chunk);
      }
      if ($max_bytes === null || $max_bytes > 0) {
        await $this->selectAsync(\STREAM_AWAIT_READ);
      }
    }
    return $data;
  }

  final public async function readLineAsync(
    ?int $max_bytes = null,
  ): Awaitable<string> {
    if ($max_bytes is int && $max_bytes < 0) {
      throw new \InvalidArgumentException('$max_bytes must be null, or >= 0');
    }

    if ($max_bytes === null) {
      // The placeholder value for 'default' is not documented
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      $impl = () ==> \fgets($this->impl);
    } else {
      // ... but if you specify a value, it returns 1 less.
      /* HH_IGNORE_ERROR[2049] __PHPStdLib */
      /* HH_IGNORE_ERROR[4107] __PHPStdLib */
      $impl = () ==> \fgets($this->impl, $max_bytes + 1);
    }
    $data = $impl();
    while ($data === false && !$this->isEndOfFile()) {
      await $this->selectAsync(\STREAM_AWAIT_READ);
      $data = $impl();
    }
    return $data === false ? '' : $data;
  }
}
