<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\DateTime\_Private;

const int NS_IN_SEC = 1000000000;

function days_in_month(int $year, int $month): int {
  return $month === 2 && is_leap_year($year)
    ? 29
    : vec[31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][$month - 1];
}

function is_leap_year(int $year): bool {
  return $year % 4 === 0 && ($year % 100 !== 0 || $year % 400 === 0);
}
