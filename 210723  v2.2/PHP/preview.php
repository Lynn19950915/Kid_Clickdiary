<?php
	session_start();
	include("db.php");
    include("upload.php");

    $project_id=isset($_GET["project_id"])?$_GET["project_id"]: 0;

	if(isset($_POST["fetchProject"])){
		$sql1="SELECT project_name, csv_schema FROM `project` WHERE project_id= :v1";
		$stmt=$db->prepare($sql1);
		$stmt->bindParam(":v1", $project_id);
		$stmt->execute();
           
		$json=array();
		while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
            $json[0]=$row["project_name"];
            $json[1]=$row["csv_schema"];
        }
        echo json_encode($json, JSON_UNESCAPED_UNICODE);
		exit();
	}

	if(isset($_POST["answer"])){
        $answer=json_encode($_POST["answer"], JSON_UNESCAPED_UNICODE);
        $beta=$project_id."beta";
        
		$sql2="INSERT INTO `:v1` VALUES(NULL, NOW(), :v2)";
		$stmt=$db->prepare($sql2);
		$stmt->bindParam(":v1", $beta);
		$stmt->bindParam(":v2", $answer);
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
	<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
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
            padding-top: 100px; padding-bottom: 125px;
        }

        .wrap{
            width: 100%; margin: 20px auto;
            display: inline-block; position: relative; text-align: center;
        }
        
        #title{
            width: 12.5%;
        }
                
        .title{
            width: 100%; top: 15%; letter-spacing: 0.05em;
            color: #2E317C;
            font-size: 1.75em; text-align: center; position: absolute;
        }
		
		.infobar{
            letter-spacing: 0.1em; padding-bottom: 2em;
            font-size: 0.9em; text-align: right;
        }
		
		.container{
			width: 60%; margin: 20px auto;
			align-content: center;
		}
        
        .card{
            background-color: #FFFFBB;
        }
        
        .card-body{
            line-height: 1.75em; letter-spacing: 0.05em; padding: 2.5em 5% 0 5%;
            text-align: left;
		}
		
		.icon{
			width: 8.5em;
		}
        
        /* RESPONSIVE */
		@media screen and (max-width: 800px){
            body{
                padding-bottom: 150px;
            }

            .wrap{
                margin: auto;
            }
            
            #title{
                width: 7.5em;
            }
            
            .title{
                font-size: 1.15em;
            }
            
            .container{
                width: 100%; margin: auto; margin-top: 6.25%;
            }
            
            .infobar{
                font-size: 0.6em;
            }
            
            .card-body{
                margin-top: 5%; padding: 2.5%;
                font-size: 0.6em;
            }
            
            .input-group-text{
                padding-left: 2.5%;
            }
            
            .row{
                width: 90%; margin-bottom: 1.5em;
            }

            .icon{
                width: 6em;
            }
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
                    if(data==""){
                        $.alert({
                            title: "",
                            content: "此連結有誤，請重新檢查！",
                        })
                    }else{                    
                        $(".project_name").empty().append(data[0]);
                        var qsetting=JSON.parse(data[1]);
						
						count=0;
                        for(var i=0; i<qsetting.length; i++){
                            //0: 單選題
                            if(qsetting[i]["type"]==0){
								count++;
                                var skips="";
                                for(var j=0; j<qsetting[i]["skip"].length; j++){
                                    if(qsetting[i]["skip"][j]!=0){
                                        skips=skips+qsetting[i]["skip"][j].replace("[","").replace("]","")+",";
                                    }
                                }
                                
                                if(window.matchMedia('(max-width: 800px)').matches){
                                    var q_txt=$("<div>").html("<b style='padding: 0.25em; margin-left: 0.25em; margin-right: 1em; background-color: #FFC90C'>單選</b>"+(count)+". "+qsetting[i]["q_txt"].substr(0, 20)+"<br>"+qsetting[i]["q_txt"].substr(20, 22)+"<br>"+qsetting[i]["q_txt"].substr(42, 22)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1em; text-align: left"});
                                }else{
                                    var q_txt=$("<div>").html("<b style='padding: 0.25em; margin-left: -0.25em; margin-right: 0.5em; background-color: #FFC90C'>單選</b>"+(count)+". "+qsetting[i]["q_txt"]).attr({"class": "input-group-text", "style": "font-size: 0.85em"});
                                }
                                var q_lbl=$("<div>").attr({"class": "col-sm-auto"});
                                q_txt.appendTo(q_lbl);
                                $(".card-body").append(q_lbl);

                                if(qsetting[i]["annotate"]!=""){
                                    if(window.matchMedia('(max-width: 800px)').matches){
                                        var a_txt=$("<div>").html("<b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"].substr(0, 32)+"<br>"+qsetting[i]["annotate"].substr(32, 34)+"<br>"+qsetting[i]["annotate"].substr(66, 34)+"</b>").attr({"class": "input-group-text", "style": "line-height: 1.9em; font-size: 0.95em; background-color: #FFFFBB; border: none; text-align: left"});
                                    }else{
                                        var a_txt=$("<div>").html("<b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"]+"</b>").attr({"class": "input-group-text", "style": "font-size: 0.75em; background-color: #FFFFBB; border: none"});
                                    }
                                    var a_lbl=$("<div>").attr({"class": "col-sm-auto"});
                                    a_txt.appendTo(a_lbl);
                                    $(".card-body").append(a_lbl);
                                }
								
								if(qsetting[i]["attach"]!=""){
                                    if(window.matchMedia('(max-width: 800px)').matches){
                                        var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 50vmin; padding: 5% 2.5%; margin: auto'></img></center>");
                                    }else{
                                        var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='height: 15vmax; padding: 3% 1.5%; margin: auto'></img></center>");
                                    }
                                    $(".card-body").append(a_pic);
                                }
                                
                                var opt_length=0;
                                for(var k=0; k<qsetting[i]["opt_txt"].length; k++){
                                    opt_length+=qsetting[i]["opt_txt"][k].length;
                                }
                                if(opt_length<=25){
                                    var o_all=$("<div>").attr({"id": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "class": "btn-group btn-group-toggle col-sm-12", "data-toggle": "buttons"});
                                    for(var l=0; l<qsetting[i]["opt_txt"].length; l++){
                                        var o_txt=$("<input>").attr({"type": "radio", "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "value": qsetting[i]["opt_value"][l], "note": qsetting[i]["note"][l], "skip": qsetting[i]["skip"][l].replace("[","").replace("]",""), "skips": skips, "required": true});
                                        var o_lbl=$("<label>").html(qsetting[i]["opt_txt"][l]).attr({"class": "btn btn-outline-secondary", "style": "font-size: 0.9em"});
                                        o_txt.appendTo(o_lbl);
                                        o_lbl.appendTo(o_all);
                                    }
                                    
                                    $(".card-body").append(o_all); 
                                }else{
                                    var o_all=$("<div>").attr({"id": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "data-toggle": "buttons"});
                                    var o_all1=$("<div>").attr({"class": "btn-group-vertical btn-group-toggle col-sm-6", "style": "vertical-align: text-top"});
                                    for(var l=0; l<qsetting[i]["opt_txt"].length/2; l++){
                                        var o_txt1=$("<input>").attr({"type": "radio", "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "value": qsetting[i]["opt_value"][l], "note": qsetting[i]["note"][l], "skip": qsetting[i]["skip"][l].replace("[","").replace("]",""), "skips": skips, "required": true});
                                        var o_lbl1=$("<label>").html(qsetting[i]["opt_txt"][l]).attr({"class": "btn btn-outline-secondary", "style": "font-size: 0.9em"});
                                        o_txt1.appendTo(o_lbl1);
                                        o_lbl1.appendTo(o_all1);
                                    }
                                    
                                    var o_all2=$("<div>").attr({"class": "btn-group-vertical btn-group-toggle col-sm-6", "style": "vertical-align: text-top"});
                                    for(var l; l<qsetting[i]["opt_txt"].length; l++){
                                        var o_txt2=$("<input>").attr({"type": "radio", "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "value": qsetting[i]["opt_value"][l], "note": qsetting[i]["note"][l], "skip": qsetting[i]["skip"][l].replace("[","").replace("]",""), "skips": skips, "required": true});
                                        var o_lbl2=$("<label>").html(qsetting[i]["opt_txt"][l]).attr({"class": "btn btn-outline-secondary", "style": "font-size: 0.9em"});
                                        o_txt2.appendTo(o_lbl2);
                                        o_lbl2.appendTo(o_all2);
                                    }
                                    o_all1.appendTo(o_all);
                                    o_all2.appendTo(o_all);
                                    $(".card-body").append(o_all); 
                                }                       

                                for(var m=0; m<qsetting[i]["note"].length; m++){
                                    if(qsetting[i]["note"][m]==1){
                                        var n_txt=$("<input>").attr({"type": "text", "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"]+"-"+qsetting[i]["opt_value"][m], "class": "form-control", "placeholder": "請補充說明", "style": "font-size: 0.9em; display: none"});
                                        var n_lbl=$("<div>").attr({"class": "col-sm-12"});
                                        n_txt.appendTo(n_lbl);
                                        $(".card-body").append(n_lbl);
                                    }
                                }
                                $(".card-body").append("<div><br></div>");

                            //1: 複選題
                            }else if(qsetting[i]["type"]==1){
								count++;
                                var skips="";
                                for(var j=0; j<qsetting[i]["skip"].length; j++){
                                    if(qsetting[i]["skip"][j]!=0){
                                        skips=skips+qsetting[i]["skip"][j].replace("[","").replace("]","")+",";
                                    }
                                }
                                
                                if(window.matchMedia('(max-width: 800px)').matches){
                                    var q_txt=$("<div>").html("<b style='padding: 0.25em; margin-left: 0.25em; margin-right: 1em; color: #FFFFFF; background-color: #6FA98D'>複選</b>"+(count)+". "+qsetting[i]["q_txt"].substr(0, 20)+"<br>"+qsetting[i]["q_txt"].substr(20, 22)+"<br>"+qsetting[i]["q_txt"].substr(42, 22)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1em; text-align: left"});
                                }else{
                                    var q_txt=$("<div>").html("<b style='padding: 0.25em; margin-left: -0.25em; margin-right: 0.5em; color: #FFFFFF; background-color: #6FA98D'>複選</b>"+(count)+". "+qsetting[i]["q_txt"]).attr({"class": "input-group-text", "style": "font-size: 0.85em"});
                                }
                                var q_lbl=$("<div>").attr({"class": "col-sm-auto"});
                                q_txt.appendTo(q_lbl);
                                $(".card-body").append(q_lbl);

                                if(qsetting[i]["annotate"]!=""){
                                    if(window.matchMedia('(max-width: 800px)').matches){
                                        var a_txt=$("<div>").html("<b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"].substr(0, 32)+"<br>"+qsetting[i]["annotate"].substr(32, 34)+"<br>"+qsetting[i]["annotate"].substr(66, 34)+"</b>").attr({"class": "input-group-text", "style": "line-height: 1.9em; font-size: 0.95em; background-color: #FFFFBB; border: none; text-align: left"});
                                    }else{
                                        var a_txt=$("<div>").html("<b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"]+"</b>").attr({"class": "input-group-text", "style": "font-size: 0.75em; background-color: #FFFFBB; border: none"});
                                    }
                                    var a_lbl=$("<div>").attr({"class": "col-sm-auto"});
                                    a_txt.appendTo(a_lbl);
                                    $(".card-body").append(a_lbl);
                                }
								
								if(qsetting[i]["attach"]!=""){
                                    if(window.matchMedia('(max-width: 800px)').matches){
                                        var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 50vmin; padding: 5% 2.5%; margin: auto'></img></center>");
                                    }else{
                                        var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='height: 15vmax; padding: 3% 1.5%; margin: auto'></img></center>");
                                    }
                                    $(".card-body").append(a_pic);
                                }
                                
                                var opt_length=0;
                                for(var k=0; k<qsetting[i]["opt_txt"].length; k++){
                                    opt_length+=qsetting[i]["opt_txt"][k].length;
                                }
                                if(opt_length<=25){
                                    var o_all=$("<div>").attr({"id": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "class": "btn-group btn-group-toggle col-sm-12", "data-toggle": "buttons"});
                                    for(var l=0; l<qsetting[i]["opt_txt"].length; l++){
                                        var o_txt=$("<input>").attr({"type": "checkbox", "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "value": qsetting[i]["opt_value"][l], "note": qsetting[i]["note"][l], "skip": qsetting[i]["skip"][l].replace("[","").replace("]",""), "skips": skips, "disjoint": qsetting[i]["disjoint"][l]});
                                        var o_lbl=$("<label>").html(qsetting[i]["opt_txt"][l]).attr({"class": "btn btn-outline-secondary", "style": "font-size: 0.9em"});
                                        o_txt.appendTo(o_lbl);
                                        o_lbl.appendTo(o_all);
                                    }
                                    
                                    $(".card-body").append(o_all); 
                                }else{
                                    var o_all=$("<div>").attr({"id": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "data-toggle": "buttons"});
                                    var o_all1=$("<div>").attr({"class": "btn-group-vertical btn-group-toggle col-sm-6", "style": "vertical-align: text-top"});
                                    for(var l=0; l<qsetting[i]["opt_txt"].length/2; l++){
                                        var o_txt1=$("<input>").attr({"type": "checkbox", "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "value": qsetting[i]["opt_value"][l], "note": qsetting[i]["note"][l], "skip": qsetting[i]["skip"][l].replace("[","").replace("]",""), "skips": skips, "disjoint": qsetting[i]["disjoint"][l]});
                                        var o_lbl1=$("<label>").html(qsetting[i]["opt_txt"][l]).attr({"class": "btn btn-outline-secondary", "style": "font-size: 0.9em"});
                                        o_txt1.appendTo(o_lbl1);
                                        o_lbl1.appendTo(o_all1);
                                    }
                                    
                                    var o_all2=$("<div>").attr({"class": "btn-group-vertical btn-group-toggle col-sm-6", "style": "vertical-align: text-top"});
                                    for(var l; l<qsetting[i]["opt_txt"].length; l++){
                                        var o_txt2=$("<input>").attr({"type": "checkbox", "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "value": qsetting[i]["opt_value"][l], "note": qsetting[i]["note"][l], "skip": qsetting[i]["skip"][l].replace("[","").replace("]",""), "skips": skips, "disjoint": qsetting[i]["disjoint"][l]});
                                        var o_lbl2=$("<label>").html(qsetting[i]["opt_txt"][l]).attr({"class": "btn btn-outline-secondary", "style": "font-size: 0.9em"});
                                        o_txt2.appendTo(o_lbl2);
                                        o_lbl2.appendTo(o_all2);
                                    }
                                    o_all1.appendTo(o_all);
                                    o_all2.appendTo(o_all);
                                    $(".card-body").append(o_all); 
                                }                       

                                for(var m=0; m<qsetting[i]["note"].length; m++){
                                    if(qsetting[i]["note"][m]==1){
                                        var n_txt=$("<input>").attr({"type": "text", "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"]+"-"+qsetting[i]["opt_value"][m], "class": "form-control", "placeholder": "請補充說明", "style": "font-size: 0.9em; display: none"});
                                        var n_lbl=$("<div>").attr({"class": "col-sm-12"});
                                        n_txt.appendTo(n_lbl);
                                        $(".card-body").append(n_lbl);
                                    }
                                }
                                $(".card-body").append("<div><br></div>");

                            //2: 數值題
                            }else if(qsetting[i]["type"]==2){
								count++;
                                if(window.matchMedia('(max-width: 800px)').matches){
                                    var q_txt=$("<div>").html("<b style='padding: 0.25em; margin-left: 0.25em; margin-right: 1em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]["q_txt"].substr(0, 20)+"<br>"+qsetting[i]["q_txt"].substr(20, 22)+"<br>"+qsetting[i]["q_txt"].substr(42, 22)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1em; text-align: left"});
                                }else{
                                    var q_txt=$("<div>").html("<b style='padding: 0.25em; margin-left: -0.25em; margin-right: 0.5em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]["q_txt"]).attr({"class": "input-group-text", "style": "font-size: 0.85em"});
                                }
                                var q_lbl=$("<div>").attr({"class": "col-sm-auto"});
                                q_txt.appendTo(q_lbl);
                                $(".card-body").append(q_lbl);

                                if(qsetting[i]["annotate"]!=""){
                                    if(window.matchMedia('(max-width: 800px)').matches){
                                        var a_txt=$("<div>").html("<b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"].substr(0, 32)+"<br>"+qsetting[i]["annotate"].substr(32, 34)+"<br>"+qsetting[i]["annotate"].substr(66, 34)+"</b>").attr({"class": "input-group-text", "style": "line-height: 1.9em; font-size: 0.95em; background-color: #FFFFBB; border: none; text-align: left"});
                                    }else{
                                        var a_txt=$("<div>").html("<b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"]+"</b>").attr({"class": "input-group-text", "style": "font-size: 0.75em; background-color: #FFFFBB; border: none"});
                                    }
                                    var a_lbl=$("<div>").attr({"class": "col-sm-auto"});
                                    a_txt.appendTo(a_lbl);
                                    $(".card-body").append(a_lbl);
                                }
								
								if(qsetting[i]["attach"]!=""){
                                    if(window.matchMedia('(max-width: 800px)').matches){
                                        var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 50vmin; padding: 5% 2.5%; margin: auto'></img></center>");
                                    }else{
                                        var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='height: 15vmax; padding: 3% 1.5%; margin: auto'></img></center>");
                                    }
                                    $(".card-body").append(a_pic);
                                }

                                var o_txt=$("<input>").attr({"type": "number", "id": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "class": "form-control validateNumber", "min": qsetting[i]["range_min"], "max": qsetting[i]["range_max"], "placeholder": "請輸入數字", "style": "font-size: 0.9em; text-align: center", "required": true});
                                var o_lbl=$("<div>").attr({"class": "col-sm-12"});
                                o_txt.appendTo(o_lbl);
                                $(".card-body").append(o_lbl);
                                $(".card-body").append("<div><br></div>");

                            //3: 文字題
                            }else if(qsetting[i]["type"]==3){
								count++;
                                if(window.matchMedia('(max-width: 800px)').matches){
                                    var q_txt=$("<div>").html("<b style='padding: 0.25em; margin-left: 0.25em; margin-right: 1em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]["q_txt"].substr(0, 20)+"<br>"+qsetting[i]["q_txt"].substr(20, 22)+"<br>"+qsetting[i]["q_txt"].substr(42, 22)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1em; text-align: left"});
                                }else{
                                    var q_txt=$("<div>").html("<b style='padding: 0.25em; margin-left: -0.25em; margin-right: 0.5em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]["q_txt"]).attr({"class": "input-group-text", "style": "font-size: 0.85em"});
                                }
                                var q_lbl=$("<div>").attr({"class": "col-sm-auto"});
                                q_txt.appendTo(q_lbl);
                                $(".card-body").append(q_lbl);

                                if(qsetting[i]["annotate"]!=""){
                                    if(window.matchMedia('(max-width: 800px)').matches){
                                        var a_txt=$("<div>").html("<b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"].substr(0, 32)+"<br>"+qsetting[i]["annotate"].substr(32, 34)+"<br>"+qsetting[i]["annotate"].substr(66, 34)+"</b>").attr({"class": "input-group-text", "style": "line-height: 1.9em; font-size: 0.95em; background-color: #FFFFBB; border: none; text-align: left"});
                                    }else{
                                        var a_txt=$("<div>").html("<b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"]+"</b>").attr({"class": "input-group-text", "style": "font-size: 0.75em; background-color: #FFFFBB; border: none"});
                                    }
                                    var a_lbl=$("<div>").attr({"class": "col-sm-auto"});
                                    a_txt.appendTo(a_lbl);
                                    $(".card-body").append(a_lbl);
                                }
								
								if(qsetting[i]["attach"]!=""){
                                    if(window.matchMedia('(max-width: 800px)').matches){
                                        var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 50vmin; padding: 5% 2.5%; margin: auto'></img></center>");
                                    }else{
                                        var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='height: 15vmax; padding: 3% 1.5%; margin: auto'></img></center>");
                                    }
                                    $(".card-body").append(a_pic);
                                }

                                var o_txt=$("<input>").attr({"type": "text", "id": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "class": "form-control", "placeholder": "請輸入答案", "style": "font-size: 0.9em; text-align: center", "required": true});
                                var o_lbl=$("<div>").attr({"class": "col-sm-12"});
                                o_txt.appendTo(o_lbl);
                                $(".card-body").append(o_lbl);
                                $(".card-body").append("<div><br></div>");

                            //4. 時間題                   
                            }else if(qsetting[i]["type"]==4){
								count++;
                                if(window.matchMedia('(max-width: 800px)').matches){
                                    var q_txt=$("<div>").html("<b style='padding: 0.25em; margin-left: 0.25em; margin-right: 1em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]["q_txt"].substr(0, 20)+"<br>"+qsetting[i]["q_txt"].substr(20, 22)+"<br>"+qsetting[i]["q_txt"].substr(42, 22)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1em; text-align: left"});
                                }else{
                                    var q_txt=$("<div>").html("<b style='padding: 0.25em; margin-left: -0.25em; margin-right: 0.5em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]["q_txt"]).attr({"class": "input-group-text", "style": "font-size: 0.85em"});
                                }
                                var q_lbl=$("<div>").attr({"class": "col-sm-auto"});
                                q_txt.appendTo(q_lbl);
                                $(".card-body").append(q_lbl);

                                if(qsetting[i]["annotate"]!=""){
                                    if(window.matchMedia('(max-width: 800px)').matches){
                                        var a_txt=$("<div>").html("<b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"].substr(0, 32)+"<br>"+qsetting[i]["annotate"].substr(32, 34)+"<br>"+qsetting[i]["annotate"].substr(66, 34)+"</b>").attr({"class": "input-group-text", "style": "line-height: 1.9em; font-size: 0.95em; background-color: #FFFFBB; border: none; text-align: left"});
                                    }else{
                                        var a_txt=$("<div>").html("<b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"]+"</b>").attr({"class": "input-group-text", "style": "font-size: 0.75em; background-color: #FFFFBB; border: none"});
                                    }
                                    var a_lbl=$("<div>").attr({"class": "col-sm-auto"});
                                    a_txt.appendTo(a_lbl);
                                    $(".card-body").append(a_lbl);
                                }
								
								if(qsetting[i]["attach"]!=""){
                                    if(window.matchMedia('(max-width: 800px)').matches){
                                        var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 50vmin; padding: 5% 2.5%; margin: auto'></img></center>");
                                    }else{
                                        var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='height: 15vmax; padding: 3% 1.5%; margin: auto'></img></center>");
                                    }
                                    $(".card-body").append(a_pic);
                                }

                                var o_txt=$("<input>").attr({"type": "time", "id": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "class": "form-control", "style": "font-size: 0.9em; text-align: center", "required": true});
                                var o_lbl=$("<div>").attr({"class": "col-sm-12"});
                                o_txt.appendTo(o_lbl);
                                $(".card-body").append(o_lbl);
                                $(".card-body").append("<div><br></div>");

                            //9. 題目區段
                            }else if(qsetting[i]["type"]==9){
                                if(window.matchMedia('(max-width: 800px)').matches){
                                    var q_txt=$("<div>").html("<b>"+qsetting[i]["q_txt"]+"</b>").attr({"class": "input-group-text", "style": "margin-left: -0.25em; line-height: 0.75em; font-size: 1.5em; background-color: #FFFFBB; border: none; text-align: left"});
                                }else{
                                    var q_txt=$("<div>").html("<b>"+qsetting[i]["q_txt"]+"</b>").attr({"class": "input-group-text", "style": "margin-left: -0.5em; font-size: 1.15em; background-color: #FFFFBB; border: none; text-align: left"});
                                }
                                var q_lbl=$("<div>").attr({"class": "col-sm-auto", "style": "background-color: #FFFFBB"});
                                q_txt.appendTo(q_lbl);
                                $(".card-body").append(q_lbl);

                                if(qsetting[i]["annotate"]!=""){
                                    if(window.matchMedia('(max-width: 800px)').matches){
                                        var a_txt=$("<div>").html(qsetting[i]["annotate"].substr(0, 26)+"<br>"+qsetting[i]["annotate"].substr(26, 26)+"<br>"+qsetting[i]["annotate"].substr(52, 26)).attr({"class": "input-group-text", "style": "margin-left: -0.25em; font-size: 1.2em; background-color: #FFFFBB; border: none; text-align: left"});
                                    }else{
                                        var a_txt=$("<div>").html(qsetting[i]["annotate"].substr(0, 55)+"<br>"+qsetting[i]["annotate"].substr(55, 55)).attr({"class": "input-group-text", "style": "margin-left: -0.5em; font-size: 0.85em; background-color: #FFFFBB; border: none; text-align: left"});
                                    }
                                    var a_lbl=$("<div>").attr({"class": "col-sm-auto"});
                                    a_txt.appendTo(a_lbl);
                                    $(".card-body").append(a_lbl);
									$(".card-body").append("<p></p>");
                                }
								
								if(qsetting[i]["attach"]!=""){
                                    if(window.matchMedia('(max-width: 800px)').matches){
                                        var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 80vmin; padding: 5% 2.5%; margin: auto'></img></center>");
                                    }else{
                                        var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 50vmax; padding: 3% 1.5%; margin: auto'></img></center>");
                                    }
                                    $(".card-body").append(a_pic);
									$(".card-body").append("<p></p>");
                                }
                            }
                        }
						$(".question_n").empty().append(count);
                    }
                    
                    if(window.matchMedia('(max-width: 800px)').matches){
                        $(".btn-group-toggle").removeClass('btn-group').addClass('btn-group-vertical');
                    }  
                }, error: function(e){
                    console.log(e);
                }
			})

            $(document).on("change", "input[type='radio']", function(event){
                event.preventDefault();
                var q_id=$(this).attr("name").split("-")[0];
                var q_sn=$(this).attr("name").split("-")[1];
                var value=$(this).parent().parent().parent().find("input[name="+q_id+"-"+q_sn+"][note='1']").attr("value");
                
                if($(this).attr("note")==1){
                    $("input[name="+q_id+"-"+q_sn+"-"+value+"]").attr("required", true).show();          
                }else{
                    $("input[name="+q_id+"-"+q_sn+"-"+value+"]").attr("required", false).val("").hide();
                }
                
                if($(this).attr("skip")!=0){
                    //skip: 這個選項要跳掉的題目
                    for(i=0; i<$(this).attr("skip").split(",").length; i++){
                        var skip_q1=$(this).attr("skip").split(",")[i].trim();
                        var type_q1=$("input[name="+skip_q1+"]").attr("type");
                        var value_q1=$("input[name="+skip_q1+"]").parent().parent().parent().find("input[name="+skip_q1+"][note='1']").attr("value");
                        
                        if(type_q1=="radio"|type_q1=="checkbox"){
                            $("input[name="+skip_q1+"]").prop("checked", false).parent().removeClass("active");                            
                            $("input[name="+skip_q1+"]").parent().parent().append("<input type='radio' name="+skip_q1+" value='99' style='display: none' checked>");
                            $("input[name="+skip_q1+"-"+value_q1+"]").val("99").hide();
                            $("div[id="+skip_q1+"]").hide();
                            var element_q1=document.getElementById(skip_q1);
                            if(window.matchMedia('(max-width: 800px)').matches){
                                element_q1.insertAdjacentHTML("afterend", "<div id="+skip_q1+skip_q1+" style='padding-top: 2.25em; padding-bottom: 1.25em; text-align: center'><b style='padding: 2.25%; font-size: 1.5em; color: #EF475D; background-color: yellow'>本題不需作答</b></div>");
                            }else{
                                element_q1.insertAdjacentHTML("afterend", "<div id="+skip_q1+skip_q1+" style='padding-top: 1em; text-align: center'><b style='padding: 1%; color: #EF475D; background-color: yellow'>本題不需作答</b></div>");
                            }
                        }else{
                            $("input[name="+skip_q1+"]").val("99").hide();
                            var element_q1=document.getElementById(skip_q1);
                            if(window.matchMedia('(max-width: 800px)').matches){
                                element_q1.insertAdjacentHTML("afterend", "<div id="+skip_q1+skip_q1+" style='padding-top: 2.25em; padding-bottom: 1.25em; text-align: center'><b style='padding: 2.25%; font-size: 1.5em; color: #EF475D; background-color: yellow'>本題不需作答</b></div>");
                            }else{
                                element_q1.insertAdjacentHTML("afterend", "<div id="+skip_q1+skip_q1+" style='padding-top: 1em; text-align: center'><b style='padding: 1%; color: #EF475D; background-color: yellow'>本題不需作答</b></div>");
                            }
                        }
                        
                    }
                }else{
                    //skips: 這組問題所有可能被跳掉的題目
                    for(i=0; i<$(this).attr("skips").split(",").length-1; i++){
                        var skip_q2=$(this).attr("skips").split(",")[i].trim();
                        var type_q2=$("input[name="+skip_q2+"]").attr("type");
                        var value_q2=$("input[name="+skip_q2+"]").parent().parent().parent().find("input[name="+skip_q2+"][note='1']").attr("value");
                        
                        if(type_q2=="radio"|type_q2=="checkbox"){
                            $("input[name="+skip_q2+"]").parent().parent().find("input[name="+skip_q2+"][value='99']").prop("checked", false).removeClass("active");
                            $("input[name="+skip_q2+"-"+value_q2+"]").val("");
                            $("div[id="+skip_q2+"]").show();
                            $("div[id="+skip_q2+skip_q2+"]").hide();
                        }else{
                            $("input[name="+skip_q2+"]").val("").show();
                            $("div[id="+skip_q2+skip_q2+"]").hide();
                        }
                    }
                }
            })
            
            $(document).on("change", "input[type='checkbox']", function(event){
                event.preventDefault();
                var q_id=$(this).attr("name").split("-")[0];
                var q_sn=$(this).attr("name").split("-")[1];
                var value1=$(this).attr("value");
                var value2=$(this).parent().parent().parent().find("input[name="+q_id+"-"+q_sn+"][note='1']").attr("value");
                var checked=$(this).is(":checked")?1 :0;
                
                if($(this).attr("disjoint")==1&checked==1){
                    $("input[name="+q_id+"-"+q_sn+"]").prop("checked", false).parent().removeClass("active");
                    $("input[name="+q_id+"-"+q_sn+"][value="+value1+"]").prop("checked", true); 
                }else{
                    $("input[name="+q_id+"-"+q_sn+"][disjoint='1']").prop("checked", false).parent().removeClass("active");
                }
                
                if($(this).attr("note")==1&checked==1){
                    $("input[name="+q_id+"-"+q_sn+"-"+value2+"]").attr("required", true).show();          
                }else if($(this).attr("note")==1&checked==0){
                    $("input[name="+q_id+"-"+q_sn+"-"+value2+"]").attr("required", false).val("").hide();
                }else if($(this).attr("disjoint")==1&checked==1){
                    $("input[name="+q_id+"-"+q_sn+"-"+value2+"]").attr("required", false).val("").hide();
                }
                
                if($(this).attr("skip")!=0&checked==1){
                    //skip: 這個選項要跳掉的題目
                    for(i=0; i<$(this).attr("skip").split(",").length; i++){
                        var skip_q1=$(this).attr("skip").split(",")[i].trim();
                        var type_q1=$("input[name="+skip_q1+"]").attr("type");
                        var value_q1=$("input[name="+skip_q1+"]").parent().parent().parent().find("input[name="+skip_q1+"][note='1']").attr("value");
                        
                        if(type_q1=="radio"|type_q1=="checkbox"){
                            $("input[name="+skip_q1+"]").prop("checked", false).parent().removeClass("active");
                            $("input[name="+skip_q1+"]").parent().parent().append("<input type='radio' name="+skip_q1+" value='99' style='display: none' checked>");
                            $("input[name="+skip_q1+"-"+value_q1+"]").val("99").hide();
                            $("div[id="+skip_q1+"]").hide();
                            var element_q1=document.getElementById(skip_q1);
                            if(window.matchMedia('(max-width: 800px)').matches){
                                element_q1.insertAdjacentHTML("afterend", "<div id="+skip_q1+skip_q1+" style='padding-top: 2.25em; padding-bottom: 1.25em; text-align: center'><b style='padding: 2.25%; font-size: 1.5em; color: #EF475D; background-color: yellow'>本題不需作答</b></div>");
                            }else{
                                element_q1.insertAdjacentHTML("afterend", "<div id="+skip_q1+skip_q1+" style='padding-top: 1em; text-align: center'><b style='padding: 1%; color: #EF475D; background-color: yellow'>本題不需作答</b></div>");
                            }
                        }else{
                            $("input[name="+skip_q1+"]").val("99").hide();
                            var element_q1=document.getElementById(skip_q1);
                            if(window.matchMedia('(max-width: 800px)').matches){
                                element_q1.insertAdjacentHTML("afterend", "<div id="+skip_q1+skip_q1+" style='padding-top: 2.25em; padding-bottom: 1.25em; text-align: center'><b style='padding: 2.25%; font-size: 1.5em; color: #EF475D; background-color: yellow'>本題不需作答</b></div>");
                            }else{
                                element_q1.insertAdjacentHTML("afterend", "<div id="+skip_q1+skip_q1+" style='padding-top: 1em; text-align: center'><b style='padding: 1%; color: #EF475D; background-color: yellow'>本題不需作答</b></div>");
                            }
                        }
                    }
                }else{
                    //skips: 這組問題所有可能被跳掉的題目
                    for(i=0; i<$(this).attr("skips").split(",").length-1; i++){
                        var skip_q2=$(this).attr("skips").split(",")[i].trim();
                        var type_q2=$("input[name="+skip_q2+"]").attr("type");
                        var value_q2=$("input[name="+skip_q2+"]").parent().parent().parent().find("input[name="+skip_q2+"][note='1']").attr("value");
                        
                        if(type_q2=="radio"|type_q2=="checkbox"){                            
                            $("input[name="+skip_q2+"]").parent().parent().find("input[name="+skip_q2+"][value='99']").prop("checked", false).removeClass("active");
                            $("input[name="+skip_q2+"-"+value_q2+"]").val("");
                            $("div[id="+skip_q2+"]").show();
                            $("div[id="+skip_q2+skip_q2+"]").hide();
                        }else{
                            $("input[name="+skip_q2+"]").val("").show();
                            $("div[id="+skip_q2+skip_q2+"]").hide();
                        }
                    }
                }
            })
            
            $(document).on("change", ".validateNumber", function(event){
                event.preventDefault();
                var min=parseInt($(this).attr("min"));
                var max=parseInt($(this).attr("max"));
                var ansnum=Number($(this).val());

                if(ansnum>max|ansnum<min){
                    $(this).val("");
                    $.alert({
                        title: "",
                        content: "注意：<br>數值超出範圍，合理值應介於 <b style='color: red'>"+min+"－"+max+"</b> 之間，請修正！",
                    })
                    return false;
                }
            })
            
            $.fn.serializeObject=function(){
                var checkboxNames=[];
                var $checkbox=$("input[type='checkbox']", this);
                $.each($checkbox, function(){
                    if($.inArray(this.name, checkboxNames)==-1){
                        checkboxNames.push(this.name);
                    }
                })
            
                var o={};
                var a=this.serializeArray();
                $.each(a, function(){
                    if(o[this.name]!==undefined){
                        if(!o[this.name].push){
                            o[this.name]=[o[this.name]];
                        }
                        o[this.name].push(this.value||"");
                    }else{
                        if($.inArray(this.name, checkboxNames)>=0){
                            o[this.name]=[this.value];
                        }else{
                            o[this.name]=this.value||"";
                        }
                    }
                })
                return o;
            }
					
            $("#betaForm").on("submit", function(){
                event.preventDefault();
                $("#submit").attr("disabled", true);
                var answer=$('#betaForm').serializeObject();

                $.ajax({ 
                    type: "POST",
                    url: "",
                    data: {answer: answer},
                    success: function(data){ 
                        console.log(data);
                        $.confirm({
                            title: "<i class='fas fa-check-circle' style='color: blue'></i> 填答完成",
                            content: "恭喜完成作答！可以下載測試資料囉！",
                            buttons:{
                                "OK": function(){
                                    window.location.href="./main.php";
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
    <?php include("header.php");?>
    
    <div class="wrap">
        <img id="title" src="./pic/square.png">
        <div class="title"><b>專案預覽</b></div>
    </div>
	
	<div class="container">
		<div class="infobar">
            <b><span style="color: #FFFFFF; background-color: #813C85; padding: 0.5em; border-radius: 10px 0px 0px 10px">專案名稱</span><span style="background-color: #F2E7E5; padding: 0.5em; border-radius: 0px 10px 10px 0px"><span class="project_name"></span></span></b>
            <b><span style="color: #FFFFFF; background-color: #F25A47; padding: 0.5em; border-radius: 10px 0px 0px 10px">題數</span><span style="background-color: #F2E7E5; padding: 0.5em; border-radius: 0px 10px 10px 0px"><span class="question_n"></span></span></b>
		</div>
		
        <div class="card">
			<form id="betaForm">
            <div class="card-body">
			<p style="font-size: 0.9em">
				▼ 以下是專案問卷之<b>預覽畫面</b>，您可直接點選作答：
			</p>
			</div>
			
			<div style="text-align: center; margin-bottom: 2.5%">
				<button id="submit" class="btn">
					<img src="/pic/submit.png" class="icon">
				</button>
			</div>
			</form>
		</div>
	</div>
    
    <?php include("footer.php");?>
</body>
</html>
