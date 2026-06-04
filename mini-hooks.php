<?php

// mini-hooks.php — a minimal, runnable reimplementation of the WordPress hook system.
// Demonstrates: add_filter / add_action / apply_filters / do_action / remove_filter /
// did_action / current_filter, priority ordering, and accepted_args slicing.
//
// Run with: php mini-hooks.php
//
// No WordPress, no Composer, no framework. Paired with section A.4.1 of
// new-employee-roadmap.md — read the "eight facts" there first, then run this
// file and watch each fact play out.

class MiniHooks{
    /** @var array<string, array<int, array<string, array{function: callable, accepted_args: int}>>> */
    private static $filters = [];

    /** @var array<string, int> */
    private static $actions = [];

    /** @var string[] */
    private static $current = [];

    public static function add_filter( string $tag, callable $cb, int $priority = 10, int $accepted_args = 1 ): void {
        $id = self::callback_id( $cb );
        self::$filters[ $tag ][ $priority ][ $id ] = [
            'function'      => $cb,
            'accepted_args' => $accepted_args,
        ];
        ksort( self::$filters[ $tag ] ); // priorities low-to-high
    }

    public static function add_action( string $tag, callable $cb, int $priority = 10, int $accepted_args = 1 ): void {
        self::add_filter( $tag, $cb, $priority, $accepted_args ); // actions ARE filters
    }

    public static function remove_filter( string $tag, callable $cb, int $priority = 10 ): bool {
        $id = self::callback_id( $cb );
        if ( isset( self::$filters[ $tag ][ $priority ][ $id ] ) ) {
            unset( self::$filters[ $tag ][ $priority ][ $id ] );
            return true;
        }
        return false;
    }

    public static function apply_filters( string $tag, $value, ...$args ) {
        self::$current[] = $tag;
        array_unshift( $args, $value ); // the filtered value is always arg #0

        if ( isset( self::$filters[ $tag ] ) ) {
            foreach ( self::$filters[ $tag ] as $priority => $bucket ) {
                foreach ( $bucket as $entry ) {
                    $slice   = array_slice( $args, 0, $entry['accepted_args'] );
                    $args[0] = call_user_func_array( $entry['function'], $slice );
                }
            }
        }

        array_pop( self::$current );
        return $args[0];
    }

    public static function do_action( string $tag, ...$args ): void {
        self::$actions[ $tag ] = ( self::$actions[ $tag ] ?? 0 ) + 1;
        self::$current[] = $tag;

        if ( isset( self::$filters[ $tag ] ) ) {
            foreach ( self::$filters[ $tag ] as $priority => $bucket ) {
                foreach ( $bucket as $entry ) {
                    $slice = array_slice( $args, 0, $entry['accepted_args'] );
                    call_user_func_array( $entry['function'], $slice ); // return value discarded
                }
            }
        }

        array_pop( self::$current );
    }

    public static function did_action( string $tag ): int {
        return self::$actions[ $tag ] ?? 0;
    }

    public static function current_filter(): ?string {
        return end( self::$current ) ?: null;
    }

    private static function callback_id( callable $cb ): string {
        if ( is_string( $cb ) )       return $cb;
        if ( $cb instanceof Closure ) return spl_object_hash( $cb );
        if ( is_array( $cb ) ) {
            [ $obj, $method ] = $cb;
            return ( is_object( $obj ) ? spl_object_hash( $obj ) : $obj ) . '::' . $method;
        }
        return spl_object_hash( (object) $cb );
    }
}

// ---------------------------------------------------------------------------
// DEMO — run with: php mini-hooks.php
// ---------------------------------------------------------------------------

echo "=== 1. Filters transform a value, priority decides order ===\n";
MiniHooks::add_filter( 'the_price', fn( $p ) => $p + 10, 20 );  // runs second
MiniHooks::add_filter( 'the_price', fn( $p ) => $p * 2,  10 );  // runs first (lower priority)
$final = MiniHooks::apply_filters( 'the_price', 100 );
echo "100 → *2 (prio 10) → +10 (prio 20) = {$final}\n\n"; // 210

echo "=== 2. Actions fire side effects, return values are ignored ===\n";
MiniHooks::add_action( 'user_registered', function( $name ) {
    echo "  send welcome email to {$name}\n";
});
MiniHooks::add_action( 'user_registered', function( $name ) {
    echo "  log signup for {$name}\n";
}, 20 );
MiniHooks::do_action( 'user_registered', 'Alice' );
echo "\n";

echo "=== 3. accepted_args controls how many args reach the callback ===\n";
MiniHooks::add_action( 'order_changed', function( $order_id ) {
    echo "  1-arg callback sees only order_id={$order_id}\n";
}); // default accepted_args = 1
MiniHooks::add_action( 'order_changed', function( $order_id, $from, $to ) {
    echo "  3-arg callback sees order_id={$order_id}, {$from} → {$to}\n";
}, 10, 3 ); // asks for all 3
MiniHooks::do_action( 'order_changed', 42, 'pending', 'processing' );
echo "\n";

echo "=== 4. did_action counts firings ===\n";
MiniHooks::do_action( 'user_registered', 'Bob' );
MiniHooks::do_action( 'user_registered', 'Carol' );
echo "  user_registered has fired " . MiniHooks::did_action( 'user_registered' ) . " times\n\n";

echo "=== 5. remove_filter works when you keep the reference ===\n";
$noisy = function( $s ) { return $s . '!!!'; };
MiniHooks::add_filter( 'shout', $noisy );
echo "  before remove: " . MiniHooks::apply_filters( 'shout', 'hello' ) . "\n";
MiniHooks::remove_filter( 'shout', $noisy );
echo "  after remove:  " . MiniHooks::apply_filters( 'shout', 'hello' ) . "\n";
