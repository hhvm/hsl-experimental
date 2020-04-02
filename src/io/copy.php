<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\IO;

use namespace HH\Lib\Str;

/**
 * Copies content from the given $source read handle to the $target write handle
 * until the eol of $source is reached.
 *
 * If `$chunk_size` is `null`, there is no limit on the chunk size - this function will
 * copy the content of $source all at once.
 */
async function copy(
  <<__AcceptDisposable>> ReadHandle $source,
  <<__AcceptDisposable>> WriteHandle $target,
  ?int $chunk_size = null,
  ?float $timeout_seconds = null,
): Awaitable<void> {
  if (!$source->isEndOfFile()) {
    $content = await $source->readAsync($chunk_size, $timeout_seconds);
    await $target->writeAsync($content, $timeout_seconds);
  }

  if (!$source->isEndOfFile()) {
    await copy($source, $target, $chunk_size, $timeout_seconds);
  }
}
