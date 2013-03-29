<?php

  class CopperGolem {

    private $formNE = array('input', 'textarea', 'button', 'select', 'label');
    private $lurl = '';

    function CopperGolem (){
      $this->ch = curl_init();
      $ch = $this->ch;
      curl_setopt($ch, CURLOPT_HEADER, 0); // Include headers in response or not
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // Follow redirects
      curl_setopt($ch, CURLOPT_COOKIEJAR, "cookies.txt"); // We keep the cookies here
      curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");// We keep the cookies here! 
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Return (don't print) answer of exec
      curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.12) Gecko/2009070611 Firefox/3.0.12");
      curl_setopt($ch, CURLOPT_AUTOREFERER, true); // Isn't this great?
      curl_setopt($ch, CURLOPT_ENCODING, ""); // We want everything, then... we'll have fun
      //curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: */*', 'Accept-Language: en-us,en;q=0.5', 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7'));      
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: */*', 'Accept-Language: en-us,en;q=0.5', 'Accept-Charset: utf-8;q=0.7,*;q=0.7'));      
    }
    
    private function act($url, $params = false){
      $ch = $this->ch;
      curl_setopt($ch, CURLOPT_URL, $url);
      if ($params != false){
        echo($this->myurlencode($params));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->myurlencode($params));
        curl_setopt($ch, CURLOPT_POST, 1);
      }
      else
        curl_setopt($ch, CURLOPT_POST, 0);
      $r = curl_exec($ch);
      $this->lurl = $url;
      return $r;
    }
    
    private function myurlencode($dict){
      $r = mb_convert_encoding('', 'utf-8');
      foreach($dict as $key => $value)
        if (is_array($value))
          foreach($value as $v)
            $r = $r.urlencode($key).'='.urlencode($v).'&';
        else
          $r = $r.urlencode($key).'='.urlencode($value).'&';
      return $r;
    }
    
    function get ($url){
      return $this->act($url);
    }
    
    function post($url, $params){
      return $this->act($url, $params);
    }

    function form($p, $params){
      $dom = new DOMDocument();
      //$dom->loadHTML(mb_convert_encoding($p, 'utf-8'));
      $dom->loadHTML($p);
      $forms = $dom->getElementsByTagName('form');
      $form = $this->findMatch($forms, $params);
      if (!$form)
        return '';
      $params = array_merge($this->getDefaultParams($form), $params);
      $newurl = $this->getNewURL($form->getAttribute('action'));
      if ($form->getAttribute('method') == 'get')
        return $this->act($newurl.'?'.$this->myurlencode($params));
      return $this->act($newurl, $params);
    }
    
    private function getNewURL($dest){
      if (parse_url($dest, PHP_URL_SCHEME) != '') // absolute already
        return $dest;
      $orig = $this->lurl;
      if ($dest[0] != '/')
        $splitpos = strpos($orig, '/', 10)+1;
      else
        $splitpos = strrpos($orig, '/');
      return substr($orig, 0, $splitpos).$dest;
    }
    
    // I am tempted to use functional programming here, but didn't find a nice way in php
    private function findMatch($forms, $params){
      foreach($forms as $form){
        $matches = true;
        $fields = $this->getFieldNames($form);
        foreach($params as $p => $v){
          $isHere = false;
          foreach($fields as $field){
            if ($field == $p){
              $isHere = true;
              break;
            }
          }
          $matches &= $isHere;
        }
        if ($matches)
          return $form; 
      }
      $this->errorNote("form not found\n");
      return false; 
    }
    
    private function errorNote($str){
      echo($str); //TODO: nice error
    }

    private function getFieldNames($form){
      $r = array();
      foreach ($this->formNE as $ne)
        foreach($form->getElementsByTagName($ne) as $e)
          $r[] = $e->getAttribute('name');
      return $r;
    }

    private function getDefaultParams($form){
      $r = array();
      foreach ($this->formNE as $ne)
        foreach($form->getElementsByTagName($ne) as $e)
          if ($e->getAttribute('value'))
            $r[$e->getAttribute('name')] = $e->getAttribute('value');
      return $r;
    }

    function __destruct() {
      curl_close($this->ch);
    }

  }  
?>
