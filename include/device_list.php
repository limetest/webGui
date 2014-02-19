<?
//Global (used by preclear_disk)
$screen = '/tmp/screen_buffer';

// Helper functions
function echo_table_device_begin() {
  global $temps, $temps_count, $fsSize, $fsUsed, $fsFree, $reads, $writes, $errors;
  $temps=0; $temps_count=0;
  $fsSize=0; $fsUsed=0; $fsFree=0;
  $reads=0; $writes=0; $errors=0;
  echo <<<EOT
<table class="disk_status {$display['view']} {$display['align']}">
<thead>
<tr>
<td>Device</td>
<td>Identification</td>
<td>Temp.</td>
<td>Size</td>
<td>Used</td>
<td>Free</td>
<td>Reads</td>
<td>Writes</td>
<td>Errors</td>
<td>View</td>
</tr>
</thead>
<tbody>
EOT;
}
function echo_table_end() {
  echo <<<EOT
</tbody>
</table>
EOT;
}

function tr_row($toggle=1) {
  static $tr_num=1;
  $tr_num = ($tr_num + $toggle) & 1;
  return "tr_row$tr_num";
}

function device_info($disk) {
  global $root, $path, $var, $display, $screen;
  $href = $disk['name'];
  if ($href != 'preclear') {
    $name = my_disk($href);
    $type = strpos($href,'disk')===false ? $name : "Disk";
  } else {
    $name = '+++';
    $type = 'Preclear';
    $href = "{$disk['device']}&file=$screen";
  }
  $action = strpos($disk['color'],'blink')===false ? "down" : "up";
  $spin_disk = "";
  $icon = "icon gap";
  if ($display['spin']) {
    if ($var['fsState']=="Started") {
      if ($href != 'cache' && isset($disk['idx'])) {
        $cmd = "/root/mdcmd&arg1=spin{$action}&arg2={$disk['idx']}";
      } else {
        $cmd = ($action=='up' ? "smartctl&arg1=-H" : "hdparm&arg1=-y")."&arg2=/dev/{$disk['device']}";
      }
      $a_ = "<a href='update.htm?cmd={$cmd}&runCmd=Apply' target='progressFrame'>"; $_a = "</a>";
      $title = "Spin ".ucfirst($action);
    } else {
      $a_ = ""; $_a = "";
      $title = "Unavailable";
    }
    $spin_disk = "{$a_}<img src='plugins/webGui/images/$action.png' title='$title' class='icon gap'>{$_a}";
    $icon = "icon";
  }
  $ball = "plugins/webGui/images/{$disk['color']}".(!$display['blink'] && $action=='up' ? "0" : "").".gif";
  $blink = str_replace('on','blink',$ball);
  $optional_blinking_indicator = strpos($disk['color'],'grey')===false ? "<img src='$blink' class='icon'>Disk spun-down<br>" : "";
  if ($type != 'Flash' && $type != 'Preclear') {
    $status = "<a href='#' class='info' onClick='return false'>
    <img src='$ball' class='$icon'><span>
    <img src='plugins/webGui/images/green-on.gif' class='icon'>Normal operation<br>
    <img src='plugins/webGui/images/yellow-on.gif' class='icon'>Invalid data content<br>
    <img src='plugins/webGui/images/red-on.gif' class='icon'>Disabled disk<br>
    <img src='plugins/webGui/images/blue-on.gif' class='icon'>New disk, not in array<br>
    <img src='plugins/webGui/images/grey-off.gif' class='icon'>No disk present<br>
    $optional_blinking_indicator
    </span></a>$spin_disk";
  } else {
    $icon = $display['spin'] ? "icon wide" : "icon gap";
    $status = "<img src='$ball' class='$icon'>";
  }
  $link = strpos($disk['status'], '_NP')===false ? "<a href='$path/$type?name=$href'>$name</a>" : $name;
  return $status.$link;
}
function device_browse($disk) {
  global $path;
  if ($disk['fsStatus'] == 'Mounted'):
    $dir = $disk['name']=="flash" ? "/boot" : "/mnt/{$disk['name']}";
    return "<a href='$path/Browse?dir=$dir'><img src='plugins/webGui/images/folder_explore.png' title='Browse $dir'></a>";
  else:
    return "";
  endif;
}
function device_desc($disk) {
  global $display;
  return "{$disk['id']} ({$disk['device']})".($display['size'] ? " {$disk['size']}" : "");
}
function assignment($disk) {
  global $devs, $screen;
  $out = "";
  $out .= "<form method='POST' name=\"{$disk['name']}Form\" action='/update.htm' target='progressFrame'><input type='hidden' name='changeDevice' value='Apply'>";
  $out .= "<select name=\"slotId.{$disk['idx']}\" onChange=\"{$disk['name']}Form.submit()\">";
  $empty = ($disk['idSb']!="" ? "no device" : "unassigned");
  if ($disk['id']!=""):
    $out .= "<option value=\"{$disk['id']}\" selected>".device_desc($disk)."</option>";
    $out .= "<option value=''>$empty</option>";
  else:
    $out .= "<option value='' selected>$empty</option>";
  endif;
  foreach ($devs as $dev):
    if (!file_exists("$screen_{$dev['device']}")) $out .= "<option value=\"{$dev['id']}\">".device_desc($dev)."$warning</option>";
  endforeach;
  $out .= "</select></form>";
  return $out;
}

function echo_flash_row($disk) {
  $disk['fsUsed'] = $disk['size'] - $disk['fsFree'];
  echo "<tr class='".tr_row()."'>";
  echo "<td>".device_info($disk)."</td>";
  echo "<td>".device_desc($disk)."</td>";
  echo "<td>*</td>";
  echo "<td>".my_scale($disk['size']*1024, $units).' '.$units."</td>";
  echo "<td>".my_scale($disk['fsUsed']*1024, $units).' '.$units."</td>";
  echo "<td>".my_scale($disk['fsFree']*1024, $units).' '.$units."</td>";
  echo "<td>".$disk['numReads']."</td>";
  echo "<td>".$disk['numWrites']."</td>";
  echo "<td>".$disk['numErrors']."</td>";
  echo "<td>".device_browse($disk)."</td>";
  echo "</tr>";
}

function echo_disk_row_stopped($disk) {
  echo "<tr class='".tr_row()."'>";
  switch ($disk['status']) {
  case "DISK_NP":
    echo "<td>".device_info($disk)."</td>";
    echo "<td colspan='9'>".assignment($disk)."</td>";
    break;
  case "DISK_OK":
    echo "<td>".device_info($disk)."</td>";
    echo "<td>".assignment($disk)."</td>";
    echo "<td>".my_temp($disk['temp'])."</td>";
    echo "<td>".my_scale($disk['size']*1024, $units).' '.$units."</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td></td>";
    break;
  case "DISK_INVALID":
    echo "<td>".device_info($disk)."</td>";
    echo "<td>".assignment($disk)."</td>";
    echo "<td>".my_temp($disk['temp'])."</td>";
    echo "<td>".my_scale($disk['size']*1024, $units).' '.$units."</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td></td>";
    break;
  case "DISK_DSBL":
    echo "<td>".device_info($disk)."</td>";
    echo "<td>".assignment($disk)."</td>";
    echo "<td>".my_temp($disk['temp'])."</td>";
    echo "<td>".my_scale($disk['size']*1024, $units).' '.$units."</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td></td>";
    break;
  case "DISK_DSBL_NP":
    if ($disk['name']=="parity") {
      echo "<td>".device_info($disk)."</td>";
      echo "<td colspan='9'>".assignment($disk)."</td>";
    }
    else {
      echo "<td>".device_info($disk)."<br><em>Not installed</em></td>";
      echo "<td>".assignment($disk)."<em>{$disk['idSb']}</em></td>";
      echo "<td>-</td>";
      echo "<td><em>".my_scale($disk['sizeSb']*1024, $units).' '.$units."</em></td>";
      echo "<td>-</td>";
      echo "<td>-</td>";
      echo "<td>-</td>";
      echo "<td>-</td>";
      echo "<td>-</td>";
      echo "<td></td>";
    }
    break;
  case "DISK_DSBL_NEW":
    echo "<td>".device_info($disk)."</td>";
    echo "<td>".assignment($disk)."</td>";
    echo "<td>".my_temp($disk['temp'])."</td>";
    echo "<td>".my_scale($disk['size']*1024, $units).' '.$units."</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td></td>";
    break;
  case "DISK_NP_MISSING":
    echo "<td>".device_info($disk)."<br><em>Missing</em></td>";
    echo "<td>".assignment($disk)."<em>{$disk['idSb']}</em></td>";
    echo "<td>-<br>&nbsp;</td>";
    echo "<td><em>".my_scale($disk['sizeSb']*1024, $units).' '.$units."</em></td>";
    echo "<td>-<br>&nbsp;</td>";
    echo "<td>-<br>&nbsp;</td>";
    echo "<td>-<br>&nbsp;</td>";
    echo "<td>-<br>&nbsp;</td>";
    echo "<td>-<br>&nbsp;</td>";
    echo "<td></td>";
    break;
  case "DISK_WRONG":
    echo "<td>".device_info($disk)."<br><em>Wrong</em></td>";
    echo "<td>".assignment($disk)."<em>{$disk['idSb']}</em></td>";
    echo "<td>".my_temp($disk['temp'])."<br>&nbsp;</td>";
    echo "<td>".my_scale($disk['size']*1024, $units).' '.$units."<br><em>".my_scale($disk['sizeSb']*1024, $units).' '.$units."</em></td>";
    echo "<td>-<br>&nbsp;</td>";
    echo "<td>-<br>&nbsp;</td>";
    echo "<td>-<br>&nbsp;</td>";
    echo "<td>-<br>&nbsp;</td>";
    echo "<td>-<br>&nbsp;</td>";
    echo "<td></td>";
    break;
  case "DISK_NEW":
    echo "<td>".device_info($disk)."</td>";
    echo "<td>".assignment($disk)."</td>";
    echo "<td>".my_temp($disk['temp'])."</td>";
    echo "<td>".my_scale($disk['size']*1024, $units).' '.$units."</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td>-</td>";
    echo "<td></td>";
    break;
  }
  echo "</tr>";
}

function echo_disk_row_started($disk) {
  global $temps, $temps_count, $fsSize, $fsUsed, $fsFree, $reads, $writes, $errors;
  if (isset($disk['fsFree'])) $disk['fsUsed'] = $disk['sizeSb'] - $disk['fsFree'];
  $none = $disk['name']=="parity" ? '-' : '';
  echo "<tr class='".tr_row()."'>";
  switch ($disk['status']) {
  case "DISK_NP":
    echo "<td>".device_info($disk)."</td>";
    echo "<td colspan='9'>Not installed</td>";
    break;
  case "DISK_DSBL_NP":
    if ($disk['name']=="parity") {
      echo "<td>".device_info($disk)."</td>";
      echo "<td colspan='9'>Not installed</td>";
    }
    else {
      echo "<td>".device_info($disk)."</td>";
      echo "<td><em>Not installed</em></td>";
      echo "<td>-</td>";
      echo "<td><em>".my_scale($disk['sizeSb']*1024, $units).' '.$units."</em></td>";
      echo "<td><em>".($disk['fsStatus']=='Mounted' ? my_scale($disk['fsUsed']*1024, $units).' '.$units : $disk['fsStatus'])."</em></td>";
      echo "<td><em>".($disk['fsStatus']=='Mounted' ? my_scale($disk['fsFree']*1024, $units).' '.$units : $none)."</em></td>";
      echo "<td>-</td>";
      echo "<td>-</td>";
      echo "<td>-</td>";
      echo "<td>".device_browse($disk)."</td>";
    }
    break;
  default:
    echo "<td>".device_info($disk)."</td>";
    echo "<td>".device_desc($disk)."</td>";
    echo "<td>".my_temp($disk['temp'])."</td>";
    echo "<td>".my_scale($disk['sizeSb']*1024, $units).' '.$units."</td>";
    echo "<td>".($disk['fsStatus']=='Mounted' ? my_scale($disk['fsUsed']*1024, $units).' '.$units : $disk['fsStatus'])."</td>";
    echo "<td>".($disk['fsStatus']=='Mounted' ? my_scale($disk['fsFree']*1024, $units).' '.$units : $none)."</td>";
    echo "<td>".my_number($disk['numReads'])."</td>";
    echo "<td>".my_number($disk['numWrites'])."</td>";
    echo "<td>".my_number($disk['numErrors'])."</td>";
    echo "<td>".device_browse($disk)."</td>";
    break;
  }
  echo "</tr>";
  // update array data stats
  if (is_numeric($disk['temp'])) {
    $temps += $disk['temp'];
    $temps_count += 1;
  }
  if ($disk['name']!="parity") {
    $fsSize += $disk['sizeSb'];
    $fsUsed += $disk['fsUsed'];
    $fsFree += $disk['fsFree'];
  }
  $reads += $disk['numReads'];
  $writes += $disk['numWrites'];
  $errors += $disk['numErrors'];
}

function echo_row_device($type) {
  global $var, $disks;
  foreach ($disks as $disk) if (strstr($disk['name'], $type)) {
    if ("flash" == $type) {
      echo_flash_row($disk);
    }
    else {
      if ($var['fsState'] == "Stopped")
	echo_disk_row_stopped($disk);
      else
	echo_disk_row_started($disk);
    }
  }
}

function echo_row_unassigned_devices() {
  global $devs;
  $status = file_exists("/var/log/plugins/simpleFeatures.disk.preclear") ? '' : '_NP';
  foreach ($devs as $dev) {
    $dev['name'] = 'preclear';
    $dev['color'] = 'blue-on';
    $dev['status'] = $status;
    echo "<tr class='".tr_row()."'>";
    echo "<td>".device_info($dev)."</td>";
    echo "<td>".device_desc($dev)."</td>";
    echo "<td></td>";
    echo "<td>".my_scale($dev['size']*1024, $units).' '.$units."</td>";
    if (file_exists("/tmp/preclear_stat_{$dev['device']}")) {
      $text = exec("cut -d'|' -f3 /tmp/preclear_stat_{$dev['device']} | sed 's:\^n:\<br\>:g'");
      if (strpos($text,'Total time')===false) $text = 'Preclear in progress... '.$text;
      echo "<td colspan='6'><i>$text</i></td>";
    }
    else
      echo "<td colspan='6'></td>";
    echo "</tr>";
  }
}

function echo_array_totals() {
  global $var, $display;
  global $temps, $temps_count, $fsSize, $fsUsed, $fsFree, $reads, $writes, $errors;
  $icon = $display['spin'] ? "icon wide" : "icon gap";
  echo "<tr class='tr_last'>";
  echo "<td><img src='plugins/webGui/images/task.png' class='$icon'>Total</td>";
  echo "<td>(Size/Used/Free excludes Parity)</td>";
  echo "<td>".($temps_count>0?my_temp(round($temps/$temps_count, 1)):'*')."</td>";
  echo "<td>".my_scale($fsSize*1024, $units).' '.$units."</td>";
  echo "<td>".my_scale($fsUsed*1024, $units).' '.$units."</td>";
  echo "<td>".my_scale($fsFree*1024, $units).' '.$units."</td>";
  echo "<td>".my_number($reads)."</td>";
  echo "<td>".my_number($writes)."</td>";
  echo "<td>".my_number($errors)."</td>";
  echo "<td></td>";
  echo "</tr>";
}

function echo_row_help($text) {
  echo "<tr class='".tr_row(0)." inline_help'><td colspan='10'><blockquote class='inline_help'>";
  echo Markdown($text);
  echo "</blockquote></td></tr>";
}
?>
