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

use namespace HH\Lib\{Str, OS};
use namespace HH\Lib\_Private\_OS;

/** Trait implementing `WriteHandle` methods that can be implemented in terms
 * of more basic methods.
 */
trait WriteHandleConvenienceMethodsTrait {
  require implements WriteHandle;

  public async function writeAllAsync(
    string $data,
    ?int $timeout_ns = null,
  ): Awaitable<void> {
    if ($data === '') {
      return;
    }

    if ($timeout_ns is int && $timeout_ns <= 0) {
      _OS\throw_errno(OS\Errno::ERANGE, 'Timeout must be null, or > 0');
    }

    $original_size = Str\length($data);

    $timer = new \HH\Lib\_Private\OptionalIncrementalTimeout(
      $timeout_ns,
      () ==> {
        _OS\throw_errno(OS\Errno::ETIMEDOUT, Str\format(
          "Reached timeout before %s data could be read.",
          $data === '' ? 'any' : 'all',
        ));
      },
    );

    do {
      $written = await $this->writeAsync($data, $timer->getRemainingNS());
      $data = Str\slice($data, $written);
    } while ($written !== 0 && $data !== '');

    if ($data !== '') {
      _OS\throw_errno(
        OS\Errno::EPIPE,
        Str\format(
          "asked to write %d bytes, but only able to write %d bytes",
          $original_size,
          $original_size - Str\length($data),
        ),
      );
    }
  }
}
