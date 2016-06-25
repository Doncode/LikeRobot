<?php

namespace App;

/**
 * Class Botan
 * @package YourProject
 *
 * Usage:
 *
 * private $token = 'token';
 *
 * public function _incomingMessage($message_json) {
 *     $messageObj = json_decode($message_json, true);
 *     $messageData = $messageObj['message'];
 *
 *     $botan = new YourProject\Botan($this->token);
 *     $botan->track($messageData, 'Start');
 * }
 *
 */

class Botan {

    /**
     * @var string Tracker url
     */
    protected $template_uri = 'https://api.botan.io/track?token=#TOKEN&uid=#UID&name=#NAME';

    /**
     * @var string Url shortener url
     */
    protected $shortener_uri = 'https://api.botan.io/s/?token=#TOKEN&user_ids=#UID&url=#URL';

    /**
     * @var string Yandex AppMetrica application api_key
     */
    protected $token;

    protected $async = [];

    function __construct($token) {
        if (empty($token) || !is_string($token)) {
            throw new \Exception('Token should be a string', 2);
        }
        $this->token = $token;
    }

    public function track($message, $event_name = 'Message') {
        $uid = $message['from']['id'];
        $url = str_replace(
            ['#TOKEN', '#UID', '#NAME'],
            [$this->token, $uid, urlencode($event_name)],
            $this->template_uri
        );
//        $this->request($url, $message);
        $this->requestAsync($url, $message);
//        if ($result['status'] !== 'accepted') {
//            throw new \Exception('Error Processing Request', 1);
//        }
    }

    public function shortenUrl($url, $user_id) {
        $request_url = str_replace(
            ['#TOKEN', '#UID', '#URL'],
            [$this->token, $user_id, urlencode($url)],
            $this->shortener_uri
        );
        $response = file_get_contents($request_url);
        return $response === false ? $url : $response;
    }

    function getHTTPResponseCode($headers){
        $matches = [];
        $res = preg_match_all('/[\w]+\/\d+\.\d+ (\d+) [\w]+/', $headers[0], $matches);
        if ($res < 1)
            throw new \Exception('Incorrect response headers');
        $code = intval($matches[1][0]);
        return $code;
    }

    protected function request($url, $body) {
        $options = [
            'http' => [
                'header'  => 'Content-Type: application/json',
                'method'  => 'POST',
                'content' => json_encode($body)
            ]
        ];
        $responseData = '';
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        if ($response === false)
            throw new \Exception('Error Processing Request', 1);

        $HTTPCode = $this->getHTTPResponseCode($http_response_header);
        if ($HTTPCode !== 200)
            throw new \Exception("Bad HTTP responce code: $HTTPCode".print_r($http_response_header, true));

        $responseData = json_decode($response, true);
        if ($responseData === false)
            throw new \Exception('JSON decode error');

        return $responseData;
    }

    protected function requestAsync($url, $body) {
        $this->async[] = [$url, $body];
    }

    public function __destruct()
    {
        if (count($this->async)) {
            //создаем набор дескрипторов cURL
            $chs = [];
            $mh = curl_multi_init();
            foreach ($this->async as $req) {
                $this->request($req[0], $req[1]);
//                $chs[] = $ch = curl_init();
//                curl_setopt($ch, CURLOPT_URL, $req[0]);
//                curl_setopt($ch, CURLOPT_HEADER, 0);
//                curl_setopt($ch, CURLOPT_POST, 1);
//                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($req[1]));
//                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
//
//                //добавляем два дескриптора
//                curl_multi_add_handle($mh, $ch);
            }

            return;

            $active = null;
            //запускаем дескрипторы
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);

            while ($active && $mrc == CURLM_OK) {
                usleep(10000);
//                if (curl_multi_select($mh) != -1) {
                    do {
                        $mrc = curl_multi_exec($mh, $active);
//                        echo curl_multi_getcontent($ch1);
                        //echo curl_multi_getcontent($ch2);
                    } while ($mrc == CURLM_CALL_MULTI_PERFORM);
//                }
            }

            //закрываем все дескрипторы
            foreach ($chs as $ch) {
                curl_multi_remove_handle($mh, $ch);
            }
            curl_multi_close($mh);
        }
    }

}