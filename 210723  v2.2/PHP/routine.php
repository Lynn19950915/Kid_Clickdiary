<?php
    session_start();
	include("db.php");
	
	$filepath="C:/***";
	$p=scandir($filepath);
	foreach($p as $val){
		unlink($filepath.$val);
	}

	$end=date("Y-m-d", strtotime("-2 day"));
	$sql1="SELECT project_id FROM `project` WHERE end_date> :v1";
	$stmt=$db->prepare($sql1);
    $stmt->bindParam(":v1", $end);
    $stmt->execute();
	$row1=$stmt->fetchAll(PDO::FETCH_ASSOC);
	
	$i=0;
	while($i<count($row1)){
		$database=$row1[$i]['project_id']."final";
		$dir="C:/***";
		$filename=$dir.$row1[$i]['project_id'].".txt";

		if($fp=fopen($filename, "w+")){
			$csv="record_id, sample_id, time, record\r\n";
			$csv=iconv("utf-8", "gb2312", $csv);
			fwrite($fp, $csv);
			
			$sql2="SELECT * FROM `:v1`";
			$stmt=$db->prepare($sql2);
			$stmt->bindParam(":v1", $database);
			$stmt->execute();
			while($row2=$stmt->fetch(PDO::FETCH_ASSOC)){
				$record_id=iconv("utf-8", "gb2312", $row2["record_id"]);
				$sample_id=iconv("utf-8", "gb2312", $row2["sample_id"]);
				$time=iconv("utf-8", "gb2312", $row2["time"]);
				$csv=$record_id.",".$sample_id.",".$time.",".$row2["record"]."\r\n";
				fwrite($fp, $csv);
			}
			fclose($fp);
		}
		$i++;
	}
?>
