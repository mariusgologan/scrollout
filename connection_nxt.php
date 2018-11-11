<?
define('SETUP_FILE', '/var/www/connection.cfg');

$interface=exec("sudo /sbin/ip link show | /usr/bin/awk -F ': ' '/^[0-9]*: .*: <BROADCAST,MULTICAST/ {print $2;exit;}'");
$ipaddr = exec('sudo /sbin/ifconfig | /bin/grep -v "127\.0\.0" | /bin/grep -m1 "inet .* Bcast.* Mask" | /bin/sed "s/.*addr:\([0-9]*\.[0-9]*\.[0-9]*\.[0-9]*\) *B.*/\1/"');
$ipaddr6 = exec("sudo /sbin/ip -6 a s dev ". $interface ." | /usr/bin/awk '/inet6.*scope global/ {print $2; exit;}' | /usr/bin/awk -F '/' '{print $1}'");
$maskaddr = exec('sudo /sbin/ifconfig | /bin/grep -m1 "inet .* Bcast.* Mask" | /bin/sed "s/.*Mask:\([0-9]*\.[0-9]*\.[0-9]*\.[0-9]*\)$/\1/"');
$maskaddr6 = exec("sudo /sbin/ip -6 a s dev ". $interface ." | /usr/bin/awk '/inet6.*scope global/ {print $2; exit;}' | /usr/bin/awk -F '/' '{print $2}'");
$gwaddr = exec('sudo /sbin/route -n | /bin/grep -m1 "^0.0.0.0.*" | /bin/sed "s/^0.0.0.0 *\([0-9]*\.[0-9]*\.[0-9]*\.[0-9]*\) *.*/\1/"');
$gwaddr6 = exec("sudo /sbin/ip -6 r s dev ". $interface ." | /usr/bin/awk '/^default/ && !/ fe80:/ {print $3; exit;}'");
$ns = exec('/bin/cat /etc/resolv.conf | /bin/grep "^nameserver " | /usr/bin/cut -d " " -f2 | /usr/bin/tr "\n" " "');
$domainsearch = exec('/bin/cat /etc/resolv.conf | /bin/grep "^search " | /usr/bin/cut -d " " -f2 | /usr/bin/tr "\n" " "');
$hostname = exec('/bin/hostname');
$dn = exec('/bin/hostname -d');


function file2array ()
{
  if (file_exists(SETUP_FILE))
  {
	$array = explode("\n", file_get_contents(SETUP_FILE));
	foreach ($array AS $count => $line)
	{
	  $position = strpos($line, '=');
	  $phar = array('(',')');
	  $output[substr($line, 0, $position)] = substr(str_replace($phar, '', $line), $position + 1, -1);
	  unset($position);
	}

	return $output;

  }
}

$values = file2array();

?>
<?php

$htmlEndScripts = <<<HTML

	<script type="text/javascript">

		var swticheries = [];
	    if ($(".js-dynamic-state")[0]) {
			var elems = Array.prototype.slice.call(document.querySelectorAll('.js-dynamic-state'));
	        elems.forEach(function (html) {
	        	var sw = new Switchery(html, { color: '#04acec', jackColor: '#fff' });
	            swticheries.push(sw);
	        });
	    }

	    function validatehostname(hostname) {
	        var hostnameReg = /^([\w-]+\.[\w-]+([\.\w-]+)?)?$/;
	        if( hostnameReg.test( hostname ) ) {
	        $('#error-hostname').show();
	        } else {
	        $('#error-hostname').hide();
	        }
	    }

	    validatehostname($("#hostname").val());

	    function toggle(source) {

		    if (source.checked==true){
				$("#dns_caching_info").show("fast");
				for(var swch in swticheries){
					setSwitchery(swticheries[swch], true);
					swticheries[swch].enable();
				}
		    }else{
				$("#dns_caching_info").hide("fast");
		    	for(var swch in swticheries){
					setSwitchery(swticheries[swch], false);
					swticheries[swch].disable();
				}
		    }
		}
		$( document ).ready(function() {

			cachinginput  = $("#caching")[0];

			console.log(cachinginput);
			if (cachinginput.checked == true){

		    }else{
				$("#dns_caching_info").hide("fast");
		    	for(var swch in swticheries){
					setSwitchery(swticheries[swch], false);
					swticheries[swch].disable();
				}
		    }
		});

		function setSwitchery(switchElement, checkedBool) {
		    if((checkedBool && !switchElement.isChecked()) || (!checkedBool && switchElement.isChecked())) {
		        switchElement.setPosition(true);
		        switchElement.handleOnchange(true);
		    }
		}

		function submitSettings(){
			$("#page_form").submit();
		}
    </script>

HTML;

require_once("assets/_header.php");
require_once("assets/_footer.php");

echo $htmlHeader;
?>


    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
           <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
              
              <div class="menu_section">
                <h3><i class="fa fa-plug" aria-hidden="true"></i>&nbsp;&nbsp;CONNECT</h3>
                <ul class="nav side-menu">
                  <li class="active">
                  	<a href="#/connect/configure"><i class="fa fa-cog"></i>&nbsp;&nbsp;Configure</a>
                  </li>
                </ul>
              </div>
          
            </div>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
          	<h1 class="page-header">Connection <?= $interface ?></h1>
			<form name="setup" method="POST" action="connection_nxt.php" id="page_form">



				<div class="row">
					<div class="col-lg-1 col-md-2 col-sm-6 col-xs-6">
					    <input type="radio" class="with-font" name="network" value="0" id="network_0"<?= (isset($values['network']) && (0 == $values['network'])) ? ' checked' : '' ?> />
					    <label for="network_0">Auto</label>
					</div>
					<div class="col-lg-1 col-md-2 col-sm-6 col-xs-6">
					    <input type="radio" class="with-font" name="network" value="1" id="network_1"<?= (isset($values['network']) && (1 == $values['network'])) ? ' checked' : '' ?> />
					    <label for="network_1">Manual</label>
					</div>
				</div>




	          	<div class="row">
	          		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
						Hostname
					</div>
	          	  	<div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
		          	  	<div class="input-group">
						    <input class="form-control" style="text-align:right;" size="17" placeholder="hostname" type="text" name="hostname" id="hostname" value="<?= $hostname ?>" onChange="validatehostname(this.value)" required/>
							<span class="input-group-addon" style="text-align: left;">.<?= $dn ?></span>
						</div>
					</div>
					<div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
						<span id="error-hostname" style="color: #f00; vertical-align: -webkit-baseline-middle; display: none;">Invalid hostname</span>
					</div>
				</div>




	          	<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
						IP address
					</div>
					<div class="col-lg-3 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group">
						    <span class="input-group-addon">IPv4:</span>
						    <input class="form-control" size="12" placeholder="IPv4 address" type="text" name="ip" id="ip" value="<?= $ipaddr ?>"  required>
						</div>
					</div>
					<div class="col-lg-3 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group">
						    <span class="input-group-addon">IPv6:</span>
						    <input class="form-control" size="22" placeholder="IPv6 address" type="text" name="ipv6" id="ipv6" value="<?= $ipaddr6 ?>">
						</div>
					</div>
					<div class="col-lg-3 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group">
						    <span class="input-group-addon">Additional SMTP Ports:</span>
						    <input class="form-control" placeholder="2525,3000:4000" type="text" name="port" id="port" value="<?= $values['port'] ?>" size="10" >
						</div>
					</div>
				</div>






	          	<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
						Mask
					</div>
					<div class="col-lg-3 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group">
						    <span class="input-group-addon">IPv4:</span>
						    <input class="form-control" size="12" placeholder="IPv4 Network mask" name="mask" type="text" id="mask" value="<?= $maskaddr ?>"  required>
						</div>
					</div>
					<div class="col-lg-3 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group">
						    <span class="input-group-addon">IPv6:</span>
						    <input class="form-control" size="22" placeholder="IPv6 Network mask" name="mask6" type="text" id="mask6" value="<?= $maskaddr6 ?>" >
						</div>
					</div>
				</div>






	          	<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
						Gateway
					</div>
					<div class="col-lg-3 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group">
						    <span class="input-group-addon">IPv4:</span>
						    <input size="12" placeholder="IPv4 Router" name="gateway" class="form-control" type="text" id="gateway" value="<?= $gwaddr ?>"  required>
						</div>
					</div>
					<div class="col-lg-3 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group">
						    <span class="input-group-addon">IPv6:</span>
						    <input size="22" placeholder="IPv6 Router" name="gateway6" class="form-control" type="text" id="gateway6" value="<?= $gwaddr6 ?>">
						</div>
					</div>
				</div>





	          	<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
						DNS Servers
					</div>
					<div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
						<div class="input-group">
						    <span class="input-group-addon">IPv4 and/or IPv6 :</span>
						    <input id="msg" type="text" class="form-control" name="dns1" placeholder="DNS servers separated by space: 192.168.1.1 192.168.1.2 ::1 2001:4860:4860::8888" value="<?= $ns ?>"/>
						</div>
					</div>
				</div>



				<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
						DNS Suffixes Search
					</div>
					<div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
						<input name="dnssuffix" type="text" id="dns3" value="<?= $domainsearch ?>" size="50" class="form-control" name="msg" placeholder="Optional local.domain suffix search" />
					</div>
				</div>





				<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
						Use internal DNS server (recommended)
						<input type="checkbox" name="caching" value="1" class="js-switch" onChange="toggle(this)" id="caching" <?= (isset($values['caching']) && (1 == $values['caching'])) ? ' checked' : '' ?> />
					</div>
				</div>



				<div id="dns_caching_info"  class="row seven-cols" style="margin-top:15px;">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						Internal DNS server (nameserver caching) improves spam detection and Bayesian training.
						<br/>
						Training Days: (optional)
						<br/><br/>
					</div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-12">
						Mondays<br/>
						<input type="checkbox" name="monday" value="1" class="js-dynamic-state" id="monday" <?= (isset($values['monday']) && (1 == $values['monday'])) ? ' checked' : '' ?> />
					</div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-12">
						Tuesdays<br/>
						<input type="checkbox" name="tuesday" value="2" class="js-dynamic-state" id="tuesday" <?= (isset($values['tuesday']) && (2 == $values['tuesday'])) ? ' checked' : '' ?>/>
					</div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-12">
						Wednesdays<br/>
						<input type="checkbox" name="wednesday" value="3" class="js-dynamic-state" id="wednesday" <?= (isset($values['wednesday']) && (3 == $values['wednesday'])) ? ' checked' : '' ?>/>
					</div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-12">
						Thursdays<br/>
						<input type="checkbox" name="thursday" value="4" class="js-dynamic-state" id="thursday" <?= (isset($values['thursday']) && (4 == $values['thursday'])) ? ' checked' : '' ?>/>
					</div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-12">
						Fridays<br/>
						<input type="checkbox" name="friday" value="5" class="js-dynamic-state" id="friday" <?= (isset($values['friday']) && (5 == $values['friday'])) ? ' checked' : '' ?>/>
					</div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-12">
						Saturdays<br/>
						<input type="checkbox" name="saturday" value="6" class="js-dynamic-state" id="saturday" <?= (isset($values['saturday']) && (6 == $values['saturday'])) ? ' checked' : '' ?>/>
					</div>
					<div class="col-lg-1 col-md-1 col-sm-1 col-xs-12">
						Sundays<br/>
						<input type="checkbox" name="sunday" value="7" class="js-dynamic-state" id="sunday" <?= (isset($values['sunday']) && (7 == $values['sunday'])) ? ' checked' : '' ?>/>
					</div>
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="float: left; padding: 5px 5px; margin: 5px 5px; border-radius: 5px; word-wrap: break-word;">

	                    <div style="color: red; font-size: 11px; word-wrap: break-word;">
	                    Warning:<br>
	                    <li> Many RBL services are limited for free usage (i.e.: 100,000-300,000 queries/day).</li> 
	                    <li> Some providers may charge fees when limits are reached, but not retroactively.</li>
	                    <br>
	                    </div>
	                    <div style="font-size: 11px; word-wrap: break-word;">
	                    You may use internal DNS server to train spam detection (for few days after initial installation) or
	                    when spam detection is poor.<br>
	                    <b>With Training Days option, you can select when to use internal DNS and improve Bayes training.</b><br>
	                    </div>

                    </div>
				</div>


				<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
						<div class="button_save" id="button_save" onClick="submitSettings();">
							Save
						</div>
						<div id="apply_loading" style="display: none;">
							<i class="fa fa-circle-o-notch fa-spin fa-fw"></i>&nbsp;&nbsp;Saving and applying settings. Please wait...
						</div>
					</div>
				</div>

          	</form>
        </div>
      </div>
    </div>

<? echo $htmlFooter; ?>