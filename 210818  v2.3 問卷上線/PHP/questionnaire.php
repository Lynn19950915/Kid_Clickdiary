<?php
	session_start();
	include("db.php");
    include("upload.php");

    $project_id=isset($_GET["project_id"])?$_GET["project_id"]: 0;
    $sample_id=isset($_GET["iid"])?$_GET["iid"]: 0;
	$random_code=isset($_GET["random_code"])?$_GET["random_code"]: 0;

    if(isset($_POST["checkId"])){
		$sql1="SELECT * FROM `project` WHERE project_id= :v1 and active=3";
		$stmt=$db->prepare($sql1);
		$stmt->bindParam(":v1", $project_id);
		$stmt->execute();
        $rs1=$stmt->fetch(PDO::FETCH_ASSOC);
        
        if($stmt->rowCount()==0){
            echo "Invalid Attempt";
        }else{
            $sql2="SELECT * FROM `sample` WHERE project_id= :v1 and sample_id= :v2 and random_code= :v3";
            $stmt=$db->prepare($sql2);
            $stmt->bindParam(":v1", $project_id);
            $stmt->bindParam(":v2", $sample_id);
			$stmt->bindParam(":v3", $random_code);
            $stmt->execute();
            $rs2=$stmt->fetch(PDO::FETCH_ASSOC);
            
            if($stmt->rowCount()==0){
                echo "Invalid Attempt";
            }else{
                $final=$project_id."final";
                
                $sql3="SELECT * FROM `:v1` WHERE sample_id= :v2";
                $stmt=$db->prepare($sql3);
                $stmt->bindParam(":v1", $final);
                $stmt->bindParam(":v2", $sample_id);
                $stmt->execute();
                $rs3=$stmt->fetch(PDO::FETCH_ASSOC);
                
                if($stmt->rowCount()>=1){
                    echo "Questionnaire Done";
                }
            }
        }
		exit();
	}

	if(isset($_POST["fetchProject"])){
		$sql4="SELECT project_name, csv_schema, points FROM `project` WHERE project_id= :v1";
		$stmt=$db->prepare($sql4);
		$stmt->bindParam(":v1", $project_id);
		$stmt->execute();
        
		$json=array();
		while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
            $json[0]=$row["project_name"];
            $json[1]=$row["csv_schema"];
			$json[2]=$row["points"];
		}
		
		$sql5="SELECT accumulate FROM `sample` WHERE project_id= :v1 and sample_id= :v2";
		$stmt=$db->prepare($sql5);
		$stmt->bindParam(":v1", $project_id);
		$stmt->bindParam(":v2", $sample_id);
		$stmt->execute();
		
		while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
			$json[3]=$row["accumulate"];
		}		
		echo json_encode($json, JSON_UNESCAPED_UNICODE);
		exit();
	}

	if(isset($_POST["answer"])){
        $answer=json_encode($_POST["ans"], JSON_UNESCAPED_UNICODE);
        $final=$project_id."final";
        
		$sql6="INSERT INTO `:v1` VALUES(NULL, :v2, NOW(), :v3)";
		$stmt=$db->prepare($sql6);
		$stmt->bindParam(":v1", $final);
        $stmt->bindParam(":v2", $sample_id);
		$stmt->bindParam(":v3", $answer);
		$stmt->execute();
        
        $sql7="UPDATE `project` SET n=n+1 WHERE project_id= :v1";
		$stmt=$db->prepare($sql7);
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
            background: url("./pic/index.jpg"); background-size: cover;
        }

        .logo{
            width: 2em; margin-left: 5%; padding-bottom: 1%;
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
		
		.modal{
            width: 30%; left: 35%; top: 30%
        }
		
		.modal-body{
			padding: 7.5% 27.5%;
		}
		
		.infobar{
            letter-spacing: 0.1em; padding-bottom: 2em;
            font-size: 0.9em; text-align: right;
        }
		
		.container{
			width: 60%; margin: 20px auto 100px auto;
			align-content: center;
		}
        
        .card{
            background-color: #FFFFBB;
        }
        
        .card-body{
		    line-height: 1.75em; letter-spacing: 0.05em; margin-top: -1em; padding: 0 5%;
            text-align: left;
		}
		
		.icon{
			width: 8.5em;
		}
        
        /* RESPONSIVE */
		@media screen and (max-width: 1200px){
            body{
                padding-bottom: 150px;
            }
            
            .navbar-brand{
                letter-spacing: 0;
                font-size: 1em;
            }

            .wrap{
                margin: -10px auto;
            }
            
            #title{
                width: 7.5em;
            }
            
            .title{
                font-size: 1.15em;
            }
			
			.modal{
				width: 70%; top: 40%; left: 15%;
				font-size: 0.7em;
			}
            
			.infobar{
                font-size: 0.6em;
            }
			
            .container{
                width: 100%; margin: 7.5% auto -15% auto;
            }

            .card-body{
                padding: 0 2.5%;
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
                url: "",
                data: {checkId: 1},
                success: function(data){
                    if(data=="Invalid Attempt"){
						$("#submit").hide();
                        $.alert({
                            title: "",
                            content: "您使用的問卷連結不正確，或是此專案已關閉不開放填答！",
                        })
                    }else if(data=="Questionnaire Done"){
						$("#submit").hide();
                        $.alert({
                            title: "",
                            content: "您已完成過此份問卷！",
                        })
                    }else{
                        $.ajax({ 
                            type: "POST",
                            dataType: "json",
                            url: "",
                            data: {fetchProject: 1},
                            success: function(data){
                                $(".project_name").empty().append(data[0]);
								$(".project_points").empty().append(data[2]);
								$(".accumulate").empty().append(data[3]);
                                var qsetting=JSON.parse(data[1]);
								var s_width=screen.width;
								
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
										
										if(window.matchMedia('(max-width: 1200px)').matches){
											var n_q=Math.floor(s_width/25);
											if(qsetting[i]["q_txt"].length>5*n_q+4){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; background-color: #FFC90C'>單選</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(3*n_q+2, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(4*n_q+3, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(5*n_q+4, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(6*n_q+5, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else if(qsetting[i]["q_txt"].length>3*n_q+2){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; background-color: #FFC90C'>單選</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(3*n_q+2, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(4*n_q+3, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else if(qsetting[i]["q_txt"].length>n_q){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; background-color: #FFC90C'>單選</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else{
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; background-color: #FFC90C'>單選</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}
										}else{
											var n_q=Math.floor(s_width/37.5);
											if(qsetting[i]["q_txt"].length>n_q){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i> <b style='padding: 0.25em; margin-left: -0.25em; margin-right: 0.5em; background-color: #FFC90C'>單選</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.85em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else{
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i> <b style='padding: 0.25em; margin-left: -0.25em; margin-right: 0.5em; background-color: #FFC90C'>單選</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.85em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}
										}
										var q_lbl=$("<div>").attr({"class": "col-sm-auto"});
										q_txt.appendTo(q_lbl);
										$(".card-body").append(q_lbl);

										if(qsetting[i]["annotate"]!=""){
											if(window.matchMedia('(max-width: 1200px)').matches){
												var n_a=Math.floor(s_width/17.5);
												if(qsetting[i]["annotate"].length>n_a){
													var a_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 2.5% 0 0'></i> <b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"].substr(0, n_a)+"<br>"+qsetting[i]["annotate"].substr(n_a, n_a+1)+"<br>"+qsetting[i]["annotate"].substr(2*n_a+1, n_a+1)+"</b>").attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.95em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`annotate`]+"')"});
												}else{
													var a_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 2.5% 0 0'></i> <b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"].substr(0, n_a)+"</b>").attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.95em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`annotate`]+"')"});
												}
											}else{
												var a_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i> <b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"]+"</b>").attr({"class": "input-group-text", "style": "font-size: 0.75em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`annotate`]+"')"});
											}
											var a_lbl=$("<div>").attr({"class": "col-sm-auto"});
											a_txt.appendTo(a_lbl);
											$(".card-body").append(a_lbl);
										}
										
										if(qsetting[i]["attach"]!=""){
											if(window.matchMedia('(max-width: 1200px)').matches){
												var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 80vmin; padding: 3% 1.5%; margin: auto'></img></center>");
											}else{
												var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 50vmax; padding: 3% 1.5%; margin: auto'></img></center>");
											}
											$(".card-body").append(a_pic);
										}
										
										if(qsetting[i]["random"]!=""){
											var r_list=qsetting[i]["random"].split(",");
											var r_order=Math.floor(Math.random()*r_list.length);
											var r_number=$("<div>").html(r_list[r_order]).attr({"style": "width: 10vmin; height: 10vmin; margin: 2.5vmin auto; padding: 2.5vmin 0; text-align: center; font-size: 2em; color: #FFFFFF; background-color: red; border-radius: 5vmin"});	
											$(".card-body").append(r_number);
										}
										
										var opt_length=0;
										for(var k=0; k<qsetting[i]["opt_txt"].length; k++){
											opt_length+=qsetting[i]["opt_txt"][k].length;
										}
										if(opt_length<=20){
											var o_all=$("<div>").attr({"id": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "class": "btn-group btn-group-toggle col-sm-12", "data-toggle": "buttons"});
											for(var l=0; l<qsetting[i]["opt_txt"].length; l++){
												var o_txt=$("<input>").attr({"type": "radio", "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "value": qsetting[i]["opt_value"][l], "note": qsetting[i]["note"][l], "skip": qsetting[i]["skip"][l].replace("[","").replace("]",""), "skips": skips, "required": true});
												var o_lbl=$("<label>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> "+qsetting[i]["opt_txt"][l]).attr({"class": "btn btn-outline-secondary", "style": "font-size: 0.85em", "onClick": "read('"+qsetting[i][`opt_txt`][l]+"')"});
												o_txt.appendTo(o_lbl);
												o_lbl.appendTo(o_all);
											}
											
											$(".card-body").append(o_all); 
										}else{
											var o_all=$("<div>").attr({"id": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "data-toggle": "buttons"});
											var o_all1=$("<div>").attr({"class": "btn-group-vertical btn-group-toggle col-sm-6", "style": "vertical-align: text-top"});
											for(var l=0; l<qsetting[i]["opt_txt"].length/2; l++){
												var o_txt1=$("<input>").attr({"type": "radio", "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "value": qsetting[i]["opt_value"][l], "note": qsetting[i]["note"][l], "skip": qsetting[i]["skip"][l].replace("[","").replace("]",""), "skips": skips, "required": true});
												var o_lbl1=$("<label>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> "+qsetting[i]["opt_txt"][l]).attr({"class": "btn btn-outline-secondary", "style": "font-size: 0.85em", "onClick": "read('"+qsetting[i][`opt_txt`][l]+"')"});
												o_txt1.appendTo(o_lbl1);
												o_lbl1.appendTo(o_all1);
											}
											
											var o_all2=$("<div>").attr({"class": "btn-group-vertical btn-group-toggle col-sm-6", "style": "vertical-align: text-top"});
											for(var l; l<qsetting[i]["opt_txt"].length; l++){
												var o_txt2=$("<input>").attr({"type": "radio", "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "value": qsetting[i]["opt_value"][l], "note": qsetting[i]["note"][l], "skip": qsetting[i]["skip"][l].replace("[","").replace("]",""), "skips": skips, "required": true});
												var o_lbl2=$("<label>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> "+qsetting[i]["opt_txt"][l]).attr({"class": "btn btn-outline-secondary", "style": "font-size: 0.85em", "onClick": "read('"+qsetting[i][`opt_txt`][l]+"')"});
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
										
										if(window.matchMedia('(max-width: 1200px)').matches){
											var n_q=Math.floor(s_width/25);
											if(qsetting[i]["q_txt"].length>5*n_q+4){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; color: #FFFFFF; background-color: #6FA98D'>複選</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(3*n_q+2, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(4*n_q+3, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(5*n_q+4, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(6*n_q+5, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else if(qsetting[i]["q_txt"].length>3*n_q+2){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; color: #FFFFFF; background-color: #6FA98D'>複選</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(3*n_q+2, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(4*n_q+3, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else if(qsetting[i]["q_txt"].length>n_q){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; color: #FFFFFF; background-color: #6FA98D'>複選</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else{
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; color: #FFFFFF; background-color: #6FA98D'>複選</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}
										}else{
											var n_q=Math.floor(s_width/37.5);
											if(qsetting[i]["q_txt"].length>n_q){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i> <b style='padding: 0.25em; margin-left: -0.25em; margin-right: 0.5em; color: #FFFFFF; background-color: #6FA98D'>複選</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.85em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else{
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i> <b style='padding: 0.25em; margin-left: -0.25em; margin-right: 0.5em; color: #FFFFFF; background-color: #6FA98D'>複選</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.85em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}
										}
										var q_lbl=$("<div>").attr({"class": "col-sm-auto"});
										q_txt.appendTo(q_lbl);
										$(".card-body").append(q_lbl);

										if(qsetting[i]["annotate"]!=""){
											if(window.matchMedia('(max-width: 1200px)').matches){
												var n_a=Math.floor(s_width/17.5);
												if(qsetting[i]["annotate"].length>n_a){
													var a_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 2.5% 0 0'></i> <b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"].substr(0, n_a)+"<br>"+qsetting[i]["annotate"].substr(n_a, n_a+1)+"<br>"+qsetting[i]["annotate"].substr(2*n_a+1, n_a+1)+"</b>").attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.95em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`annotate`]+"')"});
												}else{
													var a_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 2.5% 0 0'></i> <b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"].substr(0, n_a)+"</b>").attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.95em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`annotate`]+"')"});
												}
											}else{
												var a_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i> <b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"]+"</b>").attr({"class": "input-group-text", "style": "font-size: 0.75em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`annotate`]+"')"});
											}
											var a_lbl=$("<div>").attr({"class": "col-sm-auto"});
											a_txt.appendTo(a_lbl);
											$(".card-body").append(a_lbl);
										}
										
										if(qsetting[i]["attach"]!=""){
											if(window.matchMedia('(max-width: 1200px)').matches){
												var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 80vmin; padding: 3% 1.5%; margin: auto'></img></center>");
											}else{
												var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 50vmax; padding: 3% 1.5%; margin: auto'></img></center>");
											}
											$(".card-body").append(a_pic);
										}
										
										if(qsetting[i]["random"]!=""){
											var r_list=qsetting[i]["random"].split(",");
											var r_order=Math.floor(Math.random()*r_list.length);
											var r_number=$("<div>").html(r_list[r_order]).attr({"style": "width: 10vmin; height: 10vmin; margin: 2.5vmin auto; padding: 2.5vmin 0; text-align: center; font-size: 2em; color: #FFFFFF; background-color: red; border-radius: 5vmin"});
											$(".card-body").append(r_number);
										}
										
										var opt_length=0;
										for(var k=0; k<qsetting[i]["opt_txt"].length; k++){
											opt_length+=qsetting[i]["opt_txt"][k].length;
										}
										if(opt_length<=20){
											var o_all=$("<div>").attr({"id": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "class": "btn-group btn-group-toggle col-sm-12", "data-toggle": "buttons"});
											for(var l=0; l<qsetting[i]["opt_txt"].length; l++){
												var o_txt=$("<input>").attr({"type": "checkbox", "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "value": qsetting[i]["opt_value"][l], "note": qsetting[i]["note"][l], "skip": qsetting[i]["skip"][l].replace("[","").replace("]",""), "skips": skips, "disjoint": qsetting[i]["disjoint"][l]});
												var o_lbl=$("<label>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> "+qsetting[i]["opt_txt"][l]).attr({"class": "btn btn-outline-secondary", "style": "font-size: 0.85em", "onClick": "read('"+qsetting[i][`opt_txt`][l]+"')"});
												o_txt.appendTo(o_lbl);
												o_lbl.appendTo(o_all);
											}
											
											$(".card-body").append(o_all); 
										}else{
											var o_all=$("<div>").attr({"id": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "data-toggle": "buttons"});
											var o_all1=$("<div>").attr({"class": "btn-group-vertical btn-group-toggle col-sm-6", "style": "vertical-align: text-top"});
											for(var l=0; l<qsetting[i]["opt_txt"].length/2; l++){
												var o_txt1=$("<input>").attr({"type": "checkbox", "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "value": qsetting[i]["opt_value"][l], "note": qsetting[i]["note"][l], "skip": qsetting[i]["skip"][l].replace("[","").replace("]",""), "skips": skips, "disjoint": qsetting[i]["disjoint"][l]});
												var o_lbl1=$("<label>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> "+qsetting[i]["opt_txt"][l]).attr({"class": "btn btn-outline-secondary", "style": "font-size: 0.85em", "onClick": "read('"+qsetting[i][`opt_txt`][l]+"')"});
												o_txt1.appendTo(o_lbl1);
												o_lbl1.appendTo(o_all1);
											}
											
											var o_all2=$("<div>").attr({"class": "btn-group-vertical btn-group-toggle col-sm-6", "style": "vertical-align: text-top"});
											for(var l; l<qsetting[i]["opt_txt"].length; l++){
												var o_txt2=$("<input>").attr({"type": "checkbox", "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "value": qsetting[i]["opt_value"][l], "note": qsetting[i]["note"][l], "skip": qsetting[i]["skip"][l].replace("[","").replace("]",""), "skips": skips, "disjoint": qsetting[i]["disjoint"][l]});
												var o_lbl2=$("<label>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> "+qsetting[i]["opt_txt"][l]).attr({"class": "btn btn-outline-secondary", "style": "font-size: 0.85em", "onClick": "read('"+qsetting[i][`opt_txt`][l]+"')"});
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
										if(window.matchMedia('(max-width: 1200px)').matches){
											var n_q=Math.floor(s_width/25);
											if(qsetting[i]["q_txt"].length>5*n_q+4){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(3*n_q+2, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(4*n_q+3, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(5*n_q+4, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(6*n_q+5, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else if(qsetting[i]["q_txt"].length>3*n_q+2){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(3*n_q+2, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(4*n_q+3, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else if(qsetting[i]["q_txt"].length>n_q){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else{
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}
										}else{
											var n_q=Math.floor(s_width/37.5);
											if(qsetting[i]["q_txt"].length>n_q){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i> <b style='padding: 0.25em; margin-left: -0.25em; margin-right: 0.5em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.85em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else{
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i> <b style='padding: 0.25em; margin-left: -0.25em; margin-right: 0.5em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.85em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}
										}
										var q_lbl=$("<div>").attr({"class": "col-sm-auto"});
										q_txt.appendTo(q_lbl);
										$(".card-body").append(q_lbl);

										if(qsetting[i]["annotate"]!=""){
											if(window.matchMedia('(max-width: 1200px)').matches){
												var n_a=Math.floor(s_width/17.5);
												if(qsetting[i]["annotate"].length>n_a){
													var a_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 2.5% 0 0'></i> <b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"].substr(0, n_a)+"<br>"+qsetting[i]["annotate"].substr(n_a, n_a+1)+"<br>"+qsetting[i]["annotate"].substr(2*n_a+1, n_a+1)+"</b>").attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.95em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`annotate`]+"')"});
												}else{
													var a_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 2.5% 0 0'></i> <b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"].substr(0, n_a)+"</b>").attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.95em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`annotate`]+"')"});
												}
											}else{
												var a_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i> <b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"]+"</b>").attr({"class": "input-group-text", "style": "font-size: 0.75em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`annotate`]+"')"});
											}
											var a_lbl=$("<div>").attr({"class": "col-sm-auto"});
											a_txt.appendTo(a_lbl);
											$(".card-body").append(a_lbl);
										}
										
										if(qsetting[i]["attach"]!=""){
											if(window.matchMedia('(max-width: 1200px)').matches){
												var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 80vmin; padding: 3% 1.5%; margin: auto'></img></center>");
											}else{
												var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 50vmax; padding: 3% 1.5%; margin: auto'></img></center>");
											}
											$(".card-body").append(a_pic);
										}
										
										if(qsetting[i]["random"]!=""){
											var r_list=qsetting[i]["random"].split(",");
											var r_order=Math.floor(Math.random()*r_list.length);
											var r_number=$("<div>").html(r_list[r_order]).attr({"style": "width: 10vmin; height: 10vmin; margin: 2.5vmin auto; padding: 2.5vmin 0; text-align: center; font-size: 2em; color: #FFFFFF; background-color: red; border-radius: 5vmin"});
											$(".card-body").append(r_number);
										}

										var o_txt=$("<input>").attr({"type": "number", "id": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "class": "form-control validateNumber", "min": qsetting[i]["range_min"], "max": qsetting[i]["range_max"], "placeholder": "請輸入數字", "style": "font-size: 0.9em; text-align: center", "required": true});
										var o_lbl=$("<div>").attr({"class": "col-sm-12"});
										o_txt.appendTo(o_lbl);
										$(".card-body").append(o_lbl);
										$(".card-body").append("<div><br></div>");

									//3: 文字題
									}else if(qsetting[i]["type"]==3){
										count++;
										if(window.matchMedia('(max-width: 1200px)').matches){
											var n_q=Math.floor(s_width/25);
											if(qsetting[i]["q_txt"].length>5*n_q+4){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(3*n_q+2, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(4*n_q+3, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(5*n_q+4, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(6*n_q+5, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else if(qsetting[i]["q_txt"].length>3*n_q+2){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(3*n_q+2, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(4*n_q+3, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else if(qsetting[i]["q_txt"].length>n_q){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else{
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}
										}else{
											var n_q=Math.floor(s_width/37.5);
											if(qsetting[i]["q_txt"].length>n_q){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i> <b style='padding: 0.25em; margin-left: -0.25em; margin-right: 0.5em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.85em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else{
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i> <b style='padding: 0.25em; margin-left: -0.25em; margin-right: 0.5em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.85em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}
										}
										var q_lbl=$("<div>").attr({"class": "col-sm-auto"});
										q_txt.appendTo(q_lbl);
										$(".card-body").append(q_lbl);

										if(qsetting[i]["annotate"]!=""){
											if(window.matchMedia('(max-width: 1200px)').matches){
												var n_a=Math.floor(s_width/17.5);
												if(qsetting[i]["annotate"].length>n_a){
													var a_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 2.5% 0 0'></i> <b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"].substr(0, n_a)+"<br>"+qsetting[i]["annotate"].substr(n_a, n_a+1)+"<br>"+qsetting[i]["annotate"].substr(2*n_a+1, n_a+1)+"</b>").attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.95em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`annotate`]+"')"});
												}else{
													var a_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 2.5% 0 0'></i> <b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"].substr(0, n_a)+"</b>").attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.95em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`annotate`]+"')"});
												}
											}else{
												var a_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i> <b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"]+"</b>").attr({"class": "input-group-text", "style": "font-size: 0.75em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`annotate`]+"')"});
											}
											var a_lbl=$("<div>").attr({"class": "col-sm-auto"});
											a_txt.appendTo(a_lbl);
											$(".card-body").append(a_lbl);
										}
										
										if(qsetting[i]["attach"]!=""){
											if(window.matchMedia('(max-width: 1200px)').matches){
												var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 80vmin; padding: 3% 1.5%; margin: auto'></img></center>");
											}else{
												var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 50vmax; padding: 3% 1.5%; margin: auto'></img></center>");
											}
											$(".card-body").append(a_pic);
										}
										
										if(qsetting[i]["random"]!=""){
											var r_list=qsetting[i]["random"].split(",");
											var r_order=Math.floor(Math.random()*r_list.length);
											var r_number=$("<div>").html(r_list[r_order]).attr({"style": "width: 10vmin; height: 10vmin; margin: 2.5vmin auto; padding: 2.5vmin 0; text-align: center; font-size: 2em; color: #FFFFFF; background-color: red; border-radius: 5vmin"});
											$(".card-body").append(r_number);
										}

										var o_txt=$("<textarea>").attr({"type": "text", "id": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "class": "form-control", "placeholder": "請輸入答案", "style": "font-size: 0.9em; text-align: center", "required": true});
										var o_lbl=$("<div>").attr({"class": "col-sm-12"});
										o_txt.appendTo(o_lbl);
										$(".card-body").append(o_lbl);
										$(".card-body").append("<div><br></div>");

									//4. 時間題                   
									}else if(qsetting[i]["type"]==4){
										count++;
										if(window.matchMedia('(max-width: 1200px)').matches){
											var n_q=Math.floor(s_width/25);
											if(qsetting[i]["q_txt"].length>5*n_q+4){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(3*n_q+2, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(4*n_q+3, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(5*n_q+4, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(6*n_q+5, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else if(qsetting[i]["q_txt"].length>3*n_q+2){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(3*n_q+2, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(4*n_q+3, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else if(qsetting[i]["q_txt"].length>n_q){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else{
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> <b style='padding: 0.25em; margin: auto 0.25em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.2em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}
										}else{
											var n_q=Math.floor(s_width/37.5);
											if(qsetting[i]["q_txt"].length>n_q){									
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i> <b style='padding: 0.25em; margin-left: -0.25em; margin-right: 0.5em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q+1)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q+1, n_q+1)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.85em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else{
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i> <b style='padding: 0.25em; margin-left: -0.25em; margin-right: 0.5em; color: #FFFFFF; background-color: #0083AF'>簡答</b>"+(count)+". "+qsetting[i]['q_txt'].substr(0, n_q)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.85em; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}
										}
										var q_lbl=$("<div>").attr({"class": "col-sm-auto"});
										q_txt.appendTo(q_lbl);
										$(".card-body").append(q_lbl);

										if(qsetting[i]["annotate"]!=""){
											if(window.matchMedia('(max-width: 1200px)').matches){
												var n_a=Math.floor(s_width/17.5);
												if(qsetting[i]["annotate"].length>n_a){
													var a_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 2.5% 0 0'></i> <b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"].substr(0, n_a)+"<br>"+qsetting[i]["annotate"].substr(n_a, n_a+1)+"<br>"+qsetting[i]["annotate"].substr(2*n_a+1, n_a+1)+"</b>").attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.95em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`annotate`]+"')"});
												}else{
													var a_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 2.5% 0 0'></i> <b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"].substr(0, n_a)+"</b>").attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 0.95em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`annotate`]+"')"});
												}
											}else{
												var a_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i> <b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"]+"</b>").attr({"class": "input-group-text", "style": "font-size: 0.75em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`annotate`]+"')"});
											}
											var a_lbl=$("<div>").attr({"class": "col-sm-auto"});
											a_txt.appendTo(a_lbl);
											$(".card-body").append(a_lbl);
										}
										
										if(qsetting[i]["attach"]!=""){
											if(window.matchMedia('(max-width: 1200px)').matches){
												var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 80vmin; padding: 3% 1.5%; margin: auto'></img></center>");
											}else{
												var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 50vmax; padding: 3% 1.5%; margin: auto'></img></center>");
											}
											$(".card-body").append(a_pic);
										}
										
										if(qsetting[i]["random"]!=""){
											var r_list=qsetting[i]["random"].split(",");
											var r_order=Math.floor(Math.random()*r_list.length);
											var r_number=$("<div>").html(r_list[r_order]).attr({"style": "width: 10vmin; height: 10vmin; margin: 2.5vmin auto; padding: 2.5vmin 0; text-align: center; font-size: 2em; color: #FFFFFF; background-color: red; border-radius: 5vmin"});
											$(".card-body").append(r_number);
										}

										var o_txt=$("<input>").attr({"type": "time", "id": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "name": qsetting[i]["q_id"]+"-"+qsetting[i]["q_sn"], "class": "form-control", "style": "font-size: 0.9em; text-align: center", "required": true});
										var o_lbl=$("<div>").attr({"class": "col-sm-12"});
										o_txt.appendTo(o_lbl);
										$(".card-body").append(o_lbl);
										$(".card-body").append("<div><br></div>");

									//9. 題目區段
									}else if(qsetting[i]["type"]==9){
										if(window.matchMedia('(max-width: 1200px)').matches){
											var n_q=Math.floor(s_width/20);
											if(qsetting[i]["q_txt"].length>3*n_q){
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 2.5% 0 0'></i> <b>"+qsetting[i]["q_txt"].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q, n_q)+"<br>"+qsetting[i]["q_txt"].substr(3*n_q, n_q)+"<br>"+qsetting[i]["q_txt"].substr(4*n_q, n_q)+"</b>").attr({"class": "input-group-text", "style": "margin-left: -0.25em; line-height: 1.5em; font-size: 1.2em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else if(qsetting[i]["q_txt"].length>n_q){
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 2.5% 0 0'></i> <b>"+qsetting[i]["q_txt"].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q, n_q)+"</b>").attr({"class": "input-group-text", "style": "margin-left: -0.25em; line-height: 1.5em; font-size: 1.2em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else{
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 2.5% 0 0'></i> <b>"+qsetting[i]["q_txt"].substr(0, n_q)+"</b>").attr({"class": "input-group-text", "style": "margin-left: -0.25em; line-height: 1.5em; font-size: 1.2em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}
										}else{
											var n_q=Math.floor(s_width/40);
											if(qsetting[i]["q_txt"].length>n_q){
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i> <b>"+qsetting[i]["q_txt"].substr(0, n_q)+"<br>"+qsetting[i]["q_txt"].substr(n_q, n_q)+"<br>"+qsetting[i]["q_txt"].substr(2*n_q, n_q)+"</b>").attr({"class": "input-group-text", "style": "margin-left: -0.5em; font-size: 1em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}else{
												var q_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i> <b>"+qsetting[i]["q_txt"].substr(0, n_q)+"</b>").attr({"class": "input-group-text", "style": "margin-left: -0.5em; font-size: 1em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`q_txt`]+"')"});
											}
										}
										var q_lbl=$("<div>").attr({"class": "col-sm-auto", "style": "background-color: #FFFFBB"});
										q_txt.appendTo(q_lbl);
										$(".card-body").append(q_lbl);

										if(qsetting[i]["annotate"]!=""){
											if(window.matchMedia('(max-width: 1200px)').matches){
												var n_a=Math.floor(s_width/17.5);
												if(qsetting[i]["annotate"].length>n_a){
													var a_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i>"+qsetting[i]["annotate"].substr(0, n_a)+"<br>"+qsetting[i]["annotate"].substr(n_a, n_a)+"<br>"+qsetting[i]["annotate"].substr(2*n_a, n_a)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.05em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`annotate`]+"')"});
												}else{
													var a_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 1.5% 0 0'></i> <b><i class='fa fa-info-circle'></i> "+qsetting[i]["annotate"].substr(0, n_a)).attr({"class": "input-group-text", "style": "line-height: 1.75em; font-size: 1.05em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`annotate`]+"')"});
												}
											}else{
												var a_txt=$("<div>").html("<i class='fa fa-volume-up' style='padding: 0.375% 0.75% 0 0'></i> "+qsetting[i]["annotate"]).attr({"class": "input-group-text", "style": "font-size: 0.85em; background-color: #FFFFBB; border: none; text-align: left", "onClick": "read('"+qsetting[i][`annotate`]+"')"});
											}
											var a_lbl=$("<div>").attr({"class": "col-sm-auto"});
											a_txt.appendTo(a_lbl);
											$(".card-body").append(a_lbl);
										}
										
										if(qsetting[i]["attach"]!=""){
											if(window.matchMedia('(max-width: 1200px)').matches){
												var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 80vmin; padding: 3% 1.5%; margin: auto'></img></center>");
											}else{
												var a_pic=$("<div>").html("<center><img src='"+qsetting[i]["attach"]+"' style='width: 50vmax; padding: 3% 1.5%; margin: auto'></img></center>");
											}
											$(".card-body").append(a_pic);
										}
										$(".card-body").append("<p></p>");
									}
                                }
								$(".question_n").empty().append(count);
								
								if(window.matchMedia('(max-width: 1200px)').matches){
									$(".btn-group-toggle").removeClass('btn-group').addClass('btn-group-vertical').attr({"style": "font-size: 1.25em"});
									$(".btn-group-toggle").removeClass('col-sm-6').addClass('col-sm-12').attr({"style": "font-size: 1.25em"});
								}
                            }, error: function(e){
								console.log(e);
							}
						})
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
                        
                        console.log(skip_q1);
                        
                        if(type_q1=="radio"|type_q1=="checkbox"){
                            $("input[name="+skip_q1+"]").prop("checked", false).parent().removeClass("active");                            
                            $("input[name="+skip_q1+"]").parent().parent().append("<input type='radio' name="+skip_q1+" value='99' style='display: none' checked>");
                            $("input[name="+skip_q1+"-"+value_q1+"]").val("99").hide();
                            $("div[id="+skip_q1+"]").hide();
                            var element_q1=document.getElementById(skip_q1);
                            if(window.matchMedia('(max-width: 1200px)').matches){
                                element_q1.insertAdjacentHTML("afterend", "<div id="+skip_q1+skip_q1+" style='padding-top: 2.25em; padding-bottom: 1.25em; text-align: center'><b style='padding: 2.25%; font-size: 1.5em; color: #EF475D; background-color: yellow'>本題不需作答</b></div>");
                            }else{
                                element_q1.insertAdjacentHTML("afterend", "<div id="+skip_q1+skip_q1+" style='padding-top: 1em; text-align: center'><b style='padding: 1%; color: #EF475D; background-color: yellow'>本題不需作答</b></div>");
                            }
                        }else{
                            $("input[name="+skip_q1+"]").val("99").hide();
                            var element_q1=document.getElementById(skip_q1);
                            if(window.matchMedia('(max-width: 1200px)').matches){
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
                            if(window.matchMedia('(max-width: 1200px)').matches){
                                element_q1.insertAdjacentHTML("afterend", "<div id="+skip_q1+skip_q1+" style='padding-top: 2.25em; padding-bottom: 1.25em; text-align: center'><b style='padding: 2.25%; font-size: 1.5em; color: #EF475D; background-color: yellow'>本題不需作答</b></div>");
                            }else{
                                element_q1.insertAdjacentHTML("afterend", "<div id="+skip_q1+skip_q1+" style='padding-top: 1em; text-align: center'><b style='padding: 1%; color: #EF475D; background-color: yellow'>本題不需作答</b></div>");
                            }
                        }else{
                            $("input[name="+skip_q1+"]").val("99").hide();
                            var element_q1=document.getElementById(skip_q1);
                            if(window.matchMedia('(max-width: 1200px)').matches){
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
					
            $("#finalForm").on("submit", function(){
                event.preventDefault();
                $("#submit").attr("disabled", true);
                var ans=$('#finalForm').serializeObject();

                $.ajax({ 
                    type: "POST",
                    url: "",
                    data: {answer: 1, ans: ans},
                    success: function(data){ 
                        console.log(data);
						$("#success").modal("show");
						setTimeout("window.location.href='./thankyou.php'", 5000);
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
		<a class="navbar-brand" href="./main.php" style="margin: 0.25em; background-color: #FFFFFF; border-radius: 10px">
            <img class="logo" src="./pic/KIT_logo.jpeg"><b style="padding: 0 0.75em 0 0.25em; color: #2E317C">幼兒點日記</b>
		</a>
	</nav>
	
	<div id="success" class="modal">
        <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="font-size: 1.25em; background-color: yellow">⭐ 作答完成 ⭐</h5>
            </div>
                
            <div class="modal-body" style="text-align: center; font-size: 1.1em">
				上次已累積點數：<b><span class="accumulate"></span></b><br><br>
				本次新增點數：<b><span class="project_points"></span></b><br><br>
            </div>
        </div>
        </div>
    </div>
    
    <div class="wrap">
        <img id="title" src="./pic/square.png">
        <div class="title"><b>專案問卷</b></div>
    </div>
	
	<div class="container">
		<div class="infobar">
            <b><span style="color: #FFFFFF; background-color: #813C85; padding: 0.5em; border-radius: 10px 0px 0px 10px">專案名稱</span><span style="background-color: #F2E7E5; padding: 0.5em; border-radius: 0px 10px 10px 0px"><span class="project_name"></span></span></b>
            <b><span style="color: #FFFFFF; background-color: #F25A47; padding: 0.5em; border-radius: 10px 0px 0px 10px">專案題數</span><span style="background-color: #F2E7E5; padding: 0.5em; border-radius: 0px 10px 10px 0px"><span class="question_n"></span></span></b>
		</div>
		
        <div class="card">
			<label style="padding: 2.5% 7.5%"><input type="checkbox" id="read" checked="checked" skips="0"> <b>幫我念題目</b></label>
			<form id="finalForm">
            <div class="card-body">
			<p style="font-size: 0.9em">
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
	
	<script>
        function read(word){
			var setting=document.getElementById("read");
			
			if(setting.checked==true){
				var words=new SpeechSynthesisUtterance(word);
				window.speechSynthesis.speak(words);
			}
        }
	</script>
</body>
</html>
