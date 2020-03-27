<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

use namespace HH\Lib\Vec;
use namespace HH\Lib\{IO, Network, OS, TCP};

use function Facebook\FBExpect\expect; // @oss-enable
use type Facebook\HackTest\HackTest; // @oss-enable
use type Facebook\HackTest\DataProvider; // @oss-enable
// @oss-disable: use type HackTest;
use type HH\Lib\Network\{IPProtocolBehavior, IPProtocolVersion};
use type HH\Lib\Ref;

// @oss-disable: <<Oncalls('hf')>>
final class HSLTCPTest extends HackTest {
  public static function provideConnectionParameters(
  ): vec<(IPProtocolVersion, string, string, IPProtocolBehavior)> {
    return vec[
      tuple(
        IPProtocolVersion::IPV6,
        'localhost',
        '::1',
        IPProtocolBehavior::PREFER_IPV6,
      ),
      tuple(
        IPProtocolVersion::IPV6,
        'localhost',
        'localhost',
        IPProtocolBehavior::PREFER_IPV6,
      ),
      tuple(
        IPProtocolVersion::IPV6,
        'localhost',
        '::1',
        IPProtocolBehavior::FORCE_IPV6,
      ),
      tuple(
        IPProtocolVersion::IPV4,
        'localhost',
        '127.0.0.1',
        IPProtocolBehavior::PREFER_IPV6,
      ),
      tuple(
        IPProtocolVersion::IPV4,
        'localhost',
        'localhost',
        IPProtocolBehavior::PREFER_IPV6,
      ),
      tuple(
        IPProtocolVersion::IPV4,
        'localhost',
        '127.0.0.1',
        IPProtocolBehavior::FORCE_IPV4,
      ),
    ];
  }

  <<DataProvider('provideConnectionParameters')>>
  public async function testBasicConnectivity(
    IPProtocolVersion $server_protocol,
    string $bind_address,
    string $client_address,
    IPProtocolBehavior $client_protocol,
  ): Awaitable<void> {
    try {
      $server = await TCP\Server::createAsync(
        $server_protocol,
        $bind_address,
        0,
      );
    } catch (OS\Exception $e) {
      expect($e->getErrorCode())->toEqual(OS\ErrorCode::EADDRNOTAVAIL);
      expect($server_protocol)->toEqual(IPProtocolVersion::IPV6);
      self::markTestSkipped("IPv6 not supported on this host");
      return;
    }
    list($host, $port) = $server->getLocalAddress();
    expect($host)->toNotEqual($bind_address);
    expect($port)->toNotEqual(0);
    $server_recv = new Ref('');
    $client_recv = new Ref('');
    concurrent {
      await async {
        ///// Server /////
        await using ($client = await $server->nextConnectionAsync()) {
          $server_recv->value = await $client->readLineAsync();
          await $client->writeAsync("foo\n");
        }
        ;
      };
      await async {
        ///// client /////
        await using (
          $conn = await TCP\connect_async(
            $client_address,
            $port,
            shape('ip_version' => $client_protocol),
          )
        ) {
          list($ph, $pp) = $conn->getPeerAddress();
          $expected = vec[$host];
          if (
            $host === '127.0.0.1' &&
            $client_protocol === IPProtocolBehavior::PREFER_IPV6
          ) {
            $expected[] = '::ffff:'.$host;
          }
          expect($expected)->toContain($host);
          expect($pp)->toEqual($port);
          list($lh, $lp) = $conn->getLocalAddress();
          expect($lh)->toEqual($ph);
          expect($lp)->toNotEqual($pp);
          await $conn->writeAsync("bar\n");
          $client_recv->value = await $conn->readLineAsync();
        }
      };
    }
    expect($client_recv->value)->toEqual("foo\n");
    expect($server_recv->value)->toEqual("bar\n");
  }

  public async function testConnectingToInvalidPort(): Awaitable<void> {
    $ex = expect(async () ==> await TCP\connect_nd_async('localhost', 0))
      ->toThrow(OS\Exception::class);
    expect(vec[OS\ErrorCode::EADDRNOTAVAIL, OS\ErrorCode::ECONNREFUSED])
      ->toContain($ex->getErrorCode());
  }

  public async function testReuseAddress(): Awaitable<void> {
    $s1 = await TCP\Server::createAsync(
      IPProtocolVersion::IPV4,
      '127.0.0.1',
      0,
      // Portability:
      // - MacOS only requires SO_REUSEADDR to be set on the new socket
      // - Linux requires SO_REUSEADDR on both the old and the new socket
      shape('socket_options' => shape('SO_REUSEADDR' => true)),
    );
    $port = $s1->getLocalAddress()[1];
    concurrent {
      $client = await TCP\connect_nd_async('127.0.0.1', $port);
      $_ = await $s1->nextConnectionNDAsync();
    }
    await $client->writeAsync('hello, world');
    $s1->stopListening();

    $ex = expect(
      async () ==> await TCP\Server::createAsync(
        IPProtocolVersion::IPV4,
        '127.0.0.1',
        $port,
      ),
    )->toThrow(OS\Exception::class);
    expect($ex->getErrorCode())->toEqual(OS\ErrorCode::EADDRINUSE);

    $s2 = await TCP\Server::createAsync(
      IPProtocolVersion::IPV4,
      '127.0.0.1',
      $port,
      shape('socket_options' => shape('SO_REUSEADDR' => true)),
    );
    concurrent {
      $client_recv = await async {
        await using $client = await TCP\connect_async('127.0.0.1', $port);
        await $client->writeAsync("hello, world!\n");
        return await $client->readLineAsync();
      };
      $server_recv = await async {
        await using $conn = await $s2->nextConnectionAsync();
        await $conn->writeAsync("foo bar\n");
        return await $conn->readLineAsync();
      };
    }
    expect($client_recv)->toEqual("foo bar\n");
    expect($server_recv)->toEqual("hello, world!\n");
  }
}
