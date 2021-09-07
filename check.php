<?php
  //error_reporting(E_ALL);
  header('Content-Type: application/json; charset=utf-8');
  if (!isset($_POST['url']) || (substr($_POST['url'], 0, 7) !== 'http://' && substr($_POST['url'], 0, 8) !== 'https://')) {
    echo '[]';
    exit;
  }

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $_POST['url']);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_HEADER, true);
  $data = curl_exec($curl);
  $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  list($headers, $file) = explode("\r\n\r\n", $data, 2);
  while (strpos($file, '  ') !== false) $file = str_replace('  ', ' ', $file);

  $doc = new DOMDocument();
  $doc->loadHTML($file);

  $result = ['url' => $_POST['url'], 'passed' => 0, 'failed' => 0];

  //Enable text compression

  //Use HTTP/2
  if (strpos($headers, 'HTTP/2') !== false) $result['passed']++;
  else $result['failed']++;

  //Avoids document.write()
  if (strpos($file, 'document.write(') === false) $result['passed']++;
  else $result['failed']++;

  //<html> element does have a [lang] attribute
  $passed = false;
  $nodeList = $doc->getElementsByTagName('html');
  foreach($nodeList as $node) {
    if ($node->attributes->getNamedItem('lang') !== null) $passed = true;
    break;
  }
  if ($passed) $result['passed']++; else $result['failed']++;

  //[aria-hidden="true"] is not present on the document <body>
  $passed = true;
  $nodeList = $doc->getElementsByTagName('body');
  foreach($nodeList as $node) {
    if ($node->attributes->getNamedItem('aria-hidden') !== null && $node->attributes->getNamedItem('aria-hidden')->value === 'true') $passed = false;
    break;
  }
  if ($passed) $result['passed']++; else $result['failed']++;

  //Document has a <title> element
  if ($doc->getElementsByTagName('title')->length > 0) $result['passed']++;
  else $result['failed']++;

  //[user-scalable="no"] is not used in the <meta name="viewport"> element and the [maximum-scale] attribute is not less than 5.

  //<frame> or <iframe> elements have a title
  $passed = true;
  $nodeList = $doc->getElementsByTagName('frame');
  foreach($nodeList as $node) {
    if ($node->attributes->getNamedItem('title') === null) $passed = false;
  }
  $nodeList = $doc->getElementsByTagName('iframe');
  foreach($nodeList as $node) {
    if ($node->attributes->getNamedItem('title') === null) $passed = false;
  }
  if ($passed) $result['passed']++; else $result['failed']++;

  //Image elements have [alt] attributes
  $passed = true;
  $nodeList = $doc->getElementsByTagName('img');
  foreach($nodeList as $node) {
    if ($node->attributes->getNamedItem('alt') === null) $passed = false;
  }
  if ($passed) $result['passed']++; else $result['failed']++;

  //<input type="image"> elements have [alt] text
  $passed = true;
  $nodeList = $doc->getElementsByTagName('input');
  foreach($nodeList as $node) {
    $attributes = $node->attributes;
    if ($attributes->getNamedItem('input') !== null && $attributes->getNamedItem('input')->value === 'image') {
      if ($attributes->getNamedItem('alt') === null) $passed = false;
    }
  }
  if ($passed) $result['passed']++; else $result['failed']++;

  //Form elements have associated labels

  //Lists contain only <li> elements and script supporting elements (<script> and <template>).

  //List items (<li>) are contained within <ul> or <ol> parent elements

  //The document does not use <meta http-equiv="refresh">
  $passed = true;
  $nodeList = $doc->getElementsByTagName('meta');
  foreach($nodeList as $node) {
    $attributes = $node->attributes;
    if ($attributes->getNamedItem('http-equiv') !== null) {
      if ($attributes->getNamedItem('http-equiv')->value === 'refresh') $passed = false;
    }
  }
  if ($passed) $result['passed']++; else $result['failed']++;

  //<object> elements have [alt] text
  $passed = true;
  $nodeList = $doc->getElementsByTagName('object');
  foreach($nodeList as $node) {
    if ($node->attributes->getNamedItem('alt') === null) $passed = false;
  }
  if ($passed) $result['passed']++; else $result['failed']++;

  //<video> elements contain a <track> element with [kind="captions"]

  //Does use HTTPS
  if (strpos($_POST['url'], 'https://') !== false) $result['passed']++;
  else $result['failed']++;

  //Links to cross-origin destinations are safe
  $passed = true;
  $nodeList = $doc->getElementsByTagName('a');
  foreach($nodeList as $node) {
    $attributes = $node->attributes;
    if ($attributes->getNamedItem('target') !== null && $attributes->getNamedItem('target')->value === '_blank') {
      if ($attributes->getNamedItem('rel') !== null) {
        if (strpos($attributes->getNamedItem('rel'), 'noopener') === false && strpos($attributes->getNamedItem('rel'), 'noreferrer') === false) $passed = false;
      } else {
        $passed = false;
      }
    }
  }
  if ($passed) $result['passed']++; else $result['failed']++;

  //Page has the HTML doctype
  if (strpos($file, '<!DOCTYPE html>') !== false) $result['passed']++;
  else $result['failed']++;

  //Properly defines charset
  $passed = false;
  if (strpos($header, 'charset=') !== false) {
    $passed = true;
  } else {
    $nodeList = $doc->getElementsByTagName('meta');
    foreach($nodeList as $node) {
      if ($node->attributes->getNamedItem('charset') !== null) {
        $passed = true;
        break;
      }
    }
  }
  if ($passed) $result['passed']++; else $result['failed']++;

  //Avoids Application Cache
  $passed = false;
  $nodeList = $doc->getElementsByTagName('html');
  foreach($nodeList as $node) {
    if ($node->attributes->getNamedItem('manifest') !== null) {
      if (strpos($attributes->getNamedItem('manifest')->value, '.appcache') === false) $passed = true;
    } else {
      $passed = true;
    }
    break;
  }
  if ($passed) $result['passed']++; else $result['failed']++;

  //Document does have a meta description
  $passed = false;
  $nodeList = $doc->getElementsByTagName('meta');
  foreach($nodeList as $node) {
    if ($node->attributes->getNamedItem('name') !== null && $node->attributes->getNamedItem('name')->value === 'description') {
      $passed = true;
      break;
    }
  }
  if ($passed) $result['passed']++; else $result['failed']++;

  //Has a <meta name="viewport"> tag with width or initial-scale
  $passed = false;
  $nodeList = $doc->getElementsByTagName('meta');
  foreach($nodeList as $node) {
    if ($node->attributes->getNamedItem('name') !== null && $node->attributes->getNamedItem('name')->value === 'viewport') {
      if ($node->attributes->getNamedItem('content') !== null && (strpos($node->attributes->getNamedItem('content')->value, 'width') !== false || strpos($node->attributes->getNamedItem('content')->value, 'initial-scale') !== false)) {
        $passed = true;
        break;
      }
    }
  }
  if ($passed) $result['passed']++; else $result['failed']++;

  //Page has successful HTTP status code
  if ($http_status > 199 && $http_status < 300) $result['passed']++;
  else $result['failed']++;

  //Document avoids plugins
  if (strpos($file, '.swf') === false && strpos($file, '.flv') === false && strpos($file, '.class') === false && strpos($file, '.xap') === false) $result['passed']++;
  else $result['failed']++;

  echo json_encode($result);
?>
