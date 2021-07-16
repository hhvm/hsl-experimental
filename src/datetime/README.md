# Dates & Times in the Hack Standard Library

Documentation should eventually be available at
[docs.hhvm.com](https://docs.hhvm.com/hsl-experimental/reference/).

## Library design

Previous versions:

- [original proposal](https://github.com/hhvm/hsl-experimental/issues/93)
  ([FB-only version](https://fb.quip.com/zkf0AZAVo8vY) with inline comments)
- [first iteration](https://gist.github.com/jjergus/025dc0bc343fcc825390c92836995b84)
- [second iteration](https://gist.github.com/jjergus/0298b1213f947cee8692255bbb3e4996)

Most important differences from previous versions:

- removed all scalar types -- everything is an object now, and there are no longer two possible representations of any single concept (e.g. `Time\Interval` vs `DateTime\TimeInterval` in v2)
- removed `DayInterval` and `MonthInterval` -- all intervals are now represented by `HH\Lib\Experimental\Time` (equivalent to `Time\Interval` and `DateTime\TimeInterval` from v2)
- added `DateTime\Instant` -- shared interface for `DateTime\Zoned` and `DateTime\Timestamp`

### Why (now)?

- based on an informal survey of external Hack users (Slack etc.), date/time was the most requested addition to HSL
- we analyzed internal projects that we might want to open-source in the future (GraphQL, HackAst, ...) to see which `PHP\` functions they use most, and date/time manipulation was very common (also IO, but that's already in HSL-experimental)

### Basic types

- `HH\Lib\Experimental\Time`
  - represents a time interval like "42 hours and 3.5 seconds"
  - mostly useful as an input or a result of an operation with date/time obejcts or timestamps
  - provides automatic normalization (see docblock for details), so there aren't multiple representations of the same time interval (1 hour vs 60 minutes)
- `HH\Lib\Experimental\DateTime\Timestamp`
  - a point in time on the real-life clock, with nanosecond resolution
  - internally, it's a Unix timestamp, but that can be considered an implementation detail
- `HH\Lib\Experimental\MonoTime\Timestamp`
  - a point in time on the "monotonic" clock (aka `steady_time` in C++)
- `HH\Lib\Experimental\DateTime\Zoned`
  - object representing a combination of date/time parts associated with a timezone
  - therefore representing an "absolute" point in time (can be transformed to a `DateTime\Timestamp` with no additional information needed)
- `HH\Lib\Experimental\DateTime\Unzoned`
  - object representing a combination of date/time parts with no timezone associated
  - therefore not actually representing an "absolute" point in time, e.g. you can only transform it to a timestamp if you provide a timezone
  - alternative name for `Unzoned` is `Local` (used e.g. in Java); I'm not opposed to this but I think there's some confusion around whether the zoned or the unzoned time is more appropriate to call "local"
  - TODO: we could support a literal syntax to create Unzoned objects, e.g. `dt"2020-02-13 10:08:42.026"` (this wouldn't be practical for Zoned objects because of the extra validation needed)

### Helper types

- `HH\Lib\Experimental\DateTime\DateTime`
  - base class for `DateTime\Zoned` and `DateTime\Unzoned`
  - may be useful as a typehint if you're writing code that accepts either one (almost all methods are actually declared here, so you don't lose much compared to using a more specific typehint, and your code will be more general)
- `HH\Lib\Experimental\DateTime\Instant`
  - shared interface for `DateTime\Zoned` and `DateTime\Timestamp` (both of which represent an absolute point in time, unlike `DateTime\Unzoned`)
  - most methods on `DateTime\Zoned` and `DateTime\Timestamp` take an argument of this type instead of a more specific one, making them more flexible
- `HH\Lib\Experimental\DateTime\Builder`
  - instances of this are returned by methods on `Zoned`/`Unzoned` that aren't guaranteed to result in a valid date/time
  - e.g. `$datetime->withMonth($new_month)` returns a `Builder` because `$new_month` may have less days than the current month
  - can be converted back to `Zoned`/`Unzoned` using:
    - `->exactX()` which throws if the exact date/time is invalid, or
    - `->closest()` which adjusts any invalid parts, e.g. changes the day to the last valid day of `$new_month`
  - note: I considered various altenatives to `DateTime\Builder`, see the "Why Builder?" section at the end of this document

### Examples

Full list of available functions should be available at
[docs.hhvm.com](https://docs.hhvm.com/hsl-experimental/reference/),
here we illustrate how the library "feels" with some simple examples:

#### Timing an operation

```hack
$start = MonoTime\Timestamp::now();
// ...expensive operation...
$time = MonoTime\Timestamp::now()->timeSince($start);

// Was it more than a minute?
if ($time->isLonger(Time::minutes(1))) { ... }

// Human-friendly output can be achieved by:
printf('%dh %dm %ds', $time->getHours(), $time->getMinutes(), $time->getSeconds());
// or just:
echo $time->toString();
```

#### Date/time transformations

Time three and a half hours (3:30 hours) from now:

```hack
// with Timestamp:
$ts = DateTime\Timestamp::now()
  ->plusHours(3)
  ->plusMinutes(30)
  ->format('%H:%M:%S', $timezone);

// equivalently with Zoned:
$dt = DateTime\Zoned::now($timezone)
  ->plusHours(3)
  ->plusMinutes(30)
  ->format('%H:%M:%S');

// using a Time object:
$dt = DateTime\Zoned::now($timezone)
  ->plus(Time::hours(3, 30))
  ->format('%H:%M:%S');
```

Operations that may result in an invalid date/time:

```hack
$year = 2020;
$dt = DateTime\Unzoned::fromParts($year, 2, 29)->exactX();
// would have thrown if $year was not a leap year

$dt->plusYears(1)->exactX()  // throws because 2021 is not a leap year
$dt->plusYears(1)->closest() // returns adjusted date 2021-02-28

// same day of the next month:
$dt = DateTime\Unzoned::fromParts(2020, 1, 31)->exactX();
$dt->plusMonths(1)->exactX()  // throws because 2020-02-31 is invalid
$dt->plusMonths(1)->closest() // returns 2020-02-29

// beginning of next month:
$dt->plusMonths(1)->withDay(1)->exactX() // returns 2020-02-01
// will not throw because the final date is valid, even though it goes through
// an invalid intermediate state (exactX would have thrown before withDay)
```

Convert between timezones:

```hack
// if you have date/time parts + original timezone:
echo DateTime\Zoned::fromParts($src_timezone, $y, $mon, $d, $h, $min)->exactX()
  ->convertToTimezone($target_timezone)
  ->format('%Y-%m-%d %H:%M:%S');

// if you have a timestamp (remember, timestamps are absolute):
echo $timestamp->convertToTimezone($target_timezone)->format(...);

// Changing a timezone of an object *without* conversion (probably less useful,
// so this example might be a bit contrived):
// This gets the timestamp of the same "wall time" in another timezone (e.g. if
// it's currently 16:30 in $src_timezone, will get timestamp of 16:30 in
// $target_timezone):

echo DateTime\Zoned::now($src_timezone)
  ->withoutTimezone()  // converts DateTime\Zoned to DateTime\Unzoned
  ->withTimezone($target_timezone)->exactX()  // converts Unzoned to Zoned
  ->getTimestamp();

// (explicit conversion to Unzoned and back is required, to prevent confusion
// around whether the time is being converted like above, or not)
```

#### Fun with tuples

I provide various functions that return tuples, which are explicitly designed to be compatible with arguments of other functions (using the `...` operator). I think this can be very practical:

```hack
$time = $dt1->getTime(); // (hours, minutes, seconds, nanoseconds)
$dt2 = $dt2->withTime(...$time)->exactX();

$raw = $timestamp->toRaw();
// later
$timestamp = DateTime\Timestamp::fromRaw(...$raw);
```

However, these are optional, you can always do `$dt1->getHours()`, `$dt1->getMinutes()` etc. if you want to avoid tuples.

There is a guarantee that all tuples as well as function arguments are always "big endian" (year before month, day, hour, minute, second, ns), so there is no ambiguity.

### Design decisions

#### Leap seconds

Leap seconds are **not supported**. The main reason is to have 1:1 mapping with Unix timestamps, which are defined to ignore leap seconds. AFAIK most operating systems ignore them too.

There is very little to be gained from leap second support, and it has significant disadvantages:

- everything is more complicated (e.g. "plus 1 hour" and even "plus 1 minute" becomes ambiguous because hours/minutes no longer have uniform length)
- it makes bugs more likely (i.e., it is much more likely that a bug is caused by `getSeconds()` returning 60 to a system not being able to handle it, than by `getSeconds()` skipping over a leap second even though the former is technically the correct behavior)

I wouldn't be opposed to adding support for leap seconds in some form, but even if we did I would argue that it should be disabled by default.

#### Representation of dates/times

- timestamps: object vs tuple vs single int
  - using a single 64-bit int would limit the timestamp range to years 1677--2262, which seemed too restrictive for a general purpose date/time library (e.g. we can't express dates of historical events)
  - previous version used a tuple `(sec, ns)`; in this version I decided to change it to an object, so that we can provide consistent API for timestamps and other date/time objects, including the shared `Instant` interface
- all objects are immutable
  - seemed pretty obviously right for datetime objects and intervals
  - I considered making the Builder mutable
    - this would make it possible to change multiple parts of a datetime object without creating several intermediate objects (`$dt->withDay($d)->withMonth($m)->withYear($y)->exactX()` creates 3 throwaway objects)
    - but IMHO could cause subtle bugs (`$builder = $dt->withDay($d)` stores an object that can be mutated from any random place where we pass it)
    - instead, I tried to provide "multisetters" for common combinations (`$dt->withDate($y, $m, $d)` avoids the throwaway objects)

#### Why Builder?

I considered various altenatives to `DateTime\Builder`, mostly based on the original proposal:

- having 2 variants of each method: `withMonthX()` and `withMonthApprox()`
- having `withMonthX()` that throws and `withMonth()` that returns a tuple `(result, whether_it_was_adjusted_and_why)` (this solution was favoured in the original proposal)

I believe the `Builder` solution is superior because it's basically a generalized version of the latter -- unlike a simple tuple, the builder object can easily be extended to provide more information in a more intuitive way (`isValid`, `getReasonWhyInvalid`... vs `$result[1]` being some cryptic constant), or to provide more functionality (e.g. a variant of `getClosest()` that allows you to fine-tune which adjustments are allowed might be useful).

The `Builder` solution also has additional advantages:

- allows going through invalid intermediate states (IMHO the main advantage): `$dt->nextMonth()->withDay(1)->exactX()` will not throw even if `$dt->nextMonth()->exactX()` would have
- allows "decoupling": project X that uses this library can return a `Builder`, then some completely unrelated code in project Y can decide whether it wants to call `exactX()` or `closest()` on the builder returned from project X

#### On plus/minusDays()

It's not immediately obvious, but due to DST changes (clock moving forward/back
one hour), the interval "1 day" is ambiguous.

This is why there isn't a `Time::days(...)` function and why `plusDays()`/`minusDays()` returns a Builder.

I have very mixed feelings about the design tradeoffs here. On one hand, this is
such a rare case that I feel bad making the API more complicated because of it.
On the other hand, based on my anecdotal experience, the missing/extra hour
during DST changes is the #1 source of datetime-related bugs*, so it may very
well warrant explicitly designing the API around it? On the third hand, if we
don't provide `Time::days(...)`, people will most likely just do
`Time::hours(24 * $d)`, so I'm not sure if we're actually preventing anything by
avoiding the dangerous function.

One possible solution I considered was adding `Time\days_approx(...)` or
something like that, to make it very explicit and force people to think about
the approximateness of the assumption "1 day = 24 hours"...

---

*There is literally an example in the original proposal that
has that bug:

```hack
$next_midnight = $now->plusDays(1)->withTimeX(0, 0, 0);
// fails if assuming 1 day = 24 hours
```
