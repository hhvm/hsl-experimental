
<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */
use namespace HH\Lib\Experimental\Dict;
use function Facebook\FBExpect\expect;
use type Facebook\HackTest\{DataProvider, HackTest}; // @oss-enable

// @oss-disable: <<Oncalls('hack')>>
final class DictCombineTest extends HackTest {
  public static function provideTestUnion(): varray<mixed> {
    return varray[
      tuple(
        vec[
          Map {'a' => 'apple', 'b' => 'banana'},
          dict['a' => 'pear', 'b' => 'strawberry', 'c' => 'cherry'],
          darray['c' => 'chocolat']
        ],
        dict['a' => 'apple', 'b' => 'banana', 'c' => 'cherry']
      ),
      tuple(
        vec[
          dict['a' => 'pear', 'b' => 'strawberry', 'c' => 'cherry'],
          Map {'a' => 'apple', 'b' => 'banana'},
          darray['c' => 'chocolat']
        ],
        dict['a' => 'pear', 'b' => 'strawberry', 'c' => 'cherry']
      ),
      tuple(
        vec[
          darray['c' => 'chocolat']
          dict['a' => 'pear', 'b' => 'strawberry', 'c' => 'cherry'],
          Map {'a' => 'apple', 'b' => 'banana'},
        ],
        dict['c' => 'chocolat', 'a' => 'pear', 'b' => 'strawberry']
      ),
    ];
  }
  
  <<DataProvider('provideTestUnion')>>
  public function testUnion<Tk as arraykey, Tv>(
    Container<KeyedTraversable<Tk, Tv>> $traversables,
    dict<Tk, Tv> $exprected
  ): void {
    expect(Dict\union(...$traversables))->toBeSame($expected); 
  }
}
