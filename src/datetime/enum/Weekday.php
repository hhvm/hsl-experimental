<?hh
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Experimental\DateTime;

enum Weekday: int {
  // Numbers are ISO-8601 compliant.
  MONDAY = 1;
  TUESDAY = 2;
  WEDNESDAY = 3;
  THURSDAY = 4;
  FRIDAY = 5;
  SATURDAY = 6;
  SUNDAY = 7;
}
