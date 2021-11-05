# install.packages("pacman", repos="http://cran.us.r-project.org")
library(pacman)
p_load("MASS", "data.table", "magrittr", "dplyr", "stringr", "jsonlite", "zoo", "chron", "RMySQL")

args=commandArgs(TRUE)
project_id=args[1]
filepath=args[2]
# project_id="1"
# path="C:/***"
path=paste0('C:/***', '/', filepath)

data=fread(path)
data_qindex=data[,c("q_id","q_sn")] %>% distinct
data_qindex %<>% na.omit()
ls_output=list()


for(i in 1:nrow(data_qindex)){
  data_tmp=data[q_id==data_qindex[i]$q_id & q_sn==data_qindex[i]$q_sn]
  data_tmp %<>% lapply(function(x) replace(x, is.na(x), ''))

  ls_tmp=list()
  ls_tmp$q_id=data_qindex[i]$q_id
  ls_tmp$q_sn=data_qindex[i]$q_sn
  ls_tmp$q_txt=trimws(data_tmp$q_txt) %>% unique
  ls_tmp$type=data_tmp$type %>% unique
  ls_tmp$opt_txt=trimws(data_tmp$opt_txt)
  ls_tmp$opt_value=data_tmp$opt_value
  
  ls_tmp$annotate=data_tmp$annotate %>% unique
  ls_tmp$note=data_tmp$note
  ls_tmp$disjoint=data_tmp$disjoint
  ls_tmp$range_min=data_tmp$range_min %>% unique
  ls_tmp$range_max=data_tmp$range_max %>% unique
  ls_tmp$skip=data_tmp$skip
  ls_tmp$attach=data_tmp$attach %>% unique
  ls_tmp$random=data_tmp$random %>% unique
  
  ls_output[[i]]=ls_tmp
}
json_output=ls_output %>% toJSON(auto_unbox=T, pretty=T)

connect<-dbConnect(dbDriver("MySQL"), host="***", user="***", password="***", dbname="***")
dbSendQuery(connect, 'SET NAMES UTF8')
paste0("UPDATE `project` SET `csv_schema`='", json_output, "'WHERE `project_id`='", project_id, "'") %>% dbSendQuery(connect,.)
