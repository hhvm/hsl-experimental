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
use namespace HH\Lib\Experimental\_Private\_DateTime;

/**
 * strftime(3)-compatible format string that includes standard placeholders
 * like `%y` for year, but does not include timezone placeholders like `%z`.
 */
type UnzonedDateFormatString = \HH\FormatString<_DateTime\DateFormat>;

/**
 * strftime(3)-compatible format string that includes standard placeholders
 * like `%y` for year, as well as timezone placeholders like `%z`.
 */
type ZonedDateFormatString = \HH\FormatString<_DateTime\ZonedDateFormat>;
