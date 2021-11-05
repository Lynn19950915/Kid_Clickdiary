<?php
    session_start();
	include "db.php";
?>

<!DOCTYPE html>
<html>
<head>
	<title>填答完成</title>
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
            width: 60%; padding: 2em 2em 0.5em 2em;
            background-color: #FFFFFF;
            position: relative; text-align: center;
            -webkit-box-shadow: 0 30px 60px 0 rgba(0,0,0,0.3); box-shadow: 0 30px 60px 0 rgba(0,0,0,0.3); -webkit-border-radius: 15px; border-radius: 15px; 
		}

        /* DETAILED */
		img{
			width: 8.75em;
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
            -webkit-animation-delay: 0.4s; -moz-animation-delay: 0.4s; animation-delay: 0.4s;
		}
		.fadeIn.third{
            -webkit-animation-delay: 0.7s; -moz-animation-delay: 0.7s; animation-delay: 0.7s;
		}
        
        /* RESPONSIVE */
		@media screen and (max-width: 800px){
            #formContent{
                width: 85%;
                font-size: 0.75em;
            }
            
            .icon{
                width: 7.5em;
            }
            
            h4{
                font-size: 1.375em;
            }
		}
	</style>
</head>
    

<body background="./pic/thankyou.jpg" style="background-size: cover">
	<div class="container">
    <div class="wrapper">
        <div id="formContent">

        <div class="fadeIn first">
            <h4><span style="color: #2E317C"><b>問卷填寫完成，非常感謝！</b></span></h4>
        </div>
        <div class="fadeIn second" style="padding: 1em 5%; text-align: left">
		★ 查詢點數：<br>
		<a href="https://***" target="_blank">https://***</a> → 動態調查群組_點數／禮券查詢
		<br><br>
		★ 累計滿 100 點，自動發送電子禮劵至您信箱
		<br><br>
		★ 國小家長請注意：<br>
		本次調查包括「家長問卷」及「兒童問卷」共兩份問卷，若其中一份還沒填寫，請至 <a href="https://***" target="_blank">https://***</a> 填寫，謝謝！
        </div>
        <div class="fadeIn third" style="padding-bottom: 2%">
            <img src="./pic/done.png" class="icon">
        </div>
            
        </div>
    </div>
    </div>
</body>
</html>
