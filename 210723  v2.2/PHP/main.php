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
        
        if($result["err"]=="??????????????????"){
            echo "Invalid Format";
            exit();
        }else if($result["err"]=="??????????????????"){
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
        
        if($result["err"]=="??????????????????"){
            echo "Invalid Format";
            exit();
        }else if($result["err"]=="??????????????????"){
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
	<title>???????????????</title>
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
                            append_row.insertCell(4).innerHTML="<span style='padding: 10%; color: #FFFFFF; background-color: #003B83; border-radius: 5px'><b>D ??????</b></span>";
                        }else if(data[$i].active==1){
                            append_row.insertCell(4).innerHTML="<span style='padding: 10%; color: #FFFFFF; background-color: orange; border-radius: 5px'><b>A ??????</b></span>";
                        }else if(data[$i].active==2){
                            append_row.insertCell(4).innerHTML="<span style='padding: 10% 7.5%; background-color: #FFFF00; border-radius: 5px'><b>B ?????????</b></span>";
                        }else if(data[$i].active==2.5){
                            append_row.insertCell(4).innerHTML="<span style='padding: 10% 7.5%; background-color: #CCCCD6; border-radius: 5px'><b>????????????</b></span>";
                        }else{
                            append_row.insertCell(4).innerHTML="<span style='padding: 10% 7.5%; background-color: #C3D825; border-radius: 5px'><b>C ?????????</b></span>";
                        }                        
                        if(data[$i].active==0|data[$i].active==2.5|data[$i].active==3){
                            append_row.insertCell(5).innerHTML="<span style='cursor: not-allowed'>???</span>";
                        }else{
                            append_row.insertCell(5).innerHTML="<button class='btn btn-primary' onClick='upload("+data[$i]['project_id']+")'><i class='fas fa-upload'></i></button>";
                        }                        
                        if(data[$i].active==0|data[$i].active==1){
                            append_row.insertCell(6).innerHTML="<span style='cursor: not-allowed'>???</span>";
                        }else{
                            append_row.insertCell(6).innerHTML="<a href='./preview.php?project_id="+data[$i]['project_id']+"'><i class='fas fa-search-plus' style='font-size: 1.375em; color: #003B83; vertical-align: middle'></i></a>";
                        }
                        if(data[$i].active==0|data[$i].active==1){
                            append_row.insertCell(7).innerHTML="<span style='cursor: not-allowed'>???</span>";
                        }else{
                            append_row.insertCell(7).innerHTML="<a href='./download.php?project_id="+data[$i]['project_id']+"'><button class='btn' style='color: #FFFFFF; background-color: #EF82A0'><i class='fas fa-download'></i></button></a>";
                        }
                        if(data[$i].active==0|data[$i].active==1|data[$i].active==3){
                            append_row.insertCell(8).innerHTML="<span style='cursor: not-allowed'>???</span>";
                        }else if(data[$i].active==2){
                            append_row.insertCell(8).innerHTML="<i class='fas' style='font-size: 1.25em; color: orange; vertical-align: middle' onClick='submit1("+data[$i]['project_id']+")'>GO</i>";
                        }else{
							append_row.insertCell(8).innerHTML="<i class='fas' style='font-size: 1.25em; color: orange; vertical-align: middle' onClick='submit2("+data[$i]['project_id']+")'>GO</i>";
						}
                        if(data[$i].active==0|data[$i].active==1|data[$i].active==2|data[$i].active==2.5){
                            append_row.insertCell(9).innerHTML="<span style='cursor: not-allowed'>???</span>";
                        }else{
                            append_row.insertCell(9).innerHTML=data[$i]["n"];
                        }
                        if(data[$i].active==0|data[$i].active==1|data[$i].active==2|data[$i].active==2.5){
                            append_row.insertCell(10).innerHTML="<span style='cursor: not-allowed'>???</span>";
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
                                append_row.insertCell(4).innerHTML="<span style='padding: 10%; color: #FFFFFF; background-color: #003B83; border-radius: 5px'><b>D ??????</b></span>";
                            }else if(data[$j].active==1){
                                append_row.insertCell(4).innerHTML="<span style='padding: 10%; color: #FFFFFF; background-color: orange; border-radius: 5px'><b>A ??????</b></span>";
                            }else if(data[$j].active==2){
                                append_row.insertCell(4).innerHTML="<span style='padding: 10% 7.5%; background-color: #FFFF00; border-radius: 5px'><b>B ?????????</b></span>";
                            }else if(data[$j].active==2.5){
								append_row.insertCell(4).innerHTML="<span style='padding: 10% 7.5%; background-color: #CCCCD6; border-radius: 5px'><b>????????????</b></span>";
							}else{
                                append_row.insertCell(4).innerHTML="<span style='padding: 10% 7.5%; background-color: #C3D825; border-radius: 5px'><b>C ?????????</b></span>";
                            }                        
                            if(data[$j].active==0|data[$j].active==2.5|data[$j].active==3){
                                append_row.insertCell(5).innerHTML="<span style='cursor: not-allowed'>???</span>";
                            }else{
                                append_row.insertCell(5).innerHTML="<button class='btn btn-primary' onClick='upload("+data[$j]['project_id']+")'><i class='fas fa-upload'></i></button>";
                            }                        
                            if(data[$j].active==0|data[$j].active==1){
                                append_row.insertCell(6).innerHTML="<span style='cursor: not-allowed'>???</span>";
                            }else{
                                append_row.insertCell(6).innerHTML="<a href='./preview.php?project_id="+data[$j]['project_id']+"'><i class='fas fa-search-plus' style='font-size: 1.375em; color: #003B83; vertical-align: middle'></i></a>";
                            }
                            if(data[$j].active==0|data[$j].active==1){
                                append_row.insertCell(7).innerHTML="<span style='cursor: not-allowed'>???</span>";
                            }else{
                                append_row.insertCell(7).innerHTML="<a href='./download.php?project_id="+data[$j]['project_id']+"'><button class='btn' style='color: #FFFFFF; background-color: #EF82A0'><i class='fas fa-download'></i></button></a>";
                            }
                            if(data[$j].active==0|data[$j].active==1|data[$j].active==3){
                                append_row.insertCell(8).innerHTML="<span style='cursor: not-allowed'>???</span>";
                            }else if(data[$j].active==2){
								append_row.insertCell(8).innerHTML="<i class='fas' style='font-size: 1.25em; color: orange; vertical-align: middle' onClick='submit1("+data[$j]['project_id']+")'>GO</i>";
							}else{
								append_row.insertCell(8).innerHTML="<i class='fas' style='font-size: 1.25em; color: orange; vertical-align: middle' onClick='submit2("+data[$j]['project_id']+")'>GO</i>";
							}
                            if(data[$j].active==0|data[$j].active==1|data[$j].active==2|data[$j].active==2.5){
                                append_row.insertCell(9).innerHTML="<span style='cursor: not-allowed'>???</span>";
                            }else{
                                append_row.insertCell(9).innerHTML=data[$j]["n"];
                            }
                            if(data[$j].active==0|data[$j].active==1|data[$j].active==2|data[$j].active==2.5){
                                append_row.insertCell(10).innerHTML="<span style='cursor: not-allowed'>???</span>";
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
                                append_row.insertCell(4).innerHTML="<span style='padding: 10%; color: #FFFFFF; background-color: #003B83; border-radius: 5px'><b>D ??????</b></span>";
                            }else if(data[$j].active==1){
                                append_row.insertCell(4).innerHTML="<span style='padding: 10%; color: #FFFFFF; background-color: orange; border-radius: 5px'><b>A ??????</b></span>";
                            }else if(data[$j].active==2){
                                append_row.insertCell(4).innerHTML="<span style='padding: 10% 7.5%; background-color: #FFFF00; border-radius: 5px'><b>B ?????????</b></span>";
                            }else if(data[$j].active==2.5){
								append_row.insertCell(4).innerHTML="<span style='padding: 10% 7.5%; background-color: #CCCCD6; border-radius: 5px'><b>????????????</b></span>";
							}else{
                                append_row.insertCell(4).innerHTML="<span style='padding: 10% 7.5%; background-color: #C3D825; border-radius: 5px'><b>C ?????????</b></span>";
                            }                        
                            if(data[$j].active==0|data[$j].active==2.5|data[$j].active==3){
                                append_row.insertCell(5).innerHTML="<span style='cursor: not-allowed'>???</span>";
                            }else{
                                append_row.insertCell(5).innerHTML="<button class='btn btn-primary' onClick='upload("+data[$j]['project_id']+")'><i class='fas fa-upload'></i></button>";
                            }                        
                            if(data[$j].active==0|data[$j].active==1){
                                append_row.insertCell(6).innerHTML="<span style='cursor: not-allowed'>???</span>";
                            }else{
                                append_row.insertCell(6).innerHTML="<a href='./preview.php?project_id="+data[$j]['project_id']+"'><i class='fas fa-search-plus' style='font-size: 1.375em; color: #003B83; vertical-align: middle'></i></a>";
                            }
                            if(data[$j].active==0|data[$j].active==1){
                                append_row.insertCell(7).innerHTML="<span style='cursor: not-allowed'>???</span>";
                            }else{
                                append_row.insertCell(7).innerHTML="<a href='./download.php?project_id="+data[$j]['project_id']+"'><button class='btn' style='color: #FFFFFF; background-color: #EF82A0'><i class='fas fa-download'></i></button></a>";
                            }
                            if(data[$j].active==0|data[$j].active==1|data[$j].active==3){
                                append_row.insertCell(8).innerHTML="<span style='cursor: not-allowed'>???</span>";
                            }else if(data[$j].active==2){
								append_row.insertCell(8).innerHTML="<i class='fas' style='font-size: 1.25em; color: orange; vertical-align: middle' onClick='submit1("+data[$j]['project_id']+")'>GO</i>";
							}else{
								append_row.insertCell(8).innerHTML="<i class='fas' style='font-size: 1.25em; color: orange; vertical-align: middle' onClick='submit2("+data[$j]['project_id']+")'>GO</i>";
							}
                            if(data[$j].active==0|data[$j].active==1|data[$j].active==2|data[$j].active==2.5){
                                append_row.insertCell(9).innerHTML="<span style='cursor: not-allowed'>???</span>";
                            }else{
                                append_row.insertCell(9).innerHTML=data[$j]["n"];
                            }
                            if(data[$j].active==0|data[$j].active==1|data[$j].active==2|data[$j].active==2.5){
                                append_row.insertCell(10).innerHTML="<span style='cursor: not-allowed'>???</span>";
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
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "??????????????????????????? (?????? <b style='color: red'>csv ???</b>)",
                            })  
                        }else if(data=="File Oversize"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "????????????????????????????????????",
                            })
							
                        }else if(data=="Header Q_sn"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "<b style='color: blue'>B ???</b> ???????????? <b style='color: red'>[q_sn]</b>????????????????????????????????????????????????",
                            })
                        }else if(data=="Header Q_txt"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "<b style='color: blue'>C ???</b> ???????????? <b style='color: red'>[q_txt]</b>????????????????????????????????????????????????",
                            })
                        }else if(data=="Header Type"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "<b style='color: blue'>D ???</b> ???????????? <b style='color: red'>[type]</b>????????????????????????????????????????????????",
                            })
                        }else if(data=="Header Opt_txt"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "<b style='color: blue'>E ???</b> ???????????? <b style='color: red'>[opt_txt]</b>????????????????????????????????????????????????",
                            })
                        }else if(data=="Header Opt_value"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "<b style='color: blue'>F ???</b> ???????????? <b style='color: red'>[opt_value]</b>????????????????????????????????????????????????",
                            })
                        }else if(data=="Header Annotate"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "<b style='color: blue'>G ???</b> ???????????? <b style='color: red'>[annotate]</b>????????????????????????????????????????????????",
                            })
                        }else if(data=="Header Note"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "<b style='color: blue'>H ???</b> ???????????? <b style='color: red'>[note]</b>????????????????????????????????????????????????",
                            })
                        }else if(data=="Header Disjoint"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "<b style='color: blue'>I ???</b> ???????????? <b style='color: red'>[disjoint]</b>????????????????????????????????????????????????",
                            })
                        }else if(data=="Header Range_min"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "<b style='color: blue'>J ???</b> ???????????? <b style='color: red'>[range_min]</b>????????????????????????????????????????????????",
                            })
                        }else if(data=="Header Range_max"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "<b style='color: blue'>K ???</b> ???????????? <b style='color: red'>[range_max]</b>????????????????????????????????????????????????",
                            })
                        }else if(data=="Header Skip"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "<b style='color: blue'>L ???</b> ???????????? <b style='color: red'>[skip]</b>????????????????????????????????????????????????",
                            })
						}else if(data=="Header Attach"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "<b style='color: blue'>M ???</b> ???????????? <b style='color: red'>[attach]</b>????????????????????????????????????????????????",
                            })
                            
                        }else if(data=="Missing Q_id"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[q_id]</b> ???????????????????????????????????????????????????????????????",
                            })   
                        }else if(data=="Missing Q_sn"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[q_sn]</b> ???????????????????????????????????????????????????????????????",
                            })   
                        }else if(data=="Missing Q_txt"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[q_txt]</b> ???????????????????????????????????????????????????????????????",
                            })   
                        }else if(data=="Missing Type"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[type]</b> ???????????????????????????????????????????????????????????????",
                            })   
                        }else if(data=="Missing Opt_txt"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[opt_txt]</b> ???????????????????????? <b style='color: blue'>?????????????????????</b> ???????????????????????????????????????",
                            })   
                        }else if(data=="Missing Opt_value"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[opt_value]</b> ???????????????????????? <b style='color: blue'>?????????????????????</b> ???????????????????????????????????????",
                            })   
                        }else if(data=="Missing Note"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[note]</b> ???????????????????????? <b style='color: blue'>?????????????????????</b> ???????????????????????????????????????",
                            })   
                        }else if(data=="Missing Disjoint"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[disjoint]</b> ???????????????????????? <b style='color: blue'>?????????</b> ???????????????????????????????????????",
                            })
						}else if(data=="Missing Skip"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[skip]</b> ???????????????????????? <b style='color: blue'>?????????????????????</b> ???????????????????????????????????????",
                            })
                            
                        }else if(data=="Invalid Q_id"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[q_id]</b> ???????????????????????????????????????<b style='color: blue'>??????</b>?????????????????????",
                            })
                        }else if(data=="Invalid Q_sn"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[q_sn]</b> ???????????????????????????????????????<b style='color: blue'>??????</b>?????????????????????",
                            })           
                        }else if(data=="Invalid Type"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[type]</b> ??????????????????????????????????????? <b style='color: blue'>0,1,2,3,4,9</b>?????????????????????",
                            })
                        }else if(data=="Invalid Opt_value"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[opt_value]</b> ???????????????????????????????????????<b style='color: blue'>??????</b>?????????????????????",
                            })                            
                        }else if(data=="Invalid Note"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[note]</b> ??????????????????????????????????????? <b style='color: blue'>0,1</b>?????????????????????",
                            })
                        }else if(data=="Invalid Disjoint"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[disjoint]</b> ??????????????????????????????????????? <b style='color: blue'>0,1</b>?????????????????????",
                            })
                        }else if(data=="Invalid Range_min"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[range_min]</b> ???????????????????????????????????????<b style='color: blue'>??????</b>?????????????????????",
                            })
                        }else if(data=="Invalid Range_max"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[range_max]</b> ???????????????????????????????????????<b style='color: blue'>??????</b>?????????????????????",
                            })
                        }else if(data=="Invalid Skip"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[skip]</b> ???????????????????????????????????????<b style='color: blue'>[q_id-q_sn(,q_id-q_sn...)]</b>?????????????????????",
                            })
                            
                        }else if(data=="Other Wrong"){
                            $("#submit2").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ????????????",
                                content: "???????????????????????????????????????????????????????????????????????? <b style='color: blue'>?????????????????? (????????????????????????)?????????????????????/??????</b> ???????????????????????????????????????????????????",
                            }) 
                        }else{
                            $.confirm({
                                title: "<i class='fas fa-check-circle' style='color: blue'></i> ????????????",
                                content: "??????????????????????????? <i class='fas fa-search-plus' style='color: #003B83'></i> ?????????????????????",
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
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "??????????????????????????? (?????? <b style='color: red'>csv ???</b>)",
                            })  
                        }else if(data=="File Oversize"){
                            $("#submit3").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "????????????????????????????????????",
                            })
                        
                        }else if(data=="Header Random_code"){
                            $("#submit3").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "<b style='color: blue'>B ???</b> ???????????? <b style='color: red'>[random_code]</b>????????????????????????????????????????????????",
                            })    
                        }else if(data=="Missing Sample_id"){
                            $("#submit3").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[sample_id]</b> ???????????????????????????????????????????????????????????????",
                            })   
                        }else if(data=="Missing Random_code"){
                            $("#submit3").attr("disabled", false);
                            $.alert({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "?????????????????????????????? <b style='color: red'>[random_code]</b> ???????????????????????????????????????????????????????????????",
                            })
                        
                        }else if(data=="Different Sample_size"){
                            $("#submit3").attr("disabled", false);
                            $.confirm({
                                title: "<i class='fas fa-times-circle' style='color: red'></i> ??????????????????",
                                content: "???????????????????????????<b style='color: red'>????????????????????????????????????</b>???????????????????????????????????????",
                                buttons:{
                                    "??????": function(){},
                                    "OK": function(){
                                        $.ajax({
                                            type: "POST", 
                                            url: "",
                                            data: {alterSampleSize: 1, project_id3: project_id3},
                                            success: function(data){
                                                console.log(data);
                                                $.confirm({
                                                    title: "<i class='fas fa-check-circle' style='color: blue'></i> ????????????",
                                                    content: "??????????????????????????????????????????",
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
                                title: "<i class='fas fa-check-circle' style='color: blue'></i> ????????????",
                                content: "??????????????????????????????????????????",
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
        <div class="title"><b>????????????</b></div>
    </div>
    
    <div id="project_append" class="modal fade">
        <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size: 1.25em; background-color: yellow">??? ???????????? ???</h5>
                <button class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body" style="padding-bottom: 1em">
                <form id="projectAppendForm"><br>
                    <div class="row">
                        <div class="col-sm-5 pl-0 pr-0">
                            <div class="input-group-text">A. ???????????????</div>
                        </div>
                        <div class="col-sm-7 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="text" name="project_name" required>
                        </div>
                    </div>     
                    <div class="row">
                        <div class="col-sm-5 pl-0 pr-0">
                            <div class="input-group-text">B. ??????????????????</div>
                        </div>
                        <div class="col-sm-7 pl-0 pr-0">
                            <select name="id" class="form-control btn-outline-secondary">
                                <option value="">?????????</option>
                                <?php foreach($_SESSION["all_acc"]as $r){?>
                                <option value="<?=$r["id"]?>"><?=$r["name"]?></option>
                                <?php }?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-5 pl-0 pr-0">
                            <div class="input-group-text">C. ???????????????</div>
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
                <h5 class="modal-title" style="font-size: 1.25em; background-color: yellow">??? ???????????? ???</h5>
                <button class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body" style="padding-bottom: 1em">
                <form id="uploadCsvForm"><br>
                    <div class="row">
                        <div class="col-sm-4 pl-0 pr-0">
                            <div class="input-group-text">A. ????????????</div>
                        </div>
                        <div class="col-sm-8 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="text" name="project_id1" style="cursor: not-allowed" readonly>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 pl-0 pr-0">
                            <div class="input-group-text">B. ???????????????</div>
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
                <h5 class="modal-title" style="font-size: 1.25em; background-color: yellow">??? ???????????? ???</h5>
                <button class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body" style="padding-bottom: 1em">
                <form id="submitCsvForm"><br>
                    <div class="row">
                        <div class="col-sm-4 pl-0 pr-0">
                            <div class="input-group-text">A. ????????????</div>
                        </div>
                        <div class="col-sm-8 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="text" name="project_id3" style="cursor: not-allowed" readonly>
                        </div>
                    </div>
					<p class="row" style="font-size: 0.8em; margin: 2.5% auto"><b><i class='fa fa-info-circle'></i> ??????????????????????????????????????????????????????</b></p>
					<div class="row">
                        <div class="col-sm-4 pl-0 pr-0">
                            <div class="input-group-text">B. ???????????????</div>
                        </div>
                        <div class="col-sm-8 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="date" name="end_date" style="font-size: 0.9em" required>
                        </div>
                    </div>
                    <p class="row" style="font-size: 0.8em; margin: 2.5% auto"><b><i class='fa fa-info-circle'></i> ?????????????????????????????????????????????????????????</b></p>
                    <div class="row">
                        <div class="col-sm-4 pl-0 pr-0">
                            <div class="input-group-text">C. ???????????????</div>
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
                <b style='font-size: 1.1em'>????????????<button class="close"><a data-toggle="collapse" href=".card-body">&times;</a></button></b>
            </div>
            <div class="card-body show" style="padding: 2% 10% 0% 10%">
				<?php if($_SESSION["acc_info"]["level"]==1){?>
					<p style="padding-top: 1%; text-align: center">???????????? <button class="btn btn-secondary" id="create_project1" style="margin-top: -0.25em"><i class="fas fa-plus"></i> ??????</button> ??????????????????????????????????????? <span style="padding: 1%; color: #FFFFFF; background-color: orange; border-radius: 5px;"><b>A ??????</b></span>???????????????????????????????????????????????????</p>
                <?php }else{?>
					<p style="padding-top: 1%; text-align: center">??????????????? <span style="padding: 1%; color: #FFFFFF; background-color: orange; border-radius: 5px;"><b>A ??????</b></span>???????????????????????????????????????????????????</p>
				<?php }?>
				
                <table class="table rule" style="text-align: center">
                    <thead>
                        <td style="line-height: 1em">????????????</td>
                        <td style="line-height: 1em">????????????</td>
                        <td style="line-height: 1em">????????????</td>
                    </thead>
                    <tr>
                        <td style="line-height: 2em; vertical-align: middle"><span style="padding: 10%; color: #FFFFFF; background-color: orange; border-radius: 5px"><b>A ??????</b></span></td>
                        <td style="line-height: 2em; vertical-align: middle">????????????????????????????????????</td>
                        <td style="line-height: 2em; vertical-align: middle"><button class="btn btn-primary"><i class="fas fa-upload"></i></button></td>
                    </tr>
                    <tr>
                        <td style="line-height: 2em; vertical-align: middle"><span style="padding: 10% 7.5%; background-color: #FFFF00; border-radius: 5px"><b>B ?????????</b></span></td>
                        <td style="line-height: 2em; vertical-align: middle">??????????????????????????????????????????????????????<br>????????????????????????????????????<b style="color: red">????????????????????????????????????????????????</b></td>
                        <td style="line-height: 2em; vertical-align: middle"><button class="btn btn-primary"><i class="fas fa-upload"></i></button>???<i class="fas fa-search-plus" style="font-size: 1.375em; color: #003B83; vertical-align: middle"></i>???<button class="btn" style="color: #FFFFFF; background-color: #EF82A0"><i class="fas fa-download"></i></button>???<i class="fas" style="font-size: 1.25em; color: orange; vertical-align: middle">GO</i></td>
                    </tr>
                    <tr>
                        <td style="line-height: 2em; vertical-align: middle"><span style="padding: 10% 7.5%; background-color: #C3D825; border-radius: 5px"><b>C ?????????</b></span></td>
                        <td style="line-height: 2em; vertical-align: middle">??????????????????????????????????????????????????????<br>????????????<b style="color: red">????????????????????????</b>??????????????????????????????????????????</td>
                        <td style="line-height: 2em; vertical-align: middle"><i class="fas fa-search-plus" style="font-size: 1.375em; color: #003B83; vertical-align: middle"></i>???<button class="btn" style="color: #FFFFFF; background-color: #EF82A0"><i class="fas fa-download"></i></button></td>
                    </tr>
                    <tr>
                        <td style="line-height: 2em; vertical-align: middle"><span style="padding: 10%; color: #FFFFFF; background-color: #003B83; border-radius: 5px"><b>D ??????</b></span></td>
                        <td style="line-height: 2em; vertical-align: middle">????????????????????????</td>
                        <td style="line-height: 2em; vertical-align: middle"><i class="fa fa-ban" style="font-size: 1.375em; color: #FF0000; vertical-align: middle"></i></td>
                    </tr>
                </table>
            </div>
        </div>
    </div><br>
        
    <div class="container">
		<?php if($_SESSION["acc_info"]["level"]==1){?>
			<button class="btn btn-secondary" id="create_project2" style="margin: 2% 1%"><i class="fas fa-plus"></i> ??????</button>
		<?php }?>
        <div style="float: right; padding: 2.5% 0%">?????????<input id="search"></div>
        
        <table id="project_list" class="table table-striped" style="text-align: center" cellspacing="0">
            <thead>
                <td>?????????</td>
                <td style="width: 17.5%">????????????</td>
                <td>?????????</td>
                <td>?????????</td>
                <td style="width: 10%">????????????</td>
                <td>????????????</td>
                <td>????????????</td>
                <td style="line-height: 1.25em">??????<br><b style="color: red">??????</b>??????</td>
				<td>????????????</td>
                <td>????????????</td>
                <td>????????????</td>
            </thead>
            <tr class="list">
            </tr>
        </table><br>
        
        ????????? <span class="project_n1"></span>???<span class="project_n2"></span> ??????????????? <span class="project_n2"></span> ???
    </div>
    
    <?php include("footer.php");?>
    
    <script>
        function upload(project_id){
            $.confirm({
				title: "",
				content: "?????????<br>????????????????????????????????????<b style='color: red'>?????????????????????????????????????????????</b>???????????????????????????????????????",
				buttons:{
					"??????": function(){},
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
				content: "?????????<br>???????????????<b style='color: red'>???????????????????????????</b>?????????????????????????????????",
				buttons:{
					"??????": function(){},
					"<i class='fas' style='color: orange'>GO</i>": function(){
						$.ajax({
                            type: "POST", 
                            url: "",
                            data: {submitProject: 1, project_id2: project_id},
                            success: function(data){
                                console.log(data);
								$.confirm({
									title: "",
									content: "????????????????????????????????????????????????????????????",
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
