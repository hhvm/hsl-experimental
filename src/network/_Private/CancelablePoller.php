<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\_Private\_Network;

use namespace HH\Lib\OS;
use namespace HH\Lib\_Private\_OS;

final class CancelablePoller {
  private int $nextID = 0;
  private dict<int, Awaitable<mixed>> $polls = dict[];

  public async function pollAsync(
    OS\FileDescriptor $fd,
    int $events,
    int $timeout_ns,
  ): Awaitable<int> {
    $id = $this->nextID;
    $this->nextID++;

    $awaitable = _OS\poll_async($fd, $events, $timeout_ns);
    $this->polls[$id] = $awaitable;
    try {
      return await $awaitable;
    } catch (PollCancelledException $e) {
      return $e->getResult();
    } finally {
      unset($this->polls[$id]);
    }
  }

  public function cancelAll(int $result): void {
    $ex = new PollCancelledException($result);
    foreach ($this->polls as $poll) {
      \HH\Asio\cancel($poll, $ex);
    }
  }
}
