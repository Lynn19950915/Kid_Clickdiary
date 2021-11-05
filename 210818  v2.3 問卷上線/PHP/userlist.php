<?php
	session_start();
	include("db.php");

    if(!$_SESSION["acc_info"]["id"]|$_SESSION["acc_info"]["level"]==2){
		header("Location: ./index.php");
    }

    if(isset($_POST["fetchUser"])){
		$sql1="SELECT *, from_base64(password) FROM `account` WHERE level=2";
		$stmt=$db->prepare($sql1);
		$stmt->execute();
		
		$json=array();
        while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
            $json[]=$row;
        }
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
        exit();
    }

    if(isset($_POST["user_name"])){
		$password1_encoded=base64_encode($_POST['user_password1']);
		
        $sql2="INSERT INTO `account` VALUES (NULL, :v2, :v3, :v4, 2)";
		$stmt=$db->prepare($sql2);
		$stmt->bindParam(":v2", $_POST['user_name']);
		$stmt->bindParam(":v3", $_POST["user_email"]);
		$stmt->bindParam(":v4", $password1_encoded);
		$stmt->execute();
		
		$sql3="SELECT *, from_base64(password) FROM `account` WHERE level=2";
		$stmt=$db->prepare($sql3);
		$stmt->execute();
		
		$json=array();
		while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
			$json[]=$row;
		}
		echo json_encode($json, JSON_UNESCAPED_UNICODE);       
        exit();
	}
	
	if(isset($_POST["searchUser"])){
		$search=$_POST['search'];
		
        $sql4="SELECT *, from_base64(password) FROM `account` WHERE level=2 and name LIKE '%$search%'";
		$stmt=$db->prepare($sql4);
		$stmt->execute();
		
		$json=array();
		while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
			$json[]=$row;
		}
		echo json_encode($json, JSON_UNESCAPED_UNICODE);       
        exit();
	}
	
	if(isset($_POST["user_id"])){
		$password2_encoded=base64_encode($_POST['user_password2']);
		
        $sql5="UPDATE `account` SET password= :v1 WHERE id= :v2";
		$stmt=$db->prepare($sql5);
		$stmt->bindParam(":v1", $password2_encoded);
		$stmt->bindParam(":v2", $_POST["user_id"]);
		$stmt->execute();
		
		$sql6="SELECT *, from_base64(password) FROM `account` WHERE level=2";
		$stmt=$db->prepare($sql6);
		$stmt->execute();
		
		$json=array();
		while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
			$json[]=$row;
		}
		echo json_encode($json, JSON_UNESCAPED_UNICODE);       
        exit();
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title>人員管理</title>
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
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css" integrity="sha384-1l6bfZSLxVjdRQZNwreB7Y6mrdnBlzZ6igw5Or4RXubqILQXIdJuDqo2k+kuuEfb" crossorigin="anonymous">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js" integrity="sha384-7kJ027NPn6MAvu6ZYDt7oBSCl4k5VeGk5MA3k06Jp40BuLVb6R79MdsPe+y+BoJX" crossorigin="anonymous"></script>
    
    <!-- loadash -->
	<script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js" integrity="sha384-mSSBMtpZHKT74w/c7tDbXmJJtqdlb1lR+PmokfXQwMXWVEWpxD08EA9Ymb+9PpIK" crossorigin="anonymous"></script>
    
	<style>
        html{
            min-height: 100%;
            font-family: Microsoft JhengHei; position: relative;
        }
        
        body{
            padding-top: 100px; padding-bottom: 125px;
        }
        
        .wrap{
            width: 100%; margin: 20px auto 0px auto;
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
			width: 60%; margin: 10px auto; letter-spacing: 0.05em;
            font-size: 0.8em; align-content: center;
		}
        
        td{
            line-height: 1.25em;
            vertical-align: middle;
        }
    </style>
    
    <script>
		$(document).ready(function(){            
            $.ajax({ 
                type: "POST",
                dataType: "json",
                url: "",
                data: {fetchUser: 1},
                success: function(data){
                    console.log(data);
                    
                    for($i=0; $i<data.length; $i++){
						var row_n=document.getElementById("user_list").rows.length-1;
                        var append_row=document.getElementById("user_list").insertRow(row_n);
						
                        append_row.insertCell(0).innerHTML=data[$i]["name"];
                        append_row.insertCell(1).innerHTML=data[$i]["email"];
                        append_row.insertCell(2).innerHTML=data[$i]["from_base64(password)"];
						append_row.insertCell(3).innerHTML="<i class='fas fa-pencil-alt' style='font-size: 1.25em; color: green' onClick='alter("+data[$i]['id']+")'></i>";
                    }
                }, error: function(e){
                    console.log(e);
                }     
            })
            
            $("#create_user").on("click", function(event){
			    event.preventDefault();
			    $("#user_append").modal("show");
			})
            
            $("#userAppendForm").on('submit', function(event){
                event.preventDefault();
                $("#submit1").attr("disabled", true);    
            
                $.ajax({
                    type: "POST",
                    dataType: "json", 
                    url: "",
                    data: $('#userAppendForm').serialize(),
                    success: function(data){
                        console.log(data);
                        window.location.href="./userlist.php";
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
                    data: {searchUser: 1, search: search},
                    success: function(data){
                        console.log(data);
                        
                        var row=document.getElementById("user_list").rows.length;
                        for($i=0; $i<row-2; $i++){
                            document.getElementById("user_list").deleteRow(1);
                        }
                        
                        for($j=0; $j<data.length; $j++){
                            var row_n=document.getElementById("user_list").rows.length-1;
                            var append_row=document.getElementById("user_list").insertRow(row_n);

                            append_row.insertCell(0).innerHTML=data[$j]["name"];
							append_row.insertCell(1).innerHTML=data[$j]["email"];
							append_row.insertCell(2).innerHTML=data[$j]["from_base64(password)"];
							append_row.insertCell(3).innerHTML="<i class='fas fa-pencil-alt' style='font-size: 1.25em; color: green' onClick='alter("+data[$j]['id']+")'></i>"; 
                        }
                    }, error: function(e){
                        console.log(e);
                    }     
                })           
            })
            
			$("#userAlterForm").on('submit', function(event){
                event.preventDefault();
                $("#submit2").attr("disabled", true);    
            
                $.ajax({
                    type: "POST",
                    dataType: "json", 
                    url: "",
                    data: $('#userAlterForm').serialize(),
                    success: function(data){
                        console.log(data);
                        window.location.href="./userlist.php";
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
        <div class="title"><b>人員管理</b></div>
    </div>
    
    <div id="user_append" class="modal fade">
        <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size: 1.25em; background-color: yellow">⭐ 新增帳號 ⭐</h5>
                <button class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body" style="padding-bottom: 1em">
                <form id="userAppendForm"><br>
                    <div class="row">
                        <div class="col-sm-4 pl-0 pr-0">
                            <div class="input-group-text">A. 姓名：</div>
                        </div>
                        <div class="col-sm-8 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="text" name="user_name" required>
                        </div>
                    </div>     
                    <div class="row">
                        <div class="col-sm-4 pl-0 pr-0">
                            <div class="input-group-text">B. 帳號：</div>
                        </div>
                        <div class="col-sm-8 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="email" name="user_email" placeholder="個人 email 信箱" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4 pl-0 pr-0">
                            <div class="input-group-text">C. 密碼：</div>
                        </div>
                        <div class="col-sm-8 pl-0 pr-0">
                            <input class="form-control btn-outline-secondary" type="text" name="user_password1" placeholder="限用英文及數字" required>
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
    
	<div id="alter_user" class="modal fade" style="top: 27.5%">
        <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body" style="padding-bottom: 1em">
				<button class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <form id="userAlterForm"><br>
					<input class="form-control btn-outline-secondary" type="text" name="user_id" style="display: none">
                    <center>請輸入新密碼：</center><br>
					<div class="row">
						<input class="form-control btn-outline-secondary" type="text" name="user_password2" placeholder="限用英文及數字" required>
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
	 
    <div class="container">
		<button class="btn btn-secondary" id="create_user" style="margin: 2% 1%"><i class="fas fa-plus"></i> 人員</button>
        <div style="float: right; padding: 2.5% 0%">搜尋：<input id="search"></div>
        
        <table id="user_list" class="table table-striped" style="text-align: center" cellspacing="0">
            <thead>
                <td>姓名</td>
                <td style="width: 30%">帳號</td>
                <td>密碼</td>
				<td>編輯</td>
            </thead>
            <tr class="list">
            </tr>
        </table><br>
    </div>
    
    <?php include("footer.php");?>
	
	<script>
        function alter(project_id){
			$("#alter_user").modal("show");
            $("input[name='user_id']").val(project_id);
        }
	</script>
</body>
</html>
