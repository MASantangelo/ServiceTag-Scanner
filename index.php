<?php
	// DB Connections
	include("/var/www/ServiceTags/mysqlConnect.php");
	$msc=mConnect();

	// Setup Some Functions
	function setCookies($c1_name,$c1_val,$c2_name,$c2_val,$ttl){
		$expiration = time()+$ttl;
		setcookie($c1_name,$c1_val,$expiration,"/");
		setcookie($c2_name,$c2_val,$expiration,"/");
	}
	function q_1result($dbCon,$query,$keyVal){
		$data = $dbCon->query($query);
		$data = $data->fetch_assoc();
		$data = $data[$keyVal];
		return($data);
	}
	function generateTable($dbCon,$query){
		$result = $dbCon->query($query);
		$sessionScannedTable = "<table border=1 rules=rows style='width:75%; margin:auto; '><tr align=center><th>Service Tag</th><th>List Found On</th><th>Order Number</th></tr>";
		while($row=($result->fetch_assoc())){
			$st = $row['ServiceTag'];
			$wl = $row['WhichList'];
			$onum = $row['OrderNumber'];
			if(!ISSET($onum)){
				$onum="Not Available";
			}
			if($wl=="Imported-Good"){
				$wl="Our Device";
				$rc = "green";
			}
			elseif($wl=="Imported-Bad"){
				$wl="Not Our Device";
				$rc = "red";
			}
			elseif($wl=="Manually-Scanned"){
				$wl="Not On Any List";
				$rc = "teal";
			}
			else{
				$wl="Unknown Error";
			}
			$sessionScannedTable.="<tr align=center style='background-color:".$rc."'><td>".$st."</td><td>".$wl."</td><td>".$onum."</td></tr>";
		}
		$sessionScannedTable.= "</table>";
		return($sessionScannedTable);
	}

	if(is_string($msc)==1) {
    		echo("Could not connect to MySQL Database: Likely incorrect username, password, or DB name.");
	}
	else{
		$cookie_name = "numScans";
		$cookie_id = "scan-id";
		if(ISSET($_COOKIE[$cookie_id])){
			$sessionScans = $_COOKIE[$cookie_name];
			$sessionId = $_COOKIE[$cookie_id];
		}
		else{
			// I wanted MySQL Time, Not Apache/PHP Time
			$cts = "SELECT CURRENT_TIMESTAMP;";
			$cts = q_1result($msc,$cts,"CURRENT_TIMESTAMP");
			$latest = date("Y-m-d H:i:s",strtotime($cts." - 30 minute"));

			$oldSes = sprintf("SELECT SessionID FROM Sessions WHERE LastScan > '%s';",$latest);
			$oldSes = q_1result($msc,$oldSes,"SessionID");

			if($oldSes){
				$sessionId = $oldSes;
				$oldData = sprintf("SELECT Count('ServiceTag') AS SesScans FROM TagList WHERE FoundInSession='%s';",$sessionId);
				$sessionScans = q_1result($msc,$oldData,"SesScans");
			}
			else{
				$sessionScans=0;
				$sessionId = uniqid('test-');
				$writeSession = sprintf("INSERT INTO Sessions VALUES ('%s',CURRENT_TIMESTAMP,NULL);",$sessionId);
				$result = $msc->query($writeSession);
			}
			setCookies($cookie_name,$sessionScans,$cookie_id,$sessionId,1800);
		}
		if(ISSET($_GET['tag']) && strlen($_GET['tag'])>0){
			// Make it uniform, uppercase
			$tag = strtoupper($_GET['tag']);
			// Strip out anything that isn't a Letter or Number
			$tag = preg_replace("/[^a-zA-Z0-9\s]/", "", $tag);
			$tag = $msc->real_escape_string($tag);

			// Write a simple query
			$query = sprintf("SELECT COUNT('ServiceTag') AS 'TagFound' FROM TagList WHERE ServiceTag='%s';",$tag);
			$found = q_1result($msc,$query,"TagFound");

			if($found==0){
				// The Service Tag was not found
				// Enter it in the table
				$add=sprintf("INSERT INTO TagList VALUES ('%s','Manually-Scanned',NULL,'1','%s');",$tag,$sessionId);
				$addResult = $msc->query($add);
				$sessionScans++;
				setCookies($cookie_name,$sessionScans,$cookie_id,$sessionId,1800);
				$update = sprintf("UPDATE Sessions SET LastScan = CURRENT_TIMESTAMP WHERE SessionID='%s';",$sessionId);
				$msc->query($update);
				if($addResult==1){
					$postResult = "The Service Tag: <b>".$tag."</b> was not previously imported. It has been added.";
				}
				else{
					$postResult = "The Service Tag: <b>".$tag."</b> has NOT been added to the list due to an SQL Error.";
				}
			}
			elseif($found==1){
				// The Service Tag was found
				// Check if it was already scanned.
				$query = sprintf("SELECT Found,WhichList FROM TagList WHERE ServiceTag='%s';",$tag);
				$found = q_1result($msc,$query,"Found");
				$list = q_1result($msc,$query,"WhichList");

				// Check Which List the Device was on
				// 3 Possibilites: Imported-Good, Imported-Bad, Manually-Scanned
				if($list == "Imported-Good"){
					$list = "a <font color='green'>GOOD DEVICE</font>";
				}
				elseif($list == "Imported-Bad"){
					$list = "<font color='red'>BAD DEVICE</font>";
				}
				else{
					$list = "<font color='blue'>A MANUALLY SCANNED DEVICE</font>";
				}
				// Check if this Device was already found
				// 2 Possibilites: 1 (Yes), 0 (No)
				if($found==1){
					$sessionScans++;
					setCookies($cookie_name,$sessionScans,$cookie_id,$sessionId,1800);
					$update = sprintf("UPDATE Sessions SET LastScan = CURRENT_TIMESTAMP WHERE SessionID='%s';",$sessionId);
					$msc->query($update);
					$postResult = "The Service Tag: <b>".$tag."</b> is ".$list." and had been marked as found already.";
				}
				else{
					$sessionScans++;
					$add = sprintf("UPDATE TagList SET Found='1',FoundInSession='%s' WHERE ServiceTag='%s';",$sessionId,$tag);
					$addResult = $msc->query($add);
					setCookies($cookie_name,$sessionScans,$cookie_id,$sessionId,1800);
					$update = sprintf("UPDATE Sessions SET LastScan = CURRENT_TIMESTAMP WHERE SessionID='%s';",$sessionId);
					$msc->query($update);
					if($addResult==1){
						$postResult = "The Service Tag: <b>".$tag."</b> is ".$list." and has been marked as Found.";
					}
					else{
						$postResult = "The Service Tag: <b>".$tag."</b> is ".$list." and  has NOT been marked as Found due to an SQL Error.";
					}
				}
			}
			else{
				// Note: We should never end up here.  Field ServiceTag is unique, primary key.  It should never return a count > 1.
				$postResult = "Ambiguous Data Received.  The Service Tag scanned was already in the database, but more than once.";
			}
			// Display info about existing information in the database
			$query = "SELECT COUNT('ServiceTag') AS NumInLists FROM TagList;";
			$numInList = q_1result($msc,$query,"NumInLists");

			$query = "SELECT COUNT('ServiceTag') AS NumFound FROM TagList WHERE Found=1;";
			$numFound = q_1result($msc,$query,"NumFound");

			$query = sprintf("SELECT ServiceTag,WhichList,OrderNumber FROM TagList WHERE (Found=1 AND FoundInSession='%s');",$sessionId);
			$sessionScannedTable = generateTable($msc,$query);
		}
		else{
			// Display info about existing information in the database
			$query = "SELECT COUNT('ServiceTag') AS NumInLists FROM TagList;";
			$numInList = q_1result($msc,$query,"NumInLists");

			$query = "SELECT COUNT('ServiceTag') AS NumFound FROM TagList WHERE Found=1;";
			$numFound = q_1result($msc,$query,"NumFound");

			$query = sprintf("SELECT ServiceTag,WhichList,OrderNumber FROM TagList WHERE (Found=1 AND FoundInSession='%s');",$sessionId);
			$sessionScannedTable = generateTable($msc,$query);
		}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Chromebook Service Tag Checker</title>
		<link rel="stylesheet" type="text/css" href="view.css" media="all">
		<script type="text/javascript" src="view.js"></script>
	</head>
	<body id="main_body" onLoad="document.forms.CSTC.tag.focus()">
		<img id="top" src="top.png" alt="">
		<div id="form_container">
			<h1><a>Chromebook Service Tag Checker</a></h1>
			<form name="CSTC" id="CSTC" class="appnitro" method="get" action="index.php">
				<div class="form_description">
					<h2>Chromebook Service Tag Checker</h2>
					<p>This form cross references Service Tags.  Enter a Service Tag to continue.</p>
					<hr style='height:1px; display: block;'/>
					<?php
						echo("<p>");
						if(ISSET($sessionScans)){
							echo($sessionScans." Devices Scanned In This Session. <br/>");
						}
						if(ISSET($numInList)){
							echo($numInList." Devices In The Master List.<br/>");
						}
						if(ISSET($numFound)){
							echo($numFound." Devices Found So Far.<br/>");
						}
						echo("</p>");
					?>
					<?php
						if(ISSET($postResult)){
							echo("<hr style='height:1px; display: block;'/>");
							echo("<p ><font size='+1'>".$postResult."</font></p>");
						}
					?>
					<hr style='height:1px; display: block;'/>
				</div>
				<ul >
					<li id="li_1" >
						<label class="description" for="element_1">Service Tag</label>
						<div>
							<input selected id="tag" name="tag" class="element text small" type="text" maxlength="10" value=""/>
						</div>
					</li>
					<li class="buttons">
						<input type="hidden" name="form_id" value="CSTC" />
						<input id="saveForm" class="button_text" type="submit" name="submit" value="Submit" />
					</li>
				</ul>
				<ul>
					<hr style='height:1px; display: block;'/>
					<b>Devices Scanned In This Session</b>
					<?php
						echo($sessionScannedTable);
					?>
				</ul>
			</form>
			<div id="footer">
				Generated by <a href="http://www.phpform.org">pForm</a>
			</div>
		</div>
		<img id="bottom" src="bottom.png" alt="">
	</body>
</html>

<?php
	$msc->close();
	}
?>
