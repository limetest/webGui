<?PHP
// These functions return their output (i.e., they don't "echo")
function my_scale($value, &$units, $precision = NULL) {
  global $display;
  $scale = $display['scale'];
  $number = isset($display['number']) ? $display['number'] : '.,';
  $dot = substr($number,0,1);
  $comma = substr($number,1,1);
  $unit = array('B','KB','MB','GB','TB','PB');
  if ($scale==0 && !$precision) {
    $units = '';
    return number_format($value, 0, $dot, ($value>=10000 ? $comma : ''));
  } else {
    $base = $value ? floor(log($value, 1000)) : 0;
    if ($scale>0 && $base>$scale) $base = $scale;
    $units = $unit[$base];
    $value = round($value/pow(1000, $base), $precision ? $precision : 2);
    return number_format($value, $precision ? $precision : (($value-intval($value)==0 || $value>=100) ? 0 : ($value>=10 ? 1 : 2)), $dot, ($value>=10000 ? $comma : ''));
  }
}
function my_number($value) {
  global $display;
  $number = isset($display['number']) ? $display['number'] : '.,';
  $dot = substr($number,0,1);
  $comma = substr($number,1,1);
  return number_format($value, 0, $dot, ($value>=10000 ? $comma : ''));
}
function my_time($time, $fmt = NULL) {
  global $display;
  if (!$fmt) $fmt = $display['date'].($display['date']!='%c' ? ", {$display['time']}" : " %Z");
  return $time ? strftime($fmt, $time) : "unset";
}
function my_error($code) {
  switch ($code) {
  case -4:
    return "<i>user abort</i>";
  default:
    return "<b>$code</b>";
  }
}
function my_temp($value) {
  global $display;
  $unit = $display['unit'];
  $dot = isset($display['number']) ? substr($display['number'],0,1) : '.';
  return is_numeric($value) ? (($unit=='C' ? str_replace('.', $dot, $value) : round(9/5*$value+32))." &deg;$unit") : $value;
}
function mk_option($select, $value, $text, $extra = "") {
  return "<option value='$value'".($value==$select ? " selected" : "").(strlen($extra) ? " $extra" : "").">$text</option>";
}
function mk_option_check($name, $value, $text = "") {
  if ($text) {
    $checked = strpos("$name,", "$value,")===false ? "" : " selected";
    return "<option value='$value'$checked>".my_disk($name)."</option>";
  }
  if (strpos($name, 'disk')!==false) {
    $checked = strpos("$value,", "$name,")===false ? "" : " selected";
    return "<option value='$name'$checked>".$name."</option>";
  }
}
function my_disk($name) {
  return ucfirst(preg_replace('/^(disk)/','${1}&nbsp;',$name));
}
function my_word($num) {
  $words = array('zero','one','two','three','four','five','six','seven','eight','nine','ten','eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen','eighteen','nineteen','twenty');
  return $num<count($words) ? $words[$num] : $num;
}
function day_count($time) {
  global $var;
  $days = floor($var['currTime']/86400)-floor($time/86400);
  switch (true) {
  case ($days<0):
    return "";
  case ($days==0):
    return " (today)";
  case ($days==1):
    return " (yesterday)";
  case ($days<=31):
    return " (".my_word($days)." days ago)";
  case ($days<=61):
    return " <span class='orange-text'>($days days ago)</span>";
  case ($days>61):
    return " <span class='red-text'>($days days ago)</span>";
  }
}
function plus($val, $word, $last = false) {
  return ($val || $last) ? ($val.' '.$word.($val!=1 ? 's' : '').($last ? '' : ', ')) : '';
}
function my_check($time) {
  global $disks;
  if (!$time) return "unavailable (system reboot)";
  $days = floor($time/86400);
  $time -= $days*86400;
  $hour = floor($time/3600);
  $mins = $time/60%60;
  $secs = $time%60;
  return plus($days, 'day').plus($hour, 'hour').plus($mins, 'minute').plus($secs, 'second', true).". Average speed: ".my_scale($disks['parity']['size']*1024/$time,$unit,1)." $unit/sec";
}
function end_time($time) {
  $days = floor($time/1440);
  $time -= $days*1440;
  $hour = floor($time/60);
  $mins = $time%60;
  return plus($days, 'day').plus($hour, 'hour').plus($mins, 'minute', true);
}
function my_key() {
  $keyfile = exec("find /boot/config -name '*.key'");
  return strlen($keyfile) ? my_time(filemtime($keyfile)) : "";
}
function urlencode_path($path) {
  return str_replace("%2F", "/", urlencode($path));
}
// These functions echo their output
function input_secure_users($sec) {
  global $name, $users;
  echo "<table class='access_list2'>";
  $write_list = explode(",", $sec[$name]['writeList']);
  foreach ($users as $user) {
    $idx = $user['idx'];
    if ($user['name'] == "root") {
      echo "<input type='hidden' name='userAccess.$idx' value='no-access'>";
      continue; 
    }
    if (in_array( $user['name'], $write_list))
      $userAccess = "read-write";
    else
      $userAccess = "read-only"; 
    echo "<tr><td>{$user['name']}</td>";
    echo "<td><select name='userAccess.$idx' size='1'>";
    echo mk_option($userAccess, "read-write", "Read/Write");
    echo mk_option($userAccess, "read-only", "Read-only");
    echo "</select></td></tr>";
  }
  echo "</table>";
}
function input_private_users($sec) {
  global $name, $users;
  echo "<table class='access_list2'>";
  $read_list = explode(",", $sec[$name]['readList']);
  $write_list = explode(",", $sec[$name]['writeList']);
  foreach ($users as $user) {
    $idx = $user['idx'];
    if ($user['name'] == "root") {
      echo "<input type='hidden' name='userAccess.$idx' value='no-access'>";
      continue; 
    }
    if (in_array( $user['name'], $read_list))
      $userAccess = "read-only";
    elseif (in_array( $user['name'], $write_list))
      $userAccess = "read-write";
    else
      $userAccess = "no-access";
    echo "<tr><td>{$user['name']}</td>";
    echo "<td><select name='userAccess.$idx' size='1'>";
    echo mk_option($userAccess, "read-write", "Read/Write");
    echo mk_option($userAccess, "read-only", "Read-only");
    echo mk_option($userAccess, "no-access", "No Access");
    echo "</select></td></tr>";
  }
  echo "</table>";
}
?>
