<?php

namespace LouisSicard\GoogleQuery\Classes;


class GoogleQuery
{

  private $googleDomain;
  private $query;
  private $nbPages;

  public function __construct($query, $nbPages = 1, $googleDomain = "www.google.com")
  {
    $this->query = $query;
    $this->nbPages = $nbPages;
    $this->googleDomain = $googleDomain;
  }

  public function execute($records = [], $page = 1) {
    $records = array_merge($records, $this->crawl($page));
    if($page < $this->nbPages) {
      usleep(500 * 1000);
      return $this->execute($records, $page + 1);
    }
    else {
      return $records;
    }
  }

  private function crawl($page) {
    error_reporting(E_ERROR);
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => 'http://' . $this->googleDomain . '/search?q=' . urlencode($this->query) . '&start=' . ($page - 1) * 10,
      CURLOPT_USERAGENT => 'ADSbot 1.0',
      CURLOPT_FOLLOWLOCATION => TRUE
    ));
    $html = curl_exec($curl);
    curl_close($curl);
    $dom = new \DOMDocument();
    $dom->loadHTML($html);
    $elem = $dom->getElementById('ires');
    $xml = simplexml_import_dom($elem);
    $count = 0;
    $records = [];
    foreach ($xml->xpath('//h3[@class="r"]/a') as $a) {
      $count++;
      $title = trim(html_entity_decode(strip_tags($a->asXml())));
      $url_raw = trim(html_entity_decode(strip_tags((string) $a['href'])));
      $url_r = explode('?', $url_raw);
      if (count($url_r) > 1) {
        $url_rr = explode('&', $url_r[1]);
        $params = array();
        foreach ($url_rr as $p) {
          $p_r = explode('=', $p);
          if (count($p_r) > 1) {
            $params[$p_r[0]] = $p_r[1];
          }
        }
      }
      if (isset($params['q'])) {
        $records[] = array(
          'page' => $page,
          'rank' => $count,
          'title' => $title,
          'url' => $params['q'],
        );
      }
    }
    return $records;
  }

}