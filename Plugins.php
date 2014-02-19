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

$plugins = array_diff(scandir("/var/log/plugins"), array('.', '..'));
foreach ($plugins as $name) {
  // the entries are links to plugin files
  $plugin_file = readlink("/var/log/plugins/$name");

  // get the "current version"
  $version = exec("plugin version $plugin_file");
  if ($version == "")
    $version = "unknown";

  $action = "";

  // get the "latest version" and maybe offer a 'check' action
  $latest = exec("plugin check $plugin_file");
  if ($latest == "") {
    $latest = make_link("check", $plugin_file);
  }
  else {
    // maybe offer 'update' action
    if (strcmp($latest, $version) > 0) {
      $action = make_link("update", $plugin_file);
      $action .= " | ";
    }
  }

  // offer 'remove' action
  $action .= make_link("remove", $plugin_file);
    
  echo "<tr><td>1</td><td>{$name}</td><td>{$version}</td><td>{$latest}</td><td>{$action}</td></tr>";
}
echo "</tbody></table>";
?>
