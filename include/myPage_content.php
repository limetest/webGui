<?PHP
/* Copyright Lime Technology LLC.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
// render $myPage

// Using Markdown the way we do works because Markdown will pass through any existing HTML block
// level entities in the output, and PHP blocks are passed unmodified.
// This is pretty cool; it lets us intermix the documentation with the HTML/PHP code that
// implements the page (something like Knuth's "Literate Programming" for HTML/PHP).

  if ($myPage['Type'] == "xmenu") {
    $text = Markdown($myPage['text']);
    eval("?>$text");
    $pages = find_pages($myPage['name']);
  }
  else {
    $pages = array();
    $pages[$myPage['name']] = $myPage;
  }
  foreach($pages as $page) {
    // handle conditional inclusion
    if (!empty($page['Cond'])) {
      eval( "\$enabled={$page['Cond']};");
      if (!$enabled) continue;
    }
    if (!empty($page['Title'])) {
      // handle variable substitution in Title string
      eval( "\$title=\"{$page['Title']}\";");
      $src = icon_file($page);
      echo "<div id='title'><span class='left'><img src='/$src' class='icon' width='16' height='16'>$title</span></div>";
    }
    if ($page['Type'] == "menu") {
      echo "<div class='PanelSet'>";
      $pgs = find_pages($page['name']);
      foreach($pgs as $pg) {
        // handle conditional inclusion
	if (!empty($pg['Cond'])) {
	  eval( "\$enabled={$pg['Cond']};");
	  if (!$enabled) continue;
	}
        // handle variable substitution in Title string
        eval( "\$title=\"{$pg['Title']}\";");
	$href="{$path}/{$pg['name']}";
	$src = icon_file($pg);
	echo "<div class='Panel'>";
	echo "<a href='$href'><img class='PanelImg' src='/$src' alt='$title'></a>";
	echo "<div class='PanelText'><a class='PanelText' href='$href'>$title</a></div>";
	echo "</div>";
      }	
      echo "</div>";
    }	
    else if (empty($page['Type']) || ($page['Type'] == "php")) {
      // only markdown the content included in the page file
      $text = Markdown($page['text']);
      // the @ means no error message if file doesn't exist
      $file = @file_get_contents("{$page['root']}/{$page['name']}.php");
      // this is what an 'include_string()' function would do if it existed
      eval("?>$text$file");
    }
    else if (!empty($page['Type'])) {
      echo Markdown($page['text']);
      passthru($page['Type']);
    }
  }	
