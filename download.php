<?php
	session_name('UCTEFISCAL');
	session_start();

	$salt = 'UV2pM4S6fQXgbnKn';

	$config = $_SERVER['DOCUMENT_ROOT'].'/efiscal/config.php';
	require_once ( $config );
	
	$method = $_SERVER['REQUEST_METHOD'];
	
	// only respond to post request
	if ($method == 'POST') {
		if (isset($_POST['downloadcsv']) && isset($_POST['downloadhash'])) {
			// post vars set
			$downloadhash = $_POST['downloadhash'];
			$hashcompare = md5($salt.$_SESSION['username'].'csvdownload');
			if ($hashcompare == $downloadhash) {
				// hash match, present CSV
				
				// list everything in calculations table
				$conn = new mysqli($db_host, $db_user, $db_pass);
				if ($conn->connect_error) {
					   die("Connection failed: " . $conn->connect_error);
				}
				$sql = "SELECT submitted_by, organisation, created, inputs, results, link_id FROM `efiscal`.`calculations` ORDER BY created DESC;";
				$db_result = $conn->query($sql);
				if ($db_result->num_rows > 0) {
					// add CSV content headers
					header("Content-Type: application/vnd.ms-excel");
					header('Content-Disposition: attachment; filename=calculations.csv');

					$output = fopen('php://output', 'w');
					$header_exists = false;
					while ($db_array = $db_result->fetch_array()) {
						// have headers been written?
						if (!$header_exists) {
							$header_out = array();
						
							$headers = array_merge(array("Submitted by"=>"","Organisation"=>"","Submitted"=>""), unserialize($db_array['inputs']), unserialize($db_array['results']));

							foreach ($headers as $key=>$header) {
								$key_array = explode("|", $key);
								$header_text = str_replace("_", " ", $key_array[0]);
								
								$header_out[] = $header_text;
							}

							fputcsv($output, $header_out);
							$header_exists = true;
						}
						
						// build output
						$row_out = array();
						
						$row_out[] = $db_array['submitted_by'];
						$row_out[] = $db_array['organisation'];
						$row_out[] = $db_array['created'];

						// unserialize inputs into array
						$inputs = unserialize($db_array['inputs']);
						// unserialize results into array
						$results = unserialize($db_array['results']);
						
						$row_out = array_merge($row_out, $inputs, $results);
						fputcsv($output, $row_out);
						
					}
					fclose($output);
				}
				mysqli_close($conn);
				
			}
		}
	}
?>