<?php
	 
	for($row = 0; $row<=100; $row=$row+10){
		$str = '<div class="row coloreveryothersecond">
	              <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	                <div class="row"> 
	                 	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="cursor: pointer;" data-toggle="collapse" data-target="#rep_div_'.$row.'"> 
	                    	<div class="section-header">'.$row.'</div><span class="fa fa-chevron-down float-right section-chevron"></span>
		                </div>           
	              	</div>
	              	<div class="row collapse" id="rep_div_'.$row.'">	                 	
			                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
			                      <h5>IP addresses</h5>
			                      <p>Lorem ipsum dolor</p>
			                      <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_'.$row.'_1" id="rep_ta_'.$row.'_1"><?= implode("\n",$not_ok); ?></textarea> 
			                      <br/>
			                    </div> 
			                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
			                      <h5>Domain names</h5>
			                      <p>Lorem ipsum dolor</p>
			                      <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_'.$row.'_2" id="rep_ta_'.$row.'_2"><?= implode("\n",$not_ok); ?></textarea> 
			                      <br/>
			                    </div>
			                    <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
			                      <h5>Name servers</h5>
			                      <p>Lorem ipsum dolor</p>
			                      <textarea class="form-control" placeholder="Unwanted sender@domain.com or @domain.com" rows="10" name="rep_ta_'.$row.'_3" id="rep_ta_'.$row.'_3"><?= implode("\n",$not_ok); ?></textarea> 
			                      <br/>
			                    </div>              
			         </div> 
	              	</div>
	              </div>';

	    echo $str;
	    echo "<br/>yolo";
	}

	





?>