<?php
?>

<style>
    nav{
        background: url("./pic/index.jpg"); background-size: cover;
    }
    
    .logo{
        width: 2em; margin-left: 5%; padding-bottom: 1%;
    }

    @media screen and (max-width: 800px){
        .navbar-brand{
            letter-spacing: 0;
            font-size: 1em;
        }
        
        .mr-3{
            padding: 0.3em 0;
        }
    }
</style>

<script>
	$(document).ready(function(){
        $(document).click(function(event){
            $('.navbar-collapse').collapse('hide');    
        })
    })
</script>


<header>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
		<a class="navbar-brand" href="./main.php" style="margin: 0.25em; background-color: #FFFFFF; border-radius: 10px">
            <img class="logo" src="./pic/KIT_logo.jpeg"><b style="padding: 0 0.75em 0 0.25em; color: #2E317C">幼兒點日記</b>
		</a>
        
        <button class="navbar-toggler" data-toggle="collapse" data-target="#option" style="background-color: #FFFFFF">
			<span class="navbar-toggler-icon"></span>
        </button>
		<div id="option" class="collapse navbar-collapse">            
            <ul class="navbar-nav ml-auto">
				<li class="nav-item mr-3"><a href="./logout.php" style="color: white"><b>登出</b></a></li>
			</ul>
		</div>
	</nav>
</header>
