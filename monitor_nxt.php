<?
define('SETUP_FILE', '/var/www/filter.cfg');


function file2array ()
{
  if (file_exists(SETUP_FILE))
  {
	$array = explode("\n", file_get_contents(SETUP_FILE));
	foreach ($array AS $count => $line)
	{
	  $position = strpos($line, '=');
	  $phar = array('"','"');
	  $output[substr($line, 0, $position)] = substr(str_replace($phar, '', $line), $position + 1, -1);
	  unset($position);
	}

#	return str_replace('_', ' ', $output);
	return $output;
  }
}

/**
*	Load data from file
*/
$values = file2array();



?>




<?php

$htmlEndScripts = <<<HTML

	<script type="text/javascript">
 
  
		var lastpos = 0;
		$(document).ready(function() {

			$("#responsecontainer").load("logsreload.html", function(){
				$("#responsecontainer").animate({
				  scrollTop: $('#responsecontainer')[0].scrollHeight - $('#responsecontainer')[0].clientHeight
				}, 500);
			});
			
			var refreshId = setInterval(function() {
				//lastpos = $('#responsecontainer .logs').scrollBottom();
				 
				//var test =  $("#responsecontainer").scrollTop() < $('#responsecontainer')[0].scrollHeight - $('#responsecontainer')[0].clientHeight;
				if($("#responsecontainer").scrollTop() == $('#responsecontainer')[0].scrollHeight - $('#responsecontainer')[0].clientHeight){
					$("#responsecontainer").load('logsreload.html?randval='+ Math.random(), function(){
					//if(!test){
						$("#responsecontainer").animate({
						  scrollTop: $('#responsecontainer')[0].scrollHeight - $('#responsecontainer')[0].clientHeight
						}, 500);
					//} 
				});
				}
				
			}, 10000);

			$("button.floating-action-button").on("click", function(){
				$("#responsecontainer").load("logsreload.html", function(){
					$("#responsecontainer").animate({
					  scrollTop: $('#responsecontainer')[0].scrollHeight - $('#responsecontainer')[0].clientHeight
					}, 500);
				});
			});	

			$("#responsecontainer").scroll(function(){
				if($("#responsecontainer").scrollTop() == $('#responsecontainer')[0].scrollHeight - $('#responsecontainer')[0].clientHeight){
					/*$("#responsecontainer").load("logsreload.html", function(){
						$("#responsecontainer").animate({
						  scrollTop: $('#responsecontainer')[0].scrollHeight - $('#responsecontainer')[0].clientHeight
						}, 500);
					});*/
					$("button.floating-action-button").hide();
				}else{
					$("button.floating-action-button").show();
				}

			});

		});


		function submitSettings(){
			$("#page_form").submit();
		}
		function clearFilter(){
			$("#search").val("");
		}
		setTimeout(function(){
		   refresh_graphs();
		}, 300000);


		function refresh_graphs(){
		 	$.get('api/api_graphs.php',function(result){
		   		$("#div_graphs").html(result);
		   	});
		}

		refresh_graphs();

		function clearLog(){
			$.get("api/api_logs_clear.php", function(){
				new PNotify({
	                title: 'Succes',
	                text: 'Log cleared successfuly',
	                type: 'success',
	                styling: 'bootstrap3'
	            });
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
	            <h3><i class="fa fa-eye" aria-hidden="true"></i>&nbsp;&nbsp;MONITOR</h3>
	            <ul class="nav side-menu">
	            	<li>
	              		<a href="#/monitor/logs"><i class="fa fa-book"></i>&nbsp;&nbsp;&nbsp;&nbsp;Logs</a>
		            </li>
		            <li>
		              	<a href="#/monitor/graph"><i class="fa fa-bar-chart"></i>&nbsp;&nbsp;&nbsp;&nbsp;Graphs</a>
		            </li>
	            </ul>
	          </div>
	      
	        </div>
	    </div>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main default-view" id="page-monitor-logs">
	      	<h1 class="page-header"><a href="#/monitor" class="hidden-lg hidden-md hidden-sm"><i class="fa fa-angle-left" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;</a>Logs</h1>
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

					<form name="setup" action="monitor_nxt.php" method="POST" id="page_form">
						<div class="row">
			                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
			                  Search:
			                </div>
			                <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12" style="margin-bottom:10px;">
			                  <input class="form-control" placeholder="text1.*text2.*text3" size="50" name="search" type="text" id="search" value="<?= !empty($values['search']) ? $values['search'] : ''?>" />
			                </div>
			                <div class="col-lg-2  col-md-2 col-sm-2 col-xs-12 mb-sm-10">
				                <div class="button_save" id="button_save" onClick="submitSettings();" style="margin: -2px 0 0 0;">
				                    Filter
				                </div>
			                </div>
			                <div class="col-lg-2   col-md-2 col-sm-2 col-xs-12">
				                <div class="button_clear" id="button_clear_filter" onClick="clearFilter(); submitSettings();" style="margin: -2px 0 0 0;">
				                    Clear filter
				                </div>
			                </div>
			            </div>

			            <div class="row">
			                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
			                  <div id="apply_loading" style="display: none;">
			                    <i class="fa fa-circle-o-notch fa-spin fa-fw"></i>&nbsp;&nbsp;Filtering, please wait...
			                  </div>
			                </div>
			            </div>

			            <div class="row">
			                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
			                  	<a href="mail.log.1" download="mail.log.1" class="no-deco">
				                  	<div class="button_dl">
					                    <i class="fa fa-download" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;Last week
					                </div>
				                </a>
				            	<a href="mail.log" download="mail.log" class="no-deco">
					                <div class="button_dl" onClick="submitSettings();">
					                    <i class="fa fa-download" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;This week
					                </div>
					            </a>
			                </div>
			            </div>
					</form>

					<div class="row">
						<div style="text-align: center;">
						<button type="button" class="btn btn-info btn-fab btn-raised floating-action-button"><i class="fa fa-refresh"></i>
  						</button></div>
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="responsecontainer" style="margin-top:30px;height: 50vh;overflow-y: scroll; width:100%;">

						</div>
					</div>

					<div class="row">
		                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="margin-top: 20px;font-size:18px;">
		                  	<div class="button_dl" onclick="clearLog();">
			                    <i class="fa fa-trash" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;Clear log
			                </div>
			            </div>
			        </div>
				</div>
			</div>
		</div>

	    <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main" id="page-monitor-graph">
	      	<h1 class="page-header"><a href="#/monitor" class="hidden-lg hidden-md hidden-sm"><i class="fa fa-angle-left" aria-hidden="true"></i>&nbsp;&nbsp;&nbsp;</a>Graphs</h1>
			<div class="row">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" id="div_graphs">
				</div>
			</div>
		</div>


	</div>
</div>

<? echo $htmlFooter; ?>