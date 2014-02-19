<?PHP
/* Copyright 2012, Bergware International & Andrew Hamer-Adams.
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2, or (at your option)
 * any later version.
 */
?>
<?
$var = parse_ini_file("/var/local/emhttp/var.ini");
ini_set('display_errors', 0);
error_reporting(E_ALL|E_STRICT);
?>

<link type="text/css" rel="stylesheet" href="/plugins/webGui/style/default_layout.css">

<script>
// server uptime & update period
var uptime = <?=strtok(exec("cat /proc/uptime"),' ')?>;
var period = 1; //seconds

function add(value, label, last) {
  return parseInt(value)+' '+label+(parseInt(value)!=1?'s':'')+(!last?', ':'');
}
function two(value, last) {
  return (parseInt(value)>9?'':'0')+parseInt(value)+(!last?':':'');
}
function updateTime() {
  document.getElementById('uptime').innerHTML = add(uptime/86400,'day')+two(uptime/3600%24)+two(uptime/60%60)+two(uptime%60,true);
  uptime += period;
  setTimeout(updateTime, period*1000);
}
</script>

<body onLoad="updateTime();" style="margin-bottom: 0; margin-top: 20px;">
<center>
<img src="/plugins/webGui/images/logo.png" alt="unRAID" width="169" height="28" border="0" /><br>
<span style="font-size:16px;color:#6FA239;font-weight:bold">unRAID Server <?=$var['regTy']?></span><br>
</center>
<div style="margin-top:14px;font-size:12px;line-height:30px;color:#333; margin-left: 40px;">

<div style="margin-top:20px;"><span style="width:80px;display:inline-block"><strong>Motherboard:</strong></span>
<?
$motherboard = exec("dmidecode -q -t 2 | awk -F: '/Manufacturer:/ {print $2}'");
$product = exec("dmidecode -q -t 2 | awk -F: '/Product Name:/ {print $2}'");
echo "$motherboard - $product";
?>
</div>
<div class="clear:both;"></div>
<div><span style="width:80px; display:inline-block"><strong>CPU:</strong></span>
<?
$cpumodel = str_replace("Processor", "", exec("dmidecode -q -t 4 | awk -F: '/Version:/ {print $2}'"));
echo "$cpumodel";
?>
</div>
<div class="clear:both;"></div>
<div><span style="width:80px; display:inline-block"><strong>Speed:</strong></span>
<?
$cpuspeed = explode(' ',trim(exec("dmidecode -q -t 4 | awk -F: '/Current Speed:/ {print $2}'")));
if ($cpuspeed[0]>=1000 && $cpuspeed[1]=='MHz'):
  $cpuspeed[0] /= 1000;
  $cpuspeed[1] = 'GHz';
endif;
echo "$cpuspeed[0] $cpuspeed[1]";
?>
</div>
<div class="clear:both;"></div>
<div><span style="width:80px; display:inline-block"><strong>Cache:</strong></span>
<?
unset($cachesize);
exec("dmidecode -q -t 7 | awk -F: '/Installed Size:/ {print $2}'", $cachesize);
$cache = array();
foreach ($cachesize as $size) if ($size!=' 0 kB') $cache[] = $size;
echo implode(',', $cache);
?>
</div>
<div class="clear:both;"></div>
<div><span style="width:80px; display:inline-block"><strong>Memory:</strong></span>
<?
$memory = exec("dmidecode -q -t 17 | awk '/Size:/ {total+=$2;unit=$3} END {print total,unit}'");
$total = exec("dmidecode -q -t 16 | awk -F: '/Maximum Capacity:/ {print $2}'");
echo "$memory (max. $total)";
?>
</div>
<div class="clear:both;"></div>
<div><span style="width:80px; display:inline-block"><strong>Network:</strong></span>
<?
unset($sPorts);
exec("ifconfig -s | awk '$1~/[0-9]$/ {print $1}'", $sPorts);
$i = 0;
foreach ($sPorts as $port):
  if ($i>0) echo "<br><span style='width:84px; display:inline-block'>&nbsp;</span>";
  if ($port=='bond0'):
    $mode = exec("cat /proc/net/bonding/$port | grep 'Mode:' | cut -d: -f2");
    echo "$port: $mode";
  else:
    unset($phy);
    exec("ethtool $port | awk -F: '/Speed:/ {print $2}; /Duplex:/ {print $2}'", $phy);
    echo "$port: {$phy[0]} - {$phy[1]} Duplex";
  endif;
  $i++;
endforeach;
?>
</div>
<div class="clear:both;"></div>
<div><span style="width:80px; display:inline-block"><strong>Connections:</strong></span>
<?
function write($number) {
  $words = array('zero','one','two','three','four','five','six','seven','eight','nine','ten','eleven','twelve','thirteen','fourteen','fifteen','sixteen','seventeen','eighteen','nineteen','twenty');
  return $number<=count($words) ? $words[$number] : $number;
}
$AFPUsers = 0;
$SMBUsers = 0;
if ($var['shareAFPEnabled']=="yes") {
  $AFPUsers = exec("ps anucx | grep -c 'afpd'");
  if ($AFPUsers > 0) $AFPUsers--;
}
if ($var['shareSMBEnabled']=="yes") {
  $SMBUsers = exec("smbstatus -p | awk 'NR>4' | wc -l");
}
echo ucfirst(write($AFPUsers+$SMBUsers));
?>
</div>
<div class="clear:both;"></div>
<div><span style="width:84px; display:inline-block"><strong>Uptime:</strong></span><span id="uptime"></span></div>
<br>
<div class="clear:both;"></div>
</div>
<center>
<?if (file_exists("/var/log/plugins/simpleFeatures.system.info")):?>
<a href="/Utils/SystemProfiler" class="button" target="_parent">More Info</a>
<?endif;?>
</center>
</body>
