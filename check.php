<?php
  //error_reporting(E_ALL);
  header('Content-Type: application/json; charset=utf-8');
  if (!isset($_POST['url'])) {
    echo '{}';
    exit;
  }
  if (substr($_POST['url'], 0, 7) !== 'http://' && substr($_POST['url'], 0, 8) !== 'https://') $_POST['url'] = 'http://'.$_POST['url'];

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $_POST['url']);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($curl, CURLOPT_HEADER, true);
  $data = curl_exec($curl);
  $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  list($headers, $file) = explode("\r\n\r\n", $data, 2);
  while (substr($file, 0, 4) === 'HTTP') list($headers, $file) = explode("\r\n\r\n", $file, 2);
  while (strpos($file, '  ') !== false) $file = str_replace('  ', ' ', $file);

  $doc = new DOMDocument();
  $doc->loadHTML($file);

  $result = ['url' => $_POST['url'], 'passed' => [], 'failed' =>[]];

  function addToResult($bool, $string) {
    global $result;
    if ($bool) array_push($result['passed'], $string);
    else array_push($result['failed'], $string);
  }

  //Enable text compression

  //Use HTTP/2
  addToResult(
    strpos($headers, 'HTTP/2') !== false,
    'Use HTTP/2'
  );

  //Avoids document.write()
  addToResult(
    strpos($file, 'document.write(') === false,
    'Avoids document.write()'
  );

  //<html> element does have a [lang] attribute
  $passed = false;
  $nodeList = $doc->getElementsByTagName('html');
  foreach($nodeList as $node) {
    if ($node->attributes->getNamedItem('lang') !== null) $passed = true;
    break;
  }
  addToResult(
    $passed,
    '<html> element does have a [lang] attribute'
  );

  //[aria-hidden="true"] is not present on the document <body>
  $passed = true;
  $nodeList = $doc->getElementsByTagName('body');
  foreach($nodeList as $node) {
    if ($node->attributes->getNamedItem('aria-hidden') !== null && $node->attributes->getNamedItem('aria-hidden')->value === 'true') $passed = false;
    break;
  }
  addToResult(
    $passed,
    '[aria-hidden="true"] is not present on the document <body>'
  );

  //Document has a <title> element
  addToResult(
    $doc->getElementsByTagName('title')->length > 0,
    'Document has a <title> element'
  );

  //[user-scalable="no"] is not used in the <meta name="viewport"> element
  $passed = false;
  $nodeList = $doc->getElementsByTagName('meta');
  foreach($nodeList as $node) {
    if ($node->attributes->getNamedItem('name') !== null && $node->attributes->getNamedItem('name')->value === 'viewport') {
      if ($node->attributes->getNamedItem('content') !== null && strpos($node->attributes->getNamedItem('content')->value, 'user-scalable="no"') === false) {
        $passed = true;
        break;
      }
    }
  }
  addToResult(
    $passed,
    '[user-scalable="no"] is not used in the <meta name="viewport"> element'
  );

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
  addToResult(
    $passed,
    '<frame> or <iframe> elements have a title'
  );

  //Image elements have [alt] attributes
  $passed = true;
  $nodeList = $doc->getElementsByTagName('img');
  foreach($nodeList as $node) {
    if ($node->attributes->getNamedItem('alt') === null) $passed = false;
  }
  addToResult(
    $passed,
    'Image elements have [alt] attributes'
  );

  //<input type="image"> elements have [alt] text
  $passed = true;
  $nodeList = $doc->getElementsByTagName('input');
  foreach($nodeList as $node) {
    $attributes = $node->attributes;
    if ($attributes->getNamedItem('input') !== null && $attributes->getNamedItem('input')->value === 'image') {
      if ($attributes->getNamedItem('alt') === null) $passed = false;
    }
  }
  addToResult(
    $passed,
    '<input type="image"> elements have [alt] text'
  );

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
  addToResult(
    $passed,
    'The document does not use <meta http-equiv="refresh">'
  );

  //<object> elements have [alt] text
  $passed = true;
  $nodeList = $doc->getElementsByTagName('object');
  foreach($nodeList as $node) {
    if ($node->attributes->getNamedItem('alt') === null) $passed = false;
  }
  addToResult(
    $passed,
    '<object> elements have [alt] text'
  );

  //<video> elements contain a <track> element with [kind="captions"]

  //Does use HTTPS
  addToResult(
    strpos($_POST['url'], 'https://') !== false,
    'Does use HTTPS'
  );

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
  addToResult(
    $passed,
    'Links to cross-origin destinations are safe'
  );

  //Page has the HTML doctype
  addToResult(
    strpos($file, '<!DOCTYPE html>') !== false,
    'Page has the HTML doctype'
  );

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
  addToResult(
    $passed,
    'Properly defines charset'
  );

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
  addToResult(
    $passed,
    'Avoids Application Cache'
  );

  //Document has a meta description
  $passed = false;
  $nodeList = $doc->getElementsByTagName('meta');
  foreach($nodeList as $node) {
    if ($node->attributes->getNamedItem('name') !== null && $node->attributes->getNamedItem('name')->value === 'description') {
      $passed = true;
      break;
    }
  }
  addToResult(
    $passed,
    'Document has a meta description'
  );

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
  addToResult(
    $passed,
    'Has a <meta name="viewport"> tag with width or initial-scale'
  );

  //Page has successful HTTP status code
  addToResult(
    $http_status > 199 && $http_status < 300,
    'Page has successful HTTP status code'
  );

  //Document avoids plugins
  addToResult(
    strpos($file, '.swf') === false && strpos($file, '.flv') === false && strpos($file, '.class') === false && strpos($file, '.xap') === false,
    'Document avoids plugins'
  );

  echo json_encode($result);
?>
