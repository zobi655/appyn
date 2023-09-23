<?php

if( ! defined( 'ABSPATH' ) ) die ( '✋' );

class ShortLink {

    var $apikey;
    var $url;
    
    public function __construct( $url ) {
        $this->url = $url;
    }

    public function Ouo() {
        
        $this->apikey = appyn_options( 'shortlink_ouo' );

        try {
            $api_url = "http://ouo.io/api/{$this->apikey}?s=".urlencode($this->url);
            $result = file_get_contents($api_url);
    
            return $result;

        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    public function shrinkEarn() {
        
        $this->apikey = appyn_options( 'shortlink_shrinkearn' );

        try {
            $api_url = "https://shrinkearn.com/api?api={$this->apikey}&url=".urlencode($this->url);
            $result = json_decode(file_get_contents($api_url),TRUE);
    
            if( $result["status"] === 'error' ) {
                return array('error' => $result["message"]);
            } else {
                return $result["shortenedUrl"];
            }
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    public function Shorte() {

        $this->apikey = appyn_options( 'shortlink_shorte' );

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.shorte.st/v1/data/url');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'public-api-token: '.$this->apikey,
                'content-type: application/x-www-form-urlencoded',
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'urlToShorten='.urlencode($this->url));

            $response = curl_exec($ch);
            $data = json_decode($response,TRUE);

            return $data['shortenedUrl'];

        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }

    public function ClicksFly() {
        
        $this->apikey = appyn_options( 'shortlink_clicksfly' );

        try {
            $api_url = "https://clicksfly.com/api?api={$this->apikey}&url=".urlencode($this->url);
            $result = @json_decode(file_get_contents($api_url),TRUE);
    
            if( $result["status"] === 'error' ) {
                return array('error' => $result["message"]);
            } else {
                return $result["shortenedUrl"];
            }

        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }  

    public function Oke() {
        
        $this->apikey = appyn_options( 'shortlink_oke' );

        try {
            $api_url = "https://oke.io/api?api={$this->apikey}&url=".urlencode($this->url);
            $result = @json_decode(file_get_contents($api_url),TRUE);
    
            if( $result["status"] === 'error' ) {
                return array('error' => $result["message"]);
            } else {
                return $result["shortenedUrl"];
            }

        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }      
}