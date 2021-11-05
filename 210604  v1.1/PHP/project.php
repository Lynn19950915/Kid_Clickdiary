<?php
	session_start();
	include("db.php");
    include("upload_function.php");

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
        if($_SESSION["acc_info"]["level"]==1){
            $sql3="INSERT INTO `project` VALUES (NULL, :v2, :v3, 1, NULL, :v6, 0)";
            $stmt=$db->prepare($sql3);
            $stmt->bindParam(":v2", $_POST['id']);
            $stmt->bindParam(":v3", $_POST["project_name"]);
            $stmt->bindParam(":v6", $_POST["sample_size"]);
            $stmt->execute();
            
            $sql4="SELECT * FROM `project` LEFT JOIN `account` ON project.id=account.id";
            $stmt=$db->prepare($sql4);
            $stmt->execute();
            
            $json=array();
            while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
                $json[]=$row;
            }
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
        }else{
            $sql5="INSERT INTO `project` VALUES (NULL, :v2, :v3, 1, NULL, :v6, 0)";
            $stmt=$db->prepare($sql5);
            $stmt->bindParam(":v2", $_SESSION["acc_info"]["id"]);
            $stmt->bindParam(":v3", $_POST["project_name"]);
            $stmt->bindParam(":v6", $_POST["sample_size"]);
            $stmt->execute();
            
            $sql6="SELECT * FROM `project` LEFT JOIN `account` ON project.id=account.id WHERE account.id= :v1";
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

    if(isset($_FILES["csv_file"])){
        $files=getFiles();
        $res=uploadFile($files[0]);
        
        putenv('PATH="C:\***"');
        exec("C:\Windows\System32\chcp 65001");
        exec("Rscript schema.R ".$_POST["project_id"]." ".$_POST["fname"]);
		
		$sql7="UPDATE `project` SET active=2 WHERE project_id= :v1";
		$stmt=$db->prepare($sql9);
		$stmt->bindParam(":v1", $_POST["project_id"]);
		$stmt->execute();
		
		$sql8="DROP TABLE IF EXISTS `:v1'test'`";
		$stmt=$db->prepare($sql10);
		$stmt->bindParam(":v1", $_POST["project_id"]);
		$stmt->execute();
		
		$sql9="CREATE TABLE `:v1'test'`(`record_id` INT AUTO_INCREMENT, `time` TIMESTAMP NOT NULL, `record` VARCHAR(10000) NOT NULL, PRIMARY KEY(`record_id`))";
		$stmt=$db->prepare($sql11);
		$stmt->bindParam(":v1", $_POST["project_id"]);
		$stmt->execute();
		
        exit();
    }

    if(isset($_POST["project_id2"])){
        $sql10="UPDATE `project` SET active=3 WHERE project_id= :v1";
		$stmt=$db->prepare($sql12);
		$stmt->bindParam(":v1", $_POST["project_id2"]);
		$stmt->execute();
        
        $sql11="CREATE TABLE `:v1'official'`(`record_id` INT AUTO_INCREMENT, `time` TIMESTAMP NOT NULL, `record` VARCHAR(10000) NOT NULL, PRIMARY KEY(`record_id`))";
		$stmt=$db->prepare($sql13);
		$stmt->bindParam(":v1", $_POST["project_id2"]);
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
	<script src="http://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
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
            padding-top: 100px; padding-bottom: 100px;
        }
        
        nav{
            background-color: #5BC85C;
        }
        
        .wrap{
            width: 100%; margin: 20px auto;
            display: inline-block; position: relative; text-align: center;
        }
                
        .title{
            width: 100%; top: 15%; letter-spacing: 0.05em;
            color: #2E317C;
            font-size: 1.75em; font-weight: bold; text-align: center; position: absolute;
        }
        
        .container{
			width: 75%; margin: 10px auto; letter-spacing: 0.05em;
            font-size: 0.85em; align-content: center;
		}
        
        hr{
            height: 1%;
        }
        
        td{
            line-height: 2.5em;
        }
        
        .btn, .input-group-text{
            font-size: 0.95em;
        }
        
        .row{
            width: 75%; margin: 0px auto;
        }
        
        .icon{
			width: 8.75em;
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
                            append_row.insertCell(4).innerHTML="關閉";
                        }else if(data[$i].active==1){
                            append_row.insertCell(4).innerHTML="開啟";
                        }else if(data[$i].active==2){
                            append_row.insertCell(4).innerHTML="<span style='padding: 7.5%; background-color: yellow'><b>測試中</b></span>";
                        }else{
                            append_row.insertCell(4).innerHTML="<span style='padding: 7.5%; background-color: yellow'><b>已上線</b></span>";
                        }                        
                        if(data[$i].active==0|data[$i].active==3){
                            append_row.insertCell(5).innerHTML="<span style='cursor: not-allowed'>　</span>";
                        }else{
                            append_row.insertCell(5).innerHTML="<button class='btn btn-info' onClick='upload("+data[$i]['project_id']+")'><i class='fas fa-upload'></i></button>";
                        }                        
                        if(data[$i].active==0|data[$i].active==1){
                            append_row.insertCell(6).innerHTML="<span style='cursor: not-allowed'>　</span>";
                        }else{
                            append_row.insertCell(6).innerHTML="<a href='./preview.php?project_id="+data[$i]['project_id']+"'><i class='fas fa-search-plus' style='font-size: 1.375em; color: blue; vertical-align: middle'></i></a>";
                        }
                        if(data[$i].active==0|data[$i].active==1){
                            append_row.insertCell(7).innerHTML="<span style='cursor: not-allowed'>　</span>";
                        }else{
                            append_row.insertCell(7).innerHTML="<a href='./download1.php?project_id="+data[$i]['project_id']+"'><button class='btn btn-danger'><i class='fas fa-download'></i></button></a>";
                        }
                        if(data[$i].active==0|data[$i].active==1|data[$i].active==3){
                            append_row.insertCell(8).innerHTML="<span style='cursor: not-allowed'>　</span>";
                        }else{
                            append_row.insertCell(8).innerHTML="<i class='fas' style='font-size: 1.25em; color: orange; vertical-align: middle' onClick='submit("+data[$i]['project_id']+")'>GO</i>";
                        }
                        if(data[$i].active==0|data[$i].active==1|data[$i].active==2){
                            append_row.insertCell(9).innerHTML="<span style='cursor: not-allowed'>　</span>";
                        }else{
                            append_row.insertCell(9).innerHTML=data[$i]["n"];
                        }
                        if(data[$i].active==0|data[$i].active==1|data[$i].active==2){
                            append_row.insertCell(10).innerHTML="<span style='cursor: not-allowed'>　</span>";
                        }else{
                            append_row.insertCell(10).innerHTML="<a href='./download2.php?project_id="+data[$i]['project_id']+"'><button class='btn btn-danger'><i class='fas fa-download'></i></button></a>";
                        }
                    }
                    
                    if(data.length==0){
                        $(".alter_n1").empty().append(0);
                        $(".alter_n2").empty().append(0);
                    }else{
                        $(".alter_n1").empty().append(1);
                        $(".alter_n2").empty().append(data.length);
                    }
                }, error: function(e){
                    console.log(e);
                }     
            })
            
            $("#create_project1").on('click', function(event){
			    event.preventDefault();
			    $("#project_append").modal("show");
			})
            
            $("#create_project2").on('click', function(event){
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
                                append_row.insertCell(4).innerHTML="關閉";
                            }else if(data[$j].active==1){
                                append_row.insertCell(4).innerHTML="開啟";
                            }else if(data[$j].active==2){
                                append_row.insertCell(4).innerHTML="<span style='padding: 7.5%; background-color: yellow'><b>測試中</b></span>";
                            }else{
                                append_row.insertCell(4).innerHTML="<span style='padding: 7.5%; background-color: yellow'><b>已上線</b></span>";
                            }                        
                            if(data[$j].active==0|data[$j].active==3){
                                append_row.insertCell(5).innerHTML="<span style='cursor: not-allowed'>　</span>";
                            }else{
                                append_row.insertCell(5).innerHTML="<button class='btn btn-info' onClick='upload("+data[$j]['project_id']+")'><i class='fas fa-upload'></i></button>";
                            }                        
                            if(data[$j].active==0|data[$j].active==1){
                                append_row.insertCell(6).innerHTML="<span style='cursor: not-allowed'>　</span>";
                            }else{
                                append_row.insertCell(6).innerHTML="<a href='./preview.php?project_id="+data[$j]['project_id']+"'><i class='fas fa-search-plus' style='font-size: 1.375em; color: blue; vertical-align: middle'></i></a>";
                            }
                            if(data[$j].active==0|data[$j].active==1){
                                append_row.insertCell(7).innerHTML="<span style='cursor: not-allowed'>　</span>";
                            }else{
                                append_row.insertCell(7).innerHTML="<a href='./download1.php?project_id="+data[$j]['project_id']+"'><button class='btn btn-danger'><i class='fas fa-download'></i></button></a>";
                            }
                            if(data[$j].active==0|data[$j].active==1|data[$j].active==3){
                                append_row.insertCell(8).innerHTML="<span style='cursor: not-allowed'>　</span>";
                            }else{
                                append_row.insertCell(8).innerHTML="<i class='fas' style='font-size: 1.25em; color: orange; vertical-align: middle' onClick='submit("+data[$j]['project_id']+")'>GO</i>";
                            }
                            if(data[$j].active==0|data[$j].active==1|data[$j].active==2){
                                append_row.insertCell(9).innerHTML="<span style='cursor: not-allowed'>　</span>";
                            }else{
                                append_row.insertCell(9).innerHTML=data[$j]["n"];
                            }
                            if(data[$j].active==0|data[$j].active==1|data[$j].active==2){
                                append_row.insertCell(10).innerHTML="<span style='cursor: not-allowed'>　</span>";
                            }else{
                                append_row.insertCell(10).innerHTML="<a href='./download2.php?project_id="+data[$i]['project_id']+"'><button class='btn btn-danger'><i class='fas fa-download'></i></button></a>";
                            }
                        }
                    
                        if(data.length==0){
                            $(".alter_n1").empty().append(0);
                            $(".alter_n2").empty().append(0);
                        }else{
                            $(".alter_n1").empty().append(1);
                            $(".alter_n2").empty().append(data.length);
                        }
                        $("#project_append").modal("hide");
                    }, error: function(e){
                        console.log(e);
                    }
                })
            })
            
            $("#uploadForm1").on('submit', function(event){
                event.preventDefault();
                $("#submit2").attr("disabled", true);
                
                var file=$("#csvfile1").prop("files")[0];
                var project_id=$("input[name='project_id1']").val();
                var filename=$("#csvfile1").get(0).files[0].name;
                
                var data=new FormData();
                data.append("csv_file", file);
                data.append("project_id", project_id);
                data.append("fname", filename);
                
                $.ajax({
                    type: 'POST',
                    url: "",
                    data: data,
                    cache: false,
                    processData: false,
                    contentType: false,
                    success: function(data){
                        console.log(data);
                        $.confirm({
                            title: "",
                            content: "上傳成功，您可以預覽問卷囉",
                            buttons:{
                                "OK": function(){
                                    window.location.href="./project.php";
                                }
                            }
                        })
                    }
                })
            })
            
            $("#uploadForm2").on('submit', function(event){
                event.preventDefault();
                $("#submit3").attr("disabled", true); 
                
                $.ajax({
                    type: 'POST',
                    url: "",
                    data: $('#uploadForm2').serialize(),
                    success: function(data){
                        console.log(data);
                        $.confirm({
                            title: "",
                            content: "問卷已上線！",
                            buttons:{
                                "OK": function(){
                                    window.location.href="./project.php";
                                }
                            }
                        })
                    }
                })
            })
        })
	</script>
</head>
    

<body>
	<nav class="navbar navbar-expand-lg navbar-light fixed-top">
		<a class="navbar-brand" href="./project.php" style="margin: 0.25em; background-color: #FFFFFF; -webkit-border-radius: 10px; border-radius: 10px;">
            <b><span style="padding: 1em 0.25em">幼兒點日記</span></b>
		</a>
        
		<button class="navbar-toggler" data-toggle="collapse" data-target="#option">
			<span class="navbar-toggler-icon"></span>
		</button>
        
		<div id="option" class="collapse navbar-collapse">            
            <ul class="navbar-nav ml-auto">
				<li class="nav-item mr-3"><a href="./logout.php" style="color: white"><b>登出</b></a></li>
			</ul>
		</div>
	</nav>
    
    <div class="wrap">
        <img style="width: 12.5%" src="./pic/square.png">
        <div class="title">專案管理</div>
    </div>
    
    <div id="project_append" class="modal fade" style="width: 38%; left: 31%; top: 25%">
        <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size: 1.25em; background-color: yellow">⭐ 新增專案 ⭐</h5>
                <button class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body" style="padding-bottom: 1em">
                <form id="projectAppendForm"><br>
                    <div class="row">
                        <div class="col-sm-4 pl-0 pr-0">
                            <div class="input-group-text">A. 專案名稱：</div>
                        </div>
                        <div class="col-sm-8 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="text" name="project_name" required>
                        </div>
                    </div>     
                    <div class="row">
                        <div class="col-sm-4 pl-0 pr-0">
                            <div class="input-group-text">B. 專案負責人：</div>
                        </div>
                        <div class="col-sm-8 pl-0 pr-0">
                            <?php if($_SESSION["acc_info"]["level"]==1){?>
                            <input class="form-control btn-outline-secondary" type="text" name="id" required>
                            <?php }else{?>
                            <input class="form-control btn-outline-secondary" type="text" name="id" value="<?=$_SESSION["acc_info"]["name"]?>" style="cursor: not-allowed" readonly>
                            <?php }?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 pl-0 pr-0">
                            <div class="input-group-text">C. 樣本數量：</div>
                        </div>
                        <div class="col-sm-8 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="number" name="sample_size" required>
                        </div>
                    </div><br>

                    <div style="text-align: center">
                        <button id="submit1" class="btn">
                            <img src="/pic/submit.png" class="icon" style="width: 7.5em">
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>
    
    <div id="upload_csv" class="modal fade" style="width: 38%; left: 31%; top: 25%">
        <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size: 1.25em; background-color: yellow">⭐ 上傳題目 ⭐</h5>
                <button class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body" style="padding-bottom: 1em">
                <form id="uploadForm1"><br>
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
                            <div class="input-group-text">B. csv 檔案：</div>
                        </div>
                        <div class="col-sm-8 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="file" id="csvfile1" style="padding: 1.5% 3%" required>
                        </div>
                    </div><br>

                    <div style="text-align: center">
                        <button id="submit2" class="btn">
                            <img src="/pic/submit.png" class="icon" style="width: 7.5em">
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>
    
    <div id="submit_csv" class="modal fade" style="width: 38%; left: 31%; top: 25%">
        <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size: 1.25em; background-color: yellow">⭐ 上線確認 ⭐</h5>
                <button class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body" style="padding-bottom: 1em">
                <form id="uploadForm2"><br>
                    <div class="row">
                        <div class="col-sm-4 pl-0 pr-0">
                            <div class="input-group-text">A. 專案號：</div>
                        </div>
                        <div class="col-sm-8 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="text" name="project_id2" style="cursor: not-allowed" readonly>
                        </div>
                    </div>
                    <p class="row" style="font-size: 0.8em; margin: 2.5% auto"><b><i class='fa fa-info-circle'></i> 此為專案上線後，可訪問該連結的有效 url 清單。若有疑問請聯繫管理人員。</b></p>
                    <div class="row">
                        <div class="col-sm-4 pl-0 pr-0">
                            <div class="input-group-text">B. url 檔案：</div>
                        </div>
                        <div class="col-sm-8 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="file" id="csvfile2" style="padding: 1.5% 3%">
                        </div>
                    </div><br>

                    <div style="text-align: center">
                        <button id="submit3" class="btn">
                            <img src="/pic/submit.png" class="icon" style="width: 7.5em">
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>
    
    <div class="container" style="width: 65%; padding-top: 1%">
        <div class="card" style="border: 0.1em solid gold">     
            <div class="card-header" style="background-color: gold">
                <b>使用說明<button class="close"><a data-toggle="collapse" href=".card-body">&times;</a></button></b>
            </div>
            <div class="card-body show">
                <p>您可點擊  <button class="btn btn-secondary" id="create_project1" style="margin-top: -0.25%"><i class="fas fa-plus"></i> 新增專案</button> 建立一個新專案，不同的專案狀態對應相異的操作權限，分為以下幾種：</p>
                
                <div>1. 開啟：建立後尚未上傳問題（csv 檔案），故無法使用預覽填寫、資料下載及上線等功能。</div><hr>                
                <p>2. <span style='padding: 0.5%; background-color: yellow'><b>測試中</b></span>：上傳題目後尚未確認上線。此階段為內部測試，您可針對命題內容做版型預覽、填寫，並下載完整測試資料。</p>
                <div>註：若需要修改問題，您可重新上傳 csv 檔案，惟<b style="color: red">先前的版本內容及測試資料將被覆寫取代</b>，請特別注意。</div><hr>
                <p>3. <span style='padding: 0.5%; background-color: yellow'><b>已上線</b></span>：上傳樣本 url 清單後即完成上線。您可即時掌握當前已回收樣本數，並下載完整正式資料。</p>
                <div>註：您仍可針對題目做預覽、填寫及下載等工作，但<b style="color: red">無法重新再上傳檔案</b>，上線確認前請務必檢查是否為最終定版。</div><hr>
                <div>4. 關閉：專案中止或已結束，所有操作權限均鎖定。</div>
            </div>
        </div>
    </div><br>
        
    <div class="container" style="margin-top: -1%">
        <button class="btn btn-secondary" id="create_project2" style="margin: 2% 1%"><i class="fas fa-plus"></i> 新增專案</button>
        <div style="float: right; padding-top: 2.5%">搜尋：<input id="search"></div>
        
        <table id="project_list" class="table table-striped" style="text-align: center" cellspacing="0">
            <thead>
                <td>專案號</td>
                <td>專案名稱</td>
                <td>負責人</td>
                <td>樣本數</td>
                <td>專案狀態</td>
                <td>上傳題目</td>
                <td>預覽填寫</td>
                <td style="line-height: 1.25em">下載<br><b style="color: red">測試</b>資料</td>
				<td>確認上線</td>
                <td>目前樣本</td>
                <td style="line-height: 1.25em">下載<br><b style="color: red">正式</b>資料</td>
            </thead>
            <tr class="list">
            </tr>
        </table><br>
        
        顯示第 <span class="alter_n1"></span> - <span class="alter_n2"></span> 項結果，共 <span class="alter_n2"></span> 項
    </div> 
    
    <script>
        function upload(project_id){
            $.confirm({
				title: "",
				content: "注意：若您原先已上傳過，既有版本(以及測試資料)將被覆蓋。是否仍要上傳檔案？",
				buttons:{
					"返回": function(){},
					"OK": function(){
						$("#upload_csv").modal("show");
                        $("input[name='project_id1']").val(project_id);
					}
				}
			})
        }
        
        function submit(project_id){
            $.confirm({
				title: "",
				content: "注意：專案上線後即無法再修改題目內容，是否仍要上線？",
				buttons:{
					"返回": function(){},
					"OK": function(){
						$("#submit_csv").modal("show");
                        $("input[name='project_id2']").val(project_id);
					}
				}
			})
        }
	</script>
</body>
</html>
