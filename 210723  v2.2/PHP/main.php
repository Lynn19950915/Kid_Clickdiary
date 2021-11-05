<?php
	session_start();
	include("db.php");
    include("upload.php");

    if(!$_SESSION["acc_info"]["id"]){
		header("Location: ./index.php");
    }

    if(isset($_POST["fetchProject"])){
        if($_SESSION["acc_info"]["level"]==1){
            $sql1="SELECT * FROM `project` LEFT JOIN `account` ON project.id=account.id";
            $stmt=$db->prepare($sql1);
            $stmt->execute();
            
            $json=array();
            while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
                $json[]=$row;
            }
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
        }else{
            $sql2="SELECT * FROM `project` LEFT JOIN `account` ON project.id=account.id WHERE account.id= :v1";
            $stmt=$db->prepare($sql2);
            $stmt->bindParam(":v1", $_SESSION["acc_info"]["id"]);
            $stmt->execute();
            
            $json=array();
            while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
                $json[]=$row;
            }
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
        }
        exit();
    }

    if(isset($_POST["project_name"])){      
        $sql3="INSERT INTO `project` VALUES (NULL, :v2, :v3, 1, NULL, NULL, :v7, 0)";
		$stmt=$db->prepare($sql3);
		$stmt->bindParam(":v2", $_POST['id']);
		$stmt->bindParam(":v3", $_POST["project_name"]);
		$stmt->bindParam(":v7", $_POST["sample_size"]);
		$stmt->execute();
		
		$sql4="SELECT * FROM `project` LEFT JOIN `account` ON project.id=account.id";
		$stmt=$db->prepare($sql4);
		$stmt->execute();
		
		$json=array();
		while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
			$json[]=$row;
		}
		echo json_encode($json, JSON_UNESCAPED_UNICODE);       
        exit();
	}

    if(isset($_POST["searchProject"])){
        $search=$_POST["search"];
        
        if($_SESSION["acc_info"]["level"]==1){
            $sql5="SELECT * FROM `project` LEFT JOIN `account` ON project.id=account.id WHERE project_name LIKE '%$search%' or name LIKE '%$search%'";
            $stmt=$db->prepare($sql5);
            $stmt->execute();
            
            $json=array();
            while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
                $json[]=$row;
            }
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
        }else{
            $sql6="SELECT * FROM `project` LEFT JOIN `account` ON project.id=account.id WHERE account.id= :v1 and (project_name LIKE '%$search%' or name LIKE '%$search%')";
            $stmt=$db->prepare($sql6);
            $stmt->bindParam(":v1", $_SESSION["acc_info"]["id"]);
            $stmt->execute();
            
            $json=array();
            while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
                $json[]=$row;
            }
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
        }
        exit();
	}

    if(isset($_FILES["csv_file1"])){
        $files=getFiles();
        $result=uploadFile($files[0]);
        
        if($result["err"]=="不支援的格式"){
            echo "Invalid Format";
            exit();
        }else if($result["err"]=="檔案容量過大"){
            echo "File Oversize";
            exit();
        }
        
		$filepath="C:/***"."/".$files[0]["name"];
        $handle=fopen($filepath, "r");
        $count=1;
        while(($data=fgetcsv2($handle))!=false){
            if($count==1){
                if($data[1]!="q_sn"){
                    echo "Header Q_sn";
                    exit();
                }else if($data[2]!="q_txt"){
                    echo "Header Q_txt";
                    exit();
                }else if($data[3]!="type"){
                    echo "Header Type";
                    exit();
                }else if($data[4]!="opt_txt"){
                    echo "Header Opt_txt";
                    exit();
                }else if($data[5]!="opt_value"){
                    echo "Header Opt_value";
                    exit();
                }else if($data[6]!="annotate"){
                    echo "Header Annotate";
                    exit();
                }else if($data[7]!="note"){
                    echo "Header Note";
                    exit();
                }else if($data[8]!="disjoint"){
                    echo "Header Disjoint";
                    exit();
                }else if($data[9]!="range_min"){
                    echo "Header Range_min";
                    exit();
                }else if($data[10]!="range_max"){
                    echo "Header Range_max";
                    exit();
                }else if($data[11]!="skip"){
                    echo "Header Skip";
                    exit();
                }else if($data[12]!="attach"){
                    echo "Header Attach";
                    exit();
                }
                $count++;
            }else{
				if($data[0]==""){
                    echo "Missing Q_id";
                    exit();
                }else if($data[1]==""){
                    echo "Missing Q_sn";
                    exit();
                }else if($data[2]==""){
                    echo "Missing Q_txt";
                    exit();
                }else if($data[3]==""){
                    echo "Missing Type";
                    exit();
                }else if(($data[3]==0|$data[3]==1)&$data[4]==""){
                    echo "Missing Opt_txt";
                    exit();
                }else if(($data[3]==0|$data[3]==1)&$data[5]==""){
                    echo "Missing Opt_value";
                    exit();
                }else if(($data[3]==0|$data[3]==1)&$data[7]==""){
                    echo "Missing Note";
                    exit();
                }else if($data[3]==1&$data[8]==""){
                    echo "Missing Disjoint";
                    exit();
				}else if(($data[3]==0|$data[3]==1)&$data[11]==""){
					echo "Missing Skip";
					exit();
                    
                }else if(!ctype_digit($data[0])){
                    echo "Invalid Q_id";
                    exit();
                }else if(!ctype_digit($data[1])){
                    echo "Invalid Q_sn";
                    exit();
                }else if(!ctype_digit($data[3])|$data[3]>9){
                    echo "Invalid Type";
                    exit();
                }else if($data[5]!=""&!ctype_digit($data[5])){
                    echo "Invalid Opt_value";
                    exit();
                }else if($data[7]!=""&(!ctype_digit($data[7])|$data[7]>1)){
                    echo "Invalid Note";
                    exit();
                }else if($data[8]!=""&(!ctype_digit($data[8])|$data[8]>1)){
                    echo "Invalid Disjoint";
                    exit();
                }else if($data[9]!=""&!ctype_digit($data[9])){
                    echo "Invalid Range_min";
                    exit();
                }else if($data[10]!=""&!ctype_digit($data[10])){
                    echo "Invalid Range_max";
                    exit();
                }else if($data[11]!=0&!preg_match("/^\[\]$/", $data[11])){
                    echo "Invalid Skip";
                    exit();
                }
            }
        }
        fclose($handle);
        
        putenv('PATH="C:/***"');
        exec("C:/Windows/System32/chcp 65001");
        exec("Rscript schema.R ".$_POST["project_id1"]." ".$_POST["fname1"], $output);
        
        if($output==array()){
			exec("Rscript schema2.R ".$_POST["project_id1"]." ".$_POST["fname1"], $output);
			
			if($output==array()){
				echo "Other Wrong";
			}else{
				$sql7="UPDATE `project` SET active=2 WHERE project_id= :v1";
				$stmt=$db->prepare($sql7);
				$stmt->bindParam(":v1", $_POST["project_id1"]);
				$stmt->execute();

				$beta=$_POST["project_id1"]."beta";
				$sql8="DROP TABLE IF EXISTS `:v1`";
				$stmt=$db->prepare($sql8);
				$stmt->bindParam(":v1", $beta);
				$stmt->execute();

				$sql9="CREATE TABLE `:v1`(`record_id` INT AUTO_INCREMENT, `time` TIMESTAMP NOT NULL, `record` VARCHAR(10000) NOT NULL, PRIMARY KEY(`record_id`))";
				$stmt=$db->prepare($sql9);
				$stmt->bindValue(":v1", $beta);
				$stmt->execute();
			}
            exit();
        }else{		
            $sql7="UPDATE `project` SET active=2 WHERE project_id= :v1";
            $stmt=$db->prepare($sql7);
            $stmt->bindParam(":v1", $_POST["project_id1"]);
            $stmt->execute();

            $beta=$_POST["project_id1"]."beta";
            $sql8="DROP TABLE IF EXISTS `:v1`";
            $stmt=$db->prepare($sql8);
            $stmt->bindParam(":v1", $beta);
            $stmt->execute();

            $sql9="CREATE TABLE `:v1`(`record_id` INT AUTO_INCREMENT, `time` TIMESTAMP NOT NULL, `record` VARCHAR(10000) NOT NULL, PRIMARY KEY(`record_id`))";
            $stmt=$db->prepare($sql9);
            $stmt->bindValue(":v1", $beta);
            $stmt->execute();
        }
        exit();
    }
	
	if(isset($_POST["submitProject"])){
        $sql10="UPDATE `project` SET active=2.5 WHERE project_id= :v1";
		$stmt=$db->prepare($sql10);
		$stmt->bindParam(":v1", $_POST["project_id2"]);
		$stmt->execute();

        exit();
	}

    if(isset($_FILES["csv_file2"])){
        $files=getFiles();
        $result=uploadFile($files[0]);
        
        if($result["err"]=="不支援的格式"){
            echo "Invalid Format";
            exit();
        }else if($result["err"]=="檔案容量過大"){
            echo "File Oversize";
            exit();
        }
        
        $filepath="C:/***"."/".$files[0]["name"];
        $handle=fopen($filepath, "r");
        $sql11="DELETE FROM `sample` WHERE project_id= :v1";
		$stmt=$db->prepare($sql11);
		$stmt->bindParam(":v1", $_POST["project_id3"]);
		$stmt->execute();
        
        $count=1;
        while(($data=fgetcsv2($handle))!=false){
			$enc=mb_detect_encoding($data[0], "UTF-8", true);
			if(strtolower($enc)!="utf-8"){
				$data=mb_convert_encoding($data, 'UTF-8');
			}
			
            if($count==1){
				if($data[1]!="random_code"){
                    echo "Header Random_code";
                    exit();
                }
                $count++;
                
            }else{
                if($data[0]==""){
                    echo "Missing Sample_id";
                    exit();
                }else if($data[1]==""){
                    echo "Missing Random_code";
                    exit();
                }else{
                    $sql12="INSERT INTO `sample` VALUES(:v1, :v2, :v3)";
                    $stmt=$db->prepare($sql12);
                    $stmt->bindParam(":v1", $_POST["project_id3"]);
                    $stmt->bindParam(":v2", $data[0]);
                    $stmt->bindParam(":v3", $data[1]);
                    $stmt->execute();
					$count++;
                }
            }
        }
        fclose($handle);
                
        $sql13="SELECT sample_size FROM `project` WHERE project_id= :v1";
        $stmt=$db->prepare($sql13);
		$stmt->bindParam(":v1", $_POST["project_id3"]);
		$stmt->execute();
        $rs13=$stmt->fetch(PDO::FETCH_ASSOC);
        
        if($count-2!=$rs13["sample_size"]){
			$_SESSION["Sample_size"]=$count-2;
            echo "Different Sample_size";
            exit();        
        }else{
            $sql14="UPDATE `project` SET active=3, end_date= :v1 WHERE project_id= :v2";
            $stmt=$db->prepare($sql14);
			$stmt->bindParam(":v1", $_POST["end_date"]);
            $stmt->bindParam(":v2", $_POST["project_id3"]);
            $stmt->execute();

            $final=$_POST["project_id3"]."final";
            $sql15="CREATE TABLE `:v1`(`record_id` INT AUTO_INCREMENT, `sample_id` VARCHAR(50) NOT NULL, `time` TIMESTAMP NOT NULL, `record` VARCHAR(10000) NOT NULL, PRIMARY KEY(`record_id`))";
            $stmt=$db->prepare($sql15);
            $stmt->bindParam(":v1", $final);
            $stmt->execute();

			echo "Success2";
            exit();
        }
	}

    if(isset($_POST["alterSampleSize"])){
        $sql16="UPDATE `project` SET sample_size= :v1 WHERE project_id= :v2";
		$stmt=$db->prepare($sql16);
        $stmt->bindParam(":v1", $_SESSION["Sample_size"]);
		$stmt->bindParam(":v2", $_POST["project_id3"]);
		$stmt->execute();
        
        $sql17="UPDATE `project` SET active=3, end_date= :v1 WHERE project_id= :v2";
        $stmt=$db->prepare($sql17);
        $stmt->bindParam(":v1", $_POST["end_date"]);
        $stmt->bindParam(":v2", $_POST["project_id3"]);
        $stmt->execute();

        $final=$_POST["project_id3"]."final";
        $sql18="CREATE TABLE `:v1`(`record_id` INT AUTO_INCREMENT, `sample_id` VARCHAR(50) NOT NULL, `time` TIMESTAMP NOT NULL, `record` VARCHAR(10000) NOT NULL, PRIMARY KEY(`record_id`))";
        $stmt=$db->prepare($sql18);
        $stmt->bindParam(":v1", $final);
        $stmt->execute();

        exit();
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title>總覽儀表板</title>
    <meta http-equiv="Content-Type" content="text/html" charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="cache-control" content="no-cache">
    
	<!-- Bootsrap 4 CDN -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    
	<!-- Fontawesome CDN -->
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
    
	<!-- Jquery-Confirm -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    
    <!-- loadash -->
    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>
    
	<style>
        html{
            min-height: 100%;
            font-family: Microsoft JhengHei; position: relative;
        }
        
        body{
            padding-top: 100px; padding-bottom: 125px;
        }
        
        .wrap{
            width: 100%; margin: 20px auto;
            display: inline-block; position: relative; text-align: center;
        }
                
        .title{
            width: 100%; top: 15%; letter-spacing: 0.05em;
            color: #2E317C;
            font-size: 1.75em; text-align: center; position: absolute;
        }
        
        .modal{
            width: 30%; left: 35%; top: 20%
        }
        
        .row{
            width: 80%; margin: 0px auto;
        }
        
        .btn, .input-group-text{
            font-size: 0.95em;
        }
        
        .icon{
            width: 7.5em;
        }    
        
        .container{
			width: 75%; margin: 10px auto; letter-spacing: 0.05em;
            font-size: 0.8em; align-content: center;
		}
        
        td{
            line-height: 2.5em;
            vertical-align: middle;
        }
    </style>
    
    <script>
		$(document).ready(function(){            
            $.ajax({ 
                type: "POST",
                dataType: "json",
                url: "",
                data: {fetchProject: 1},
                success: function(data){
                    console.log(data);
                    
                    for($i=0; $i<data.length; $i++){
                        var row_n=document.getElementById("project_list").rows.length-1;
                        var append_row=document.getElementById("project_list").insertRow(row_n);
                        
                        append_row.insertCell(0).innerHTML=data[$i]["project_id"];
                        append_row.insertCell(1).innerHTML=data[$i]["project_name"];
                        append_row.insertCell(2).innerHTML=data[$i]["name"];
						append_row.insertCell(3).innerHTML=data[$i]["sample_size"];
                        
						if(data[$i].active==0){
                            append_row.insertCell(4).innerHTML="<span style='padding: 10%; color: #FFFFFF; background-color: #003B83; border-radius: 5px'><b>D 關閉</b></span>";
                        }else if(data[$i].active==1){
                            append_row.insertCell(4).innerHTML="<span style='padding: 10%; color: #FFFFFF; background-color: orange; border-radius: 5px'><b>A 開啟</b></span>";
                        }else if(data[$i].active==2){
                            append_row.insertCell(4).innerHTML="<span style='padding: 10% 7.5%; background-color: #FFFF00; border-radius: 5px'><b>B 測試中</b></span>";
                        }else if(data[$i].active==2.5){
                            append_row.insertCell(4).innerHTML="<span style='padding: 10% 7.5%; background-color: #CCCCD6; border-radius: 5px'><b>準備上線</b></span>";
                        }else{
                            append_row.insertCell(4).innerHTML="<span style='padding: 10% 7.5%; background-color: #C3D825; border-radius: 5px'><b>C 已上線</b></span>";
                        }                        
                        if(data[$i].active==0|data[$i].active==2.5|data[$i].active==3){
                            append_row.insertCell(5).innerHTML="<span style='cursor: not-allowed'>　</span>";
                        }else{
                            append_row.insertCell(5).innerHTML="<button class='btn btn-primary' onClick='upload("+data[$i]['project_id']+")'><i class='fas fa-upload'></i></button>";
                        }                        
                        if(data[$i].active==0|data[$i].active==1){
                            append_row.insertCell(6).innerHTML="<span style='cursor: not-allowed'>　</span>";
                        }else{
                            append_row.insertCell(6).innerHTML="<a href='./preview.php?project_id="+data[$i]['project_id']+"'><i class='fas fa-search-plus' style='font-size: 1.375em; color: #003B83; vertical-align: middle'></i></a>";
                        }
                        if(data[$i].active==0|data[$i].active==1){
                            append_row.insertCell(7).innerHTML="<span style='cursor: not-allowed'>　</span>";
                        }else{
                            append_row.insertCell(7).innerHTML="<a href='./download.php?project_id="+data[$i]['project_id']+"'><button class='btn' style='color: #FFFFFF; background-color: #EF82A0'><i class='fas fa-download'></i></button></a>";
                        }
                        if(data[$i].active==0|data[$i].active==1|data[$i].active==3){
                            append_row.insertCell(8).innerHTML="<span style='cursor: not-allowed'>　</span>";
                        }else if(data[$i].active==2){
                            append_row.insertCell(8).innerHTML="<i class='fas' style='font-size: 1.25em; color: orange; vertical-align: middle' onClick='submit1("+data[$i]['project_id']+")'>GO</i>";
                        }else{
							append_row.insertCell(8).innerHTML="<i class='fas' style='font-size: 1.25em; color: orange; vertical-align: middle' onClick='submit2("+data[$i]['project_id']+")'>GO</i>";
						}
                        if(data[$i].active==0|data[$i].active==1|data[$i].active==2|data[$i].active==2.5){
                            append_row.insertCell(9).innerHTML="<span style='cursor: not-allowed'>　</span>";
                        }else{
                            append_row.insertCell(9).innerHTML=data[$i]["n"];
                        }
                        if(data[$i].active==0|data[$i].active==1|data[$i].active==2|data[$i].active==2.5){
                            append_row.insertCell(10).innerHTML="<span style='cursor: not-allowed'>　</span>";
                        }else{
							append_row.insertCell(10).innerHTML=data[$i]["end_date"];
                        }
                    }
                    
                    if(data.length==0){
                        $(".project_n1").empty().append(0);
                        $(".project_n2").empty().append(0);
                    }else{
                        $(".project_n1").empty().append(1);
                        $(".project_n2").empty().append(data.length);
                    }
                }, error: function(e){
                    console.log(e);
                }     
            })
            
            $("#create_project1").on("click", function(event){
			    event.preventDefault();
			    $("#project_append").modal("show");
			})
            
            $("#create_project2").on("click", function(event){
			    event.preventDefault();
			    $("#project_append").modal("show");
			})
            
            $("#projectAppendForm").on('submit', function(event){
                event.preventDefault();
                $("#submit1").attr("disabled", true);    
            
                $.ajax({
                    type: "POST",
                    dataType: "json", 
                    url: "",
                    data: $('#projectAppendForm').serialize(),
                    success: function(data){
                        console.log(data);
                        
                        var row=document.getElementById("project_list").rows.length;
                        for($i=0; $i<row-2; $i++){
                            document.getElementById("project_list").deleteRow(1);
                        }
                        
                        for($j=0; $j<data.length; $j++){
                            var row_n=document.getElementById("project_list").rows.length-1;
                            var append_row=document.getElementById("project_list").insertRow(row_n);
                            
                            append_row.insertCell(0).innerHTML=data[$j]["project_id"];
                            append_row.insertCell(1).innerHTML=data[$j]["project_name"];
                            append_row.insertCell(2).innerHTML=data[$j]["name"];
                            append_row.insertCell(3).innerHTML=data[$j]["sample_size"];

                            if(data[$j].active==0){
                                append_row.insertCell(4).innerHTML="<span style='padding: 10%; color: #FFFFFF; background-color: #003B83; border-radius: 5px'><b>D 關閉</b></span>";
                            }else if(data[$j].active==1){
                                append_row.insertCell(4).innerHTML="<span style='padding: 10%; color: #FFFFFF; background-color: orange; border-radius: 5px'><b>A 開啟</b></span>";
                            }else if(data[$j].active==2){
                                append_row.insertCell(4).innerHTML="<span style='padding: 10% 7.5%; background-color: #FFFF00; border-radius: 5px'><b>B 測試中</b></span>";
                            }else if(data[$j].active==2.5){
								append_row.insertCell(4).innerHTML="<span style='padding: 10% 7.5%; background-color: #CCCCD6; border-radius: 5px'><b>準備上線</b></span>";
							}else{
                                append_row.insertCell(4).innerHTML="<span style='padding: 10% 7.5%; background-color: #C3D825; border-radius: 5px'><b>C 已上線</b></span>";
                            }                        
                            if(data[$j].active==0|data[$j].active==2.5|data[$j].active==3){
                                append_row.insertCell(5).innerHTML="<span style='cursor: not-allowed'>　</span>";
                            }else{
                                append_row.insertCell(5).innerHTML="<button class='btn btn-primary' onClick='upload("+data[$j]['project_id']+")'><i class='fas fa-upload'></i></button>";
                            }                        
                            if(data[$j].active==0|data[$j].active==1){
                                append_row.insertCell(6).innerHTML="<span style='cursor: not-allowed'>　</span>";
                            }else{
                                append_row.insertCell(6).innerHTML="<a href='./preview.php?project_id="+data[$j]['project_id']+"'><i class='fas fa-search-plus' style='font-size: 1.375em; color: #003B83; vertical-align: middle'></i></a>";
                            }
                            if(data[$j].active==0|data[$j].active==1){
                                append_row.insertCell(7).innerHTML="<span style='cursor: not-allowed'>　</span>";
                            }else{
                                append_row.insertCell(7).innerHTML="<a href='./download.php?project_id="+data[$j]['project_id']+"'><button class='btn' style='color: #FFFFFF; background-color: #EF82A0'><i class='fas fa-download'></i></button></a>";
                            }
                            if(data[$j].active==0|data[$j].active==1|data[$j].active==3){
                                append_row.insertCell(8).innerHTML="<span style='cursor: not-allowed'>　</span>";
                            }else if(data[$j].active==2){
								append_row.insertCell(8).innerHTML="<i class='fas' style='font-size: 1.25em; color: orange; vertical-align: middle' onClick='submit1("+data[$j]['project_id']+")'>GO</i>";
							}else{
								append_row.insertCell(8).innerHTML="<i class='fas' style='font-size: 1.25em; color: orange; vertical-align: middle' onClick='submit2("+data[$j]['project_id']+")'>GO</i>";
							}
                            if(data[$j].active==0|data[$j].active==1|data[$j].active==2|data[$j].active==2.5){
                                append_row.insertCell(9).innerHTML="<span style='cursor: not-allowed'>　</span>";
                            }else{
                                append_row.insertCell(9).innerHTML=data[$j]["n"];
                            }
                            if(data[$j].active==0|data[$j].active==1|data[$j].active==2|data[$j].active==2.5){
                                append_row.insertCell(10).innerHTML="<span style='cursor: not-allowed'>　</span>";
                            }else{
                                append_row.insertCell(10).innerHTML=data[$i]["end_date"];
                            }
                        }
                    
                        if(data.length==0){
                            $(".project_n1").empty().append(0);
                            $(".project_n2").empty().append(0);
                        }else{
                            $(".project_n1").empty().append(1);
                            $(".project_n2").empty().append(data.length);
                        }
                        $("#project_append").modal("hide");
                    }, error: function(e){
                        console.log(e);
                    }
                })
            })
            
            $("#search").on("input", function(event){
                event.preventDefault();
                var search=$(this).val();
                
			    $.ajax({ 
                    type: "POST",
                    dataType: "json",
                    url: "",
                    data: {searchProject: 1, search: search},
                    success: function(data){
                        console.log(data);
                        
                        var row=document.getElementById("project_list").rows.length;
                        for($i=0; $i<row-2; $i++){
                            document.getElementById("project_list").deleteRow(1);
                        }
                        
                        for($j=0; $j<data.length; $j++){
                            var row_n=document.getElementById("project_list").rows.length-1;
                            var append_row=document.getElementById("project_list").insertRow(row_n);

                            append_row.insertCell(0).innerHTML=data[$j]["project_id"];
                            append_row.insertCell(1).innerHTML=data[$j]["project_name"];
                            append_row.insertCell(2).innerHTML=data[$j]["name"];
                            append_row.insertCell(3).innerHTML=data[$j]["sample_size"];

                            if(data[$j].active==0){
                                append_row.insertCell(4).innerHTML="<span style='padding: 10%; color: #FFFFFF; background-color: #003B83; border-radius: 5px'><b>D 關閉</b></span>";
                            }else if(data[$j].active==1){
                                append_row.insertCell(4).innerHTML="<span style='padding: 10%; color: #FFFFFF; background-color: orange; border-radius: 5px'><b>A 開啟</b></span>";
                            }else if(data[$j].active==2){
                                append_row.insertCell(4).innerHTML="<span style='padding: 10% 7.5%; background-color: #FFFF00; border-radius: 5px'><b>B 測試中</b></span>";
                            }else if(data[$j].active==2.5){
								append_row.insertCell(4).innerHTML="<span style='padding: 10% 7.5%; background-color: #CCCCD6; border-radius: 5px'><b>準備上線</b></span>";
							}else{
                                append_row.insertCell(4).innerHTML="<span style='padding: 10% 7.5%; background-color: #C3D825; border-radius: 5px'><b>C 已上線</b></span>";
                            }                        
                            if(data[$j].active==0|data[$j].active==2.5|data[$j].active==3){
                                append_row.insertCell(5).innerHTML="<span style='cursor: not-allowed'>　</span>";
                            }else{
                                append_row.insertCell(5).innerHTML="<button class='btn btn-primary' onClick='upload("+data[$j]['project_id']+")'><i class='fas fa-upload'></i></button>";
                            }                        
                            if(data[$j].active==0|data[$j].active==1){
                                append_row.insertCell(6).innerHTML="<span style='cursor: not-allowed'>　</span>";
                            }else{
                                append_row.insertCell(6).innerHTML="<a href='./preview.php?project_id="+data[$j]['project_id']+"'><i class='fas fa-search-plus' style='font-size: 1.375em; color: #003B83; vertical-align: middle'></i></a>";
                            }
                            if(data[$j].active==0|data[$j].active==1){
                                append_row.insertCell(7).innerHTML="<span style='cursor: not-allowed'>　</span>";
                            }else{
                                append_row.insertCell(7).innerHTML="<a href='./download.php?project_id="+data[$j]['project_id']+"'><button class='btn' style='color: #FFFFFF; background-color: #EF82A0'><i class='fas fa-download'></i></button></a>";
                            }
                            if(data[$j].active==0|data[$j].active==1|data[$j].active==3){
                                append_row.insertCell(8).innerHTML="<span style='cursor: not-allowed'>　</span>";
                            }else if(data[$j].active==2){
								append_row.insertCell(8).innerHTML="<i class='fas' style='font-size: 1.25em; color: orange; vertical-align: middle' onClick='submit1("+data[$j]['project_id']+")'>GO</i>";
							}else{
								append_row.insertCell(8).innerHTML="<i class='fas' style='font-size: 1.25em; color: orange; vertical-align: middle' onClick='submit2("+data[$j]['project_id']+")'>GO</i>";
							}
                            if(data[$j].active==0|data[$j].active==1|data[$j].active==2|data[$j].active==2.5){
                                append_row.insertCell(9).innerHTML="<span style='cursor: not-allowed'>　</span>";
                            }else{
                                append_row.insertCell(9).innerHTML=data[$j]["n"];
                            }
                            if(data[$j].active==0|data[$j].active==1|data[$j].active==2|data[$j].active==2.5){
                                append_row.insertCell(10).innerHTML="<span style='cursor: not-allowed'>　</span>";
                            }else{
								append_row.insertCell(10).innerHTML=data[$i]["end_date"];
                            }
                        }
                    
                        if(data.length==0){
                            $(".project_n1").empty().append(0);
                            $(".project_n2").empty().append(0);
                        }else{
                            $(".project_n1").empty().append(1);
                            $(".project_n2").empty().append(data.length);
                        }
                    }, error: function(e){
                        console.log(e);
                    }     
                })           
            })
            
            $("#uploadCsvForm").on("submit", function(event){
                event.preventDefault();
                $("#submit2").attr("disabled", true);
                
                var file1=$("#csv_file1").prop("files")[0];
                var project_id1=$("input[name='project_id1']").val();
                var filename1=$("#csv_file1").get(0).files[0].name;
                
                var data=new FormData();
                data.append("csv_file1", file1);
                data.append("project_id1", project_id1);
                data.append("fname1", filename1);
                
                $.ajax({
                    type: "POST",
                    url: "",
                    data: data,
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function(data){
                        console.log(data);
                        if(data=="Invalid Format"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 不支援的格式",
                                content: "上傳的檔案格式不符 (需為 <b style='color: red'>csv 檔</b>)",
                            })  
                        }else if(data=="File Oversize"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 檔案容量過大",
                                content: "上傳的檔案太大，請修正！",
                            })
							
                        }else if(data=="Header Q_sn"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 欄位名稱錯誤",
                                content: "<b style='color: blue'>B 欄</b> 表頭應為 <b style='color: red'>[q_sn]</b>，請檢查欄位是否存在或順序有誤！",
                            })
                        }else if(data=="Header Q_txt"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 欄位名稱錯誤",
                                content: "<b style='color: blue'>C 欄</b> 表頭應為 <b style='color: red'>[q_txt]</b>，請檢查欄位是否存在或順序有誤！",
                            })
                        }else if(data=="Header Type"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 欄位名稱錯誤",
                                content: "<b style='color: blue'>D 欄</b> 表頭應為 <b style='color: red'>[type]</b>，請檢查欄位是否存在或順序有誤！",
                            })
                        }else if(data=="Header Opt_txt"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 欄位名稱錯誤",
                                content: "<b style='color: blue'>E 欄</b> 表頭應為 <b style='color: red'>[opt_txt]</b>，請檢查欄位是否存在或順序有誤！",
                            })
                        }else if(data=="Header Opt_value"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 欄位名稱錯誤",
                                content: "<b style='color: blue'>F 欄</b> 表頭應為 <b style='color: red'>[opt_value]</b>，請檢查欄位是否存在或順序有誤！",
                            })
                        }else if(data=="Header Annotate"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 欄位名稱錯誤",
                                content: "<b style='color: blue'>G 欄</b> 表頭應為 <b style='color: red'>[annotate]</b>，請檢查欄位是否存在或順序有誤！",
                            })
                        }else if(data=="Header Note"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 欄位名稱錯誤",
                                content: "<b style='color: blue'>H 欄</b> 表頭應為 <b style='color: red'>[note]</b>，請檢查欄位是否存在或順序有誤！",
                            })
                        }else if(data=="Header Disjoint"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 欄位名稱錯誤",
                                content: "<b style='color: blue'>I 欄</b> 表頭應為 <b style='color: red'>[disjoint]</b>，請檢查欄位是否存在或順序有誤！",
                            })
                        }else if(data=="Header Range_min"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 欄位名稱錯誤",
                                content: "<b style='color: blue'>J 欄</b> 表頭應為 <b style='color: red'>[range_min]</b>，請檢查欄位是否存在或順序有誤！",
                            })
                        }else if(data=="Header Range_max"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 欄位名稱錯誤",
                                content: "<b style='color: blue'>K 欄</b> 表頭應為 <b style='color: red'>[range_max]</b>，請檢查欄位是否存在或順序有誤！",
                            })
                        }else if(data=="Header Skip"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 欄位名稱錯誤",
                                content: "<b style='color: blue'>L 欄</b> 表頭應為 <b style='color: red'>[skip]</b>，請檢查欄位是否存在或順序有誤！",
                            })
						}else if(data=="Header Attach"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 欄位名稱錯誤",
                                content: "<b style='color: blue'>M 欄</b> 表頭應為 <b style='color: red'>[attach]</b>，請檢查欄位是否存在或順序有誤！",
                            })
                            
                        }else if(data=="Missing Q_id"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 必填欄位缺漏",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[q_id]</b> 欄位有漏填，此為必填欄位，每行都需填寫唷！",
                            })   
                        }else if(data=="Missing Q_sn"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 必填欄位缺漏",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[q_sn]</b> 欄位有漏填，此為必填欄位，每行都需填寫唷！",
                            })   
                        }else if(data=="Missing Q_txt"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 必填欄位缺漏",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[q_txt]</b> 欄位有漏填，此為必填欄位，每行都需填寫唷！",
                            })   
                        }else if(data=="Missing Type"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 必填欄位缺漏",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[type]</b> 欄位有漏填，此為必填欄位，每行都需填寫唷！",
                            })   
                        }else if(data=="Missing Opt_txt"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 必填欄位缺漏",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[opt_txt]</b> 欄位有漏填，此為 <b style='color: blue'>單選題、複選題</b> 的必填欄位，務必要填寫唷！",
                            })   
                        }else if(data=="Missing Opt_value"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 必填欄位缺漏",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[opt_value]</b> 欄位有漏填，此為 <b style='color: blue'>單選題、複選題</b> 的必填欄位，務必要填寫唷！",
                            })   
                        }else if(data=="Missing Note"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 必填欄位缺漏",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[note]</b> 欄位有漏填，此為 <b style='color: blue'>單選題、複選題</b> 的必填欄位，務必要填寫唷！",
                            })   
                        }else if(data=="Missing Disjoint"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 必填欄位缺漏",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[disjoint]</b> 欄位有漏填，此為 <b style='color: blue'>複選題</b> 的必填欄位，務必要填寫唷！",
                            })
						}else if(data=="Missing Skip"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 必填欄位缺漏",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[skip]</b> 欄位有漏填，此為 <b style='color: blue'>單選題、複選題</b> 的必填欄位，務必要填寫唷！",
                            })
                            
                        }else if(data=="Invalid Q_id"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 填寫格式錯誤",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[q_id]</b> 欄位有錯誤，輸入類型應為：<b style='color: blue'>數字</b>，請檢查修正！",
                            })
                        }else if(data=="Invalid Q_sn"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 填寫格式錯誤",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[q_sn]</b> 欄位有錯誤，輸入類型應為：<b style='color: blue'>數字</b>，請檢查修正！",
                            })           
                        }else if(data=="Invalid Type"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 填寫格式錯誤",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[type]</b> 欄位有錯誤，合理的輸入值為 <b style='color: blue'>0,1,2,3,4,9</b>，請檢查修正！",
                            })
                        }else if(data=="Invalid Opt_value"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 填寫格式錯誤",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[opt_value]</b> 欄位有錯誤，輸入類型應為：<b style='color: blue'>數字</b>，請檢查修正！",
                            })                            
                        }else if(data=="Invalid Note"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 填寫格式錯誤",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[note]</b> 欄位有錯誤，合理的輸入值為 <b style='color: blue'>0,1</b>，請檢查修正！",
                            })
                        }else if(data=="Invalid Disjoint"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 填寫格式錯誤",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[disjoint]</b> 欄位有錯誤，合理的輸入值為 <b style='color: blue'>0,1</b>，請檢查修正！",
                            })
                        }else if(data=="Invalid Range_min"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 填寫格式錯誤",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[range_min]</b> 欄位有錯誤，輸入類型應為：<b style='color: blue'>數字</b>，請檢查修正！",
                            })
                        }else if(data=="Invalid Range_max"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 填寫格式錯誤",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[range_max]</b> 欄位有錯誤，輸入類型應為：<b style='color: blue'>數字</b>，請檢查修正！",
                            })
                        }else if(data=="Invalid Skip"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 填寫格式錯誤",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[skip]</b> 欄位有錯誤，輸入格式應為：<b style='color: blue'>[q_id-q_sn(,q_id-q_sn...)]</b>，請檢查修正！",
                            })
                            
                        }else if(data=="Other Wrong"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 其他錯誤",
                                content: "無欄位漏填、格式錯誤，但上傳仍失敗。這可能是因為 <b style='color: blue'>檔名無法解析 (請避免中文及空格)、欄位內容有斷/換行</b> 等原因。請再次檢查或聯繫管理人員。",
                            }) 
                        }else{
                            $.confirm({
                                title: "<i class='fas fa-check-circle' style='color: blue'></i> 上傳成功",
                                content: "題目已上傳囉！點按 <i class='fas fa-search-plus' style='color: #003B83'></i> 即可預覽問卷！",
                                buttons:{
                                    "OK": function(){
                                        window.location.href="./main.php";
                                    }
                                }
                            })
                        }
                    }, error: function(e){
                        console.log(e);
                    }
                })
            })
            
            $("#submitCsvForm").on("submit", function(event){
                event.preventDefault();
                $("#submit3").attr("disabled", true);
                
                var file2=$("#csv_file2").prop("files")[0];
                var project_id3=$("input[name='project_id3']").val();
				var end_date=$("input[name='end_date']").val();
                var filename2=$("#csv_file2").get(0).files[0].name;	
				console.log(end_date);
                
                var data=new FormData();
                data.append("csv_file2", file2);
                data.append("project_id3", project_id3);
				data.append("end_date", end_date);
                data.append("fname2", filename2);
                
                $.ajax({
                    type: "POST",
                    url: "",
                    data: data,
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function(data){
                        console.log(data);
                        if(data=="Invalid Format"){
                            $("#submit3").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 不支援的格式",
                                content: "上傳的檔案格式不符 (需為 <b style='color: red'>csv 檔</b>)",
                            })  
                        }else if(data=="File Oversize"){
                            $("#submit3").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 檔案容量過大",
                                content: "上傳的檔案太大，請修正！",
                            })
                        
                        }else if(data=="Header Random_code"){
                            $("#submit3").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 欄位名稱錯誤",
                                content: "<b style='color: blue'>B 欄</b> 表頭應為 <b style='color: red'>[random_code]</b>，請檢查欄位是否存在或順序有誤！",
                            })    
                        }else if(data=="Missing Sample_id"){
                            $("#submit3").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 必填欄位缺漏",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[sample_id]</b> 欄位有漏填，此為必填欄位，每行都需填寫唷！",
                            })   
                        }else if(data=="Missing Random_code"){
                            $("#submit3").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 必填欄位缺漏",
                                content: "系統偵測到您檔案中的 <b style='color: red'>[random_code]</b> 欄位有漏填，此為必填欄位，每行都需填寫唷！",
                            })
                        
                        }else if(data=="Different Sample_size"){
                            $("#submit3").attr("disabled", false);
                            $.confirm({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> 清單筆數不符",
                                content: "您上傳的樣本清單，<b style='color: red'>與原訂的專案樣本數量不符</b>，請確認是否要修改樣本數？",
                                buttons:{
                                    "返回": function(){},
                                    "OK": function(){
                                        $.ajax({
                                            type: "POST", 
                                            url: "",
                                            data: {alterSampleSize: 1, project_id3: project_id3},
                                            success: function(data){
                                                console.log(data);
                                                $.confirm({
                                                    title: "<i class='fas fa-check-circle' style='color: blue'></i> 上線成功",
                                                    content: "樣本名單已上傳！問卷上線囉！",
                                                    buttons:{
                                                        "OK": function(){
                                                            window.location.href="./main.php";
                                                        }
                                                    }
                                                })
                                            }, error: function(e){
                                                console.log(e);
                                            }
                                        })
                                    }
                                }
                            })                             
                        }else if(data=="Success2"){
                            $.confirm({
                                title: "<i class='fas fa-check-circle' style='color: blue'></i> 上線成功",
                                content: "樣本名單已上傳！問卷上線囉！",
                                buttons:{
                                    "OK": function(){
                                        window.location.href="./main.php";
                                    }
                                }
                            })
                        }
                    }, error: function(e){
                        console.log(e);
                    }
                })
            })
        })
	</script>
</head>


<body>
	<?php include("header.php");?>
    
    <div class="wrap">
        <img style="width: 12.5%" src="./pic/square.png">
        <div class="title"><b>專案管理</b></div>
    </div>
    
    <div id="project_append" class="modal fade">
        <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size: 1.25em; background-color: yellow">⭐ 新增專案 ⭐</h5>
                <button class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body" style="padding-bottom: 1em">
                <form id="projectAppendForm"><br>
                    <div class="row">
                        <div class="col-sm-5 pl-0 pr-0">
                            <div class="input-group-text">A. 專案名稱：</div>
                        </div>
                        <div class="col-sm-7 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="text" name="project_name" required>
                        </div>
                    </div>     
                    <div class="row">
                        <div class="col-sm-5 pl-0 pr-0">
                            <div class="input-group-text">B. 專案負責人：</div>
                        </div>
                        <div class="col-sm-7 pl-0 pr-0">
                            <select name="id" class="form-control btn-outline-secondary">
                                <option value="">請選擇</option>
                                <?php foreach($_SESSION["all_acc"]as $r){?>
                                <option value="<?=$r["id"]?>"><?=$r["name"]?></option>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-5 pl-0 pr-0">
                            <div class="input-group-text">C. 樣本數量：</div>
                        </div>
                        <div class="col-sm-7 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="number" name="sample_size" min=1 required>
                        </div>
                    </div><br>

                    <div style="text-align: center">
                        <button id="submit1" class="btn">
                            <img src="/pic/submit.png" class="icon">
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>
    
    <div id="upload_csv" class="modal fade">
        <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size: 1.25em; background-color: yellow">⭐ 上傳題目 ⭐</h5>
                <button class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body" style="padding-bottom: 1em">
                <form id="uploadCsvForm"><br>
                    <div class="row">
                        <div class="col-sm-4 pl-0 pr-0">
                            <div class="input-group-text">A. 專案號：</div>
                        </div>
                        <div class="col-sm-8 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="text" name="project_id1" style="cursor: not-allowed" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 pl-0 pr-0">
                            <div class="input-group-text">B. 題目檔案：</div>
                        </div>
                        <div class="col-sm-8 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="file" id="csv_file1" style="padding-bottom: 2.25em; font-size: 0.8em" required>
                        </div>
                    </div><br>

                    <div style="text-align: center">
                        <button id="submit2" class="btn">
                            <img src="/pic/submit.png" class="icon">
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>
    
    <div id="submit_csv" class="modal fade" style="top: 15%">
        <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size: 1.25em; background-color: yellow">⭐ 確認上線 ⭐</h5>
                <button class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body" style="padding-bottom: 1em">
                <form id="submitCsvForm"><br>
                    <div class="row">
                        <div class="col-sm-4 pl-0 pr-0">
                            <div class="input-group-text">A. 專案號：</div>
                        </div>
                        <div class="col-sm-8 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="text" name="project_id3" style="cursor: not-allowed" readonly>
                        </div>
                    </div>
					<p class="row" style="font-size: 0.8em; margin: 2.5% auto"><b><i class='fa fa-info-circle'></i> 即專案上線的最後一天，隔日將會關閉。</b></p>
					<div class="row">
                        <div class="col-sm-4 pl-0 pr-0">
                            <div class="input-group-text">B. 截止日期：</div>
                        </div>
                        <div class="col-sm-8 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="date" name="end_date" style="font-size: 0.9em" required>
                        </div>
                    </div>
                    <p class="row" style="font-size: 0.8em; margin: 2.5% auto"><b><i class='fa fa-info-circle'></i> 此為專案上線後，可訪問連結的樣本清單。</b></p>
                    <div class="row">
                        <div class="col-sm-4 pl-0 pr-0">
                            <div class="input-group-text">C. 樣本檔案：</div>
                        </div>
                        <div class="col-sm-8 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="file" id="csv_file2" style="padding-bottom: 2.25em; font-size: 0.8em" required>
                        </div>
                    </div><br>

                    <div style="text-align: center">
                        <button id="submit3" class="btn">
                            <img src="/pic/submit.png" class="icon">
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>
    
    <div class="container" style="padding-top: 1%">
        <div class="card" style="border: 0.1em solid #FCD217">     
            <div class="card-header" style="background-color: #FCD217">
                <b style='font-size: 1.1em'>使用說明<button class="close"><a data-toggle="collapse" href=".card-body">&times;</a></button></b>
            </div>
            <div class="card-body show" style="padding: 2% 10% 0% 10%">
				<?php if($_SESSION["acc_info"]["level"]==1){?>
					<p style="padding-top: 1%; text-align: center">您可點擊 <button class="btn btn-secondary" id="create_project1" style="margin-top: -0.25em"><i class="fas fa-plus"></i> 專案</button> 建立一個新專案，專案預設為 <span style="padding: 1%; color: #FFFFFF; background-color: orange; border-radius: 5px;"><b>A 開啟</b></span>，不同狀態對應相異的操作權限如下：</p>
                <?php }else{?>
					<p style="padding-top: 1%; text-align: center">專案預設為 <span style="padding: 1%; color: #FFFFFF; background-color: orange; border-radius: 5px;"><b>A 開啟</b></span>，不同狀態對應相異的操作權限如下：</p>
				<?php }?>
				
                <table class="table rule" style="text-align: center">
                    <thead>
                        <td style="line-height: 1em">專案狀態</td>
                        <td style="line-height: 1em">執行狀況</td>
                        <td style="line-height: 1em">操作權限</td>
                    </thead>
                    <tr>
                        <td style="line-height: 2em; vertical-align: middle"><span style="padding: 10%; color: #FFFFFF; background-color: orange; border-radius: 5px"><b>A 開啟</b></span></td>
                        <td style="line-height: 2em; vertical-align: middle">專案建立後，尚未上傳問題</td>
                        <td style="line-height: 2em; vertical-align: middle"><button class="btn btn-primary"><i class="fas fa-upload"></i></button></td>
                    </tr>
                    <tr>
                        <td style="line-height: 2em; vertical-align: middle"><span style="padding: 10% 7.5%; background-color: #FFFF00; border-radius: 5px"><b>B 測試中</b></span></td>
                        <td style="line-height: 2em; vertical-align: middle">上傳題目後，尚未確認上線（內部測試）<br>註：您可重複上傳題目，惟<b style="color: red">先前的版本內容及資料將被覆寫取代</b></td>
                        <td style="line-height: 2em; vertical-align: middle"><button class="btn btn-primary"><i class="fas fa-upload"></i></button>　<i class="fas fa-search-plus" style="font-size: 1.375em; color: #003B83; vertical-align: middle"></i>　<button class="btn" style="color: #FFFFFF; background-color: #EF82A0"><i class="fas fa-download"></i></button>　<i class="fas" style="font-size: 1.25em; color: orange; vertical-align: middle">GO</i></td>
                    </tr>
                    <tr>
                        <td style="line-height: 2em; vertical-align: middle"><span style="padding: 10% 7.5%; background-color: #C3D825; border-radius: 5px"><b>C 已上線</b></span></td>
                        <td style="line-height: 2em; vertical-align: middle">上傳樣本清單後即完成上線（正式施測）<br>註：此時<b style="color: red">不可重新上傳問題</b>，上線前請確認題目為最終定版</td>
                        <td style="line-height: 2em; vertical-align: middle"><i class="fas fa-search-plus" style="font-size: 1.375em; color: #003B83; vertical-align: middle"></i>　<button class="btn" style="color: #FFFFFF; background-color: #EF82A0"><i class="fas fa-download"></i></button></td>
                    </tr>
                    <tr>
                        <td style="line-height: 2em; vertical-align: middle"><span style="padding: 10%; color: #FFFFFF; background-color: #003B83; border-radius: 5px"><b>D 關閉</b></span></td>
                        <td style="line-height: 2em; vertical-align: middle">專案中止或已結束</td>
                        <td style="line-height: 2em; vertical-align: middle"><i class="fa fa-ban" style="font-size: 1.375em; color: #FF0000; vertical-align: middle"></i></td>
                    </tr>
                </table>
            </div>
        </div>
    </div><br>
        
    <div class="container">
		<?php if($_SESSION["acc_info"]["level"]==1){?>
			<button class="btn btn-secondary" id="create_project2" style="margin: 2% 1%"><i class="fas fa-plus"></i> 專案</button>
		<?php }?>
        <div style="float: right; padding: 2.5% 0%">搜尋：<input id="search"></div>
        
        <table id="project_list" class="table table-striped" style="text-align: center" cellspacing="0">
            <thead>
                <td>專案號</td>
                <td style="width: 17.5%">專案名稱</td>
                <td>負責人</td>
                <td>樣本數</td>
                <td style="width: 10%">專案狀態</td>
                <td>上傳題目</td>
                <td>預覽填寫</td>
                <td style="line-height: 1.25em">下載<br><b style="color: red">測試</b>資料</td>
				<td>確認上線</td>
                <td>目前回收</td>
                <td>截止日期</td>
            </thead>
            <tr class="list">
            </tr>
        </table><br>
        
        顯示第 <span class="project_n1"></span>－<span class="project_n2"></span> 項結果，共 <span class="project_n2"></span> 項
    </div>
    
    <?php include("footer.php");?>
    
    <script>
        function upload(project_id){
            $.confirm({
				title: "",
				content: "注意：<br>若您原先已經上傳過題目，<b style='color: red'>既有的版本及測試資料將會被覆蓋</b>，請再次確認是否上傳檔案？",
				buttons:{
					"返回": function(){},
					"<i class='fas fa-upload'></i>": function(){
						$("#upload_csv").modal("show");
                        $("input[name='project_id1']").val(project_id);
					}
				}
			})
        }
        
        function submit1(project_id){
			$.confirm({
				title: "",
				content: "注意：<br>專案上線後<b style='color: red'>無法再修改題目內容</b>，請再次確認是否上線？",
				buttons:{
					"返回": function(){},
					"<i class='fas' style='color: orange'>GO</i>": function(){
						$.ajax({
                            type: "POST", 
                            url: "",
                            data: {submitProject: 1, project_id2: project_id},
                            success: function(data){
                                console.log(data);
								$.confirm({
									title: "",
									content: "已提交管理人員處理，請耐心等待專案上線！",
									buttons:{
										"OK": function(){
											window.location.href="./main.php";
										}
									}
								})
                            }, error: function(e){
                                console.log(e);
                            }
                        })
					}
				}
			})
        }
		
		function submit2(project_id){
			$("#submit_csv").modal("show");
			$("input[name='project_id3']").val(project_id);
        }
	</script>
</body>
</html>
