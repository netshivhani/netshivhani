<?php
	putenv('LDAPTLS_REQCERT=never');
	$ldap_host = "srvwinads001.wf.uct.ac.za";
	$ldap_anon_host = "srvslsadm001.uct.ac.za";
	$ad_domain = "wf.uct.ac.za";
	$base_dn = "DC=wf,DC=uct,DC=ac,DC=za";
	$base_anon_dn = "o=uct";
	$grouparray = array("CN=Linux_CBS_Admins,OU=Services,DC=wf,DC=uct,DC=ac,DC=za",
		"CN=CBS_ECM_Admins,OU=CBS,OU=IT Admins,DC=wf,DC=uct,DC=ac,DC=za",
		"CN=CBS Remote Admins,OU=Services,DC=wf,DC=uct,DC=ac,DC=za");

	// Database configuration
	$db_host = "localhost";
	$db_user = "root";
	$db_pass = "";
	$db = "efiscal";

	// Folder paths
	$site_root = "/efiscal/";
?>
