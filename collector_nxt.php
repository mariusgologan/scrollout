<?
define('SETUP_FILE', '/var/www/collector.cfg');


function file2array ()
{
  if (file_exists(SETUP_FILE))
  {
	$array = explode("\n", file_get_contents(SETUP_FILE));
	foreach ($array AS $count => $line)
	{
	  $position = strpos($line, '=');
	  $output[substr($line, 0, $position)] = substr(str_replace('"', '', $line), $position + 1, -1);
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

		function validatedns9(dns9) {
			dns9 = dns9.value;
		    var dns9Reg = /[\!\#\$\&\*\)\(\$]/;
		    if( dns9Reg.test( dns9 ) ) {
		    	$('#error-dns9').show();
		    	addError("pw-error");
		    } else {
		    	$('#error-dns9').hide();
	    		removeError("pw-error");

		    }
	    }

		function submitSettings(){
			$("#page_form").submit();
		}

		function validateMailbox(input){

			if(validateEmail(input.value)){
				$("#error-mailbox").hide();
				removeError("email-error");
			}
			else{
				$("#error-mailbox").show();
				addError("email-error");
		    }
			console.log(hasError);
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
                <h3><i class="fa fa-recycle" aria-hidden="true"></i>&nbsp;&nbsp;COLLECTOR</h3>
                <ul class="nav side-menu">
                  <li class="active">
                  	<a href="#/collect"><i class="fa fa-cog"></i>&nbsp;&nbsp;Configure</a>
                  </li>
                </ul>
              </div>
          
            </div>
        </div>
        <div class="col-lg-6 col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
          	<h1 class="page-header">Collector configuration</h1>
			<form name="setup" method="POST" action="collector_nxt.php" id="page_form"> 
				<div class="row coloreveryothersecond">
	                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	                  <div class="row">
	                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#mailbox_div">
	                      <div class="section-header">Mailbox</div><span class="fa fa-chevron-down float-right section-chevron"></span>
	                    </div>
	                  </div>
	                  <div class="row">
	                    <div class="col-lg-12 section-description">
	                       
	                      First, Scrollout F1 will quarantine junk emails to this address.<br />
	                      Second, It will connect via IMAP to this mailbox, fetch and learn:
	                      <li>Legit emails and whitelist sender domain from <b>Mailbox Legit Folder</b></li>
	                      <li>Spam emails and blacklist sender address from <b>Mailbox Spam Folder</b></li>
	                      <br />
	                      Create a mailbox on your real email server and input the address here.<br />
	                      i.e.: <b>spam.collector@mydomain.com</b>.<br />
	                      Connect or attach the collector mailbox to your MS Outlook client or other IMAP email client.<br />
	                      You may use a web-mail GUI or even an external mailbox on yahoo.com or gmail.com (not recommended).
	                    </div>
	                  </div>

	                  <div class="row collapse mb20" id="mailbox_div">
	                    
	                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
	                      
	                        <input class="form-control" onchange="validateMailbox(this);" placeholder="An email address for reports, collector and feeder" name="mailbox" type="text" id="mailbox" value="<?= !empty($values['mailbox']) ? $values['mailbox'] : ''?>" size="50" />
	                      </div>
	                      <div class="col-lg-8 col-md-12 col-sm-12 col-xs-12">
	                        <span id="error-mailbox" style="color: #f00; vertical-align: -webkit-baseline-middle; display: none;">Valid email address required: address@example.com</span>
	                      </div>
	                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding-top:10px;">
	                        <input class="js-switch" type="checkbox" name="reportc" value="1" id="reportc"<?= (isset($values['reportc']) && (1 == $values['reportc'])) ? ' checked' : '' ?> />
	                        Do not collect detailed reports 
	                      </div>
	                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding-top:10px;">
	                        <input class="js-switch" type="checkbox" name="spamc" value="1" id="spamc"<?= (isset($values['spamc']) && (1 == $values['spamc'])) ? ' checked' : '' ?> />
	                        Do not collect spam (erase spam emails) 
	                      </div>
	                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding-top:10px;">
	                        <input class="js-switch" type="checkbox" name="virusc" value="1" id="virusc"<?= (isset($values['virusc']) && (1 == $values['virusc'])) ? ' checked' : '' ?> />
	                        Do not collect virus (erase virus emails) 
	                      </div>
	                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding-top:10px;">
	                        <input class="js-switch" type="checkbox" name="banc" value="1" id="banc"<?= (isset($values['banc']) && (1 == $values['banc'])) ? ' checked' : '' ?> />
	                        Do not collect banned files (erase emails with banned files)
	                      </div>
                  		</div>
              		</div> 
				</div>


				<div class="row coloreveryothersecond">
	                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	                  <div class="row">
	                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#imap_div">
	                      <div class="section-header">IMAP Server</div><span class="fa fa-chevron-down float-right section-chevron"></span>
	                    </div>
	                  </div>
	                  <div class="row">
	                    <div class="col-lg-12 section-description">                      
	                      Input the address of your IMAP server here.<br />
	                      Scrollout F1 will use this address to conect and fetch emails from your collector mailbox, via IMAP or IMAPS service.<br />
	                      i.e.:<br />
	                      <li>192.168.1.234</li>
	                      <li>imap.mail.yahoo.com</li>
	                      <li>imap.gmail.com</li>
	                    </div>
	                  </div>
	                  <div class="row collapse mb20" id="imap_div">
	                    
	                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
	                      
	                         
	                          <label for="imapserver">Hostname or IP</label>
	                          <input class="form-control" placeholder="hostname or IP" name="imapserver" type="text" id="dns7" value="<?= !empty($values['imapserver']) ? $values['imapserver'] : ''?>" size="50" />
	                        
	                      </div> 
	                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding-top:10px;">
	                        <input class="js-switch" type="checkbox" name="ssl" value="1" id="ssl_0"<?= (isset($values['ssl']) && (1 == $values['ssl'])) ? ' checked' : '' ?> />
	                          Secure connection with IMAP server
	                      </div> 
	                  </div> 
	              </div> 
				</div>

				<div class="row coloreveryothersecond">
				                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				                  <div class="row">
				                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#auth_div">
				                      <div class="section-header">Authentication</div><span class="fa fa-chevron-down float-right section-chevron"></span>
				                    </div>
				                  </div>
				                  <div class="row">
				                    <div class="col-lg-12 section-description">                      
				                      Here are some possible Username formats:<br />
				                      <ul>
				                      <li> user.name@gmail.com</li> 
				                      <li> user.name@yahoo.com</li> 
				                      <li> user.name</li> </ul>
				                      <br />
				                      For MS Exchange:<br />
				                      <ul><li> win.domain\user.name</li> </ul>
				                    </div>
				                  </div>
				                  <div class="row collapse mb20" id="auth_div">
				                    
				                      
				                    <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
				                          <label for="username">IMAP User</label>
				                          <input class="form-control" placeholder="IMAP User" name="username" type="text" id="dns8" value="<?= !empty($values['username']) ? $values['username'] : ''?>" />
				                    </div>
				                     
				                    <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="padding-top:10px;">
				                        <label for="password">IMAP password</label>
				                        <input class="form-control" placeholder="IMAP password" name="password" type="password" id="dns9" onkeyup="validatedns9(this)" value="<?= !empty($values['password']) ? $values['password'] : ''?>" />
				                      </div> 
				                    <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
				                        <span id="error-dns9" style="color: #f00; vertical-align: -webkit-baseline-middle; display: none;">Password should not contain characters like !,&,#,*,(,),$</span>
				                    </div> 
				                  </div> 
				              </div> 
				</div>

				



				<div class="row coloreveryothersecond">
	                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	                  <div class="row">
	                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#legit_div">
	                      <div class="section-header">Legit folder</div><span class="fa fa-chevron-down float-right section-chevron"></span>
	                    </div>
	                  </div>
	                  <div class="row">
	                    <div class="col-lg-12 section-description">                      
	                      In the collector mailbox create a separed folder using your MS Outlook, webmail or other IMAP client.<br />
	                      e.g. "GOOD folder"<br />
	                      Place all legit (HAM) emails you want to allow in this folder (invoices, payments etc).<br />
	                      Scrollout F1 will connect via IMAP service to collector mailbox, fetch, learn and allow all emails from GOOD folder.<br />
	                      i.e.:<br />
	                      <li><b>GOOD</b></li>
	                      <li><b>Inbox.GOOD</b> in case is a subfolder under Inbox folder</li>
	                      <br />
	                      <b>Note: never use important folders like Inbox or Sent Items. <u>The messages older than 7 days are deleted</u>.</b> 
	                    </div>
	                  </div>
	                  <div class="row collapse mb20" id="legit_div">
	                    
	                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;"> 
	                          <label for="legitimatefolder">Legit folder</label>
	                          <input class="form-control" placeholder="A LEGIT folder in mailbox" name="legitimatefolder" type="text" id="dns10" value="<?= !empty($values['legitimatefolder']) ? $values['legitimatefolder'] : ''?>" /> 
	                      </div> 
	                      <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding-top:10px;">
	                        <input class="js-switch" type="checkbox" style="{background-color: #494949}" name="resend" value="1" id="resend_0"<?= (isset($values['resend']) && (1 == $values['resend'])) ? ' checked' : '' ?> />
	                        Send false positive messages to original recipients
	                      </div>                     
	                  </div> 
	                </div> 
	            </div>

				<div class="row coloreveryothersecond">
	                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	                  <div class="row">
	                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#spam_div">
	                      <div class="section-header">Spam Folder</div><span class="fa fa-chevron-down float-right section-chevron"></span>
	                    </div>
	                  </div>
	                  <div class="row">
	                    <div class="col-lg-12 section-description">                      
	                      In the collector mailbox create a separed folder using your MS Outlook, webmail or other IMAP client.<br />
	                      e.g. "BAD folder"<br />
	                      Place all junk emails you want to block in this folder.<br />
	                      Scrollout F1 will connect via IMAP service to collector mailbox, fetch, learn and block all emails from BAD folder.<br />
	                      i.e.:<br />
	                      <li><b>BAD</b></li>
	                      <li><b>Inbox.BAD</b> in case is a subfolder under Inbox folder</li>
	                      <li><b>Bulk Mail</b> in case you want to use "Spam folder" from your Yahoo account</li>
	                      <br />
	                      <b>Note: never use important folders like Inbox or Sent Items. <u>The messages older than 7 days are deleted</u>.</b>
	                    </div>
	                  </div>
	                  <div class="row collapse mb20" id="spam_div">
	                    
	                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;"> 
	                          <label for="legitimatefolder">SPAM folder</label>
	                          <input class="form-control" placeholder="A SPAM folder in mailbox" name="spamfolder" type="text" id="dns11" value="<?= !empty($values['spamfolder']) ? $values['spamfolder'] : ''?>" /> 
	                      </div>            
	                  </div> 
	                </div> 
              	</div>



				<div class="row coloreveryothersecond">
	                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	                  <div class="row">
	                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#reports_div">
	                      <div class="section-header">Report</div><span class="fa fa-chevron-down float-right section-chevron"></span>
	                    </div>
	                  </div>
	                  <div class="row">
	                    <div class="col-lg-12 section-description">                      
	                      When enabled, ham &amp; spam fingerprints are reported to ScrolloutF1.com from messages in Legit and Spam folders<br/> 
	                    </div>
	                  </div>
	                  <div class="row collapse mb20" id="reports_div">
	                    
	                      <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 20px;"> 
	                         <input class="js-switch" type="checkbox" name="rspam" value="1" id="rspam_0"<?= (isset($values['rspam']) && (1 == $values['rspam'])) ? ' checked' : '' ?> />
	                          Report fingerprints to ScrolloutF1.com
	                      </div>            
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