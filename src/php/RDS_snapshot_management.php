<?php
/** RDS Backup Script
* options are as follows:
*
* -c config_filename_in_json_format
* example config file:
*{
  "includes": ["_aws"],
  "services": {
      "default_settings": {
          "params": {
              "key": "YOURKEY",
              "secret": "YOURAWSSECRETKEY",
              "region": "us-east-1",
              "scheme": "https"
          }
      }
  }
}
* -i instanceIdentifier
* this is the RDS instance name you'd like to backup
*
* -n numberofBackups
* this is the number of backups you'd like to keep for that instance 
* 
* so a full example:
* php RDS_snapshot_management.php -c config.json -i dbname -n 20
* @author Michael Slate <slatem@gmail.com>
* Twitter: @michaelslate 
*/

require_once __DIR__ .'/../../vendor/autoload.php';
use Aws\Rds\RdsClient;
$options = getopt("c:i:n:");
$config = $options['c'];
$instanceIdentifier = $options['i'];
$numberBackups = $options['n'];
if (empty($instanceIdentifier) || empty($config) || !is_numeric($numberBackups)){
	die("USAGE: php RDS_snapshot_management.php -c [config file in json format] -i [db instance identifier] -n [number of backups you'd like to keep]\n");
}
$aws = Aws\Common\Aws::factory($config);
$rds = $aws->get('rds');

$snapshots = $rds->describeDBSnapshots();
$snaps=$snapshots->getAll();
$numberOfBackups=0;
$mysnapshots = array();
foreach($snaps['DBSnapshots'] as $snap){
	$status = $snap['Status'];
	$type = $snap['SnapshotType'];
	$time = $snap['SnapshotCreateTime'];
	$instance = $snap['DBInstanceIdentifier'];
	$name = $snap['DBSnapshotIdentifier'];
	if ($status == "creating" && $instance == $instanceIdentifier)
		die("creating snapshot - cannot initiate backup");
	if (strpos($name,$instanceIdentifier."-") !== 0)
		continue;
	if ($instance == $instanceIdentifier)
		$numberOfBackups++;
	$mysnapshots[] = $snap;
}

while(count($mysnapshots) > $numberBackups){
	usort($mysnapshots,"snap_cmp");
	//DBSnapshotIdentifier
	$to_delete = end($mysnapshots);
	$id = $to_delete['DBSnapshotIdentifier'];
        if (empty($id))
		die("Empty Snapshot ID encountered!! Could not delete old snapshot ID: DOWN DOWN DOWN\n");
	$rds->deleteDBSnapshot($to_delete);
	unset($mysnapshots[count($mysnapshots)-1]);
}
$new_snapshot_id = $instanceIdentifier."-".date("m-d-y-H-i");
$rds->createDBSnapshot(array(
	"DBSnapshotIdentifier"=>$new_snapshot_id,
	"DBInstanceIdentifier"=>$instanceIdentifier
	));


function snap_cmp($a,$b){
	if (preg_replace("/[^0-9]/","",$a['SnapshotCreateTime']) < preg_replace("/[^0-9]/","",$b['SnapshotCreateTime']))
		return true;
	else   
		return false;

}


?>
