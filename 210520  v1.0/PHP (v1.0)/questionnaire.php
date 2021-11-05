<?php
	session_start();
	include("db.php");
    include("upload_function.php");

    if(!$_SESSION['acc_info']['id']){
		header("Location: ./index.php");
    }
    $project_id=isset($_GET["project_id"])?$_GET["project_id"]: 0;

	if(isset($_POST['fetchProject'])){
		$sql1="SELECT project_name, csv_schema FROM `project` WHERE project_id= :v1";
		$stmt=$db->prepare($sql1);
		$stmt->bindParam(":v1", $project_id);
		$stmt->execute();
        
		$json=array();
		while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
            $json[0]=$row['csv_schema'];
			$json[1]=$row['project_name'];
		}
		echo json_encode($json, JSON_UNESCAPED_UNICODE);
		exit();
	}

	if(isset($_POST['1_1'])){
		$sql2="INSERT INTO `:v1` VALUES(NULL, NOW(), :v2)";
		$stmt=$db->prepare($sql2);
		$stmt->bindParam(":v1", $project_id);
		$stmt->bindParam(":v2", $_POST['1_1']);
		$stmt->execute();
		
		$sql3="UPDATE `project` SET active=3 WHERE project_id= :v1";
		$stmt=$db->prepare($sql3);
		$stmt->bindParam(':v1', $project_id);
		$stmt->execute();
		
		exit();
	}
?>

<!DOCTYPE html>
<html>
<head>
    <title>專案預覽</title>
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
	
    <!-- Dexie -->
    <script src="https://capi.geohealth.tw/js/dexie.js"></script>
	
	<style>
        html{
            min-height: 100%;
            font-family: Microsoft JhengHei; position: relative;
        }
        
        body{
            padding-top: 100px; padding-bottom: 75px;
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
		
		.infobar{
            letter-spacing: 0.1em;
            text-align: right;
        }
		
		.container{
			width: 60%; margin: 20px auto;
			align-content: center;
		}
        
        .card{
            background-color: #FFFFBB;
        }
        
        .card-body{
		    line-height: 1.75em; letter-spacing: 0.05em; padding-top: 5%;
            text-align: left;
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
				url: '',
				data: {fetchProject: 1},
				success: function(data){
					$(".project_name").empty().append(data[1]);
					var qsetting=JSON.parse(data[0]);
					console.log(qsetting);
					
					for(var i=0; i<qsetting.length; i++){
						if(qsetting[i]['type']=="0"){	//0: 單選題
							var qtxt=$('<div>').html(qsetting[i]['q_id']+". "+qsetting[i]['q_txt']).attr({'class': 'input-group-text', 'style': 'font-size: 0.9em'});
							var qlbl=$('<div>').attr({'class': 'col-sm-auto'});
							qtxt.appendTo(qlbl);
							$(".card-body").append(qlbl);
							
							if(qsetting[i]['annotate']!=""){
								var annotate=$('<div>').html("<b><i class='fa fa-info-circle'></i> "+qsetting[i]['annotate']+"</b>").attr({'class': 'input-group-text', 'style': 'font-size: 0.8em; background-color: #FFFFBB; border: none'});
								var albl=$('<div>').attr({'class': 'col-sm-auto'});
								annotate.appendTo(albl);
								$(".card-body").append(albl);
							}
																					
							var olbl=$('<div>').attr({'class': 'btn-group btn-group-toggle col-sm-12', 'data-toggle': 'buttons'});
							for(var j=0; j<qsetting[i]['opt_txt'].length; j++){
								var opt=$('<input>').attr({'type': 'radio', 'name': qsetting[i]['q_id']+'_'+qsetting[i]['q_sn'],
														   'value': qsetting[i]['opt_value'][j], 'required': true});
								var opts=$('<label>').html(qsetting[i]['opt_txt'][j]).attr({'class': 'btn btn-outline-secondary', 'style': 'font-size: 0.9em'});
								opt.appendTo(opts);
								opts.appendTo(olbl);
							}
							$(".card-body").append(olbl);
							$(".card-body").append('<p><br></p>');
							
						}else if(qsetting[i]['type']==1){	//1: 複選題
							var qtxt=$('<div>').html(qsetting[i]['q_id']+". "+qsetting[i]['q_txt']).attr({'class': 'input-group-text', 'style': 'font-size: 0.9em'});
							var qlbl=$('<div>').attr({'class': 'col-sm-auto'});
							qtxt.appendTo(qlbl);
							$(".card-body").append(qlbl);
							
							if(qsetting[i]['annotate']!=""){
								var annotate=$('<div>').html("<b><i class='fa fa-info-circle'></i> "+qsetting[i]['annotate']+"</b>").attr({'class': 'input-group-text', 'style': 'font-size: 0.8em; background-color: #FFFFBB; border: none'});
								var albl=$('<div>').attr({'class': 'col-sm-auto'});
								annotate.appendTo(albl);
								$(".card-body").append(albl);
							}
							
							var olbl=$('<div>').attr({'class': 'btn-group btn-group-toggle col-sm-12', 'data-toggle': 'buttons'});
							for(var j=0; j<qsetting[i]['opt_txt'].length; j++){
								var opt=$('<input>').attr({'type': 'checkbox', 'name': qsetting[i]['q_id']+'_'+qsetting[i]['q_sn'],
														   'value': qsetting[i]['opt_value'][j], 'disjoint':qsetting[i]['disjoint'][j]});
								var opts=$('<label>').html(qsetting[i]['opt_txt'][j]).attr({'class': 'btn btn-outline-secondary', 'style': 'font-size: 0.9em'});
								opt.appendTo(opts);
								opts.appendTo(olbl);
							}
							$(".card-body").append(olbl);
							$(".card-body").append('<p><br></p>');
							
						}else if(qsetting[i]['type']==2){	//2: 數值題
							var qtxt=$('<div>').html(qsetting[i]['q_id']+". "+qsetting[i]['q_txt']).attr({'class': 'input-group-text', 'style': 'font-size: 0.9em'});
							var qlbl=$('<div>').attr({'class': 'col-sm-auto'});
							qtxt.appendTo(qlbl);
							$(".card-body").append(qlbl);
							
							if(qsetting[i]['annotate']!=""){
								var annotate=$('<div>').html("<b><i class='fa fa-info-circle'></i> "+qsetting[i]['annotate']+"</b>").attr({'class': 'input-group-text', 'style': 'font-size: 0.8em; background-color: #FFFFBB; border: none'});
								var albl=$('<div>').attr({'class': 'col-sm-auto'});
								annotate.appendTo(albl);
								$(".card-body").append(albl);
							}
							
							var olbl=$('<div>').attr({'class': 'col-sm-12'});
							var opt=$('<input>').attr({'type': 'number', 'name': qsetting[i]['q_id']+'_'+qsetting[i]['q_sn'],
													   'class': 'form-control ValidateNumber', 'min': qsetting[i]['range_min'],
													   'max': qsetting[i]['range_max'], 'placeholder': '請輸入數字',
													   'style': 'font-size: 0.9em', 'required': true});
							opt.appendTo(olbl);
							$(".card-body").append(olbl);
							$(".card-body").append('<p><br></p>');
							
						}else if(qsetting[i]['type']==3){	//3: 簡答題
							var qtxt=$('<div>').html(qsetting[i]['q_id']+". "+qsetting[i]['q_txt']).attr({'class': 'input-group-text', 'style': 'font-size: 0.9em'});
							var qlbl=$('<div>').attr({'class': 'col-sm-auto'});
							qtxt.appendTo(qlbl);
							$(".card-body").append(qlbl);
							
							if(qsetting[i]['annotate']!=""){
								var annotate=$('<div>').html("<b><i class='fa fa-info-circle'></i> "+qsetting[i]['annotate']+"</b>").attr({'class': 'input-group-text', 'style': 'font-size: 0.8em; background-color: #FFFFBB; border: none'});
								var albl=$('<div>').attr({'class': 'col-sm-auto'});
								annotate.appendTo(albl);
								$(".card-body").append(albl);
							}
							
							var olbl=$('<div>').attr({'class': 'col-sm-12'});
							var opt=$('<input>').attr({'type': 'text', 'name': qsetting[i]['q_id']+'_'+qsetting[i]['q_sn'],
													   'class': 'form-control', 'placeholder': '請輸入答案',
													   'style': 'font-size: 0.9em', 'required': true});
							opt.appendTo(olbl);
							$(".card-body").append(olbl);
							$(".card-body").append('<p><br></p>');
						
						}
					}
					
					$(document).on('change', "input[type='checkbox']", function(event){
						event.preventDefault();
						var qid=parseInt($(this).attr('name').split('_')[0]);
						var qsn=parseInt($(this).attr('name').split('_')[1]);
						var disjoint=parseInt($(this).attr('disjoint'));
						var checked=$(this).is(':checked') ?1 :0;
						
						if(disjoint==1&checked==1){
							var val=parseInt($(this).attr('value'));
							console.log(val);
							$("input[name="+qid+"_"+qsn+"]").prop("checked", false).parent().removeClass('active');
							$("input[name="+qid+"_"+qsn+"][value="+val+"]").prop("checked", true);
						}
					})
					
					$(document).on('change', ".ValidateNumber", function(event){
						event.preventDefault();
						var min=parseInt($(this).attr('min'));
						var max=parseInt($(this).attr('max'));
						var answer=Number($(this).val());
						
						if(answer>max|answer<min){
							$.alert("輸入數值超出範圍，請修正！");
							return false;
						}
					})
					
					$("#qForm").on('submit', function(){
						event.preventDefault();
						$("#submit").attr("disabled", true);
                	
						$.ajax({ 
							type: "POST",
							url: "",
							data: $('#qForm').serialize(),
							success: function(data){ 
								console.log(data);
								$.confirm({
									title: "",
									content: "填答完成，您可以下載測試資料囉！",
									buttons:{
										"OK": function(){
											window.location.href="./project.php";
										}
									}
								})
							}, error: function(e){
								console.log(e);
							}
						})
					})
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
        <div class="title">專案預覽</div>
    </div>
	
	<div class="container">
		<div class="infobar">
			<b><span style="background-color: orange; padding: 0.5em; -webkit-border-radius: 10px 0px 0px 10px; border-radius: 10px 0px 0px 10px">專案名稱</span><span style="background-color: yellow; padding: 0.5em; -webkit-border-radius: 0px 10px 10px 0px; border-radius: 0px 10px 10px 0px"><span class="project_name"></span></span></b>
		</div><br>
		
        <div class="card">
			<form id="qForm">
            <div class="card-body">
			<p>
				▼ 以下是專案問卷之<b>預覽畫面</b>，您可直接點選作答：
			</p>
			</div>
			
			<div style="text-align: center; margin: -2.5% 0 2.5% 0">
				<button id="submit" class="btn">
					<img src="/pic/submit.png" class="icon">
				</button>
			</div>
			</form>
		</div>
	</div>
</body>
</html>
