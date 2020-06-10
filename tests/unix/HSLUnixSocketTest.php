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
    // Not using sys_get_temp_dir() as on MacOS, that defaults to something too
    // long to reliably be valid unix socket paths.
    $path = '/tmp/hsl-unix-socket-'.PseudoRandom\int(0, Math\INT64_MAX).'.sock';
    try {
      $server = await Unix\Server::createAsync($path);
      expect($server->getLocalAddress())->toEqual($path);
      $server_recv = new Ref('');
      $client_recv = new Ref('');
      concurrent {
        await async {
          ///// Server /////
          $client = await $server->nextConnectionAsync();
          expect($client->getLocalAddress())->toEqual($path);
          expect($client->getPeerAddress())->toEqual(null);

          $server_recv->value = await $client->readAsync();
          await $client->writeAsync("foo\n");
          $client->close();
        };
        await async {
          ///// client /////
          $conn = await Unix\connect_async($path);
          expect($conn->getLocalAddress())->toEqual(null);
          expect($conn->getPeerAddress())->toEqual($path);

          await $conn->writeAsync("bar\n");
          $client_recv->value = await $conn->readAsync();
          $conn->close();
        };
      }
      expect($client_recv->value)->toEqual("foo\n");
      expect($server_recv->value)->toEqual("bar\n");
    } finally {
      /* HH_FIXME[2049] PHPStdLib */
      /* HH_FIXME[4107] PHPStdLib */
      \unlink($path);
    }
  }
}
