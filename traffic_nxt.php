<?
define('SETUP_FILE', '/var/www/traffic.cfg');
$keys = shell_exec('sudo /var/www/bin/dkim.sh showkeys');
$pwd = shell_exec('sudo /var/www/bin/pwd.sh create');
$jsAutosave = "";
                 
function file2array ()
{
  if (file_exists(SETUP_FILE))
  {
  $array = explode("\n", file_get_contents(SETUP_FILE));
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

/**
 *  Load data from file
 */
$values = file2array();
if(isset($_GET["add"]) && !empty($_GET["add"])){
  if(!in_array($_GET["add"], $values["domain"])){
    $values["domain"][] = $_GET["add"];
    $values["transport"][] = $_GET["dest"];
    $jsAutosave = '$( document ).ready(function() {submitSettings();});';
  }
}
// var_dump($values);
?>
<?php

$count = 0;
foreach ($values["domain"] as $strDomain) {
  $jsArrRangeParam1 .= (!empty($values["tag"][$count]) ? $values["tag"][$count] : '5') .",";
  $jsArrRangeParam2 .= (!empty($values["block"][$count]) ? $values["block"][$count] : '7') .",";
  $jsArrRangeParam3 .= (!empty($values["cutoff"][$count]) ? $values["cutoff"][$count] : '25') .",";
  $count++;
}

$jsArrRangeParam1 = rtrim($jsArrRangeParam1,",");
$jsArrRangeParam2 = rtrim($jsArrRangeParam2,",");
$jsArrRangeParam3 = rtrim($jsArrRangeParam3,",");
 
$htmlEndScripts = <<<HTML

<script type="text/javascript">
    $(document).ready(function() {
      $(function(){ 
        
        $("#error-certkey").hide();
        $("#certkey").on('keydown',function(){
         
        var Addresscertkey=$(this).val(); 
        
        validatecertkey(Addresscertkey);
        });
      });
      var rangeParam1 = [$jsArrRangeParam1];
      var rangeParam2 = [$jsArrRangeParam2];
      var rangeParam3 = [$jsArrRangeParam3];

      var ranges = document.querySelectorAll('.range');



      for(var j = 0; j<ranges.length; j++){

        // var range = ranges[j];
        console.log(j);
        ranges[j].style.height = '18px';
        ranges[j].style.margin = '0 auto 30px';

        var slider = noUiSlider.create(ranges[j], {
          id: j,
          start: [ rangeParam1[j], rangeParam2[j], rangeParam3[j] ], // 3 handles, starting at...
          connect: [true, true, true, true],
          // margin: 1, // Handles must be at least 300 apart
          // limit: 20, // ... but no more than 600
          // direction: 'rtl', // Put '0' at the bottom of the slider
          orientation: 'horizontal', // Orient the slider vertically
          behaviour: 'tap-drag', // Move handle on tap, bar is draggable
          step: .1,
          tooltips: true,
          format: wNumb({
            decimals: 1
          }),
          range: {
            // Starting at 500, step the value by 500,
            // until 4000 is reached. From there, step by 1000.
            'min': [ 0 ],
            '40%': [ 20, 0.1],
            '60%': [ 40, 5 ],
            '80%': [ 100, 10 ],
            'max': [ 500 ]
          }
        });
        var connect = ranges[j].querySelectorAll('.noUi-connect');
        var classes = ['c-1-color', 'c-2-color', 'c-3-color', 'c-4-color'];
        
        ranges[j].noUiSlider.on('set', function ( values, handle ) {
          
          var inputs_tag = document.querySelectorAll('.input_tag');
          var inputs_block = document.querySelectorAll('.input_block');
          var inputs_cutoff = document.querySelectorAll('.input_cutoff');

          if ( handle == 0 ) {
            inputs_tag[this.options.id].value = values[handle];
          }
          if ( handle == 1 ) {
            inputs_block[this.options.id].value = values[handle];
          }
          if ( handle == 2 ) {
            if(values[handle] == '500.0')
              values[handle] = 'false';
            inputs_cutoff[this.options.id].value = values[handle];
          }

        });




        for ( var i = 0; i < connect.length; i++ ) {
            connect[i].classList.add(classes[i]);
        }       
      }
    });



    function submitSettings(){
       $('input:checkbox:not(:checked)').each(function() {
                console.log($(this).attr('name'));
                $(this).before($('<input>')
                .attr('type', 'hidden')
                .attr('class', 'temporary-aux-field')
                .attr('name', $(this).attr('name')));
                // .val('off'));
        });  

      $("#page_form").submit();

      [].forEach.call(document.querySelectorAll('.temporary-aux-field'),function(e){
        e.parentNode.removeChild(e);
      });
    }

    function validatecertkey(certkey) {
      var certkeyReg = /ENCRYPTED/;
      if( certkeyReg.test( certkey ) ) {
        $("#error-certkey").show();
      } else {
        $("#error-certkey").hide();
      }
    }

    function addNewDomain(){
      if(!$.trim($("#new-domain").val()).length ||  !$.trim($("#new-destination").val()).length) {
      	$("#error_new_domain").removeClass("hidden"); 
      }else{
      	$("#error_new_domain").addClass("hidden"); 
      	window.location.href = "traffic_nxt.php?add="+$("#new-domain").val()+"&dest="+$("#new-destination").val();
      }	
      	
      

      // var md5NewDomain = CryptoJS.MD5($("#new-domain").val());
      // var newItem = document.createElement("li");
      // newItem.id = 'li-'+md5NewDomain;
      // newItem.innerHTML = '<a><i class="fa fa-globe"></i>&nbsp;&nbsp;&nbsp;&nbsp;'+$("#new-domain").val()+'<span class="fa fa-chevron-down float-right"></span></a>'+
      //                 '<ul class="nav child_menu">'+
      //                 '  <li><a href="#/traffic/'+md5NewDomain+'/general"><i class="fa fa-cog" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;&nbsp;Configure</a></li>'+
      //                 '  <li><a href="#/traffic/'+md5NewDomain+'/quarantine"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;&nbsp;Quarantine</a></li>'+
      //                 '  <li><a href="#/traffic/'+md5NewDomain+'/inbound"> <i class="fa fa-sign-in fa-fw"></i>&nbsp;&nbsp;&nbsp;&nbsp;Inbound</a></li>'+
      //                 '  <li><a href="#/traffic/'+md5NewDomain+'/outbound"><i class="fa fa-sign-out fa-fw"></i>&nbsp;&nbsp;&nbsp;&nbsp;Outbound</a></li>'+
      //                 '  <li><a href="#/traffic/'+md5NewDomain+'/remove" class="red"><i class="fa fa-trash-o fa-fw"></i>&nbsp;&nbsp;&nbsp;&nbsp;Remove domain</a></li>'+
      //                 '</ul>';

      // var list = document.getElementById("side-menu");
      // list.insertBefore(newItem, list.childNodes[list.childNodes.length-2]);

      // menuSetup();
      // $('#myModal').modal('toggle');
    }

    function searchDomains(text){
      $('.menu-domain').each(function() {
        var classList = $(this).attr('class').split(/\s+/);
        if (classList[1].indexOf(text) >= 0) {
            $(this).show();
        }
        else{
            $(this).hide();
        }
      });
    }

    $jsAutosave


</script>

HTML;

require_once("assets/_header.php");
require_once("assets/_footer.php");

echo $htmlHeader;
?>

<!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header" style="border-bottom: 1px solid rgb(117, 117, 117);">
          <div data-dismiss="modal" style="float: right; cursor:pointer;"><i class="fa fa-times" aria-hidden="true"></i></div>
          <h4 class="modal-title">New domain</h4>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
              Domain name*:
            </div>
            <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
              <input class="form-control" placeholder="newdomain.com" name="new-domain" type="text" id="new-domain" />
            </div>
          </div>
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
              Destination*:
            </div>
            <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
              <input class="form-control" required="required" placeholder="[mai.server.com] or [ip.add.re.ss]" name="new-destination" type="text" id="new-destination" />
            </div>
          </div>
          <div class="row hidden" id="error_new_domain">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px; color:red;">
              Please fill out both fields!
            </div> 
          </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid rgb(117, 117, 117);">
          <div class="button_save" onClick="addNewDomain();" style="width:150px;">Add domain</div>
        </div>
      </div>
      
    </div>
  </div>

    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
           <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
              
              <div class="menu_section">
                <h3><i class="fa fa-exchange" aria-hidden="true"></i>&nbsp;&nbsp;ROUTE</h3>
                <div style="margin-bottom:10px;">
                  <i class="fa fa-search" aria-hidden="true"></i> Search domain:
                  <input type="text" class="form-control" onkeyup="searchDomains(this.value);"/>
                </div>
                <ul class="nav side-menu" id="side-menu">
                  <li>
                    <a href="#/traffic/config"><i class="fa fa-cog"></i>&nbsp;&nbsp;&nbsp;&nbsp;Configure</a>
                  </li>


                  <?php
                  $isFirst = true;
                  foreach ($values["domain"] as $strDomain) {
                    $md5Domain = md5($strDomain);
                    ?>

                    <li id="li-<?php echo $md5Domain; ?>" class="menu-domain <?= $strDomain?>">
                      <a><i class="fa fa-globe"></i>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $strDomain; ?><span class="fa fa-chevron-down float-right"></span></a>
                      <ul class="nav child_menu">
                        <li><a href="#/traffic/<?php echo $md5Domain; ?>/general"><i class="fa fa-cog" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;&nbsp;Configure</a></li>
                        <li><a href="#/traffic/<?php echo $md5Domain; ?>/quarantine"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;&nbsp;Quarantine</a></li>
                        <li><a href="#/traffic/<?php echo $md5Domain; ?>/inbound"> <i class="fa fa-sign-in fa-fw"></i>&nbsp;&nbsp;&nbsp;&nbsp;Inbound</a></li>
                        <li><a href="#/traffic/<?php echo $md5Domain; ?>/outbound"><i class="fa fa-sign-out fa-fw"></i>&nbsp;&nbsp;&nbsp;&nbsp;Outbound</a></li>
                        <li><a href="#/traffic/<?php echo $md5Domain; ?>/certificates"><i class="fa fa-certificate fa-fw"></i>&nbsp;&nbsp;&nbsp;&nbsp;Certificates</a></li>

                        <?php if(!$isFirst){ ?>
                        <li><a href="#/traffic/<?php echo $md5Domain; ?>/remove" class="red"><i class="fa fa-trash-o fa-fw"></i>&nbsp;&nbsp;&nbsp;&nbsp;Remove domain</a></li>
                        <?php }else{$isFirst = false;}?>
                      </ul>
                    </li> 

                    <?php

                  }

                  ?>

                     
                  <li data-toggle="modal" data-target="#myModal">
                    <a><i class="fa fa-plus"></i>&nbsp;&nbsp;&nbsp;&nbsp;New domain name</a>
                  </li>       
                </ul>
                
              </div>
          
            </div>
        </div>


        <form name="setup" method="POST" action="traffic_nxt.php" id="page_form">


        <div class="col-lg-6 col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main default-view" id="page-traffic-config">
            <h1 class="page-header"><a href="#/traffic" class="hidden-lg hidden-md hidden-sm"><i class="fa fa-angle-left" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;</a>Route configuration</h1>
              

            <div class="row coloreveryothersecond">
              
               <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="row">
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#organization_div">
                    <div class="section-header">Organization</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                  </div> 
                </div>

                <div class="row">
                  <div class="col-lg-12 section-description">
                    <span>
                    <b>Organization name</b> is a part of the SMTP banner.<br><br>
                    Recommendation:<br>
                    Don't use special characters.<br><br>
                    SMTP banner example:<br>
                    220 Hostname.Domain.tld ESMTP <b>Organization or company name</b>
                    </span>
                  </div>
                </div>
                <div class="row collapse" id="organization_div">
                 
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                          Name:
                        </div>
                        <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
                          <input class="form-control"  placeholder="Company name" name="organization" type="text" id="organization" value="<?= !empty($values['organization']) ? $values['organization'] : ''?>" size="25" required />
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                          Web support:
                        </div>
                        <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
                          <input class="form-control" placeholder="http://www.domain.com" name="web" type="text" id="web" value="<?= !empty($values['web']) ? $values['web'] : ''?>" size="20" />
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                          Phone support:
                        </div>
                        <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
                          <input class="form-control" placeholder="+40.000.00.00" name="tel" type="text" id="web" value="<?= !empty($values['tel']) ? $values['tel'] : ''?>" size="15" />
                          <br/>
                        </div>
                      </div>
                    </div>  
                </div> 
              </div> 


            </div> <!--row-->
 
            <div class="row coloreveryothersecond">
              
               <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="row">
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#isp_div">
                    <div class="section-header">ISP or Smarthost SMTP:</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                  </div> 
                </div>

                <div class="row">
                  <div class="col-lg-12 section-description">
                    <span>
                    <b>Optional: </b>Input an ISP <b>hostname</b> or <b>[ip.add.re.ss]</b> where you want to forward all<br/>
                    your outgoing messages sent from your network to Internet.
                    </span>
                  </div>
                </div>
                <div class="row collapse" id="isp_div">
                 
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="font-size:18px;">
                          Host or IP address:
                        </div>
                        <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
                          <input class="form-control" placeholder="Optional: hostrelay.domain.com:port or [ip.add.re.ss]:port" name="hostrelay" type="text" id="hostrelay" value="<?= !empty($values['hostrelay']) ? $values['hostrelay'] : ''?>" size="50" />
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                          Username:
                        </div>
                        <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
                          <input class="form-control" placeholder="User" name="uhostrelay" type="text" id="uhostrelay" value="<?= !empty($values['uhostrelay']) ? $values['uhostrelay'] : ''?>" size="15" />
                        </div>
                      </div>
                      
                      <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                          Password:
                        </div>
                        <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
                          <input class="form-control" placeholder="Password" name="phostrelay" type="password" id="phostrelay" value="<?= !empty($values['phostrelay']) ? $values['phostrelay'] : ''?>" size="15"  />
                          <br/>
                        </div>
                      </div>
                    </div>  
                </div> 
              </div> 


            </div>  
             <!--row-->


            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                <div class="button_save" class="button_save" onClick="submitSettings();">
                  Save
                </div>
                <div class="apply_loading" style="display: none;">
                  <i class="fa fa-circle-o-notch fa-spin fa-fw"></i>&nbsp;&nbsp;Saving and applying settings. Please wait...
                </div>
              </div>
            </div>
            
        </div>

        <?php
        $count = 0;
        foreach ($values["domain"] as $strDomain) {
          $md5Domain = md5($strDomain);

          ?>

          <div class="col-lg-6 col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main" id="page-traffic-domain-<?php echo $md5Domain; ?>-general">
              <h1 class="page-header"><a href="#/traffic" class="hidden-lg hidden-md hidden-sm"><i class="fa fa-angle-left" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;</a>General configuration for <strong><?php echo $strDomain;?></strong></h1>

              <div class="row">
              
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="font-size:25px;">
                      Domain names:
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin:20px 0;">
                      <span>
                        <b>Domain</b><br>
                        Input the domain name you want to protect in the first field<br>
                        In the second field input the real email server as a hostname, domain or the [ip.add.re.ss] <br>
                        Scrollout F1 will receive emails for the inputed domain, will scan the emails and forward them to the real email server.<br>
                        <br>
                        In this case: <br>
                        <li>your domain is: <?= $strDomain; ?></li>
                        <li>your email server is: <?= $values["transport"][$count]; ?></li>
                      </span> 
                    </div> 
                  </div>
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                      Domain:
                    </div>
                    <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
                      <input class="form-control" placeholder="example.com" size="30" type="text" name="domain[]" value="<?= !empty($strDomain) ? $strDomain : '' ?>"/>
                    </div>
                  </div>
                </div>


                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top:20px;">
                      <img src="assets/img/arrow_down.png" style="width: 75px;"/>
                    </div>
                  </div>	
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin:20px 0;">

                      <span>
                        <b>Destination can be:</b><br>
                        A domain: gmail.com<br>
                        A server name or IP: [server.domain.com] or [ip.add.re.ss]<br>
                        A server name or IP with custom port: [server.domain.com]:2525 or [ip.add.re.ss]:2525<br>
                        Multiple servers separated by commas: [server1.domain.com],[ip2.add.re.ss]<br>
                        <br>
                        Note:<br>
                        Value without brackets = MX lookup - used for domains.<br>
                        Value between [brackets] = no MX lookup - used for IPs and servers<br>
                        <br>
                      </span>
                    </div>
                  </div>

                  

                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                      Destination:
                    </div>
                    <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
                      <input class="form-control" placeholder="[mail.server.com] or [ip.add.re.ss]" size="55" type="text" name="transport[]" value="<?= !empty($values["transport"][$count]) ? $values["transport"][$count] : '' ?>" />
                    </div>
                  </div>
                </div>
              
              </div>

              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                  <div class="button_save" class="button_save" onClick="submitSettings();">
                    Save
                  </div>
                  <div class="apply_loading" style="display: none;">
                    <i class="fa fa-circle-o-notch fa-spin fa-fw"></i>&nbsp;&nbsp;Saving and applying settings. Please wait...
                  </div>
                </div>
              </div>

          </div>

          <div class="col-lg-6 col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main" id="page-traffic-domain-<?php echo $md5Domain; ?>-quarantine">
              <h1 class="page-header"><a href="#/traffic" class="hidden-lg hidden-md hidden-sm"><i class="fa fa-angle-left" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;</a>Quaratine for <strong><?php echo $strDomain;?></strong></h1>

              <div class="row">

                  <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
                    <div style="height:50px;"></div>
                    <div class="range"></div>

                    <input placeholder="5.0" style="background-color: #292929; width: 1.8em" class="input_tag" name="tag[]" id="<?php echo $md5Domain;?>_tag" type="hidden" hidden value="<?= !empty($values["tag"][$count]) ? $values["tag"][$count] : '' ?>">
                    <input placeholder="7.0" style="background-color: #292929; width: 1.8em" class="input_block" name="block[]" id="<?php echo $md5Domain;?>_block" type="hidden" hidden value="<?= !empty($values["block"][$count]) ? $values["block"][$count] : '' ?>">
                    <input placeholder="25.0" style="background-color: #292929; width: 1.8em" class="input_cutoff" name="cutoff[]" id="<?php echo $md5Domain;?>_cutoff" type="hidden" hidden value="<?= !empty($values["cutoff"][$count]) ? $values["cutoff"][$count] : '' ?>">

                  </div>

              </div>                

              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                  TAG as:
                </div>
                <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                  <input class="form-control" placeholder="Spam" name="sbj[]" size="2" type="text" id="dns11" value="<?= !empty($values['sbj'][$count]) ? $values['sbj'][$count] : ''?>" />
                </div>
              </div>

              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                  Quarantine to:
                </div>
                <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                  <input class="form-control" placeholder="e.g.: quarantine@<?= $strDomain ?>" size="30" type="text" name="quarantine[]" value="<?= !empty($quarantines[$count]) ? $quarantines[$count] : '' ?>" id="quarantines<?= $count + 1 ?>" />
                </div>
              </div>


              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding-top:10px;">
                  <input class="js-switch" type="checkbox" name="report[]" value="checked" id="report<?= $count + 1 ?>" <?= (!empty($values["report"][$count]) && ("checked" == $values["report"][$count])) ? $values["report"][$count] : '' ?> />
                  Do not send detailed report 
                </div>
              </div>

              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding-top:10px;">
                  <input class="js-switch" type="checkbox" name="spam[]" value="checked" id="spamall<?= $count + 1 ?>" <?= (!empty($values["spam"][$count]) && ("checked" == $values["spam"][$count])) ? $values["spam"][$count] : '' ?> />
                  Do not send spam to quarantine (erase spam emails)
                </div>
              </div>

              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding-top:10px;">
                  <input class="js-switch" type="checkbox" name="virus[]" value="checked" id="virusall<?= $count + 1 ?>" <?= (!empty($values["virus"][$count]) && ("checked" == $values["virus"][$count])) ? $values["virus"][$count] : '' ?> />
                  Do not send virus to quarantine (erase virus emails)
                </div>
              </div>

              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding-top:10px;">
                  <input class="js-switch" type="checkbox" name="ban[]" value="checked" id="banall<?= $count + 1 ?>" <?= (!empty($values["ban"][$count]) && ("checked" == $values["ban"][$count])) ? $values["ban"][$count] : '' ?> />
                  Do not send banned files to quarantine (erase emails with banned files)
                </div>
              </div>


              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                  <div class="button_save" class="button_save" onClick="submitSettings();">
                    Save
                  </div>
                  <div class="apply_loading" style="display: none;">
                    <i class="fa fa-circle-o-notch fa-spin fa-fw"></i>&nbsp;&nbsp;Saving and applying settings. Please wait...
                  </div>
                </div>
              </div>

          </div>

          <div class="col-lg-6 col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main" id="page-traffic-domain-<?php echo $md5Domain; ?>-inbound">
            <h1 class="page-header"><a href="#/traffic" class="hidden-lg hidden-md hidden-sm"><i class="fa fa-angle-left" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;</a>Inbound for <strong><?php echo $strDomain;?></strong></h1>

            <div class="row coloreveryother">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="row">
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#smtpauth-<?php echo $md5Domain; ?>">
                    <div class="section-header">SMTP Authentication</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                  </div>
                </div>
                <div id="smtpauth-<?php echo $md5Domain; ?>" class="collapse">
                <?= shell_exec('/var/www/bin/pwd_nxt.sh list'.' '.EscapeShellArg($strDomain)) ?>
                </div>
              </div>
            </div>

            <div class="row coloreveryother">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="row">
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#trustnetw-<?php echo $md5Domain; ?>">
                    <div class="section-header">Trusted networks</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-lg-12 section-description">
                    Input one or more subnets in CIDR format to allow relay and sign with DKIM.
                    <br>
                    <strong>Important:</strong> Do not trust your router/firewall by including its IP here.
                    By doing that, you will open a relay to Internet world.<br>
                    Examples:
                    <ul><li>72.150.150.1/32,72.1.1.0/24</li>
                    <li>!72.1.1.1/32,72.1.1.0/24 (allows any IP in 72.1.1.0/24, except 72.1.1.1)</li>
                    </ul>
                  </div>
                </div>
                <div class="row collapse" id="trustnetw-<?php echo $md5Domain; ?>">
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                    <input class="form-control" placeholder="e.g.: 172.16.0.0/12,10.0.0.0/8" size="70" type="text" name="mynets[]" value="<?= !empty($values["mynets"][$count]) ? $values["mynets"][$count] : '' ?>" />
                  </div>
                </div>
              </div>
            </div>

            <div class="row coloreveryother">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="row">
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#validaterecip-<?php echo $md5Domain; ?>">
                    <div class="section-header">Validate recipients</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-lg-12 section-description">
                    Validate recipients in this Active Directory, OpenLDAP, Zimbra or Lotus Domino
                  </div>
                </div>
                <div class="row collapse" id="validaterecip-<?php echo $md5Domain; ?>">
                  
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="font-size:18px;">Server:</div>
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                    <input class="form-control" placeholder="host.domain.com:port" size="15" name="lsrv[]"  id="lsrvs<?= $count + 1 ?>" type="text" value="<?= !empty($values["lsrv"][$count]) ? $values["lsrv"][$count] : '' ?>" />
                  </div>

                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="font-size:18px;">Forest Domain:</div>
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                    <input class="form-control" placeholder="domain.local" size="15" name="ldom[]"  id="ldoms<?= $count + 1 ?>" type="text" value="<?= !empty($values["ldom"][$count]) ? $values["ldom"][$count] : '' ?>" />
                  </div>

                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="font-size:18px;">User account:</div>
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                    <input class="form-control" placeholder="User, NOT Administrator" size="15" name="luser[]"  id="lusers<?= $count + 1 ?>" type="text" value="<?= !empty($values["luser"][$count]) ? $values["luser"][$count] : '' ?>" />
                  </div>

                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="font-size:18px;">Password:</div>
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="">
                    <input class="form-control" placeholder="password" size="15" name="lpass[]"  id="lpasses<?= $count + 1 ?>" type="password" value="<?= !empty($values["lpass"][$count]) ? $values["lpass"][$count] : '' ?>" />
                  </div>

                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin: 20px 0;">
                    <input class="js-switch" type="checkbox" name="ldap[]" value="checked" id="ldapall<?= $count + 1 ?>" <?= (!empty($values["ldap"][$count]) && ("checked" == $values["ldap"][$count])) ? $values["ldap"][$count] : '' ?> />
                    Secure connection with AD Server
                  </div>
                </div>
              </div>
            </div>

            <div class="row coloreveryother">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="row">
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#backupmails-<?php echo $md5Domain; ?>">
                    <div class="section-header">Backup incoming emails</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-lg-12 section-description">
                    Backup incoming emails sent TO <?= $strDomain; ?>
                  </div>
                </div>
                <div class="row collapse" id="backupmails-<?php echo $md5Domain; ?>">
                  
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="font-size:18px;">FROM: anywhere TO:</div>
                  <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 10px;">
                    <div class="input-group">
                      <input class="form-control" placeholder="anyone" style="text-align:right;" size="15" name="receivers[]"  id="receivers<?= $count + 1 ?>" type="text" value="<?= !empty($values["receivers"][$count]) ? $values["receivers"][$count] : '' ?>" />
                      <span class="input-group-addon" style="text-align: left;">@<?= $strDomain; ?></span>
                    </div>
                  </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom:10px;">
                      <img src="assets/img/arrow_down.png" style="width: 35px;"/>
                    </div>

                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="font-size:18px;">BCC:</div>
                  <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                      <input class="form-control" placeholder="mailbox@domain.com" size="15" name="receiversd[]"  id="receiversd<?= $count + 1 ?>" type="text" value="<?= !empty($values["receiversd"][$count]) ? $values["receiversd"][$count] : '' ?>" />
                      
                  </div>

                </div>
              </div>
            </div>



            <div class="row coloreveryother">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="row">
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#clonemails-<?php echo $md5Domain; ?>">
                    <div class="section-header">Clone incoming emails</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-lg-12 section-description">
                    <strong>Clone incoming emails for recipients@<?= $strDomain; ?> ⇒ recipients@(SUB.)DOMAIN(s)<br/></strong>
                    Messages received for recipient@<?= $strDomain; ?> can be delivered as duplicate/backup to:<br>
                    <ul><li>same-recipient@other-domain.com</li>
                    <li>same-recipient@other-sub.domain.com</li></ul>
                    <br>
                    CLONE is useful when migrating mailboxes from an old server to a new server may take days.<br>
                    Hence, incoming emails should be delivered to both servers during the transition period.<br>
                    <ul><li>Old server has @domain.com already set</li>
                    <li>Create @domain.com on the new server (as primary, default domain)</li>
                    <li>Create alias domain @new.domain.com on the new server</li>
                    <li>Create sub-zone new.domain.com on the DNS server</li>
                    <li>Create MX record for new.domain.com sub-zone pointing to the new server</li>
                    <li>Add @new.domain.com in the field below</li></ul>
                    <br>
                    Expected result:<br>
                    Any email to @domain.com (on the old server) will be cloned to @new.domain.com (new server) as well.<br>
                    <br>
                  </div>
                </div>
                <div class="row collapse" id="clonemails-<?php echo $md5Domain; ?>">
                    <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                    <div class="input-group">
                      <span class="input-group-addon" style="text-align: left;">RECIPIENTS@</span>
                      <input class="form-control" placeholder="(sub.)domain1.com,(sub.)domain2.com" size="70" type="text" name="clones[]" value="<?= !empty($values["clones"][$count]) ? $values["clones"][$count] : '' ?>" id="clones<?= $count + 1 ?>" />
                    </div>
                  </div>


                </div>
              </div>
            </div>



            <div class="row coloreveryother">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="row">
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#redirectemails-<?php echo $md5Domain; ?>">
                    <div class="section-header">Redirect all emails</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-lg-12 section-description">
                    <strong>Redirect all emails for recipients@<?= $strDomain; ?> ⇒ mailbox@domain.com:<br/></strong>
                    Messages received for recipient@<?= $strDomain; ?> can be delivered to:<br>
                    <ul>user@other-domain.com</ul>
                    <br>
                    REDIRECT is useful when all mails sent to a domain have to be forwared to one single user in another domain.<br>
                    Expected result:<br>
                    Any email to @domain.com will be sent to user@other-domain.com. <br>
                  </div>
                </div>
                <div class="row collapse" id="redirectemails-<?php echo $md5Domain; ?>">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="font-size:18px;">RECIPIENTS@<?= $strDomain; ?> ⇒ TO:</div>
                    <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                      <input class="form-control"  placeholder="mailbox@domain.com" size="20" type="text" name="redirect[]" value="<?= !empty($values["redirect"][$count]) ? $values["redirect"][$count] : '' ?>" id="redirect<?= $count + 1 ?>" />
                    </div>
                </div>
              </div>
            </div>

            <div class="row coloreveryother">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div class="row">
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#spamtraps-<?php echo $md5Domain; ?>">
                    <div class="section-header">Spam traps</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                  </div>
                </div>
                <div class="row">
                  <div class="col-lg-12 section-description">
                    <strong>Declare old removed addresses (known by spammers) as spam traps (separate by commas ",")</strong>
                    <br>
                    Any message received for these address(es) is considered 100% spam and will be learned and quarantined.<br>
                    Hide these email addresses in the source of your web site or copy the div id "contacts" from the source of welcome page.<br>
                    These addresses are considered as non-existent.<br>
                    Do not add the domain name @my-domain.com.<br>
                    e.g.: for <b>my-trap1@my-domain.com, my-trap2@my-domain.com</b> input <b>my-trap1, my-trap2</b> only.<br>

                  </div>
                </div>
                <div class="row collapse" id="spamtraps-<?php echo $md5Domain; ?>">
                  <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                    <div class="input-group">
                      <input class="form-control" placeholder="former-employee1, former-employee2" size="70" type="text" name="spamtraps[]" value="<?= !empty($values["spamtraps"][$count]) ? $values["spamtraps"][$count] : '' ?>" id="spamtraps<?= $count + 1 ?>" />
                      <span class="input-group-addon" style="text-align: left;">@<?= $strDomain; ?></span>
                    </div>
                  </div>


                </div>
              </div>
            </div>


            <div class="row">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                <div class="button_save" class="button_save" onClick="submitSettings();">
                  Save
                </div>
                <div class="apply_loading" style="display: none;">
                  <i class="fa fa-circle-o-notch fa-spin fa-fw"></i>&nbsp;&nbsp;Saving and applying settings. Please wait...
                </div>
              </div>
            </div>

          </div>


          <div class="col-lg-6 col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main" id="page-traffic-domain-<?php echo $md5Domain; ?>-outbound">
              <h1 class="page-header"><a href="#/traffic" class="hidden-lg hidden-md hidden-sm"><i class="fa fa-angle-left" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;</a>Outbound for <strong><?php echo $strDomain;?></strong></h1>

              <div class="row">
                  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin: 20px 0;">
                    <input class="js-switch" type="checkbox" name="encrypt[]" value="checked" id="encrypt<?= $count + 1 ?>" <?= (!empty($values["encrypt"][$count]) && ("checked" == $values["encrypt"][$count])) ? $values["encrypt"][$count] : '' ?> />
                    Enforce encryption with downstream server(s). Do not use unecrypted connections.
                  </div> 
              </div>


              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#managebounces-<?php echo $md5Domain; ?>">
                      <div class="section-header">Manage bounces</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                      Manage bounces caused by downstream server(s) <?= $values["transport"][$count]?>
                    </div>
                  </div>
                  <div class="row collapse" id="managebounces-<?php echo $md5Domain; ?>">
                    

                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin: 20px 0;">
                      <input class="js-switch" type="checkbox" name="spamd[]" value="checked" id="spamdall<?= $count + 1 ?>" <?= (!empty($values["spamd"][$count]) && ("checked" == $values["spamd"][$count])) ? $values["spamd"][$count] : '' ?> />
                      Downstream server(s) runs an accurate spam detection. Learn from rejected spam instead of bouncing 
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                      <input class="js-switch" type="checkbox" name="bounce[]" value="checked" id="bounceall<?= $count + 1 ?>" <?= (!empty($values["bounce"][$count]) && ("checked" == $values["bounce"][$count])) ? $values["bounce"][$count] : '' ?> />
                      Reduce other bounces caused by downstream server(s)
                    </div>
                  </div>
                </div>
              </div>


              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#protectbrand-<?php echo $md5Domain; ?>">
                      <div class="section-header">Protect the brand</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                      Protect the brand by certifying nxthost.net domain and its IP addresses with SPF, DKIM & DMARC DNS records
                    </div>
                  </div>
                  <div class="row collapse" id="protectbrand-<?php echo $md5Domain; ?>">
                    <?= !empty($domains[$count]) ? '
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="font-size:18px;"><?= $strDomain; ?>. 3600 TXT</div>
                    <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                      <input class="form-control" disabled="disabled" type="text" value="v=spf1 a mx -all" />
                    </div>':''; ?>

                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin: 20px 0;">
                      <input class="js-switch" type="checkbox" name="srs[]" value="checked" id="srs<?= $count + 1 ?>" <?= (!empty($values["srs"][$count]) && ("checked" == $values["srs"][$count])) ? $values["srs"][$count] : '' ?> />
                      Disable SRS for messages sent FROM <?= $strDomain; ?>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin: 20px 0;">
                      <input class="js-switch" type="checkbox" name="srsto[]" value="checked" id="srsto<?= $count + 1 ?>" <?= (!empty($values["srsto"][$count]) && ("checked" == $values["srsto"][$count])) ? $values["srsto"][$count] : '' ?> />
                      Disable SRS for messages sent TO <?= $strDomain; ?>
                    </div>
                    
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="font-size:18px;">DKIM signature</div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <?= shell_exec('sudo /var/www/bin/dkim_nxt.sh'.' '.EscapeShellArg($keys).' '.EscapeShellArg($strDomain)); ?>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin: 20px 0;">
                      <input class="js-switch" type="checkbox" name="d_dkim[]" value="checked" id="d_dkim<?= $count + 1 ?>" <?= (!empty($values["d_dkim"][$count]) && ("checked" == $values["d_dkim"][$count])) ? $values["d_dkim"][$count] : '' ?> />
                      Disable DKIM for <?= $strDomain; ?>
                    </div>

                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="font-size:18px;">_dmarc.<?= $strDomain; ?>. 3600 TXT:</div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                      <input class="form-control"  size="100" disabled="disabled" type="text" value="v=DMARC1; p=quarantine; rua=mailto:abuse@<?= $strDomain; ?>"  />
                    </div>
                  </div>
                </div>
              </div>


              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#differentoutboundip-<?php echo $md5Domain; ?>">
                      <div class="section-header">Different local IP for outbound</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                      <b>Assign a different local IP or IP Pool for outbound.</b><br>
                      <i>The IP address must be directly connected to Internet. (not behind a NAT)</i><br>
                      <i>The IP address must have a valid reverse DNS (PTR) records.</i><br>
                      <br>
                      Assigning an outbound IP address to a Sender Domain may:<br>
                      Prevent default IP from losing reputation when this Sender Domain is not trusted.
                      Increase delivery/quality by associating an IP with good reputation to this Sender Domain.
                      Increase limits/time by associating an IP with good throughout to this Sender Domain.
                      Build reputation for a new IP using a Sender Domain with normal transactions.
                      Isolate this domain from being associated with others.
                        
                    </div>
                  </div>
                  <div class="row collapse" id="differentoutboundip-<?php echo $md5Domain; ?>">
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <input class="form-control"  placeholder="e.g.: 11.11.11.11, 22.22.22.22" size="75" type="text" name="assips[]" value="<?= !empty($values["assips"][$count]) ? $values["assips"][$count] : '' ?>" id="assips<?= $count + 1 ?>" />
                      </div>
                  </div>
                </div>
              </div>




              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#forwardtosmarthost-<?php echo $md5Domain; ?>">
                      <div class="section-header">Forward to smart host</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                        <b>Forward mail from @<?= $strDomain ?> ⇒ to smart host</b><br>
                        <b>Optional: </b>Input an ISP <b>hostname</b> or <b>[ip.add.re.ss]</b> where you want to forward all your outgoing messages sent from <b>this domain</b> <?= $strDomain ?>      
                        
                    </div>
                  </div>
                  <div class="row collapse" id="forwardtosmarthost-<?php echo $md5Domain; ?>">

                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="font-size:18px;">Relay server:</div>
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <input class="form-control"  placeholder="relay.server.com or [ip.add.re.ss]:port" size="35" type="text" name="hrelays[]" value="<?= !empty($values["hrelays"][$count]) ? $values["hrelays"][$count] : '' ?>" id="hrelays<?= $count + 1 ?>" />

                      </div>
                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="font-size:18px;">User:</div>
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <input class="form-control"  placeholder="User" size="15" type="text" name="urelays[]" value="<?= !empty($values["urelays"][$count]) ? $values["urelays"][$count] : '' ?>" id="urelays<?= $count + 1 ?>" />

                      </div>
                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="font-size:18px;">Password:</div>
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <input class="form-control"  placeholder="Password" size="15" type="password" name="prelays[]" value="<?= !empty($values["prelays"][$count]) ? $values["prelays"][$count] : '' ?>" id="prelays<?= $count + 1 ?>" />
                      </div>
                  </div>
                </div>
              </div>



              <div class="row coloreveryothersecond">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                  <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#backupoutgoingmails-<?php echo $md5Domain; ?>">
                      <div class="section-header">Backup outgoing emails</div><span class="fa fa-chevron-down float-right section-chevron"></span>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-lg-12 section-description">
                        <b>Backup outgoing emails sent FROM <?= $strDomain; ?></b><br>
                        Outgoing messages from <?= $strDomain; ?> can be BCC-ed to a mailbox@domain.com as follows:<br>
                        <ul>From a specific sender: From Sender@<?= $strDomain; ?> to ANYWHERE</ul>
                        <ul>From any sender: From @<?= $strDomain; ?> to ANYWHERE</ul>
                    </div>
                  </div>
                  <div class="row collapse" id="backupoutgoingmails-<?php echo $md5Domain; ?>">

                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="font-size:18px;">FROM:</div>
                      <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <div class="input-group">
                          <input class="form-control" placeholder="anyone" size="10" type="text" name="senders[]" value="<?= !empty($values["senders"][$count]) ? $values["senders"][$count] : '' ?>" id="senders<?= $count + 1 ?>" style="text-align: right;"/>
                          <span class="input-group-addon" style="text-align: left;">@<?= $strDomain; ?></span>
                        </div>
                      </div>
                      
                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="font-size:18px;">TO: anywhere ⇒ BCC: :</div>
                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                        <input class="form-control" placeholder="mailbox@domain.com" size="20" type="text" name="sendersd[]" value="<?= !empty($values["sendersd"][$count]) ? $values["sendersd"][$count] : '' ?>" id="sendersd<?= $count + 1 ?>" />
                      </div>
                  </div>
                </div>
              </div>



              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                  <div class="button_save" class="button_save" onClick="submitSettings();">
                    Save
                  </div>
                  <div class="apply_loading" style="display: none;">
                    <i class="fa fa-circle-o-notch fa-spin fa-fw"></i>&nbsp;&nbsp;Saving and applying settings. Please wait...
                  </div>
                </div>
              </div>

          </div>

         <div class="col-lg-6 col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main" id="page-traffic-domain-<?php echo $md5Domain; ?>-certificates"">
            <h1 class="page-header"><a href="#/traffic" class="hidden-lg hidden-md hidden-sm"><i class="fa fa-angle-left" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;</a>Certificates for <strong><?php echo $strDomain;?></strong></h1>

            <div class="row ">
              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
               
                <div class="row">
                  <div class="col-lg-12 section-description">
                    Valid From: <strong><?= !empty($validFrom) ? $validFrom : 'NA'  ?></strong> to <strong><?= !empty($validTo) ? $validTo : '' ?></strong><br/> Name: <?= !empty($name) ? $name : '' ?> 
                  </div>
                </div>
                <div class="row" id="cert_textarea">
                   
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                      <h5>Include -----BEGIN CERTIFICATE-----</h5>
                      <textarea class="form-control" placeholder="Open your trusted certificate file in a text editor. Copy & paste the CERTIFICATE part here." 
                      rows="10" name="cert[]" id="cert"><?= !empty($value_cert) ? $value_cert : '' ?></textarea>
                      <h5>Include -----END CERTIFICATE-----</h5>
                      <br/>
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                      Private key:
                    </div>
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mb20">
                      <h5>Include -----BEGIN PRIVATE KEY-----</h5> 
                      <textarea class="form-control" placeholder="Open your trusted certificate or key file in a text editor. Copy & paste the PRIVATE KEY part here." rows="10" name="certkey[]" id="certkey"><?= !empty($value_key) ? $value_key : '' ?></textarea>
                      <h5>Include -----END PRIVATE KEY-----</h5>
                      <span id="error-certkey" style="color: rgb(4, 172, 236); font-size: 12px; display: none;"><br>WARNNING: Private key is encrypted with passphrase.<br>
                      Passphrase: <input placeholder="passphrase" name="passkey[]" type="password" id="passkey" value="" class="form-control">
                      </span>
                    </div> 
                   
                </div>
                 
              </div>
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
                  <div class="button_save" class="button_save" onClick="submitSettings();">
                    Save
                  </div>
                  <div class="apply_loading" style="display: none;">
                    <i class="fa fa-circle-o-notch fa-spin fa-fw"></i>&nbsp;&nbsp;Saving and applying settings. Please wait...
                  </div>
                </div>
              </div>
            </div> 

        <?$count++;}//foreach end?>
        </form>

      </div>
    </div>

<? echo $htmlFooter; ?>