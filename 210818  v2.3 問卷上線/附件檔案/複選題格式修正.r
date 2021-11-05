library(dplyr, warn.conflicts = FALSE)
library(magrittr)
library(stringr)
library(stringi)
library(plyr)
#filepath1:問卷編輯csv檔路徑
##filepath2:問卷下載資料txt檔路徑
##filepath3:匯出轉換後csv檔路徑
f_convert = function(filepath1,filepath2,filepath3)  
{

	f_sign = function(x){
		pattern_starts <- "\\[\\d{1,}+(\\,{1,}+\\d{1,}){0,}\\]"
		if (!str_detect(x, pattern_starts))
			return(x)
		stairs <- str_extract_all(x, pattern_starts) %>% unlist()
		for(i in 1:length(stairs))
		{
		  x <- str_replace_all(stairs[i], ",", "、") %>% 
		  {str_replace(x, fixed(stairs[i]), .)}
		}
		return(x)
	}


	data_problem = read.csv(filepath1, stringsAsFactors = FALSE, header = TRUE, fileEncoding = "UTF-8-BOM")
	data_problem[is.na(data_problem)] <- ""
	data_multiple = data_problem %>% filter(str_detect(q_txt,"複選"))

	data0 = readLines(con <- file(filepath2, encoding = "UTF-8")) %>% str_replace_all(.,"\"","") %>% str_split_fixed(.,",",n=3)
	data0[,3] = str_replace_all(data0[,3],"\\{","") %>% str_replace_all(.,"\\}","")
	data0[,3] = data0[,3] %>% sapply(.,f_sign) %>% `names<-` (NULL) %>% `colnames<-`(NULL) %>% unlist %>% as.vector()
	#其他
	data_colname = apply(str_split_fixed(data0[2,3],",", n = str_count(data0[2,3],",")+1),2,function(x) str_split_fixed(x,":",2))[1,]
	other = which(str_count(data_colname,"-") ==2)
	for (i in 1:length(other))
	{
		data_colname[other[i]] = paste0(data_colname[other[i]],"-其他")
	}
	data_value = NULL
	for (j in 2:nrow(data0))
	{
		data_value = rbind(data_value, apply(str_split_fixed(data0[j,3],",", n = str_count(data0[j,3],",")+1),2,function(x) str_split_fixed(x,":",2))[2,])

	}

	#複選
	data_multiple_unique = data_multiple %>% select(q_id,q_sn,q_txt) %>% unique
	multiple_convert = list()
	for (i in 1:nrow(data_multiple_unique))
	{
		opt = data_multiple %>% filter(q_txt == data_multiple_unique[i,"q_txt"]) %>% mutate(num = paste0(q_id,"-",q_sn,"-",opt_value))
		opt2 = opt %>% pull(num)
		multiple_convert2 = NULL
		for (j in 2:nrow(data0))
		{
			data_question = str_split_fixed(data0[j,3],",", n = str_count(data0[j,3],",")+1)
			value = apply(data_question,2,function(x) str_split_fixed(x,":",2))[2,][which(apply(data_question,2,function(x) str_split_fixed(x,":",2))[1,] == paste0(data_multiple_unique[i,"q_id"],"-",data_multiple_unique[i,"q_sn"]))] %>% str_replace(.,"\\[","") %>% str_replace(.,"\\]","") %>% str_split_fixed(.,"\\、",n = str_count(.,"、")+1) %>% as.numeric()
			value2 = as.numeric(min(opt$opt_value):max(opt$opt_value) %in% value)
			multiple_convert2 = rbind(multiple_convert2, value2)
			rownames(multiple_convert2) = 1:nrow(multiple_convert2)
			
		}
		colnames(multiple_convert2) = opt2
		multiple_convert[[i]] = multiple_convert2
	}

	data_final = NULL
	data_final_colname = NULL
	for (i in 1:nrow(data_multiple_unique))
	{
		gap = which(data_colname == paste0(data_multiple_unique[i,"q_id"],"-", data_multiple_unique[i,"q_sn"]))
		
		if (i == 1)
		{
			data_final = cbind(data_final, cbind(data_value[,1:(gap-1)],multiple_convert[[i]]))
			data_final_colname = c(data_final_colname, c(data_colname[1:(gap-1)],colnames(multiple_convert[[i]])))
		} else
		{
			gap_pre = which(data_colname == paste0(data_multiple_unique[i-1,"q_id"],"-", data_multiple_unique[i-1,"q_sn"]))
			data_final = cbind(data_final, cbind(data_value[,(gap_pre+1):(gap-1)],multiple_convert[[i]]))
			data_final_colname = c(data_final_colname, c(data_colname[(gap_pre+1):(gap-1)],colnames(multiple_convert[[i]])))

		}
	}
	data_final = cbind(data_final, data_value[,(gap+1):ncol(data_value)])
	data_final_colname = c(data_final_colname, data_colname[(gap+1):length(data_colname)]) %>% paste0("Q",.)
	data_final2 = cbind(data0[-1,1:2],data_final)
	colnames(data_final2) = c(data0[1,1:2],data_final_colname)

	data_final2 %>% write.csv(filepath3, row.names = FALSE)

}

f_convert("D:/兒童與生活學習調查問卷/幼兒點日記/COVID-19/COVID-19.csv","D:/兒童與生活學習調查問卷/幼兒點日記/COVID-19/110020beta.txt","D:/兒童與生活學習調查問卷/幼兒點日記/COVID-19/test.csv")


