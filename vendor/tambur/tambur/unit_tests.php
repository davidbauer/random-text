#!/usr/bin/env php

<?php

require_once 'tambur.php';
define('VERBOSE', true);

print("Starting Unit Tests\n----\n");



/* BEGIN UNIT TESTS */
$credentials = get_bot_credentials();
$subscriber_id = get_subscriber_id();
$tambur =  new TamburClient($credentials['api_key'], $credentials['app_id'], $credentials['secret']);

test('test_publish');
test('test_auth_publish');
test('test_private_publish');
test('test_generate_auth_token');
test('test_generate_presence_token');
test('test_generate_direct_token');

function test_publish() {
    publish('test', 'test message');
}

function test_auth_publish() {
    publish('auth:test', 'test message');
}

function test_private_publish() {
    global $subscriber_id;
    publish('private:' . $subscriber_id, 'test message');
}

function publish($stream, $msg) {
    global $tambur;
    $handle = generate_handle();
    $msg = array('msg' => $msg, 'handle' => $handle);
    $json_msg = json_encode($msg);
    $response = $tambur->publish($stream, $json_msg);
    test_assert($response[0]['http_code'] == '204');
    sleep(1);
    $results = TamburUtils::http_request('GET', 'http://wsbot.tambur.io/results?handle=' . $handle);
    $results = $results[1];
    $json_results = json_decode($results, true);
    test_assert(sizeof($json_results) == 1);
    if ($stream == 'test' or $stream == 'auth:test') {
        test_assert($json_results[0][$handle] == array($stream => $msg));
    } else {
        test_assert($json_results[0][$handle] == array('private' => $msg));
    }
}

function test_generate_auth_token() {
    $t = new TamburClient('30af96de47e3c58329045ff136a4a3ea', 'ws-bot-1', 'wsbot');
    $token = $t->generate_auth_token('test', 'a0629978-28d8-4fd4-b862-f67e9b6dfd8f');
    test_assert('2f25ad1ce5afab906cc582b6254a912590c60f73' == $token);
}

function test_generate_presence_token() {
    $t = new TamburClient('30af96de47e3c58329045ff136a4a3ea', 'ws-bot-1', 'wsbot');
    $token = $t->generate_presence_token('test', 'test_user', 'a0629978-28d8-4fd4-b862-f67e9b6dfd8f');
    test_assert('dcadf9659116ebbe024a4cd5ae12bde48d95408e' == $token);
}

function test_generate_direct_token() {
    $t = new TamburClient('30af96de47e3c58329045ff136a4a3ea', 'ws-bot-1', 'wsbot');
    $token = $t->generate_direct_token('test', 'test_user', 'a0629978-28d8-4fd4-b862-f67e9b6dfd8f');
    test_assert('2403374744295f5d22e3f999d4eb85b3f689c6b2' == $token);
}

function get_bot_credentials() {
    $res = TamburUtils::http_request('GET', 'http://wsbot.tambur.io/credentials');
    return json_decode($res[1], true);
}
function get_subscriber_id() {
    $res = TamburUtils::http_request('GET', 'http://wsbot.tambur.io/subscriber_id');
    return $res[1];
}

function generate_handle() {
    $length = 6;
    $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'; 
    $str = '';
    $count = strlen($charset);
    while ($length--) {
        $str .= $charset[mt_rand(0, $count-1)];
    }
    return $str;
}

/* BEGIN UNIT TEST FRAMEWORK 
 * taken from https://github.com/basho/riak-php-client/blob/master/unit_tests.php
 * */
$test_pass = 0; $test_fail = 0;

function test($method) {
  global $test_pass, $test_fail;
  try {
    $method();
    $test_pass++;
    print "  [.] TEST PASSED: $method\n";
  } catch (Exception $e) {
    $test_fail++;
    print "  [X] TEST FAILED: $method\n";
    if (VERBOSE) {
      throw $e;
    }
  }
}

function test_summary() {
  global $test_pass, $test_fail;
  if ($test_fail == 0) {
    print "\nSUCCESS: Passed all $test_pass tests.\n";
  } else {
    $test_total = $test_pass + $test_fail;
    print "\nFAILURE: Failed $test_fail of $test_total tests!";
  }
}

function test_assert($bool) {
  if (!$bool) throw new Exception("Test failed.");
}

/* END UNIT FRAMEWORK */

?>
