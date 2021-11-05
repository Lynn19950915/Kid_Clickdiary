<?php
    session_start();
	include("db.php");

    if(!$_SESSION["acc_info"]["id"]){
		header("Location: ./index.php");
    }
    $project_id=isset($_GET["project_id"])?$_GET["project_id"]: 0;
    $dbbeta=$project_id."beta";

    $sql1="SELECT * FROM `:v1`";
    $stmt=$db->prepare($sql1);
    $stmt->bindParam(":v1", $dbbeta);
    $stmt->execute();

    $csv="record_id, time, record\n";
    $csv=iconv("utf-8", "gb2312", $csv);
    while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
        $record_id=iconv("utf-8", "gb2312", $row["record_id"]);
        $time=iconv("utf-8", "gb2312", $row["time"]);
        $csv.=$record_id.",".$time.",".$row["record"]."\n";
    }

    $filename=$project_id."beta.txt";
    header("Content-Description: File Transfer");
    header("Content-Type: application/octet-stream");
    header('Content-Disposition: attachment; filename="'.basename($filename).'"');
    header("Expires: 0");
    header("Cache-Control: must-revalidate");
    header("Pragma: public");
    echo $csv;
?>
