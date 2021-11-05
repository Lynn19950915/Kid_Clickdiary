<?php
    session_start();
	include "db.php";

	if(isset($_POST["username"])){
        $password=isset($_POST["password1"])?$_POST["password1"]: $_POST["password2"];
        
		$sql1="SELECT * FROM `account` WHERE email= :v1";
		$stmt=$db->prepare($sql1);
		$stmt->bindParam(":v1", $_POST["username"]);
		$stmt->execute();
		$rs1=$stmt->fetch(PDO::FETCH_ASSOC);
		
		if($stmt->rowCount()==0){
			echo "Invalid Username";
		}else if($password!=$rs1['password']){
			echo "Wrong Password";
		}else{
            $_SESSION["acc_info"]=$rs1;
            $sql2="SELECT * FROM `account`";
            $stmt=$db->prepare($sql2);
            $stmt->execute();
            $rs2=$stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $_SESSION["all_acc"]=$rs2;
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
            font-family: Microsoft JhengHei;
		}
        
        /* STRUCTURE */
        .container{
            height: 100%;
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
        
        /* DETAILED */
        .form-group{
            justify-content: center;
		}
        
        hr{
            height: 0.05em;
            background-color: #2E317C;
        }
        
        .input-group-prepend, input{
            margin: 0.25em 0;
        }
        
        input{
            width: 75%; height: 2.5em; border: 0;
            background-color: #F0F5E5;
            text-align: center; display: inline-block;
            -webkit-border-radius: 5px; border-radius: 5px;
		}

		input:focus{
            border: 2.5px solid #2E317C;
		}
        
        #signIn{
            margin: -0.25em;
        }

		.icon{
			width: 7.5em; height: 5em;
		}
    
		/* ANIMATIONS */
        @-webkit-keyframes fadeIn{from{opacity:0;}to{opacity:1;}}
		@-moz-keyframes fadeIn{from{opacity:0;}to{opacity:1;}}
		@keyframes fadeIn{from{opacity:0;}to{opacity:1;}}
        
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
            $("#eye1").on("click", function(event){
			    event.preventDefault();
                var password1=$("#password1").val();
			    $("#password1").replaceWith(`<input id="password2" name="password2" type="text" value="${password1}" style="width: 65%">`);
                $("#eye1").hide();
                $("#eye2").show();
			})
            
            $("#eye2").on("click", function(event){
			    event.preventDefault();
                var password2=$("#password2").val();
			    $("#password2").replaceWith(`<input id="password1" name="password1" type="password" value="${password2}" style="width: 65%">`);
                $("#eye1").show();
                $("#eye2").hide();
			})
            
            $("#loginForm").on("submit", function(event){
			    event.preventDefault();
                $("#signIn").attr("disabled", true);
                
                $.ajax({ 
                    type: "POST",
                    url: "",
                    data: $("#loginForm").serialize(),
                    success: function(data){ 
                        console.log(data);
                        if(data=="Invalid Username"){
                            $("#signIn").attr("disabled", false);
                            $.alert({
                                title: "",
								content: "信箱帳號錯誤，請修正！",
                            })
                        }else if(data=="Wrong Password"){
                            $("#signIn").attr("disabled", false);
                            $.alert({
                                title: "",
                                content: "密碼輸入錯誤，請修正！",
                            })
                        }else{
                            window.location.href="./main.php";
                        }
                    }, error: function(e){
                        console.log(e);
                    }
                })
            })   
    	})
	</script>
</head>


<body background="./pic/index.jpg" style="background-size: cover">
	<div class="container">
    <div class="wrapper">
        <div id="formContent">
            
        <div class="fadeIn first">
            <h4><span style="color: #2E317C"><b>幼兒點日記管理</b></span></h4><hr>
        </div>

        <form id="loginForm">
            <div class="input-group form-group fadeIn second">
                <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                </div>
                <input name="username" type="email" placeholder="請輸入帳號 (E-mail)" required>
            </div>        
            <div class="input-group form-group fadeIn second">
                <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-key"></i></span>
                </div>
                <input id="password1" name="password1" type="password" placeholder="請輸入密碼" required style="width: 65%">
                <button id="eye1" style="margin: 1%; border: 0"><i class="fas fa-eye"></i></button>
                <button id="eye2" style="margin: 1%; border: 0; display: none"><i class="fas fa-eye-slash"></i></button>
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
