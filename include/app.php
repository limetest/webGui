<?PHP
/* Copyright 2010, Lime Technology LLC.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?php
require_once "plugins/vendor/markdown_extra/markdown.php";

// MORE: this goes away once we update PHP
if (!function_exists('parse_ini_string')){
    function parse_ini_string($str, $ProcessSections=false){
        $lines  = explode("\n", $str);
        $return = Array();
        $inSect = false;
        foreach($lines as $line){
            $line = trim($line);
            if(!$line || $line[0] == "#" || $line[0] == ";")
                continue;
            if($line[0] == "[" && $endIdx = strpos($line, "]")){
                $inSect = substr($line, 1, $endIdx-1);
                continue;
            }
            if(!strpos($line, '=')) // (We don't use "=== false" because value 0 is not valid as well)
                continue;
           
            $tmp = explode("=", $line, 2);
            $key = rtrim($tmp[0]);
	    $val = ltrim($tmp[1]);
	    $len = strlen($val);
	    if ($len >= 2) {
              if ((($val[0] == '"') && ($val[$len-1] == '"')) ||
	          (($val[0] == "'") && ($val[$len-1] == "'")))
	        $val = substr($val, 1, $len-2);
	    }
            if($ProcessSections && $inSect)
                $return[$inSect][$key] = $val;
            else
                $return[$key] = $val;
        }
        return $return;
    }
}

function build_pages( $pattern) {
  global $page_array;
  foreach (glob( "$pattern", GLOB_NOSORT) as $entry) {
    list($header, $content) = explode('---', file_get_contents($entry), 2);
    $page = parse_ini_string($header);
    $page['text'] = $content;
  
    $page['name'] = basename( $entry, ".page");
    $page['root'] = dirname( $entry);
  
    // add to page_array
    $page_array[$page['name']] = $page;
  }
}

// Return sorted array of pages on the indicated menu.
function find_pages( $menu) {
  global $page_array;
  $pages = array();
  foreach ($page_array as $page) {
    $tok = strtok( $page['Menu'], " ");
    while ($tok !== false) {
      $delim = strpos( $tok, ":");
      if ($delim) {
	$t = substr( $tok, 0, $delim);
	if ($t == $menu) {
          $key = substr( $tok, $delim+1) . $page['name'];
          $pages[$key] = $page;
	  break;
	}
      }
      else {
	if ($tok == $menu) {
	  $pages[$page['name']] = $page;
	  break;
	}
      }
      $tok = strtok( " ");
    }
  }
  ksort( $pages);
  return $pages;
}

function icon_file($page)
{
  if ($page['Icon'])
    $file = "{$page['root']}/{$page['Icon']}";
  else
    $file = "{$page['root']}/icons/{$page['name']}";
  if (!is_file($file)) $file = "plugins/webGui/icons/default.png";
  return $file;
}

// hack to embed function output in a quoted string (e.g., in a page Title)
// see: http://stackoverflow.com/questions/6219972/why-embedding-functions-inside-of-strings-is-different-than-variables
function _func($x) { return $x; }
$func = '_func';
?>
