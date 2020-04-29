<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\{Network, Unix};
use namespace HH\Lib\{Math, PseudoRandom};

use function Facebook\FBExpect\expect; // @oss-enable
use type Facebook\HackTest\HackTest; // @oss-enable
use type Facebook\HackTest\DataProvider; // @oss-enable
// @oss-disable: use type HackTest;
use type HH\Lib\Network\{IPProtocolBehavior, IPProtocolVersion};
use type \HH\Lib\Ref;

// @oss-disable: <<Oncalls('hf')>>
final class HSLUnixSocketTest extends HackTest {
  public async function testBasicConnectivity(): Awaitable<void> {
    /* HH_IGNORE_ERROR[2049] PHPStdLib */
    /* HH_IGNORE_ERROR[4107] PHPStdLib */
    $path = \sys_get_temp_dir().'/hsl-unix-socket-'.PseudoRandom\int(0, Math\INT64_MAX).'.sock';
    try {
      $server = await Unix\Server::createAsync($path);
      // FIXME: HHVM bug (D21295118) truncates the last character on MacOS.
      // expect($server->getLocalAddress())->toEqual($path);
      $server_recv = new Ref('');
      $client_recv = new Ref('');
      concurrent {
        await async {
          ///// Server /////
          await using ($client = await $server->nextConnectionAsync()) {
            // FIXME: HHVM bug (D21295118) truncates the last character on MacOS.
            // expect($client->getLocalAddress())->toEqual($path);
            expect($client->getPeerAddress())->toBeNull();

            $server_recv->value = await $client->readAsync();
            await $client->writeAsync("foo\n");
          }
          ;
        };
        await async {
          ///// client /////
          await using ($conn = await Unix\connect_async($path)) {
            expect($conn->getLocalAddress())->toBeNull();
            // FIXME: HHVM bug (D21295118) truncates the last character on MacOS.
            // expect($conn->getPeerAddress())->toEqual($path);

            await $conn->writeAsync("bar\n");
            $client_recv->value = await $conn->readAsync();
          }
        };
      }
      expect($client_recv->value)->toEqual("foo\n");
      expect($server_recv->value)->toEqual("bar\n");
    } finally {
      /* HH_IGNORE_ERROR[2049] PHPStdLib */
      /* HH_IGNORE_ERROR[4107] PHPStdLib */
      \unlink($path);
    }
  }
}
