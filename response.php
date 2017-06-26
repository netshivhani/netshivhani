<?php

	$config = $_SERVER['DOCUMENT_ROOT'].'/efiscal/config.php';
	require_once ( $config );

	$conn = new mysqli($db_host, $db_user, $db_pass, $db);
	
	$method = $_SERVER['REQUEST_METHOD'];
	
	if ($method == 'GET') {
		// Display PDF for selected calculations
		if (isset($_GET['pdf'])) {
			$pdf_id = $_GET['pdf'];
			if (strlen($pdf_id) == 23) {
				$results = $conn->query('SELECT inputs,results,charts FROM calculations WHERE link_id="'.$pdf_id.'"');
				$row = $results->fetch_row();
				
				$inputs = unserialize($row[0]);
				$fields = unserialize($row[1]);
				$charts = unserialize($row[2]);

				// Array of subtotals
				$subtotals = array('Total_Investment_(1)', 'Total_Investment_(2)', 'CAPEX', 'OPEX', 'CAPEX_+_OPEX');
				
				// Include the main TCPDF library (search for installation path).
				require_once('/tcpdf/tcpdf.php');

				// create new PDF document
				$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

				// set document information
				$pdf->SetCreator(PDF_CREATOR);
				$pdf->SetAuthor('UCT');
				$pdf->SetTitle('UCT e-Fiscal');
				$pdf->SetSubject('e-Fiscal Calculations');
				//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

				// set default header data
				//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 029', PDF_HEADER_STRING);

				// set header and footer fonts
				$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
				$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

				// set default monospaced font
				$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

				// set margins
				$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
				$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
				$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

				// set auto page breaks
				$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

				// set image scale factor
				$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

				// set some language-dependent strings (optional)
				if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
					require_once(dirname(__FILE__).'/lang/eng.php');
					$pdf->setLanguageArray($l);
				}

				// ---------------------------------------------------------

				// set array for viewer preferences
				$preferences = array(
					'HideToolbar' => true,
					'HideMenubar' => true,
					'HideWindowUI' => true,
					'FitWindow' => true,
					'CenterWindow' => true,
					'DisplayDocTitle' => true,
					'NonFullScreenPageMode' => 'UseNone', // UseNone, UseOutlines, UseThumbs, UseOC
					'ViewArea' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
					'ViewClip' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
					'PrintArea' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
					'PrintClip' => 'CropBox', // CropBox, BleedBox, TrimBox, ArtBox
					'PrintScaling' => 'AppDefault', // None, AppDefault
					'Duplex' => 'DuplexFlipLongEdge', // Simplex, DuplexFlipShortEdge, DuplexFlipLongEdge
					'PickTrayByPDFSize' => true,
					'PrintPageRange' => array(1,1,2,3),
					'NumCopies' => 2
				);

				// set pdf viewer preferences
				$pdf->setViewerPreferences($preferences);

				// set font
				$pdf->SetFont('helvetica', 'BI', 8);

				// add a page
				$pdf->AddPage();

				// Questionnaire
				$pdf->SetFont('helvetica', 'I', 10);
				$pdf->setTextColor(255, 255, 255);
				$pdf->SetFillColor(0, 122, 204);
				$pdf->setDrawColor(0, 122, 204);
				$pdf->setCellPaddings(1);
				$pdf->Cell(0, 0, "Questionnaire", 1, 1, 'C', true);
				$pdf->Ln(2);

				// Input questions
				foreach ($inputs as $key => $val) {
					$inputinfo = explode('|',$key);
					
					$pdf->SetFont('helvetica', 'I', 8);
					$pdf->SetTextColor(0, 0, 255);
					
					$pdf->Cell(120, 0, str_replace('_', ' ', $inputinfo[0]), 0, 0, 'L');
					
					$pdf->SetFont('helvetica', 'I', 8);
					$pdf->SetTextColor(0, 0, 0);
					$pdf->Cell(30, 0, $val, 0, 1, 'L');
				}
				$pdf->Ln(5);
				
				$pdf->setDrawColor(174, 223, 255);
	
				// Totals
				$pdf->SetFont('helvetica', 'I', 10);
				$pdf->setTextColor(255, 255, 255);
				$pdf->SetFillColor(0, 122, 204);
				$pdf->setDrawColor(0, 122, 204);
				$pdf->setCellPaddings(1);
				$pdf->Cell(0, 0, "Totals", 1, 1, 'C', true);
				$pdf->Ln(2);
				
				$pdf->SetFillColor(240, 249, 255);
				$pdf->setDrawColor(174, 223, 255);
				
				// Calculated results
				foreach ($fields as $key => $val) {
					$fieldinfo = explode('|',$key);
					$title = $fieldinfo[0];
					
					if ($pdf->GetY() > 290) {
						$pdf->AddPage();
					}
					
					// Regular text
					$sub = false;
					if (in_array($title, $subtotals)) {
						$sub = true;
					}

					// Section breaks
					if (($title == 'FTEs/1000s_Cores') || ($title == 'Total_Depreciation_-_CAPEX')) {
						$pdf->Ln(5);
						$pdf->SetFont('helvetica', 'I', 10);
						$pdf->setTextColor(255, 255, 255);
						$pdf->SetFillColor(0, 122, 204);
						$pdf->setDrawColor(0, 122, 204);
						$pdf->setCellPaddings(1);
						switch ($title) {
							case 'FTEs/1000s_Cores':
								$heading = 'Ratios';
								break;
							case 'Total_Depreciation_-_CAPEX':
								$heading = 'Final Costs';
								break;
						}
						$pdf->Cell(0, 0, $heading, 1, 1, 'C', true);
						$pdf->Ln(2);
						$pdf->SetFillColor(240, 249, 255);
						$pdf->setDrawColor(174, 223, 255);
					}
					
					$pdf->SetFont('helvetica', 'BI', 8);
					if ($sub) {
						$pdf->setTextColor(0, 122, 213);
					} else {
						$pdf->setTextColor(0, 0, 255);
					}
					
					$pdf->setCellPadding(1);
					$pdf->Cell(40, 0, str_replace('_', ' ', $title), 1, 0, 'L', true);
					$pdf->SetFont('helvetica', '', 8);
					if ($sub) {
						$pdf->setTextColor(0, 122, 213);
					} else {
						$pdf->setTextColor(0, 0, 0);
					}
					$pdf->setCellPaddings(12, 1, 1, 1);
					$pdf->Cell(40, 0, $val, 1, 1, 'L');
					
					if ($sub) {
						$pdf->Ln(2);
					}
				}
				
				// write charts
				$pdf->Ln(5);
				foreach ($charts as $chart_title => $chart_png) {
					// Page break if no more room for chart
					if ($pdf->GetY() > 200) {
						$pdf->AddPage();
					}
					
					// Chart heading
					$titlefields=explode('|',$chart_title);
					$titlefields[0]=str_replace('_', ' ', $titlefields[0]);
					$pdf->SetFont('helvetica', 'I', 10);
					$pdf->setTextColor(255, 255, 255);
					$pdf->SetFillColor(0, 122, 204);
					$pdf->setDrawColor(0, 122, 204);
					$pdf->setCellPaddings(1);
					$pdf->Cell(0, 0, $titlefields[0], 1, 1, 'C', true);
					$pdf->Ln(2);
					
					$pdf->SetFillColor(240, 249, 255);
					$pdf->setDrawColor(174, 223, 255);
				
					// Chart
					$y = $pdf->GetY();
					$imgdata = base64_decode(str_replace('data:image/png;base64,', '', $chart_png));
					$pdf->Image('@'.$imgdata, 20, $y, 144, 60);
					$pdf->SetY($y + 60);
				}
				
				// ---------------------------------------------------------

				//Close and output PDF document
				$pdf->Output('results.pdf', 'D');
			}
		}
	}
	
	if ($method == 'POST') {
		// Save posted calculations
		$link_id = uniqid('', TRUE);

		$postvars = $_POST;
		$inputs = array();
		$calcresults = array();
		$charts = array();
		$info = array();
		
		foreach ($postvars as $key => $val) {
			$fieldinfo = explode('|', $key);
			
			switch ($fieldinfo[2]) {
				case 'input':
					$inputs[$key]=$val;
					break;
				case 'result':
					$calcresults[$key]=$val;
					break;
				case 'chart':
					$charts[$key]=$val;
					break;
				case 'info':
					$info[$fieldinfo[1]]=$val;
					break;
			}
		}
		
		$inputserial = serialize($inputs);
		$calcserial = serialize($calcresults);
		$chartserial = serialize($charts);
		
		$created = date("Y-m-d H:i:s");
		
		$results = $conn->query("INSERT INTO calculations (link_id,inputs,results,charts,submitted_by,organisation,created) VALUES ('".$link_id."', '$inputserial', '$calcserial', '$chartserial', '".$info['myName']."', '".$info['myOrganisation']."', '$created');");
			
		// return unique URL for calculation results
		$url = $_SERVER['SCRIPT_NAME'].'?pdf='.$link_id;
		echo $url;
	}
	
	$conn->close();
?>