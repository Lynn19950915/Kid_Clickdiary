<?php
    session_start();
	include "db.php";

	if(isset($_POST["username"])){
		$sql1="SELECT * FROM `account` WHERE id= :v1";
		$stmt=$db->prepare($sql1);
		$stmt->bindParam(":v1", $_POST["username"]);
		$stmt->execute();
		$rs1=$stmt->fetch(PDO::FETCH_ASSOC);
		
		if($stmt->rowCount()==0){
			echo "Invalid Username";
		}else if($_POST['password']!=$rs1['password']){
			echo "Wrong Password";
		}else{
            $_SESSION['acc_info']=$rs1;
        }
        exit();
	}
?>

<!DOCTYPE html>
<html>
<head>
	<title>幼兒點日記管理</title>
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
    
	<style>
		/* BASIC */
		html, body{
            height: 100%; letter-spacing: 0.05em;
            background-color: #5CB85C;
            font-family: Microsoft JhengHei;
		}
        
        /* STRUCTURE */
        .container{
            height: 100%;
			align-content: center;
		}
        
		.wrapper{
            min-height: 100%; width: 100%;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
		}
        
        #formContent{
            width: 40%; padding: 2em 2em 1.5em 2em;
            background-color: #FFFFFF;
            position: relative; text-align: center;
            -webkit-box-shadow: 0 30px 60px 0 rgba(0,0,0,0.3); box-shadow: 0 30px 60px 0 rgba(0,0,0,0.3); -webkit-border-radius: 15px; border-radius: 15px; 
		}
        
        #formFooter{
            text-align: center;
        }
        
        /* DETAILED */
        .form-group{
            justify-content: center;
		}
        
        hr{
            height: 0.1em; border: 0;
            background-color: #5CB85C;
        }
        
        .input-group-prepend, input{
            margin: 0.25em 0;
        }
        
        input{
            width: 60%; height: 2.5em; border: 0;
            background-color: #F0F5E5;
            text-align: center; display: inline-block;
            -webkit-border-radius: 5px; border-radius: 5px;
		}

		input:focus{
            border: 2.5px solid #5CB85C;
		}
        
        #signIn{
            margin: -0.5em;
        }

		.icon{
			width: 7.5em; height: 5em;
		}

        *:focus {
		    outline: none;
		}
    
		/* ANIMATIONS */
        @-webkit-keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
		@-moz-keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
		@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
        
        .fadeIn{
            opacity:0;
            -webkit-animation:fadeIn ease-in 1; -moz-animation:fadeIn ease-in 1; animation:fadeIn ease-in 1;
            -webkit-animation-duration: 1s; -moz-animation-duration: 1s; animation-duration: 1s;
            -webkit-animation-fill-mode: forwards; -moz-animation-fill-mode: forwards; animation-fill-mode: forwards;
		}

		.fadeIn.first{
            -webkit-animation-delay: 0.1s; -moz-animation-delay: 0.1s; animation-delay: 0.1s;
		}
		.fadeIn.second{
            -webkit-animation-delay: 0.3s; -moz-animation-delay: 0.3s; animation-delay: 0.3s;
		}
		.fadeIn.third{
            -webkit-animation-delay: 0.5s; -moz-animation-delay: 0.5s; animation-delay: 0.5s;
		}
		.fadeIn.fourth{
            -webkit-animation-delay: 0.7s; -moz-animation-delay: 0.7s; animation-delay: 0.7s;
		}
	</style>
    
	<script>
		$(document).ready(function(){            
            $("#loginForm").on('submit', function(event){
			    event.preventDefault();
                $("#signIn").attr("disabled", true);
                
                $.ajax({ 
                    type: "POST",
                    url: "",
                    data: $('#loginForm').serialize(),
                    success: function(data){ 
                        console.log(data);
                        if(data=="Invalid Username"){
                            $("#signIn").attr("disabled", false);
                            $.alert({
                                title: "",
								content: "帳號錯誤",
                            })
                        }else if(data=="Wrong Password"){
                            $("#signIn").attr("disabled", false);
                            $.alert({
                                title: "",
                                content: "密碼錯誤",
                            })
                        }else{
                            window.location.href="./project.php";
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
	<div class="container">
    <div class="wrapper">
        <div id="formContent">

        <!-- 1.Icon -->
        <div class="fadeIn first">
            <h4><span class="logo_title" style="color: #5CB85C"><b>幼兒點日記管理</b></span></h4><hr>
        </div>

        <!-- 2.Login Form -->
        <form id="loginForm">
            <div class="input-group form-group fadeIn second">
                <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                </div>
                <input name="username" type="text" placeholder="請輸入帳號" required>
            </div>
        
            <div class="input-group form-group fadeIn second">
                <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-key"></i></span>
                </div>
                <input id="password" name="password" type="password" placeholder="請輸入密碼" required>
            </div>
            
            <button id="signIn" class="btn fadeIn third">
                <img src="./pic/sign_in.png" class="icon">
            </button>
        </form>
            
        </div>
    </div>
    </div>
</body>
</html>
