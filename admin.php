<?php
require_once ( './config.php' );
require_once ( './libs/ldap.php' );

global $db_host, $db_user, $db_pass;

session_name('UCTEFISCAL');
session_start();

$salt = 'UV2pM4S6fQXgbnKn';

// validate session
function is_authed() {
	global $db_host, $db_user, $db_pass;
	if (isset($_SESSION['ip'])) {
		if($_SESSION['ip']==getenv("REMOTE_ADDR")) {
			if(isset($_SESSION['username'])) {
				// compare to database and set admin value
				$conn = new mysqli($db_host, $db_user, $db_pass);
				if ($conn->connect_error) {
					   die("Connection failed: " . $conn->connect_error);
				}
				$sql = "SELECT session_id, ip, user, admin FROM `efiscal`.`session` WHERE session_id='".session_id()."' AND ip='".$_SESSION['ip']."' AND user='".$_SESSION['username']."';";
				$db_result = $conn->query($sql);
				if ($db_result->num_rows == 1) {
				        $db_array = $db_result->fetch_array();
					$result->role = $db_array['admin'];
					$result->authed = true;
				} else {
					$result->authed = false;
				}
			        $conn->close();
			} else {
				unset($_SESSION['ip']);
				unset($_SESSION['username']);
				$_SESSION['error']="Session error";
				$result->authed = false;
			}
		} else {
			unset($_SESSION['ip']);
			unset($_SESSION['username']);
			$_SESSION['error']="Session error";
			$result->authed = false;
		}
	} else {
		$result->authed = false;
	}

	return $result;
}

// log out and destroy session
function logout() {
	global $db_host, $db_user, $db_pass; 
	// delete from session table
        $conn = new mysqli($db_host, $db_user, $db_pass);
        if ($conn->connect_error) {
               die("Connection failed: " . $conn->connect_error);
        }
        $sql = "DELETE FROM `efiscal`.`session` WHERE session_id='".session_id()."';";
        $conn->query($sql);
        $conn->close();

        //unset($_SESSION['ip']);
        //unset($_SESSION['username']);
        session_unset();
        session_destroy();
        // Unset the cookie on the client-side.
        setcookie("UCTEFISCAL", "", 1); // Force the cookie to expire.
        header('Location: '.$_SERVER['SCRIPT_NAME']);
}

// user controller
function user($request, $target) {
	global $db_host, $db_user, $db_pass, $salt;
	// check if post vars exist
	$method = $_SERVER['REQUEST_METHOD'];
	switch ($request) {
		case 'search':
			// display search results
			if ( $method == 'POST' ) {
				if (!empty($_POST['newusername']) && !empty($_POST['search-user'])) {
					$cn = $_POST['newusername'];
					
					if (strlen($cn) < 4) {
						$_SESSION['error']="Too many results. Please enter a longer search string.";
						header('Location: '.$_SERVER['SCRIPT_NAME']);
					} else {
						// get existing users for comparison
						$existingusers = array(); 
						// list all regular users
						$conn = new mysqli($db_host, $db_user, $db_pass);
						if ($conn->connect_error) {
							   die("Connection failed: " . $conn->connect_error);
						}
						$sql = "SELECT username FROM `efiscal`.`users` ORDER BY username;";
						$db_result = $conn->query($sql);

						if ($db_result->num_rows > 0) {
							while ($db_array = $db_result->fetch_array()) {
								$existingusers[] = $db_array[0];
							}
						}

						$result=LdapSearch($cn);
						
						if ($result->count > 0) {
							if ($result->count == 1) {
								// just one result, skip to adding
								header('Location: '.$_SERVER['SCRIPT_NAME']."/user/add/".$cn);
							} else {
								echo "<h2>Search results for: $cn</h2>";
								echo "<h3>".$result->count." found</h3>";
								echo "<div class='table'>";
								echo "<div class='header'>";
								echo "<div class='heading'>Username</div><div class='heading'>Name</div><div class='heading'>Email</div><div class='heading'>&nbsp;</div>";
								echo "</div>";
								foreach ($result->entries as $entry) {
									if (isset($entry['cn'])) {
										// link to select only active if user does not already exist
										if (in_array($entry['cn'],$existingusers)) {
											$action = 'Already added';
										} else {
											$action = "<a href='".$_SERVER['SCRIPT_NAME']."/user/add/".$entry['cn']."'>Add user</a>";
										}
									
										echo "<div class='row'>";
										echo "<div class='cell'>".$entry['cn']."</div><div class='cell'>".$entry['displayname']."</div><div class='cell'>".$entry['mail']."</div><div class='cell'>".$action."</div>";
										echo "</div>";
									}
								}
								echo "</div>";
							}
						} else {
							// no results
							$_SESSION['error']="No results found.";
							header('Location: '.$_SERVER['SCRIPT_NAME']);
						}
					}
					
				} else {
					// redirect to index
					header('Location: '.$_SERVER['SCRIPT_NAME']);
				}
			} else {
				// redirect to index
				header('Location: '.$_SERVER['SCRIPT_NAME']);
			}

			break;
			
		case 'add':
			// add to database
			if (isset($target)) {
				if (strlen($target) <= 9) {
					// look up and add
					$result = LdapSearch($target);
					
					if ($result->count == 1) {
						// check if user exists in database
						$conn = new mysqli($db_host, $db_user, $db_pass);
						if ($conn->connect_error) {
							   die("Connection failed: " . $conn->connect_error);
						}
						$db_result = $conn->query("SELECT username from `efiscal`.`users` WHERE username LIKE '".$target."%'");
						if ($db_result->num_rows == 0) {

							// add single result to database
							$sql = "INSERT INTO `efiscal`.`users`(username, displayname, email) VALUES('".$result->entries[1]['cn']."', '".$result->entries[1]['displayname']."', '".$result->entries[1]['mail']."');";

							$db_result = $conn->query($sql);
							if ($db_result) {
								$_SESSION['result']="User ".$result->entries[1]['cn']." added successfully.";
								header('Location: '.$_SERVER['SCRIPT_NAME']);
							}
						} else {
							$_SESSION['error']="User ".$result->entries[1]['cn']." already exists.";
							header('Location: '.$_SERVER['SCRIPT_NAME']);
						}
					} else {
						$_SESSION['error']="Too many results.";
						header('Location: '.$_SERVER['SCRIPT_NAME']);
					}
				} else {
					$_SESSION['error']="Target invalid.";
					header('Location: '.$_SERVER['SCRIPT_NAME']);
				}
			} else {
				$_SESSION['error']="Target missing.";
				header('Location: '.$_SERVER['SCRIPT_NAME']);
			}
			break;
			
		case 'delete':

			$newtargethash = md5($salt.$target);
			if ($method=='POST') {

				if (isset($_POST['confirm']) && isset($_POST['targethash'])) {
					// hash and confirmation set
					
					if ($newtargethash == $_POST['targethash']) {
						// posted hash matches calculated hash
						// ready to delete
						
						// check if user exists in database
						$conn = new mysqli($db_host, $db_user, $db_pass);
						if ($conn->connect_error) {
							   die("Connection failed: " . $conn->connect_error);
						}
						$db_result = $conn->query("SELECT username from `efiscal`.`users` WHERE username='".$target."'");
						if ($db_result->num_rows == 1) {

							// delete user from database
							$sql = "DELETE FROM `efiscal`.`users` WHERE username='".$target."';";
							$db_result = $conn->query($sql);
							if ($db_result) {
								$_SESSION['result']="User ".$target." deleted successfully.";
								header('Location: '.$_SERVER['SCRIPT_NAME']);
							}
						} else {
							$_SESSION['error']="User not found.";
							header('Location: '.$_SERVER['SCRIPT_NAME']);
						}
					} else {
						$_SESSION['error']="Invalid target hash.";
						header('Location: '.$_SERVER['SCRIPT_NAME']);
					}
				} else {
					$_SESSION['error']="Confirmation missing.";
					header('Location: '.$_SERVER['SCRIPT_NAME']);
				}
			} else {
				// display confirmation
				echo "<form name='confirmform' id='confirmform' method='post' action='".$_SERVER['REQUEST_URI']."'>";
				echo "<h2>Confirm delete</h2>";
				echo "<div class='form-label'>Are you sure you want to delete ".$target."?</div><br />";
				echo "<div><input name='confirm' id='delete' type='submit' value='Confirm'>&nbsp;&nbsp;<a href='".$_SERVER['SCRIPT_NAME']."'>or go back</a></div><br />";
				
				echo "<input name='targethash' type='hidden' value='".$newtargethash."'>";
				echo "</form>";
			}
			break;
	}
	
}

// write login form
function login_form() {
	echo "<form name='loginform' id='loginform' method='post' action='".$_SERVER['SCRIPT_NAME']."'>";
	echo "<h2>Enter your username and password</h2>";
	echo "<div class='form-label'>Username:</div><div><input name='username' id='username' type='text' maxlength='16'></div>";
	echo "<div class='form-label'>Password:</div><div><input name='password' id='password' type='password'></div>";
	echo "<div><input name='login' id='login' type='submit' value='Log in'></div><br />";
	echo "</form>";
}

// write user list
function user_list() {
	global $db_host, $db_user, $db_pass; 
	// list all regular users
	$conn = new mysqli($db_host, $db_user, $db_pass);
	if ($conn->connect_error) {
		   die("Connection failed: " . $conn->connect_error);
	}
	$sql = "SELECT username, displayname, email FROM `efiscal`.`users` ORDER BY username;";
	$db_result = $conn->query($sql);

	if ($db_result->num_rows > 0) {
		echo "<h2>User List</h2>";
		echo "<div class='table'>";
		echo "<div class='header'>";
		echo "<div class='heading'>Username</div><div class='heading'>Display Name</div><div class='heading'>Email</div><div class='heading'>&nbsp;</div>";
		echo "</div>";
		while ($db_array = $db_result->fetch_array()) {
			echo "<div class='row'>";
			echo "<div class='cell'>".$db_array['username']."</div><div class='cell'>".$db_array['displayname']."</div><div class='cell'>".$db_array['email']."</div><div class='cell'><a href='".$_SERVER['SCRIPT_NAME']."/user/delete/".$db_array['username']."'>Delete</a></div>";
			echo "</div>";
		}
		echo "</div>";
	}

	$conn->query($sql);
	$conn->close();
	
	// new user form
	echo "<form name='userform' id='userform' method='post' action='".$_SERVER['SCRIPT_NAME']."/user/search'>";
	echo "<h2>New User</h2>";
	echo "<div class='form-label'>Username:</div><div><input name='newusername' id='newusername' type='text' maxlength='9'></div>";
	echo "<div><input name='search-user' id='search-user' type='submit' value='Add User'></div><br />";
	echo "</form>";
	echo "<hr>";
}

// write calculations tables
function show_calculations() {
	global $db_host, $db_user, $db_pass, $salt; 
	// list everything in calculations table
	$conn = new mysqli($db_host, $db_user, $db_pass);
	if ($conn->connect_error) {
		   die("Connection failed: " . $conn->connect_error);
	}
	$sql = "SELECT submitted_by, organisation, created, inputs, results, link_id FROM `efiscal`.`calculations` ORDER BY created DESC;";
	$db_result = $conn->query($sql);
	if ($db_result->num_rows > 0) {
		echo "<h2>Submitted Calculations</h2>";
		// csv export link
		$downloadhash = md5($salt.$_SESSION['username'].'csvdownload');
		echo "<form name='downloadform' id='downloadform' method='post' action='download.php'>";
		echo "<div><input name='downloadcsv' id='downloadcsv' type='submit' value='Download CSV'></div><br />";
		echo "<input type='hidden' name='downloadhash' value='".$downloadhash."'>";
		echo "</form>";
	
		echo "<div class='table'>";
		$header_exists = false;
		while ($db_array = $db_result->fetch_array()) {
		
			// have headers been written?
			if (!$header_exists) {
				$headers = array_merge(array("Submitted by"=>"","Organisation"=>"","Submitted"=>""), unserialize($db_array['inputs']), unserialize($db_array['results']));
				$headers['PDF Link'] = "";
				echo "<div class='header'>";
				foreach ($headers as $key=>$header) {
					$key_array = explode("|", $key);
					$header_text = str_replace("_", " ", $key_array[0]);
					
					echo "<div class='heading'>$header_text</div>";
				}
				echo "</div>";
				$header_exists = true;
			}
		
			echo "<div class='row'>";
			echo "<div class='cell'>".$db_array['submitted_by']."</div><div class='cell'>".$db_array['organisation']."</div><div class='cell'>".$db_array['created']."</div>";
			
			// unserialize and explode inputs into cells
			$inputs = unserialize($db_array['inputs']);
			foreach ($inputs as $key=>$input) {
				$key_array = explode("|", $key);
				//echo "<td>".str_replace("_", " ", $key_array[0])."</td>";
				echo "<div class='cell'>".$input."</div>";
			}
			
			// unserialize and explode results into cells
			$results = unserialize($db_array['results']);
			foreach ($results as $key=>$result) {
				$key_array = explode("|", $key);
				//echo "<td>".str_replace("_", " ", $key_array[0])."</td>";
				echo "<div class='cell'>".$result."</div>";
			}
			echo "<div class='cell'><a href='/efiscal/response.php?pdf=".$db_array['link_id']."'>Download</a></div>";
			echo "</div>";
		}
		echo "</div>";
	}
	
	$conn->query($sql);
	$conn->close();
}

// load auth object
$authobj = is_authed();

// add html bits
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
        <head>
                <title>University of Cape Town / e-fiscal@uct</title>
                <link rel="stylesheet" type="text/css" href="/efiscal/css/Site.css">
				<style>
					body {
						background: #fff;
						padding: 0;
						margin: 0;
					}
				</style>
        </head>

        <body>
                <div id="header-content">
                <div id="banner-content">
                        <a href="http://www.uct.ac.za"><img id="site-logo" src="/efiscal/uctlogo_h.png"></a><div id="site-name"><h1>e-fiscal@uct</h1>
                        <div><a href='<?php echo $_SERVER['SCRIPT_NAME']; ?>'>Home</a>&nbsp;&nbsp;&nbsp;<?php if ($authobj->authed) { echo "<a href='".$_SERVER['SCRIPT_NAME']."/logout'>Sign out</a>";}?></div></div>
                </div>
                <div id="subheader-content">
                </div>
                </div>
                <div id="main-content">
<?php
if (isset($_SESSION['result'])) {
        // write result and unset
        echo "<div class='message result-message'>".$_SESSION['result']."</div>";
        unset($_SESSION['result']);
}

if (isset($_SESSION['error'])) {
        // write error and unset
        echo "<div class='message error-message'>".$_SESSION['error']."</div>";
        unset($_SESSION['error']);
}

if (!$authobj->authed) {
	// check if post vars exist
	$method = $_SERVER['REQUEST_METHOD'];
	if ( $method == 'POST' ) {
		if (!empty($_POST['username']) && !empty($_POST['password'])) {
			$user = $_POST['username'];
			$pass = $_POST['password'];

				$userobj = Login($user,$pass);
				if ($userobj->auth) {
				$_SESSION['username'] = $user;
				$_SESSION['ip'] = getenv ( 'REMOTE_ADDR');
				// log session
				$conn = new mysqli($db_host, $db_user, $db_pass);
				if ($conn->connect_error) {
						die("Connection failed: " . $conn->connect_error);
				}
				
				if (($userobj->CBS) || ($user == '01446469')) {
					$role = 1;
				} else {					
					// Check for regular user access
					$sql = "SELECT username FROM `efiscal`.`users` WHERE username = '$user'";
					$user_result = $conn->query($sql);
					if ($user_result->num_rows == 1) {
						$role = 2;
					} else {
						// destroy any session and redirect to login
						$_SESSION['error'] = "Access denied.";
						logout();
					}
				}
				
				// Generate a new session ID
				session_regenerate_id(true);

				$sql = "INSERT INTO `efiscal`.`session` (session_id, ip, user, admin, created) VALUES ('".session_id()."','".getenv( 'REMOTE_ADDR' )."','".$user."','".$role."','".date("Y-m-d H:i:s")."');";

				$conn->query($sql);
				$conn->close();

				// redirect as authed
				header('Location: '.$_SERVER['SCRIPT_NAME']);
			} else {
				$_SESSION['error'] = $userobj->ldaperror;
				header('Location: '.$_SERVER['SCRIPT_NAME']);
			}
		} else {
			// show login form
			login_form();
		}
	} else {
		// show login form
		login_form();
	}

} else {
	$user = $_SESSION['username'];

	$request = explode("/", substr(@$_SERVER['PATH_INFO'], 1));

	if ($authobj->role == 1) {
		// admin section request
		switch (strtolower($request[0])) {
			case 'user':
				user($request[1], $request[2]);
				break;
			case 'logout':
				logout();
			default:
				user_list();
				show_calculations();
				break;
		}
	} else {			
		// user section request
		switch (strtolower($request[0])) {
			case 'logout':
				logout();
				break;
			default:
				show_calculations();
				break;
		}
	}

}
// remember to add closing html bits
?>
                </div>
                <div class="spacer">&nbsp;</div>
                <div id="footer-content">&nbsp;</div>
                <div id="subfooter-content"><div id="postscript">&copy;&nbsp;University of Cape Town 2014. All rights reserved.</div></div>
        </body>
</html>