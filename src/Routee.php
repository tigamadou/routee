<?php

namespace Tigamadou\Routee;

class Routee
{
    public function __construct($ApiKey,$ApiSecret){
        $this->key = $ApiKey;
        $this->secret = $ApiSecret;
        $this->b64auth = base64_encode("$this->key:$this->secret");
        $this->response = new \stdClass();
        $this->request = new \stdClass();
        $this->request->postfields=[];
    }



    public function token(){
        $this->request->url = "https://auth.routee.net/oauth/token";
        $this->request->method = "POST";
        $this->request->postfields["grant_type"]= "client_credentials";
        $this->request->headers[]="authorization: Basic $this->b64auth";
        $this->request->headers[]="content-type: application/x-www-form-urlencoded";
        $this->request->isJson=false;
        $this->send_request();
        if($this->response->code==200){
            $this->access_token = $this->response->data->access_token;
        }
    }

    public function send_sms($body=null,$to=null,$from=null){
        $this->request->url = "https://connect.routee.net/sms";
        $this->request->postfields=[];
        $this->request->headers=[];
        $this->request->postfields["body"]= $body;
        $this->request->postfields["to"]= $to;
        $this->request->postfields["from"]= $from;
        $this->request->isJson=true;
        $this->request->headers[]="authorization: Bearer $this->access_token";
        $this->request->headers[]="content-type: application/json";
        $this->send_request();
    }

    public function send_request(){
        
        $postfields = $this->parse_postfields();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,$this->request->url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($curl, CURLOPT_ENCODING,"");
        curl_setopt($curl, CURLOPT_MAXREDIRS,10);
        curl_setopt($curl, CURLOPT_TIMEOUT,30);
        curl_setopt($curl, CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST,$this->request->method);
        curl_setopt($curl, CURLOPT_HTTPHEADER,$this->request->headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS,$postfields);        
        $this->response->data = json_decode(curl_exec($curl));
        $this->response->code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        if ($err) {
            $this->response->data= $err;
        } 
        curl_close($curl);
        
        $this->response->request = $this->request;
        
        $this->response->postfields=$postfields;
    }

 
    public function parse_postfields(){
        $fields = http_build_query($this->request->postfields);
        if($this->request->isJson==true){
     
            $fields = json_encode($this->request->postfields);
        }  
        return $fields;
    }

    public function error($code){

    }
}