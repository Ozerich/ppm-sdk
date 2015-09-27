<?php

namespace ppm;

class Downloader
{
    private $curl;

    private $cookie;

    public function __construct()
    {
        $this->curl = curl_init();

        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36');

    }

    public function __destruct()
    {
        curl_close($this->curl);
    }

    public function get($url)
    {
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_POST, false);

        if ($this->cookie) {
            curl_setopt($this->curl, CURLOPT_COOKIE, $this->cookie);
        }

        $result = curl_exec($this->curl);

        return $result;
    }


    public function post($url, $params = array(), $load_cookie = false)
    {
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_POST, false);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, http_build_query($params));


        if ($load_cookie) {
            curl_setopt($this->curl, CURLOPT_COOKIE, null);
            curl_setopt($this->curl, CURLOPT_HEADER, 1);
        }

        $result = curl_exec($this->curl);

        if ($load_cookie) {
            if (preg_match_all("/^Set-Cookie:(.+?)[$\n]/mi", $result, $m)) {
                $this->cookie = trim(implode(';', array_map(function ($row) {
                    return trim($row);
                }, $m[1])));
            }
        }

        return $result;
    }
}