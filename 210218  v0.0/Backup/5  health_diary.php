<?php 
	session_start();
	include("db.php"); 
	if ($_SESSION['id']==null) {
		header("Location:login.html");
		
	} 
	date_default_timezone_set("Asia/Taipei");
	$today=date("Y-m-d"); 
	$yesterday=date("Y-m-d",strtotime("-1 day"));
	date("m/d");
	$id = $_SESSION['id'];

	// Query Dynamic Posts
	$sql_query = "SELECT * FROM `postlist` 
				  WHERE (receiver='all' or FIND_IN_SET(:id,receiver) > 0) 
				  AND start_time<=:today 
				  AND end_time>=:today  
				  ORDER BY post_id"; 
	$stmt=$db->prepare($sql_query);
	$stmt->bindParam(':id',$_SESSION['id']);
	$stmt->bindParam(':today',$today);
	$stmt->execute();
	$n_post = $stmt->rowCount();
	$post_arr = array();
	while ( $rs = $stmt->fetch(PDO::FETCH_ASSOC)) {
		// echo $rs['post_id'];
		// $tmp = json_decode($rs['post_id'],true);
		$all_post_content[] = $rs;
		array_push($post_arr, $rs['post_id']);
		
	}
	
	// Query Responsed Dynamic Posts
	$sql2  = "SELECT post_response FROM `hdiary` WHERE id=:v1 and post_response != 'NULL' ";
	$stmt2 = $db->prepare($sql2);
	$stmt2->bindParam(':v1',$id);
	$stmt2->execute();
	$responsed_post_arr = array();
	while ( $rs2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
		$tmp2 = json_decode($rs2['post_response'],true);
		array_push($responsed_post_arr, $tmp2['post_id']);
		
	}
	
	// Display Non-Responsed Post
	$remain_arr = array_values(array_diff($post_arr, $responsed_post_arr));		// reset index of array_diff results
	
	if(!empty($remain_arr)){
		// echo $remain_arr[0];   // 最優先顯示的post_id
		foreach( $all_post_content as $key => $value){
			if ( $all_post_content[$key]['post_id'] == $remain_arr[0] ){
				$query_result = 1;
				$curr_post_id = $remain_arr[0];
				$curr_title   = $all_post_content[$key]['title'];
				$curr_content = $all_post_content[$key]['content'];
				$question_content = array_filter(json_decode($all_post_content[$key]['question']));
			}
		}
	}else{
		$rs=[];
		$rs['post_id']	= 0;
		$rs['title']	= "";
		$rs['content']	= "";
		$curr_post_id = 0;
		$question_content = "";
		$query_result=0;
	}

	// if( $n_post>0 ){
	// 	$rs = $stmt->fetch(PDO::FETCH_ASSOC);
	// 	$query_result = 1;
	// 	$question_content = array_filter(json_decode($rs['question']));
		
	// 	if( in_array($rs['post_id'], $responsed_post_arr) ){
	// 		$rs['post_id']	= 0;
	// 		$rs['title']	= "";
	// 		$rs['content']	= "";
	// 		$question_content = "";
	// 		$query_result=0;
	// 	}
		
	// }else{
	// 	$rs=[];
	// 	$rs['post_id']	= 0;
	// 	$rs['title']	= "";
	// 	$rs['content']	= "";
	// 	$question_content = "";
	// 	$query_result=0;
		
	// }
	
	// 是否修改過profile_v2
	// $sql_query1="SELECT * FROM `profile_v2` WHERE id=:v1";
	// $stmt1=$db->prepare($sql_query1);
	// $stmt1->bindParam(':v1',$_SESSION['id']);
	// $stmt1->execute();
	// $n1 = $stmt1->rowCount();
	// if($n1==0){
	// 	echo '<script language="javascript" type="text/javascript"> 
 //                alert("請先修改個人資料中的職業、教育程度、婚姻狀況");
 //                window.location = "http://cdiary2.tw/edit_myprofile.php";
	// 		  </script>';

	// }
	
?>
<!doctype html>
<html lang="zh-tw">
<head>
	<title>健康日記</title>
	<meta http-equiv="Content-Type" content="text/html"  charset="utf-8">
	<meta http-equiv="cache-control" content="no-cache">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="https://code.jquery.com/jquery-3.1.1.js"></script>
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<script src="js/bootstrap.min.js"></script>
	<!--<script type="text/javascript" src="./js/Options.js"></script>-->
	
	<script type="text/javascript">	// 選項、題目延展、動態題
		$(document).ready(function(){	// 勾選是否運動後，跳出運動強度的問題
		$("#sport1").click(function(){  		// click在label上，非button
	        	$("#sp1_hide").show();
	        	$("#sp2_hide").show();
	        	$("#sp3_hide").show();
		});
		$("#sport0").click(function(){  		// click在label上，非button
	        	$("#sp1_hide").hide();
	        	$("#sp2_hide").hide();
	        	$("#sp3_hide").hide();
	        	
	        	$("#sportlight_lbl").prop("checked", false); 
				$("#sportlight_btn").prop("checked", false); // 清除checkbox值
	        	$('#sp1_hide').find('label').removeClass('active');
	        	$("#sportlighttime_sel").hide();
				
	        	$("#sportmoderate_lbl").prop("checked", false);
				$("#sportmoderate_btn").prop("checked", false);
	        	$('#sp2_hide').find('label').removeClass('active');
	        	$("#sportmoderatetime_sel").hide();
				
				$("#sportvigorous_lbl").prop("checked", false);
				$("#sportvigorous_btn").prop("checked", false);
	        	$('#sp3_hide').find('label').removeClass('active');
	        	$("#sportvigoroustime_sel").hide();
				
				
				
				$("#sportlighttime_opt0").prop("selected",true);
				$("#sportmoderatetime_opt0").prop("selected",true);
				$("#sportvigoroustime_opt0").prop("selected",true);
		});
		});
		
		$(document).ready(function(){	// 勾選運動強度後，跳出運動時間的問題
			$("#sportlight_btn").click(function(){  // click在button(checkbox)上
	        	$("#sportlighttime_sel").toggle();
	        	if(!$(this).is(':checked'))			//若將此checkbox取消勾選，選項跳回請選擇，回傳value 0
	        	$("#sportlighttime_opt0").prop("selected",true);
			});
			$("#sportmoderate_btn").click(function(){  
	        	$("#sportmoderatetime_sel").toggle();
	        	if(!$(this).is(':checked'))			
	        	$("#sportmoderatetime_opt0").prop("selected",true);
			});
			$("#sportvigorous_btn").click(function(){  
	        	$("#sportvigoroustime_sel").toggle();
	        	if(!$(this).is(':checked'))			
	        	$("#sportvigoroustime_opt0").prop("selected",true);
			});
		});
		

		$(document).ready(function(){	// 症狀題目展延
			$("#symptom1").click(function(){  		// click在label上，非button
		        	$("#symptom_hide").show();
		        	
			});
			$("#symptom0").click(function(){  					// click在label上，非button
	        	$("#symptom_hide").hide();					// 隱藏症狀按鈕
	        	$(".lbl-symptom").prop("checked", false);	//清除已勾選的按鈕
				$(".btn-symptom").prop("checked", false);	//清除已勾選的按鈕
				
	        	$('#symptom_hide').find('label').removeClass('active');
			});
		});
		
		
		$(document).ready(function(){	// 壓力題目展延
			$(".stressT").click(function(){  		// click在label上，非button
		        	$("#stress_hide").show();
		        	
			});
			$(".stressF").click(function(){  				// click在label上，非button
	        	$("#stress_hide").hide();					// 隱藏壓力來源選項
	        	$(".lbl-stress").prop("checked", false);	// 清除已勾選的按鈕
				$(".btn-stress").prop("checked", false);	// 清除已勾選的按鈕
				$('#stress_hide').find('label').removeClass('active');	// 清除已勾選的按鈕
			});
		});
		
		
		
		$(document).ready(function(){	// 動態題目設定
			 query_result =<?php echo $query_result; ?>;
			 console.log(query_result)
			if (query_result==1){	// 有query符合條件的post
				
				var title	="<?php echo $curr_title; ?>";
				var content	="<?php echo $curr_content ?>";
				$('#dynamic_div').append(
					$('<div>').attr({'class':'dynamic_title'}).html(title)
				).append(
					$('<div>').attr({'class':'dynamic_content'}).html(content)
				).append($('<hr>'));
				
				<?="question_content=".json_encode($question_content,256).";";?>
				// console.log(question_content.length,question_content);
				for(var i=0;i<question_content.length;i++){ // 第i題
					// console.log(question_content[i]['type']); // 0:信任題，1:分享題，2:新增單選，3:新增多選，4:新增簡答
					
					if( question_content[i]['type']==0 || question_content[i]['type']==1 || question_content[i]['type']==2 ){ // radio
						var q_text= $('<div>').attr({'class':'dynamic_qtext'}).html(question_content[i]['text']);
						var btndiv= $('<div>').attr({class:'btn-group','data-toggle':'buttons'});
						for(var j=0;j<question_content[i]['opts'].length;j++){  
							// console.log(j);
							// console.log(question_content[i]['opts'][j]); 	// 第i題的第j個選項
							var opts=$('<input>').attr({'class':'','type':'radio','name':'q'+i+'_opts','value':j,required:true});//.html(data2[i].opt[0][j]);
							btndiv.append( $('<label>').html(question_content[i]['opts'][j]['opt'+j]).attr({class:'btn btn-default  dynamic_radio_opt '}).prepend(opts))
						}
						$('#dynamic_div').append(q_text).append(btndiv);
					}else if( question_content[i]['type']==3 ){ // checkbox
						console.log(question_content[i]['type']);
						var q_text=$('<div>').attr({'class':'dynamic_qtext'}).html(question_content[i]['text']);
						var btndiv=$('<div>');
						for(var j=0;j<question_content[i]['opts'].length;j++){  
							// console.log(j);
							// console.log(question_content[i]['opts'][j]); 	// 第i題的第j個選項
							var opts=$('<input>').attr({'class':'','type':'checkbox','name':'q'+i+'_opt'+j});
							btndiv.append( $('<label>').attr({'class':'q_checkbox_lbl'}).html(question_content[i]['opts'][j]['opt'+j]).prepend(opts));
						}
						$('#dynamic_div').append(q_text).append(btndiv);
					}else if( question_content[i]['type']==4 ){ // 簡答題
						var q_text=$('<div>').attr({'class':'dynamic_qtext'}).html(question_content[i]['text']);
						$('#dynamic_div').append(q_text).append(
							$('<div>').append(
								$('<input>').attr({'class':'q_textarea','type':'text','name':'q'+i+'_textarea','style':'height:5em;'})
							)
						);
						
					}
					
				} 
				
			}
			
			/*
			$.ajax({ 
				type: "POST",
				//async: false,
				dataType: "json", 
				url: 'db_query_dynamic_content.php',
				data: {//js變數:php變數
					
				},success: function(data){
					
					// 要show在畫面上的有title,content,	question,opt					
					console.log(data);
					console.log(data.post_id);
					console.log(data.type);			// 新聞時事,Innovation
					console.log(data.receiver);
					console.log(data.title);
					console.log(data.content);
					console.log(data.question);
					
					var data2=JSON.parse(data.question);
					//console.log(data2);
					
					var title	=$('<div>').attr({'class':''}).html(data.title);
					var content	=$('<div>').attr({'class':''}).html(data.content);
					
					// 語法1
					$('#dynamic_div').append(title).append(content).append($('<hr>'));
					
					// 語法2，2者結果一樣
					//title.appendTo('#dynamic_div'); 
					//content.appendTo('#dynamic_div'); 
					
					console.log(data2);
					console.log(data2.length);	// number of questions
					for (var i in data2){
						console.log(data2[i].question);  			// 第i題的題目
						console.log(data2[i].type);				// radio,checkbox,textarea
						if(data2[i].type=="radio"){
							var questions=$('<div>').attr({'class':'dynamic_question_div'}).html(data2[i].question);
							var btndiv=$('<div>').attr({class:'btn-group','data-toggle':'buttons'});
							
							for(var j in data2[i].opt[0]){  
								console.log(j);
								console.log(data2[i].opt[0][j]); 	// 第i題的第j個選項
								var opts=$('<input>').attr({'class':'','type':data2[i].type,'name':'q'+i+'_opts',required:true});//.html(data2[i].opt[0][j]);
									
								
								btndiv.append( $('<label>').html(data2[i].opt[0][j]).attr({class:'btn btn-default dynamic_radio_opt '}).prepend(opts ))
								//questions.append(btndiv);
								
							}
							//questions.appendTo('#dynamic_div'); 	
							$('#dynamic_div').append(questions).append(btndiv);
						}else if(data2[i].type=="checkbox"){
							var questions=$('<div>').attr({'class':'dynamic_question_div'}).html(data2[i].question);
							var btndiv=$('<div>');//.attr({class:'btn-group','data-toggle':'buttons'});
							for(var j in data2[i].opt[0]){  
								var opts=$('<input>').attr({'class':'','type':data2[i].type,'name':'q'+i+'_'+j});
								btndiv.append( $('<label>').html(data2[i].opt[0][j]).prepend(opts ));
								questions.append(btndiv);
							}
							questions.appendTo('#dynamic_div'); 	
							//<label  for="sick" class="lbl-symptom-left" > <input id="sick"	class="btn-symptom" type="checkbox" name="sick" value="1">確定有感冒</label>
						
						}else if(data2[i].type=="textarea"){
							var questions=$('<div>').attr({'class':'dynamic_question_div'}).html(data2[i].question);
							var btndiv	=$('<div>');
							var opts	=$('<textarea>').attr({'rows':5,'class':'form-control'});
							btndiv.append(opts);
							questions.append(btndiv);
							questions.appendTo('#dynamic_div'); 
						
						}
					}
					
					
					
					
				
					
				},error: function(e){
					console.log(e);
					
				}
			});
			*/
		});
		
		
	</script>
	<script type="text/javascript">	// 傳值進DB
		$(document).ready(function(){
			$("#form").on("submit", function (event) {
			event.preventDefault();
			var date	=$("select[id='date']").val(); 
			var getup_h	=$("select[id='getup-h']").val(); 	// 起床時間
			var getup_m	=$("select[id='getup-m']").val();   // 起床時間
			var gobed_h	=$("select[id='gobed-h']").val();   // 睡了多久
			var gobed_m	=$("select[id='gobed-m']").val();   // 睡了多久
			var sleep	=$("input[name='sleep']:checked").val();
			console.log(getup_h)
			var mood	=$("input[name='mood']:checked").val(); 
			
			/*
			// 壓力，需validate
			var stress=$("input[name='stress']:checked").val(); 
			var stress_work	=$("input[name='stress_work']").is(':checked')? 1: 0;
			var stress_social=$("input[name='stress_social']").is(':checked')? 1: 0;
			var stress_family=$("input[name='stress_family']").is(':checked')? 1: 0;
			var stress_self_expectation=$("input[name='stress_self_expectation']").is(':checked')? 1: 0;
			var stress_life_event=$("input[name='stress_life_event']").is(':checked')? 1: 0;
			var stress_sum=stress_work+stress_social+stress_family+stress_self_expectation+stress_life_event;
			*/

			// 飲食
			var cereal	=$("input[name='cereal']:checked").val(); 
			var vegetable	=$("input[name='vegetable']:checked").val(); 
			var fruit	=$("input[name='fruit']:checked").val(); 
			var meat	=$("input[name='meat']:checked").val(); 
			var seafood	=$("input[name='seafood']:checked").val(); 
			var bean	=$("input[name='bean']:checked").val(); 
			var egg		=$("input[name='egg']:checked").val(); 
			var milk	=$("input[name='milk']:checked").val(); 
			var fried	=$("input[name='fried']:checked").val(); 
			var sweet	=$("input[name='sweet']:checked").val(); 
			
			var sport	=$("input[name='sport']:checked").val(); 
			// 運動強度,用於validation
			var sportlight=$("input[name='sportlight']:checked").length
			var sportmoderate=$("input[name='sportmoderate']:checked").length
			var sportvigorous=$("input[name='sportvigorous']:checked").length
			var sporttype=sportlight+sportmoderate+sportvigorous;
			// 運動時間，直接從選單獲取值，不須獲取checkbox是否被勾選
			var sportlighttime		=$("select[name='sportlighttime']").val(); 
			var sportmoderatetime	=$("select[name='sportmoderatetime']").val(); 
			var sportvigoroustime	=$("select[name='sportvigoroustime']").val(); 
			
			var symptom=$("input[name='symptom']:checked").val(); 
			// checkbox 有勾回傳1，沒勾回傳0
			var sick		=$("input[name='sick']").is(':checked')? 1: 0;
			var fever		=$("input[name='fever']").is(':checked')? 1: 0;
			var cough		=$("input[name='cough']").is(':checked')? 1: 0;
			var sorethroat	=$("input[name='sorethroat']").is(':checked')? 1: 0;
			var hospital	=$("input[name='hospital']").is(':checked')? 1: 0;
			var symptom_other	=$("input[name='symptom_other']").is(':checked')? 1: 0;
			var symptom_other_text	=$("input[name='symptom_other_text']").val();
			var symptom_sum =sick+fever+cough+sorethroat+hospital+symptom_other;
			// var runnynose	=$("input[name='runnynose']").is(':checked')? 1: 0;
			// var stuffynose	=$("input[name='stuffynose']").is(':checked')? 1: 0;
			// var chills		=$("input[name='chills']").is(':checked')? 1: 0;
			// var tiredness	=$("input[name='tiredness']").is(':checked')? 1: 0;
			// var chesttightness	=$("input[name='chesttightness']").is(':checked')? 1: 0;
			// var headache	=$("input[name='headache']").is(':checked')? 1: 0;
			// var mask		=$("input[name='mask']").is(':checked')? 1: 0;
			// var symptom_sum =sick+fever+cough+sorethroat+runnynose+stuffynose+chills+tiredness+chesttightness+headache+mask;
			
			//var touchpeople	=$("select[id='touchpeople']").val(); 
			var touchpeople	=$("input[name='touchpeople']:checked").val(); 
			
			query_result =<?php echo $query_result; ?>;

			if (query_result==1){
				
				var post_id = <?php echo $curr_post_id; ?>;
				console.log(post_id);
				console.log(question_content);
				post_response={};
				ans_items=[];
				var ans_items_key={};
				for(var n=0;n<question_content.length;n++){
					// var ans_items_key={};
					if (question_content[n]['type']==0 || question_content[n]['type']==1 ||question_content[n]['type']==2){ // 取radio值
						ans_items_key["ans"+n]=$("input[name=q"+n+"_opts]:checked").val();
					}else if(question_content[n]['type']==3){ // 取checkbox
						opt_items=[];
						var opt_items_key={};
						for(var n2=0;n2<question_content[n]['opts'].length;n2++){  
							opt_items_key["opt"+n2]=$("input[name=q"+n+"_opt"+n2+"]").is(':checked')? 1: 0;
						}
						opt_items.push(opt_items_key);
						ans_items_key["ans"+n]=opt_items;
					}else if(question_content[n]['type']==4){ // 取簡答
						ans_items_key["ans"+n]=$("input[name=q"+n+"_textarea]").val();
					}
				}
				ans_items.push(ans_items_key);
				post_response["post_id"]=post_id;
				post_response["response"]=ans_items;
				// console.log(post_response);
			}else if (query_result==0){	// 沒有符合條件的動態題目
				post_response=[];
			}
			// console.log(sleep);
			// console.log(date,getup_h,getup_m,gobed_h,gobed_m,sleep,mood,cereal,vegetable,fruit,meat,seafood,bean,egg,milk,fried,sweet);
			// console.log(sport,sportlight,sportmoderate,sportvigorous);
			// console.log(sportlighttime,sportmoderatetime,sportvigoroustime);
			// console.log(symptom,sick,fever,cough,sorethroat,runnynose,stuffynose,chills,tiredness,chesttightness,headache,touchpeople);
			 // console.log(sport,sporttype);
			 // console.log(symptom_sum);
			if(getup_h=="請選擇"){
				alert("請選擇起床時間");
				return false;
			}else if(gobed_h=="請選擇"){
				alert("請選擇睡了多久");
				return false;
			}else if (sleep==undefined){
				alert("請選擇睡眠品質");
				return false;
			}else if (mood==undefined){
				alert("當天心情如何");
				return false;
			}
			/*else if (stress==undefined){
				alert("當天感覺有壓力嗎");
				return false;
			}else if (stress==1 && stress_sum==0){
				alert("請選擇壓力來源");
				return false;
			}
			*/
			 else if (sport==undefined){
				alert("當天有沒有運動");
				return false;
			}else if (symptom==undefined){
				alert("當天有沒有身體不適");
				return false;
			}else if(sport==1 && sporttype==0){
				alert("請選擇運動強度和時間");
				return false;
			}else if(sportlight==1 && sportlighttime==0){
				alert("請選擇輕度運動時間");
				return false;
			}else if(sportmoderate==1 && sportmoderatetime==0){
				alert("請選擇中度運動時間");
				return false;
			}else if(sportvigorous==1 && sportvigoroustime==0){
				alert("請選擇強度運動時間");
				return false;
			}else if(symptom==1 && symptom_sum==0){
				alert("請選擇症狀");
				return false;
			}else if(touchpeople==undefined){
				alert("當天大概接觸多少人呢");
				return false;
			}else{
				
				$.ajax({ 
					type: "POST",
					async: false,
					// dataType: "json", 
					url: 'db_healthdiary.php',
					data: {//js變數:php變數
						date:date,
						getup_h:getup_h,
						getup_m:getup_m,
						gobed_h:gobed_h,
						gobed_m:gobed_m,
						sleep:sleep,
						mood:mood,
						// stress:stress,
						// stress_work:stress_work,
						// stress_social:stress_social,
						// stress_family:stress_family,
						// stress_self_expectation:stress_self_expectation,
						// stress_life_event:stress_life_event,
						cereal:cereal,
						vegetable:vegetable,
						fruit:fruit,
						meat:meat,
						seafood:seafood,
						bean:bean,
						egg:egg,
						milk:milk,
						fried:fried,
						sweet:sweet,
						sport:sport,
						sportlighttime:sportlighttime,
						sportmoderatetime:sportmoderatetime,
						sportvigoroustime:sportvigoroustime,
						symptom:symptom,
						sick:sick,
						fever:fever,
						cough:cough,
						sorethroat:sorethroat,
						hospital:hospital,
						symptom_other:symptom_other,
						symptom_other_text:symptom_other_text,
						// runnynose:runnynose,
						// stuffynose:stuffynose,
						// chills:chills,
						// tiredness:tiredness,
						// chesttightness:chesttightness,
						// headache:headache,
						// mask:mask,
						touchpeople:touchpeople,
						post_response:post_response
					},beforeSend: function() { 
						$("#loadingtext").show();
						$("#submit").prop('disabled', true); // disable button
					},complete : function (){
						$("#loadingtext").hide();
						$("#submit").prop('disabled', false); // disable button
					},success: function(data){
						if(data==1){
							alert("已填寫過這天的健康日記，請勿重複填寫");
						}else{
							//alert("填寫完成!!");
							if (confirm('健康日記已填寫完成，要填寫接觸日記嗎?')){
								window.location.href='main_touchdiary.php';
							}else{
								window.location.href='main.php';
							}
						}
					},
					error: 	 function(data){
						alert("請再確認是否有題目漏填，若仍無法成功填寫請按右上方的聯絡我們");
						
					}
				});
				
			}
			
			
			});
		});
	</script>

	<script type="text/javascript"> 
		// log date
		$(document).ready(function(){	
			var datetemp = $("select[id='date']").val(); 
			// 當天
			var date_selected = new Date(datetemp).toISOString();
			console.log(date_selected);
			var date_selected_reformat = date_selected.substring(5, 7)+'/'+datetemp.substring(8,10);
			// 前一晚
			var d_temp = new Date(datetemp);
			d_temp.setDate(d_temp.getDate() - 1);
			var date_selected_minus = ( d_temp.getMonth()+1 )+'/'+d_temp.getDate();
			
			$('.changebydate').html(date_selected_reformat);
			$('.changebydate2').html(date_selected_minus);

			//document.getElementsByClassName('changebydate')[0].textContent=date_selected_reformat;
			//document.getElementsByClassName("changebydate2").textContent=date_selected_minus;

			$("#date").on('change',function(){
				var datetemp = $("select[id='date']").val(); 
				var date_selected = new Date(datetemp).toISOString();
				var date_selected_reformat = date_selected.substring(6, 7)+'/'+datetemp.substring(8,10);
				var d_temp = new Date(datetemp);
				d_temp.setDate(d_temp.getDate() - 1);
				var date_selected_minus = ( d_temp.getMonth()+1 )+'/'+d_temp.getDate();
				
				$('.changebydate').html(date_selected_reformat);
				$('.changebydate2').html(date_selected_minus);
				//document.getElementsByClassName("changebydate").textContent=date_selected_reformat;
				//document.getElementsByClassName("changebydate2").textContent=date_selected_minus;
			});
			
			
			$("input[id='symptom_other']").on("click", function (event) {
			var symptom_other	=$("input[id='symptom_other']").is(':checked')? 1: 0;
			if (symptom_other==1){
				//$("#relationship").attr({'style':'width:30%'});
				//$("#relationship_other").attr({'style':'width:60%'});
				$("input[id='symptom_other_text']").show();
				//$("input[id='symptom_other_text']").attr({required:'true'});
				
			}else{
				//$("#relationship").attr({'style':'width:60%'});
				$("input[id='symptom_other_text']").hide();
				//$("input[id='symptom_other_text']").removeAttr('required');
				$("input[id='symptom_other_text']").val('');    

			}
		});
			
		});
	</script>
	<style type="text/css">
			html {
			  position: relative;
			  min-height: 100%;
			}
			body {
			  /*Avoid nav bar overlap web content*/
			  padding-top: 70px; 
			  /* Margin bottom by footer height ，avoid footer overlap web content*/
			  margin-bottom: 60px;
			}
			.footer {
			  position: absolute;
			  bottom: 0;
			  width: 100%;
			  /* Set the fixed height of the footer here */
			  /*height: 60px;*/
			  /*line-height: 60px; */
			  /* Vertically center the text there */
			  background-color: #f5f5f5;
			  
			}
			.text{
				display: table-cell;
			    vertical-align: middle;
			    /*height: 100%;*/
			    font-size: 0.8em;
			    padding-top: 0.5em
			}
			#footerimg{
				float: left;
				height: 3em;
				padding-top: 0.5em;
			}
			#getup-h,#getup-m,#gobed-h,#gobed-m{
				width: 40%;
			}
			.form-control{
				display: inline;
				width: 80%;
			}
			/*控制button顏色 */
			.btn-primary { 
			    color: #3084cc;
			    background-color: white; 
			    border-color: #2e6da4;

			}
			.btn-success { 
			    color: #3c763d;
			    background-color: white; 
			    border-color: #4cae4c;

			}
			
			
			/* hover doesn't perform well on mobile*/
			.btn-success:hover{
				
			}
			
			
			
			
			
			/* 按鈕被點選時的顏色變化*/
			.btn-primary:hover, .btn-primary:focus, .btn-primary.focus, .btn-primary:active, .btn-primary.active, .open>.dropdown-toggle.btn-primary { 
			    color: #fff;
			    background-color: /*#286090;*/ #3084cc;
			    border-color: #204d74;

			}
			.btn-default:hover, .btn-default:focus, .btn-default.focus, .btn-default:active, .btn-default.active{
				color: #fff;
				background-color: #888888;
			}
		
			label{font-weight: 400}     /* 字體不加粗 */
			.btn {        
		    padding: 0.5em 0;
		    margin-bottom: 0;
		    font-size: 0.9em;
		    font-weight: 400;
		    line-height: 1.42857143;
		    width: 20%;   /* Radio button的寬度 */
		    
			}
			
			.btn-group-vertical{

			}
			/*先設btn-group為100%，再用.btn控制width*/
			.btn-group{
				width: 100%
			}

			.panel-success {
			    border-color: #4cae4c;
			}
			.panel-success>.panel-heading {
			    color: white;/*#3c763d;*/
			    background-color: /*#dff0d8;*/ #4cae4c;
			    border-color: #d6e9c6;
			}
			
			/*壓力*/
			#stress_hide{
				display:none;
			}
			.lbl-stress{
				width:45%;
			}
			/*運動相關文提、選項*/
			#sp1_hide,#sp2_hide,#sp3_hide{
				display: none
			}
			#sportlight_lbl,#sportlighttime_sel,#sportmoderate_lbl,#sportmoderatetime_sel,#sportvigorous_lbl,#sportvigoroustime_sel{
				width: 100%;
			}
			#sportlighttime_sel,#sportmoderatetime_sel,#sportvigoroustime_sel{/*先將選單隱藏，點checkbox label才顯示 */
				display: none;
			}
			
			/*症狀按鈕的外觀控制*/
			.lbl-symptom{
				width: 49%;
				font-size:0.95em;
			}
			.lbl-symptom-left{
				width: 37%;
			}
			.lbl-symptom-right{
				width: 61%;
			}
			.div-symptom-opt{
				padding-right: 0;
			}
			#symptom_hide{
				display: none;
			}
			.symptom-hide-lbl{
				display:none
			}
			
			/*submit button */
			#submit{
				color: white;
			    background-color: #2e6da4;
			    border-color: #2e6da4;
			    height: 3em;
			}
			
			/* Dynamic Div*/
			.dynamic_title{
				font-size:1.3em;
				/*padding-bottom:1em;*/
				// background-color:lightblue;
			}
			.dynamic_content{
				font-size:1.2em;
				letter-spacing:0.25em;
			}
			.q_checkbox_lbl{
				font-size:1em;
				padding-right:1em
			}
			.q_textarea{
				width:60vw;

			}
			.dynamic_qtext{
				padding-top: 1em;
			}
			.dynamic_radio_opt{
				/*width: 12%;*/  /* Adjust width For 7 opts*/
			}
			
			#dynamic_content{
				padding: 1em 0.25em;
			}
			.dynamic_radio_opt {
				font-size: 0.7em;
			}
			
			
			/*針對大螢幕進行調整*/
			@media screen and (min-width: 550px) { 
				.btn { 
					font-size:1.1em
				}
				#sportlight_lbl,#sportmoderate_lbl,#sportvigorous_lbl{
					width: 50%;
				}
				#sportlighttime_sel,#sportmoderatetime_sel,#sportvigoroustime_sel{
					width: 30%;
				}
				.lbl-symptom{
					width: 40%;
					font-size:1em;
				}
				.symptom-hide-lbl{
					display:block
				}
				.div-symptom-opt{
					padding-left: 15px;padding-right: 15px;
				}
				#submit{
					width: 40vw
				}
				.q_checkbox_lbl{
					font-size:1.8em;
				}
				.q_textarea{
					width:35vw;
				}
				#dynamic_content{
					padding: 1em ;
				}
			}
			
	</style>
</head>
<body>
	<?php include_once("analyticstracking.php") ?>
	<?php include("header.php");?>
	
	<div class="container">
		<form id="form" class="form-horizontal">
		<div class="panel panel-primary">
		  <div class="panel-heading">睡眠紀錄</div>
		  <div class="panel-body">	
		  	<div class="form-group">
					<label class="col-sm-3 control-label" >填寫哪一天的生活紀錄</label>
					<div class="col-sm-6">
						<select id="date" name="date" class="form-control" required>
							<?php 
								if( date('H')<18){
									echo "<option value={$yesterday}> {$yesterday}&nbsp(昨日) </option>";
									
									
								}else if(date('H')>=18){
									echo "<option value={$today}> 	  {$today}&nbsp(今日)    </option>";
									echo "<option value={$yesterday}> {$yesterday}&nbsp(昨日)</option>";
								}
							
							?>
							<!--
							<option value="<?php echo $today; ?>" />	<?php echo $today; ?>	 </option>
							<option value="<?php echo $yesterday; ?>" /><?php echo $yesterday; ?></option>
							-->
						</select>
					</div> 
			</div>
			<div class="form-group">
					<label class="col-sm-3 control-label" ><span class="changebydate"></span>當天幾點起床</label>
					<div class="col-sm-6">
						<select id="getup-h" name="getup-h" class="form-control " required>
							<option value="0" >00</option>
							<option value="01" >01</option>
							<option value="02" >02</option>
							<option value="03" >03</option>
							<option value="04" >04</option>
							<option value="05" >05</option>
							<option value="06" >06</option>
							<option selected>請選擇</option>
							<option value="07" >07</option>
							<option value="08" >08</option>
							<option value="09" >09</option>
							<option value="10" >10</option>
							<option value="11" >11</option>
							<option value="12" >12</option>
							<option value="13" >13</option>
							<option value="14" >14</option>
							<option value="15" >15</option>
							<option value="16" >16</option>
							<option value="17" >17</option>
							<option value="18" >18</option>
							<option value="19" >19</option>
							<option value="20" >20</option>
							<option value="21" >21</option>
							<option value="22" >22</option>
							<option value="23" >23</option>	
						</select>
						<select id="getup-m" name="getup-m" class="form-control " required>
                        	<option value="0" >00</option>
                        	<option value="30" >30</option>
                        </select>
					</div> 
			</div>
			<div class="form-group">
					<!--<label class="col-sm-3 control-label" ><span class="changebydate2"></span>晚上幾點睡覺</label>-->
					<label class="col-sm-3 control-label" >大約睡了多久</label>
					<div class="col-sm-6">
						<select id="gobed-h" name="gobed-h" class="form-control " required>
							<option value="0" >0小時</option>
							<option value="01" >01小時</option>
							<option value="02" >02小時</option>
							<option value="03" >03小時</option>
							<option value="04" >04小時</option>
							<option value="05" >05小時</option>
							<option value="06" >06小時</option>
							<option value="07" >07小時</option>
							<option selected>請選擇</option>
							<option value="08" >08小時</option>
							<option value="09" >09小時</option>
							<option value="10" >10小時</option>
							<option value="11" >11小時</option>
							<option value="12" >12小時</option>
							<option value="13" >13小時</option>
							<option value="14" >14小時</option>
							<option value="15" >15小時</option>
							<option value="16" >16小時</option>
							<option value="17" >17小時</option>
							<option value="18" >18小時</option>
							<option value="19" >19小時</option>
							<option value="20" >20小時</option>
							<option value="21" >21小時</option>
							<option value="22" >22小時</option>
							<option value="23" >23小時</option>	
						</select>
						<select id="gobed-m" name="gobed-m" class="form-control " required>
                        	<option value="0" >0分鐘</option>
                        	<option value="30" >30分鐘</option>
                        </select>
					</div> 
			</div>
			<div class="form-group" data-toggle="buttons">
					<label class="col-sm-3 control-label" >睡眠品質</label>
					<div class="col-sm-6" >
						<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-primary "><input name="sleep" type="radio" value="0" required>非常好</label>
						<label class="btn btn-primary"><input name="sleep" type="radio" value="1" >很好</label>
						<label class="btn btn-primary"><input name="sleep" type="radio" value="2" >還好</label>
						<label class="btn btn-primary"><input name="sleep" type="radio" value="3" >不好</label>
						<label class="btn btn-primary"><input name="sleep" type="radio" value="4" >非常不好</label>
						</div>
					</div>
			</div>
		  </div>
		</div>
		<div class="panel panel-success">
		  <div class="panel-heading">生活紀錄</div>
		  <div class="panel-body">	
		  	<div class="form-group" data-toggle="buttons">
					<label class="col-sm-3 control-label" ><span class="changebydate"></span>當天心情如何</label>
					<div class="col-sm-6" >
						<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-success"><input name="mood" type="radio" value="0" required>非常好</label>
						<label class="btn btn-success"><input name="mood" type="radio" value="1" >很好</label>
						<label class="btn btn-success"><input name="mood" type="radio" value="2" >還好</label>
						<label class="btn btn-success"><input name="mood" type="radio" value="3" >不好</label>
						<label class="btn btn-success"><input name="mood" type="radio" value="4" >非常不好</label>
						</div>
					</div>
			</div>
			
			<div class="form-group" data-toggle="buttons">
					<label class="col-sm-3 control-label" ><span class="changebydate"></span>當天感覺有壓力嗎?</label>
					<div class="col-sm-6" >
						<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-success stressF"><input name="stress" type="radio" value="0" required>完全沒有</label>
						<label class="btn btn-success stressT"><input name="stress" type="radio" value="1" >有一點</label>
						<label class="btn btn-success stressT"><input name="stress" type="radio" value="2" >中等程度</label>
						<label class="btn btn-success stressT"><input name="stress" type="radio" value="3" >很有壓力</label>
						<label class="btn btn-success stressT"><input name="stress" type="radio" value="4" >極有壓力</label>
						</div>
					</div>
			</div>
			
			
			<div id="stress_hide" class="form-group" >
					<label class="col-sm-3 control-label" >請選擇壓力來源(可複選)</label>
					<div  class="col-sm-8 div-stress-opt" >
						<div  class="checkbox">
							<label  for="stress_work" class="lbl-stress" > 
								<input id="stress_work" class="btn-stress"	type="checkbox" name="stress_work" value="1">工作/課業
							</label>
						  	<label  for="stress_social" class="lbl-stress">
								<input id="stress_social" class="btn-stress"	type="checkbox" name="stress_social" value="1">人際關係
							</label>
						</div>
					</div>
					<label class="col-sm-3 control-label symptom-hide-lbl" ></label>
					<div  class="col-sm-8 div-stress-opt" >
						<div  class="checkbox">
							<label  for="stress_family" class="lbl-stress">		
								<input id="stress_family" class="btn-stress"	type="checkbox" name="stress_family" value="1">家庭
							</label>
							<label  for="stress_self_expectation" class="lbl-stress">	
								<input id="stress_self_expectation" class="btn-stress"	type="checkbox" name="stress_self_expectation" value="1">自我期許
							</label>
						</div>
					</div>
					<label class="col-sm-3 control-label symptom-hide-lbl" ></label>
					<div  class="col-sm-8 div-stress-opt" >
						<div  class="checkbox">
							<label  for="stress_life_event"  class="lbl-stress">
								<input id="stress_life_event" class="btn-stress"	type="checkbox" name="stress_life_event" value="1">重大生活事件
							</label>
							
						</div>
					</div>
			</div>
			

			<hr>
			
			<div class="form-group" data-toggle="buttons">
					<label class="col-sm-4 control-label" ><span class="changebydate"></span>當天飲食紀錄</label>
					<div class="col-sm-6" >
						<label class="control-label"><span style="color: blue">點擊圖片可查看份量說明</span></label>
					</div>
			</div>
			<div class="form-group" data-toggle="buttons">
					<label class="col-sm-4 control-label" >
						全榖根莖類	(含米飯、麵食)
						<a onClick="alert('例如：白飯、麵條、麵包、吐司、饅頭、小麥、糙米、薏仁、地瓜、芋頭、馬鈴薯\n1份=1碗白飯(一般家用碗)=2碗稀飯=2個小蕃薯約220公克=1個饅頭或1.5片吐司約100公克')">
							<img src="./pic/cereal1.png" alt="" style="height:2em;width:2.5em">
							<!-- <img src="./pic/cereal2.png" alt="" style="height:2em;width:2.5em"> -->
							<img src="./pic/cereal3.png" alt="" style="height:2em;width:2.5em">
						</a>
					</label>
					
					<div class="col-sm-6" >
						<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-success"><input name="cereal" type="radio" value="0" required>0</label>
						<label class="btn btn-success"><input name="cereal" type="radio" value="1" >1</label>
						<label class="btn btn-success"><input name="cereal" type="radio" value="2" >2</label>
						<label class="btn btn-success"><input name="cereal" type="radio" value="3" >3</label>
						<label class="btn btn-success"><input name="cereal" type="radio" value="4" >4份以上</label>
						</div>
					</div>
			</div>
			<div class="form-group" data-toggle="buttons">
					<label class="col-sm-4 control-label" >
						蔬菜	
						<a onClick="alert('1份=生菜沙拉約1碗=半碗煮熟後的蔬菜')">
							<img src="./pic/veg1.png" alt="" style="height:2em;width:2.5em">
						</a>
					</label>
					<div class="col-sm-6" >
						<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-success"><input name="vegetable" type="radio" value="0" required>0</label>
						<label class="btn btn-success"><input name="vegetable" type="radio" value="1" >1</label>
						<label class="btn btn-success"><input name="vegetable" type="radio" value="2" >2</label>
						<label class="btn btn-success"><input name="vegetable" type="radio" value="3" >3</label>
						<label class="btn btn-success"><input name="vegetable" type="radio" value="4" >4份以上</label>
						</div>
					</div>
			</div>
			<div class="form-group" data-toggle="buttons">
					<label class="col-sm-4 control-label" >
						水果
						<a onClick="alert('1份=1碗切好的水果=1個奇異果的大小=半個芭樂的大小=15~20顆小蕃茄')">
							<img src="./pic/fruit2.png" alt="" style="height:2em;width:2.5em">
						</a>
					</label>
					<div class="col-sm-6" >
						<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-success"><input name="fruit" type="radio" value="0" required>0</label>
						<label class="btn btn-success"><input name="fruit" type="radio" value="1" >1</label>
						<label class="btn btn-success"><input name="fruit" type="radio" value="2" >2</label>
						<label class="btn btn-success"><input name="fruit" type="radio" value="3" >3</label>
						<label class="btn btn-success"><input name="fruit" type="radio" value="4" >4份以上</label>
						</div>
					</div>
			</div>
			<div class="form-group" data-toggle="buttons">
					<label class="col-sm-4 control-label" >
						肉類
						<a onClick="alert('1份=30~35公克重的牛肉、豬肉或雞肉...等，約一塊漢堡肉大小')">
							<img src="./pic/meat2.png" alt="" style="height:2em;width:2.5em">
						</a>
					</label>
					<div class="col-sm-6" >
						<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-success"><input name="meat" type="radio" value="0" required>0</label>
						<label class="btn btn-success"><input name="meat" type="radio" value="1" >1</label>
						<label class="btn btn-success"><input name="meat" type="radio" value="2" >2</label>
						<label class="btn btn-success"><input name="meat" type="radio" value="3" >3</label>
						<label class="btn btn-success"><input name="meat" type="radio" value="4" >4份以上</label>
						</div>
					</div>
			</div>
			<div class="form-group" data-toggle="buttons">
					<label class="col-sm-4 control-label" >
						海鮮
						<a onClick="alert('例如：魚、蝦、蟹、貝類\n1份=約半條秋刀魚=約6隻草蝦=一般大小的蛤蜊15~20個')">
							<img src="./pic/sea1.png" alt="" style="height:2em;width:2.5em">
							<!--<img src="./pic/sea2.png" alt="" style="height:2em;width:2.5em">-->
						</a>
					</label>
					<div class="col-sm-6" >
						<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-success"><input name="seafood" type="radio" value="0" required>0</label>
						<label class="btn btn-success"><input name="seafood" type="radio" value="1" >1</label>
						<label class="btn btn-success"><input name="seafood" type="radio" value="2" >2</label>
						<label class="btn btn-success"><input name="seafood" type="radio" value="3" >3</label>
						<label class="btn btn-success"><input name="seafood" type="radio" value="4" >4份以上</label>
						</div>
					</div>
			</div>
			<div class="form-group" data-toggle="buttons">
					<label class="col-sm-4 control-label" >
						豆類製品
						<a onClick="alert('例如：豆漿、豆腐、豆干、毛豆、黑豆\n1份=260c.c.豆漿=傳統豆腐3格=嫩豆腐半盒=方形豆干約1.5片')">
							<img src="./pic/bean1.png" alt="" style="height:2em;width:2.5em">
						</a>
					</label>
					<div class="col-sm-6" >
						<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-success"><input name="bean" type="radio" value="0" required>0</label>
						<label class="btn btn-success"><input name="bean" type="radio" value="1" >1</label>
						<label class="btn btn-success"><input name="bean" type="radio" value="2" >2</label>
						<label class="btn btn-success"><input name="bean" type="radio" value="3" >3</label>
						<label class="btn btn-success"><input name="bean" type="radio" value="4" >4份以上</label>
						</div>
					</div>
			</div>
			<div class="form-group" data-toggle="buttons">
					<label class="col-sm-4 control-label" >
						油炸食品
						<a onClick="alert('例如：炸豬排、薯條、甜不辣、炸香菇...等\n1份=30~35公克重，約半個手掌大小')">
							<img src="./pic/fried1.png" alt="" style="height:2em;width:2.5em">
							<img src="./pic/fried2.png" alt="" style="height:2em;width:2.5em">
						</a>
					</label>
					<div class="col-sm-6" >
						<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-success"><input name="fried" type="radio" value="0" required>0</label>
						<label class="btn btn-success"><input name="fried" type="radio" value="1" >1</label>
						<label class="btn btn-success"><input name="fried" type="radio" value="2" >2</label>
						<label class="btn btn-success"><input name="fried" type="radio" value="3" >3</label>
						<label class="btn btn-success"><input name="fried" type="radio" value="4" >4份以上</label>
						</div>
					</div>
			</div>
			<div class="form-group" data-toggle="buttons">
					<label class="col-sm-4 control-label" >
						蛋
						<a onClick="alert('1份=1顆雞蛋')">
							<img src="./pic/egg.png" alt="" style="height:2em;width:2.5em">
						</a>
					</label>
					<div class="col-sm-6" >
						<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-success"><input name="egg" type="radio" value="0" required>0</label>
						<label class="btn btn-success"><input name="egg" type="radio" value="0.5" >0.5</label>
						<label class="btn btn-success"><input name="egg" type="radio" value="1" >1</label>
						<label class="btn btn-success"><input name="egg" type="radio" value="2" >2</label>
						<label class="btn btn-success"><input name="egg" type="radio" value="3" >3份以上</label>
						</div>
					</div>
			</div>
			<div class="form-group" data-toggle="buttons">
					<label class="col-sm-4 control-label" >
						乳品類
						<a onClick="alert('1份=240c.c.牛奶或2片起司')">
							<img src="./pic/milk1.png" alt="" style="height:2em;width:2.5em">
							<img src="./pic/milk2.png" alt="" style="height:2em;width:2.5em">
						</a>
					</label>
					<div class="col-sm-6" >
						<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-success"><input name="milk" type="radio" value="0" required>0</label>
						<label class="btn btn-success"><input name="milk" type="radio" value="0.5" >0.5</label>
						<label class="btn btn-success"><input name="milk" type="radio" value="1" >1</label>
						<label class="btn btn-success"><input name="milk" type="radio" value="2" >2</label>
						<label class="btn btn-success"><input name="milk" type="radio" value="3" >3份以上</label>
						</div>
					</div>
			</div>
			<div class="form-group" data-toggle="buttons">
					<label class="col-sm-4 control-label" >
						甜食或含糖飲料
						<a onClick="alert('例如：蛋糕、糖果、巧克力、餅乾、汽水、果汁...等\n1份=約半個手掌大小的甜點=1瓶600c.c.罐裝飲料')">
							<img src="./pic/sweet1.png" alt="" style="height:2em;width:2.5em;padding-left:0.5em">
							<img src="./pic/sweet2.png" alt="" style="height:2em;width:2.5em">
						</a>
					</label>
					<div class="col-sm-6" >
						<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-success"><input name="sweet" type="radio" value="0" required>0</label>
						<label class="btn btn-success"><input name="sweet" type="radio" value="0.5" >0.5</label>
						<label class="btn btn-success"><input name="sweet" type="radio" value="1" >1</label>
						<label class="btn btn-success"><input name="sweet" type="radio" value="2" >2</label>
						<label class="btn btn-success"><input name="sweet" type="radio" value="3" >3份以上</label>
						</div>
					</div>
			</div>
			<hr>

			<div class="form-group" data-toggle="buttons">
					<label class="col-sm-3 control-label" ><span class="changebydate"></span>當天是否有運動</label>
					<div class="col-sm-6" >
						<div class="btn-group" data-toggle="buttons">
						<label id="sport1" class="btn btn-success"><input  name="sport" type="radio" value="1" required>有運動</label>
						<label id="sport0" class="btn btn-success"><input  name="sport" type="radio" value="0" >沒運動</label>
						</div>
					</div>
			</div>
			<div id="sp1_hide" class="form-group" > <!-- 若有data-toggle="buttons"會導致checkbox無法被勾選 -->
					<label class="col-sm-3 control-label" >請選擇運動與時間(可複選)</label>
					<div  class="col-sm-8" >
						<div class="checkbox" >
							<!--<input  type="hidden" name="sportlight" value="0">-->
						  <label  id="sportlight_lbl" for="sportlight_btn" ><input id="sportlight_btn" type="checkbox" name="sportlight" value="1">輕度運動(不會覺得喘，例如:散步)</label>
						  <select id="sportlighttime_sel" name="sportlighttime" class="form-control"  required>
							<option id="sportlighttime_opt0" value="0"> 請選擇輕度運動時間</option>
							<option value="1"> 0~15分鐘</option>
							<option value="2">15~30分鐘</option>
							<option value="3">30~60分鐘</option>
							<option value="4">60分鐘以上</option>
						</select>
						</div>
					</div>
			</div>
			<div id="sp2_hide" class="form-group" > <!-- 若有data-toggle="buttons"會導致checkbox無法被勾選 -->
					<label class="col-sm-3 control-label" ></label>
					<div class="col-sm-8" >
						<div class="checkbox" >
						  <label id="sportmoderate_lbl" for="sportmoderate_btn"><input id="sportmoderate_btn" type="checkbox" name="sportmoderate" value="1">中度運動(會感覺到喘，例如:慢跑)</label>
						  <select id="sportmoderatetime_sel" name="sportmoderatetime" class="form-control"  required>
							<option id="sportmoderatetime_opt0" value="0"> 請選擇中度運動時間</option>
							<option value="1"> 0~15分鐘</option>
							<option value="2">15~30分鐘</option>
							<option value="3">30~60分鐘</option>
							<option value="4">60分鐘以上</option>
						</select>
						</div>
					</div>
			</div>
			<div id="sp3_hide" class="form-group" > <!-- 若有data-toggle="buttons"會導致checkbox無法被勾選 -->
					<label class="col-sm-3 control-label" ></label>
					<div class="col-sm-8" >
						<div class="checkbox" >
						  <label id="sportvigorous_lbl" for="sportvigorous_btn"><input id="sportvigorous_btn" type="checkbox" name="sportvigorous" value="1">強度運動(感覺非常喘，例如:打籃球)</label>
						  <select id="sportvigoroustime_sel" name="sportvigoroustime" class="form-control"  required>
							<option id="sportvigoroustime_opt0" value="0"> 請選擇強度運動時間</option>
							<option value="1"> 0~15分鐘</option>
							<option value="2">15~30分鐘</option>
							<option value="3">30~60分鐘</option>
							<option value="4">60分鐘以上</option>
						</select>
						</div>
					</div>
			</div>
			<hr>
			<div class="form-group" data-toggle="buttons">
					<label class="col-sm-3 control-label" ><span class="changebydate"></span>當天是否有身體不適</label>
					<div class="col-sm-6" >
						<div class="btn-group" data-toggle="buttons">
						<label id="symptom1" class="btn btn-success"><input name="symptom" type="radio" value="1" required>有</label>
						<label id="symptom0" class="btn btn-success"><input name="symptom" type="radio" value="0" >無</label>
						</div>
					</div>
			</div>
			<div id="symptom_hide" class="form-group" > <!-- 若有data-toggle="buttons"會導致checkbox無法被勾選 -->
					<label class="col-sm-3 control-label" >請選擇症狀(可複選)</label>
					<div  class="col-sm-8 div-symptom-opt" >
						<div  class="checkbox">
							<label  for="sick" class="lbl-symptom-left" > <input id="sick"	class="btn-symptom" type="checkbox" name="sick" value="1">確定有感冒</label>
						  	<label  for="fever" class="lbl-symptom-right" ><input id="fever" 	class="btn-symptom" type="checkbox" name="fever" value="1">發燒(高於38度)</label>
						</div>
					</div>
					<label class="col-sm-3 control-label symptom-hide-lbl" ></label>
					<div  class="col-sm-8 div-symptom-opt" >
						<div  class="checkbox">
							<label  for="cough" class="lbl-symptom-left" >		<input id="cough" 		class="btn-symptom" type="checkbox" name="cough" value="1">咳嗽</label>
							<label  for="sorethroat" class="lbl-symptom-right" >	<input id="sorethroat"  class="btn-symptom" type="checkbox" name="sorethroat" value="1">喉嚨痛</label>
						</div>
					</div>
					<label class="col-sm-3 control-label symptom-hide-lbl" ></label>
					<div  class="col-sm-8 div-symptom-opt" >
						<div  class="checkbox">
							<label  for="hospital"  ><input id="hospital"  class="btn-symptom" type="checkbox" name="hospital" value="1">前往就醫(含診所)</label>
							
						</div>
					</div>
					<label class="col-sm-3 control-label symptom-hide-lbl" ></label>
					<div  class="col-sm-8 div-symptom-opt" >
						<div  class="checkbox">
							<label for="symptom_other"  ><input id="symptom_other" name="symptom_other"  class="btn-symptom" type="checkbox"  value="1">其他</label>
							<input id="symptom_other_text" name="symptom_other_text" type="text" placeholder="請描述身體不適的症狀" style="display:none">
						</div>
						
					</div>
					
					
					
					
					
					<!--
					<div  class="col-sm-8 div-symptom-opt" >
						<div  class="checkbox">
							<label  for="sick" class="lbl-symptom-left" > <input id="sick"	class="btn-symptom" type="checkbox" name="sick" value="1">確定有感冒</label>
						  	<label  for="fever" class="lbl-symptom-right" ><input id="fever" 	class="btn-symptom" type="checkbox" name="fever" value="1">發燒(高於38度)</label>
						</div>
					</div>
					<label class="col-sm-3 control-label symptom-hide-lbl" ></label>
					<div  class="col-sm-8 div-symptom-opt" >
						<div  class="checkbox">
							<label  for="cough" class="lbl-symptom-left" >		<input id="cough" 		class="btn-symptom" type="checkbox" name="cough" value="1">咳嗽</label>
							<label  for="sorethroat" class="lbl-symptom-right" >	<input id="sorethroat"  class="btn-symptom" type="checkbox" name="sorethroat" value="1">喉嚨痛</label>
						</div>
					</div>
					<label class="col-sm-3 control-label symptom-hide-lbl" ></label>
					<div  class="col-sm-8 div-symptom-opt" >
						<div  class="checkbox">
							<label  for="stuffynose" class="lbl-symptom-left" ><input id="stuffynose" class="btn-symptom" type="checkbox" name="stuffynose" value="1">鼻塞</label>
						  	<label  for="runnynose" class="lbl-symptom-right" > <input id="runnynose" class="btn-symptom" type="checkbox" name="runnynose" value="1">打噴嚏、流鼻水</label>
						  	
						</div>
					</div>
					<label class="col-sm-3 control-label symptom-hide-lbl" ></label>
					<div  class="col-sm-8 div-symptom-opt" >
						<div  class="checkbox">
						  	<label  for="chills"    class="lbl-symptom-left" >	<input id="chills"    class="btn-symptom" type="checkbox" name="chills" value="1">畏寒、寒顫</label>
						  	<label  for="tiredness" class="lbl-symptom-right" >	<input id="tiredness" class="btn-symptom" type="checkbox" name="tiredness" value="1">肌肉痠痛、全身疲倦</label>
						</div>
					</div>
					<label class="col-sm-3 control-label symptom-hide-lbl" ></label>
					<div  class="col-sm-8 div-symptom-opt" >
						<div  class="checkbox">
							<label  for="chesttightness"class="lbl-symptom-left" ><input id="chesttightness" class="btn-symptom" type="checkbox" name="chesttightness" value="1">胸悶、胸痛</label>
						  	<label  for="headache"		class="lbl-symptom-right" ><input id="headache" 		class="btn-symptom" type="checkbox" name="headache" value="1">頭痛、頭重、身體沉重</label>
						</div>
					</div>
					<label class="col-sm-3 control-label symptom-hide-lbl" ></label>
					<div  class="col-sm-8 div-symptom-opt" >
						<div  class="checkbox">
							<label  for="mask"class="lbl-symptom-left" ><input id="mask" class="btn-symptom" type="checkbox" name="mask" value="1">有戴口罩</label>
						  	
						</div>
					</div>
					-->
			</div>
			
			
			<div class="form-group" data-toggle="buttons">
					<label class="col-sm-3 control-label" ><span class="changebydate"></span>當天總共跟多少人接觸</label>
					<div class="col-sm-6" >
						<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-success"><input name="touchpeople" type="radio" value="0" required>0-4人</label>
						<label class="btn btn-success"><input name="touchpeople" type="radio" value="1" >5-9人</label>
						<label class="btn btn-success"><input name="touchpeople" type="radio" value="2" >10-19人</label>
						<label class="btn btn-success"><input name="touchpeople" type="radio" value="3" >20人以上</label>
						</div>
					</div>
			</div>
			
			<!--
			<div class="form-group">
					<label class="col-sm-3 control-label" ><span class="changebydate"></span>當天總共跟多少人接觸</label>
					<div class="col-sm-6">
						<select id="touchpeople" name="touchpeople" class="form-control"  required>
							<option value="" />請選擇</option>
							<option value="0" />0-4人</option>
							<option value="1" />5-9人</option>
							<option value="2" />10-19人</option>
							<option value="3" />20-49人</option>
							<option value="4" />50-99人</option>
							<option value="5" />100人以上</option>
							<option value="6" />不知道</option>
						</select>
					</div> 
			</div>
			-->
			




		  </div>
		</div>
		<!-- <div class="panel panel-info">
			<div class="panel-heading">來點不一樣的</div>
			<div class="panel-body" id="dynamic_content">
				<div id="dynamic_div"></div>
			</div>
		</div> -->
		<!--
		<div class="panel panel-info">
		  <div class="panel-heading"></div>
		  <div class="panel-body">	
			<div class="row">
				<div class="col-sm-1"></div>
				<div class="col-sm-8">
					<span style="color:#31708f">點擊以下標題，查看新聞內容，再回答相關問題</span>
					<a href=https://udn.com/news/plus/9402/2582307 target="_blank">
					<h4 style="color:blue; font-weight:bold; font-style: normal;">防禽流感為野鴿節育北市投避孕藥</h4>
					</a>
					<p>防範禽流感，台北市啟動「野鴿節育計畫」。動保處今年於華山藝文特區示範投放「鴿類避孕藥」，預計1年內可降低半數野鴿，將視成效擴大至大安森林&nbsp;...</p>
				</div>
				<div class="col-sm-3"></div>
			</div>
			<hr>
			<div class="form-group" data-toggle="buttons">
					<label class="col-sm-3 control-label" >我對於這則新聞的信任程度</label>
					<div class="col-sm-6" >
						<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-success"><input name="" type="radio" value="0" >信任</label>
						<label class="btn btn-success"><input name="" type="radio" value="1" >不信任</label>
						<label class="btn btn-success"><input name="" type="radio" value="2" >不知道</label>
						
						</div>
					</div>
			</div>
			<div class="form-group" data-toggle="buttons">
					<label class="col-sm-3 control-label" >會不會將這則新聞分享出去</label>
					<div class="col-sm-6" >
						<div class="btn-group" data-toggle="buttons">
						<label class="btn btn-success"><input name="" type="radio" value="0" >會分享</label>
						<label class="btn btn-success"><input name="" type="radio" value="1" >不會</label>
						<label class="btn btn-success"><input name="" type="radio" value="2" >不知道</label>
						
						</div>
					</div>
			</div>
			<div class="form-group" data-toggle="buttons">
		  			<label class="col-sm-3 control-label" >其他想法/意見表達</label>
					<div class="col-sm-6">
						
						<textarea rows=5 class="form-control"></textarea>
					</div> 
				</div>
		  </div>
		</div>
		-->
			<div class="submit_button" align="center">
				<div id='loadingtext' style='font-size:1.5em;color:red;display:none'>傳送中˙˙˙請稍候</div>
				<input class="btn btn-lg btn-block " type="submit" id="submit" value="送出!!" rows=5 >
			</div>
		</form>
	</div>
	<footer class="footer">
      		<div class="container">
      			<img id="footerimg"src="./pic/Academia_Sinica_Emblem.png" >
			    <div class="text">
				著作權©中研院統計科學研究所. 版權所有.<br>
			    Copyright© Institute of Statistical Science, Academia Sinica.
			    All rights reserved.
			    </div>
			</div>
   	</footer>
		 
	

	
	
</body>
</html>
