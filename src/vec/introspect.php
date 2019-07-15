<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace HH\Lib\Vec;

use namespace HH\Lib\C;

/**
 * Returns true if the given `Container<num>` is sorted using strict weak ordering.
 * Sort order is ascending.
 *
 * Time complexity: O(n), where `n` is the size of $c
 * Space complexity: O(1)
 */
<<__Rx, __ProvenanceSkipFrame>>
function is_sorted(Container<num> $c): bool {
	if (C\is_empty($c)) {
		return true;
	}

	$laggard = C\firstx($c);
	foreach ($c as $val) {
		if ($val < $laggard) {
			return false;
		}
		$laggard = $val;
	}

	return true;
}

/**
 * Returns true if the given `Container<T>` is sorted according to the supplied
 * $spaceship_func.
 * Sort order is ascending.
 *
 * A spaceship function must return:
 *  - A positve integer is the first argument is greater than the second
 *  - A negative integer is the first argument is lesser than the second
 *  - Zero if both arguments are equal
 *
 * Time complexity: O(n), where `n` is the size of $c
 * Space complexity: O(1)
 */
<<__Rx, __AtMostRxAsArgs, __ProvenanceSkipFrame>>
function is_sorted_by<T>(
	Container<T> $c,
	<<__AtMostRxAsFunc>> (function(T, T): int) $spaceship_func,
): bool {
	if (C\is_empty($c)) {
		return true;
	}

	$laggard = C\firstx($c);
	foreach ($c as $val) {
		if ($spaceship_func($laggard, $val) > 0) {
			return false;
		}
		$laggard = $val;
	}

	return true;
}
