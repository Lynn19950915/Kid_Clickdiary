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
            width: 30%; padding: 2em 2em 0.5em 2em;
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
                width: 65%;
                font-size: 0.8em;
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
            <h4><span style="color: #2E317C"><b>恭喜您完成填寫</b></span></h4>
        </div>
        <div class="fadeIn second" style="padding-top: 1em">
            您現在可關閉此視窗
        </div>
        <div class="fadeIn third">
            <img src="./pic/done.gif" class="icon">
        </div>
            
        </div>
    </div>
    </div>
</body>
</html>
