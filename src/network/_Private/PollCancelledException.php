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

final class PollCancelledException extends \Exception {
  public function __construct(private int $result) {
    parent::__construct();
  }

  public function getResult(): int {
    return $this->result;
  }
}
