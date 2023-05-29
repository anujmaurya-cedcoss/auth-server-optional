<?php

use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Collection\Manager;
use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Token\Parser;

define("BASE_PATH", (__DIR__));
require_once(BASE_PATH . '/vendor/autoload.php');


// Use Loader() to autoload our model
$container = new FactoryDefault();
$container->set(
    'mongo',
    function () {
        $mongo = new MongoDB\Client(
            'mongodb+srv://root:VajsFVXK36vxh4M6@cluster0.nwpyx9q.mongodb.net/?retryWrites=true&w=majority'
        );
        return $mongo->auth_server;
    },
    true
);
$container->set(
    'collectionManager',
    function () {
        return new Manager();
    }
);
$app = new Micro($container);
// Define the routes here

$app->post(
    '/api/login',
    function () use ($app) {
        $data = $_POST;
        $response = $this->mongo->users->findOne(["mail" => $data['mail'], "password" => $data['password']]);
        $signer  = new Hmac();
        if ($response->mail == $data['mail'] && $response->mail != '') {
            // Builder object
            $builder = new Builder($signer);
            $now        = new DateTimeImmutable();
            $issued     = $now->getTimestamp();
            $notBefore  = $now->modify('-1 minute')->getTimestamp();
            $expires    = $now->modify('+1 day')->getTimestamp();
            $passphrase = 'QcMpZ&b&mo3TPsPk668J6QH8JA$&U&m2';

            $builder
                ->setAudience('https://target.phalcon.io')  // aud
                ->setContentType('application/json')        // cty - header
                ->setExpirationTime($expires)               // exp
                ->setId('abcd123456789')                    // JTI id
                ->setIssuedAt($issued)                      // iat
                ->setIssuer('https://phalcon.io')           // is
                ->setNotBefore($notBefore)                  // nbf
                ->setSubject(json_encode($data))   // sub
                ->setPassphrase($passphrase)                // password
            ;
            $tokenObject = $builder->getToken();
            return $tokenObject->getToken();
        } else {
            return "Invalid Credentials!";
        }
    }
);

$app->post(
    '/api/authorise',
    function () use ($app) {
        $tokenReceived = $this->request->getJsonRawBody();
        $parser      = new Parser();
        $tokenObject = $parser->parse($tokenReceived);

        $arr = $tokenObject->getClaims()->getPayload()['sub'];
        $arr = json_decode($arr, true);

        $response = $this->mongo->users->findOne(["mail" => $arr['mail'], "password" => $arr['password']]);

        if ($response['mail'] != '') {
            return "Authorized!";
        } else {
            return "Unauthorized";
        }
    }
);
$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    echo 'This is crazy, but this page was not found!';
});

$app->handle($_SERVER['REQUEST_URI']);
