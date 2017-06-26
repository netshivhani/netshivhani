<?php 
        function printIndicatorGraph ($data) 
		{			
			if ($data["average"]!='')
			{
				return sprintf('<button class="graph"  title="Show Average, Median and Comparison Chart" questionId="%s" average="%s" median="%s" prefix="%s"><span class="graph"></span></button>',$data["id"],$data["average"],$data["median"],$data["prefix"]); //.				       sprintf('%s %s',$data["average"],$data["median"]); 	
			}
			return '';
		}
		
		
		function printTooltip($data)
		{
	       if ($data["tooltip"]!='') 
		   {
	           echo sprintf('<div><a href="javascript:void(0)" class="help" tooltipid="tooltip_%s">Show Help</a></div>',$data["id"]);	
			   return sprintf('<div id="tooltip_%s" class="row-tooltip" >%s</div>',$data["id"], $data["tooltip"]);
		   }
		}
	
		function TemplateValue ($data) 
		{ 
		?>
			<div id="<?php echo $data["id"]?>" class="question" questionindex="<?php echo $data["index"]?>">
				<div class="row">
					 <div class="col0"><?php echo $data["indexcaption"]?></div>
					 <div class="col1">
							<div><?php  echo $data["text"]; ?>																	
							</div>							
					 </div>
					 <div class="col2">&nbsp;</div>
					 <div class="col3">
					     <div>
							 <input type="text" class="number" range="final"/>
							 <span class="prefix"><?php echo $data["prefix"]?></span>							
							 <?php echo printIndicatorGraph($data) ?>						
						 </div>
						 <div class="row-error">Please enter a valid number</div> 
					 </div>
				 </div>
				 <?php echo printTooltip($data) ?>									 
			</div>	
		<?php }

		function TemplateRange ($data) 
		{ 
		?>
				   <div id="<?php echo $data["id"]?>" class="question" questionindex="<?php echo $data["index"]?>">
				   		<div class="row">
	                        <div class="col0"><?php echo $data["indexcaption"]?></div>
							<div class="col1">
								<div><?php echo $data["text"]?></div>
							</div>
							<div class="col2">&nbsp;</div>
							<div class="col3">
								<div>
									<input type="text" class="number range" range="average"/>
									<span class="prefix"><?php echo $data["prefix"]?></span>
									<?php echo printIndicatorGraph($data) ?>						
								</div>
							</div>
						</div>
						<div class="row">
	                        <div class="col0"></div>
							<div class="col1">&nbsp;</div>
							<div class="col2">&nbsp;</div>
							<div class="col3">or</div>						
						</div>
						<div  class="row">
	                        <div class="col0"></div>
							<div class="col1">&nbsp;</div>
							<div class="col2">From:</div>
							<div class="col3">
								<div>
									<input type="text" class="number range" range="from"/><span class="prefix"><?php echo $data["prefix"]?></span>
								</div>	
							</div>						
						</div>	
						<div  class="row">
	                        <div class="col0"></div>
							<div class="col1">&nbsp;</div>
							<div class="col2">To:</div>
							<div class="col3">
								<div>
									<input type="text" class="number range" range="to"/><span class="prefix"><?php echo $data["prefix"]?></span>
									<input type="text" class="number range" range="final" style="border:0px; display:none; background-color:transparent;"/> 
								</div>	
								<div class="row-error">Please enter a valid number</div> 		
							</div>													
						</div>						
						<?php echo printTooltip($data) ?>					
					</div>
		<?php }
		
		function TemplateQuestion ($data,$question)
		{

				 	if ($data["type"]=="VALUE") 
						 TemplateValue($data);
		       		else if ($data["type"]=="RANGE") 
						 TemplateRange($data);
					else	
					{ 
						 echo '<div class="question">';
						 $subquestion=$question->Question[0];								 
						 echo '<div class="row"><div class="col0">'. $data["index"] . '</div><div class="col123"><label><input name="'. $question["Id"] . '" id="for_' . $subquestion["Id"] . '" type="radio" checked="checked"/>' . $subquestion["Text"] . ' <br/><b>- OR -</b></label></div></div>';

						 $subquestion=$question->Question[1];								 
						 echo '<div class="row"><div class="col0">&nbsp;</div><div class="col123"><label><input name="'. $question["Id"] . '" id="for_' . $subquestion["Id"] . '" type="radio"/>' . $subquestion["Text"] . '</label></div></div>';

						 
						 foreach($question->Question as $subquestion)
						 {
								  $data=array("index"=>$data['index'],
											  "indexcaption"=>'',
											  "type"=>(string) $subquestion["InputType"],
											  "id"=>(string) $subquestion["Id"],
											  "text"=>(string) $subquestion["Text"],
											  "tooltip"=>(string) $subquestion->Tooltip,
											  "prefix"=>(string) $subquestion["Prefix"],
											  "average"=>(string) $subquestion["Average"],
											  "median"=>(string) $subquestion["Median"]);	
								  

								  TemplateQuestion($data,$subquestion);	
								  		  		   
						 }
 						 echo '</div>';
					}
		}
		
		
	    $xmlDoc = simplexml_load_file("questions.xml");
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title></title>
    <link href="css/Site.css" rel="stylesheet" />
    <link href="css/tipsy.css" rel="stylesheet" />
	<link rel="stylesheet" type="text/css" href="scripts/colorbox/example3/colorbox.css">
    <link href="css/vscontext.css" rel="stylesheet" />
	
		
    <script src="scripts/jquery-1.7.1.min.js" type="text/javascript"></script>
	<script src="scripts/globalize/lib/globalize.js" type="text/javascript"></script>
	<!--<script src="scripts/globalize/lib/cultures/globalize.cultures.js" type="text/javascript"></script>-->
	<script src="scripts/globalize/lib/cultures/globalize.culture.en-US.js" type="text/javascript"></script>

    <script src="scripts/jquery.tipsy.js" type="text/javascript"></script>
    <script src="scripts/colorbox/colorbox/jquery.colorbox-min.js" type="text/javascript"></script>	
    <script src="scripts/jquery.cookie.js" type="text/javascript"></script>	   

	<script src="scripts/jsapi.js" type="text/javascript"></script>	
	<script src="scripts/index.js"></script>
</head>
<body>

    <!--
	<button onClick="Save()">Save</button>
	<button onClick="Load()">Load</button>
	
	Select Language:<select id="cultures">
					</select>
	-->	
 	<div class="wrapper">
	    
		<div class="header">
			<div>
				<!--<a href="http://www.eFiscal.eu" target="_blank" title="http://www.eFiscal.eu" alt="http://www.eFiscal.eu"style="border:0px;top:70px; left:203px;">--> <img src="images/efiscal-uct.png" width="300" height="100" style="float:left"/></a>
				<img src="images/uct-logo.png" width="300" height="44" style="float:right; margin-top:44px; margin-right:10px"/>				
			</div>
			<p style="clear:both"></p>
		</div>
		
		<div class="banner">
		
		</div>

		<div class="tabs">
			<ul>
				<li class="active" div="questions"><a href="#">Questionnaire</a></li>
				<li div="results"><a href="#">Results & Analysis</a></li>
				<!-- 20150225 Removed comparative graphs
				<li div="comparative"><a href="#">Comparative Graphs</a></li>
				-->
				<li div="save"><a id="pdflink" href="#">Download PDF</a></li> 
				<li div="admin"><a id="pdflink" href="/efiscal/admin.php">Admin Login</a></li> 
			</ul>
			<p style="clear:both"></p>
		</div>
		
					
		<div class="tabs-container">
			<div style="color:blue; font-style:italic; margin:20px;">*All Average and Median values refer to 2011
			

			<div id="questions" class="content">
				<div id="data_myName" class="question">
					<div class="row">
						<div class="col0"></div>
						<div class="col1">
							<div>Your name</div>
						</div>
						<div class="col2">&nbsp;</div>
						<div class="col3">
							<div><input class="text" id="myName" type="text"></input><span class="prefix">(required)</span></div>
						</div>
					</div>
				</div>
				<div id="data_myOrganisation" class="question">
					<div class="row">
						<div class="col0"></div>
						<div class="col1">
							<div>Your organisation</div>
						</div>
						<div class="col2">&nbsp;</div>
						<div class="col3">
							<div><input class="text" id="myOrganisation" type="text"></input><span class="prefix">(required)</span></div>
						</div>
					</div>
				</div>
				<?php 
					  $counter=0;
					  foreach($xmlDoc->Questions->Question as $question)
					  {
						  $counter++;
		
						  $data=array("index"=>$counter,
									  "indexcaption"=>$counter . '.',
									  "type"=>(string) $question["InputType"],
									  "id"=>(string) $question["Id"],
									  "text"=>(string) $question["Text"],
									  "tooltip"=>(string) $question->Tooltip,
									  "prefix"=>(string) $question["Prefix"],
									  "average"=>(string)$question["Average"],
									  "median"=> (string)$question["Median"]);	
		
						  TemplateQuestion($data,$question);
					 }?>				 				
			</div>
	
			<div id="results" class="content" style="display:none">
				 <table style="width:100%;" cellspacing="10">
					<tr>
						<td style="vertical-align:top">
							<h4>Totals</h4>
							<div>
							<div style="padding:3px 0px"><button id="btnCalculations">Show Calculations</button></div>
							<table id="calculations" class="result-table" cellspacing="0" cellpadding="0" style="display:none">
								<col width="190">
								<col width="160">
								<tr>
									<td>Computing</td>
									<td><input type="text" id="computing"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td>Tape storage</td>
									<td><input type="text" id="tapeStorage"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td>Disk storage</td>
									<td><input type="text" id="diskStorage"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td class="total">Total investement (1)</td>
									<td class="total"><input type="text" id="totalInvesment1"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td class="gap" colspan="2"></td>
								</tr>
								<tr>
									<td>Interconnect costs</td>
									<td><input type="text" id="interconnectCosts"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td>Support costs</td>
									<td><input type="text" id="supportCosts"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td>Auxiliary equipment</td>
									<td><input type="text" id="auxiliaryEquipment"/><span class="prefix">R</span></td>
								</tr>
								<tr >
									<td class="total">Total investement (2)</td>
									<td class="total"><input type="text" id="totalInvesment2"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td class="gap" colspan="2"></td>
								</tr>
								<tr>
									<td>Depreciation Computing</td>
									<td><input type="text" id="depreciationComputing"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td>Depreciation Tape</td>
									<td><input type="text" id="depreciationTape"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td>Depreciation Disk</td>
									<td><input type="text" id="depreciationDisk"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td>Depreciation Inteconnect</td>
									<td><input type="text" id="depreciationInteconnect"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td>Depreciation Support</td>
									<td><input type="text" id="depreciationSupport"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td>Depreciation Auxiliary</td>
									<td><input type="text" id="depreciationAuxiliary"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td  class="total">CAPEX</td>
									<td  class="total"><input type="text" id="totalDepreciationCAPEX"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td class="gap" colspan="2"></td>
								</tr>
								<tr>
									<td>Software cost</td>
									<td><input type="text" id="softwareCost"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td>Personnel cost</td>
									<td><input type="text" id="personnelCost"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td>Electricity cost</td>
									<td><input type="text" id="electricityCost"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td>Premises</td>
									<td><input type="text" id="premisesCost"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td>Connectivity cost</td>
									<td><input type="text" id="connectivityCost"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td>Other cost</td>
									<td><input type="text" id="otherCost"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td class="total">OPEX</td>
									<td class="total"><input type="text" id="totalOperatingCosts"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td class="gap" colspan="2"></td>
								</tr>
								<tr>
									<td class="total">CAPEX + OPEX</td>
									<td class="total"><input type="text" id="totalCAPEXOPEX"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td class="gap" colspan="2"></td>
								</tr>
								<tr style="display:none">
									<td>Available CPU minutes</td>
									<td><input type="text" id="availableCPUminutes"/></td>
								</tr>			
								<tr  style="display:none">
									<td>Used CPU minutes</td>
									<td><input type="text" id="usedCPUminutes"/></td>
								</tr>			
								<tr>
									<td class="gap" colspan="2"></td>
								</tr>
	
							</table>
	
							<table  class="result-table" cellspacing="0" cellpadding="0">
								<col width="190">
								<col width="160">
							
								<tr>
									<td>CAPEX</td>
									<td><input type="text" id="CAPEX"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td>OPEX</td>
									<td><input type="text" id="OPEX"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td>CAPEX + OPEX</td>
									<td><input type="text" id="CAPEX_OPEX"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td>Utilization rate</td>
									<td><input type="text" id="utilizationRate" average="65" median="75"/><span class="prefix">%</span></td>
								</tr>
								<tr>
									<td>Cost per Core/Hour</td>
									<td><input type="text" id="costPerCorePerHour" average="0.0730" median="0.0317"/><span class="prefix">R</span></td>
								</tr>
								<tr>
									<td>Cost per Core/Year</td>
									<td><input type="text" id="costPerCorePerYear" average="415.82" median="208.04"/><span class="prefix">R</span></td>
								</tr>
								
							</table>		 
							</div>								 							
						</td>
						<td  style="vertical-align:top">
							<h4>Ratios</h4>
							<div>
								<table  class="result-table" cellspacing="0" cellpadding="0">
								<tr>
									<td>FTEs/1000s cores</td>
									<td><input type="text" id="FTEsPer1000Cores"/><span class="prefix"></span></td>
								</tr>
								<tr>
									<td>m2/1000s cores</td>
									<td><input type="text" id="m2Per1000cores"/><span class="prefix"></span></td>
								</tr>
								<tr>
									<td>kwh/core per year</td>
									<td><input type="text" id="kwhPercore" average="339.15" median="307.74"/><span class="prefix"></span></td>
								</tr>
								<tr>
									<td>Power Usage Effectiveness</td>
									<td><input type="text" id="powerUsageEffectiveness" average="1.56" median="1.50"/><span class="prefix"></span></td>
								</tr>                            
								<tr>
									<td>OPEX/Total</td>
									<td><input type="text" id="OPEXPerTotal" average="73.77" median="69.71"/><span class="prefix">%</span></td>
								</tr>
								<tr>
									<td>CAPEX/Total</td>
									<td><input type="text" id="CAPEXPerTotal" average="26.23" median="30.29"/><span class="prefix">%</span></td>
								</tr>
								<!--
								<tr>
									<td>% Tape/CPU</td>
									<td><input type="text" id="TapePerCPU"/><span class="prefix">%</span></td>
								</tr>
								<tr>
									<td>% Disk/CPU</td>
									<td><input type="text" id="DiskPerCPU"/><span class="prefix">%</span></td>
								</tr>
								-->
							</table>		 
							</div>
						</td>
					</tr>
					<tr>
						<td  colspan="2"  style="vertical-align:top">
							<h4>Final Costs</h4>
							<div>
								<table class="result-table" border="0" cellspacing="0" cellpadding="0">
								<tr>
									<td>Total depreciation - CAPEX</td>
									<td><input type="text" id="totalDepreciationCAPEX_FINAL" average="26.23" median="30.29"/><span class="prefix">%</span></td>
								</tr>
								<tr>
									<td>Software cost</td>
									<td><input type="text" id="softwareCost_FINAL" average="3.97" median="1.71"/><span class="prefix">%</span></td>
								</tr>
								<tr>
									<td>Personnel cost</td>
									<td><input type="text" id="personnelCost_FINAL" average="60.08" median="50.69"/><span class="prefix">%</span></td>
								</tr>
								<tr>
									<td>Electricity cost</td>
									<td><input type="text" id="electricityCost_FINAL" average="8.16" median="14.79"/><span class="prefix">%</span></td>
								</tr>
								<tr>
									<td>Premises</td>
									<td><input type="text" id="premisesCost_FINAL" average="1.56" median="2.52"/><span class="prefix">%</span></td>
								</tr>
								<tr>
									<td>Connectivity cost</td>
									<td><input type="text" id="connectivityCost_FINAL"/><span class="prefix">%</span></td>
								</tr>
								<tr>
									<td>Other cost</td>
									<td><input type="text" id="otherCost_FINAL"/><span class="prefix">%</span></td>
								</tr>
							</table>		
								<div id="chart_div_costs">
							
								</div>
														
								<div id="chart_div_stacked">
							
								</div>								
							</div>			
						</td>
					</tr>
				 </table>
			</div>

			<div id="comparative" class="content" style="display:none">
				<div style="padding:3px 0px"><button id="btnComparative">Expand All</button></div>
				<div class="graph-container">
				
				</div>
			</div>
	
		</div>	
         
	</div>
	    <!--
		<div class="wrapper footer">
			<img src="images/icts.png" width="300" height="100"/>						
		</div>		
		-->
		
	</div>
	
	<div class="wrapper footer">&copy; University of Cape Town 2014. All rights reserved</div>

	 <div style="display:none">
		  <div id="error_message">
		  
		  </div>
		  <div id="chart_div_dynamic">
		  </div>						
	 </div>					
 
	 <div id="contextMenu" class="vs-context-menu"> 
			<ul> 
				 <li><a href="javascript:contextMenuAction('Average')" id="menu_average"></a></li> 
				 <li class="seprator"><a href="javascript:contextMenuAction('Median');" id="menu_median"></a></li> 
				 <li class="graph"><a href="javascript:contextMenuAction('Graph');" id="menu_3"><b>Draw Graph...</b></a></li> 
			</ul> 
	 </div>

	<textarea id="json" style="display:none; left:0px; position:fixed; top:0px; width:200px; height:500px;">
{
   "number0":"1000",
   "number1":"100",
   "number2":"300",
   "number3":"200000",
   "number4":"1760",
   "number5":"3400",
   "number6":"3400",
   "number7":"4",
   "number8":"5",
   "number9":"8",
   "number10":"25",
   "number11":"20",
   "number12":"0",
   "number13":"500000",
   "number14":"8",
   "number15":"730000",
   "number16":"4745",
   "number17":"13140",
   "number18":"8280",
   "number19":"50000",
   "number20":"20000",
   "number21":"2372",
   "number22":"1.36"
}	</textarea> 	
</body>
</html>
