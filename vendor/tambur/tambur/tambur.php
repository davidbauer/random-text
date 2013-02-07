<?php

class TamburClient {
    function TamburClient($api_key, $app_id, $secret, $ssl=False, $api_host='api.tambur.io') {
        $this->app_id = $app_id;
        $this->ssl = $ssl;
        $this->api_host = $api_host;
        $this->oauth = new TamburOAuth();
        $this->oauth->consumer_key = $api_key;
        $this->oauth->consumer_secret = $secret;
        $this->oauth->req_method = 'POST';
    }

    function publish($stream, $message) {
        $url = parse_url(TamburUtils::build_rest_path($this, $stream, $message));
        return TamburUtils::http_request('POST', $url['host'] . $url['path'], 
            array('Content-Type' => 'application/x-www-form-urlencoded'), $this->oauth->sign($url)->query_string());
    }

    function generate_auth_token($stream, $subscriber_id) {
        return $this->generate_mode_token('auth', $stream, $subscriber_id);
    }

    function generate_presence_token($stream, $user_id, $subscriber_id) {
        return $this->generate_mode_token('presence', $stream . ':' . $user_id, $subscriber_id);
    }

    function generate_direct_token($stream, $user_id, $subscriber_id) {
        return $this->generate_mode_token('direct', $stream . ':' . $user_id, $subscriber_id);
    }

    function generate_mode_token($mode, $property, $subscriber_id) {
        $mode_string = $this->oauth->consumer_key . ':' . $this->app_id . ':' . $mode . ':' . $property . ':' . $subscriber_id; 
        return hash_hmac('sha1', $mode_string, $this->oauth->consumer_secret);
    }
}

/**
 * Private class used to accumulate a CURL response.
 * @package TamburStringIO
 */
class TamburStringIO {
    function TamburStringIO() {
        $this->contents = '';
    }

    function write($ch, $data) {
        $this->contents .= $data;
        return strlen($data);
    }

    function contents() {
        return $this->contents;
    }
}


class TamburUtils {
    /**
     * Given a TamburClient, TamburStream, Message
     * construct and return a URL.
     */
    public static function build_rest_path($client, $stream, $message) {
        $path = $client->ssl ? 'https://' : 'http://';
        $path.= $client->api_host . '/app/' . $client->app_id . '/stream/' . $stream . '?api_version=1.0&message=' . urlencode($message);
        return $path;
    }

    /**
     * Given a Method, URL, Headers, and Body, perform and HTTP request,
     * and return an array of arity 2 containing an associative array of
     * response headers and the response body.
     *
     * taken from: https://github.com/basho/riak-php-client/blob/master/riak.php
     */
    public static function http_request($method, $url, $request_headers = array(), $obj = '') {
        # Set up curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);

        if ($method == 'GET') {
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
        } else if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $obj);
        } else if ($method == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $obj);
        } else if ($method == 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        # Capture the response headers...
        $response_headers_io = new TamburStringIO();
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array(&$response_headers_io, 'write'));

        # Capture the response body...
        $response_body_io = new TamburStringIO();
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, array(&$response_body_io, 'write'));

        try {
            # Run the request.
            curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            # Get the headers...
            $parsed_headers = TamburUtils::parse_http_headers($response_headers_io->contents());
            $response_headers = array("http_code"=>$http_code);
            foreach ($parsed_headers as $key=>$value) {
                $response_headers[strtolower($key)] = $value;
            }

            # Get the body...
            $response_body = $response_body_io->contents();

            # Return a new TamburResponse object.
            return array($response_headers, $response_body);
        } catch (Exception $e) {
            curl_close($ch);
            error_log('Error: ' . $e->getMessage());
            return NULL;
        } 
    }

    /**
     * Parse an HTTP Header string into an asssociative array of
     * response headers.
     * taken from: https://github.com/basho/riak-php-client/blob/master/riak.php
     */
    static function parse_http_headers($headers) {
        $retVal = array();
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $headers));
        foreach( $fields as $field ) {
            if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
                $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
                if( isset($retVal[$match[1]]) ) {
                    $retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
                } else {
                    $retVal[$match[1]] = trim($match[2]);
                }
            }
        }
        return $retVal;
    } 
}

class TamburOAuth {
    function TamburOAuth($consumer_key = '', $consumer_secret = '', $token = '', $token_secret = '', $req_method = 'GET', $sig_method = 'HMAC-SHA1', $oauth_version = '1.0', $callback_url = '') {
        $this->consumer_key = $consumer_key;
        $this->consumer_secret = $consumer_secret;
        $this->token = $token;
        $this->token_secret = $token_secret;
        $this->req_method = $req_method;
        $this->sig_method = $sig_method;
        $this->oauth_version = $oauth_version;
        $this->callback_url = $callback_url;
        $this->params = array();
        $this->req_url = '';
        $this->base_str = '';
    }
    
    # sort (very important as it affects the signature), concat, and percent encode
    # ref http://oauth.net/core/1.0/#rfc.section.9.1.1
    # ref http://oauth.net/core/1.0/#9.2.1
    # ref http://oauth.net/core/1.0/#rfc.section.A.5.1
    function query_string() {
        $pairs = array();
        ksort($this->params);
        foreach ($this->params as $key => $val) {
            array_push($pairs, $this->oauth_encode($key) . '=' . $this->oauth_encode((string)$val));
        }
        return implode('&', $pairs);
    }

    // organize params & create signature
    function sign($parsed_url) {
        $this->params = array(
            'oauth_consumer_key' => $this->consumer_key,
            'oauth_nonce' => $this->nonce(),
            'oauth_signature_method' => $this->sig_method,
            'oauth_timestamp' => time(),
            'oauth_version' => $this->oauth_version
        );
        $extra_params = array();
        parse_str($parsed_url['query'], $extra_params);
        $this->params += $extra_params;

        // ref http://oauth.net/core/1.0/#rfc.section.9.1.2
        $this->req_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $parsed_url['path'];

        // ref http://oauth.net/core/1.0/#anchor14
        $this->base_str = implode('&', array(
            $this->req_method,
            $this->oauth_encode($this->req_url),

            // normalization is just x-www-form-urlencoded
            $this->oauth_encode($this->query_string()),
        ));

        // add signature
        $this->params['oauth_signature'] = $this->signature();
        return $this;
    }
    
    /*
     * openssl::random_bytes returns non-word chars, which need to be removed. using alt method to get length
     */
    private function nonce() {
        $return = '';
        for ($i = 0; $i < 5; $i++) {
            $return .= chr(mt_rand(0, 255));
        }
        return $return;
    }
    
    // ref http://oauth.net/core/1.0/#rfc.section.9.2
    private function signature() {
        $key = $this->oauth_encode($this->consumer_secret) . '&' . $this->oauth_encode($this->token_secret);
        $hash = hash_hmac('sha1', $this->base_str, $key, true);
        return base64_encode($hash); 
    }
    
    private function oauth_encode($data) {
        if (is_array($data)) {
            return array_map(array($this, 'oauth_encode'), $data);
        } else if (is_scalar($data)) {
            return str_ireplace(
                array('+', '%7E'),
                array(' ', '~'),
                rawurlencode(stripslashes($data))
            );
        } else {
            return '';
        }
    }


}
?>
