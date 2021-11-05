<?php
require("db.php");
date_default_timezone_set("Asia/Taipei");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
if(isset($_POST['save'])){ 
	$title	=$_POST['title'];  	
	$content=$_POST['content'];
	$post_type	=$_POST['post_type'];
	$start_time	=$_POST['start_time'];
	$end_time	=$_POST['end_time'];
	$receiver_id=$_POST['receiver_id'];
	
	$question_items=$_POST['question_items'];
	$question_json=json_encode(array_filter($question_items),384);
	
	$sql="insert into `postlist`(post_id,type,receiver,title,content,question,start_time,end_time,createtime) 
						 values (NULL,:v1,:v2,:v3,:v4,:v5,:v6,:v7,:v8)";
	$prepare=$db->prepare($sql);
	$prepare->bindValue(':v1',$post_type);
	$prepare->bindValue(':v2',$receiver_id);
	$prepare->bindValue(':v3',$title);
	$prepare->bindValue(':v4',$content);
	$prepare->bindValue(':v5',$question_json);
	$prepare->bindValue(':v6',$start_time);
	$prepare->bindValue(':v7',$end_time);
	$prepare->bindValue(':v8',date("Y-m-d H:i:s"));
	$prepare->execute();
}


?><!DOCTYPE html>
<html>
<head>
	<title>時事上傳</title>
	<meta http-equiv="Content-Type" content="text/html"  charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script src="https://code.jquery.com/jquery-3.1.1.js"></script>
	
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<script src="js/bootstrap.min.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			// 選項設定
			$("#receiver0_lbl").on("click", function (event) { 
				$("#receiver_id").val("all");
			});
			$("#receiver1_lbl").on("click", function (event) {
				if($("#receiver_id").val()=="all"){
					$("#receiver_id").val("");	
				}
			});
			
			//載入題目設定區塊
			var th1=$('<th>').html('編號');
			var th2=$('<th>').html('類型');
			var th3=$('<th>').html('題目');
			var th4=$('<th>').html('選項');
			var thead=$('<thead>').append( $('<tr>').append(th1).append(th2).append(th3).append(th4) );
			$("#table_questions").append(thead);
			for(i=0;i<1;i++){ // 預先append 1行
				var td1=$('<td>').append($('<span>').attr({'id':'q'+i+'_sn'}).html(i+1));
				var td2=$('<td>').append(
							$('<input>').attr({'type':'radio','id':'q'+i+'_type0','name':'q'+i+'_type','value':0,'required':true})
						).append(
							$('<label>').html('信任度').attr({'id':'q'+i+'_type_lbl0','for':'q'+i+'_type0','onclick':'trustlevel(this.id)'})								
						).append($('<br>')).append(
							$('<input>').attr({'type':'radio','id':'q'+i+'_type1','name':'q'+i+'_type','value':1})
						).append(
							$('<label>').html('分享').attr({'id':'q'+i+'_type_lbl1','for':'q'+i+'_type1','onclick':'share(this.id)'})
						).append($('<br>')).append(
							$('<label>').html('新增(單選)').prepend(
								$('<input>').attr({'type':'radio','name':'q'+i+'_type','value':2})
							)
						).append($('<br>')).append(
							$('<label>').html('新增(多選)').prepend(
								$('<input>').attr({'type':'radio','name':'q'+i+'_type','value':3})
							)
						).append($('<br>')).append(
							$('<label>').html('新增(簡答)').attr({'id':'q'+i+'_type_lbl4','onclick':'notrequired(this.id)'}).prepend(
								$('<input>').attr({'type':'radio','name':'q'+i+'_type','value':4})
							)
						);
				var td3=$('<td>').append($('<input>').attr({'type':'text','id':'q'+i+'_text','class':'tb_input2','required':true}));
				// 第i題 第0個選項
				var add_opt=$('<button>').attr({'id':'add_q'+i,'onclick':'addopt(this.id)'}).append(
								$('<span>').attr({'class':'glyphicon glyphicon-plus'})
							);
				var remove_opt=$('<button>').attr({'id':'remove_q'+i,'onclick':'removeopt(this.id)'}).append(
								$('<span>').attr({'class':'glyphicon glyphicon-minus'})
							);
				var td4=$('<td>').attr({'id':'td4_row'+i}).append(
							$('<input>').attr({'type':'text','id':'q'+i+'_opt0','class':'q'+i+'_opt','placeholder':'選項1','required':true})
						).append(add_opt).append(remove_opt).append($('<br>'))
						 .append(
							$('<input>').attr({'type':'text','id':'q'+i+'_opt1','class':'q'+i+'_opt','placeholder':'選項2'})
						).append($('<br>')).append(
							$('<input>').attr({'type':'text','id':'q'+i+'_opt2','class':'q'+i+'_opt','placeholder':'選項3'})
						).append($('<br>')).append(
							$('<input>').attr({'type':'text','id':'q'+i+'_opt3','class':'q'+i+'_opt','placeholder':'選項4'})
						).append($('<br>')).append(
							$('<input>').attr({'type':'text','id':'q'+i+'_opt4','class':'q'+i+'_opt','placeholder':'選項5'})
						)
						;			
				
				
				var tbody=$('<tbody>').append( $('<tr>').append(td1).append(td2).append(td3).append(td4) );
				$("#table_questions").append(tbody);	
				
				
				
			}
			
			// 點擊增加row數
			$("#add_row").on("click", function (event) {  // each click add 1 row
				event.preventDefault();
				var a = $('#table_questions tbody tr').length; // tbody中有幾列，a的初始值為1 (已預先append 1列)
				
				tbody.append(
					$('<tr>').append(
						$('<td>').append($('<span>').attr({'id':'q'+a+'_sn'}).html(a+1))
					).append(
						$('<td>').append(
							$('<input>').attr({'type':'radio','id':'q'+a+'_type0','name':'q'+a+'_type','value':0,'required':true})
						).append(
							$('<label>').html('信任度').attr({'id':'q'+a+'_type_lbl0','for':'q'+a+'_type0','onclick':'trustlevel(this.id)'})								
						).append($('<br>')).append(
							$('<input>').attr({'type':'radio','id':'q'+a+'_type1','name':'q'+a+'_type','value':1})
						).append(
							$('<label>').html('分享').attr({'id':'q'+a+'_type_lbl1','for':'q'+a+'_type1','onclick':'share(this.id)'})
						).append($('<br>')).append(
							$('<label>').html('新增(單選)').prepend(
								$('<input>').attr({'type':'radio','name':'q'+a+'_type','value':2})
							)
						).append($('<br>')).append(
							$('<label>').html('新增(多選)').prepend(
								$('<input>').attr({'type':'radio','name':'q'+a+'_type','value':3})
							)
						).append($('<br>')).append(
							$('<label>').html('新增(簡答)').attr({'id':'q'+a+'_type_lbl4','onclick':'notrequired(this.id)'}).prepend(
								$('<input>').attr({'type':'radio','name':'q'+a+'_type','value':4})
							)
						)
						/*
						$('<td>').append(
							$('<label>').html('信任度').attr({'id':'q'+a+'_type_lbl0'}).prepend(
								$('<input>').attr({'type':'radio','name':'q'+a+'_type','value':0,'required':true})
							)
						).append($('<br>')).append(
							$('<label>').html('分享').prepend(
								$('<input>').attr({'type':'radio','name':'q'+a+'_type','value':1})
							)
						).append($('<br>')).append(
							$('<label>').html('新增(單選)').prepend(
								$('<input>').attr({'type':'radio','name':'q'+a+'_type','value':2})
							)
						).append($('<br>')).append(
							$('<label>').html('新增(多選)').prepend(
								$('<input>').attr({'type':'radio','name':'q'+a+'_type','value':3})
							)
						).append($('<br>')).append(
							$('<label>').html('新增(簡答)').prepend(
								$('<input>').attr({'type':'radio','name':'q'+a+'_type','value':4})
							)
						)
						*/
					).append(
						$('<td>').append($('<input>').attr({'type':'text','id':'q'+a+'_text','class':'tb_input2','required':true}))
					).append(
						$('<td>').attr({'id':'td4_row'+a}).append(
							$('<input>').attr({'type':'text','id':'q'+a+'_opt0','class':'q'+a+'_opt','placeholder':'選項1','required':true})
						).append(
							$('<button>').attr({'id':'add_q'+a,'onclick':'addopt(this.id)'}).append(
								$('<span>').attr({'class':'glyphicon glyphicon-plus'})
							)
						).append(
							$('<button>').attr({'id':'remove_q'+a,'onclick':'removeopt(this.id)'}).append(
								$('<span>').attr({'class':'glyphicon glyphicon-minus'})
							)
						).append($('<br>')).append(
							$('<input>').attr({'type':'text','id':'q'+a+'_opt1','class':'q'+a+'_opt','placeholder':'選項2'})
						).append($('<br>')).append(
							$('<input>').attr({'type':'text','id':'q'+a+'_opt2','class':'q'+a+'_opt','placeholder':'選項3'})
						).append($('<br>')).append(
							$('<input>').attr({'type':'text','id':'q'+a+'_opt3','class':'q'+a+'_opt','placeholder':'選項4'})
						).append($('<br>')).append(
							$('<input>').attr({'type':'text','id':'q'+a+'_opt4','class':'q'+a+'_opt','placeholder':'選項5'})
						)
						/*
						$('<td>').attr({'id':'td4_row'+a}).append(
							$('<input>').attr({'type':'text','id':'q'+a+'_opt'+0,'class':'q'+a+'_opt','placeholder':'選項1','required':true})
						).append(
							$('<button>').attr({'id':'add_q'+a,'onclick':'addopt(this.id)'}).append(
								$('<span>').attr({'class':'glyphicon glyphicon-plus'})
							)
						).append(
							$('<button>').attr({'id':'remove_q'+a,'onclick':'removeopt(this.id)'}).append(
								$('<span>').attr({'class':'glyphicon glyphicon-minus'})
							)
						)
						*/
					)
					
					
				);
			});
			
			// 點擊remove row
			$("#remove_row").on("click", function (event) {
				event.preventDefault();
				var b = $("#table_questions tbody tr").length;
				if(b >1){
					$('#table_questions tr:last').remove(); //document.getElementById("mytable").deleteRow(-1);
				}
			});


			// submit form
			$("#form").on("submit", function (event) {
				event.preventDefault();
				var title	 	=$("input[name='title']").val(); 
				var content	 	=$("input[name='content']").val(); 
				var post_type	=$("input[name='type']:checked").val(); 
				var start_time	=$("input[name='start_time']").val(); 
				var end_time	=$("input[name='end_time']").val(); 
				var receiver_id =$("input[name='receiver_id']").val(); // all 或者 a serial of numbers
				console.log(title,content,post_type,start_time,end_time,receiver_id);
				
				var number_rows=$("#table_questions >tbody >tr").length;//有幾題
				question_items =[];
				for(var n1=0;n1<number_rows;n1++){  // n1:第n1題
					var question_items_key={};
					question_items_key["sn"]	=n1+1;	// 題目編號
					question_items_key["type"]	=$("input[name=q"+n1+"_type]:checked").val(); 	// 題型(預設、單選、多選、簡答)
					question_items_key["text"]	=$("input[id=q"+n1+"_text]").val();	// 題目敘述
					
					var number_options=$(".q"+n1+"_opt").length;	//有幾個選項
					opt_items =[];
					for(var n2=0;n2<number_options;n2++){ // n2:第n2個選項
						var opt_items_key={};
						if($("input[id=q"+n1+"_opt"+n2+"]").val()!=""){
							opt_items_key["opt"+n2] =$("input[id=q"+n1+"_opt"+n2+"]").val();		//選項
							opt_items.push(opt_items_key);
						}
						
					}
					question_items_key["opts"] = opt_items;
					question_items.push(question_items_key);
				}
				console.log(number_rows);
				console.log(opt_items);
				console.log(question_items);
				
				$.ajax({
					url: "",
					method:"POST",
					// dataType: "json",	
					async:false,
					data: {
						save: 1,
						title:title,  	
						content:content,
						post_type:post_type,
						start_time:start_time,
						end_time:end_time,
						receiver_id:receiver_id,
						question_items : question_items			//array   
					},success: function(data) {
						alert("已上傳");
					},error: function(e) {
						console.log(e);
					}
				});
				
				
				/*
				var out_num =$(".tb1_input1").length;
				var out_num2=$(".tb1_input2").length;
				out_items = [];
					for(var n1=0;n1<out_num;n1++){
						var out_items_key={};  
						out_items_key["name"]=$("#outpatient_name"+n1).val();
						out_items_key["date"]=$("#outpatient_date"+n1).val();
						out_items.push(out_items_key);
					}

				*/
				
			});
		
		});
		function addopt(clicked_id){  	// 增加第i題的選項，起始題為第0題
			event.preventDefault();
			// 點擊的是第i題的btn
			//console.log(clicked_id);
			var question_i = clicked_id.substr(clicked_id.length - 1); // last character, which means ith question
			//console.log(question_i);
			// 已有幾個選項
			var opt_number=$(".q"+question_i+"_opt").length;
			//console.log(opt_number);
			// 增加一個選項
			$("#td4_row"+question_i).append($('<br>')).append(
				$('<input>').attr({'type':'text','id':'q'+question_i+'_opt'+(opt_number),'class':'q'+question_i+'_opt','placeholder':'選項'+(opt_number+1),'required':true})
			);

		}
		function removeopt(clicked_id){	// 移除第i題的選項，起始題為第0題
			event.preventDefault();
			// 點擊的是第i題的btn
			//console.log(clicked_id);
			var question_i = clicked_id.substr(clicked_id.length - 1); // last character, which means ith question
			//console.log(question_i);
			//
			var c = $(".q"+question_i+"_opt").length;
			if(c >1){
				//$("#td4_row"+question_i+"br:last").remove();
				//$('.q'+question_i+'_opt:last').remove(); 
				$("#td4_row"+question_i+">input").last().remove();
				$("#td4_row"+question_i+">br").last().remove();
			}

		}
		function trustlevel(clicked_id){// 預填信任程度的題目、選項
			//console.log(clicked_id);
			var question_i = clicked_id.substr(0,2); // ith Q
			//console.log(question_i);
			$("#"+question_i+"_text").val("我對這則新聞/消息的信任程度");
			$("#"+question_i+"_opt0").val("非常信任");
			$("#"+question_i+"_opt1").val("信任");
			$("#"+question_i+"_opt2").val("普通");
			$("#"+question_i+"_opt3").val("不信任");
			$("#"+question_i+"_opt4").val("非常不信任");
			
		}
		function share(clicked_id){		// 預填是否會分享的題目、選項
			var question_i = clicked_id.substr(0,2); // ith Q
			$("#"+question_i+"_text").val("我會不會分享這則新聞/消息");
			$("#"+question_i+"_opt0").val("會");
			$("#"+question_i+"_opt1").val("不會");
			$("#"+question_i+"_opt2").val("");
			$("#"+question_i+"_opt3").val("");
			$("#"+question_i+"_opt4").val("");
			
		}
		function notrequired(clicked_id){ // 簡答題不需要設定選項
			var question_i = clicked_id.substr(0,2); // ith Q
			$("#"+question_i+"_opt0").removeAttr("required");
		}
		
	</script>
	<style type="text/css">
	label{
		font-weight:400;
	}
	.form-control{
		display:inline;
		width:50%;
	}
	#start_time,#end_time{
		
	}
	#content,#receiver_id{
		height:5em;
	}
	.tb_input2{ /* 題目*/
		width:100%;
	}
	#add_row,#remove_row,#submit{
		width:3em;
		height:3em;
	}
	
	/*針對大螢幕進行調整*/
	@media screen and (min-width: 550px) { 
		#start_time,#end_time{
			width:20%
		}
	}
	</style>
</head>
<body>
	<div class="container" style="padding-bottom:2em">
	<form id="form" action='' method='post'>
		<div class="form-group">標題
			<div>
				<input id="title" name="title" type="text" class="form-control" required> 
			</div>
		</div>
		<div class="form-group">內容
			<div>
			<input id="content" name="content"  class="form-control" required></input>
			</div>
		</div>
		<div class="form-group">類型
			<div class="btn-group" data-toggle="buttons" >
				<label class="btn btn-default "><input name="type" type="radio" value="0" required>新聞時事</label>
				<label class="btn btn-default "><input name="type" type="radio" value="1" >type2</label>
			</div>
		</div>
		<div class="form-group">
			<div>
				開始日期<input id="start_time" name="start_time" type="date" class="form-control" required> 
			</div>
		</div>
		<div class="form-group">
			<div>
				結束日期<input id="end_time" name="end_time" type="date" class="form-control" required> 
			</div>
		</div>
		<div class="form-group">目標對象
			<div class="btn-group" data-toggle="buttons">
				<label id="receiver0_lbl" class="btn btn-default "><input id="receiver0" name="receiver" type="radio" value="0" required>所有人</label>
				<label id="receiver1_lbl" class="btn btn-default "><input id="receiver1" name="receiver" type="radio" value="1" >特定對象</label>
			</div>
			<div>
			<input id="receiver_id" name="receiver_id"  class="form-control" placeholder="填入目標對象id並以,區隔" required></input>
			</div>
		</div>
		<hr>
		<div>
			<div class="col-sm-12 " style="padding-left:0;padding-right:0"><!--position:relative;-->
				<table id="table_questions" class="table table-condensed table-bordered"></table>
			</div>
			<div class="col-sm-12" style="padding-left:0;">
				<button class="btn-success" id="add_row" ><span class="glyphicon glyphicon-plus"></span></button>
				<button class="btn-success" id="remove_row" ><span class="glyphicon glyphicon-minus"></span></button>
				<input id="submit" type="submit" class="btn-primary" value="上傳"></input>
			</div>
		</div>
		
		
		
		
		
		
	</form>
	</div>
</body>
</html>
