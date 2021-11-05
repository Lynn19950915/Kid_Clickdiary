<?php
function getFiles(){
    $i=0;
    foreach($_FILES as $file){
        if(is_string($file["name"])){
			$files[$i]=$file;
			$i++;
        }
		else if(is_array($file["name"])){
            foreach($file["name"] as $key=>$value){
                $files[$i]["name"]=$file["name"][$key];
                $files[$i]["type"]=$file["type"][$key];
                $files[$i]["tmp_name"]=$file["tmp_name"][$key];
                $files[$i]["error"]=$file["error"][$key];
                $files[$i]["size"]=$file["size"][$key];
                $i++;
            }
        }
    }
    return $files;
}

function uploadFile($fileInfo, $allowExt=array("csv", "json"), $maxSize=209715200, $flag=true, $uploadPath="C:/***"){
    $result=array();
    $extention=pathinfo($fileInfo["name"], PATHINFO_EXTENSION);
	$destination=$uploadPath."/".iconv("utf-8", "big5", $fileInfo["name"]);
    
    if(!in_array($extention, $allowExt)){
        $result["err"]="不支援的格式";
        return $result;
    }else if($fileInfo["size"]>$maxSize){
        $result["err"]="檔案容量過大";
        return $result;
    }else{
        //mkdir($uploadPath, 0777, true);
		move_uploaded_file($fileInfo["tmp_name"], $destination);
		
        if(!@move_uploaded_file($fileInfo["tmp_name"], $destination)){
            $result["err"]="檔案移動失敗";
            $result["dest"]=$destination;
            return $result;
        }else{
            $result["err"]="上傳成功";
            $result["dest"]=$destination;
            return $result;
        }
    }
}

function fgetcsv2(&$handle, $length=null, $d=",", $e='"'){
	$d=preg_quote($d);
	$e=preg_quote($e);
	$_line="";
	$eof=false;
	while($eof!=true){
		$_line.=(empty($length)?fgets($handle): fgets($handle, $length));
		$itemcnt=preg_match_all('/'.$e.'/', $_line, $dummy);
		if($itemcnt%2==0){
			$eof=true;
		}
	}

	$_csv_line=preg_replace('/(?: |[ ])?$/', $d, trim($_line));
	$_csv_pattern='/('.$e.'[^'.$e.']*(?:'.$e.$e.'[^'.$e.']*)*'.$e.'|[^'.$d.']*)'.$d.'/';
	preg_match_all($_csv_pattern, $_csv_line, $_csv_matches);
	$_csv_data=$_csv_matches[1];

	for($_csv_i=0; $_csv_i<count($_csv_data); $_csv_i++){
	  $_csv_data[$_csv_i]=preg_replace("/^".$e."(.*)".$e."$/s", "$1", $_csv_data[$_csv_i]);
	  $_csv_data[$_csv_i]=str_replace($e.$e, $e, $_csv_data[$_csv_i]);
	}

	return empty($_line)?false: $_csv_data;
}
?>
