<?php

##
# © 2017 Partners HealthCare System, Inc. All Rights Reserved. 
##

#####
# Setup this plugin in your REDCap plugins folder
# Specify the:
#  - Users who can access this plugin (their usernames)
#  - Specify a custom "Secret" passphrase that will need to be 
#     entered every time an IP is to be unbanned
#  - Specify the PIDs that this plugin can be added to
#####

require_once "../redcap_connect.php";
 

// Set the usernames of those who can access this plugin
$allowedUsers = array(<ARRAY OF USERS WHO CAN USE THIS>);
$secret_phrase = "MySecretPhrase";

################################################################################
################################################################################
allowUsers($allowedUsers);

$allowed_project = array(<ALLOWED PROJECT IDs>);
allowProjects($allowed_project);

require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';

# Show the currently banned IPs
$table_headers = array (
	array (120, "IP", "center"),
        array (120, "Time Banned", "center"),
);

$current_ban_sql = "SELECT * FROM redcap_ip_banned order by time_of_ban;";
$result = db_query ( $current_ban_sql );
$table_data = array();
$counter = 0;
$ok_to_del = false;
if ( isset($_POST) ) {
	if ( isset ($_POST['secretphrase']) && strlen($_POST['secretphrase'])>0) {
		if ( $_POST['secretphrase'] === $secret_phrase ) {
			$ok_to_del = true;
		}
		else {
			echo "You entered an incorrect secret phrase!";
		}
	}
}
$num_deleted = 0;
$confirm_msg = "";
while ($row = db_fetch_array($result)) {
	$deleted = false;
	if ( isset ($_POST) && isset ( $_POST['unbanip'] ) ) {
		if ( $ok_to_del ) {
			if ( $_POST['unbanip'] === $row['ip'] ) {
				db_query("DELETE FROM redcap_ip_banned WHERE ip='".$row['ip']."' and time_of_ban='".$row['time_of_ban']."';");
				$confirm_msg .= RCView::confBox("IP Deleted: ".$_POST['unbanip']);
				$deleted = true;
				$num_deleted++;
			}
		}
	}
	if (!$deleted) {
		$table_data[] = array (
			RCView::div(array('class'=>"wrap", 'style'=>'font-weight:normal;padding:2px 0;', 'id'=>'row_1_'.$counter), $row['ip']),
               		RCView::div(array('class'=>"wrap", 'style'=>'font-weight:normal;padding:2px 0;', 'id'=>'row_2_'.$counter), $row['time_of_ban']),
       		);
	}
	$counter++;
}

if ( $ok_to_del && $num_deleted==0) {
    // We didn't find the entry in the table
    $html .= RCView::errorBox("The IP Address: ".$_POST['unbanip']." was not found in the database. Please check your entry"); 
}
$html .= $confirm_msg;
$html .= renderGrid("Currently Banned IPs", "Currently Banned IPs List", 300, 'auto', $table_headers, $table_data, true, true, false);
echo $html ."</p>";

# Show an input field to enter the IP you want to unban
echo "<form id='ipbanadm' action='ipbanadmin.php?pid=".PROJECT_ID."' method='POST'>";
echo "IP to Unban: <input type='text' name='unbanip' id='unbanip'></p>";
echo "What is the secret phrase: <input type='password' name='secretphrase' id='secretphrase'></p>";
echo " <input type='submit' value='Unban IP'>";
echo "</form>";
unset($_POST['unbanip']);
unset($_POST['secretphrase']);
require_once APP_PATH_DOCROOT . 'ProjectGeneral/footer.php';
?>
