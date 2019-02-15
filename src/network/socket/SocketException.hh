<?hh // strict

namespace HH\Lib\Experimental\Network;

use type RuntimeException;

/**
 * Is thrown when a network-related error is encountered.
 */
final class SocketException extends RuntimeException {}