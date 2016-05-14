<?php
	$msc=include("/var/www/ServiceTags/mysqlConnect.php");
	$cookie_name = "numScans";
	if(ISSET($_COOKIE[$cookie_name])){
		$sessionScans = $_COOKIE[$cookie_name];
	}
	else{
		$sessionScans=0;
		setcookie($cookie_name,$sessionScans,time()+(1800),"/");
	}
	if ($msc->connect_errno) {
    		$postResult = "Failed to connect to MySQL: (".$msc->connect_errno.") ".$msc->connect_error;
	}

	if(ISSET($_GET['tag']) && strlen($_GET['tag'])>0){
		$sessionScans++;
		setcookie($cookie_name,$sessionScans,time()+(3600),"/");
		// Make it uniform, uppercase
		$tag = strtoupper($_GET['tag']);
		// Strip out anything that isn't a Letter or Number
		$tag = preg_replace("/[^a-zA-Z0-9\s]/", "", $tag);

		// Write a simple query
		$query = "SELECT COUNT('ServiceTag') AS 'TagFound' FROM TagList WHERE ServiceTag='".$tag."';";

		// Execute a query
		$result = $msc->query($query);

		// Get the data
		$data = $result->fetch_assoc();
	    	$found = $data['TagFound'];

		if($found==0){
			// The Service Tag was not found
			// Enter it in the table
			$add='INSERT INTO TagList VALUES ("'.$tag.'","Manually-Scanned","1");';
			$addResult = $msc->query($add);
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
			$query = "SELECT Found,WhichList FROM TagList WHERE ServiceTag='".$tag."';";
			$result = $msc->query($query);
			$row = $result->fetch_assoc();
			$found = $row['Found'];
			$list = $row['WhichList'];

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
				$postResult = "The Service Tag: <b>".$tag."</b> is ".$list." and had been marked as found already.";
			}
			else{
				$add='UPDATE TagList SET Found="1" WHERE ServiceTag="'.$tag.'";';
				$addResult = $msc->query($add);
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
		$result = $msc->query($query);
		$data = $result->fetch_assoc();
		$numInList = $data['NumInLists'];

		$query = "SELECT COUNT('ServiceTag') AS NumFound FROM TagList WHERE Found=1;";
		$result = $msc->query($query);
		$data = $result->fetch_assoc();
		$numFound = $data['NumFound'];
	}
	else{
		// Display info about existing information in the database
		$query = "SELECT COUNT('ServiceTag') AS NumInLists FROM TagList;";
		$result = $msc->query($query);
		$data = $result->fetch_assoc();
		$numInList = $data['NumInLists'];

		$query = "SELECT COUNT('ServiceTag') AS NumFound FROM TagList WHERE Found=1;";
		$result = $msc->query($query);
		$data = $result->fetch_assoc();
		$numFound = $data['NumFound'];
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
					<hr style="height:1px; display:block;"/>
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
					<hr/>
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
			</form>
			<div id="footer">
				Generated by <a href="http://www.phpform.org">pForm</a>
			</div>
		</div>
		<img id="bottom" src="bottom.png" alt="">
	</body>
</html>
