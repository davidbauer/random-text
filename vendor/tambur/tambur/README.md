## PHP Tambur.io REST API Client [![Build Status](https://secure.travis-ci.org/tamburio/php-tambur.png?branch=master)](http://travis-ci.org/tamburio/php-tambur)
Tambur.io provides a pain-free websocket experience.
###Preliminary
Please register on [Tambur.io][1] and create at least one app. This will give you the unique API\_KEY, an APP\_ID and a SECRET, which you need to initialize the client.
####Composer Install
Install composer in your project:
```
curl -s https://getcomposer.org/installer | php
```
Create a <code>composer.json</code> file in your project root:
```javascript
{
    "require": {
        "tambur/tambur": "dev-master"
    }
}
```
Install via composer:
```
php composer.phar install
```
Add this line to your code:
```PHP
<?php
require 'vendor/autoload.php';
```

####Manual Install
Download and extract the Tambur library into your project directory and require it in your application.
```PHP
<?php
require_once 'tambur.php';
```

###Example
```PHP
<?php
require 'vendor/autoload.php';
...
$tambur = new TamburClient(API_KEY, APP_ID, SECRET);
$tambur->publish('mystream', 'some message');
```
The example above publishes the given message to all subscribed clients. Clients can set different modes for streams they have subscribed. Currently the Tambur.io supports a 'auth', 'presence', and 'direct' mode. But you must grant permission by issueing a specific mode token. For generating such tokens you typically need the StreamName, the SubscriberId, and for direct- and presence-modes the UserId. The SubscriberId is the only parameter your clients must send you e.g. through some AJAX request which you can answer with the generated mode token.

####Auth Mode
If you issue an auth mode token, the client can authenticate himself for a particular stream. If you use the <code>auth:</code> stream prefix you can control how we deliver your message.
```php
$tambur->publish('auth:mystream', 'some auth message');
```
The example above publishes the given message to all subscribed clients that have the auth mode enabled.
Issuing an auth mode token is straight forward:
```php
$tambur->generate_auth_token('mystream', $subscriber_id);
```

####Presence Mode 
If you issue a presence mode token, the client can switch on and off presence mode. If presence mode is switched on the client will receive join- and leave-events of all the other presence clients in that particular stream. Issuing a presence mode token needs an extra 'User-Id' parameter, which enables you to connect a tambur.io subscriber-id to a User-Id used in your application:
```php
$tambur->generate_presence_token('mystream', 'Alice', $subscriber_id);
```

####Direct Mode
If you issue a direct mode token, the client can switch on and off direct mode. If direct mode is switched on the client can send and receive messages from other clients in that particular stream. Issuing a direct mode token needs an extra 'User-Id' parameter, which enables you to connect a tambur.io subscriber-id to a User-Id used in your application:
```php
$tambur->generate_direct_token('mystream', 'Alice', $subscriber_id);
```

####Private Messages
If you want to further limit your receivers to one specific subscriber you can do this using the 'private' stream.
```php
$tambur->publish('private:' . $subscriber_id, 'some private message');
```

##License (MIT)
Copyright (c) \<2012\> \<Tambur.io\>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

  [1]: http://tambur.io
