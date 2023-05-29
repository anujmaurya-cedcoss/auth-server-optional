<?php

use Phalcon\Mvc\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        // redirected to view
    }

    public function loginAction() {
        $ch = curl_init();
        $url = "http://172.31.0.4/api/login";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $output = curl_exec($ch);
        curl_close($ch);
        echo "<h3>$output</h3>";
        echo "<a href = '/index/verify?token=$output' class = 'btn btn-secondary'>Authorize Code</a>";
        die;
    }

    public function verifyAction() {
        $token = $_GET['token'];
        $ch = curl_init();
        $url = "http://172.31.0.4/api/authorise";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        echo "<h3>$output</h3>";
        die;
    }
}
