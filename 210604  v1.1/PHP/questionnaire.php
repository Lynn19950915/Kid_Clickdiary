<?php
	session_start();
	include("db.php");
    include("upload_function.php");

    if(!$_SESSION["acc_info"]["id"]){
		header("Location: ./index.php");
    }
    $project_id=isset($_GET["project_id"])?$_GET["project_id"]: 0;

	if(isset($_POST["fetchProject"])){
		$sql1="SELECT project_name, csv_schema FROM `project` WHERE project_id= :v1";
		$stmt=$db->prepare($sql1);
		$stmt->bindParam(":v1", $project_id);
		$stmt->execute();
        
		$json=array();
		while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
            $json[0]=$row["csv_schema"];
			$json[1]=$row["project_name"];
		}
		echo json_encode($json, JSON_UNESCAPED_UNICODE);
		exit();
	}

	if(isset($_POST["answer"])){
        $answer=json_encode($_POST["answer"], JSON_UNESCAPED_UNICODE);
        
		$sql2="INSERT INTO `:v1'official'` VALUES(NULL, NOW(), :v2)";
		$stmt=$db->prepare($sql2);
		$stmt->bindParam(":v1", $project_id);
		$stmt->bindParam(":v2", $answer);
		$stmt->execute();
        
        $sql3="UPDATE `project` SET n=n+1 WHERE project_id= :v1";
		$stmt=$db->prepare($sql3);
		$stmt->bindParam(":v1", $project_id);
		$stmt->execute();
        
		exit();
	}
?>

<!DOCTYPE html>
<html>
<head>
    <title>專案問卷</title>
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
		
		.infobar{
            letter-spacing: 0.1em; padding-bottom: 2em;
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
				url: "",
				data: {fetchProject: 1},
				success: function(data){
					$(".project_name").empty().append(data[1]);
					var qsetting=JSON.parse(data[0]);
					console.log(qsetting);
					
					for(var i=0; i<qsetting.length; i++){
						if(qsetting[i]["type"]=="0"){	//0: 單選題
                            var q_txt=$("<div>").html(qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"]+". "+qsetting[i]["q_txt"]).attr({"class": "input-group-text", "style": "font-size: 0.9em"});
                            var q_lbl=$("<div>").attr({"class": "col-sm-auto"});
                            q_txt.appendTo(q_lbl);
                            $(".card-body").append(q_lbl);
                            
                            if(qsetting[i]["annotate"]!=""){
                                var a_txt=$("<div>").html("<b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"]+"</b>").attr({"class": "input-group-text", "style": "font-size: 0.8em; background-color: #FFFFBB; border: none"});
                                var a_lbl=$("<div>").attr({"class": "col-sm-auto"});
                                a_txt.appendTo(a_lbl);
                                $(".card-body").append(a_lbl);
                            }
                            
                            var o_all=$("<div>").attr({"class": "btn-group btn-group-toggle col-sm-12", "data-toggle": "buttons"});
                            for(var j=0; j<qsetting[i]["opt_txt"].length; j++){
                                var o_txt=$("<input>").attr({"type": "radio", "name": qsetting[i]["q_id"]+"_"+qsetting[i]["q_sn"], "value": qsetting[i]["opt_value"][j], "note": qsetting[i]["note"][j], "required": true});
                                var o_lbl=$("<label>").html(qsetting[i]["opt_txt"][j]).attr({"class": "btn btn-outline-secondary", "style": "font-size: 0.9em"});
                                o_txt.appendTo(o_lbl);
                                o_lbl.appendTo(o_all);
                            }                            
							$(".card-body").append(o_all);
                            
                            for(var k=0; k<qsetting[i]["note"].length; k++){
                                if(qsetting[i]["note"][k]=="1"){
                                    var n_txt=$("<input>").attr({"type": "text", "name": qsetting[i]["q_id"]+"_"+qsetting[i]["q_sn"]+"_"+qsetting[i]["opt_value"][k], "class": "form-control", "placeholder": "請補充說明", "style": "font-size: 0.9em; display: none"});
                                    var n_lbl=$("<div>").attr({"class": "col-sm-12"});
                                    n_txt.appendTo(n_lbl);
                                    $(".card-body").append(n_lbl);
                                }
                            }
							$(".card-body").append("<p><br></p>");
							
						}else if(qsetting[i]['type']==1){	//1: 複選題
							var q_txt=$("<div>").html(qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"]+". "+qsetting[i]["q_txt"]).attr({"class": "input-group-text", "style": "font-size: 0.9em"});
                            var q_lbl=$("<div>").attr({"class": "col-sm-auto"});
                            q_txt.appendTo(q_lbl);
                            $(".card-body").append(q_lbl);
							
							if(qsetting[i]['annotate']!=""){
								var annotate=$('<div>').html("<b><i class='fa fa-info-circle'></i> "+qsetting[i]['annotate']+"</b>").attr({'class': 'input-group-text', 'style': 'font-size: 0.8em; background-color: #FFFFBB; border: none'});
								var albl=$('<div>').attr({'class': 'col-sm-auto'});
								annotate.appendTo(albl);
								$(".card-body").append(albl);
							}
                            
                            var o_all=$("<div>").attr({"class": "btn-group btn-group-toggle col-sm-12", "data-toggle": "buttons"});
                            for(var j=0; j<qsetting[i]["opt_txt"].length; j++){
                                var o_txt=$("<input>").attr({"type": "checkbox", "name": qsetting[i]["q_id"]+"_"+qsetting[i]["q_sn"], "value": qsetting[i]["opt_value"][j], "note": qsetting[i]["note"][j], "disjoint": qsetting[i]["disjoint"][j]});
                                var o_lbl=$("<label>").html(qsetting[i]["opt_txt"][j]).attr({"class": "btn btn-outline-secondary", "style": "font-size: 0.9em"});
                                o_txt.appendTo(o_lbl);
                                o_lbl.appendTo(o_all);
                            }                            
							$(".card-body").append(o_all);
							$(".card-body").append('<p><br></p>');
							
						}else if(qsetting[i]['type']==2){	//2: 數值題
							var q_txt=$("<div>").html(qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"]+". "+qsetting[i]["q_txt"]).attr({"class": "input-group-text", "style": "font-size: 0.9em"});
                            var q_lbl=$("<div>").attr({"class": "col-sm-auto"});
                            q_txt.appendTo(q_lbl);
                            $(".card-body").append(q_lbl);
							
							if(qsetting[i]["annotate"]!=""){
                                var a_txt=$("<div>").html("<b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"]+"</b>").attr({"class": "input-group-text", "style": "font-size: 0.8em; background-color: #FFFFBB; border: none"});
                                var a_lbl=$("<div>").attr({"class": "col-sm-auto"});
                                a_txt.appendTo(a_lbl);
                                $(".card-body").append(a_lbl);
                            }
							
                            var o_txt=$("<input>").attr({"type": "number", "name": qsetting[i]["q_id"]+"_"+qsetting[i]["q_sn"], "class": "form-control validateNumber", "min": qsetting[i]["range_min"], "max": qsetting[i]["range_max"], "placeholder": "請輸入數字", "style": "font-size: 0.9em", "required": true});
                            var o_lbl=$("<div>").attr({"class": "col-sm-12"});
                            o_txt.appendTo(o_lbl);
							$(".card-body").append(o_lbl);
							$(".card-body").append('<p><br></p>');
							
						}else if(qsetting[i]['type']==3){	//3: 簡答題
							var q_txt=$("<div>").html(qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"]+". "+qsetting[i]["q_txt"]).attr({"class": "input-group-text", "style": "font-size: 0.9em"});
                            var q_lbl=$("<div>").attr({"class": "col-sm-auto"});
                            q_txt.appendTo(q_lbl);
                            $(".card-body").append(q_lbl);
							
							if(qsetting[i]["annotate"]!=""){
                                var a_txt=$("<div>").html("<b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"]+"</b>").attr({"class": "input-group-text", "style": "font-size: 0.8em; background-color: #FFFFBB; border: none"});
                                var a_lbl=$("<div>").attr({"class": "col-sm-auto"});
                                a_txt.appendTo(a_lbl);
                                $(".card-body").append(a_lbl);
                            }
							
                            var o_txt=$("<input>").attr({"type": "text", "name": qsetting[i]["q_id"]+"_"+qsetting[i]["q_sn"], "class": "form-control", "placeholder": "請輸入答案", "style": "font-size: 0.9em", "required": true});
                            var o_lbl=$("<div>").attr({"class": "col-sm-12"});
							o_txt.appendTo(o_lbl);
							$(".card-body").append(o_lbl);
							$(".card-body").append('<p><br></p>');						
						}
					}
				}
			})
                        
            $(document).on("change", "input[type='radio']", function(event){
                event.preventDefault();
                var q_id=$(this).attr("name").split("_")[0];
                var q_sn=$(this).attr("name").split("_")[1];
                var value=$(this).attr("value");
                var note=$(this).attr("note");

                if(note==1){
                    $("input[name="+q_id+"_"+q_sn+"_"+value+"]").show();
                }else{
                    console.log(note);
                    $("input[name="+q_id+"_"+q_sn+"_"+value+"]").hide().val("");
                }
            })
            
            $(document).on('change', "input[type='checkbox']", function(event){
                event.preventDefault();
                var q_id=$(this).attr("name").split("_")[0];
                var q_sn=$(this).attr("name").split("_")[1];
                var value=$(this).attr("value");
                var disjoint=$(this).attr("disjoint");
                var checked=$(this).is(':checked') ?1 :0;
                
                if(disjoint==1&checked==1){
                    $("input[name="+q_id+"_"+q_sn+"]").prop("checked", false).parent().removeClass("active");
                    $("input[name="+q_id+"_"+q_sn+"][value="+value+"]").prop("checked", true); 
                }else{
                    $("input[name="+q_id+"_"+q_sn+"][disjoint=1]").prop("checked", false).parent().removeClass("active");
                }
            })
            
            $(document).on("change", ".validateNumber", function(event){
                event.preventDefault();
                var min=parseInt($(this).attr("min"));
                var max=parseInt($(this).attr("max"));
                var ansnum=Number($(this).val());

                if(ansnum>max|ansnum<min){
                    $(this).val("");
                    $.alert("數值超出範圍，合理值應介於 "+min+"~"+max+" 之間，請修正！");
                    return false;
                }
            })
					
            $("#previewForm").on("submit", function(){
                event.preventDefault();
                $("#submit").attr("disabled", true);
                var answer=$('#previewForm').serializeArray();

                $.ajax({ 
                    type: "POST",
                    url: "",
                    data: {answer: answer},
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
        <div class="title">專案問卷</div>
    </div>
	
	<div class="container">
		<div class="infobar">
			<b><span style="background-color: orange; padding: 0.5em; -webkit-border-radius: 10px 0px 0px 10px; border-radius: 10px 0px 0px 10px">專案名稱</span><span style="background-color: yellow; padding: 0.5em; -webkit-border-radius: 0px 10px 10px 0px; border-radius: 0px 10px 10px 0px"><span class="project_name"></span></span></b>
		</div>
		
        <div class="card">
			<form id="previewForm">
            <div class="card-body">
			<p style="font-size: 0.95em">
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
