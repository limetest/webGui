<style>
table.tablesorter {
	font-family:arial;
	margin:10px 0pt 15px;
	text-align: left;
}
table.tablesorter thead tr th, table.tablesorter tfoot tr th {
	background-color: #e6EEEE;
	border: 1px solid #FFF;
	font-size: 8pt;
	padding: 4px;
}
table.tablesorter thead tr .header {
	background-image: url(/plugins/webGui/images/sort_both.png);
	background-repeat: no-repeat;
	background-position: center right;
	cursor: pointer;
}
table.tablesorter tbody td {
	color: #3D3D3D;
	padding: 4px;
	background-color: #FFF;
	vertical-align: top;
}
table.tablesorter tbody tr.odd td {
	background-color:#F0F0F6;
}
table.tablesorter thead tr .headerSortUp {
	background-image: url(/plugins/webGui/images/sort_asc.png);
}
table.tablesorter thead tr .headerSortDown {
	background-image: url(/plugins/webGui/images/sort_desc.png);
}
table.tablesorter thead tr .headerSortDown, table.tablesorter thead tr .headerSortUp {
	background-color: #8dbdd8;
}
</style>
<script type="text/javascript" src="/plugins/vendor/tablesorter/jquery.tablesorter.min.js"></script>
<script>
$(document).ready(function() {
  $("#plugin_table").tablesorter( {headers: { 0: { sorter: false}}} );
});
</script>
<script>
function openWindow(cmd, title) {
  var name     = "<?=$var[NAME];?> " + title;
  var width    = ((screen.width*2)/3)||0;
  var height   = ((screen.height*2)/3)||0;
  var features = "location=no,resizeable=yes,scrollbars=yes,width=" + width + ",height=" + height;

  var url      = "/logging.htm?title="+name+"&cmd="+cmd+"&forkCmd=Start";

  var myWindow = window.open(url, name.replace(/ /g, "_"), features);
  myWindow.focus();
}
function noticeUninstall() {
  alert( "You must reboot server for Uninstall to take effect, or redownload to undo the Uninstall.");
}
</script>

<?PHP

function make_link($method, $plugin_file) {
  $cmd = "plugin $method $plugin_file";
  return "<a href='/update.htm?cmd=$cmd&forkCmd=Start' target='progressFrame'>$method</a>";
}

echo "<table class='tablesorter' id='plugin_table'><thead>";
echo "<tr><th>X</th><th>Plugin</th><th>Current Version</th><th>Latest Version</th><th>Y</th></tr>";
echo "</thead><tbody>";

foreach (glob( "/var/log/plugins/*.plg", GLOB_NOSORT) as $entry) {
  $plugin_file = readlink($entry);
  if ($plugin_file === FALSE)
    continue;

  // get the plugin name
  $name = exec("plugin name $plugin_file");
  if ($name === FALSE)
    $name = basename($plugin_file, ".plg");

  // get the "current version"
  $version = exec("plugin version $plugin_file");
  if ($version == FALSE)
    $version = "unknown";

  $action = "";

  // get the "latest version" and maybe offer a 'check' action
  if (file_exists("/tmp/plugin/".basename("$plugin_file")) {
    $latest = exec("plugin version /tmp/plugin/".basename("$plugin_file"));
    if ($lastest === FALSE) {
        $latest = "unknown";
    }
    else if (strcmp($latest, $version) > 0) {
      $action = make_link("update", $plugin_file);
      $action .= " | ";
    }
    else {
      $latest = "up-to-date";
    }
  }
  else {
    $latest = make_link("check", $plugin_file);
  }
      
  // offer 'remove' action
  if (!strstr("/usr/local/emhttp/plugins/", $plugin_file))
    $action .= make_link("remove", $plugin_file);
    
  echo "<tr><td>1</td><td>{$name}</td><td>{$version}</td><td>{$latest}</td><td>{$action}</td></tr>";
}
echo "</tbody></table>";
?>
