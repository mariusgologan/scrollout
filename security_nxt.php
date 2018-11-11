<?php
/*****Levels*****/  
define('SETUP_FILE', '/var/www/security.cfg');
define('SECURITY_FILE', '/var/www/security.list');
/*****Levels end*****/  

/*****Certificate*****/  
define('CERT_FILE', '/etc/postfix/certs/scrollout.cert');
define('KEY_FILE','/etc/postfix/certs/scrollout.key');

$certificate = openssl_x509_parse(file_get_contents(CERT_FILE));
$validFrom = date('Y-m-d H:i:s', $certificate['validFrom_time_t']);
$validTo = date('Y-m-d H:i:s', $certificate['validTo_time_t']);
$name = $certificate['name'];

$value_cert = cert2array();
$value_key = key2array();
/***Certificate end***/

//*******Senders*******//
define('SETUP_FILE_SENDERS', '/var/www/cfg/sndr');
define('POSTFIX_FILE', '/var/www/postfix/sndr');
define('SA_FILE', '/var/www/spamassassin/20_wb.cf');
define('AMAVIS_FILE', '/var/www/amavis/sndr');
//*******Senders end*****//

//*******Countries*****//
define('SETUP_COUNTRIES_FILE', '/var/www/countries.cfg');
define('COUNTRIES_FILE', '/var/www/cfg/geo/countries');
//*******Countries end*****//

//******Reputation**********//
define('IPv4_PREF', '/var/www/rbldns/reputation-ip-');  
define('IPv6_PREF', '/var/www/rbldns/reputation-ip6-'); 
define('DOMAIN_PREF', '/var/www/rbldns/reputation-domain-');  
define('NS_PREF', '/var/www/rbldns/reputation-ns-');  
//******Reputation end**********//


function loadReputation($level, $type){
  if($type == 1) $file_path = IPv4_PREF.$level;
  elseif($type == 2) $file_path = DOMAIN_PREF.$level;
  elseif($type == 3) $file_path = NS_PREF.$level;
  $result=""; 
  if (file_exists($file_path)){ 
    $array = explode("\n", file_get_contents($file_path));
    $index_at = false;
    foreach ($array AS $count => $line)
    {
      if(strpos($line, '# ADD YOUR ENTRIES BELOW:') !== false ) $index_at = $count;  
    }
    $count_lines = count($array);
    $temp = array();
    for($j=$index_at+1; $j<$count_lines; $j++){
      $temp[] = $array[$j];
    }
    $result = implode("\n", $temp);
  }
  return $result;

}

if (file_exists(SETUP_FILE_SENDERS)){
  $ar=file(SETUP_FILE_SENDERS);

  $tot_lines=count($ar);

  $ok=array();
  $not_ok=array();

  for($i=0; $i<$tot_lines; $i++){
  
  $vector_d=str_getcsv($ar[$i],"\t");
  
  if(trim($vector_d[1])=='OK'){
    $ok[]=str_replace(array('$/','/^','/^$/','\#','\.','\@','/','\\','\-','\_','\^','\=','\+'), array('','','','#','.','@','','','-','_','^','=','+'), $vector_d[0]);
  }

  if(trim($vector_d[1])=='xMESSAGEx'){
    $not_ok[]=str_replace(array('$/','/^','/^$/','\#','\.','\@','/','\\','\-','\_','\^','\=','\+'), array('','','','#','.','@','','','-','_','^','=','+'), $vector_d[0]);
  }
  }

}

function file2array ($arg)
{
  if (file_exists($arg))
  {
  $array = explode("\n", file_get_contents($arg));
  foreach ($array AS $count => $line)
  {
    $position = strpos($line, '=');
    $phar = array('(',')',"'");
    if(in_array(substr($line, 0, $position), array("organization","web","tel","hostrelay","uhostrelay","phostrelay")))
      $output[substr($line, 0, $position)] = substr(str_replace($phar,'',$line), $position + 1, -1);
    else
      $output[substr($line, 0, $position)] = explode(" ",substr(str_replace($phar,'',$line), $position + 1, -1));

    unset($position);
  }

  return $output;
  }
}

function file2arrayCountries ($arg)
{
  if (file_exists($arg))
  {
  $array = explode("\n", file_get_contents($arg));
  foreach ($array AS $count => $line)
  {
    $position = strpos($line, '=');
    $output[substr($line, 0, $position)] = substr($line, $position + 1, -1);
    unset($position);
  }
  
  return $output;
  }
}

function cert2array ()
{
  if (file_exists(CERT_FILE))
  {
  $rawcert = file_get_contents(CERT_FILE);
  }

return $rawcert;
}

function key2array ()
{
  if (file_exists(KEY_FILE))
  {
  $privkey = file_get_contents(KEY_FILE);
  }

return $privkey;
}



function loadSecurities ()
{
  if (file_exists(SECURITY_FILE) && is_file(SECURITY_FILE))
  {
    $content = file_get_contents(SECURITY_FILE);
    $lines = explode("\n", $content);
    $output = Array();
    foreach ($lines AS $count => $line)
    {
#	  $values = explode(',', $line);
      $security = str_replace(' ', '_', trim($line));
      if (!empty($security))
      {
	array_push($output, $security);
      }
      unset($values, $count, $line, $security);
    }
    unset($content, $lines);
    array_multisort($output, SORT_ASC);
    
    return $output;
  }
  else
  {
    error_log("No security file [". SECURITY_FILE ." defined!");
  }
  
  return false;
}


function loadCountries ()
{
  if (file_exists(COUNTRIES_FILE) && is_file(COUNTRIES_FILE))
  {
  $content = file_get_contents(COUNTRIES_FILE);
  $lines = explode("\n", $content);
  $output = Array();
  foreach ($lines AS $count => $line)
  {
#  $values = explode(',', $line);
#  $country = str_replace(' ', '_', trim($values[3]));
  $char = array('\\', '\'', ' ');
    $country = str_replace($char, '_', trim($line));
    if (!empty($country))
    {
    array_push($output, $country);
    }
    unset($values, $count, $line, $country);
  }
  unset($content, $lines);
  array_multisort($output, SORT_ASC);
  
  return $output;
  }
  else
  {
  error_log("No country file [". COUNTRIES_FILE ." defined!");
  }
  
  return false;
}

/**
 *	Load data from files
 */
$values = file2array(SETUP_FILE);
$values_countries = file2arrayCountries(SETUP_COUNTRIES_FILE);
  


// LOAD SECURITIES

$securities = loadSecurities();
if (false === $securities)
{
  error_log("Can not load securities!");
}

$jsArrRangeParam1 = 
  $values["Auto_defense"][0].",".
  $values["Average_reputation"][0].",".
  $values["Body_filter"][0].",".
  $values["Connection_filter"][0].",".
  $values["Geographic_filter"][0].",".
  $values["Header_and_attachments_filter"][0].",".
  $values["Hostname_filter"][0].",".
  $values["IPSec_encryption"][0].",".
  $values["Picture_filter"][0].",".
  $values["Rate_limits_in"][0].",".
  $values["Rate_limits_out"][0].",".
  $values["Spam_trap_score"][0].",".
  $values["Spamassassin"][0].",".
  $values["URL_filter"][0].",".
  $values["Web_cache"][0];

// LOAD SECURITIES END

// LOAD COUNTRIES 

$countries = loadCountries();
if (false === $countries)
{
  error_log("Can not load countries!");
}
// var_dump($values_countries);
foreach ($countries as $country) {
  // echo "\n".$country;
  $jsArrRangeParam2 .= $values_countries[$country][0].",";
}

$jsArrRangeParam2 = rtrim($jsArrRangeParam2,",");
// LOAD COUNTRIES END



?>
<?php
$htmlEndScripts = <<<HTML

	<script type="text/javascript">


 $(document).ready(function() {



      var rangeParam1 = 4;

      var ranges = document.querySelectorAll('.range');
      var threestateswitches = document.querySelectorAll('.threestateswitch');

      var rangeParam1 = [$jsArrRangeParam1];
      var rangeParam2 = [$jsArrRangeParam2];

      for(var j = 0; j<ranges.length; j++){

        // var range = ranges[j];
        //console.log(j);
        ranges[j].style.height = '18px';
        ranges[j].style.margin = '0 auto 30px';

        var slider = noUiSlider.create(ranges[j], {
          id: j,
          start: [ rangeParam1[j] ], // 3 handles, starting at...
          connect: [true, false],
          // margin: 1, // Handles must be at least 300 apart
          // limit: 20, // ... but no more than 600
          // direction: 'rtl', // Put '0' at the bottom of the slider
          orientation: 'horizontal', // Orient the slider vertically
          behaviour: 'tap-drag', // Move handle on tap, bar is draggable
          step: 1,
          tooltips: true,
          format: wNumb({
            decimals: 0
          }),
          range: {
            // Starting at 500, step the value by 500,
            // until 4000 is reached. From there, step by 1000.
            'min': [ 1 ],
            'max': [ 10 ]
          },
          pips: {
            mode: 'values',
            values: [3.5, 6.5],
            density: 6
          },
        });
        var connect = ranges[j].querySelectorAll('.noUi-connect');
        var classes = [j+'d-1-color'];
        var timeout;

        
        ranges[j].noUiSlider.on('update', function ( values, handle ) {
          
          timeout = setTimeout(setColorTo, 500, values[handle],this.options.id);

        });

        ranges[j].noUiSlider.on('set', function ( values, handle ) {
          var input_autodef = document.querySelectorAll('.input_autodef');
          if ( handle == 0 ) {
            input_autodef[this.options.id].value = values[handle];
          }
        });

        ranges[j].noUiSlider.on('slide', function ( values, handle ) { 
          clearTimeout(timeout);
          setColorTo(values[handle],this.options.id);
        });


        for ( var i = 0; i < connect.length; i++ ) {
            connect[i].classList.add(classes[i]);
        }       
      }


      for(var j = 0; j<threestateswitches.length; j++){

        // var range = threestateswitches[j];
        console.log(j);
        threestateswitches[j].style.height = '18px';
        threestateswitches[j].style.margin = '0 auto 30px';

        var slider = noUiSlider.create(threestateswitches[j], {
          id: j,
          start: [ rangeParam2[j] ], // 3 handles, starting at...
          connect: [true, true],
          // margin: 1, // Handles must be at least 300 apart
          // limit: 20, // ... but no more than 600
          // direction: 'rtl', // Put '0' at the bottom of the slider
          orientation: 'horizontal', // Orient the slider vertically
          behaviour: 'tap-drag', // Move handle on tap, bar is draggable
          step: 1,
          // tooltips: true,
          format: wNumb({
            decimals: 0
          }),
          range: {
            // Starting at 500, step the value by 500,
            // until 4000 is reached. From there, step by 1000.
            'min': [ 0 ],
            'max': [ 2 ]
          }
        });
        var connect = threestateswitches[j].querySelectorAll('.noUi-connect');
        var classes = [j+'e-1-color',j+'e-1-color'];
        var timeout;

        
        threestateswitches[j].noUiSlider.on('update', function ( values, handle ) {
          
          timeout = setTimeout(setCountryColorTo, 500, values[handle],this.options.id);

        });

        threestateswitches[j].noUiSlider.on('set', function ( values, handle ) {
          var input_countries = document.querySelectorAll('.input_countries');
          if ( handle == 0 ) {
            input_countries[this.options.id].value = values[handle];
          }
        });

        threestateswitches[j].noUiSlider.on('slide', function ( values, handle ) { 
          clearTimeout(timeout);
          setCountryColorTo(values[handle],this.options.id);
        });


        for ( var i = 0; i < connect.length; i++ ) {
            connect[i].classList.add(classes[i]);
        }       
      }
      $(function(){ 
        $('#error-certkey').hide();
        $('#certkey').on('keydown',function(){
         
        var Addresscertkey=$(this).val(); 
        
        validatecertkey(Addresscertkey);
        });
      });  
     });


    function rangeToDefault(){

      var ranges = document.querySelectorAll('.range');
      for(var j = 0; j<ranges.length; j++){
        ranges[j].noUiSlider.set(5);  
      }
    }

    function rangeTo(array_values){

      var ranges = document.querySelectorAll('.range');
      for(var j = 0; j<ranges.length; j++){
        ranges[j].noUiSlider.set(array_values[j]);  
      }

    }


    function setColorTo(value, id){
      console.log("Setting color to id: "+id);
      if(value == 1.0){
        $('.'+id+'d-1-color').css('background','#4caf50');
      }
      else if(value == 2.0){
        $('.'+id+'d-1-color').css('background','#4caf50');
      }
      else if(value == 3.0){
        $('.'+id+'d-1-color').css('background','#4caf50');
      }
      else if(value == 4.0){
        $('.'+id+'d-1-color').css('background','#ffeb3b');        
      }
      else if(value == 5.0){
        $('.'+id+'d-1-color').css('background','#ffeb3b');        
      }
      else if(value == 6.0){
        $('.'+id+'d-1-color').css('background','#ffeb3b');        
      }
      else if(value == 7.0){
        $('.'+id+'d-1-color').css('background','#d32f2f');        
      }
      else if(value == 8.0){
        $('.'+id+'d-1-color').css('background','#d32f2f');        
      }
      else if(value == 9.0){
        $('.'+id+'d-1-color').css('background','#d32f2f');        
      }
      else if(value == 10.0){
        $('.'+id+'d-1-color').css('background','#d32f2f');        
      }

    }


    function setCountryColorTo(value, id){
      console.log("Setting country color to id: "+id);
      var input_countries = document.querySelectorAll('.country_rating');
      if(value == 0){
        $('.'+id+'e-1-color').css('background','#4caf50');
        input_countries[id].innerHTML = "Business" ;
      }
      else if(value == 1){
        $('.'+id+'e-1-color').css('background','#ffeb3b');
        input_countries[id].innerHTML = "Foreign";
      }
      else if(value == 2){
        $('.'+id+'e-1-color').css('background','#d32f2f');
        input_countries[id].innerHTML = "Spam";
      }
    }






    function submitSettingsLevels(){ 
      $("#page_form_levels").submit(); 
    }
    function submitSettingsCerts(){ 
      $("#page_form_certificates").submit(); 
    }
    function submitSettingsSenders(){ 
      $("#page_form_senders").submit(); 
    }
    function submitSettingsPassword(){ 
      $("#page_form_password").submit(); 
    }
    function submitSettingsCountries(){ 
      $("#page_form_countries").submit(); 
    }
    function submitReputationSettings(){ 
      $("#reputation_form").submit(); 
    }


  function validatecertkey(certkey) {
      var certkeyReg = /ENCRYPTED/;
      if( certkeyReg.test( certkey ) ) {
        $('#error-certkey').show();
      } else {
        $('#error-certkey').hide();
      }
    }

  function searchCountries(country){
      $('.row_country').each(function() {
        var classList = $(this).attr('class').split(/\s+/);
        if (classList[3].toLowerCase().indexOf(country.toLowerCase()) >= 0) {
            $(this).show();
        }
        else{
            $(this).hide();
        }
      });
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
                <h3><i class="fa fa-shield" aria-hidden="true"></i>&nbsp;&nbsp;SECURITY</h3>
                <ul class="nav side-menu">
                  <li><a href="#/security/levels"><i class="fa fa-sliders"></i>&nbsp;&nbsp;&nbsp;&nbsp;Levels</a></li>
                  <li><a href="#/security/senders"><i class="fa fa-ban"></i>&nbsp;&nbsp;&nbsp;&nbsp;Senders</a></li>
                  <li><a href="#/security/reputation"><i class="fa fa-black-tie"></i>&nbsp;&nbsp;&nbsp;&nbsp;Reputation</a></li>
                  <li><a href="#/security/countries"><i class="fa fa-globe"></i>&nbsp;&nbsp;&nbsp;&nbsp;Countries</a></li>
                  <li><a href="#/security/certificate"><i class="fa fa-certificate"></i>&nbsp;&nbsp;&nbsp;&nbsp;Certificate</a></li>
                  <li><a href="#/security/password"><i class="fa fa-lock" style="margin-right: 4px;"></i>&nbsp;&nbsp;&nbsp;&nbsp;Password</a></li>
                </ul>
              </div>
          
            </div>
        </div>
        



        <div class="col-lg-6 col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main default-view" id="page-security-levels">
          	<h1 class="page-header"><a href="#/security" class="hidden-lg hidden-md hidden-sm"><i class="fa fa-angle-left" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;</a>Levels</h1>
           
            <div class="row mb20">
                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin: 20px 0;font-size:18px;">
                        Presets:
                      </div> 
                      <div class="col-lg-2  col-md-2 col-sm-2 col-xs-12 mb-sm-10">
                        <div class="button_save" onClick="rangeToDefault();">
                            Default
                        </div>
                      </div>
                      <div class="col-lg-2 col-lg-offset-1  col-md-2 col-sm-2 col-xs-12">
                        <div class="button_save" onClick="rangeTo([1,1,1,2,3,4,1,1,1,4,4,4,3,2,1]);">
                            Aggressive
                        </div>
                      </div>
                      <div class="col-lg-2 col-lg-offset-1  col-md-2 col-sm-2 col-xs-12">
                        <div class="button_save" onClick="rangeTo([9,10,10,8,7,8,9,10,9,10,8,7,10,8,6]);">
                            Permissive
                        </div>
                      </div>
            </div>
			      <form name="setup" method="POST" action="connection_nxt.php#/levels" id="page_form_levels">


              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#autodefense">
                      <div class="section-header">Auto defense  </div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                      <b>Auto defense for SMTP, SSH, HTTP(s):</b><br>
                      Blocks IP addresses for some time based on the maximum number of:<br>
                      <ul>
                        <li>spam occurrences</li>
                        <li>bounce occurrences</li>
                        <li>malware occurrences</li>
                        <li>various limits exceeded repeatedly</li>
                        <li>wrong authentication occurrences</li>
                      </ul>
                      <br>
                    </div>
                  </div>
                  <div class="row collapse" id="autodefense">
                    
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <div class="range"></div>
                        <input placeholder="5.0" style="background-color: #292929; width: 1.8em" class="input_autodef" name="Auto_defense" type="hidden" hidden value="<?= !empty($values["Auto_defense"][0]) ? $values["Auto_defense"][0] : '5' ?>">
                      </div>

                  </div>
                </div>
              </div>



              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#avgrep">
                      <div class="section-header">Average reputation  </div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                      Measures the Volume and Score over a period of time for: <br>
                      <ul>
                        <li>Sender address</li>
                        <li>Sender domain</li>
                        <li>Sender IP</li>
                        <li>Client name</li>
                        <li>Fingerprints and more.</li>
                      </ul>
                      <br>
                      <b>FORMULA</b><br>
                      Incremented Score / Incremented Volume &gt;= Average reputation<br>
                      When <b>Incremented Score</b> divided by <b>Incremented Volume</b> is greater than (or equal to) <b>Average reputation value</b> then the SMTP connection is not accepted.<br>
                    </div>
                  </div>
                  <div class="row collapse" id="avgrep">
                    
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <div class="range"></div>
                        <input placeholder="5.0" style="background-color: #292929; width: 1.8em" class="input_autodef" name="Average_reputation" type="hidden" hidden value="<?= !empty($values["Average_reputation"][0]) ? $values["Average_reputation"][0] : '5' ?>">
                      </div>

                  </div>
                </div>
              </div>


              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#bodyfilter">
                      <div class="section-header">Body filter  </div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                      Adjusts the score for body fingerprint (Pyzor)
                    </div>
                  </div>
                  <div class="row collapse" id="bodyfilter">
                    
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <div class="range"></div>
                        <input placeholder="5.0" style="background-color: #292929; width: 1.8em" class="input_autodef" name="Body_filter" type="hidden" hidden value="<?= !empty($values["Body_filter"][0]) ? $values["Body_filter"][0] : '5' ?>">
                      </div>

                  </div>
                </div>
              </div>



              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#connectionfilter">
                      <div class="section-header">Connection filter  </div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                        This filter attempts to block the junk messages before they reach this system.
                        <br><br>
                        <b>RBL &amp; DNSBL</b><br>
                        <b>Check Sender IP</b> allows to weigh black/whitelists providers.<br>
                        <br>
                        The threshold = count all providers. In this example is 3:<br>
                        blacklist.provider.com<br>
                        blacklist.provider.com*2<br>
                        whitelist.provider.com*-2<br>
                        <br>
                        <b>Note:</b> For business/commercial use some providers ask for a fee. Read their ToS!
                    </div>
                  </div>
                  <div class="row collapse" id="connectionfilter">
                    
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <div class="range"></div>
                        <input placeholder="5.0" style="background-color: #292929; width: 1.8em" class="input_autodef" name="Connection_filter" type="hidden" hidden value="<?= !empty($values["Connection_filter"][0]) ? $values["Connection_filter"][0] : '5' ?>">
                      </div>

                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                        Check Client IP
                        <h5>Active for Connection filter: 1-7</h5>
                      </div>
                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <textarea class="form-control" placeholder="Sender IP reputation providers" rows="7" name="sip" id="sip"><?= !empty($values["sip"][0]) ? implode("\n",$values["sip"]) : '' ?></textarea>
                      </div>

                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                        Check Client HOSTNAME & HELO
                        <h5>Active for Connection filter: 1-6</h5>
                      </div>
                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <textarea class="form-control" placeholder="Client Hostname reputation providers" rows="3" name="shostname" id="shostname"><?= !empty($values["shostname"][0]) ? implode("\n",$values["shostname"]) : '' ?></textarea>
                      </div>

                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                        Check Sender DOMAIN
                        <h5>Active for Connection filter: 1-5</h5>
                      </div>
                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom:20px;">
                        <textarea class="form-control" placeholder="Sender Domain reputation providers" rows="3" name="sdomain" id="sdomain"><?= !empty($values["sdomain"][0]) ? implode("\n",$values["sdomain"]) : '' ?></textarea>
                      </div>

                  </div>
                </div>
              </div>



              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#geographicfilter">
                      <div class="section-header">Geographic filter  </div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                        A powerful and innovative geographic filtering for Sender IP, Server IP, URL IP and TLDs<br>
                        Depending on the selected Countries (see SECURE &gt; COUNTRIES) it will filter emails based on <br>
                        <ul>
                          <li> original sender IP</li>
                          <li> server sender IP</li>
                          <li> IP address of any URL in the body</li>
                          <li> Top Level Domains (TLDs)</li>
                        </ul>
                    </div>
                  </div>
                  <div class="row collapse" id="geographicfilter">
                    
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <div class="range"></div>
                        <input placeholder="5.0" style="background-color: #292929; width: 1.8em" class="input_autodef" name="Geographic_filter" type="hidden" hidden value="<?= !empty($values["Geographic_filter"][0]) ? $values["Geographic_filter"][0] : '5' ?>">
                      </div>

                  </div>
                </div>
              </div>




              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#attachfilter">
                      <div class="section-header">Header and attachments filter  </div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                      A filter based on the attachments of the email:<br>
                      <ul>
                        <li> executables</li>
                        <li> archives</li>
                        <li> multimedia</li>
                        <li> double or hidden extensions</li>
                      </ul>
                      It also attempt to validate the message based on its header
                    </div>
                  </div>
                  <div class="row collapse" id="attachfilter">
                    
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <div class="range"></div>
                        <input placeholder="5.0" style="background-color: #292929; width: 1.8em" class="input_autodef" name="Header_and_attachments_filter" type="hidden" hidden value="<?= !empty($values["Header_and_attachments_filter"][0]) ? $values["Header_and_attachments_filter"][0] : '5' ?>">
                      </div>

                  </div>
                </div>
              </div>





              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#hostnamefilter">
                      <div class="section-header">Hostname filter  </div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                      Scores or blocks the message based on the sender hostname pattern: wifi, dial-up, dynamic etc.<br>
                      Protection against popular forged domains like (fake) yahoo.com, gmail.com, att.com etc.
                    </div>
                  </div>
                  <div class="row collapse" id="hostnamefilter">
                    
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <div class="range"></div>
                        <input placeholder="5.0" style="background-color: #292929; width: 1.8em" class="input_autodef" name="Hostname_filter" type="hidden" hidden value="<?= !empty($values["Hostname_filter"][0]) ? $values["Hostname_filter"][0] : '5' ?>">
                      </div>

                  </div>
                </div>
              </div>




              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#ipsecencr">
                      <div class="section-header">IPSec encryption   </div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                      <br>
                      Is an integral part of the security model of IPv6 and is the only solution for IP to IP encryption, at the present time.<br>
                      <br>
                      Benefits: <br>
                      <ul>
                        <li>Data integrity (checksum), data confidentiality (encryption), data origin authentication (spoofing protection).</li>
                        <li>Scrollout uses IPSec, with AES 256 bit algorithm and PFS, to encrypt TCP port 25 (default) or any custom TCP port. (on top of the existing SMTP with TLS)</li>
                      </ul>
                      <br>
                      Notes:<br>
                      <ul>
                        <li>Pros: offers a good protection against hackers attempting MITM and REPLAY attacks.</li>
                        <li>Pros: This is an IP to IP (HOST to HOST) transport encryption, not a VPN tunnel: communications between machines behind Scrollout gateways are not possible.</li>
                        <li>Pros: Clean configuration, with no predefined default keys that can be exploited and used as backdoors.</li>
                        <li>Pros: Compatible with Microsoft Windows IPSec.</li>
                      </ul>
                      <ul>
                        <li>Cons: Old (but not outdated), too complex and poorly documented to become widely adopted.</li>
                        <li>Cons: Is not considered a guaranteed/proof solution against ANY traffic interception method.</li>
                      </ul>
                      <br>

                      <b>IPSec Levels:</b><br>
                      <ul>
                        <li>Permissive (red): Weak, AES 128, SHA256, PFS &amp; DH modp2048 (Group 14). Suite A cryptography</li>
                        <li>Optimum (green): Medium, AES 256, SHA384, PFS &amp; DH modp3072 (Group 15). Suite B cryptography</li>
                        <li>Aggressive (green): Strong, AES 256, SHA512, PFS &amp; DH modp8192 (Group 18). Suite B cryptography</li>
                      </ul>
                      <br>  
                    </div>
                  </div>
                  <div class="row collapse" id="ipsecencr">
                    
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <div class="range"></div>
                        <input placeholder="5.0" style="background-color: #292929; width: 1.8em" class="input_autodef" name="IPSec_encryption" type="hidden" hidden value="<?= !empty($values["IPSec_encryption"][0]) ? $values["IPSec_encryption"][0] : '5' ?>">
                      </div>

                  </div>
                </div>
              </div>




              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#picfilter">
                      <div class="section-header">Picture filter  </div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                      Various tests for embedded pictures: text-ocr, picture size, picture link etc.
                    </div>
                  </div>
                  <div class="row collapse" id="picfilter">
                    
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <div class="range"></div>
                        <input placeholder="5.0" style="background-color: #292929; width: 1.8em" class="input_autodef" name="Picture_filter" type="hidden" hidden value="<?= !empty($values["Picture_filter"][0]) ? $values["Picture_filter"][0] : '5' ?>">
                      </div>

                  </div>
                </div>
              </div>




              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#ratelimitsin">
                      <div class="section-header">Rate limits in  </div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                      <b>Rate limits for incoming SMTP:</b><br>
                      The maximum number of:<br>
                      <ul>
                        <li>connections that an SMTP client may make simultaneously.</li>
                        <li>connections that an SMTP client may make in the time interval.</li>
                        <li>message delivery requests that an SMTP client may make in the time interval.</li>
                        <li>message delivery requests that an SMTP client may make in the time interval.</li>
                        <li>new TLS sessions (without using the TLS session cache) that an SMTP client may negotiate in the time interval.</li>
                      </ul>
                      <br>
                      When value is &lt;= 3 (not recommended), expensive tests (e.g.: greylist) are taking place requiring clients to disconnect before deliver the first message.
                    </div>
                  </div>
                  <div class="row collapse" id="ratelimitsin">
                    
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <div class="range"></div>
                        <input placeholder="5.0" style="background-color: #292929; width: 1.8em" class="input_autodef" name="Rate_limits_in" type="hidden" hidden value="<?= !empty($values["Rate_limits_in"][0]) ? $values["Rate_limits_in"][0] : '5' ?>">
                      </div>


                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                        Check Sender Name Servers (Names)
                        <h5>Active for Rate limits in: 1-5</h5>
                      </div>
                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <textarea class="form-control" placeholder="Name Servers reputation providers. Domain names only." rows="3" name="check_ns_names" id="check_ns_names"><?= !empty($values["check_ns_names"][0]) ? implode("\n",$values["check_ns_names"]) : '' ?></textarea>
                      </div>

                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                        Check Sender Name Servers (IPs)
                        <h5>Active for Rate limits in: 1-5</h5>
                      </div>
                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom:20px;">
                        <textarea class="form-control" placeholder="Name Servers reputation providers. IPs only." rows="3" name="check_ns_ips" id="check_ns_ips"><?= !empty($values["check_ns_ips"][0]) ? implode("\n",$values["check_ns_ips"]) : '' ?></textarea>
                      </div>


                  </div>
                </div>
              </div>





              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#ratelimitsout">
                      <div class="section-header">Rate limits out  </div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                      <b>Rate limits for outgoing SMTP:</b><br>
                      Limits the maximum number of:<br>
                      <li>simultaneous connections made to the same destination (domain) = value selected.</li>
                      <li>recipients per delivery = value selected x 10.</li>
                      <br>
                    </div>
                  </div>
                  <div class="row collapse" id="ratelimitsout">
                    
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <div class="range"></div>
                        <input placeholder="5.0" style="background-color: #292929; width: 1.8em" class="input_autodef" name="Rate_limits_out" type="hidden" hidden value="<?= !empty($values["Rate_limits_out"][0]) ? $values["Rate_limits_out"][0] : '5' ?>">
                      </div>

                  </div>
                </div>
              </div>



              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#spamtrapscore">
                      <div class="section-header">Spam trap score  </div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                      Spamassassin may learn depeding on the score of the trapped messages.   </span>
                    </div>
                  </div>
                  <div class="row collapse" id="spamtrapscore">
                    
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <div class="range"></div>
                        <input placeholder="5.0" style="background-color: #292929; width: 1.8em" class="input_autodef" name="Spam_trap_score" type="hidden" hidden value="<?= !empty($values["Spam_trap_score"][0]) ? $values["Spam_trap_score"][0] : '5' ?>">
                      </div>

                  </div>
                </div>
              </div>


              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#spamassassin">
                      <div class="section-header">Spamassassin  </div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                        Sets the level of Spamassassin. <br>
                        <br>
                        e.g.: <br>
                        <b>Level 4</b><br>
                        <ul>
                          <li>messages below score 4.5: pass</li>
                          <li>messages between score 4.5 and 6.5: pass with "Spam:" in the Subject</li>
                          <li>messages above score 6.5: quarantine, do not reach the recipient mailbox</li>
                        </ul>
                        <br>
                        <b>Level 5</b><br>
                        <ul>
                          <li>messages below score 5: pass</li>
                          <li>messages between score 5 and 7: pass with "Spam:" in the Subject</li>
                          <li>messages above score 7: quarantine, do not reach the recipient mailbox</li>
                        </ul>
                        <br>
                        <b>Level 6</b><br>
                        <ul>
                          <li>messages below score 5.5: pass</li>
                          <li>messages between score 5.5 and 7.5: pass with "Spam:" in the Subject</li>
                          <li>messages above score 7.5: quarantine, do not reach the recipient mailbox</li>
                        </ul>
                    </div>
                  </div>
                  <div class="row collapse" id="spamassassin">
                    
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <div class="range"></div>
                        <input placeholder="5.0" style="background-color: #292929; width: 1.8em" class="input_autodef" name="Spamassassin" type="hidden" hidden value="<?= !empty($values["Spamassassin"][0]) ? $values["Spamassassin"][0] : '5' ?>">
                      </div>

                  </div>
                </div>
              </div>


              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#urlfilter">
                      <div class="section-header">URL filter</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                        Scores the message based on the pattern of links:<br>
                        phishing, newsletters elements, unsubscribe elements, hash-keys, external content etc.
                    </div>
                  </div>
                  <div class="row collapse" id="urlfilter">
                    
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <div class="range"></div>
                        <input placeholder="5.0" style="background-color: #292929; width: 1.8em" class="input_autodef" name="URL_filter" type="hidden" hidden value="<?= !empty($values["URL_filter"][0]) ? $values["URL_filter"][0] : '5' ?>">
                      </div>

                  </div>
                </div>
              </div>




              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#webcache">
                      <div class="section-header">Web cache  </div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                      Aggressive or Permissive levels increase or decrease:<br>
                      <ul>
                        <li> cache size</li>
                        <li> cache retention</li>
                        <li> multiplication factor for original expiration</li>
                      </ul>
                      Note: https caching is not possible for public websites.<br>
                      Example: <br>
                      Web cache level 1 provides more recent/fresh data (&lt;=1 day): short period of retention<br>
                      Web cache level 5 provides older data (&lt;=5 days): long period of retention<br>
                      Web cache level 8 and 9 force retention for private data.<br>
                    </div>
                  </div>
                  <div class="row collapse" id="webcache">
                    
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <div class="range"></div>
                        <input placeholder="5.0" style="background-color: #292929; width: 1.8em" class="input_autodef" name="Web_cache" type="hidden" hidden value="<?= !empty($values["Web_cache"][0]) ? $values["Web_cache"][0] : '5' ?>">
                      </div>

                  </div>
                </div>
              </div>


              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                  <div class="button_save" id="button_save" onClick="submitSettingsLevels();">
                    Save
                  </div>
                  <div class="apply_loading" style="display: none;">
                    <i class="fa fa-circle-o-notch fa-spin fa-fw"></i>&nbsp;&nbsp;Saving and applying settings. Please wait...
                  </div>
                </div>
              </div>




          	</form>
        </div>



        <div class="col-lg-6 col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main" id="page-security-senders">
            <h1 class="page-header"><a href="#/security" class="hidden-lg hidden-md hidden-sm"><i class="fa fa-angle-left" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;</a>Senders</h1>
            <form name="setup" method="POST" action="connection_nxt.php#/senders" id="page_form_senders">


            <div class="row coloreveryothersecond">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="row">
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#whitelist_div">
                    <div class="section-header">Whitelist</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                  </div>
                </div>

                <div class="row">
                  <div class="col-lg-12 section-description">
                    Works differently than a Blacklist.<br/>
                    Works for SMTP <strong>Senders</strong> (in Return-Path) with SPF to avoid spoofed Senders addresses<br/>
                    Works for <strong>From:</strong> with DKIM to avoid spoofed addresses in From<br/>
                    Overrides SMTP rules, BANNED attachments and SPAM filter.<br/>
                    <br/>
                    Supported Formats: <br/>
                    <ul>sender@domain.com - the exact sender address.</ul>
                    <ul>@domain.com - any sender address @domain.com.</ul>
                    <ul>.domain.com - any sender address @any-sub.domain.com.</ul>
                    <ul>domain.com - any sender address @any-domain-that-ends-with-domain.com.</ul>
                    <br/>
                    Effect on SMTP rules (1st layer)<br/>
                    <ul>Connection filter <= 6: SPF authenticated Senders, found in the list, skip most SMTP rules.</ul>
                    <ul>Connection filter >= 7: Senders, found in the list, skip most SMTP rules.</ul>
                    <br/>
                    Effect on BANNED attachments (2nd layer)<br/>
                    <ul>DKIM authenticated Senders, found in the list, skip the attachment BAN.<br/>
                    Note: Some forged Senders might be rejected by SPF fail at SMTP (1st layer).</ul>
                    <br/>
                    Effect on SPAM filter (3rd layer)<br/>
                    <ul>SPF authenticated Senders, found in the list, skip the Spam filter.</ul>
                    <ul>DKIM authenticated Senders, found in the list, skip the Spam filter.</ul>
                    <br/>
                  </div>
                </div>
                <div class="row collapse" id="whitelist_div">
                 
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <h5>1st priority: allow</h5>
                      <textarea class="form-control" placeholder="Trusted sender@domain.com or @domain.com" rows="10" name="OK" id="OK"><?= implode("\n",$ok); ?></textarea> 
                      <br/>
                    </div>  
                </div> 
              </div>
            </div>


            <div class="row coloreveryothersecond mb20">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="row">
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#trusted_div">
                    <div class="section-header">Blind Whitelist</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-lg-12 section-description">
                    Works differently than a Blacklist.<br/>
                    Works for SMTP <strong>Senders</strong> (in Return-Path) with SPF to avoid spoofed Senders addresses<br/>
                    Works for <strong>From:</strong> with DKIM to avoid spoofed addresses in From<br/>
                    Overrides SMTP rules, BANNED attachments and SPAM filter.<br/>
                    <br/>
                    Supported Formats: <br/>
                    <ul>sender@domain.com - the exact sender address.</ul>
                    <ul>@domain.com - any sender address @domain.com.</ul>
                    <ul>.domain.com - any sender address @any-sub.domain.com.</ul>
                    <ul>domain.com - any sender address @any-domain-that-ends-with-domain.com.</ul>
                    <br/>
                    Effect on SMTP rules (1st layer)<br/>
                    <ul>Connection filter <= 6: SPF authenticated Senders, found in the list, skip most SMTP rules.</ul>
                    <ul>Connection filter >= 7: Senders, found in the list, skip most SMTP rules.</ul>
                    <br/>
                    Effect on BANNED attachments (2nd layer)<br/>
                    <ul>DKIM authenticated Senders, found in the list, skip the attachment BAN.<br/>
                    Note: Some forged Senders might be rejected by SPF fail at SMTP (1st layer).</ul>
                    <br/>
                    Effect on SPAM filter (3rd layer)<br/>
                    <ul>SPF authenticated Senders, found in the list, skip the Spam filter.</ul>
                    <ul>DKIM authenticated Senders, found in the list, skip the Spam filter.</ul>
                    <br/>
                  </div>
                </div>
                <div class="row collapse" id="trusted_div">
                 
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <h5>0 priority: block</h5>
                      <textarea class="form-control" placeholder="Fully trusted sender@domain.com or @domain.com" rows="10" name="trused_ta" id="trused_ta"></textarea> 
                      <br/>
                    </div>              
                </div>                 
              </div>
            </div>

            <div class="row coloreveryothersecond">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="row">
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#blacklist_div">
                    <div class="section-header">Blacklist</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-lg-12 section-description">
                    Works for any SMTP <b>Senders</b> (in Return-Path)<br/>
                    Works for any <b>From:</b><br/>
                    Overrides most rules and filters.<br/>
                    <br/>
                    Supported Formats: <br/>
                    <ul>sender@domain.com - the exact sender address.</ul>
                    <ul>@domain.com - any sender address @domain.com.</ul>
                    <ul>.domain.com - any sender address @any-sub.domain.com.</ul>
                    <ul>domain.com - any sender address @any-domain-that-ends-with-domain.com.</ul>
                    <br/>
                    Effect on SMTP rules (1st layer)<br/>
                    <ul>Rejects the sender at SMTP.</ul>
                    <br/>
                    Effect on SPAM filter (3rd layer)<br/>
                    <ul>Any Sender address found in the list is marked as SPAM.</ul>
                    <ul>Any From address found in the list is marked as SPAM.</ul>
                    <br/>
                  </div>
                </div>
                <div class="row collapse" id="blacklist_div">
                 
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <h5>2nd priority: block</h5>
                      <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="xMESSAGEx" id="xMESSSAGEx"><?= implode("\n",$not_ok); ?></textarea> 
                      <br/>
                    </div>              
                </div>                 
              </div>
            </div>
            
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
              <div class="button_save" class="button_save" onClick="submitSettingsSenders();">
                Save
              </div>
              <div class="apply_loading" style="display: none;">
                <i class="fa fa-circle-o-notch fa-spin fa-fw"></i>&nbsp;&nbsp;Saving and applying settings. Please wait...
              </div>
            </div>
          </form>
        </div>


        <div class="col-lg-6 col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main" id="page-security-reputation">
            <h1 class="page-header"><a href="#/security" class="hidden-lg hidden-md hidden-sm"><i class="fa fa-angle-left" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;</a>Reputation</h1>
            <form name="setup" method="POST" action="connection_nxt.php#/reputation" id="reputation_form">
            	 <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row"> 
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#rep_div_0"> 
                        <div class="section-header">0</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>           
                  </div>
                  <div class="row collapse" id="rep_div_0">                   
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>IP addresses</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_0_1" id="rep_ta_0_1"><?= loadReputation(0, 1); ?></textarea> 
                            <br/>
                          </div> 
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Domain names</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_0_2" id="rep_ta_0_2"><?= loadReputation(0, 2); ?></textarea> 
                            <br/>
                          </div>
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Name servers</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_0_3" id="rep_ta_0_3"><?= loadReputation(0, 3); ?></textarea> 
                            <br/>
                          </div>              
               </div> 
                  </div>
                </div> <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row"> 
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#rep_div_10"> 
                        <div class="section-header">10</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>           
                  </div>
                  <div class="row collapse" id="rep_div_10">                    
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>IP addresses</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_10_1" id="rep_ta_10_1"><?= loadReputation(10, 1); ?></textarea> 
                            <br/>
                          </div> 
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Domain names</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_10_2" id="rep_ta_10_2"><?= loadReputation(10, 2); ?></textarea> 
                            <br/>
                          </div>
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Name servers</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_10_3" id="rep_ta_10_3"><?= loadReputation(10, 3); ?></textarea> 
                            <br/>
                          </div>              
               </div> 
                  </div>
                </div> <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row"> 
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#rep_div_20"> 
                        <div class="section-header">20</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>           
                  </div>
                  <div class="row collapse" id="rep_div_20">                    
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>IP addresses</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_20_1" id="rep_ta_20_1"><?= loadReputation(20, 1); ?></textarea> 
                            <br/>
                          </div> 
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Domain names</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_20_2" id="rep_ta_20_2"><?= loadReputation(20, 2); ?></textarea> 
                            <br/>
                          </div>
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Name servers</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_20_3" id="rep_ta_20_3"><?= loadReputation(20, 3); ?></textarea> 
                            <br/>
                          </div>              
               </div> 
                  </div>
                </div> <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row"> 
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#rep_div_30"> 
                        <div class="section-header">30</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>           
                  </div>
                  <div class="row collapse" id="rep_div_30">                    
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>IP addresses</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_30_1" id="rep_ta_30_1"><?= loadReputation(30, 1); ?></textarea> 
                            <br/>
                          </div> 
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Domain names</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_30_2" id="rep_ta_30_2"><?= loadReputation(30, 2); ?></textarea> 
                            <br/>
                          </div>
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Name servers</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_30_3" id="rep_ta_30_3"><?= loadReputation(30, 3); ?></textarea> 
                            <br/>
                          </div>              
               </div> 
                  </div>
                </div> <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row"> 
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#rep_div_40"> 
                        <div class="section-header">40</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>           
                  </div>
                  <div class="row collapse" id="rep_div_40">                    
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>IP addresses</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_40_1" id="rep_ta_40_1"><?= loadReputation(40, 1); ?></textarea> 
                            <br/>
                          </div> 
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Domain names</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_40_2" id="rep_ta_40_2"><?= loadReputation(40, 2); ?></textarea> 
                            <br/>
                          </div>
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Name servers</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_40_3" id="rep_ta_40_3"><?= loadReputation(40, 3); ?></textarea> 
                            <br/>
                          </div>              
               </div> 
                  </div>
                </div> <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row"> 
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#rep_div_50"> 
                        <div class="section-header">50</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>           
                  </div>
                  <div class="row collapse" id="rep_div_50">                    
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>IP addresses</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_50_1" id="rep_ta_50_1"><?= loadReputation(50, 1); ?></textarea> 
                            <br/>
                          </div> 
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Domain names</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_50_2" id="rep_ta_50_2"><?= loadReputation(50, 2); ?></textarea> 
                            <br/>
                          </div>
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Name servers</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_50_3" id="rep_ta_50_3"><?= loadReputation(50, 3); ?></textarea> 
                            <br/>
                          </div>              
               </div> 
                  </div>
                </div> <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row"> 
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#rep_div_60"> 
                        <div class="section-header">60</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>           
                  </div>
                  <div class="row collapse" id="rep_div_60">                    
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>IP addresses</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_60_1" id="rep_ta_60_1"><?= loadReputation(60, 1); ?></textarea> 
                            <br/>
                          </div> 
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Domain names</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_60_2" id="rep_ta_60_2"><?= loadReputation(60, 2); ?></textarea> 
                            <br/>
                          </div>
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Name servers</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_60_3" id="rep_ta_60_3"><?= loadReputation(60, 3); ?></textarea> 
                            <br/>
                          </div>              
               </div> 
                  </div>
                </div> <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row"> 
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#rep_div_70"> 
                        <div class="section-header">70</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>           
                  </div>
                  <div class="row collapse" id="rep_div_70">                    
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>IP addresses</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_70_1" id="rep_ta_70_1"><?= loadReputation(70, 1); ?></textarea> 
                            <br/>
                          </div> 
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Domain names</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_70_2" id="rep_ta_70_2"><?= loadReputation(70, 2); ?></textarea> 
                            <br/>
                          </div>
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Name servers</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_70_3" id="rep_ta_70_3"><?= loadReputation(70, 3); ?></textarea> 
                            <br/>
                          </div>              
               </div> 
                  </div>
                </div> <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row"> 
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#rep_div_80"> 
                        <div class="section-header">80</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>           
                  </div>
                  <div class="row collapse" id="rep_div_80">                    
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>IP addresses</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_80_1" id="rep_ta_80_1"><?= loadReputation(80, 1); ?></textarea> 
                            <br/>
                          </div> 
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Domain names</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_80_2" id="rep_ta_80_2"><?= loadReputation(80, 2); ?></textarea> 
                            <br/>
                          </div>
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Name servers</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_80_3" id="rep_ta_80_3"><?= loadReputation(80, 3); ?></textarea> 
                            <br/>
                          </div>              
               </div> 
                  </div>
                </div> <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row"> 
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#rep_div_90"> 
                        <div class="section-header">90</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>           
                  </div>
                  <div class="row collapse" id="rep_div_90">                    
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>IP addresses</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_90_1" id="rep_ta_90_1"><?= loadReputation(90, 1); ?></textarea> 
                            <br/>
                          </div> 
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Domain names</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_90_2" id="rep_ta_90_2"><?= loadReputation(90, 2); ?></textarea> 
                            <br/>
                          </div>
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Name servers</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_90_3" id="rep_ta_90_3"><?= loadReputation(90, 3); ?></textarea> 
                            <br/>
                          </div>              
               </div> 
                  </div>
                </div> <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row"> 
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#rep_div_100"> 
                        <div class="section-header">100</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>           
                  </div>
                  <div class="row collapse" id="rep_div_100">                   
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>IP addresses</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_100_1" id="rep_ta_100_1"><?= loadReputation(100, 1); ?></textarea> 
                            <br/>
                          </div> 
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Domain names</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_100_2" id="rep_ta_100_2"><?= loadReputation(100, 2); ?></textarea> 
                            <br/>
                          </div>
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <h5>Name servers</h5>
                            <p>Lorem ipsum dolor</p>
                            <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_100_3" id="rep_ta_100_3"><?= loadReputation(100, 3); ?></textarea> 
                            <br/>
                          </div>              
               </div> 
                  </div>
                </div> 
	              
            	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
	              <div class="button_save" class="button_save" onClick="submitReputationSettings();">
	                Save
	              </div>
	              <div class="apply_loading" style="display: none;">
	                <i class="fa fa-circle-o-notch fa-spin fa-fw"></i>&nbsp;&nbsp;Saving and applying settings. Please wait...
	              </div>
	            </div> 
            </form>
        </div>


        <div class="col-lg-6 col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main" id="page-security-countries">
            <h1 class="page-header"><a href="#/security" class="hidden-lg hidden-md hidden-sm"><i class="fa fa-angle-left" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;</a>Countries</h1>

            <div class="row">
              <form name="setup" method="POST" action="connection_nxt.php#/countries" id="page_form_countries">

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <b>Bussines area:</b> no score applied<br>
                  <b>Foreign area:</b><br>
                  - a score is applied based on the sender location, relay location and location of the links in the body, depending on the security level.<br>
                  <b>Out of area:</b> (aggressive)<br>
                  - same as Foreign area policy, but it tries to block the message, this time, using 2 Geo methods, one at the connection level, second in the message scoring process.
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top:20px;">

                  <div class="row">
                    <div style="margin-bottom:10px;">
                      <i class="fa fa-search" aria-hidden="true"></i> Search country:
                      <input type="text" class="form-control" onkeyup="searchCountries(this.value);">
                    </div>
                  </div>

                  <?php
                    foreach ($countries as $strCountry) {     $shortname = str_replace('_', ' ', $strCountry);

                      ?>
                      <div class="row coloreveryothersecond row_country <?= $strCountry; ?>" style="padding-top:25px;">
                        <div class="col-lg-8 country_name">
                          <?= $shortname; ?>
                        </div>
                        <div class="col-lg-2">
                          <div class="threestateswitch"></div>
                          <input class="input_countries" type="hidden" hidden name="<?= $strCountry;?>" value="<?= !empty($values_countries[$strCountry]) ? $values_countries[$strCountry] : '0' ?>">
                        </div>
                        <div class="col-lg-2">
                          <div class="country_rating country_name"></div>
                        </div>
                      </div>
                      <?
                    }
                  ?>


                </div>


              </form>
            </div>

            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
              <div class="button_save" class="button_save" onClick="submitSettingsCountries();">
                Save
              </div>
              <div class="apply_loading" style="display: none;">
                <i class="fa fa-circle-o-notch fa-spin fa-fw"></i>&nbsp;&nbsp;Saving and applying settings. Please wait...
              </div>
            </div>
        </div>



        <div class="col-lg-6 col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main" id="page-security-certificate">
            <h1 class="page-header"><a href="#/security" class="hidden-lg hidden-md hidden-sm"><i class="fa fa-angle-left" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;</a>Certificate</h1>

            <div class="row ">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
               
                <div class="row">
                  <div class="col-lg-12 section-description">
                    Valid From: <strong><?= !empty($validFrom) ? $validFrom : 'NA'  ?></strong> to <strong><?= !empty($validTo) ? $validTo : '' ?></strong><br/> Name: <?= !empty($name) ? $name : '' ?> 
                  </div>
                </div>
                <div class="row" id="cert_textarea">
                  <form name="setup" method="POST" action="connection_nxt.php#/certs" id="page_form_certificates">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <h5>Include -----BEGIN CERTIFICATE-----</h5>
                      <textarea class="form-control" placeholder="Open your trusted certificate file in a text editor. Copy & paste the CERTIFICATE part here." 
                      rows="10" name="cert" id="cert"><?= !empty($value_cert) ? $value_cert : '' ?></textarea>
                      <h5>Include -----END CERTIFICATE-----</h5>
                      <br/>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                      Private key:
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mb20">
                      <h5>Include -----BEGIN PRIVATE KEY-----</h5> 
                      <textarea class="form-control" placeholder="Open your trusted certificate or key file in a text editor. Copy & paste the PRIVATE KEY part here." rows="10" name="certkey" id="certkey"><?= !empty($value_key) ? $value_key : '' ?></textarea>
                      <h5>Include -----END PRIVATE KEY-----</h5>
                      <span id="error-certkey" style="color: rgb(4, 172, 236); font-size: 12px; display: none;"><br>WARNNING: Private key is encrypted with passphrase.<br>
                      Passphrase: <input placeholder="passphrase" name="passkey" type="password" id="passkey" value="" class="form-control">
                      </span>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mb20">
                      <div class="button_save" class="button_save" onClick="submitSettingsCerts();">
                        Save
                      </div>
                      <div class="apply_loading" style="display: none;">
                        <i class="fa fa-circle-o-notch fa-spin fa-fw"></i>&nbsp;&nbsp;Saving and applying settings. Please wait...
                      </div>
                    </div>
                  </form>
                </div>
                 
              </div>
            </div>
 

              

            
        </div>




        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main" id="page-security-password">
            <h1 class="page-header"><a href="#/security" class="hidden-lg hidden-md hidden-sm"><i class="fa fa-angle-left" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;</a>Password</h1>
            <form name="setup" method="POST" action="connection_nxt.php#/pwd" id="page_form_password">

              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                  Old password:
                </div>
                <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                  <input class="form-control" type="password" name="currpass" />
                </div>
              </div>

              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                  New password:
                </div>
                <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                  <input class="form-control" type="password" name="newpass" />
                </div>
              </div>

              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                  Confirm new password:
                </div>
                <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                  <input class="form-control" type="password" name="newpass2" />
                </div>
              </div>



              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                  <div class="button_save" id="button_save" onClick="submitSettingsPassword();">
                    Save
                  </div>
                  <div class="apply_loading" style="display: none;">
                    <i class="fa fa-circle-o-notch fa-spin fa-fw"></i>&nbsp;&nbsp;Saving and applying settings. Please wait...
                  </div>
                </div>
              </div>


            </form>
        </div>


      </div>
    </div>

<? echo $htmlFooter; ?>
