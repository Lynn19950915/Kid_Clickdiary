<?php
require_once("../rsa/rsa.php");   
// 封裝好的 PHP 多檔案上傳 function
include_once 'upload_func.php';
// ini_set('display_errors', 1);
// error_reporting(~0);
$servername = "localhost";
$username   = "***";
$password   = "***";
$dbname     = "***";


$conn = mysqli_connect($servername, $username, $password, $dbname);
if (mysqli_connect_errno())
{
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

// Change character set to utf8
mysqli_set_charset($conn,"utf8");
if($_SESSION['account']['id_level'] == 2){
	$sql = "SELECT * FROM project WHERE active = 1";
	$result = $conn->query($sql);
	$prj = [];
	foreach($result as $key => $value){
		array_push($prj, $value);
	}
	$prj = json_encode($prj);

}else{
	$sql = "SELECT * FROM project WHERE active = 1 AND prjId = ".$_SESSION['account']['prjid']."";
	
	$result = $conn->query($sql);
	$prj = [];
	foreach($result as $key => $value){
		array_push($prj, $value);
	}
	$prj = json_encode($prj);

}

$sql = "SELECT prjid FROM questionnaire";
$result = $conn->query($sql);
$ques = [];
foreach($result as $key => $value){
	array_push($ques, $value);
}
$ques = json_encode($ques);

$sql = "SELECT * FROM content";
$result = $conn->query($sql);
$cont = [];
foreach($result as $key => $value){
	array_push($cont, $value);
}
$cont = json_encode($cont);

if(isset($_POST['prjid_select'])){
	$sql = "SELECT * FROM prjwork WHERE prjid = ".$_POST['prjid_select']."";	
	// print_r($sql);
	$result = $conn->query($sql);
	$prjwork = [];
	foreach($result as $key => $value){
		array_push($prjwork, $value);
	}
	$prjwork = json_encode($prjwork);
	echo $prjwork;
	exit();
}

// if(isset($_POST['tableClick'])){
// 	$sql = "SELECT * FROM prjwork WHERE prjid = ".$_SESSION['account']['prjid']."";	
// 	// print_r($sql);
// 	$result = $conn->query($sql);
// 	$prjwork = [];
// 	foreach($result as $key => $value){
// 		array_push($prjwork, $value);
// 	}
// 	$prjwork = json_encode($prjwork);
// }


$sql = "SELECT * FROM account WHERE active = 1 AND id_level < 2";
$result = $conn->query($sql);
$account = [];
foreach($result as $key => $value){
	array_push($account, $value);
}
$account = json_encode($account);

//上傳問卷
if(isset($_FILES['csv_file'])){
	// 1. Upload File to Server
	$files = getFiles();
	$res = uploadFile($files[0]);


	$prjid = $_POST['prjid'];	// 對應之專案ID
	$fname = $_POST['fname'];	// 上傳之csv檔名 
	$fpath = $res['dest'];		// 上傳後csv路徑
	// print_r($fname);
	// 2. Process By R
	putenv('PATH="C:\Program Files\R\R-3.5.1\bin\x64"');
	exec("chcp 65001;");
	exec("Rscript upload_questionnaire_test.r ".$prjid." ".$fname." ".$fpath, $return);
	
	// Return Insert or Update
	echo $return[0];
	// // print_r($return);
	exit();
}

if(isset($_POST['submit'])){

	$sql = "INSERT INTO `project` (`prjId`, `prjName`, `style`) VALUES (:v1, :v2, :v3)";
	$stmt=$db->prepare($sql);
	
	$stmt->bindParam(':v1', $_POST['prjNum']);
	$stmt->bindParam(':v2', $_POST['prjName']);
	$stmt->bindParam(':v3', $_POST['prjStyle']);
	
	try {
		$stmt -> execute();		
		echo '建立成功';
		echo '建立成功';
		echo '建立成功';
	} catch (Exception $e) {
		print_r($e);
	}	

	
	exit;
}
// 上傳抽樣規則
if(isset($_FILES['sample_rule'])){
	// 1. Upload File to Server
	$files = getFiles();
	$res   = uploadFile($files[0]);
	$prjid = $_POST['prjid'];	// 對應之專案ID
	$fname = $_POST['fname'];	// 上傳之csv檔名 
	$fpath = $res['dest'];		// 上傳後csv路徑
	// print_r($fpath);
	// 2. Process By R/ Python
	putenv('PATH="C:\Program Files\R\R-3.5.1\bin\x64"');
	exec("chcp 65001;");
	exec("Rscript upload_sample_rule.r ".$prjid." ".$fname." ".$fpath, $return);
	// Return Insert or Update
	
	echo $return[0];
	exit();
}
//上傳訪問紀錄
if(isset($_FILES['record'])){
	// 1. Upload File to Server
	$files = getFiles();
	$res = uploadFile($files[0]);
	
	$prjid = $_POST['prjid'];	// 對應之專案ID
	$qid = $_POST['qid'];	// 對應之專案ID
	$fname = $_POST['fname'];	// 上傳之csv檔名 
	$fpath = $res['dest'];		// 上傳後csv路徑

	// 2. Process By R/ Python
	putenv('PATH="C:\Program Files\R\R-3.5.1\bin\x64"');
	exec("chcp 65001;");
	exec("Rscript upload_surveyrecord.r ".$prjid." ".$fname." ".$fpath." ".$qid, $return);

	// Return Insert or Update
	// print_r($return);
	echo $return[0];

	exit();
}
	


//上傳訪員
if(isset($_FILES['member'])){
	
	// 重新建構上傳檔案 array 格式
	$files = getFiles();
	$res = uploadFile($files[0]);
	$path = $res['dest'];
	$check_new = is_null(explode('_', explode('.', $path)[0])[1]);

	if (!empty($res['dest'])) {

		if($check_new != 1){
			$url = "C:/inetpub/wwwroot/00_domain/capi/root/".$path;
			$file_handle = fopen($url, "r");
			$num_of_line = 0;
			$new_count = 0;
			while (!feof($file_handle)){

				$line_of_text = fgetcsv($file_handle, 2048, ",");
				$num_of_line = $num_of_line + 1;

				if($num_of_line > 1 && $line_of_text!=FALSE){
					
					$line_of_text[3] = iconv(mb_detect_encoding($line_of_text[3], mb_detect_order(), true), "UTF-8", $line_of_text[3]);
					$line_of_text[5] = iconv(mb_detect_encoding($line_of_text[5], mb_detect_order(), true), "UTF-8", $line_of_text[5]);
					
					
					$cnt = 0;
					$sql_str = null;
					$eachRecord = null;
					if($line_of_text[0] != '' && gettype($line_of_text[0]) == "string"){
						
						$eachRecord = $eachRecord."$line_of_text[0]";
						
						$cnt += 1;
						if($cnt != 0){
							$sql_str = $sql_str."`prjid`";
						}
					}
					if($line_of_text[1] != '' && gettype($line_of_text[1]) == "string"){
						
						$eachRecord = $eachRecord.","."'$line_of_text[1]'";
						$cnt += 1;
						if($cnt != 0){
							$sql_str = $sql_str.",`username`";
						}
					}
					if ($line_of_text[2] != ""  && gettype($line_of_text[2]) == "string") {
						$eachRecord = $eachRecord.","."'$line_of_text[2]'";
						$cnt += 1;
						if($cnt != 0){
							$sql_str = $sql_str.",`password`";
						}
					}
					if ($line_of_text[3] != ""&& gettype($line_of_text[3]) == "string") {
						$eachRecord = $eachRecord.","."'$line_of_text[3]'";
						$cnt += 1;
						if($cnt != 0){
							$sql_str = $sql_str.",`name`";
						}
					}
					if ($line_of_text[4] != ""&& gettype($line_of_text[4]) == "string") {
						$eachRecord = $eachRecord.","."'$line_of_text[4]'";
						$cnt += 1;
						if($cnt != 0){
							$sql_str = $sql_str.",`phone`";
						}
					}
					if ($line_of_text[5] != ""&& gettype($line_of_text[5]) == "string") {
						$eachRecord = $eachRecord.","."'$line_of_text[5]'";
						$cnt += 1;
						if($cnt != 0){
							$sql_str = $sql_str.",`address`";
						}
					}
					
					$eachRecord = $eachRecord.",".'0';
					$sql_str = $sql_str.",`id_level`";
					$eachRecord = $eachRecord.","."'".date("Y-m-d H:i:s",time())."'";
					$sql_str = $sql_str.",`createtime`";
					$eachRecord = $eachRecord.",".'1';
					$sql_str = $sql_str.",`active`";

					$sql = "SELECT * FROM `account` WHERE `username` = '".$line_of_text[1]."' AND `prjid` = '".$line_of_text[0]."'";
					$result = $conn->query($sql);
					
					if ($result->num_rows == 0) {
						$sql = "insert into `account` (".$sql_str.") values ( ".$eachRecord." )"; 
						$stmt=$db->prepare($sql);
						$stmt->execute();
						$new_count++;
					}


					
				}
					
			}
			
			echo "<br><h3>已插入 ".$new_count." 筆訪員</h3><br>";
			echo "<br><h3>5秒後重新導回列表</h3><br>";
			echo "<meta http-equiv='refresh' content='5;url=index.php?prjManage'>";
			fclose($file_handle);
			

		}else{
			$url = "C:/inetpub/wwwroot/00_domain/capi/root/".$path;
			$file_handle = fopen($url, "r");
			$num_of_line = 0;
			$tmp = $_SESSION['account']['prjid'];
			$sql = "DELETE FROM `account` where prjid = ".$tmp." AND id_level = 0"; 
			$conn->query($sql);
			while (!feof($file_handle)){

				$line_of_text = fgetcsv($file_handle, 2048, ",");
				$num_of_line = $num_of_line + 1;

				if($num_of_line > 1 && $line_of_text!=FALSE){
					
					$line_of_text[3] = iconv(mb_detect_encoding($line_of_text[3], mb_detect_order(), true), "UTF-8", $line_of_text[3]);
					$line_of_text[5] = iconv(mb_detect_encoding($line_of_text[5], mb_detect_order(), true), "UTF-8", $line_of_text[5]);
					
					
					$cnt = 0;
					$sql_str = null;
					$eachRecord = null;
					if($line_of_text[0] != '' && gettype($line_of_text[0]) == "string"){
						
						$eachRecord = $eachRecord."$line_of_text[0]";
						
						$cnt += 1;
						if($cnt != 0){
							$sql_str = $sql_str."`prjid`";
						}
					}
					if($line_of_text[1] != '' && gettype($line_of_text[1]) == "string"){
						
						$eachRecord = $eachRecord.","."'$line_of_text[1]'";
						$cnt += 1;
						if($cnt != 0){
							$sql_str = $sql_str.",`username`";
						}
					}
					if ($line_of_text[2] != ""  && gettype($line_of_text[2]) == "string") {
						$eachRecord = $eachRecord.","."'$line_of_text[2]'";
						$cnt += 1;
						if($cnt != 0){
							$sql_str = $sql_str.",`password`";
						}
					}
					if ($line_of_text[3] != ""&& gettype($line_of_text[3]) == "string") {
						$eachRecord = $eachRecord.","."'$line_of_text[3]'";
						$cnt += 1;
						if($cnt != 0){
							$sql_str = $sql_str.",`name`";
						}
					}
					if ($line_of_text[4] != ""&& gettype($line_of_text[4]) == "string") {
						$eachRecord = $eachRecord.","."'$line_of_text[4]'";
						$cnt += 1;
						if($cnt != 0){
							$sql_str = $sql_str.",`phone`";
						}
					}
					if ($line_of_text[5] != ""&& gettype($line_of_text[5]) == "string") {
						$eachRecord = $eachRecord.","."'$line_of_text[5]'";
						$cnt += 1;
						if($cnt != 0){
							$sql_str = $sql_str.",`address`";
						}
					}
					
					$eachRecord = $eachRecord.",".'0';
					$sql_str = $sql_str.",`id_level`";
					$eachRecord = $eachRecord.","."'".date("Y-m-d H:i:s",time())."'";
					$sql_str = $sql_str.",`createtime`";
					$eachRecord = $eachRecord.",".'1';
					$sql_str = $sql_str.",`active`";

					$sql = "insert into `account` (".$sql_str.") values ( ".$eachRecord." )"; 
					$stmt=$db->prepare($sql);
					$stmt->execute();
				}
					
			}
			echo "<br><h3>已新增訪員名單</h3><br>";
			echo "<br><h3>5秒後重新導回列表</h3><br>";
			echo "<meta http-equiv='refresh' content='5;url=index.php?prjManage'>";

			fclose($file_handle);
		}


		
	}
	
	
	exit;
}
//上傳樣本

if(isset($_FILES['sample'])){
	
	// 重新建構上傳檔案 array 格式
	$files = getFiles();
	$res = uploadFile($files[0]);
	$path = $res['dest'];
	$check_new = is_null(explode('_', explode('.', $path)[0])[1]);
	// print_r($check_new);
	if (!empty($res['dest'])) {
		if($check_new != 1){
			$url = "C:/inetpub/wwwroot/00_domain/capi/root/".$path;
			$file_handle = fopen($url, "r");
			$tmp= $_SESSION['account']['prjid'];
			while (!feof($file_handle) ) 
			{
				$line_of_text = fgetcsv($file_handle, 2048, ",");
				$num_of_line = $num_of_line + 1;
				$new_count = 0;
				if($num_of_line > 1 && $line_of_text!=FALSE) 
				{
						
						$line_of_text[5] = iconv(mb_detect_encoding($line_of_text[5], mb_detect_order(), true), "UTF-8", $line_of_text[5]);
						
						$sql_str = null;
						$eachRecord = null;
						$cnt = 0;
						if($line_of_text[0] != '' && gettype($line_of_text[0]) == "string"){
							
							$eachRecord = $eachRecord."$line_of_text[0]";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str."`prjid`";
							}
						}
						if($line_of_text[1] != '' && gettype($line_of_text[1]) == "string"){
							
							$eachRecord = $eachRecord.","."'$line_of_text[1]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`username`";
							}
						}
						if ($line_of_text[2] != "" && gettype($line_of_text[2]) == "string") {

							$eachRecord = $eachRecord.","."'$line_of_text[2]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`Sid`";
							}
						}
						if ($line_of_text[3] != "" && gettype($line_of_text[3]) == "string") {
							$eachRecord = $eachRecord.","."$line_of_text[3]";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`Qid`";
							}
						}

						if ($line_of_text[4] != "" && gettype($line_of_text[4]) == "string") {
							$eachRecord = $eachRecord.","."'$line_of_text[4]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`areaid`";
							}
						}
						
						if ($line_of_text[5] != "" && gettype($line_of_text[5]) == "string") {
							$eachRecord = $eachRecord.","."'$line_of_text[5]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`address`";
							}
						}
						if ($line_of_text[6] != "" && gettype($line_of_text[6]) == "string") {
							$eachRecord = $eachRecord.","."'$line_of_text[6]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`sample_rule`";
							}
						}
						
						$eachRecord = $eachRecord.",".'0';
						$sql_str = $sql_str.",`status`";
						$sql = "SELECT * FROM `prjwork` WHERE prjid = '".$line_of_text[0]."' and `Sid` = '".$line_of_text[2]."'";
						
						$result = $conn->query($sql);
						// print_r($result->num_rows );
						if ($result->num_rows == 0) {
							$sql = "insert into `prjwork` (".$sql_str.") values ( ".$eachRecord." )";
							$conn->query($sql);
							$new_count++;
						}
						print_r($sql);
				}
				
			}
			fclose($file_handle);
			
			echo "<br><h3>已插入 ".$new_count." 筆樣本</h3><br>";
			echo "<br><h3>5秒後重新導回列表</h3><br>";
			echo "<meta http-equiv='refresh' content='5;url=index.php?prjManage'>";
		}else{
			$url = "C:/inetpub/wwwroot/00_domain/capi/root/".$path;
			
			$file_handle = fopen($url, "r");

			$tmp= $_SESSION['account']['prjid'];

			$sql = "DELETE FROM `prjwork` where prjid = ".intval($tmp).""; 
			$conn->query($sql);
			
			$sql = "DELETE FROM `answer` where prjid = ".intval($tmp).""; 
			$conn->query($sql);

			$sql = "DELETE FROM `access_record` where prjid = ".intval($tmp).""; 
			$conn->query($sql);
			$num_of_line = 0;
			while (!feof($file_handle) ) 
			{
				$line_of_text = fgetcsv($file_handle, 2048, ",");
				$num_of_line = $num_of_line + 1;

				if($num_of_line > 1 && $line_of_text!=FALSE) 
				{
						
						$line_of_text[5] = iconv(mb_detect_encoding($line_of_text[5], mb_detect_order(), true), "UTF-8", $line_of_text[5]);
						
						$sql_str = null;
						$eachRecord = null;
						$cnt = 0;
						if($line_of_text[0] != '' && gettype($line_of_text[0]) == "string"){
							
							$eachRecord = $eachRecord."$line_of_text[0]";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str."`prjid`";
							}
						}
						if($line_of_text[1] != '' && gettype($line_of_text[1]) == "string"){
							
							$eachRecord = $eachRecord.","."'$line_of_text[1]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`username`";
							}
						}
						if ($line_of_text[2] != "" && gettype($line_of_text[2]) == "string") {

							$eachRecord = $eachRecord.","."'$line_of_text[2]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`Sid`";
							}
						}
						if ($line_of_text[3] != "" && gettype($line_of_text[3]) == "string") {
							$eachRecord = $eachRecord.","."$line_of_text[3]";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`Qid`";
							}
						}

						if ($line_of_text[4] != "" && gettype($line_of_text[4]) == "string") {
							$eachRecord = $eachRecord.","."'$line_of_text[4]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`areaid`";
							}
						}
						
						if ($line_of_text[5] != "" && gettype($line_of_text[5]) == "string") {
							$eachRecord = $eachRecord.","."'$line_of_text[5]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`address`";
							}
						}
						if ($line_of_text[6] != "" && gettype($line_of_text[6]) == "string") {
							$eachRecord = $eachRecord.","."'$line_of_text[6]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`sample_rule`";
							}
						}
						
						$eachRecord = $eachRecord.",".'0';
						$sql_str = $sql_str.",`status`";


						
						
						$sql = "insert into `prjwork` (".$sql_str.") values ( ".$eachRecord." )";
						// print_r($sql);
						$conn->query($sql);
				}
				
			}
			fclose($file_handle);
			echo "<br><h3>已新增樣本名單</h3><br>";
			echo "<br><h3>5秒後重新導回列表</h3><br>";
			echo "<meta http-equiv='refresh' content='5;url=index.php?prjManage'>";
		}
	
	}

	exit;
}

//上傳戶籍樣本
if(isset($_FILES['sample2'])){
	
	// 重新建構上傳檔案 array 格式
	$files = getFiles();
	$res = uploadFile($files[0]);
	$path = $res['dest'];
	$check_new = is_null(explode('_', explode('.', $path)[0] )[1]);
	// print_r($check_new);
	if (!empty($res['dest'])) {
		if($check_new != 1){
			$url = "C:/inetpub/wwwroot/00_domain/capi/root/".$path;
			$file_handle = fopen($url, "r");
			$tmp= $_SESSION['account']['prjid'];
			while (!feof($file_handle) ) 
			{
				$line_of_text = fgetcsv($file_handle, 2048, ",");
				$num_of_line = $num_of_line + 1;
				$new_count = 0;
				if($num_of_line > 1 && $line_of_text!=FALSE) 
				{
						
						
						$line_of_text[5] = iconv(mb_detect_encoding($line_of_text[5], mb_detect_order(), true), "UTF-8", $line_of_text[5]);
						$line_of_text[4] = iconv(mb_detect_encoding($line_of_text[4], mb_detect_order(), true), "UTF-8", $line_of_text[4]);
						$line_of_text[6] = iconv(mb_detect_encoding($line_of_text[6], mb_detect_order(), true), "UTF-8", $line_of_text[6]);
						$line_of_text[7] = iconv(mb_detect_encoding($line_of_text[7], mb_detect_order(), true), "UTF-8", $line_of_text[7]);
						$line_of_text[8] = iconv(mb_detect_encoding($line_of_text[8], mb_detect_order(), true), "UTF-8", $line_of_text[8]);
						$sql_str = null;
						$eachRecord = null;
						$cnt = 0;
						if($line_of_text[0] != '' && gettype($line_of_text[0]) == "string"){
							
							$eachRecord = $eachRecord."$line_of_text[0]";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str."`prjid`";
							}
						}
						if($line_of_text[1] != '' && gettype($line_of_text[1]) == "string"){
							
							$eachRecord = $eachRecord.","."'$line_of_text[1]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`username`";
							}
						}
						if ($line_of_text[2] != "" && gettype($line_of_text[2]) == "string") {

							$eachRecord = $eachRecord.","."$line_of_text[2]";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`Sid`";
							}
						}
						if ($line_of_text[3] != "" && gettype($line_of_text[3]) == "string") {
							$eachRecord = $eachRecord.","."$line_of_text[3]";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`Qid`";
							}
						}

						if ($line_of_text[4] != "" && gettype($line_of_text[4]) == "string") {
							$eachRecord = $eachRecord.","."'$line_of_text[4]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`gender`";
							}
						}
						
						if ($line_of_text[5] != "" && gettype($line_of_text[5]) == "string") {
							$eachRecord = $eachRecord.","."'$line_of_text[5]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`name`";
							}
						}
						if ($line_of_text[6] != "" && gettype($line_of_text[6]) == "string") {
							$eachRecord = $eachRecord.","."'$line_of_text[6]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`birthday`";
							}
						}
						if ($line_of_text[7] != "" && gettype($line_of_text[7]) == "string") {
							$eachRecord = $eachRecord.","."'$line_of_text[7]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`address`";
							}
						}
						if ($line_of_text[8] != "" && gettype($line_of_text[8]) == "string") {
							$eachRecord = $eachRecord.","."'$line_of_text[8]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`target_mobile`";
							}
						}

						$eachRecord = $eachRecord.",".'0';
						$sql_str = $sql_str.",`status`";
						$sql = "SELECT * FROM `prjwork` WHERE `Sid` = '".$line_of_text[2]."'";
						
						$result = $conn->query($sql);
						
						if ($result->num_rows == 0) {
							$sql = "insert into `prjwork` (".$sql_str.") values ( ".$eachRecord." )";
							$conn->query($sql);
							$new_count++;
						}
				}
				
			}
			fclose($file_handle);
			
			echo "<br><h3>已插入 ".$new_count." 筆樣本</h3><br>";
			echo "<br><h3>5秒後重新導回列表</h3><br>";
			echo "<meta http-equiv='refresh' content='5;url=index.php?prjManage'>";
		}else{
			$url = "C:/inetpub/wwwroot/00_domain/capi/root/".$path;
			
			$file_handle = fopen($url, "r");

			$tmp= $_SESSION['account']['prjid'];

			$sql = "DELETE FROM `prjwork` where prjid = ".intval($tmp).""; 
			$conn->query($sql);
			
			$sql = "DELETE FROM `answer` where prjid = ".intval($tmp).""; 
			$conn->query($sql);

			$sql = "DELETE FROM `access_record` where prjid = ".intval($tmp).""; 
			$conn->query($sql);
			$num_of_line = 0;
			while (!feof($file_handle) ) 
			{
				$line_of_text = fgetcsv($file_handle, 2048, ",");
				$num_of_line = $num_of_line + 1;

				if($num_of_line > 1 && $line_of_text!=FALSE) 
				{
						
						$line_of_text[5] = iconv(mb_detect_encoding($line_of_text[5], mb_detect_order(), true), "UTF-8", $line_of_text[5]);
						$line_of_text[4] = iconv(mb_detect_encoding($line_of_text[4], mb_detect_order(), true), "UTF-8", $line_of_text[4]);
						$line_of_text[6] = iconv(mb_detect_encoding($line_of_text[6], mb_detect_order(), true), "UTF-8", $line_of_text[6]);
						$line_of_text[7] = iconv(mb_detect_encoding($line_of_text[7], mb_detect_order(), true), "UTF-8", $line_of_text[7]);
						$line_of_text[8] = iconv(mb_detect_encoding($line_of_text[8], mb_detect_order(), true), "UTF-8", $line_of_text[8]);
						$sql_str = null;
						$eachRecord = null;
						$cnt = 0;
						if($line_of_text[0] != '' && gettype($line_of_text[0]) == "string"){
							
							$eachRecord = $eachRecord."$line_of_text[0]";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str."`prjid`";
							}
						}
						if($line_of_text[1] != '' && gettype($line_of_text[1]) == "string"){
							
							$eachRecord = $eachRecord.","."'$line_of_text[1]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`username`";
							}
						}
						if ($line_of_text[2] != "" && gettype($line_of_text[2]) == "string") {

							$eachRecord = $eachRecord.","."$line_of_text[2]";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`Sid`";
							}
						}
						if ($line_of_text[3] != "" && gettype($line_of_text[3]) == "string") {
							$eachRecord = $eachRecord.","."$line_of_text[3]";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`Qid`";
							}
						}

						if ($line_of_text[4] != "" && gettype($line_of_text[4]) == "string") {
							$eachRecord = $eachRecord.","."'$line_of_text[4]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`gender`";
							}
						}
						
						if ($line_of_text[5] != "" && gettype($line_of_text[5]) == "string") {
							$eachRecord = $eachRecord.","."'$line_of_text[5]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`name`";
							}
						}
						if ($line_of_text[6] != "" && gettype($line_of_text[6]) == "string") {
							$eachRecord = $eachRecord.","."'$line_of_text[6]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`birthday`";
							}
						}
						if ($line_of_text[7] != "" && gettype($line_of_text[7]) == "string") {
							$eachRecord = $eachRecord.","."'$line_of_text[7]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`address`";
							}
						}
						if ($line_of_text[8] != "" && gettype($line_of_text[8]) == "string") {
							$eachRecord = $eachRecord.","."'$line_of_text[8]'";
							$cnt += 1;
							if($cnt != 0){
								$sql_str = $sql_str.",`target_mobile`";
							}
						}
						$eachRecord = $eachRecord.",".'0';
						$sql_str = $sql_str.",`status`";


						
						
						$sql = "insert into `prjwork` (".$sql_str.") values ( ".$eachRecord." )";
						print_r($sql);
						$conn->query($sql);
						// if ($conn->query($sql) === TRUE) {

						// 	// echo $sql;
						// } else {
						// 	echo "Error: " . $sql . "<br>" . $conn->error;
						// }
				}
				
			}
			fclose($file_handle);
			// echo "<br><h3>已新增樣本名單</h3><br>";
			// echo "<br><h3>5秒後重新導回列表</h3><br>";
			// echo "<meta http-equiv='refresh' content='5;url=index.php?prjManage'>";
		}
	
	}

	exit;
}
if(isset($_POST['submitOpen'])){

	$sql = "UPDATE prjwork SET status = 1 WHERE prjid = ".$_POST['openVal']."";
	$stmt=$db->prepare($sql);
	$stmt->execute();
	$sql = "UPDATE account SET active = 1 WHERE prjid = ".$_POST['openVal']."";
	$stmt=$db->prepare($sql);
	$stmt->execute();
	$sql = "UPDATE `project` SET `active` = 1 WHERE `prjId` = ".$_POST['openVal']."";
	$stmt=$db->prepare($sql);
	$stmt->execute();
	exit;
};
if(isset($_POST['submitClose'])){

	// $sql = "UPDATE prjwork SET status = 0 WHERE prjid = ".$_POST['closeVal']."";
	// $stmt=$db->prepare($sql);
	// $stmt->execute();
	// $sql = "UPDATE account SET active = 0 WHERE prjid = ".$_POST['closeVal']." AND id_level < 1";
	// $stmt=$db->prepare($sql);
	// $stmt->execute();
	$sql = "UPDATE `project` SET `active` = 0 WHERE `prjId` = ".$_POST['closeVal']."";
	$stmt=$db->prepare($sql);
	$stmt->execute();
	
	exit;
};
if(isset($_POST['submitDelete'])){

	$sql = "DELETE FROM prjwork WHERE prjid = ".$_POST['deleteVal']."";
	$stmt=$db->prepare($sql);
	$stmt->execute();
	$sql = "DELETE FROM account WHERE prjid = ".$_POST['deleteVal']."  AND id_level < 1";
	$stmt=$db->prepare($sql);
	$stmt->execute();
	$sql = "DELETE FROM `project` WHERE `prjId` = ".$_POST['deleteVal']."";
	$stmt=$db->prepare($sql);
	$stmt->execute();
	
	$sql = "DELETE FROM `questionnaire` WHERE `prjid` = ".$_POST['deleteVal']."";
	$stmt=$db->prepare($sql);
	$stmt->execute();
	$sql = "DELETE FROM `answer` WHERE `prjid` = ".$_POST['deleteVal']."";
	$stmt=$db->prepare($sql);
	$stmt->execute();
	$sql = "DELETE FROM `access_record` WHERE `prjid` = ".$_POST['deleteVal']."";
	$stmt=$db->prepare($sql);
	$stmt->execute();
	$sql = "DELETE FROM `access_paper` WHERE `prjid` = ".$_POST['deleteVal']."";
	$stmt=$db->prepare($sql);
	$stmt->execute();
	$sql = "DELETE FROM `downloads` WHERE `prjid` = ".$_POST['deleteVal']."";
	$stmt=$db->prepare($sql);
	$stmt->execute();
	$sql = "DELETE FROM `problem` WHERE `prjid` = ".$_POST['deleteVal']."";
	$stmt=$db->prepare($sql);
	$stmt->execute();
	exit;
};
if(isset($_POST['submitDown'])){
	
	$v = $_POST['downVal'];
	$v2 = $_POST['email'];
	// C:\Users\scsCAPI\AppData\Local\conda\conda\envs\py35
	$status = exec("C:/Users/scsCAPI/AppData/Local/Programs/Python/Python35/python C:/inetpub/wwwroot/00_domain/capi/root/php/downloads.py $v $v2");
	print_r($status);
	// print_r(json_encode($status,JSON_UNESCAPED_UNICODE));

 exit();

}

if(isset($_POST['content'])){
	$sql = "SELECT * FROM `content` WHERE `prjid` = '".$_POST['contentName']."'";		
	$result = $conn->query($sql);
	// echo $result->num_rows ;
	if ($result->num_rows == 0) {
		
		$sql = "insert into `content` (`prjid`, `content`) values ( ".$_POST['contentName'].", '".$_POST['content']."' )";
		if ($conn->query($sql) === TRUE) {

			echo "1";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}
	else{
		$sql = "UPDATE `content` SET `content` = '".$_POST['content']."' WHERE `prjid` = '".$_POST['contentName']."'";
		if ($conn->query($sql) === TRUE) {

			echo "0";
		} else {
			echo "Error: " . $sql . "<br>" . $conn->error;
		}
	}

	
	exit();
}
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
	    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	    <title>CAPI</title>
		
		<!-- Bootstrap -->
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
		<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>

		<!-- datatable -->
		<link href="https://cdnjs.cloudflare.com/ajax/libs/uikit/3.0.0-beta.35/css/uikit.min.css" rel="stylesheet" type="text/css" />
		<link href="https://cdn.datatables.net/1.10.19/css/dataTables.uikit.min.css" rel="stylesheet" type="text/css" />

		<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js" type="text/javascript"></script>
		<script src="https://cdn.datatables.net/1.10.19/js/dataTables.uikit.min.js" type="text/javascript"></script>
		<script src="https://capi.geohealth.tw/jQuery-Plugin-To-Export-Table-Data-To-CSV-File-table2csv/src/table2csv.js"></script>

		 <!-- Bootstrap Select -->
	    <script src="https://capi.geohealth.tw/js/bootstrap-select.min.js"></script>
	    <link   rel="stylesheet" href="https://capi.geohealth.tw/css/bootstrap-select.min.css">
		 <script src="https://cdn.datatables.net/select/1.2.7/js/dataTables.select.min.js"></script>
		<!--confirm-->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.css">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.0/jquery-confirm.min.js"></script>
		
		<!-- loadash -->
		<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

		<style type="text/css">
			/*body{
				overscroll-behavior-y: contain;
			}*/
			.dataTables_wrapper {
				width:110em;
				/*margin-left: 400px;
				margin-right: 200px;*/
				margin:auto;
				font-family: tahoma;
				font-size: 16px;
				position: relative;
				clear: both;
				zoom: 1;
			}
			.tableBtn{
				width: 3em;
			}
			.file{
			    position: absolute;
			    width: 1px;
			    height: 1px;
			    padding: 0;
			    margin: -1px;
			    
			    clip: rect(0,0,0,0);
			    border: 0;
			}
			.custom-file-upload {
			 
			    cursor: pointer;
			}
			.confirm{
				width: 45%;
			}
			.col-md-auto{
			    padding-bottom: 2px;
			}
			.larger-font {
				font-size: 100%;
			}
			.btn_upload_qn{
				height: 3em;
			}
			.uploadRecord{
				height: 3em;
			}
			#myModalt {
				position: absolute;
				left: 50%;
				top: 50%;
				z-index: 999999;
				display: none;
				border: 16px solid #f3f3f3;
				border-radius: 50%;
				border-top: 16px solid #3498db;
				width: 120px;
				height: 120px;
				-webkit-animation: spin 2s linear infinite; /* Safari */
				animation: spin 2s linear infinite;
				background: no-repeat center center;
			}
			#myModalt2{
				width: 100%;
				height: 500%;
				position: absolute;
				z-index: 999998 ;
				background-color: #cccccc;
				opacity:0.5;
				display: none;
			}
			.card-header{
                text-align:center;
                
            }
            #container_1{
            	
                width: 30%;
                padding-left: 0;
                padding-right: 0;
                position: absolute;
                z-index: 999;
                text-align: center;
            }
            .panel-body{
                padding-left:2px;
                padding-right:2px;
            }
            .panel-inner{
                float:left;
                width:5.5em;
                font-size:0.5em;
                text-align:center;
                margin-bottom: 0px;
            }
            .outer{

                float:right;
                font-weight: bold;
                text-align:center;
                width: 25.9em;
                border: 1px solid #000;
                z-index: 100;
                position: absolute;
                cursor: move;
                top: 70px; 
                left: 20px;
                display: block!important;
                width: fit-content;
            }
			/* Safari */
			@-webkit-keyframes spin {
			  0% { -webkit-transform: rotate(0deg); }
			  100% { -webkit-transform: rotate(360deg); }
			}

			@keyframes spin {
			  0% { transform: rotate(0deg); }
			  100% { transform: rotate(360deg); }
			}
		@media only screen and (min-width: 321px) and (max-width: 768px) {
			.dataTables_wrapper {
				width:30em;
				margin:auto;
				font-family: tahoma;
				font-size: 6px;
				position: relative;
				clear: both;
				zoom: 1;
			}
			
		}
		</style>
	</head>
  <body >
  <div id="myModalt">
    
  </div>
  <div id="myModalt2">
    
  </div>

<!-- Modal -->
<div class="modal fade" id="editContent" role="dialog">
	<div class="modal-dialog">

		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<!-- <button type="button" class="close" data-dismiss="modal">&times;</button> -->
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body">
				<textarea class="form-control" id="content" rows="8"></textarea>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" id="contentSubmit" data-dismiss="modal">確認</button>
			</div>
		</div>
	  
	</div>
</div>
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarText">
			<ul class="navbar-nav mr-auto">

				<li class="nav-item">
					<button class="btn btn-outline-danger my-2 my-sm-0" data-toggle="modal" data-target="#myModal"  style="width: 6em;">建立專案</button>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="https://capi.geohealth.tw/index.php?main">主選單</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="https://capi.geohealth.tw/index.php?prjManage">專案管理</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="https://capi.geohealth.tw/index.php?manpage">帳號名單</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="https://capi.geohealth.tw/index.php?samplepage">樣本名單</a>
				</li>

			</ul>
			<span class="navbar-text">
				<a href="https://capi.geohealth.tw/index.php?lg">登出</a> 
			</span>
		</div>
	</nav> 

	<div class="container" id="container_1">      
        <div class="card bg-light outer" id="dashBoard" style="cursor: move;z-index: 10;">
            <div class="card-header">專案進度
                <button type="button" id="clossBtn" class="close" data-target="#dashBoard" data-dismiss="alert">
                    <span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
                </button>
            </div>  
            <div>        
                <div class="card panel-inner">
                    <div class="card bg-warning panel-inner" >
                        <div class="card-header inner">樣本再訪</div>
                    </div>  
                    <div class="card border-warning panel-inner">
                        <div class="card-header inner ne">0</div>
                    </div>
                </div>
                <div class="card panel-inner">
                    <div class="card bg-danger panel-inner" >
                        <div class="card-header inner ">樣本拒訪</div>
                    </div>  
                    <div class="card border-danger panel-inner">
                        <div class="card-header inner dont">0</div>
                    </div>
                </div>
                <!-- <div class="card panel-inner" >
                    <div class="card bg-info panel-inner" >
                        <div class="card-header inner">樣本完訪</div>
                    </div>  
                    <div class="card border-info panel-inner">
                        <div class="card-header inner good">0</div>
                    </div>
                </div> -->
                <div class="card panel-inner">
                    <div class="card bg-success panel-inner" >
                        <div class="card-header inner">完成問卷</div>
                    </div>  
                    <div class="card border-success panel-inner">
                        <div class="card-header inner success">0</div>
                    </div>
                </div>
                <!-- <div class="card panel-inner">
                    <div class="card bg-default panel-inner" >
                        <div class="card-header inner">訪問紀錄</div>
                    </div>  
                    <div class="card border-default panel-inner">
                        <div class="card-header inne recr">0</div>
                    </div>
                </div> -->
                <div class="card panel-inner">
                    <div class="card bg-primary panel-inner" >
                        <div class="card-header inner">填預過錄</div>
                    </div>  
                    <div class="card border-primary panel-inner">
                        <div class="card-header inner upld">0</div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
	<div class="main-login main-center modal fade" id="myModal" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
    			<div class="modal-header">
      				<button type="button" class="close" data-dismiss="modal">&times;</button>
      				<h4 class="modal-title" style="font-weight: bold;text-align: center;"></h4>
    			</div>
    			<div class="modal-body">
					<form id="form" class="needs-validation" role="form" novalidate>
					
						<div class="form-group" >
							<label class="col-sm-3 control-label" >專案名稱</label>
							<div class="col-md-9">
								<div class="btn-group">
									<input id="prjName" type="text" class="form-control">
								</div>
							</div>
						</div>
						
						<div class="form-group" >
							<label class="col-sm-3 control-label" >專案代碼</label>
							<div class="col-md-9">
								<div class="btn-group">
									<input id="prj_code" type="text" class="form-control">
								</div>
							</div>
						</div>
						<div class="form-group" >
							<label class="col-sm-3 control-label" >專案型態</label>
							<div class="col-md-9">
								<div class="btn-group">
									<select class="selectpicker" title="請選擇..." id="prjStyle">
										<option value="0">地址抽樣</option>
										<option value="1">個資抽樣</option>
									</select>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button class="btn btn-primary" id="createprj">確認</button>
				        </div>
					</form>
					
				</div>
			</div>
    	</div>
	</div>
	<div class="main-login main-center modal fade" id="myModal2" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
    			<div class="modal-header">
      				<button type="button" class="close" data-dismiss="modal">&times;</button>
      				<h4 class="modal-title" style="font-weight: bold;text-align: center;"></h4>
    			</div>
    			<div class="modal-body">
					<form id="form" class="needs-validation" role="form" novalidate>
						<div class="form-group" >
							<label class="col-sm-3 control-label" >Email</label>
							<div class="col-md-9">
								<div class="btn-group">
									<input id="email" type="text" class="form-control">
								</div>
							</div>
						</div>

						<div class="modal-footer">
							<button class="btn btn-primary emailSubmit" >確認</button>
				        </div>
					</form>
					
				</div>
			</div>
    	</div>
	</div>
	<script type="text/javascript">
		var cont = <?echo $cont;?>;
		function functionClose(x){
			console.log(x)
			$.confirm({
			    title: '警告!',
			    content: "確認是否關閉專案?",
			    buttons: {
			        confir: {
			            text: '確認',
			            btnClass: 'btn-blue',
			      
			            action: function(){

			        		$.ajax({						
								type:"POST",
								data:{submitClose:1,
									  closeVal:x
								}
							})
							.done(function (response){
								console.log(response)
								// if(response == "0"){
								// 	$.alert({
								// 		content: '資料已新增!',
								// 		buttons: {
								// 	        confirm: function () {
								// 	            location.reload()
								// 	        }
								// 	    }
								// 	})
									
									
								// }
								// else if(response == "1"){
								// 	$.alert({
								// 		content: '資料已更新!',
								// 		buttons: {
								// 	        confirm: function () {
								// 	            location.reload()
								// 	        }
								// 	    }
								// 	})
								// }
								
								
								
							})
							.fail(function(response){
								console.log(response)
								// $.alert("發生錯誤");
								// location.reload()
							});
			        		
			            }

			        },
			        cancle: {
			            text: '取消',
			            btnClass: 'btn-red',
			            // keys: ['enter', 'shift'],
			            action: function(){
			                $.alert('已取消!!');
			            }
			        }
			    }
			    
			});
		}
		function functionOpen(x){
			console.log(x)
			$.confirm({
				    title: '警告!',
				    content: "確認是否開啟專案?",
				    buttons: {
				        confir: {
				            text: '確認',
				            btnClass: 'btn-blue',
				      
				            action: function(){
				        		$.ajax({						
									type:"POST",
									data:{submitOpen:1,
										  openVal:x
									}
								})
								.done(function (response){
									console.log(response)
									$.alert("專案已開啟");
									location.reload()
									
									
								})
								.fail(function(response){
									console.log(response)
									$.alert("發生錯誤");l
									location.reload()
								});
				        		
				            }

				        },
				        cancle: {
				            text: '取消',
				            btnClass: 'btn-red',
				            // keys: ['enter', 'shift'],
				            action: function(){
				                $.alert('已取消!!');
				            }
				        }
				    }
				    
				});
		}
		function functionDelete(x){
			console.log(x)
			$.confirm({
			    title: '警告!',
			    content: "確認是否刪除專案?",
			    buttons: {
			        confir: {
			            text: '確認',
			            btnClass: 'btn-blue',
			      
			            action: function(){
			        		$.ajax({						
								type:"POST",
								data:{submitDelete:1,
									  deleteVal:x
								}
							})
							.done(function (response){
								console.log(response)
								$.alert("專案已刪除");
								location.reload()
								
								
							})
							.fail(function(response){
								console.log(response)
								$.alert("發生錯誤");l
								location.reload()
							});
			        		
			            }

			        },
			        cancle: {
			            text: '取消',
			            btnClass: 'btn-red',
			            // keys: ['enter', 'shift'],
			            action: function(){
			                $.alert('已取消!!');
			            }
			        }
			    }
			    
			});
		}
		function functionEdit(x){
			$('#editContent').find('.modal-title').html('')
			var html_tmp = $('#editContent').find('.modal-title').html();
			var cont_tmp = _.filter(cont, {'prjid': x.toString()})
			console.log('test:', cont_tmp)
			if(cont_tmp.length != 0 ){
				$('#content').val(cont_tmp[0].content)	
			}
			
			
			$('#editContent').find('.modal-title').html('專案 '+x+' 開始訪問內容')
			$('#contentSubmit').attr('name', x)
		}

		function update_url(url) {
            history.pushState(null, null, url);
        }
		function functionDown(x){
			// $('.emailSubmit').attr({'id':x})
			window.location = 'https://capi.geohealth.tw/uploads/prj_'+x+'.csv';
		}

		function btn_upload_qn(x){	    		
	    		console.log('btn_upload_qn', 123)
				var files =  $('#uploadsName').prop('files');
	            var data = new FormData();
	            
	            data.append('csv_file', files[0]);

	            var prjid = x
	    		var selectedFilename = $('#uploadsName').get(0).files[0].name;
	    		data.append('prjid', prjid)
	    		data.append('fname', selectedFilename)
	    		console.log('btn_upload_qn', data)
	    		//
	    		$('#myModalt').show()
				$('#myModalt2').show()
				//
	            $.ajax({
	                type: 'POST',
	                url: "",
	                data: data,
	                cache: false,
	                processData: false,
	                contentType: false,
	                success: function (data) {
	                	console.log('btn_upload_qn', data)
	                	var result = _.split(data,'_')
	                	// console.log(result)
	                	var result_msg = "專案編號: " + result[1] + "<br>問卷編號: " + result[2]
	                	if(result[0] == 'Update'){
	                		$.confirm({
	                			title: '問卷設定已更新',
	                			content: result_msg,
	                			buttons: {
	                				'OK': function(){
	                					window.location.reload();
	                				}
	                			}
	                		});
	                	}else if(result[0] == 'Insert'){
	                		$.confirm({
	                			title: '問卷設定已上傳',
	                			content: result_msg,
	                			buttons: {
	                				'OK': function(){
	                					window.location.reload();
	                				}
	                			}
	                		});
	                	}
	                }
	            });
		}

		function uploadRecord(x){
			var files =  $('#record').prop('files');
            var data = new FormData();
            data.append('record', files[0]);

            var prjid = x
    		var selectedFilename = $('#record').get(0).files[0].name;
    		var qid = $('#record').get(0).files[0].name.split('_')[1].split('.')[0];
    		console.log("test", qid)
    		data.append('prjid', prjid)
    		data.append('qid', qid)
    		data.append('fname', selectedFilename)
    		//
    		$('#myModalt').show()
			$('#myModalt2').show()
			//
            $.ajax({
                type: 'POST',
                url: "",
                data: data,
                cache: false,
                processData: false,
                contentType: false,
                success: function (data) {
                	console.log(data)
                	if(data == 'Update'){
                		$.alert('問卷設定已更新');location.reload();
                	}else if(data == 'Insert'){
                		$.alert('問卷設定已上傳');location.reload();
                	}
                }
            });
		}
		function uploadRule(x){
			var files =  $('#rule').prop('files');
			var prjid = x
    		var selectedFilename = $('#rule').get(0).files[0].name;
            var data = new FormData();
            data.append('sample_rule', files[0]);
    		data.append('prjid', prjid)
    		data.append('fname', selectedFilename)
    		console.log(data)
    		//
    		$('#myModalt').show()
			$('#myModalt2').show()
			//
			console.log(files)
            $.ajax({
                type: 'POST',
                url: "",
                data: data,
                cache: false,
                processData: false,
                contentType: false,
                success: function (data) {
                	
                	if (data == 'Update') {
                		$.confirm({
                			title: '抽樣規則已更新',
                			content: '',
                			buttons: {
                				'OK': function(){
                					// window.location.reload();
                				}
                			}
                		});
                	}else if(data == 'Insert'){
                		$.confirm({
                			title: '抽樣規則已上傳',
                			content: '',
                			buttons: {
                				'OK': function(){
                					// window.location.reload();
                				}
                			}
                		});
                	}else{
                		$.alert(data)
                	}
                }
            });
		}

		$(document).ready(function() {
			
			$('#contentSubmit').on('click', function(evt){
				evt.preventDefault()
				var content = $('#content').val()
				var contentName = $('#contentSubmit').attr('name')
				console.log(123)
				$.confirm({
				    title: '',
				    content: "是否設定 專案編號: "+contentName+" 的開始訪問內容?",
				    buttons: {
				        confir: {
				            text: '確認',
				            btnClass: 'btn-blue',
				            action: function(){
				            	
				        		$.ajax({						
									type:"POST", 
									data:{content:content,
										  contentName:contentName
									}
								})
								.done(function (response){
									console.log(response)
									if(response == '1'){
										$.alert('資料已新增')
									}else if(response == '0'){
										$.alert('資料已更新')
									}
									
									
								})
								.fail(function(response){
									console.log(response)
									// $.alert("發生錯誤或專案未完成");
									// location.reload()
								});
				        		
				            }

				        },
				        cancle: {
				            text: '取消',
				            btnClass: 'btn-red',
				            
				            action: function(){
				                $.alert('已取消!!');
				            }
				        }
				    }
				    
				});
			})

			$('.emailSubmit').on('click', function(evt){
				evt.preventDefault()
				x = $(this).attr('id')
				$.confirm({
				    title: '警告!',
				    content: "確認是否下載 專案編號: "+x+" 的全部檔案?",
				    buttons: {
				        confir: {
				            text: '確認',
				            btnClass: 'btn-blue',
				      
				            action: function(){
				            	$('#myModalt').show()
								$('#myModalt2').show()
				        		$.ajax({						
									type:"POST", 
									data:{submitDown:1,
										  downVal:x,
										  email:$('#email').val()
									}
								})
								.done(function (response){
									console.log(response)
									location.reload()
									
								})
								.fail(function(response){
									console.log(response)
									$.alert("發生錯誤或專案未完成");
									// location.reload()
								});
				        		
				            }

				        },
				        cancle: {
				            text: '取消',
				            btnClass: 'btn-red',
				            
				            action: function(){
				                $.alert('已取消!!');
				            }
				        }
				    }
				    
				});
			})
			dragElement(document.getElementById("container_1"));

	        function dragElement(elmnt) {
	            var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
	            if (document.getElementById(elmnt.id + "header")) {
	            // if present, the header is where you move the DIV from:
	                document.getElementById(elmnt.id + "header").onmousedown = dragMouseDown;
	            } else {
	            // otherwise, move the DIV from anywhere inside the DIV: 
	                elmnt.onmousedown = dragMouseDown;
	            }

	            function dragMouseDown(e) {
	                e = e || window.event;
	                e.preventDefault();
	                // get the mouse cursor position at startup:
	                pos3 = e.clientX;
	                pos4 = e.clientY;
	                document.onmouseup = closeDragElement;
	                // call a function whenever the cursor moves:
	                document.onmousemove = elementDrag;
	            }

	            function elementDrag(e) {
	                e = e || window.event;
	                e.preventDefault();
	                // calculate the new cursor position:
	                pos1 = pos3 - e.clientX;
	                pos2 = pos4 - e.clientY;
	                pos3 = e.clientX;
	                pos4 = e.clientY;
	                // set the element's new position:
	                elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
	                elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
	            }

	            function closeDragElement() {
	                // stop moving when mouse button is released:
	                document.onmouseup = null;
	                document.onmousemove = null;
	            }
	        }

			var acc_level = <?echo $_SESSION['account']['id_level'] ?>;
			var acc_prjid = <?echo $_SESSION['account']['prjid'] ?>;
			console.log('test:',acc_prjid)
			
			console.log('acc_level')
			console.log(acc_level)

			var echoPrj = <?echo $prj;?>;
			var echoPrjwork
			var account = <?echo $account;?>;
			var ques = <?echo $ques;?>;
			var table 

			console.log('ehco sql project:', echoPrj)
			// console.log('ehco sql project work:', echoPrjwork)
			console.log('ehco sql account:', account)
			console.log('ehco sql cont:', cont)
			for(var key1 in echoPrj){
                opt = $('<option>', {value : echoPrj[key1].prjId, text : echoPrj[key1].prjId})
                $("#prjid_select").append( 
                    $(opt) 
                );
            }
            $("#prjid_select").selectpicker("refresh");
            $('#prjid_select').on('change', function(){
            	$.ajax({						
					type:"POST",
					data:{prjid_select:$('#prjid_select').val()
					}
				})
				.done(function (t){
					echoPrjwork = JSON.parse(t)
					// console.log('t', echoPrjwork)
					var prj = _.filter(echoPrj, {'prjId': $("#prjid_select").val()})
					for(var i = 0; i < prj.length; i++){

						prj[i]['prjworkNum'] = echoPrjwork.length
						prj[i]['qid'] = _.filter(ques, { 'prjid': prj[i].prjId}).length
						prj[i]['accNum'] = _.filter(account, { 'prjid': prj[i].prjId}).length
					}
					$('#example').DataTable().destroy();
					table = $('#example').DataTable( {

						data: prj,
						"dom": 'Bfrtip',
						"select":true,
						"columns":[
							
							{"data":"prjId", "className": "text-center"},
							{"data":"prjName", "className": "text-center"},
							{"data":"accNum", "className": "text-center"},
							{"data":"prjworkNum", "className": "text-center"},
							{"data":"qid", "className": "text-center"},
							{ "mdata":null,
							  "mRender":function (dat, type,  row) { 
							  		if(row['style'] == 0){
							  			var style = '地址抽樣';
							  		}else if(row['style'] == 1){
							  			var style = '個資抽樣';
							  		}else{
							  			var style = '地址抽樣'
							  		}
									return style;
										    
								}, "className": "text-center"
							},
							{ "mdata":null,
							  "mRender":function (dat, type,  row) { 
									return  "<form method='post' enctype='multipart/form-data'>"+								
											'<button id="btnUploads" class="btn btn-info tableBtn" type="button" data-toggle="tooltip" data-placement="top" title="選擇問卷">'+
										    "<label for='uploadsName'><input id='uploadsName' name='uploadsName' type='file'  hidden/><img src='icon/notepad.png' class='icon_picture'></label></button>"+
										    '<a> </a>'+
										    '<button class="btn tableBtn btn_upload_qn" type="button" data-toggle="tooltip" data-placement="top" onClick="btn_upload_qn('+row['prjId']+')" title="上傳問卷" name="'+row['prjId']+'">'+
										    "<img src='icon/file.png' class='icon_picture'>"+
										    '</button></form>'
										    
								}, "className": "text-center"
							},
							{ "mdata":null,
							  "mRender":function (dat, type,  row) { 
							  		return  '<form method="post" enctype="multipart/form-data">'+								
											'<button id="btnRecord" class="btn btn-danger tableBtn" type="button" data-toggle="tooltip" data-placement="top" title="選擇訪問記錄">'+
										    '<label id="test" for="record"><input id="record" name="record"  type="file" hidden/><img src="icon/contract.png" class="icon_picture"></label></button>'+
										    '<a> </a>'+
										    '<button class="btn tableBtn uploadRecord" type="button" data-toggle="tooltip" data-placement="top" onClick="uploadRecord('+row['prjId']+')" title="上傳訪問記錄" name="'+row['prjId']+'">'+
										    '<img src="icon/file.png" class="icon_picture"></button></form>'

										    // <label for="uploadRecord"><input type="submit" id="uploadRecord" name="uploadRecord" value="'+row['prjId']+'"  hidden/>
								},  "className": "text-center"
							},
							{ "mdata":null,
							  "mRender":function (dat, type,  row) { 
							  		return  '<form method="post" enctype="multipart/form-data">'+								
											'<button id="btnSampleRule" class="btn btn-warning tableBtn" type="button" data-toggle="tooltip" data-placement="top" title="選擇抽樣規則">'+
										    '<label id="test" for="rule"><input id="rule" name="rule"  type="file" hidden/><img src="icon/sampling.png" class="icon_picture"></label></button>'+
										    '<a> </a>'+
										    '<button class="btn tableBtn uploadRule" type="button" data-toggle="tooltip" data-placement="top"  onClick="uploadRule('+row['prjId']+')" title="上傳抽樣規則" name="'+row['prjId']+'">'+
										    '<img src="icon/file.png" class="icon_picture"></button></form>'
								},  "className": "text-center"
							},
							{ "mdata":null,
							  "mRender":function (dat, type,  row) { 
							  		return  '<form method="post" enctype="multipart/form-data">'+								
											'<button id="btnMember" class="btn btn-success tableBtn" type="button" data-toggle="tooltip" data-placement="top" title="選擇訪員">'+
										    '<label id="test" for="member"><input id="member" name="member"  type="file" hidden/><img src="icon/boss.png" class="icon_picture"></label></button>'+
										    '<a> </a>'+
										    '<button class="btn tableBtn" type="button" data-toggle="tooltip" data-placement="top" title="上傳訪員">'+
										    '<label for="uploadMember"><input type="submit" id="uploadMember" name="uploadMember" value="'+row['prjId']+'"  hidden/><img src="icon/file.png" class="icon_picture"></label></button></form>'

										    
								},  "className": "text-center"
							},
							
							{ "mdata":null,
							  "mRender":function (dat, type,  row) { 

							  		if(row['style'] == 0){
							  			return  "<form method='post' enctype='multipart/form-data'>"+								
												'<button id="btnSample" class="btn btn-primary tableBtn" type="button" data-toggle="tooltip" data-placement="top" title="選擇樣本">'+
											    "<label for='sample'><input id='sample' name='sample' type='file' hidden/><img src='icon/network.png' class='icon_picture'></label></button>"+
											    '<a> </a>'+
											    '<button class="btn tableBtn" type="button" data-toggle="tooltip" data-placement="top" title="上傳樣本">'+
											    '<label for="uploadSample"><input type="submit" id="uploadSample" name="uploadSample" value="'+row['prjId']+'" hidden/><img src="icon/file.png" class="icon_picture"></label></button></form>'
									}else if(row['style'] == 1){
										return  "<form method='post' enctype='multipart/form-data'>"+								
												'<button id="btnSample" class="btn btn-primary tableBtn" type="button" data-toggle="tooltip" data-placement="top" title="選擇樣本">'+
											    "<label for='sample2'><input id='sample2' name='sample2' type='file' hidden/><img src='icon/network.png' class='icon_picture'></label></button>"+
											    '<a> </a>'+
											    '<button class="btn tableBtn" type="button" data-toggle="tooltip" data-placement="top" title="上傳樣本">'+
											    '<label for="uploadSample"><input type="submit" id="uploadSample" name="uploadSample" value="'+row['prjId']+'" hidden/><img src="icon/file.png" class="icon_picture"></label></button></form>'
									}
									

								}, "className": "text-center"
							},
							{ "mdata":null,
							  "mRender":function (dat, type,  row) { 
									return  '<div class="col-md-auto">'+
											'<button id="open"  data-toggle="modal" data-target="#editContent" name="'+row['prjId']+'" onClick="functionEdit('+row['prjId']+')" class="btn btn-light controlBtn" data-toggle="tooltip" data-placement="top" title="編輯樣本資訊內容" style="width: 3em;"/><img src="icon/contract (1).png"class="icon_picture"></button>'+
										    '<a> </a>'+
											'<button id="down" onClick="functionDown('+row['prjId']+')" class="btn btn-light controlBtn"  data-toggle="tooltip" data-placement="top" title="下載檔案" style="width: 3em;"/><img src="icon/down-arrow.png"class="icon_picture"></button>'+
										    '<a> </a>'+
											// '<button id="open" name="'+row['prjId']+'" onClick="functionOpen('+row['prjId']+')" class="btn btn-light controlBtn" data-toggle="tooltip" data-placement="top" title="開啟專案" style="width: 3em;"/><img src="icon/open.png"class="icon_picture"></button>'+
										 //    '<a> </a>'+
											'<button id="close" name="'+row['prjId']+'" onClick="functionClose('+row['prjId']+')" class="btn btn-light controlBtn" data-toggle="tooltip" data-placement="top" title="關閉專案" style="width: 3em;"/><img src="icon/cross.png"class="icon_picture"></button>'+
										    '<a> </a>'+
										    '<button id="delete" name="'+row['prjId']+'" onClick="functionDelete('+row['prjId']+')" class="btn btn-light controlBtn" data-toggle="tooltip" data-placement="top" title="刪除專案" style="width: 3em;" /><img src="icon/bin.png" class="icon_picture"></button></div>'
								}, "className": "text-center"
							}
						],
						//data-toggle="modal" data-target="#myModal2" 
						// "columnDefs": [onClick="functionDown('+row['prjId']+')"
			   //          {
			   //              "targets": [ 8 ],
			   //              "visible": dt_control,
			   //              "searchable": dt_control
			   //          }
			   //      ]
					});

	 				table.on( 'select', function ( e, dt, type, indexes ) {
			                
			                $("#dashBoard").replaceWith(divClone);

			                var t = table.rows( indexes ).data().toArray()
			                // console.log('echoPrjwork', echoPrjwork)
			                // console.log('t', t)
			                var l = _.filter(echoPrjwork, {'prjid': t[0].prjId}).length
			                // var s = _.filter(echoPrjwork, {'prjid': t[0].prjId, 'prjwork_status': '1'}).length
			                var r = _.filter(echoPrjwork, {'prjid': t[0].prjId, 'prjwork_status': '11'}).length
			                var u = _.filter(echoPrjwork, {'prjid': t[0].prjId, 'prjwork_status': '100'}).length
			                var n = 0
			                var d = 0
			                var f = 0
			                _.filter(echoPrjwork, function(o){
			                    if(o.prjid == t[0].prjId){
			                       // console.log(o.Sid,o.status)
			                       if(o.status.indexOf("需再訪") >= 0){
			                            n += 1
			                        }else if(o.status.indexOf("不需再訪") >= 0){
			                            d += 1
			                        }else if(o.status.indexOf("完訪") >= 0){
			                            f += 1
			                        } 
			                    }
			                    
			                })
			                $('.success').text(u)
			                // $('.recr').text(s)
			                $('.upld').text(r)
			                $('.ne').text(n)
			                $('.dont').text(d)
			                // $('.good').text(f)
			        } )
				})
				.fail(function(response){
					console.log(response)
					$.alert("發生錯誤");
					// location.reload()
				});
            })
			$("#createprj").on('click',function(evt){
				evt.preventDefault();
				var check2 = _.filter(echoPrj, { 'prjId': acc_prjid.toString()}).length
				var prjStyle = $('#prjStyle').val()
				console.log(acc_prjid, check2)
				if(check2 != 0 && acc_level > 2 ){
					$.alert('已新增過專案，如要新增請換帳號!!')
				}else{
					$.confirm({
					    title: '警告!',
					    content: "確認是否建立專案?",
					    buttons: {
					        confir: {
					            text: '確認',
					            btnClass: 'btn-blue confirm',
					            action: function(){					     
					            	$.ajax({
					            		type:"POST",
					            		data:{submit:1,
					            			  prjName:$("#prjName").val(),
					            			  // prjNum:acc_prjid,
					            			  prjNum:$("#prj_code").val(),
					            			  prjStyle:prjStyle							           
					            		}
					            	})

					            	.done(function(response){$.alert("專案已成立!!");console.log(response);location.reload();})//location.reload();
									.fail(function(response){$.alert("失敗!!");console.log(response);location.reload();});	//location.reload();
					            }
					        },
					        cancle: {
					            text: '取消',
					            btnClass: 'btn-red confirm',
					            // keys: ['enter', 'shift'],
					            action: function(){
					                $.alert('已取消!!');
					            }
					        }
					    }
						    
					});
				}
				
			})

		
		
		// console.log('echoPrj', echoPrj)
		
		

	    	$('[data-toggle="tooltip"]').tooltip();   

	   //  	$(".btn_upload_qn").on('click', function(evt){
	   //  		evt.preventDefault();	    		
	   //  		console.log('btn_upload_qn', 123)
				// var files =  $('#uploadsName').prop('files');
	   //          var data = new FormData();
	            
	   //          data.append('csv_file', files[0]);

	   //          var prjid = $(this).attr('name')
	   //  		var selectedFilename = $('#uploadsName').get(0).files[0].name;
	   //  		data.append('prjid', prjid)
	   //  		data.append('fname', selectedFilename)
	   //  		console.log('btn_upload_qn', data)
	   //  		//
	   //  		$('#myModalt').show()
				// $('#myModalt2').show()
				// //
	   //          $.ajax({
	   //              type: 'POST',
	   //              url: "",
	   //              data: data,
	   //              cache: false,
	   //              processData: false,
	   //              contentType: false,
	   //              success: function (data) {
	   //              	console.log('btn_upload_qn', data)
	   //              	var result = _.split(data,'_')
	   //              	// console.log(result)
	   //              	var result_msg = "專案編號: " + result[1] + "<br>問卷編號: " + result[2]
	   //              	if(result[0] == 'Update'){
	   //              		$.confirm({
	   //              			title: '問卷設定已更新',
	   //              			content: result_msg,
	   //              			buttons: {
	   //              				'OK': function(){
	   //              					window.location.reload();
	   //              				}
	   //              			}
	   //              		});
	   //              	}else if(result[0] == 'Insert'){
	   //              		$.confirm({
	   //              			title: '問卷設定已上傳',
	   //              			content: result_msg,
	   //              			buttons: {
	   //              				'OK': function(){
	   //              					window.location.reload();
	   //              				}
	   //              			}
	   //              		});
	   //              	}
	   //              }
	   //          });
	    		
	   //  	})

	    	

	    	var divClone = $("#dashBoard").clone(); 
	       
	        if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ){
	        	$("#container_1").hide()
	        }
	});
	</script>
	<div class="row" style="height:1.5em"></div>
	
    <!-- <div class="container">
		<div class="main-login main-center"> 
			<form id="form" class="form-horizontal form" role="form">
				<fieldset id='field'> -->
					<!-- <div class="row" style="height:1.5em"></div> -->
					<div style="left: 150px;" class="col-sm-12">
						<select id="prjid_select" class="selectpicker" data-live-search="true"  title="選擇專案編號" >							
						</select>
					</div>
					

					<!-- <div class="row" style="height:1.5em"></div> -->
					<table id="example" class="uk-overflow-auto uk-table uk-table-hover uk-table-striped larger-font"  cellspacing="0">
				        <thead>
				            <tr>
								<th>專案編號</th>
							    <th>專案名稱</th>
							    <th>訪員數</th> 
								<th>樣本數</th>
								<th>問卷數</th>
								<th>專案型態</th>
							    <th>匯入問卷</th>
							    <th>訪問紀錄</th>
							    <th>抽樣規則</th>
				                <th>匯入訪員</th>
				                <th>匯入樣本</th>
							    <th>專案操作</th>
				            </tr>
				        </thead>
				        
				    </table>
				<!-- </fieldset>
			</form>
		</div> -->

	<!-- </div> -->
  </body>
  
</html>   
