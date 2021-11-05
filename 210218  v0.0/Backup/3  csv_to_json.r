require(pacman)
{p_load(
  MASS,
  # Data Processing
  data.table, magrittr, dplyr, stringr, jsonlite,
  # Time  
  zoo, chron,
  # DataBase
  RMySQL
)}

## Input
args = commandArgs(TRUE)
prjid   = args[1]
# qid     = prjid
fname   = args[2]
fpath   = args[3]
abs_path = paste0(getwd(), '/', fpath)
# c(prjid,fname,abs_path) %>% print
parse_st = regexpr("\\(",fname) [1] + 1
qid      = substr(fname, parse_st, parse_st)
# 
df = fread(abs_path)
df_qindex = df[,c("q_id","q_sn")] %>% distinct   ## 對每一大題+小題編號取unique
df_qindex %<>% na.omit()                         ## 移除空白列
ls_output = list()                               ## 建立空白list
parsing_err = 0

for (i in 1 : nrow(df_qindex)){                  ## 針對每一小題設定值建立JSON Object
  df_tmp = df[q_id == df_qindex[i]$q_id][q_sn == df_qindex[i]$q_sn]
  df_tmp %<>% lapply(function(x) replace(x, is.na(x), ''))    ## 未設定之欄位標註為空白
  
  ## 開始對每一小題取值
  ## 允許非unique的欄位: opt_txt, opt_value, escape, target, note, disjoint
  ## 其餘欄位在每小題中必須為unique設定
  ls_tmp = list()
  ls_tmp$q_id     = df_qindex[i]$q_id
  ls_tmp$q_sn     = df_qindex[i]$q_sn
  ls_tmp$q_txt    = df_tmp$q_txt %>% unique
  ls_tmp$type     = df_tmp$type %>% unique
  ls_tmp$sub_type = df_tmp$sub_type %>% unique
  # opt_txt = trimws(df_tmp$opt_txt)
  ls_tmp$opt_txt    = trimws(df_tmp$opt_txt)
  ls_tmp$opt_value  = df_tmp$opt_value
  ls_tmp$escape     = df_tmp$escape
  ls_tmp$target_special_code     = df_tmp$target_special_code

  ls_tmp$escaped_code = df_tmp$escaped_code %>% unique
  ls_tmp$escape_range = df_tmp$escape_range %>% unique
  ls_tmp$answer_limitation = df_tmp$answer_limitation %>% unique
  ls_tmp$answer_limitation_ref = df_tmp$answer_limitation_ref %>% unique
  
  if(df_tmp$target %>% n_distinct() > 1){
    ls_tmp$target = df_tmp$target
  }else if(df_tmp$target %>% unique() == ""){
    ls_tmp$target = df_tmp$target %>% unique
  }else{
    ls_tmp$target = df_tmp$target  
  }
  
  if(df_tmp$target_limitation %>% n_distinct() > 1){
    ls_tmp$target_limitation = df_tmp$target_limitation
  }else if(df_tmp$target_limitation %>% unique() == ""){
    ls_tmp$target_limitation = df_tmp$target_limitation %>% unique
  }else{
    ls_tmp$target_limitation = df_tmp$target_limitation  
  }

  ls_tmp$annotate = df_tmp$annotate %>% unique
  ls_tmp$note = df_tmp$note
  ls_tmp$range = df_tmp$range %>% unique
  ls_tmp$prefill = df_tmp$prefill %>% unique
  ls_tmp$prefill_escape = df_tmp$prefill_escape %>% unique ##預過錄不須填答
  ls_tmp$prefill_range = df_tmp$prefill_range %>% unique ##預過錄數值範圍
  ls_tmp$special_code = df_tmp$special_code %>% unique
  ls_tmp$ck_target = 0
  ls_tmp$ck_pre_target = 0
  ls_tmp$ck_pre_tmp = 0
  
  if(df_tmp$disjoint %>% n_distinct() > 1){
    ls_tmp$disjoint = df_tmp$disjoint
  }else if(df_tmp$disjoint %>% unique() == ""){
    ls_tmp$disjoint = df_tmp$disjoint %>% unique  
  }else{
    ls_tmp$disjoint = df_tmp$disjoint
  }
  
  ls_tmp$random_order = df_tmp$random_order %>% unique
  
  # ## 輸入至output前先檢查
  # tmp_vec = Filter(function(x){length(x)>1 },ls_tmp) %>% names
  
  # ## 若有長度異常欄位，終止迴圈
  # if (tmp_vec[!tmp_vec %in% c("opt_txt", "opt_value", "escape", "target", "note", "disjoint")] %>% length) {
  #   colname = tmp_vec[!tmp_vec %in% c("opt_txt", "opt_value", "escape", "target", "note", "disjoint")]
  #   msg = paste0("第" , ls_tmp$q_id , "大題 - 第" , ls_tmp$q_sn , "小題的" , colname %>% paste0(.,collapse = ', ') , "欄位設定有誤")
  #   cat(msg)
  #   parsing_err = 1
  #   break
  # }
  
  # ## 檢查通過則寫入list中
  ls_output[[i]] = ls_tmp 
}
# print(parsing_err)
if (parsing_err == 0){
  json_output = ls_output %>% toJSON(auto_unbox = T, pretty = T)
  
  con <- dbConnect(dbDriver("MySQL"), 
                  host = "localhost", 
                  user = "***", 
                  password = "***", 
                  dbname = "***")
  
  n_setting = paste0("SELECT * FROM `questionnaire` WHERE qid =",qid," AND prjid = ",prjid) %>% dbGetQuery(con,.) %>% nrow
  
  if (n_setting == 0) {
    cat("Insert_", prjid ,"_", qid ,"\n", sep = '')
    dbSendQuery(con, 'SET NAMES utf8')
    paste0("INSERT INTO `questionnaire`(`qid`, `prjid`, `q_setting`, `filename`, `createtime`) VALUES ('",qid,"', '",prjid,"', '",json_output,"', '",fpath,"', NOW())") %>% dbSendQuery(con,.)
  } else {
    cat("Update_", prjid ,"_", qid ,"\n", sep = '')
    dbSendQuery(con, 'SET NAMES utf8')
    paste0("UPDATE `questionnaire` SET `q_setting`='", json_output ,"',`filename`='", fpath ,"',`update_time`=NOW() WHERE qid = ", qid ," AND prjid = ", prjid) %>% dbSendQuery(con,.)
  }
}
