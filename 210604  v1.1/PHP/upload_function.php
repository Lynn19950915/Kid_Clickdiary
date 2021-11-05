<?php

function getFiles(){
    $i=0;
    foreach($_FILES as $file){
		// string: 表示單一檔案
        if(is_string($file['name'])){
			$files[$i]=$file;
			$i++;
        }
		// array: 表示多個檔案
		else if(is_array($file['name'])){
            foreach($file['name'] as $key=>$value){
                $files[$i]['name']=$file['name'][$key];
                $files[$i]['type']=$file['type'][$key];
                $files[$i]['tmp_name']=$file['tmp_name'][$key];
                $files[$i]['error']=$file['error'][$key];
                $files[$i]['size']=$file['size'][$key];
                $i++;
            }
        }
    }
    return $files;
}


function uploadFile($fileInfo, $allowExt=array('csv', 'json'), $maxSize=209715200, $flag=true, $uploadPath='C:\***'){
    $res=array();
	$ext=pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
	$destination=$uploadPath.'/'.$fileInfo['name'];    

    // 判斷是否有錯誤
    if($fileInfo['error']>0){
        switch($fileInfo['error']){
            case 1:
                $res['mes']=$fileInfo['name'].'上傳的檔案超過了 php.ini 中 upload_max_filesize 允許上傳檔案容量的最大值';
                break;
            case 2:
                $res['mes']=$fileInfo['name'].'上傳檔案的大小超過了 HTML 表單中 MAX_FILE_SIZE 選項指定的值';
                break;
            case 3:
                $res['mes']=$fileInfo['name'].'檔案只有部分被上傳';
                break;
            case 4:
                $res['mes']=$fileInfo['name'].'沒有檔案被上傳（沒有選擇上傳檔案就送出表單）';
                break;
            case 6:
                $res['mes']=$fileInfo['name'].'找不到臨時目錄';
                break;
            case 7:
                $res['mes']=$fileInfo['name'].'檔案寫入失敗';
                break;
            case 8:
                $res['mes']=$fileInfo['name'].'上傳的文件被 PHP 擴展程式中斷';
                break;
        }
        return $res;
    }

    // 檢查檔案是否是通過 HTTP POST 上傳的
    if(!is_uploaded_file($fileInfo['tmp_name'])){
        $res['mes']=$fileInfo['name'].'檔案不是通過 HTTP POST 方式上傳的';
    }
    
    // 檢查上傳檔案是否為允許的擴展名
    if(!is_array($allowExt)){
        $res['mes']=$fileInfo['name'].'檔案類型型態必須為 array';
    }else{
        if(!in_array($ext, $allowExt)){
			$res['mes']=$fileInfo['name'].'非法檔案類型';
		}
    }

    // 檢查上傳檔案的容量大小是否符合規範
    if($fileInfo['size']>$maxSize){
        $res['mes']=$fileInfo['name'].'上傳檔案容量超過限制';
    }

	
    if(!empty($res)){
        return $res;
    }else{
		mkdir($uploadPath, 0777, true);
	}       
    if(!@move_uploaded_file($fileInfo['tmp_name'], $destination)){
        $res['mes']=$fileInfo['name'].'檔案移動失敗';
        $res['dest']=$destination;
    }else{
        $res['mes']=$fileInfo['name'].'上傳成功';
        $res['dest']=$destination;
    }
	
	return $res;
}
