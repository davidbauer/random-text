<?php
require 'vendor/autoload.php';
use RedBean_Facade as R;
// R::setup('mysql:host=localhost;dbname=banggomat', 'root','root'); //mysql
R::setup('mysql:host=localhost;dbname=tageswoc_banggomat', 'tageswoc_banggom','QUDooee-'); //mysql

$app = new \Slim\Slim(array(
    'mode' => 'development'
));

$tambur = new TamburClient("a1892d4076a7421aa9e1ac6b2fb5dd68", "banggomat-14", "DQIsFQAk");
$csv_link = "https://docs.google.com/spreadsheet/pub?key=0AhM9lBUdMo93dEpzanh0anc5VlU5REVFZlZBMnJHYnc&single=true&gid=0&output=csv";

$app->get('/bangg_reset', function() {
    R::wipe("bangg");
    R::wipe("csvdata");
});

$app->get('/bangg', function() use ($app) {
    $all = R::findAll('bangg',
        ' ORDER BY id DESC LIMIT 14 ');
    $banggs = array();
    foreach ($all as $bangg) {
        array_push($banggs, array(
            'id' => $bangg->banggid,
            'person' => $bangg->person,
            'text' => $bangg->text
        ));
    }
    echo jsonp_wrapper($app, json_encode($banggs));
    
});
$app->get('/bangg/:banggid', function ($banggid) use ($app) {
    $banggs = R::find('bangg', ' banggid = ? ',
        array( $banggid)
    );
    if (sizeof($banggs) == 0) {
        $app->halt(404);
    }
    $bangg = end($banggs);
    $obj = array(
        'id' => $bangg->banggid,
        'person' => $bangg->person,
        'text' => $bangg->text
    );
    echo jsonp_wrapper($app, json_encode($obj));
});

$app->post('/bangg', function() use ($tambur, $app, $csv_link) {
    $req = $app->request();
    $body = $req->getBody();
    $obj = json_decode($body);
    $person = $obj->{'person'};
    if ($person == "") {
        $app->halt(400);
    }
    $bangg = R::dispense('bangg');
    $text = poetize($csv_link, $person);
    $bangg->person = $person;
    $bangg->text = $text;
    $bangg->banggid = generateRandomString();
    $banggid = R::store($bangg);
    if ($banggid ==  0) {
        $app->halt(500);
    }
    $res = $app->response();
    $obj = array(
        'id' => $bangg->banggid,
        'person' => $bangg->person,
        'text' => $bangg->text
    );
    $res['Content-Type'] = 'application/json';
    $result = json_encode($obj);
    // tambur publish is not yet working, bug in tambur lib
    $tambur->publish('banggomat', $result);
    $res->write($result);
});

$app->run();

function generateRandomString($length = 6) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function jsonp_wrapper($app, $response) {
    $req = $app->request();
    $callback = $req->get('callback');
    $res = $app->response();
    $res['Content-Type'] = 'application/json';
    if ($callback) {
        return $callback . '(' . $response .');';
    }
    return $response;
}

function poetize($csv_link, $person) {
    $csvs = R::findAll('csvdata', ' LIMIT 1 ');
    $csv = null;
    if (sizeof($csvs) == 0) {
        $csv = R::dispense('csvdata');
    } else {
        $csv = end($csvs);
    }
    $timediff = strtotime(R::$f->now()) - strtotime($csv->last_update);
    if (!$csv->last_update or $timediff >= 60) {
        // refetch csv from google spreadsheet
        $response = \Httpful\Request::get($csv_link)
            ->withoutAutoParsing()
            ->send();
        if (!$response->hasErrors()) {
            $csv->body = $response->body;
            $csv->last_update = R::$f->now();
            R::store($csv);
        }
    }
    $gaps = array();
    $lines = explode("\n", $csv->body);
    array_shift($lines);
    foreach($lines as $line) {
        $line = explode(",", $line);
        $gap = $line[0];
        if (!array_key_exists($gap, $gaps)) {
            $gaps[$gap] = array();
        }
        array_push($gaps[$gap], $line[1]);
    }

    $gap1k = array_rand($gaps["1"], 1);
    $gap2k = array_rand($gaps["2"], 1);
    $gap3k = array_rand($gaps["3"], 1);
    $gap4k = array_rand($gaps["4"], 1);
    $gap5k = array_rand($gaps["5"], 1);
    $gap1 = $gaps["1"][$gap1k];
    $gap2 = $gaps["2"][$gap2k];
    $gap3 = $gaps["3"][$gap3k];
    $gap4 = $gaps["4"][$gap4k];
    $gap5 = $gaps["5"][$gap5k];
    
    $ret = 'ey, ' . $person . ', mach mal vorw&auml;rts mit dem text!! <br /> ' . $gap1 . ' du ' . $gap2 . ' alter ' . $gap3 . ' mach die ' . $gap4 . ' ' . $gap5;
    return $ret;
}

?>
