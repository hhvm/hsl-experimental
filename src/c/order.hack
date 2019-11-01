namespace HH\Lib\C;

/**
 * Returns true if the given Traversable<Tv> is sorted in ascending order.
 * If two neighbouring elements compare equal, this will be considered sorted.
 *
 * If no $comparator is provided, the `<=>` operator will be used. 
 * This will sort numbers by value, strings by alphabetical order
 * and DateTime/DateTimeImmutable by their unixtime.
 *
 * If the comparison operator `<=>` is not useful on Tv
 * and no $comparator is provided, the result of is_sorted
 * will not be useful.
 *
 * Time complexity: O((n log n) * c), where c is the complexity of the
 * comparator function (which is O(1) if not provided explicitly)
 * Space complexity: O(n)
 */
<<__Rx, __AtMostRxAsArgs, __ProvenanceSkipFrame>>
function is_sorted<Tv>(
  <<__MaybeMutable, __OnlyRxIfImpl(\HH\Rx\Traversable::class)>>
  Traversable<Tv> $traversable,
  <<__AtMostRxAsFunc>> ?(function(Tv, Tv): int) $comparator = null,
): bool {
  $vec = vec($traversable);
  if (is_empty($vec)) {
    return true;
  }

  $comparator ??= ($a, $b) ==>
    /*HH_IGNORE_ERROR[4240] Comparison may not be useful on Tv*/$a <=> $b;

  $previous = firstx($vec);
  foreach ($vec as $next) {
    if ($comparator($next, $previous) < 0) {
      return false;
    }
    $previous = $next;
  }

  return true;
}
