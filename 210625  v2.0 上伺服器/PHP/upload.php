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
	$destination=$uploadPath."/".$fileInfo["name"];
    
    if(!in_array($extention, $allowExt)){
        $result["err"]="不支援的格式";
        return $result;
    }else if($fileInfo["size"]>$maxSize){
        $result["err"]="檔案容量過大";
        return $result;
    }else{
        mkdir($uploadPath, 0777, true);
        
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
