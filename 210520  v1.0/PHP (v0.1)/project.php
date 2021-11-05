<?php
	session_start();
	include("db.php");
    include("upload_function.php");

    if(!$_SESSION['acc_info']['id']){
		header("Location: ./index.php");
    }

    if(isset($_POST["fetchProject"])){
        if($_SESSION['acc_info']['level']==1){
            $sql1="SELECT * FROM `project` LEFT JOIN `account` ON account.project_id=project.project_id ";
            $stmt=$db->prepare($sql1);
            $stmt->execute();
            
            $json=array();
            while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
                $json[]=$row;
            }
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
        }else{
            $sql2="SELECT * FROM `project` LEFT JOIN `account` ON account.project_id=project.project_id WHERE account.id= :v1";
            $stmt=$db->prepare($sql2);
            $stmt->bindParam(':v1', $_SESSION['acc_info']['id']);
            $stmt->execute();
            
            $json=array();
            while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
                $json[]=$row;
            }
            echo json_encode($json, JSON_UNESCAPED_UNICODE);
        }
        exit();
    }

    if(isset($_FILES['csv_file'])){
        $files=getFiles();
        $res=uploadFile($files[0]);
        $project_id=$_POST['project_id'];
        $fname=$_POST['fname'];
        $fpath=$res['dest'];
        
        //putenv('PATH="C:\***"');
        //exec("chcp 65001;");
        //exec("sudo Rscript u.r ");
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
            width: 100%; top: 20%; left: 0; letter-spacing: 0.05em;
            color: #2E317C;
            font-size: 1.8em; font-weight: bold; text-align: center; position: absolute;
        }
        
        .container{
			width: 70%; margin: 20px auto; letter-spacing: 0.05em;
            font-size: 0.95em; align-content: center;
		}
        
        td{
            line-height: 2em;
        }
        
        .btn-warning, .btn-info{
            font-size: 0.95em;
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
                        
                        append_row.insertCell(0).innerHTML=data[$i]['project_id'];
                        append_row.insertCell(1).innerHTML=data[$i]['project_name'];
                        append_row.insertCell(2).innerHTML=data[$i]['name'];
                        if(data[$i].active==1){
                            append_row.insertCell(3).innerHTML="開啟";
                        }else{
                            append_row.insertCell(3).innerHTML="關閉";
                        }
                        append_row.insertCell(4).innerHTML='<button class="btn btn-warning" style="width: 70%"><input id="uploadcsv" type="file"></button> <button class="btn btn-info" onClick="upload_csv('+data[$i]["project_id"]+')">上傳</button>';
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
        })
	</script>
</head>
    

<body>
	<nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <!--1.Icon-->
		<a class="navbar-brand" href="./project.php" style="background-color: #FFFFFF; -webkit-border-radius: 10px; border-radius: 10px;">
            <b><span style="padding: 1em 0.25em">幼兒點日記</span></b>
		</a>
        
        <!--2.Toggler-->
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
        <img style="width: 15%" src="./pic/square.png">
        <div class="title">專案管理</div>
    </div><br><br>
    
    <div class="container">
        <table id="project_list" class="table table-striped" style="text-align: center" cellspacing="0">
            <thead>
                <td>專案編號</td>
                <td>專案名稱</td>
                <td>專案負責人</td>
                <td>專案狀態</td>
                <td style="width: 30%">功能操作</td>
            </thead>
            <tr class="list">
            </tr>
        </table><br>
        
        顯示第 <span class="alter_n1"></span> 至 <span class="alter_n2"></span> 項結果，共 <span class="alter_n2"></span> 項
    </div>
    
    
    <script>
		function upload_csv(project_id){
            var file=$("#uploadcsv").prop('files')[0];
            var project_id= project_id;
            var filename=$("#uploadcsv").get(0).files[0].name;
            
            var data=new FormData();
            data.append('csv_file', file);
            data.append('project_id', project_id);
            data.append('fname', filename);
            
            $.ajax({
                type: 'POST',
                url: "",
                data: data,
                cache: false,
                processData: false,
                contentType: false,
                success: function(data){
                    console.log(data)
                }
            })
        }
	</script>
</body>
</html>
