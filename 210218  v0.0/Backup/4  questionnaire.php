<?
if (!isset($_SESSION)) {
        session_start();
    }
    
include_once("db_k.php");
include_once("upload_func.php");

?>
<!DOCTYPE html>
<html>
<head>
    <title>CAPI問卷</title>
    <meta http-equiv="Content-Type" content="text/html"  charset="utf-8">
    <meta name="viewport" content="width=device-width" initial-scale="1">
    <script src="https://capi.geohealth.tw/js/jquery-3.1.1.js"></script>
    <!-- Bootstrap 3 -->   
    <script src="https://capi.geohealth.tw/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <link   rel="stylesheet" href="https://capi.geohealth.tw/css/bootstrap.min.css" crossorigin="anonymous" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"> 
    <!-- Bootstrap Select -->
    <script src="https://capi.geohealth.tw/js/bootstrap-select.min.js"></script>
    <link   rel="stylesheet" href="https://capi.geohealth.tw/css/bootstrap-select.min.css">
    <!-- lodash.js -->
    <script src="https://capi.geohealth.tw/js/lodash.min.js"></script>
    <!-- JQuery Confirm-->
    <script src="https://capi.geohealth.tw/js/jquery-confirm.min.js"></script>
    <link   rel="stylesheet" href="https://capi.geohealth.tw/css/jquery-confirm.min.css">
    <!-- Dexie -->
    <script src="https://capi.geohealth.tw/js/dexie.js"></script>
    <script type="text/javascript">
        var q_info, tmp_project;
        var village, city, town;
        var survey_starttime;
        var ans_arr = [];
        var sample_rules;
        var sample_info = [];
        var sample_clicktimes = 0;
        var qnum;
        var vill;
        var special_codes = [];
        var find_id;
        var temp_ans;
        var uniq_array = [];
        var dynamic_ids = new Array();
        var tmp_fdata = [];
        var contents
        
        $(document).ready(function(){

            $.fn.selectpicker.Constructor.DEFAULTS.dropupAuto = false;
            
            FetchQuestionnaire(); 
            $('.selectpicker').selectpicker({
                'selectedTextFormat': 'count > 1',
                'countSelectedText': "{0} 個項目已勾選"
            })
            $(document).on('click', "#Goback" ,function(evt){
                evt.preventDefault();
                window.location.href = 'https://capi.geohealth.tw/php/selprj.php';
            })

            $(document).on('click', "#SurveyTerminate" ,function(evt){
                evt.preventDefault();
                $("#Md_Terminate").modal('show')
            })

            $(document).on('click', "#btn_editanswer" ,function(evt){
                evt.preventDefault();
                find_id = $(".page:visible").attr('id').split('_')[1]
                $('.btn_falBback').attr({'name': find_id})
                $('.btn_find').toggle(false);
                _.filter(ans_arr, function(o) { 

                    $('button[name="'+o.num+'_'+o.num_n+'"]').toggle(true);
                });

                $("#Md_Editanswer").modal('show');
            })

            $(document).on('click', "#btn_stop" ,function(evt){
                evt.preventDefault();
                $("#Md_Terminate").modal('show')
            })
        });
        

        function FormatVillList(result){
            // console.log(result)
            village = _.each(result, item => item.zip = parseInt(item.zip, 10));
            // console.log(village)
            city = _.sortBy(_.uniqBy(village, 'city'), ['zip']);
            town = _.sortBy(_.uniqBy(village, ('town', 'zip')), ['zip']);
            vill = _.sortBy(_.uniqBy(village, ('vill_code')), ['zip']);
        }

        function FetchQuestionnaire(){
           
            // Params
            const myParams = JSON.parse(localStorage.getItem('current_case'));
            const myParam_prjid = myParams.prjid
            const myParam_Sid   = myParams.sid
            console.log(myParam_prjid, myParam_Sid,localStorage.getItem('current_case'))
            var db = new Dexie("CAPI");
            db.version(1).stores({
                project: "prjid",                       // 專案清單
                prjwork: "[prjid+Sid]",            // 樣本清單
                questionnaire: "++tkey, [prjid+qid]",   // 問卷設定清單
                sample_status: "[prjid+Sid]",                   // 訪問代碼
                ans: '[prjid+Sid]',                             // 問卷結果
                vill_list: "++tkey",                     // 村里清單
                check: "[prjid+Sid]",
                rule: 'prjid',
                contents: 'prjid'
            });
            
            
            db.transaction('rw', db.prjwork, db.check, db.rule,   async(obj) => {

                // Query:
                return Dexie.Promise.all(
                    db.prjwork.where('[prjid+Sid]').equals([myParam_prjid, myParam_Sid]).toArray(),   
                )
                
            }).then (function (rs) {

                if(rs.length > 0){
                    console.log(rs)
                    var tmp_caseinfo = _.omit(rs[0][0], ['tkey'])
                    
                    console.log('rs')
                    console.log(rs)
                    
                    console.log('rs[0][0]')
                    console.log(rs[0][0])
                    
                    console.log('tmp_caseinfo')
                    console.log(tmp_caseinfo)
                    
                    
                    console.log('tmp_caseinfo.prjid, tmp_caseinfo.Qid')
                    console.log(tmp_caseinfo.prjid, tmp_caseinfo.Qid)
                    return Dexie.Promise.all(
                        tmp_caseinfo,
                        db.questionnaire.where('[prjid+qid]').equals([tmp_caseinfo.prjid, tmp_caseinfo.Qid]).toArray(),
                        db.vill_list.toArray(),
                        db.check.where('[prjid+Sid]').equals([myParam_prjid, myParam_Sid]).toArray(),
                        db.rule.where('prjid').equals(myParam_prjid).toArray(),
                        db.project.where('prjid').equals(myParam_prjid).toArray(),
                        db.contents.where('prjid').equals(myParam_prjid).toArray(),
                    )
                }
            }).then (function (rs) {
                // console.log(rs)
                if(rs.length > 0){
                    var tmp_caseinfo = rs[0];
                    var tmp_questinfo = _.omit(rs[1][0], ['tkey'])
                    tmp_project = rs[5][0]
                    console.log(tmp_project)
                    contents = rs[6][0]
                    console.log("樣本資訊", tmp_caseinfo)
                    console.log("問卷設定", tmp_questinfo)
                    console.log("抽樣規則:", sample_rules)
                    console.log("開始訪問內容:", contents)
                    q_info = _.merge(tmp_caseinfo, tmp_questinfo)

                    if(tmp_project.style == "0"){
                        var sample_rules = _.omit(rs[4][0], ['prjid', 'prjname'])

                        $('.style1').prop('disabled', false);
                        $('.style0').prop('disabled', true);
                        $('.selectpicker').selectpicker('refresh');

                    }else if(tmp_project.style == "1"){

                        $('.style1').prop('disabled', true);
                        $('.style0').prop('disabled', false);
                        $('.selectpicker').selectpicker('refresh');
                        
                    }
                    
                    DisplaySampleInfo(q_info, sample_rules, tmp_project.style);
                    if(rs[3].length == 0){
                        db.check.put(q_info)
                        
                    }
                    
                    console.log("q_info", q_info)

                    FormatVillList(rs[2])
                    
                    DisplayQuestions(q_info);
                }
            }).catch(e => {
                console.error(e.stack || e);
            });
            
        
        }

        // function DisplaySampleInfo2(fdata){
        //     var panel_bd = $("#case_info").children(".panel-body").attr({'id': 'sample_info'});
        //     $("<div>").attr({'class': 'col-sm-12'}).html("樣本編號 : " + fdata.Sid).appendTo(panel_bd)
        //     $("<div>").attr({'class': 'col-sm-12'}).html("問卷別 : "   + fdata.Qid).appendTo(panel_bd)
        //     $("<div>").attr({'class': 'col-sm-12'}).html("樣本地址 : " + fdata.address).appendTo(panel_bd)
        //     $("<hr>").appendTo(panel_bd)
        //     var tmp_html = ""
        //     // for(var content_i = 0; content_i < contents.content.split(";").length; content_i++){
        //     //     if(content_i != contents.content.split(";").length-1){
        //     //         tmp_html += contents.content.split(";")[content_i] + "<br>"    
        //     //     }else{
        //     //         tmp_html += contents.content.split(";")[content_i]
        //     //     }
                
        //     // }
        //     // $("<div>").append(
        //     //     $("<div>").attr({'class': 'col-sm-12'}).html(tmp_html)
        //     // ).append(
        //     //     div_household_total
        //     // ).appendTo(panel_bd)
        //     // $("<div>").attr({'id': 'sample_rule_text', 'class': 'col-sm-12'}).appendTo(panel_bd)
        //     // div_sampling_target_name.appendTo(panel_bd)
        //     // div_sampling_istarget.appendTo(panel_bd)
        //     // div_sampling_record.appendTo(panel_bd)
            
        // }

        function DisplaySampleInfo(fdata, sample_rules, style){

            var panel_bd = $("#case_info").children(".panel-body").attr({'id': 'sample_info'});
            
            // console.log(fdata)
            if(style == "0"){
                $("<div>").attr({'class': 'col-sm-12'}).html("樣本編號 : " + fdata.Sid).appendTo(panel_bd)
                $("<div>").attr({'class': 'col-sm-12'}).html("問卷別 : "   + fdata.Qid).appendTo(panel_bd)
                $("<div>").attr({'class': 'col-sm-12'}).html("樣本地址 : " + fdata.address).appendTo(panel_bd)
                $("<hr>").appendTo(panel_bd)
                if(fdata.target_name == null || fdata.target_name == ""){
                    // 未執行戶中抽樣
                    sample_status = 0;
                    // 戶中抽樣
                    var div_household_total = $('<div>').attr({'class': 'btn-group col-sm-6','data-toggle':'buttons', 'id':'household'});
                        for(var i = 1; i <= 6; i++){
                            if(i == 6){
                                var v_text = '≥' + i
                            }else{
                                var v_text = i
                            }
                            var opts = $('<input>').attr({  'class': '',
                                                            'type': 'radio',
                                                            'name': 'household_total',
                                                            'value': i,
                                                             required: true});
                            var lbl = $('<label>').html(v_text).attr({'class': 'btn btn-default radio_lbl'})
                            opts.appendTo(lbl)
                            lbl.appendTo(div_household_total)
                        }
                    var div_sampling_target_name =  $("<div>").append(
                                                        $('<div>').attr({'class': 'col-sm-12'}).html('中選者姓名')
                                                    ).append(
                                                        $('<div>').attr({'class': 'col-sm-6'}).append(
                                                                $('<input>').attr({ 'class': '',
                                                                                    'type': 'text',
                                                                                    'name': 'target_name',
                                                                                    'class': 'form-control',
                                                                                     required: true})
                                                        )
                                                    )
                    var div_sampling_istarget = $("<div>").append(
                                                    $('<div>').attr({'class': 'col-sm-12'}).html('應門者是否為中選者')
                                                ).append(
                                                    $('<div>').attr({'class': 'btn-group col-sm-6','data-toggle':'buttons'}).append(
                                                        $('<label>').html('是').attr({'class': 'btn btn-default radio_lbl col-sm-1', 'style': 'text-align: left;'}).append(
                                                            $('<input>').attr({ 'class': '',
                                                                                    'type': 'radio',
                                                                                    'name': 'istarget',
                                                                                    'value': 1,
                                                                                     required: true})
                                                        )
                                                    ).append(
                                                        $('<label>').html('否').attr({'class': 'btn btn-default radio_lbl col-sm-1', 'style': 'text-align: left;'}).append(
                                                            $('<input>').attr({ 'class': '',
                                                                                    'type': 'radio',
                                                                                    'name': 'istarget',
                                                                                    'value': 2,
                                                                                     required: true})
                                                        )
                                                    )
                                                )
                    var div_sampling_istarget2 = $("<div>").append(
                                                    $('<div>').attr({'class': 'col-sm-12'}).html('現在可以開始訪問')
                                                ).append(
                                                    $('<div>').attr({'class': 'btn-group col-sm-6','data-toggle':'buttons'}).append(
                                                        $('<label>').html('是 (進入開始訪問)').attr({'class': 'btn btn-default radio_lbl col-sm-12', 'style': 'text-align: left;'}).append(
                                                            $('<input>').attr({ 'class': '',
                                                                                    'type': 'radio',
                                                                                    'name': 'istarget2',
                                                                                    'value': 1,
                                                                                     required: true})
                                                        )
                                                    ).append(
                                                        $('<label>').html('否,不需要輸入聯絡電話').attr({'class': 'btn btn-default radio_lbl col-sm-12', 'style': 'text-align: left;'}).append(
                                                            $('<input>').attr({ 'class': '',
                                                                                    'type': 'radio',
                                                                                    'name': 'istarget2',
                                                                                    'value': 2,
                                                                                     required: true})
                                                        )
                                                    ).append(
                                                        $('<label>').html('否,需要輸入聯絡電話').attr({'class': 'btn btn-default radio_lbl col-sm-12', 'style': 'text-align: left;'}).append(
                                                            $('<input>').attr({ 'class': '',
                                                                                    'type': 'radio',
                                                                                    'name': 'istarget2',
                                                                                    'value': 0,
                                                                                     required: true})
                                                        )
                                                    )
                                                )                        
                    var div_sampling_record =   $("<div>").attr({'id': 'div_sampling_record','style': 'display: none;'}).append(
                                                    $("<div>").attr({'id': 'sample_info_text','class': 'col-sm-12', 'style': 'border: groove'}).html("由於○○○是中選的受訪者，我們想訪問他/她。<br>在訪問結束後，將致贈200元的超商商品卡給他/她，以答謝他/她的幫忙。<br>請問怎麼聯絡他/她？您所提供的聯絡資訊，我們會依個資法的規定妥善保管，絕對不會外洩或給別人使用，<br>調查工作結束後也會銷毀，請您放心！")

                                                 ).append(
                                                    $("<div>").append(
                                                        $('<div>').attr({'class': 'col-sm-12'}).html('中選者聯絡電話(市話 & 分機)')
                                                    ).append(
                                                        $('<div>').attr({'class': 'col-sm-6','style': 'display: inline-flex;'}).append(
                                                            $('<input>').attr({ 'class': '',
                                                                                'type': 'tel',
                                                                                'name': 'target_tel',
                                                                                'class': 'form-control',
                                                                                'placeholder': '例: 0226525100',
                                                                                })
                                                        ).append(
                                                            $('<input>').attr({ 'class': '',
                                                                                'type': 'tel',
                                                                                'name': 'target_ext',
                                                                                'class': 'form-control',
                                                                                'placeholder': '5碼，無則填0',
                                                                                })
                                                        )
                                                    )
                                                ).append(
                                                    $("<div>").append(
                                                        $('<div>').attr({'class': 'col-sm-12'}).html('中選者聯絡電話(手機)')
                                                    ).append(
                                                        $('<div>').attr({'class': 'col-sm-6','style': 'display: inline-flex;'}).append(
                                                            $('<input>').attr({ 'class': '',
                                                                                'type': 'tel',
                                                                                'name': 'target_mobile',
                                                                                'class': 'form-control',
                                                                                'placeholder': '例: 0912345678',
                                                                                })
                                                        )
                                                    )
                                                )
                    // Appending elements
                    // console.log('contents:', contents)
                    var tmp_html = ""
                    for(var content_i = 0; content_i < contents.content.split(";").length; content_i++){
                        if(content_i != contents.content.split(";").length-1){
                            tmp_html += contents.content.split(";")[content_i] + "<br>"    
                        }else{
                            tmp_html += contents.content.split(";")[content_i]
                        }
                        
                    }
                    $("<div>").append(
                        $("<div>").attr({'class': 'col-sm-12'}).html(tmp_html)
                    ).append(
                        div_household_total
                    ).appendTo(panel_bd)
                    $("<div>").attr({'id': 'sample_rule_text', 'class': 'col-sm-12'}).appendTo(panel_bd)
                    div_sampling_target_name.appendTo(panel_bd)
                    div_sampling_istarget.appendTo(panel_bd)
                    div_sampling_istarget2.appendTo(panel_bd)
                    div_sampling_record.appendTo(panel_bd)
                }else{
                    // 已完成戶中抽樣並記錄中選者資訊
                    sample_status = 1;
                    // 戶中抽樣
                    var div_household_total = $('<div>').attr({'class': 'btn-group col-sm-6','data-toggle':'buttons', 'id':'household'});
                        for(var i = 1; i <= 6; i++){
                            if(i == 6){
                                var v_text = '≥' + i
                            }else{
                                var v_text = i
                            }
                            var opts = $('<input>').attr({  'class': '',
                                                            'type': 'radio',
                                                            'name': 'household_total',
                                                            'value': i,
                                                             required: true});
                            var lbl = $('<label>').html(v_text).attr({'class': 'btn btn-default radio_lbl'})
                            opts.appendTo(lbl)
                            lbl.appendTo(div_household_total)
                        }
                    var div_sampling_target_name =  $("<div>").append(
                                                        $('<div>').attr({'class': 'col-sm-12'}).html('中選者姓名')
                                                    ).append(
                                                        $('<div>').attr({'class': 'col-sm-6'}).append(
                                                                $('<input>').attr({ 'class': '',
                                                                                    'type': 'text',
                                                                                    'name': 'target_name',
                                                                                    'class': 'form-control',
                                                                                     required: true})
                                                        )
                                                    )
                    var div_sampling_istarget = $("<div>").append(
                                                    $('<div>').attr({'class': 'col-sm-12'}).html('應門者是否為中選者')
                                                ).append(
                                                    $('<div>').attr({'class': 'btn-group col-sm-6','data-toggle':'buttons'}).append(
                                                        $('<label>').html('是').attr({'class': 'btn btn-default radio_lbl col-sm-12', 'style': 'text-align: left;'}).append(
                                                            $('<input>').attr({ 'class': '',
                                                                                    'type': 'radio',
                                                                                    'name': 'istarget',
                                                                                    'value': 1,
                                                                                     required: true})
                                                        )
                                                    ).append(
                                                        $('<label>').html('否').attr({'class': 'btn btn-default radio_lbl col-sm-12', 'style': 'text-align: left;'}).append(
                                                            $('<input>').attr({ 'class': '',
                                                                                    'type': 'radio',
                                                                                    'name': 'istarget',
                                                                                    'value': 2,
                                                                                     required: true})
                                                        )
                                                    )
                                                )
                    var div_sampling_istarget2 = $("<div>").append(
                                                    $('<div>').attr({'class': 'col-sm-12'}).html('現在可以開始訪問')
                                                ).append(
                                                    $('<div>').attr({'class': 'btn-group col-sm-6','data-toggle':'buttons'}).append(
                                                        $('<label>').html('是 (進入開始訪問)').attr({'class': 'btn btn-default radio_lbl col-sm-12', 'style': 'text-align: left;'}).append(
                                                            $('<input>').attr({ 'class': '',
                                                                                    'type': 'radio',
                                                                                    'name': 'istarget2',
                                                                                    'value': 1,
                                                                                     required: true})
                                                        )
                                                    ).append(
                                                        $('<label>').html('否,不需要輸入聯絡電話').attr({'class': 'btn btn-default radio_lbl col-sm-12', 'style': 'text-align: left;'}).append(
                                                            $('<input>').attr({ 'class': '',
                                                                                    'type': 'radio',
                                                                                    'name': 'istarget2',
                                                                                    'value': 2,
                                                                                     required: true})
                                                        )
                                                    ).append(
                                                        $('<label>').html('否,需要輸入聯絡電話').attr({'class': 'btn btn-default radio_lbl col-sm-12', 'style': 'text-align: left;'}).append(
                                                            $('<input>').attr({ 'class': '',
                                                                                    'type': 'radio',
                                                                                    'name': 'istarget2',
                                                                                    'value': 0,
                                                                                     required: true})
                                                        )
                                                    )
                                                )  
                    var div_sampling_record =   $("<div>").attr({'id': 'div_sampling_record','style': 'display: none;'}).append(
                                                    $("<div>").attr({'id': 'sample_info_text','class': 'col-sm-12', 'style': 'border: groove'}).html("由於○○○是中選的受訪者，我們想訪問他/她。<br>在訪問結束後，將致贈200元的超商商品卡給他/她，以答謝他/她的幫忙。<br>請問怎麼聯絡他/她？您所提供的聯絡資訊，我們會依個資法的規定妥善保管，絕對不會外洩或給別人使用，<br>調查工作結束後也會銷毀，請您放心！")

                                                 ).append(
                                                    $("<div>").append(
                                                        $('<div>').attr({'class': 'col-sm-12'}).html('中選者聯絡電話(市話 & 分機)')
                                                    ).append(
                                                        $('<div>').attr({'class': 'col-sm-6','style': 'display: inline-flex;'}).append(
                                                            $('<input>').attr({ 'class': '',
                                                                                'type': 'tel',
                                                                                'name': 'target_tel',
                                                                                'class': 'form-control',
                                                                                'placeholder': '例: 0226525100',
                                                                                })
                                                        ).append(
                                                            $('<input>').attr({ 'class': '',
                                                                                'type': 'tel',
                                                                                'name': 'target_ext',
                                                                                'class': 'form-control',
                                                                                'placeholder': '5碼，無則填0',
                                                                                })
                                                        )
                                                    )
                                                ).append(
                                                    $("<div>").append(
                                                        $('<div>').attr({'class': 'col-sm-12'}).html('中選者聯絡電話(手機)')
                                                    ).append(
                                                        $('<div>').attr({'class': 'col-sm-6','style': 'display: inline-flex;'}).append(
                                                            $('<input>').attr({ 'class': '',
                                                                                'type': 'tel',
                                                                                'name': 'target_mobile',
                                                                                'class': 'form-control',
                                                                                'placeholder': '例: 0912345678',
                                                                                })
                                                        )
                                                    )
                                                )
                    // Appending elements
                    console.log('contents:', contents.content.split(";"))
                    var tmp_html = ""
                    for(var content_i = 0; content_i < contents.content.split(";").length; content_i++){
                        if(content_i != contents.content.split(";").length-1){
                            tmp_html += contents.content.split(";")[content_i] + "<br>"    
                        }else{
                            tmp_html += contents.content.split(";")[content_i]
                        }
                        
                    }
                    $("<div>").append(
                        $("<div>").attr({'class': 'col-sm-12'}).html(tmp_html)
                    ).append(
                        div_household_total
                    ).appendTo(panel_bd)
                    $("<div>").attr({'id': 'sample_rule_text', 'class': 'col-sm-12'}).appendTo(panel_bd)
                    div_sampling_target_name.appendTo(panel_bd)
                    div_sampling_istarget.appendTo(panel_bd)
                    div_sampling_istarget2.appendTo(panel_bd)
                    div_sampling_record.appendTo(panel_bd)

                    $('input[name="household_total"][value="'+fdata.sample_household_total+'"]').parent().addClass('active')
                    $('input[name="target_name"]').val(fdata.target_name)
                    $('input[name="istarget"][value="'+fdata.istarget+'"]').parent().addClass('active')
                    $('input[name="istarget2"][value="'+fdata.access+'"]').parent().addClass('active')
                    $('input[name="target_tel"]').val(fdata.target_tel)
                    $('input[name="target_ext"]').val(fdata.target_ext)
                    $('input[name="target_mobile"]').val(fdata.target_mobile)

                    if(fdata.access == 0){
                        $("#div_sampling_record").show()
                        $("#sample_info_text").show()
                    }else{
                        $("#div_sampling_record").hide()
                    }
                    // $("<div>").attr({'class': 'col-sm-12'}).html("中選者姓名 : " + fdata.target_name).appendTo(panel_bd)
                    // $("<div>").attr({'class': 'col-sm-12'}).html("中選者聯絡電話(市話) : " + fdata.target_tel + " 分機 : " + fdata.target_ext).appendTo(panel_bd)
                    // $("<div>").attr({'class': 'col-sm-12'}).html("中選者聯絡電話(手機) : " + fdata.target_mobile).appendTo(panel_bd)
                }  
            }else if(style == "1"){
                $("<div>").attr({'class': 'col-sm-12'}).html("樣本編號 : " + fdata.Sid).appendTo(panel_bd)
                $("<div>").attr({'class': 'col-sm-12'}).html("姓名 : " + fdata.name).appendTo(panel_bd)
                $("<div>").attr({'class': 'col-sm-12'}).html("性別 : " + fdata.gender).appendTo(panel_bd)
                $("<div>").attr({'class': 'col-sm-12'}).html("生日 : " + fdata.birthday).appendTo(panel_bd)
                $("<div>").attr({'class': 'col-sm-12'}).html("樣本地址 : " + fdata.address).appendTo(panel_bd)
                
                
                
                // $('#sample_info').prepend($("<div>").attr({'class': 'col-sm-12'}).html("樣本姓名 : " + fdata.name))
                // $('#sample_info').prepend($("<div>").attr({'class': 'col-sm-12'}).html("樣本性別 : " + fdata.gender))
                // $('#sample_info').prepend($("<div>").attr({'class': 'col-sm-12'}).html("樣本生日 : " + fdata.birthday))
                
                if(fdata.target_mobile != undefined){
                    // $('#sample_info').prepend($("<div>").attr({'class': 'col-sm-12'}).html("樣本電話 : " + fdata.target_mobile))
                    $("<div>").attr({'class': 'col-sm-12'}).html("電話 : "   + fdata.target_mobile).appendTo(panel_bd)
                }
                $("<div>").attr({'class': 'col-sm-12'}).html("問卷別 : "   + fdata.Qid).appendTo(panel_bd)
                $("<hr>").appendTo(panel_bd)
                // $("<div>").attr({'class': 'col-sm-12'}).html("樣本生日 : " + fdata.birthday).prepend($('#hr'))
                var tmp_html = ""
                    for(var content_i = 0; content_i < contents.content.split(";").length; content_i++){
                        if(content_i != contents.content.split(";").length-1){
                            tmp_html += contents.content.split(";")[content_i] + "<br>"    
                        }else{
                            tmp_html += contents.content.split(";")[content_i]
                        }
                        
                    }
                    $("<div>").append(
                        $("<div>").attr({'class': 'col-sm-12'}).html(tmp_html)
                    ).append(
                        div_household_total
                    ).appendTo(panel_bd)
            }
            

            $("<div>").attr({'class': 'col-sm-12','align': 'center'}).append(
                $("<button>").html("返回").attr({'id': 'Goback',
                                                 'class': 'btn btn-info',
                                                 'style': 'margin-top: 2em;'})
            ).append(
                $("<button>").html("終止訪問").attr({'id': 'SurveyTerminate',
                                                    'class': 'btn btn-danger',
                                                    'style': 'margin-top: 2em;'})
            ).append(
                $("<button>").html("開始訪問").attr({'id': 'SurveyStart',
                                                    'class': 'btn btn-success',
                                                    'style': 'margin-top: 2em;'})
            ).appendTo(panel_bd)

            $("input[name='household_total'").on('change', function(evt){
                // console.log(sample_rules)
                var rule_type = Number(q_info.sample_rule);
                var household_total = Number($(this).val());
                var sample_rs = _.filter(sample_rules, { 'type': rule_type, 'household_total': household_total });
                $("#sample_rule_text").empty().html("抽樣規則 : " + sample_rs[0].result).attr({'style': 'color: blue'})
                // Record of Sampling Btn click times
                sample_clicktimes = sample_clicktimes + 1; 
                // console.log(sample_clicktimes)
            })

            $("input[name='istarget2'").on('change', function(evt){
                var tmp = $(this).val();
                // console.log(tmp)
                if(tmp == 0){
                    $("#div_sampling_record").show()
                    $("#sample_info_text").show()
                }else{
                    $("#div_sampling_record").hide()
                }
            })
        }

        function DisplayQuestions(fdata){

            
            $(document).on('click', "#SurveyStart" ,function(evt){
           
                // 1. Check Records of Sampling
                if(CheckTargetinfo("SurveyStart") == false){

                }else{
                    // Display Sample Info on top
                    var panel_bd = $("#case_info").children(".panel-body");
                    panel_bd.empty().attr({'style': 'font-size: 1em;'});
                    survey_starttime = new Date().toLocaleString();
                    
                    $("<div>").attr({'class': 'col-sm-4', 'style': 'font-weight: bold;font-size: 16px;'}).html("樣本編號 : " + fdata.Sid).appendTo(panel_bd)
                    $("<div>").attr({'class': 'col-sm-5', 'style': 'font-weight: bold;font-size: 16px;'}).html("開始訪問 : " + survey_starttime).appendTo(panel_bd)
                    $("<div>").attr({'class': 'col-sm-3', 'align': 'right'}).append(
                        // $("<button>").html("更改答案").attr({'id':'btn_editanswer','class': 'btn btn-success'})
                    ).append(
                        $("<button>").html("跳回答題").attr({'class': 'btn btn-warning btn_falBback', 'style': 'display: none'})
                    ).append(
                        $("<button>").html("中止訪問").attr({'id':'btn_stop','class': 'btn btn-danger'})
                    ).appendTo(panel_bd)

                    // Formatting Questions
                    // 總共有幾大題
                    var index_q_id = _.chain(fdata.q_setting).map('q_id').uniq().value();
                    // console.log(index_q_id)
                    // 每一大題的內容
                    _.map(index_q_id, function(i){
                        $("div.container").append(
                            $("<div>").attr({   'id': 'div_' + i,
                                                'class': 'panel panel-primary page'}).append(
                                $("<div>").html("第" + i + "大題").attr({'class': 'panel-heading'})
                            )
                        )
                        // 該大題的每一小題
                        var q_contents = _.filter(fdata.q_setting, { 'q_id': i})
                        // console.log(i, tmp)
                        _.map(q_contents, function(obj){
                            // console.log(obj)
                            var q_text   = obj.q_txt;
                            // console.log('test:', q_text)
                            var shorten_q_text = q_text.substring(0, 30);
                            var q_annotate = obj.annotate.split('。').join('<br>');
                            var q_inputs = DetectType(obj)
                            
                            if(obj.special_code != ""){
                                var arr_spe = new Array();
                                var tmp_arr = _.chain(obj.special_code).replace("[", "").replace("]", "").split(',').value()
                                // console.log(tmp_arr)
                                _.map(tmp_arr, function(o){
                                    var tmp  = o.split(':')
                                    var tmp_obj = new Object();
                                    tmp_obj.spe_txt = tmp[0]
                                    tmp_obj.spe_val = tmp[1]
                                    arr_spe.push(tmp_obj)
                                    arr_spe.qid = obj.q_id
                                    arr_spe.qsn = obj.q_sn
                                })
                                special_codes.push(arr_spe)
                                // console.log('special_code_each_qsn', arr_spe)
                                // console.log('special_code_each_qsn', special_codes)
                                if(obj.type == 6 && obj.q_sn != 1){
                                    var btn_specode =   $("<span>")
                                }else{
                                    var btn_specode =   $("<button>").attr({'id': 'btn_specode_'+ obj.q_id + '_' + obj.q_sn,
                                                                            'class' : 'btn btn-default btn-sm btn-specialcodes'}).append(
                                                            $("<span>").attr({'class' : 'glyphicon glyphicon-info-sign'})
                                                        )
                                }
                            }else{
                      
                                var btn_specode = $("<span>")
                            }

                            $("<div>").attr({'class': 'panel-body'}).append(
                                $("<div>").attr({'class': 'col-sm-12', 'id':'div_qtext_' + obj.q_id + obj.q_sn}).html(q_text).append(
                                    btn_specode
                                )
                            ).append(
                                $("<div>").attr({'class': 'col-sm-12','style': 'color: blue; font-size: 0.9em;'}).html(q_annotate)
                            ).append(
                                q_inputs
                            ).append(
                                (obj == _.last(q_contents) ?    $("<div>").attr({'class': 'col-sm-12','align': 'center'}).append(
                                                            (i == index_q_id[0] ? $("<button>").html("&#xe091上一題").attr({'id': 'btn_last_' + obj.q_id,
                                                                                             'class': 'btn btn-info glyphicon btn_last',
                                                                                             'style': 'margin-top: 2em;',
                                                                                             'disabled': true})
                                                                                : $("<button>").html("&#xe091上一題").attr({'id': 'btn_last_' + obj.q_id,
                                                                                             'class': 'btn btn-info glyphicon btn_last',
                                                                                             'style': 'margin-top: 2em;'})
                                                            )                                   
                                                        ).append(
                                                            (i ==_.last(index_q_id) ? $("<button>").html("完成訪問").attr({'id': 'btn_finish',
                                                                                             'class': 'btn btn-success glyphicon btn_finish',
                                                                                             'style': 'margin-top: 2em; '}) 
                                                                                    : $("<button>").html("&#xe092下一題").attr({'id': 'btn_next_' + obj.q_id,
                                                                                             'class': 'btn btn-info glyphicon btn_next',
                                                                                             'style': 'margin-top: 2em;'})
                                                            )
                                                        )
                                                    :   $("<div>")
                                )
                            ).appendTo(
                                $("#div_" + i)
                            )

                            $("#Md_Editanswer").children().children().children(".modal-body").append(
                                $("<div>").append(
                                    $("<button>").attr({'id': 'btn_find_' + obj.q_id,
                                                        'class': 'btn btn-default btn_find',
                                                        'name': obj.q_id+'_'+obj.q_sn,
                                                        'style': 'display: none;'
                                    }).html(obj.q_id + " - " + shorten_q_text)
                                )
                            )
                        })
                    })

                    
                    
                    
                    
                    $(".page").hide();
                    //縣市
                    $(".selectpicker").selectpicker("refresh")
                    $(".city").on('change',function(evt){
                        evt.preventDefault();
                        var sVal = $(this).attr('name');
                        // console.log('test:', sVal)
                        var st1 = town.filter(x=>x.city === $(".city[name='"+sVal+"']").val())
                        
                        $(".town[name='"+sVal+"']").empty();
                        $(".town[name='"+sVal+"']").append(
                            $('<option>', {value:'',text:'請選擇區'})
                        );
                        for(key1 in st1){
                            opt1 = $('<option>', {value:st1[key1].town,text:st1[key1].town})
                            $(".town[name='"+sVal+"']").append(
                                $(opt1)
                            );
                        }
                        $(".town[name='"+sVal+"']").append($('<option>', {value:'999',text:'不知道 / 拒答'}));
                        $(".town[name='"+sVal+"']").selectpicker("refresh");
                    }); 
                    $(".town").on('change',function(){  
                        var sVal = $(this).attr('name');            
                    }); 
                    
                    $(".btn-specialcodes").on('click', function(evt){
                        
                        evt.preventDefault();
                        var tmp = $(this).attr('id').split('_')
                        
                        // tmp = _.split(tmp, '_')
                        // console.log(tmp)
                        if(tmp.length == 4){
                            tmp = _.takeRight(tmp, 2)
                            var tmp_qid = Number(tmp[0])
                            var tmp_qsn = Number(tmp[1])
                            var id_btn_submit_specode = 'btn_submit_specode_' + tmp_qid + '_' + tmp_qsn;
                            var id_btn_cancel_specode = 'btn_cancel_specode_' + tmp_qid + '_' + tmp_qsn;
                            // _.chain(tmp).split('_').takeRight(2).value()
                        }else if(tmp.length == 5){
                            tmp = _.takeRight(tmp, 3)
                            var tmp_qid = Number(tmp[0])
                            var tmp_qsn = Number(tmp[1])
                            var tmp_col = Number(tmp[2])
                            var id_btn_submit_specode = 'btn_submit_specode_' + tmp_qid + '_' + tmp_qsn + '_' + tmp_col;
                            var id_btn_cancel_specode = 'btn_cancel_specode_' + tmp_qid + '_' + tmp_qsn + '_' + tmp_col;
                            // tmp = _.chain(tmp).split('_').takeRight(3).value()
                        }
                        
                        
                        var arr_specodes = _.filter(special_codes, {'qid': tmp_qid, 'qsn': tmp_qsn})
                        var rs_specode = _.filter(arr_specodes[0], 'spe_txt')
                        // console.log(arr_specodes, rs_specode)
                        var div_btns_specode = $("<div>").attr({'class': 'btn-group', 'data-toggle': 'buttons'})
                        _.map(rs_specode, function(obj){
                            var tmp_inputs = $("<div>").attr({'class': 'col-sm-12'}).append(
                                                $("<label>").html(obj.spe_txt).attr({
                                                    'class': 'btn btn-default radio_lbl',
                                                    'style': 'width: 100%;'
                                                }).append(
                                                    $("<input>").attr({ 'type': 'radio',
                                                                        'name': 'special_code_' + tmp_qid + '_' + tmp_qsn,
                                                                        'value': obj.spe_val,
                                                                        'style': 'display:none;'

                                                            })
                                                )
                                            )
                            tmp_inputs.appendTo(div_btns_specode)
                        })
                        $("#Md_SpecialCodes").find(".modal-body").empty().append(div_btns_specode).append(
                            $("<div>").attr({'align': 'center'}).append(
                                $("<button>").attr({'class': 'btn btn-success btn-md btn_submit_specode',
                                                'id': id_btn_submit_specode
                                            }).html('確認使用特殊碼')
                            ).append(
                                $("<button>").attr({'class': 'btn btn-warning btn-md btn_cancel_specode',
                                                'id': id_btn_cancel_specode
                                            }).html('取消，開啟一般選項')
                            )
                        )
                        $("#Md_SpecialCodes").modal('show');
                    })
                    
                }

                console.log("DisplayQuestions", fdata)
               putAns(fdata)

            })

            $(document).on('click', '.btn_finish', function(evt){
                evt.preventDefault();
                
                getAns(fdata.q_setting[fdata.q_setting.length-1].q_id, fdata)
                
                // console.log('btn_finish', fdata.Sid)
                var db = new Dexie("CAPI");
                db.version(1).stores({
                    project: "prjid",                       // 專案清單
                    prjwork: "[prjid+Sid]",            // 樣本清單
                    questionnaire: "++tkey, [prjid+qid]",   // 問卷設定清單
                    sample_status: "[prjid+Sid]",                   // 訪問代碼
                    ans: '[prjid+Sid]',                             // 問卷結果
                    vill_list: "++tkey",                     // 村里清單
                });
                db.prjwork.where("[prjid+Sid]").equals([fdata.prjid, fdata.Sid]).modify({
                    check_status: "3",
                    prjwork_status: "1",
                    status: "完訪: 100",
                });
                db.transaction('rw', db.prjwork, db.ans, async(obj) => {
                    // Query:
                    return Dexie.Promise.all(
                        db.prjwork.where("[prjid+Sid]").equals([fdata.prjid, fdata.Sid]).toArray(),
                        db.ans.where("[prjid+Sid]").equals([fdata.prjid, fdata.Sid]).toArray(),

                    )
                    
                }).then (function (obj) {

                    var downLoadFile = []

                    downLoadFile.push({'prjwork':obj[0][0]})
                    downLoadFile.push({'ans': obj[1][0]['ans']})

                    $('.testtest').attr({'data-obj': JSON.stringify(downLoadFile), 'name':obj[0][0]['Sid'], 'id':obj[0][0]['prjid']})
                }).catch(e => {
                    console.error(e.stack || e);
                });
                
                $.confirm({
                    title: '',
                    content: "確認是否結束訪問?",
                    buttons: {
                        confir: {
                            text: '確認',
                            btnClass: 'btn-blue confirm testtest',
                            action: function(){
                                
                                var name = $('.testtest').attr('id')+"_"+$('.testtest').attr('name')+".json"
                                console.log(name)
                                 $("<a />", {
                                        "download": name,
                                        "href" : "data:application/json;charset=utf-8," + encodeURIComponent(JSON.stringify($('.testtest').data().obj)),
                                    }).appendTo("body")
                                    .click(function() {
                                    $(this).remove()
                                })[0].click()
                                    window.location = 'https://capi.geohealth.tw/php/selprj.php'
                            }
                        },
                        cancle: {
                            text: '取消',
                            btnClass: 'btn-red confirm',
                            action: function(){
                                $.alert('已取消!!');
                            }
                        }
                    }
                    
                }); 
                    
                
               
                
            });
            function getAns(x, fdata) {
                // console.log('fdata getAns:', fdata)
                var q_type
                var ans_arr2 = []
                var element = fdata.q_setting.filter(function(element) {
                                if(element.q_id == x){
                                    return element;
                                }
                            })
                
                var q_length = fdata.q_setting.filter(function(element) {
                                    return element.q_id == x;
                                }).length
                
                for(var i = 1; i <= q_length ; i++){
                    
                    q_type = element[i-1].type
                    var tmp_title = $('#div_qtext_'+x+i).html().split('<')[0]
                    if(q_type != '6'){
                        var check_tmp = $('input[name="'+x+'_'+i+'"]').attr('placeholder');
                        
                        if(check_tmp){
                            var check_tmp2 = check_tmp.split('_')[0]

                        }
                    }

                    if(check_tmp && check_tmp2 == '1' && q_type != '6'){
                        console.log(check_tmp, check_tmp2)    

                       if(q_type == '1'){
                            var tmp_val = []
                            var tmp_body = []

                            tmp_val.push($('input[name="'+x+'_'+i+'"]').attr('placeholder').split('_')[2])
                            tmp_body.push($('input[name="'+x+'_'+i+'"]').attr('placeholder').split('_')[0] +'_'+ $('input[name="'+x+'_'+i+'"]').attr('placeholder').split('_')[1])

                        }else{
                            var tmp_val = $('input[name="'+x+'_'+i+'"]').attr('placeholder').split('_')[2]
                            var tmp_body = $('input[name="'+x+'_'+i+'"]').attr('placeholder').split('_')[0] +'_'+ $('input[name="'+x+'_'+i+'"]').attr('placeholder').split('_')[1]
                        } 


                    }else{
                        if(q_type == '0'){//單選 

                            var tmp = _.filter(fdata.q_setting, { 'q_id': parseInt(x), 'q_sn': parseInt(i) })
                            var tmp_val = $('input[name="'+x+'_'+i+'"]').parent('.active').find('input[name="'+x+'_'+i+'"]').val()

                            var note_v = $('#input_note_'+x+'_'+i+'_'+tmp_val).val()
                            // console.log('note_v:', note_v, 'tmp_val:',tmp_val, 'input_note_'+x+'_'+i+'_'+tmp_val)
                            // console.log('input[name="'+x+'_'+i+'"]','.class_'+tmp_val,note_v,tmp_val)
                            var tmp_index = _.indexOf(tmp[0].opt_value, tmp_val)
                            // console.log(note_v)
                            if(tmp_val == undefined){
                                tmp_val = undefined
                                $.alert('請填值')
                                evt
                            }else{
                                if(tmp[0].note[tmp_index] == '1' || tmp[0].note[tmp_index] == '2'){
                                    
                                    if(note_v.length < 1){
                                        tmp_val = undefined
                                    }else{
                                        tmp_val = tmp_val+'_'+note_v
                                        var tmp_body = $('input[name="'+x+'_'+i+'"]').parent('.active').html().split('<')[0]+'_'+note_v
                                    }
                                    
                                }else{
                                    var tmp_body = $('input[name="'+x+'_'+i+'"]').parent('.active').html().split('<')[0]
                                }

                            }
                            
                            // console.log(tmp_val, tmp_body)
                        }else if(q_type == '2'){

                            var tmp_val = $('input[name="'+x+'_'+i+'"]').val()
                            
                            var tmp_body = tmp_val

                            if(tmp_val.length < 1){
                                tmp_val = undefined
                            }

                        }else if(q_type == '3'){

                            var tmp_val = $('input[name="'+x+'_'+i+'"]').val()
                            var tmp_body = tmp_val

                            if(tmp_val.length < 1){
                                tmp_val = undefined

                            }
                        }else if(q_type == '1'){

                            var tmp_val = []
                            var tmp_body = []

                                $('input[name="'+x+'_'+i+'"]').each( function () {
                                    
                            if($('input[name="'+x+'_'+i+'"]').prop('disabled')){
                                var tmp = _.filter(fdata.q_setting, { 'q_id': parseInt(x), 'q_sn': parseInt(i) })
                                tmp_body.push(tmp_b.find('span').html() + ', ' + note_v)
                                tmp_val.push($('input[name="'+x+'_'+i+'"]').attr('placeholder').split('_')[2])
                            }else{

                                    var tmp = _.filter(fdata.q_setting, { 'q_id': parseInt(x), 'q_sn': parseInt(i) })

                                    var tmp_v = $(this).val()
                                    
                                    var tmp_b = $(this).parent()
                                    var note_v = $(this).parent().find('#input_note_'+x+'_'+i).val()
                                    var tmp_index = _.indexOf(tmp[0].opt_value, tmp_v)
                                    // console.log(tmp[0], tmp_v, 'input[name="'+x+'_'+i+'"][value="'+tmp_v+'"][type="checkbox"]')
                                    if($('input[name="'+x+'_'+i+'"][value="'+tmp_v+'"][type="checkbox"]').is(":checked")){
                                        if((tmp[0].note[tmp_index] == '1'|| tmp[0].note[tmp_index] == '2')){
                                            
                                            if((note_v.length == 0) || (note_v==undefined)){
                                                
                                                $.alert('請填值')
                                                evt
                                            }else{
                                                tmp_body.push(tmp_b.find('span').html() + ', ' + note_v)
                                                tmp_val.push(tmp_v+'_'+note_v)
                                            }
                                            

                                        }else{
                                            tmp_body.push(tmp_b.find('span').html())
                                            tmp_val.push(tmp_v)
                                        }
                                    }
                                    
                                    }
                                    
                                })
                            
                                if(tmp_val.length == 0){
                                    $.alert('請填值')
                                    evt
                                }
                            // console.log('note_v', tmp_val, tmp_body)
                            // if(tmp_val.length < 1){
                                
                            //     $.alert('請選值!!')
                            //     evt
                            // }
                        }else if(q_type == '4'){

                            var io_val = $('.InOrOut[name="'+x+'_'+i+'"] option:selected').val()
                            if(io_val == 0){
                                var tmp_val = $('.city[name="'+x+'_'+i+'"] option:selected').val()+$('.town[name="'+x+'_'+i+'"] option:selected').val()
                                
                                if($('.city[name="'+x+'_'+i+'"] option:selected').val().length < 1){
                                    tmp_val = undefined
                                    $.alert('請選值!!')
                                    evt
                                }
                                if($('.town[name="'+x+'_'+i+'"] option:selected').val().length < 1){
                                    tmp_val = undefined
                                    $.alert('請選值!!')
                                    evt
                                }
                                // if($('.village[name="'+x+'_'+i+'"] option:selected').val().length < 1){
                                //     tmp_val = undefined
                                //     $.alert('請選值!!')
                                //     evt
                                // }

                            }else if(io_val == 1){
                                var tmp_val =$('.outText[name="'+x+'_'+i+'"]').val()
                                if(tmp_val.length < 1){
                                    tmp_val = undefined
                                    $.alert('請選值!!')
                                    evt
                                }
                            }
                            
                            var tmp_body = tmp_val
                            
                        }else if(q_type == '6' || q_type == '5'){
                            
                           
                            var sub_type = element[i-1].sub_type
                            var stmp_title = $('#div_qtext_'+x+i).html().split('<')[0]
                            
                            if(i == 1){
                                var s_num = $('input[name="'+x+'_1"]').parent('.active').find('input[name="'+x+'_'+i+'"]').val()
                                var check_tmp = $('input[name="'+x+'_1"]').attr('placeholder');
                                
                                if(check_tmp){
                                    var sum = [];
                                    var check_tmp2 = check_tmp.split('_')[0]
                                    var tmp_val = check_tmp2
                                    var tmp_body = check_tmp[0] +'_'+ check_tmp.split('_')[1]
                                    $('input[name="'+x+'_1"]').each(function(evt){
                                        var v = $(this).val()
                                        sum.push(v)
                                    })
                                    var s_num = sum[sum.length-1]
                                }else{
                                    var tmp_val = $('input[name="'+x+'_1"]').parent('.active').find('input[name="'+x+'_1"]').val()
                                    var tmp_body = $('input[name="'+x+'_1"]').parent('.active').html().split('<')[0]    
                                }
                                

                                ans_arr.push({'ansTime':new Date().toLocaleString(),
                                              'num':parseInt(x),
                                              'num_n':i,
                                              'title':stmp_title,
                                              'ans':tmp_body,
                                              'val':tmp_val,
                                              'escape':0,
                                              'escape_title':'',
                                              'escape_body':'',
                                              'prefill': element[i - 1].prefill,
                                              'prefill_escape': element[i - 1].prefill_escape,
                                              'prefill_val': '',
                                              'edit_count': 0})
                            }else if(i > 1){
                                
                                for(var j = 0; j < s_num ; j++){
                                    var xxx = x+'_'+i+'_'+j
                                    
                                    var check_tmp = $('input[name="'+xxx+'"]').attr('placeholder');
                                    
                                    if(check_tmp){
                                        var check_tmp2 = check_tmp.split('_')[0]

                                    }
                                    // console.log('test:', xxx, check_tmp)
                                    if(sub_type != '8'){
                                        if(sub_type == '0'){//單選 
                                            var tmp = _.filter(fdata.q_setting, { 'q_id': parseInt(x), 'q_sn': parseInt(i) })

                                            
                                            if(check_tmp && check_tmp2 == '1'){
                                                
                                                var tmp_val = check_tmp.split('_')[2]
                                                var tmp_body = check_tmp.split('_')[0] +'_'+ check_tmp.split('_')[1]
                                            }else{
                                                var tmp_val = $('input[name="'+xxx+'"]').parent('.active').find('input[name="'+xxx+'"]').val()
                                                var tmp_body = $('input[name="'+xxx+'"]').parent('.active').html().split('<')[0]   
                                                
                                                if(tmp_val == undefined){
                                                
                                                    $.alert('請選值!!')
                                                    evt

                                                }else{
                                                    var tmp_index = _.indexOf(tmp[0].opt_value, tmp_val)
                                                    if(tmp[0].note[tmp_index] == '1'|| tmp[0].note[tmp_index] == '2'){
                                                        var note_v = $('#input_note_'+xxx).val()
                                                        // console.log(xxx, note_v)
                                                        if(note_v.length == 0){
                                                            $.alert('請選值!!')
                                                            evt
                                                            tmp_val = undefined
                                                        }else{
                                                            tmp_val = tmp_val+'_'+note_v
                                                        }
                                                        console.log(tmp_val) 
                                                        // console.log(tmp[0].note[tmp_index], xxx, tmp_val, note_v)
                                                    }
                                                }
                                                
                                            }
                                            
                                            // console.log(tmp_val, tmp_body)
                                            

                                        }else if(sub_type == '2'){

                                            if($('input[name="'+xxx+'"]').attr('placeholder') && $('input[name="'+xxx+'"]').attr('placeholder').split('_')[0] == '1'){
                                                // var check_tmp2 = check_tmp.split('_')[0]
                                                var tmp_val = $('input[name="'+xxx+'"]').attr('placeholder').split('_')[2]
                                                var tmp_body = $('input[name="'+xxx+'"]').attr('placeholder').split('_')[0] +'_'+ $('input[name="'+xxx+'"]').attr('placeholder').split('_')[1]
                                            }else{
                                                var tmp_val = $('input[name="'+xxx+'"]').val()
                                            
                                                var tmp_body = tmp_val

                                                
                                            }
                                            
                                            if(tmp_val.length < 1){
                                                tmp_val = undefined
                                                $.alert('請選值!!')
                                                evt
                                            }

                                        }else if(sub_type == '3' || sub_type == '7'){
                                            if($('input[name="'+xxx+'"]').attr('placeholder') && $('input[name="'+xxx+'"]').attr('placeholder').split('_')[0] == '1'){
                                                // var check_tmp2 = check_tmp.split('_')[0]
                                                var tmp_val = $('input[name="'+xxx+'"]').attr('placeholder').split('_')[2]
                                                var tmp_body = $('input[name="'+xxx+'"]').attr('placeholder').split('_')[0] +'_'+ $('input[name="'+xxx+'"]').attr('placeholder').split('_')[1]
                                            }else{
                                                var tmp_val = $('input[name="'+xxx+'"]').val()
                                                var tmp_body = tmp_val
                                            }
                                            if(tmp_val.length < 1){
                                                tmp_val = undefined
                                                $.alert('請選值!!')
                                                evt
                                            }

                                        }else if(sub_type == '1'){

                                            var tmp_val = []
                                            var tmp_body = []


                            
                                            if(check_tmp && check_tmp2 == '1'){
                                                // var check_tmp2 = check_tmp.split('_')[0]
                                                tmp_val.push($('input[name="'+xxx+'"]').attr('placeholder').split('_')[2])
                                                tmp_body.push($('input[name="'+xxx+'"]').attr('placeholder').split('_')[0] +'_'+ $('input[name="'+xxx+'"]').attr('placeholder').split('_')[1])
                                            }else{
                                                $('input[name="'+x+'_'+i+'"]:checked').each( function () {
                                                    // console.log('input_note_'+x+'_'+i,)

                                                    var tmp = _.filter(fdata.q_setting, { 'q_id': parseInt(x), 'q_sn': parseInt(i) })
                                                    var tmp_v = $(this).val()
                                                    var tmp_b = $(this).parent()
                                                    var note_v = $(this).parent().find('#input_note_'+x+'_'+i).val()
                                                    var tmp_index = _.indexOf(tmp[0].opt_value, tmp_v)

                                                    if(tmp[0].note[tmp_index] == '1'|| tmp[0].note[tmp_index] == '2'){
                                                        
                                                        tmp_body.push(tmp_b.find('span').html() + ', ' + note_v)
                                                        tmp_val.push(tmp_v+'_'+note_v)

                                                    }else{
                                                        tmp_body.push(tmp_b.find('span').html())
                                                        tmp_val.push(tmp_v)
                                                    }
                                                    // console.log('tmp_body:',tmp_body, 'tmp_val:', tmp_val)
                                                    
                                                })  
                                            }
                                            
                                            
                                        }else if(sub_type == '4'){
                                            var io_val = $('.InOrOut[name="'+xxx+'"] option:selected').val()
                                            if(io_val == 0){
                                                var tmp_val = $('.city[name="'+xxx+'"] option:selected').val()+$('.town[name="'+xxx+'"] option:selected').val()
                                                
                                                
                                                if($('.city[name="'+xxx+'"] option:selected').val().length < 1){
                                                    tmp_val = undefined
                                                }
                                            }else if(io_val == 1){
                                                var tmp_val =$('.outText[name="'+xxx+'"]').val()
                                                if(tmp_val.length < 1){
                                                    tmp_val = undefined
                                                }
                                            }
                                            if(tmp_val == undefined){
                                                $.alert('請選值!!')
                                                evt
                                            }
                                            var tmp_body = tmp_val
                                            
                                        }
                                        // console.log(xxx, sub_type, tmp_val, tmp_body)
                                        var tmp = _.filter(fdata.q_setting, { 'q_id': parseInt(x), 'q_sn': parseInt(i) })
                                        
                                        if(tmp_val == undefined){
                                            $.alert('請選值!!')
                                            evt
                                        }else{
                                            var tmp_index = _.indexOf(tmp[0].opt_value, tmp_val)
                                            console.log('tmp[0].opt_value, tmp_val', tmp[0].opt_value, tmp_val)
                                            var escape_val = tmp[0].escape[tmp_index]
                                            console.log('escape_val', escape_val)
                                            if(tmp[0].note[tmp_index] == '1'|| tmp[0].note[tmp_index] == '2'){
                                                    
                                                tmp_body = tmp_body+'_'+$('#input_note_'+xxx).val()
                                                tmp_val = tmp_val+'_'+$('#input_note_'+xxx).val()

                                            }
                                            if(escape_val == 1){

                                                var escape_title = tmp[0].target[tmp_index].split(',')[0].split('[')[1]
                                                var escape_body = tmp[0].target[tmp_index].split(',')[1].split(']')[0]
                                                
                                            }else{
                                                var escape_title = 0
                                                var escape_body = 0
                                                escape_val = 0
                                            }
                                            ans_arr.push({'ansTime':new Date().toLocaleString(),
                                                          'num':parseInt(x),
                                                          'num_n':i,
                                                          'title':tmp_title,
                                                          'ans':tmp_body,
                                                          'val':tmp_val,
                                                          'escape':0,
                                                          'escape_title':'',
                                                          'escape_body':'',
                                                          'prefill': tmp[0].prefill,
                                                          'prefill_escape': tmp[0].prefill_escape,
                                                          'prefill_val': '',
                                                          'edit_count': 0})
                                            // console.log('check_tmp:', xxx, check_tmp, check_tmp2, ans_arr)
                                        }
                                        
                                    }
                                }       
                            }
                            if(sub_type == '8'){
                                var tmp_val = []
                                var tmp_body = []
                                $('#div_familiar_pairs_'+x+'_'+q_length).find('*').each(function(){

                                    if($(this).find('.col-sm-3').html() != undefined){
                                        var t = $(this).find('.col-sm-3').html().split(' ')

                                        
                                        tmp_val.push(t[0] + '&' + t[2] + ': '+$('input[name="'+x+'_'+q_length+t[0]+t[2]+'"]').parent('.active').find('input[name="'+x+'_'+q_length+t[0]+t[2]+'"]').val())
                                        tmp_body.push(t[0] + '&' + t[2] + ': '+$('input[name="'+x+'_'+q_length+t[0]+t[2]+'"]:checked').parent('.active').html().split('<')[0])
                                        
                                    }
                                    
                                })

                                ans_arr.push({'ansTime':new Date().toLocaleString(),
                                              'num':parseInt(x),
                                              'num_n':i,
                                              'title':tmp_title,
                                              'ans':tmp_body,
                                              'val':tmp_val,
                                              'escape':escape_val,
                                              'escape_title':escape_title,
                                              'escape_body':escape_body,
                                              'prefill': tmp[0].prefill,
                                              'prefill_escape': tmp[0].prefill_escape,
                                              'prefill_val': '',
                                              'edit_count': 0})
                            }       
                        }
                    }
                    
                    if(((q_type == '6' || q_type == '5') && i == 1) || (q_type != '6' && q_type != '5')){

                        var tmp = _.filter(fdata.q_setting, { 'q_id': parseInt(x), 'q_sn': parseInt(i) })
                        // console.log('tmp:', tmp)
                        if(tmp_val == undefined){
                            $.alert('請選值!!')
                            evt
                        }else{
                            if(tmp[0].type == "1"){
                                for(var tmp_i = 0; tmp_i < tmp_val.length; tmp_i++){
                                    var tmp_index = _.indexOf(tmp[0].opt_value, tmp_val[tmp_i].split('_')[0])
                                    // console.log('tmp[0].opt_value, tmp_val[tmp_i]', tmp[0].opt_value, tmp_val[tmp_i].split('_')[0])
                                    // var escape_val = tmp[0].escape[tmp_index]
                                    // console.log('escape_val', escape_val)
                                    // if(tmp[0].note[tmp_index] == '1'|| tmp[0].note[tmp_index] == '2'){
                                                    
                                    //     tmp_body = tmp_body+'_'+$('#input_note_'+x+'_'+i).val()
                                    //     tmp_val = tmp_val+'_'+$('#input_note_'+x+'_'+i).val()

                                    // }
                                    if(tmp[0].escape[tmp_index] == 1){

                                        var escape_title = tmp[0].target[tmp_index].split(',')[0].split('[')[1]
                                        var escape_body = tmp[0].target[tmp_index].split(',')[1].split(']')[0]
                                        var escape_val = tmp[0].escape[tmp_index]
                            
                                    }
                                    // else{
                                    //     var escape_title = 0
                                    //     var escape_body = 0
                                    //     escape_val = 0
                                    // }
                                }
                                console.log(escape_val)
                            }else{
                                var tmp_index = _.indexOf(tmp[0].opt_value, tmp_val.split("_")[0])
                                var escape_val = tmp[0].escape[tmp_index]
                                // console.log(escape_val, tmp)
                                if(tmp[0].type == "0"){
                                    
                                    if(escape_val == 1){

                                        var escape_title = tmp[0].target[tmp_index].split(',')[0].split('[')[1]
                                        var escape_body = tmp[0].target[tmp_index].split(',')[1].split(']')[0]
                                        
                                    }else{
                                        var escape_title = 0
                                        var escape_body = 0
                                        escape_val = 0
                                    }  
                                }else if(tmp[0].type == "2" && tmp[0].escape == "1"){
                                    var escape_range = JSON.parse(tmp[0].escape_range)[0]
                                    var escape_target = JSON.parse(tmp[0].target)
                                    
                                    if(escape_range[0] < tmp_val && escape_range[1] > tmp_val){
                                        var escape_title = escape_target[0]
                                        var escape_body = escape_target[1]
                                        var escape_val = 1
                                    }else{
                                        var escape_title = 0
                                        var escape_body = 0
                                        var escape_val = 0
                                    }
                                }
                                // console.log(_.chain(tmp[0].special_code).replace("[", "").replace("]", "").split(',').value())

                                // console.log(tmp[0], tmp_val.split("_")[0], )

                                if(tmp[0].note[tmp_index] == '1'|| tmp[0].note[tmp_index] == '2'){
                                    var ck_sp_tmp = _.chain(tmp[0].special_code).replace("[", "").replace("]", "").split(',').value()
                                    for(var ck_sp = 0; ck_sp < ck_sp_tmp.length; ck_sp++){
                                        if(ck_sp_tmp[ck_sp].split(':')[1] == tmp_val){
                                            tmp_body = tmp_body+'_'+$('#input_note_'+x+'_'+i).val()
                                            tmp_val = tmp_val+'_'+$('#input_note_'+x+'_'+i).val()
                                        }
                                    }
                                    
                                    // console.log('test:',tmp_body, tmp_val,tmp[0].note[tmp_index])
                                }
                            }
                            
                            
                        }
                    }
                    if(q_type != "6" && q_type != "5"){
                        ans_arr.push({'ansTime':new Date().toLocaleString(),
                                  'num':parseInt(x),
                                  'num_n':i,
                                  'title':tmp_title,
                                  'ans':tmp_body,
                                  'val':tmp_val,
                                  'escape':escape_val,
                                  'escape_title':escape_title,
                                  'escape_body':escape_body,
                                  'prefill': tmp[0].prefill,
                                  'prefill_escape': tmp[0].prefill_escape,
                                  'prefill_val': '',
                                  'edit_count': 0})
                    }
                        
                    
                }
                
                localStorage['ansArr'] = JSON.stringify(_.sortBy(ans_arr, ['num']));
                
                
                var loacalAns = []
                loacalAns['prjid'] = fdata.prjid
                loacalAns['Sid'] = fdata.Sid
                loacalAns['ans'] = ans_arr
                console.log("test",loacalAns)
                var db = new Dexie("CAPI");
     
                db.version(1).stores({
                    ans: "[prjid+Sid]"
                });
                db.transaction('rw', db.ans, function () {
                    // Let's add some data to db:
                    db.ans.where(["prjid+Sid"]).equals([fdata.prjid, fdata.Sid]).delete();

                    db.ans.put(loacalAns);
                })
                
                
                $('#btn_editanswer').show()
                $('.btn_falBback').hide()
                console.log('ansArr:', $.parseJSON(localStorage['ansArr']))
                
            }
            
            
            $(document).on('click', '.btn_submit_specode', function(evt){
                evt.preventDefault();
                
                var tmp = $(this).attr('id').split('_')
                
                var specode_text = $('input[name="special_code_'+tmp[3] + '_' + tmp[4]+'"]:radio:checked').parent().html().split('<')[0]
                var tmp_q = q_info.q_setting.filter(function(element) {
                                if(element.q_id == tmp[3] && element.q_sn == tmp[4]){
                                    return element;
                                }
                            })
                
                if(tmp_q[0].type == '0'){
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').parent().attr({'disabled': true})
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').attr({'placeholder': '1_'+specode_text + '_' + $('input[name="special_code_'+tmp[3] + '_' + tmp[4]+'"]:radio:checked').val(), 'disabled': true})
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').hide()
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"][type="radio"]').parent().css("background-color", "#9d3535");
                }else if(tmp_q[0].type == '6'){

                    if(tmp[4] == "1"){
                        $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').parent().attr({'disabled': true})
                        $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').attr({'placeholder': '1_'+specode_text + '_' + $('input[name="special_code_'+tmp[3] + '_' + tmp[4]+'"]:radio:checked').val(), 'disabled': true})
                        $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').parent().css("background-color", "#9d3535");
                        var s = '1_'+specode_text + '_' + $('input[name="special_code_'+tmp[3] + '_' + tmp[4]+'"]:radio:checked').val()
                        var dynamic_div =_.filter(q_info.q_setting, function(o) { 
                            if(o.q_id == parseInt(tmp[3]) && o.type == "6" && o.q_sn > 1){
                                return o 
                            }
                            
                        });

                        $('.dynamic_display').each(function(evt){
                            dynamic_ids.push($(this).attr('id').split('_'))
                        })
                        // console.log('dynamic_ids:', dynamic_ids)
                        var t = _.filter(dynamic_ids, {0: 'div', 1: 'dynamic', 2: tmp[3]})
                        console.log('t:', t)
                        for(var i = 0; i < t.length ; i++){

                                
                            var sub_type = _.filter(dynamic_div, {'q_id': parseInt(t[i][2]), 'q_sn': parseInt(t[i][3])})[0].sub_type
                            if(sub_type == "0"){
                                
                                $('#'+t[i].join('_')).find('.radio_lbl').attr({'disabled': true})
                                $('#'+t[i].join('_')).find('.radio_lbl').css("background-color", "#9d3535");
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').attr({'placeholder': s, 'disabled': true})
                                $('#input_note_'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]).val("")
                                $('#input_note_'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]).attr({'placeholder': s, 'disabled': true})

                            }else if(sub_type == "2"){
                                 
                                // console.log('#'+t[i].join('_'))
                                $('#'+t[i].join('_')).find('.ValidateNumber').attr({'disabled': true})
                                $('#'+t[i].join('_')).find('.ValidateNumber').css("background-color", "#9d3535");
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').val("")
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').attr({'disabled': true})
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').attr({'placeholder': s, 'disabled': true})
                            }
                            else if(sub_type == "3"){
                                 
                                // console.log('#'+t[i].join('_'))
                                $('#'+t[i].join('_')).find('.form-control').attr({'disabled': true})
                                $('#'+t[i].join('_')).find('.form-control').css("background-color", "#9d3535");
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').val("")
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').attr({'disabled': true})
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').attr({'placeholder': s, 'disabled': true})
                            }
                            else{
                                // console.log('#'+t[i].join('_'))
                                // $('#'+t[i].join('_')).find('.radio_lbl').attr({'disabled': true})
                                // $('#'+t[i].join('_')).find('.radio_lbl').css("background-color", "#9d3535");
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').val("")
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').attr({'disabled': true})
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').attr({'placeholder': s, 'disabled': true})
                            }

                        }

                        
                    }else{

                        var dynamic_div =_.filter(q_info.q_setting, function(o) { 
                            if(o.q_id == parseInt(tmp[3]) && o.type == "6" && o.q_sn > 1){
                                return o 
                            }
                            
                        });
                        $('.dynamic_display').each(function(evt){
                            dynamic_ids.push($(this).attr('id').split('_'))
                        })
                            
                        var t = _.filter(dynamic_ids, {0: 'div', 1: 'dynamic', 4: tmp[5], 2: tmp[3], 3: tmp[4]})
                        
                        _.forEach(t, function(value, key) {
                            
                            var sub_type = _.filter(dynamic_div, {'q_id': parseInt(t[0][2]), 'q_sn': parseInt(t[0][3])})[0].sub_type
                            if(sub_type == "0"){
                                
                                $('#'+value.join('_')).find('.radio_lbl').attr({'disabled': true})
                                $('#'+value.join('_')).find('.radio_lbl').css("background-color", "#9d3535");
                                $('input[name="'+value[2]+'_'+value[3]+'_'+value[4]+'"]').attr({'placeholder': '1_'+specode_text + '_' + $('input[name="special_code_'+value[2]+'_'+value[3]+'"]:radio:checked').val()})
                                $('#input_note_'+value[2]+'_'+value[3]+'_'+value[4]).val("")
                                $('#input_note_'+value[2]+'_'+value[3]+'_'+value[4]).attr({'placeholder': '1_'+specode_text + '_' + $('input[name="special_code_'+value[2]+'_'+value[3]+'"]:radio:checked').val()})
                            }else{
                                $('input[name="'+value[2]+'_'+value[3]+'_'+value[4]+'"]').val("")
                                $('input[name="'+value[2]+'_'+value[3]+'_'+value[4]+'"]').attr({'disabled': true})
                                $('input[name="'+value[2]+'_'+value[3]+'_'+value[4]+'"]').attr({'placeholder': '1_'+specode_text + '_' + $('input[name="special_code_'+value[2]+'_'+value[3]+'"]:radio:checked').val(), 'disabled': true})
                            }
                            
                        });

                        
                    }
                    
                    
                    
                }else if(tmp_q[0].type == '4'){
                    $('select[name="'+tmp[3] + '_' + tmp[4]+'"]').css("background-color", "#9d3535");
                    $('select[name="'+tmp[3] + '_' + tmp[4]+'"]').attr({'disabled': true})
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').attr({'placeholder': '1_'+specode_text + '_' + $('input[name="special_code_'+tmp[3] + '_' + tmp[4]+'"]:radio:checked').val(), 'disabled': true})
                }else  if(tmp_q[0].type == '2'){
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').val("")
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').attr({'placeholder': '1_'+specode_text + '_' + $('input[name="special_code_'+tmp[3] + '_' + tmp[4]+'"]:radio:checked').val(), 'disabled': true})
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').css("background-color", "#9d3535");
                }else  if(tmp_q[0].type == '3'){
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').val("")
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').attr({'placeholder': '1_'+specode_text + '_' + $('input[name="special_code_'+tmp[3] + '_' + tmp[4]+'"]:radio:checked').val(), 'disabled': true})
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').css("background-color", "#9d3535");
                }else  if(tmp_q[0].type == '1'){
                    // $('input[name="'+tmp[3] + '_' + tmp[4]+'"][type="text"]').attr({'placeholder': '1_'+specode_text + '_' + $('input[name="special_code_'+tmp[3] + '_' + tmp[4]+'"]:radio:checked').val(), 'disabled': true})
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"][type="text"]').attr({'placeholder': '1_'+specode_text + '_' + $('input[name="special_code_'+tmp[3] + '_' + tmp[4]+'"]:radio:checked').val(), 'disabled': true, "display": 'none'})
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').css("background-color", "#9d3535");
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').attr({'placeholder': '1_'+specode_text + '_' + $('input[name="special_code_'+tmp[3] + '_' + tmp[4]+'"]:radio:checked').val(), 'disabled': true});
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').prop("checked", false);
                }
                $("#Md_SpecialCodes").modal('hide');
               
            })
             
            $(document).on('click', '.btn_cancel_specode', function(evt){
                evt.preventDefault();
                var tmp = $(this).attr('id').split('_')
                
                var tmp_q = q_info.q_setting.filter(function(element) {
                                if(element.q_id == tmp[3] && element.q_sn == tmp[4]){
                                    return element;
                                }
                            })
                if(tmp_q[0].type == '0'){
                    
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').parent().attr({'disabled': false})
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').removeAttr('placeholder')
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').parent().removeAttr('style');
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"][type="radio"]').parent().css({"width": "50%", "text-align": "left"});
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"][type="radio"]').parent().removeClass('active')

                }else if(tmp_q[0].type == '6'){

                    if(tmp[4] == "1"){
                        $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').parent().attr({'disabled': false})
                        $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').removeAttr('placeholder')
                        $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').parent().css("background-color", "#fff");
                        $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').parent().removeAttr('style');
                        $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').parent().css({"width": "50%", "text-align": "left"});
                        // var s = '1_'+specode_text + '_' + $('input[name="special_code_'+tmp[3] + '_' + tmp[4]+'"]:radio:checked').val()
                        var dynamic_div =_.filter(q_info.q_setting, function(o) { 
                            if(o.q_id == parseInt(tmp[3]) && o.type == "6" && o.q_sn > 1){
                                return o 
                            }
                            
                        });

                        $('.dynamic_display').each(function(evt){
                            dynamic_ids.push($(this).attr('id').split('_'))
                        })
                        
                        var t = _.filter(dynamic_ids, {0: 'div', 1: 'dynamic', 2: tmp[3]})
                        
                        for(var i = 0; i < t.length ; i++){

                                
                            var sub_type = _.filter(dynamic_div, {'q_id': parseInt(t[i][2]), 'q_sn': parseInt(t[i][3])})[0].sub_type
                            if(sub_type == "0"){
                                
                                $('#'+t[i].join('_')).find('.radio_lbl').attr({'disabled': false})
                                $('#'+t[i].join('_')).find('.radio_lbl').css("background-color", "#fff");
                                $('#'+t[i].join('_')).find('.radio_lbl').removeAttr('style');
                                $('#'+t[i].join('_')).find('.radio_lbl').css({"width": "100%", "text-align": "left"});
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').removeAttr('placeholder')
                                $('#input_note_'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]).val("")
                                $('#input_note_'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]).removeAttr('placeholder')

                            }else if(sub_type == "2"){
                                 
                                $('#'+t[i].join('_')).find('.ValidateNumber').attr({'disabled': false})
                                $('#'+t[i].join('_')).find('.ValidateNumber').css("background-color", "#fff");
                                $('#'+t[i].join('_')).find('.ValidateNumber').removeAttr('style');
                                $('#'+t[i].join('_')).find('.ValidateNumber').css({"width": "100%", "text-align": "left"});
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').removeAttr('placeholder')
                                $('#input_note_'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]).val("")
                                $('#input_note_'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]).removeAttr('placeholder')

                                
                            }else if(sub_type == "3"){
                                 
                                $('#'+t[i].join('_')).find('.control').attr({'disabled': false})
                                $('#'+t[i].join('_')).find('.control').css("background-color", "#fff");
                                $('#'+t[i].join('_')).find('.control').removeAttr('style');
                                $('#'+t[i].join('_')).find('.control').css({"width": "100%", "text-align": "left"});
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').removeAttr('placeholder')
                                $('#input_note_'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]).val("")
                                $('#input_note_'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]).removeAttr('placeholder')


                            }else{
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').val("")
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').attr({'disabled': false})
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').css("background-color", "#fff");
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').removeAttr('style');
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').css({"width": "100%", "text-align": "left"});
                                $('input[name="'+t[i][2]+'_'+t[i][3]+'_'+t[i][4]+'"]').removeAttr('placeholder')
                            }

                        }

                        
                    }else{

                        $('.dynamic_display').each(function(evt){
                            dynamic_ids.push($(this).attr('id').split('_'))
                        })
                        var t = _.filter(dynamic_ids, {0: 'div', 1: 'dynamic', 4: tmp[5], 2: tmp[3], 3: tmp[4]})
                        var dynamic_div =_.filter(q_info.q_setting, function(o) { 
                                            if(o.q_id == parseInt(tmp[3]) && o.type == "6" && o.q_sn > 1){
                                                return o 
                                            }
                                        })
                         _.forEach(t, function(value, key) {
                            
                            var sub_type = _.filter(dynamic_div, {'q_id': parseInt(t[0][2]), 'q_sn': parseInt(t[0][3])})[0].sub_type
                            
                            if(sub_type == "0"){
                                
                                
                                $('input[name="'+value[2]+'_'+value[3]+'_'+value[4]+'"]').removeAttr('placeholder');
                                $('#'+value.join('_')).find('.radio_lbl').attr({'disabled': false})
                                $('#'+value.join('_')).find('.radio_lbl').css("background-color", "#fff");
                                $('#'+value.join('_')).find('.radio_lbl').removeAttr('style');
                                $('#'+value.join('_')).find('.radio_lbl').css({"width": "50%", "text-align": "left"});
                                $('#input_note_'+value[2]+'_'+value[3]+'_'+value[4]).val("")
                                $('#input_note_'+value[2]+'_'+value[3]+'_'+value[4]).attr({'disabled': false})
                                $('#input_note_'+value[2]+'_'+value[3]+'_'+value[4]).removeAttr('placeholder');
                            }else{
                                $('input[name="'+value[2]+'_'+value[3]+'_'+value[4]+'"]').css("background-color", "#fff");
                                $('input[name="'+value[2]+'_'+value[3]+'_'+value[4]+'"]').removeAttr('style');
                                $('input[name="'+value[2]+'_'+value[3]+'_'+value[4]+'"]').css({"width": "50%", "text-align": "left"});
                                $('input[name="'+value[2]+'_'+value[3]+'_'+value[4]+'"]').val("")
                                $('input[name="'+value[2]+'_'+value[3]+'_'+value[4]+'"]').attr({'disabled': false})
                                $('input[name="'+value[2]+'_'+value[3]+'_'+value[4]+'"]').removeAttr('placeholder');
                            }
                            
                        }); 
                        

                        
                    }
                    
                    
                    
                }else if(tmp_q[0].type == '4'){
                    $('select[name="'+tmp[3] + '_' + tmp[4]+'"]').attr({'disabled': false})
                    $('select[name="'+tmp[3] + '_' + tmp[4]+'"]').css("background-color", "#fff");
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').removeAttr('placeholder')
                    
                }else if(tmp_q[0].type == '1'){
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').css("background-color", "#fff");
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"][type="text"]').removeAttr('style');
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"][type="text"]').css({"width": "50%", "text-align": "left", "display": 'none'});
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"][type="text"]').removeAttr('placeholder');
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').prop("checked", false);
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').removeAttr('placeholder');
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').attr({'disabled': false})
                }else  if(tmp_q[0].type == '2'){
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').css("background-color", "#fff");
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').removeAttr('style');
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').css({"width": "50%", "text-align": "left"});
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').removeAttr('placeholder');
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').attr({'disabled': false})
                }else  if(tmp_q[0].type == '3'){
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').css("background-color", "#fff");
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').removeAttr('style');
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').css({"width": "50%", "text-align": "left"});
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').removeAttr('placeholder');
                    $('input[name="'+tmp[3] + '_' + tmp[4]+'"]').attr({'disabled': false})
                }

                
                $("#Md_SpecialCodes").modal('hide');
            })
            $("select[name=Terminate_code]").on("change", function(evt){
                evt.preventDefault();
                var tmp = $('option:selected',this).val()
                console.log(tmp)
                if(tmp == 201){
                    $("#div_Terminate_code_reject").show()
                }else{
                    $("#div_Terminate_code_reject").hide()
                }
            })
 
            $("#Terminate_submit").on('click', function(evt){
                evt.preventDefault();
                var survey_status_label = $('select[name=Terminate_code] option:selected').parent().attr('label');
                var survey_status       = $('select[name=Terminate_code] option:selected').val()
                var rs_status_note      = $("#Terminate_note").val()
                // console.log(survey_status, survey_status_label, rs_other_note, rs_status_note)
                
                if(survey_status == 201){
                    var survey_status_reject = $('select[name=Terminate_code_reject] option:selected').val()
                    var rs_status = survey_status_label + ': ' + survey_status + survey_status_reject
                }else{
                    var rs_status = survey_status_label + ': ' + survey_status
                }
                
                if((rs_status == 20110 || rs_status == 999) && (rs_status_note == "") ){
                    $.alert("請填寫說明文字");
                    return false;
                }else if (CheckTargetinfo("SurveyTerminate") == false){

                }else{
                    $("#Md_Terminate").modal('hide')
    

                    var db = new Dexie("CAPI");
                    db.version(1).stores({
                        project: "prjid",                       // 專案清單
                        prjwork: "[prjid+Sid]",            // 樣本清單
                        questionnaire: "++tkey, [prjid+qid]",   // 問卷設定清單
                        sample_status: "[prjid+Sid]",                   // 訪問代碼
                        ans: '[prjid+Sid]',                             // 問卷結果
                        vill_list: "++tkey",                     // 村里清單
                        check:'[prjid+Sid]'
                    });

                    
                    // Params
                    const myParams = JSON.parse(localStorage.getItem('current_case'));
                    const myParam_prjid = myParams.prjid
                    const myParam_Sid   = myParams.sid

                    var his_arr = []
                    console.log("test", fdata.his_status)
                    if(fdata.his_status == undefined || fdata.his_status.length == 0){

                        his_arr.push(new Date().toLocaleString() +' '+ rs_status)
                    }else{

                        his_arr = JSON.parse(fdata.his_status)
                        his_arr.push(new Date().toLocaleString() +' '+ rs_status)
                    }

                    // 訪問結果代碼 寫入IDB fdata
                    db.prjwork.where('[prjid+Sid]').equals([myParam_prjid, myParam_Sid]).modify(
                        {
                         "status": rs_status,
                         "status_note": rs_status_note,
                         "check_status":"2",
                         "his_status":JSON.stringify(his_arr),
                         "sys_his_status":JSON.stringify(his_arr)
                        }
                    )
                    // db.prjwork.where('Sid').equals([myParam_Sid]).modify(
                    //     {
                    //      "status": rs_status,
                    //      "status_note": rs_status_note,
                    //      "check_status":"2",
                    //      "his_status":JSON.stringify(his_arr)
                    //     }
                    // )
                    db.sample_status.where('[prjid+Sid]').equals([myParam_prjid, myParam_Sid]).modify(
                        {
                         "Sid": myParam_Sid,
                         "status": rs_status,
                         "status_note": rs_status_note,
                         "his_obj": JSON.stringify(his_arr),
                         "his_status":JSON.stringify(his_arr),
                         "sys_his_status":JSON.stringify(his_arr)
                        }
                    )
                    
                    db.transaction('rw', db.prjwork, db.ans, async(obj) => {
                        // Query:
                        return Dexie.Promise.all(
                            db.prjwork.where("[prjid+Sid]").equals([myParam_prjid, myParam_Sid]).toArray(), 
                            db.ans.where("[prjid+Sid]").equals([myParam_prjid, myParam_Sid]).toArray()
                        )
                        
                    }).then (function (obj) {

                        var downLoadFile = []

                        downLoadFile.push({'prjwork':obj[0][0]})
                        if(obj[1][0] != undefined){
                            downLoadFile.push({'ans': obj[1][0]['ans']})    
                        }

                        $(".confirm").attr({'data-obj': JSON.stringify(downLoadFile), 'name': fdata.Sid})

                    }).catch(e => {
                        console.error(e.stack || e);
                    });

                    $.confirm({
                        title: '',
                        content: "確認是否結束訪問?",
                        buttons: {
                            confir: {
                                text: '確認',
                                btnClass: 'btn-blue confirm',
                                action: function(){
                                    var name = fdata.prjid+"_"+$('.confirm').attr('name')+".json"
                                    console.log(name)
                                     $("<a />", {
                                            "download": name,
                                            "href" : "data:application/json;charset=utf-8," + encodeURIComponent(JSON.stringify($('.confirm').data().obj)),
                                        }).appendTo("body")
                                        .click(function() {
                                        $(this).remove()
                                    })[0].click()
                                        window.location = 'https://capi.geohealth.tw/php/selprj.php'
                                    

                                }
                            },
                            cancle: {
                                text: '取消',
                                btnClass: 'btn-red',
                                action: function(){
                                    $.alert('已取消!!');
                                }
                            }
                        }
                        
                    }); 
                }
                    
                
            })
 
            $("#Terminate_cancel").on('click', function(evt){
                evt.preventDefault();
                $("#Md_Terminate").modal('hide');
            })
             
            $(document).on('change', '.btn_radio' ,function(evt){

                evt.preventDefault();

                var q = $(this).attr('name').split('_')[0]
                var s = $(this).attr('name').split('_')[1]
                var this_val = $(this).val(); 

                var tmp = _.filter(q_info.q_setting, {'q_id': parseInt(q), 'q_sn': parseInt(s)})
                
                var note = tmp[0].note[_.indexOf(tmp[0].opt_value, this_val)]
                if(tmp[0].type != "6"){
                    // 
                    
                    if(note != "1" && note !="2"){
                        // console.log('input[name="'+q+'_'+s+'"]')
                        $('input[name="'+q+'_'+s+'"]').val()
                        $('input[name="'+q+'_'+s+'"]').attr('disabled', true)
                        $('input[name="'+q+'_'+s+'"]').css({'display': 'none'})
                    }
                    $('input[name="'+q+'_'+s+'"]').attr('disabled', true)
                    $('input[name="'+q+'_'+s+'"]').css({'display': 'none'})
                    $('#input_note_'+q+'_'+s+'_'+this_val).attr('disabled', false)
                    $('#input_note_'+q+'_'+s+'_'+this_val).css({'display': 'block'})
                    
                }else{
                    var z = $(this).attr('name').split('_')[2]

                    if(note != "1" && note !="2"){
                        $('#input_note_'+q+'_'+s+'_'+z).val()
                        $('#input_note_'+q+'_'+s+'_'+z).attr('disabled', true)
                        $('#input_note_'+q+'_'+s+'_'+z).css({'display': 'none'})
                    }else{
                        $('#input_note_'+q+'_'+s+'_'+z).attr('disabled', false)
                        $('#input_note_'+q+'_'+s+'_'+z).css({'display': 'block'})
                    }
                }
                
                
            })
             
            $(document).on('change', "input[type='checkbox']" ,function(evt){
                evt.preventDefault();
                var tmp = $(this).attr('name').split('_')
                var tmp_val     = $(this).val() 
                var tmp_checked = $(this).is(':checked') ?1 :0;
                // console.log(tmp_val,tmp_checked)
                tmp_qid = Number(tmp[0])
                tmp_qsn = Number(tmp[1])
                // console.log(fdata.q_setting)
                var tmp_obj = _.filter(q_info.q_setting, {'q_id': tmp_qid, 'q_sn': tmp_qsn})[0]
                // console.log(tmp_obj)
                if(tmp_obj.disjoint != ""){
                    var index_disjoint_opt = _.indexOf(tmp_obj.disjoint, '1')
                    var disjoint_val = tmp_obj.opt_value[index_disjoint_opt]
                    // console.log('test:', disjoint_val)
                    if(tmp_val == disjoint_val && tmp_checked == 1){

                        // uncheck other checkboxes
                        $("input[name=" + tmp_qid + "_" + tmp_qsn + "]").prop("checked", false);
                        $("input[name=" + tmp_qid + "_" + tmp_qsn + "][value=" + disjoint_val + "]").prop("checked", true);
                        $("input[name=" + tmp_qid + "_" + tmp_qsn + "]").parent().find('#input_note_'+tmp_qid+'_'+tmp_qsn).val()
                        $("input[name=" + tmp_qid + "_" + tmp_qsn + "]").parent().find('#input_note_'+tmp_qid+'_'+tmp_qsn).css({'display': 'none'})
                    }else if(tmp_val != disjoint_val && tmp_checked == 1){
                        // uncheck the disjoint checkbox
                        $("input[name=" + tmp_qid + "_" + tmp_qsn + "][value=" + disjoint_val + "]").prop("checked", false);
                        $("input[name=" + tmp_qid + "_" + tmp_qsn + "][value=" + disjoint_val + "]").parent().find('#input_note_'+tmp_qid+'_'+tmp_qsn).val()
                        $("input[name=" + tmp_qid + "_" + tmp_qsn + "][value=" + disjoint_val + "]").parent().find('#input_note_'+tmp_qid+'_'+tmp_qsn).css({'display': 'none'})
                    }
                }

                var note = tmp_obj.note[_.indexOf(tmp_obj.opt_value, tmp_val)]

                if(tmp_checked == 0){               
                    $("input[name=" + tmp_qid + "_" + tmp_qsn + "][value=" + tmp_val + "]").parent().find('#input_note_'+tmp_qid+'_'+tmp_qsn).val("")
                    $("input[name=" + tmp_qid + "_" + tmp_qsn + "][value=" + tmp_val + "]").parent().find('#input_note_'+tmp_qid+'_'+tmp_qsn).css({'display': 'none'})
                }else if((note == "1" || note == "2") && tmp_checked == 1){
                    $("input[name=" + tmp_qid + "_" + tmp_qsn + "]:checked").parent().find('#input_note_'+tmp_qid+'_'+tmp_qsn).css({'display': 'block'})

                }
                
            })
             
            $(document).on('change', ".ChangeNameLength" ,function(evt){
                evt.preventDefault();
                var tmp         = $(this).val()
                
                // var dynamic_ids = new Array();
                
                $('.dynamic_display').each(function(evt){
                    dynamic_ids.push($(this).attr('id').split('_'))
                })
                
                _.map(dynamic_ids, function(array){
                    if(array[4] >= tmp){
                        var target_tohide_id = array.join('_')
                        $("#" + target_tohide_id).hide()
                        
                    }else{
                        var target_tohide_id = array.join('_')
                        $("#" + target_tohide_id).show()
                    }
                })
            })

            $(document).on('change', ".input_names " ,function(evt){
                evt.preventDefault();
                var tmp         = $(this).val()
                // var tmp_qid  = $(this).attr('name').split('_')[0]
                // var tmp_qsn  = $(this).attr('name').split('_')[1]
                var tmp_qpr     = $(this).attr('name').split('_')[2]
                _.map($('.fillname'), function(fn){
                    var tmp_spanid = fn.id.split('_')[4]
                    if(tmp_spanid == tmp_qpr){
                        $("#" + fn.id).html(tmp)
                    }
                    // console.log(fn)
                    // console.log()
                })
            })

            $(document).on('click', ".btn_familiar_pairs " ,function(evt){
                evt.preventDefault();
                var tmp_qid  = $(this).attr('id').split('_')[3]
                var tmp_qsn  = $(this).attr('id').split('_')[4]
                var n_alters = $("input[name=" + tmp_qid + "_1]:checked").val()
                console.log("input[name=" + tmp_qid + "_1]:checked")
                // console.log(tmp_qid, n_alters)
                if(n_alters > 1){
                    var n_pairs     = n_alters * (n_alters - 1) / 2
                    var alter_names = new Array();
                    _.map($("input[id*='input_names_" + tmp_qid + "']"), function(arr){
                        var tmp_name = $(arr).val();
                        if(tmp_name != ""){
                            alter_names.push(tmp_name)
                        }
 
                    })
                    
                    var alter_names_pairs = pairwise(alter_names);
                    var fdata = _.filter(q_info.q_setting, {'q_id': parseInt(tmp_qid), 'q_sn': parseInt(tmp_qsn)})[0]
                    
                    $("#div_familiar_pairs_" + tmp_qid + '_' + tmp_qsn).empty()
                    _.map(alter_names_pairs, function(arr){
                        // console.log(alter_names_pairs)
                        var names_pairs = arr[0] + " & " + arr[1]
                        var div_rows  = $("<div>")
                        var span_name = $("<div>").attr({'class': 'col-sm-3'}).html(names_pairs)
                        var btndiv = $('<div>').attr({'class': 'btn-group col-sm-9','data-toggle':'buttons'});
                        for(var i = 0; i < fdata.opt_txt.length; i++){
                            var opts = $('<input>').attr({  'class': '',
                                                            'type': 'radio',
                                                            'name': fdata.q_id + '_' + fdata.q_sn + arr[0] + arr[1],
                                                            'value': fdata.opt_value[i],
                                                            'note': (fdata.note[i] == 1 ?1 :0),
                                                            'style': 'display: none',
                                                             required: true});
                            var lbl = $('<label>').html(fdata.opt_txt[i]).attr({'class': 'btn btn-default radio_lbl', 'style':'width: 30%; text-align:left'})
                                      
                            var tmp_div = $('<div>').attr({'style': 'display: inline'})
                            opts.appendTo(lbl)
                            lbl.appendTo(tmp_div)
                            tmp_div.appendTo(btndiv)
                        }
                        div_rows.append(span_name).append(btndiv)
                        $("#div_familiar_pairs_" + tmp_qid + '_' + tmp_qsn).append(div_rows)
                    })
                }else{
                    $.alert('僅大於1人有效')
                }
            })
            
            $(document).on('change', ".ValidateNumber" ,function(evt){
                evt.preventDefault();
                var qid = parseInt($(this).attr('name').split('_')[0])
                var qsn = parseInt($(this).attr('name').split('_')[1])
                var data = JSON.parse(_.filter(fdata.q_setting, {'q_id': qid, 'q_sn': qsn})[0].range)
                var in_val = Number($(this).val());
                var cnt = 0 
                console.log(data, in_val)
                if(data.length >= 2){
                    for(var i = 0; i < data.length ; i++){
                        console.log(in_val, data[i][0], data[i][1], data[i][0] > in_val , in_val > data[i][1])
                        if(data[i][0] > in_val || in_val > data[i][1]){
                            cnt += 1
                            
                        }else{
                            cnt -= 1 
                        }
                    }
                    // console.log(cnt, data.length)
                    if(cnt == data.length){
                        $.alert("輸入數值超出範圍");
                        $(this).val('');
                        return false;
                    }
                }else{
                    if((data[0][0] > in_val || in_val > data[0][1])){
                        $.alert("輸入數值超出範圍");
                        $(this).val('');
                        return false;
                    }
                }
                
            })
            
            function deleteArray(eleID){
                ans_arr = $.grep(ans_arr, function(e){ 
                     return e.num != eleID
                });
            }
            function escape_function(fdata, escape_title, ele){
                var db = new Dexie("CAPI");
                db.version(1).stores({
                    project: "prjid",                       // 專案清單
                    prjwork: "[prjid+Sid]",            // 樣本清單
                    questionnaire: "++tkey, [prjid+qid]",   // 問卷設定清單
                    sample_status: "[prjid+Sid]",                   // 訪問代碼
                    ans: '[prjid+Sid]',                             // 問卷結果
                    vill_list: "++tkey",                     // 村里清單
                    check: "[prjid+Sid]",
                });
                db.transaction('rw', db.check, async(obj) => {
                    // Query:
                    return Dexie.Promise.all(
                        db.check.where('[prjid+Sid]').equals([fdata.prjid, fdata.Sid]).toArray(),
                    )
                    
                }).then (function (rs) {
                    var sid_q_setting = rs[0][0].q_setting;
                    _.filter(sid_q_setting, function(o){
                        if(o.q_id == parseInt(escape_title)){
                            if(o.ck_pre_target != 0){
                                console.log(fdata, o.ck_pre_target)

                                escape_function(fdata, o.ck_pre_target, ele.split("_")[0] +"_"+ ele.split("_")[1] + escape_title.toString())
                            }else{
                                ChangeDiv(ele, 1, escape_title)
                            }
                        }
                    })
                })
            }
            $(document).on('click', '.btn_last', function(evt){
                evt.preventDefault();

                var eleID = $(this).attr('id')
                var tmp = ans_arr[ans_arr.length - 1]['num']
                // console.log(fdata.Sid)
               
                ChangeDiv(eleID, 0, 0, tmp);
                deleteArray(tmp)
                
            });

            $(document).on('click', '.btn_next', function(evt){
                evt.preventDefault();
                var ck_lim2 = 0
                var res_title = 0
                var escape = 0
                var ck_special_escape, ck_special_target, ck_escape, ck_target, ck_lim, ck_pre_target, res_title
                var escape_pre_title = 0
                var escape_title = 0
                var tmp_ck_pre_target
                var eleID = $(this).attr('id');
                qnum = eleID.split("_")[2];
                getAns(qnum, fdata);
                var ans_filter = ans_arr.filter(function(element) {
                                    return element.num == qnum;
                                })

                var db = new Dexie("CAPI");
                db.version(1).stores({
                    project: "prjid",                       // 專案清單
                    prjwork: "[prjid+Sid]",            // 樣本清單
                    questionnaire: "++tkey, [prjid+qid]",   // 問卷設定清單
                    sample_status: "[prjid+Sid]",                   // 訪問代碼
                    ans: '[prjid+Sid]',                             // 問卷結果
                    vill_list: "++tkey",                     // 村里清單
                    check: "[prjid+Sid]",
                });
                
                
                db.transaction('rw', db.check, async(obj) => {
                    // Query:
                    return Dexie.Promise.all(
                        db.check.where('[prjid+Sid]').equals([fdata.prjid, fdata.Sid]).toArray(),
                    )
                    
                }).then (function (rs) {
                    var sid_data = rs[0][0]
                    var sid_q_setting = rs[0][0].q_setting;
                    // console.log(sid_q_setting)
                    var q_setting_filter = _.filter(sid_q_setting, {'q_id':parseInt(qnum), 'q_sn':parseInt(1)})
                    console.log(q_setting_filter)
                    var special_code_arr = _.chain(q_setting_filter[0].special_code).replace("[", "").replace("]", "").split(',').value()

                    //判斷是否有跳答
                    _.filter(sid_q_setting, function(o){
                        if(o.q_id == parseInt(qnum)){
                           var tmp =  _.filter(ans_arr, {'num': parseInt(qnum), 'num_n': parseInt(o.q_sn)})[0]
                           console.log('tmp:', tmp)
                           // console.log(tmp.num, parseInt(qnum), tmp.escape,o.ck_target)
                           if(tmp.num == parseInt(qnum) && tmp.escape == "1"){
                                console.log(tmp.escape, tmp.escape_title)
                                ck_escape = tmp.escape
                                ck_target = tmp.escape_title
                                o.ck_target =parseInt(ck_target)
                                
                            }
                            else if(tmp.num == parseInt(qnum) && (tmp.escape == "0" || tmp.escape == undefined)){
                                // console.log(tmp.num, parseInt(qnum), tmp.escape,o.ck_target)
                                ck_escape = 0
                                ck_target = null
                                o.ck_target = 0
                                // console.log(tmp.num, parseInt(qnum), tmp.escape,o.ck_target)
                            }
                        }

                        
                    })
                    console.log(ck_escape, ck_target)

                    //判斷是否有現在題目設定的answer_limitation_ref
                    _.filter(sid_q_setting, function(o){
                        if(o.answer_limitation == "1"){
                            // console.log(o.answer_limitation, o.q_id)
                            // console.log(o.answer_limitation_ref)
                            var lim_ref = JSON.parse(o.answer_limitation_ref)
                            // console.log(lim_ref)
                            for(var i = 0; i < lim_ref.length; i++){
                                var ck_lim = 0
                                var lim_ref_qnum = lim_ref[i][0][0]
                                var lim_ref_qsn = lim_ref[i][0][1]
                                console.log('lim_ref_qnum', lim_ref_qnum)
                                console.log('lim_ref_qsn', lim_ref_qsn)
                                var o_sub = _.filter(sid_q_setting, {'q_id': lim_ref_qnum, 'q_sn': lim_ref_qsn})[0]
                                var tmp =  _.filter(ans_arr, {'num': parseInt(lim_ref_qnum), 'num_n': parseInt(lim_ref_qsn)})
                                // console.log('qnum', qnum, lim_ref_qsn)
                                // console.log(tmp)
                                if(tmp.length != 0){
                                    if(o_sub.q_id == tmp[0].num){
                                        console.log('test', o_sub)
                                        if(lim_ref_qnum == parseInt(o_sub.q_id) && lim_ref_qsn == parseInt(o_sub.q_sn) && tmp.length != 0){
                                            // console.log(lim_ref_qnum, parseInt(o_sub.q_id), lim_ref_qsn, parseInt(o_sub.q_sn))
                                            if(lim_ref[i][1].length == 2){//如果lim_reft長度是2 判斷是數值題或其他題型
                                                
                                                if(o_sub.type == '2'){
                                                    
                                                   if(lim_ref[i][1][0] < parseInt(tmp[0].val) && lim_ref[i][1][1] > parseInt(tmp[0].val)){
                                                        ck_lim = 1
                                                        // console.log(lim_ref[i][1][0], lim_ref[i][1][1], parseInt(tmp[0].val))
                                                    } 
                                                    
                                                }else if(o_sub.type == '0'){
                                                    
                                                   if(lim_ref[i][1][0] == parseInt(tmp[0].val) || lim_ref[i][1][1] == parseInt(tmp[0].val)){
                                                        ck_lim = 1
                                                        // console.log(lim_ref[i][1][0], lim_ref[i][1][1], parseInt(tmp[0].val))
                                                    } 
                                                    
                                                }else if(o_sub.type == '1'){
                                                    
                                                    console.log('o_sub.type == 1', tmp)
                                                    // console.log('test', lim_ref[i])
                                                    // console.log('test', lim_ref)
                                                    for(var j = 0; j < tmp[0].val.length; j++){
                                                        // console.log('test', lim_ref[i][1][j], lim_ref.val[j])
                                                        if(lim_ref[i][1][j] == tmp[0].val[j]){
                                                            ck_lim2 += 1
                                                        }
                                                    }
                                                    // console.log(ck_lim2, tmp[0].val.length)
                                                    if(ck_lim2 == tmp[0].val.length){
                                                        ck_lim = 1
                                                    }
                                                    
                                                }else{
                                                    
                                                   if(lim_ref[i][1][0] < parseInt(tmp[0].val) && lim_ref[i][1][1] > parseInt(tmp[0].val)){
                                                        ck_lim = 1
                                                        // console.log(parseInt(o_sub.q_id),parseInt(o_sub.q_sn),lim_ref[i][1][0], lim_ref[i][1][1], parseInt(tmp[0].val), ck_lim)
                                                    } 
                                                    
                                                }
                                                
                                            }else if(lim_ref[i][1].length == 1  ){
                                                // console.log(lim_ref[i][1].length, lim_ref[i][1][0],  parseInt(tmp[0].val))
                                                if(lim_ref[i][1][0] == parseInt(tmp[0].val)){
                                                    
                                                    ck_lim = 1
                                                    // tmp_ck_pre_target = parseInt(o_sub.q_id)
                                                }
                                                // if(tmp_ck_pre_target==)
                                                
                                            }else if(lim_ref[i][1].length > 2){

                                                for(var j = 0; j < lim_ref[i][1].length; j++){

                                                    if(lim_ref[i][1][j] == parseInt(tmp[0].val)){
                                                        ck_lim = 1
                                                    }
                                                    
                                                }
                                            
                                            }
                                            // console.log('ck_lim', ck_lim)
                                            if(ck_lim == 1){
                                                o.ck_pre_tmp += 1
                                                
                                            }else{
                                                o.ck_pre_tmp = 0
                                            }
                                            // console.log("test", o.ck_pre_tmp, lim_ref.length)
                                            if(o.ck_pre_tmp >= lim_ref.length){
                                                console.log(o.target_limitation, typeof(o.target_limitation))
                                                if(typeof(o.target_limitation) == 'string'){
                                                    _.filter(sid_q_setting, function(o2){
                                                        if(o2.q_id == o.q_id && o2.q_sn == o.q_sn){
                                                            // console.log('ttttt'. JSON.parse(o.target_limitation))
                                                            o2.ck_pre_target = JSON.parse(o.target_limitation)[0]
                                                            ck_pre_target = JSON.parse(o.target_limitation)[0]
                                                        }
                                                    })
                                                    
                                                }else{
                                                    _.filter(sid_q_setting, function(o2){
                                                        if(o2.q_id == o.q_id && o2.q_sn == o.q_sn){

                                                            
                                                            o2.ck_pre_target = JSON.parse(o.target_limitation[0])[0]
                                                            ck_pre_target = JSON.parse(o.target_limitation[0])[0]

                                                        }
                                                    })
                                                    // o.ck_pre_target = JSON.parse(o.target_limitation[0])[0]
                                                    // ck_pre_target = JSON.parse(o.target_limitation[0])[0]
                                                }
                                                
                                            }else{
                                                o.ck_pre_target = 0
                                                escape_pre_title = 0
                                            }
                                        }  
                                    }  
                                }
                                
                                
                                
                            } 
                        }
                    })
                    
                    //判斷特殊碼跳答
                    for(var ck_special = 0; ck_special < special_code_arr.length ; ck_special++){
                        //如果使用特殊碼
                        console.log("如果使用特殊碼")
                        if(special_code_arr[ck_special].split(":")[1] == ans_filter[0].val){ 
                            console.log(special_code_arr[ck_special].split(":")[1], ans_filter[0].val)
                            console.log(q_setting_filter[0].target_special_code)
                            if(q_setting_filter[0].target_special_code[0] != undefined && q_setting_filter[0].target_special_code[0][0] != "["){
                                console.log(q_setting_filter[0].target_special_code.length, q_setting_filter[0].target_special_code[0][0])
                               if(q_setting_filter[0].target_special_code[0].length != 0 ){ //&& q_setting_filter[0].target_special_code[0][0] != "["
                                    ck_special_escape = 1
                                    ck_special_target = JSON.parse(q_setting_filter[0].target_special_code)[0]
                                    _.filter(sid_q_setting, function(o){
                                        if(o.q_id == parseInt(qnum) && o.q_sn == 1){
                                            o.ck_target = ck_special_target
                                        }
                                    } )
                                }
                            
                            
                            }else if(q_setting_filter[0].target_special_code.length != 0 && q_setting_filter[0].target_special_code[0][0] == "["){
                                console.log(q_setting_filter[0].target_special_code[0])
                                ck_special_escape = 1
                                if(q_setting_filter[0].type == '2'){
                                    ck_special_target = JSON.parse(q_setting_filter[0].target_special_code)[0]
                                }else{
                                    ck_special_target = JSON.parse(q_setting_filter[0].target_special_code[0])[0]
                                }
                                

                                _.filter(sid_q_setting, function(o){

                                    if(o.q_id == parseInt(qnum) && o.q_sn == 1){
                                        // console.log(o.ck_target, ck_special_target)
                                        o.ck_target = ck_special_target
                                        // console.log(o, o.ck_target, ck_special_target)
                                    }
                                })
                                
                            }
                            else{
                                _.filter(sid_q_setting, function(o){
                                    if(o.q_id == parseInt(qnum) && o.q_sn == 1){
                                        o.ck_target = 0
                                    }
                                })
                                ck_special_target = null
                                ck_special_escape = 0
                            }
                        }
                    }
                    // console.log(ck_special_escape, ck_special_target)
                    //
                    console.log("特殊碼跳答:", ck_special_escape, "到:", ck_special_target)
                    console.log("自己跳答:", ck_escape, "到:", ck_target)
                    console.log("先前題目跳答:", ck_lim, "到:", ck_pre_target)

                    console.log(parseInt(qnum))
                    // console.log('test', sid_q_setting)
                    _.filter(sid_q_setting, function(o){
                        
                        if(o.answer_limitation_ref.length != 0){
                            var lim_ref = JSON.parse(o.answer_limitation_ref)    
                        }else{
                            var lim_ref = 0
                        }
                        if(o.target.length != 0){
                            var esc_ref = o.target
                            // console.log(esc_ref)
                        }else{
                            var esc_ref = 0
                        }
                        // console.log('test', lim_ref, esc_ref)
                        if(lim_ref != 0 && esc_ref == 0){
                            for(var i = 0; i < lim_ref.length; i++){
                                
                                var lim_ref_qnum = lim_ref[i][0][0]
                                var lim_ref_qsn = lim_ref[i][0][1]
                                // console.log('test4',o.q_id ,parseInt(qnum), lim_ref_qnum, lim_ref_qsn)
                                // // console.log('test', lim_ref_qnum, lim_ref_qsn)
                                var tmp =  _.filter(ans_arr, {'num': parseInt(lim_ref_qnum), 'num_n': parseInt(lim_ref_qsn)})
                                // console.log('test4', tmp)
                                // console.log('test4', tmp.length)
                                if(tmp.length != 0 && o.q_id == parseInt(qnum)+1){

                                    // console.log(lim_ref[i][1].length, lim_ref[i][1][0], lim_ref[i][1][1], parseInt(tmp[0].val),tmp ,o)
                                    if(lim_ref[i][1].length == 2){//如果lim_reft長度是2 判斷是數值題或其他題型

                                        console.log('test', o.type)
                                        console.log('test', lim_ref[i][1][0], lim_ref[i][1][1])
                                        console.log('test', tmp[0].val)
                                        console.log('test', o.tmp_ck_pre_target )
                                        if(o.type == '2'){
                                            
                                           if(lim_ref[i][1][0] < parseInt(tmp[0].val) && lim_ref[i][1][1] > parseInt(tmp[0].val)){
                                                escape_pre_title = o.ck_pre_target
                                            } 
                                            if(lim_ref[i][1][0] == parseInt(tmp[0].val)|| lim_ref[i][1][1] == parseInt(tmp[0].val)){
                                                escape_pre_title = o.ck_pre_target
                                                
                                            } 
                                        }else if(o.type == '0'){
                                            
                                           if(lim_ref[i][1][0] == parseInt(tmp[0].val)|| lim_ref[i][1][1] == parseInt(tmp[0].val)){
                                                escape_pre_title = o.ck_pre_target
                                                
                                            } 
                                            if(lim_ref[i][1][0] < parseInt(tmp[0].val) && lim_ref[i][1][1] > parseInt(tmp[0].val)){
                                                escape_pre_title = o.ck_pre_target
                                                
                                            } 
                                            
                                        }else if(o.type == '1'){
                                            console.log('test0')
                                            if(lim_ref[i][1][0] == parseInt(tmp[0].val)|| lim_ref[i][1][1] == parseInt(tmp[0].val)){
                                                escape_pre_title = o.ck_pre_target
                                                
                                            }
                                            if(lim_ref[i][1][0] < parseInt(tmp[0].val) && lim_ref[i][1][1] > parseInt(tmp[0].val)){
                                                escape_pre_title = o.ck_pre_target
                                                
                                            } 
                                            console.log('test0',escape_pre_title)
                                        }else{
                                            console.log('test1')
                                           if(lim_ref[i][1][0] < parseInt(tmp[0].val) && lim_ref[i][1][1] > parseInt(tmp[0].val)){
                                                escape_pre_title = o.ck_pre_target
                                                
                                            } 
                                            
                                        }
                                        
                                    }else if(lim_ref[i][1].length == 1 ){
                                        console.log('test2')
                                        // console.log(lim_ref[i][1][0], parseInt(tmp[0].val), parseInt(lim_ref_qnum),parseInt(lim_ref_qsn))
                                        if(lim_ref[i][1][0] == parseInt(tmp[0].val)){
     
                                            // console.log(lim_ref[i][1][0], parseInt(tmp[0].val),o.ck_pre_target)
                                            escape_pre_title = o.ck_pre_target    
                                            // tmp_ck_pre_target = o.ck_pre_target
                                        }
                                        // console.log(tmp_ck_pre_target)
                                        // if(parseInt(lim_ref_qnum) == tmp_qsn && tmp_ck_pre_target != undefined){
                                        //     escape_pre_title = tmp_ck_pre_target
                                        // }
                                        // var tmp_qsn = parseInt(lim_ref_qnum)
                                        
                                    }else if(lim_ref[i][1].length > 2){
                                        console.log('test3')
                                        for(var j = 0; j < lim_ref[i][1].length; j++){

                                            if(lim_ref[i][1][j] == parseInt(tmp[0].val)){
                                                
                                                escape_pre_title = o.ck_pre_target
                                                // ck_lim = 1
                                                // console.log(o, parseInt(tmp[0].val), lim_ref[i][1][0], escape_pre_title)
                                            }
                                            
                                        }
                                    
                                    }
                                }
                                // if(tmp.length == 0 && o.q_id == parseInt(qnum)){
                                //     var tmp =  _.filter(ans_arr, {'num': parseInt(qnum), 'num_n': parseInt(1)})
                                //     escape_pre_title = o.ck_pre_target
                                // }
                            }
                        }else if(lim_ref == 0 && esc_ref != 0){
                            
                            
                            if(o.q_id == parseInt(qnum)){

                                escape_title = o.ck_target
                                // console.log('test4 escape_title', o.ck_target)
                            }
                           // console.log('test4 escape_title', escape_title)
                            
                        }else if(lim_ref != 0 && esc_ref != 0){
                            if(o.ck_target >　o.ck_pre_target && o.q_id == parseInt(qnum)){
                                console.log('test5')
                                // console.log(o, o.ck_target, o.ck_pre_target)
                                escape_title = o.ck_target    
                            }else if(o.ck_target <　o.ck_pre_target && o.q_id == parseInt(qnum)+1){
                                console.log('test6')
                                escape_pre_title = o.ck_pre_target    
                            }
                            
                        }
                        
                        // console.log('test4 escape_title', escape_title)
                    })
                    
                    // console.log('testres', escape_title, escape_pre_title)
                    if(escape_title > escape_pre_title){
                        res_title = escape_title
                    }else if(escape_title < escape_pre_title){
                        res_title = escape_pre_title
                    }else if(ck_special_target > escape_pre_title && ck_special_target > escape_title){
                        res_title = ck_special_target
                    }
                    if(res_title != undefined && res_title != 0){
                        escape = 1
                    }else{
                        escape = 0
                    }


                    // console.log(escape, res_title)
                    sid_data.q_setting = sid_q_setting
                    
                    db.check.put(sid_data)
                    if(escape == 1){
                        escape_function(fdata, res_title, eleID)    
                    }else{
                        ChangeDiv(eleID, 0, res_title)
                    }
                    
                    

                }).catch(e => {
                    console.error(e.stack || e);
                });

                
                $('html,body').scrollTop(100);
            });

            // $(document).on('click', '.btn_next', function(evt){
            //     evt.preventDefault();
            //     var eleID = $(this).attr('id');
            //     qnum = eleID.split("_")[2];
            //     var change_div_escape, change_div_escapeTitle;
            //     getAns(qnum, fdata);
            //     var element = ans_arr.filter(function(element) {
            //                         return element.num == qnum;
            //                     })

            //     var element2 = _.filter(fdata.q_setting, {'q_id':parseInt(qnum)+1, 'q_sn':parseInt(1)})
            //     var element3 = _.filter(fdata.q_setting, {'q_id':parseInt(qnum), 'q_sn':parseInt(1)})
            //     var special_code_arr = _.chain(element3[0].special_code).replace("[", "").replace("]", "").split(',').value()
            //     for(var ck_special = 0; ck_special < special_code_arr.length ; ck_special++){
            //         // console.log(special_code_arr[ck_special].split(":")[1], element[0].val)
            //         if(special_code_arr[ck_special].split(":")[1] == element[0].val){
            //             var ck_special_res = 1    
            //         }
            //     }
            //     if(element3[0].target_special_code[0] != "" && element3[0].target_special_code != "" && ck_special_res == 1){
            //         if(element3[0].type == "2"){
            //             change_div_escape = 1
            //             // console.log(element3[0].target_special_code)
            //             change_div_escapeTitle = JSON.parse(element3[0].target_special_code)[0]
            //         }else{
            //             change_div_escape = 1
            //             // console.log(element3[0].target_special_code[0])
            //             change_div_escapeTitle = JSON.parse(element3[0].target_special_code[0])[0]
            //         }
            //     }else{
            //        element.filter(function(element) {

            //             if(element.escape == 1){
                            
            //                  change_div_escape = element.escape
            //                  change_div_escapeTitle = element.escape_title
            //             }else{
                            
            //                 change_div_escape = 0
            //                 change_div_escapeTitle = null
            //             }
                       
            //         })
                    
            //         if (element2[0].answer_limitation == "1") {

            //             var ans_lim = JSON.parse(element2[0].answer_limitation_ref)
                        
            //             var ans_cnt = 0
            //             var ans_cnt2 = 0

            //             for(var ans_i = 0; ans_i < ans_lim.length; ans_i++){
            //                 var ans_qid = ans_lim[ans_i][0][0]
            //                 var ans_qsn = ans_lim[ans_i][0][1]
                            
            //                 var ck_ans = _.filter(ans_arr, {'num': ans_qid, 'num_n': ans_qsn})[0]
            //                 var ck_type = _.filter(fdata.q_setting, {'q_id':ans_qid, 'q_sn':ans_qsn})[0].type
            //                 console.log(ans_lim[ans_i][1].length, ans_lim[ans_i][1], ans_qid, ans_qsn, ck_ans, ck_type)
            //                 if(ans_lim[ans_i][1].length == 2){
            //                     if(ck_type != '1'){
            //                        if(ans_lim[ans_i][1][0] < parseInt(ck_ans.val) && ans_lim[ans_i][1][1] > parseInt(ck_ans.val)){
            //                             ans_cnt+=1
            //                         } 
            //                     }else{
            //                         for(var j = 0; j < ck_ans.val.length; j++){
            //                             if(ans_lim[ans_i][1][j] == ck_ans.val[j]){
            //                                 ans_cnt2+=1
            //                             }
            //                         }
            //                         if(ans_cnt2 == ck_ans.val.length){
            //                             ans_cnt+=1
            //                         }

            //                     }
            //                     console.log(ans_cnt2, ck_ans.val.length,ans_cnt)
            //                 }else if(ans_lim[ans_i][1].length == 1){
                                
            //                     if(ans_lim[ans_i][1][0] == parseInt(ck_ans.val)){
                                    
            //                         ans_cnt+=1
            //                     }
            //                 }else if(ans_lim[ans_i][1].length > 2){

            //                     for(var j = 0; j < ans_lim[ans_i][1].length; j++){

            //                         if(ans_lim[ans_i][1][j] == parseInt(ck_ans.val)){
            //                             ans_cnt+=1
            //                         }
            //                         console.log(ans_lim.length, ans_lim[ans_i][1][j], parseInt(ck_ans.val), ans_cnt)
            //                     }
            //                 }
            //             }

                        
            //             if(ans_cnt == ans_lim.length){
                            
            //                 change_div_escape = 1
            //                 change_div_escapeTitle = JSON.parse(element2[0].target_limitation[0])[0]
            //                 console.log(123,change_div_escapeTitle,change_div_escape)
            //             }
                        
            //         } 
            //     }
            //     console.log(eleID, change_div_escape, change_div_escapeTitle)
                
                
            //     ChangeDiv(eleID, change_div_escape, change_div_escapeTitle)
            //     $('html,body').scrollTop(100);
            // });
            
            $(document).on('click', '.btn_falBback', function(evt){
                evt.preventDefault();
                
                
                
                getAns($(this).attr('id'), fdata)
                // $('#btn_editanswer').show()
                $('#btn_last_'+$('.btn_falBback').attr('id')).attr('disabled', false)
                $('#btn_next_'+$('.btn_falBback').attr('id')).attr('disabled', false)
                var eleID  = 'btn_find_'+$(this).attr('name');
                ChangeDiv(eleID);
            })
            
            $(document).on('click', '.btn_find', function(evt){
                evt.preventDefault();
                var eleID  = $(this).attr('id');
                // $('#btn_editanswer').hide()
                // $('.btn_falBback').show()
                $('.btn_falBback').attr({'id': eleID.split("_")[2]})
                // $('#btn_last_'+eleID.split("_")[2]).attr('disabled', true)
                // $('#btn_next_'+eleID.split("_")[2]).attr('disabled', true)

                // deleteArray()
                ans_arr = $.grep(ans_arr, function(e){ 
                     return e.num < eleID.split("_")[2]
                });
                ChangeDiv(eleID);
                $("#Md_Editanswer").modal('hide');
            });



            $(document).on('change', '.InOrOut', function(evt){
                evt.preventDefault();
                var tmp = $(this).attr('name')
                $('.stv[name="'+tmp+'"]').hide()
                $('.outText[name="'+tmp+'"]').hide()
                $('.test_'+tmp).val('default')
                $('.test_'+tmp).selectpicker('refresh')
                
                var sV = $('.InOrOut[name="'+tmp+'"] option:selected').val()
                if(sV == 0){
                    $('.stv[name="'+tmp+'"]').show()
                }else if(sV == 1){
                    $('.outText[name="'+tmp+'"]').show()
                }
            });
        }

        $.fn.scrollView = function () {
            return this.each(function () {
                $('html, body').animate({
                    scrollTop: $(this).offset().top
                }, 750);
            });
        }
        function putAns(fdata){
            q_info = fdata
            
            var db = new Dexie("CAPI");
            db.version(1).stores({
                project: "prjid",                       // 專案清單
                prjwork: "[prjid+Sid]",            // 樣本清單
                questionnaire: "++tkey, [prjid+qid]",   // 問卷設定清單
                sample_status: "[prjid+Sid]",                   // 訪問代碼
                ans: '[prjid+Sid]',                             // 問卷結果
                vill_list: "++tkey"                     // 村里清單
            });
            db.transaction('rw', db.ans, db.questionnaire, async(obj) => {
                // Query:
                return Dexie.Promise.all(
                    db.ans.where('[prjid+Sid]').equals([q_info.prjid, q_info.Sid]).toArray(),
                    db.questionnaire.toArray()
                )
                
            }).then (function (obj) {

                if(obj[0][0] != undefined){
                        
                    var temp_ans = obj[0][0]['ans']
                    console.log("ans test", temp_ans)
                    ans_arr = temp_ans
                    for(var t = 0; t< temp_ans.length; t++){
                        // var cnt = 0;
                        x = temp_ans[t].num
                        y = temp_ans[t].num_n
                        var ans_element = _.filter(temp_ans, {'num': x, 'num_n': y})[0]     
                        var element = _.filter(q_info.q_setting, {'q_id': parseInt(x), 'q_sn': parseInt(y)})[0]
                        // console.log(ans_element)
                            if(element.type == "0"){//單選
                                
                                if(ans_element.val.split('_')[1] != undefined){
                                    
                                    $("input[name=" + x + "_" + y + "]").parent().find('#input_note_'+x+'_'+y+'_'+ans_element.val.split('_')[0]).val(ans_element.val.split('_')[1])
                                    $("input[name=" + x + "_" + y + "]").parent().find('#input_note_'+x+'_'+y+'_'+ans_element.val.split('_')[0]).css({'display': 'block'})
                                    $('input[name="'+x+'_'+y+'"][value="'+ans_element.val.split('_')[0]+'"]').parent().addClass("active")

                                }
                                else{
                                    if(ans_element.ans.indexOf('1') == "0"){
                                        
                                        $('input[name="'+x+'_'+y+'"][type="radio"]').parent().attr({'disabled': true})
                                        $('input[name="'+x+'_'+y+'"][type="radio"]').parent().css("background-color", "#9d3535");
                                        $('input[name="'+x+'_'+y+'"]').attr({'placeholder': ans_element.ans+'_'+ans_element.val, 'disabled': true})
                                    }else{
                                        $('input[name="'+x+'_'+y+'"][value="'+ans_element.val+'"]').parent().addClass("active")
                                    }
                                    
                                }
                                
                            }else if(element.type == "1"){//複選
                                
                                if(ans_element.ans[0].indexOf('1') == "0"){
                                    
                                    $('input[name="'+x+'_'+y+'"]').val()
                                    $('input[name="'+x+'_'+y+'"]').attr({'placeholder': ans_element.ans[0]+'_'+ans_element.val[0], 'disabled': true})
                                    $('input[name="'+x+'_'+y+'"]').parent().find('#input_note_'+x+'_'+y).css({'display': 'none'})
                                    $('input[name="'+x+'_'+y+'"]').css("background-color", "#9d3535");

                                }else{
                                    for(var i = 0; i < ans_element.val.length; i++){
                                        if(ans_element.val[i].split('_')[1] != undefined){
                                        
                                            $('input[name="'+x+'_'+y+'"][value="'+ans_element.val[i].split('_')[0]+'"]').parent().find('#input_note_'+x+'_'+y).val(ans_element.val[i].split('_')[1])
                                            $('input[name="'+x+'_'+y+'"][value="'+ans_element.val[i].split('_')[0]+'"]').parent().find('#input_note_'+x+'_'+y).css({'display': 'block'})
                                            $('input[name="'+x+'_'+y+'"][value="'+ans_element.val[i].split('_')[0]+'"]').attr({'checked': 'checked'})

                                        }else{
                                            
                                            $('input[name="'+x+'_'+y+'"][value="'+ans_element.val[i]+'"]').attr({'checked': 'checked'})

                                        }
                                    }
                                }
                                
                                
                                

                            }else if(element.type == "2"){//數值
                                
                                if(ans_element.ans.split('_')[1] != undefined){
                                    $('input[name="'+x+'_'+y+'"]').attr({'placeholder': ans_element.ans+'_'+ans_element.val, 'disabled': true})
                                    $('input[name="'+x+'_'+y+'"]').css("background-color", "#9d3535");
                                }else{
                                    $('input[name="'+x+'_'+y+'"]').val(ans_element.val)
                                }
                                
                                
                            }else if(element.type == "3"){
                                if(ans_element.ans.indexOf('1') == "0"){
                                    $('input[name="'+x+'_'+y+'"]').attr({'placeholder': ans_element.ans+'_'+ans_element.val, 'disabled': true})
                                    $('input[name="'+x+'_'+y+'"]').css("background-color", "#9d3535");
                                }else{
                                    $('input[name="'+x+'_'+y+'"]').val(ans_element.val)
                                }
                            }else if(element.type == "4"){
                                
                                if(ans_element.val.indexOf('市') == -1 && ans_element.val.indexOf('縣') == -1){
                                
                                    $('.InOrOut[name="'+x+'_'+y+'"]').selectpicker('val', "1")
                                    $('.outText[name="'+x+'_'+y+'"]').css({'display':'block'}).val(ans_element.val)
                                
                                }else{
                                
                                    $('.stv[name="'+x+'_'+y+'"]').css({'display': 'block'})
                                    $('.InOrOut[name="'+x+'_'+y+'"]').selectpicker('val', "0")
                                    $('.city[name="'+x+'_'+y+'"]').selectpicker('val', ans_element.val.slice(0, 3))
                                    //town
                                    var st1 = town.filter(x=>x.city === ans_element.val.slice(0, 3))
                                    
                                    $(".town[name='"+x+'_'+y+"']").empty();
                                    $(".town[name='"+x+'_'+y+"']").append(
                                        $('<option>', {value:'',text:'請選擇區'})
                                    );
                                    for(key1 in st1){
                                        opt1 = $('<option>', {value:st1[key1].town,text:st1[key1].town})
                                        $(".town[name='"+x+'_'+y+"']").append(
                                            $(opt1)
                                        );
                                    }
                                    $(".town[name='"+x+'_'+y+"']").append($('<option>', {value:'999',text:'不知道 / 拒答'}));
                                    $(".town[name='"+x+'_'+y+"']").selectpicker("refresh");
                                    $('.town[name="'+x+'_'+y+'"]').selectpicker('val', ans_element.val.slice(3, 6))

                                }
                            }else if(element.type == "6" || element.type == "5"){
                                $('.dynamic_display').each(function(evt){
                                    dynamic_ids.push($(this).attr('id').split('_'))
                                })
                                                            
                                if(y == 1){
                                    if(ans_element.ans.split('_')[1] != undefined){

                                        // $("input[name='"+x+'_'+y+"']").val("")
                                        $("input[name='"+x+'_'+y+"']").parent().attr({'disabled': true})
                                        $("input[name='"+x+'_'+y+"']").attr({'placeholder': ans_element.ans+'_'+ans_element.val})
                                        $("input[name='"+x+'_'+y+"']").parent().css("background-color", "#9d3535");
                                    }else{
                                        $("input[name='"+x+'_'+y+"'][value='"+ans_element.val+"']").attr({'checked': 'checked'})
                                        $("input[name='"+x+'_'+y+"'][value='"+ans_element.val+"']").parent().addClass("active")
                                        var tmp = parseInt(ans_element.val) 
                                    }
                                    
                                }else{
                                    var ans_element = _.filter(temp_ans, function(o) {
                                                            if(o.num == x && o.num_n == y){
                                                                return o
                                                            }
                                                        });
                                    var sub_element = _.filter(q_info.q_setting, {'q_id': parseInt(x), 'q_sn': parseInt(y)})[0]
                                    if(y == last_y){

                                        
                                        cnt += 1

                                    }else{
                                        var cnt = 0;
                                        var last_y = 0

                                    }
                                    // 
                                    if(ans_element[cnt].ans.split('_')[0] == "1" && sub_element.sub_type == "0"){
                                       $("input[name='"+x+'_'+y+'_'+cnt+"']").each(function(){
                                            $(this).parent().attr({'disabled': true})
                                            $(this).parent().css("background-color", "#9d3535");
                                            $(this).attr({'placeholder': ans_element[cnt].ans+'_'+ans_element[cnt].val})
                                        }) 
                                    }else{
                                        
                                        if(sub_element.sub_type == "0"){


                                            if(ans_element[cnt].val.split('_')[1] != undefined){

                                                $("input[name='"+x+'_'+y+'_'+cnt+"'][value='"+ans_element[cnt].val.split('_')[0]+"']").parent().addClass('active')    
                                                $("#input_note_"+x+"_"+y+"_"+cnt).val(ans_element[cnt].val.split('_')[1])
                                                $("#input_note_"+x+"_"+y+"_"+cnt).show()
                                            }else{
                                                $("input[name='"+x+'_'+y+'_'+cnt+"'][value='"+ans_element[cnt].val+"']").attr({'checked': 'checked'})
                                                $("input[name='"+x+'_'+y+'_'+cnt+"'][value='"+ans_element[cnt].val+"']").parent().addClass('active')    
                                            }
                                            
                                        }else if(sub_element.sub_type == "2" || sub_element.sub_type == "3"){

                                            if(ans_element[cnt].ans.split('_')[1] != undefined){
                                                $("input[name='"+x+'_'+y+'_'+cnt+"']").val("")
                                                $("input[name='"+x+'_'+y+'_'+cnt+"']").attr({'placeholder': ans_element[cnt].ans+'_'+ans_element[cnt].val, 'disabled': true})
                                                $("input[name='"+x+'_'+y+'_'+cnt+"']").css("background-color", "#9d3535");
                                            }else{
                                                $("input[name='"+x+'_'+y+'_'+cnt+"']").val(ans_element[cnt].val)
                                            }
                                            
                                        }
                                    }
                                    var last_y = y
                                     _.map(dynamic_ids, function(array){
                                    
                                        if(array[2] == x.toString()){
                                            
                                            if(parseInt(array[4]) > cnt){

                                                var target_tohide_id = array.join('_')

                                                $("#" + target_tohide_id).hide()

                                            }
                                            else{

                                                var target_tohide_id = array.join('_') 
                                                $("#" + target_tohide_id).show() 
                                                
                                            }
                                        }
                                        

                                    }) 
                                }
                               
                            }
                              

                    }

                    if((parseInt(temp_ans[(temp_ans.length - 1)].num) + 1) > parseInt(temp_ans[(temp_ans.length - 1)].num)){
                        if(temp_ans[(temp_ans.length - 1)].escape == "1"){
                            var div_num = temp_ans[(temp_ans.length - 1)].escape_title
                        }else{
                            var div_num = parseInt(temp_ans[(temp_ans.length - 1)].num + 1)
                        }
                        
                    }
                    
                    $.confirm({
                        title: '',
                        content: "將從 " + div_num + " 題開始繼續訪問",
                        buttons: {
                            confir: {
                                text: '確認',
                                btnClass: 'btn-blue confirm',
                                action: function(){
                                    
                                    $('.page').hide()
                                    $('#div_'+div_num).show()
                                   
                                }
                            },
                            cancle: {
                                text: '取消',
                                btnClass: 'btn-red confirm',
                                action: function(){
                                    window.location = "https://capi.geohealth.tw/php/selprj.php";
                                    
                                }
                            }
                        }
                        
                    });
                }else{
                    // addAns(q_info.prjid, q_info.Sid)
                    $(".page").hide();
                    // Initial First Div_Question
                    $("#div_1").show();

        
                }
            })
        
            
        }

        
        function DetectType(fdata){
            // console.log('fdata:',fdata)
            console.log(fdata.range_min, fdata.range_max)
            if(fdata.type == 0){//單選
                if(fdata.opt_txt.length > 13){
                    var btndiv = $('<div>').attr({'class': 'btn-group col-sm-12','data-toggle':'buttons'});
                    var btndiv_1 = $('<div>').attr({'class': 'btn-group col-sm-6'});
                    var btndiv_2 = $('<div>').attr({'class': 'btn-group col-sm-6'});
                    var half_len = Math.ceil(fdata.opt_txt.length / 2);
                    for(var i = 0; i < half_len; i++){
                        var opts = $('<input>').attr({  'class': 'btn_radio',
                                                        'type': 'radio',
                                                        'name': fdata.q_id + '_' + fdata.q_sn,
                                                        'value': fdata.opt_value[i],
                                                        'note': (fdata.note[i] == 1 ?1 :0),
                                                        'style': 'display: none',
                                                         required: true});
                        var lbl = $('<label>').html(fdata.opt_txt[i]).attr({'class': 'btn btn-default radio_lbl', 'style':'width:70%;text-align:left'})
                                  
                        var tmp_div = $('<div>').attr({'class': 'col-sm-12'})
                        opts.appendTo(lbl)
                        lbl.appendTo(tmp_div)
                        tmp_div.appendTo(btndiv_1)
                    }
                    for(var i = half_len ; i < fdata.opt_txt.length; i++){
                        var opts = $('<input>').attr({  'class': 'btn_radio',
                                                        'type': 'radio',
                                                        'name': fdata.q_id + '_' + fdata.q_sn,
                                                        'value': fdata.opt_value[i],
                                                        'note': (fdata.note[i] == 1 ?1 :0),
                                                        'style': 'display: none',
                                                         required: true});
                        var lbl = $('<label>').html(fdata.opt_txt[i]).attr({'class': 'btn btn-default radio_lbl', 'style':'width:70%;text-align:left'})
                                  
                        var tmp_div = $('<div>').attr({'class': 'col-sm-12'})
                        opts.appendTo(lbl)
                        lbl.appendTo(tmp_div)
                        tmp_div.appendTo(btndiv_2)
                    }
                    btndiv_1.appendTo(btndiv)
                    btndiv_2.appendTo(btndiv)
                }else{
                    var btndiv = $('<div>').attr({'class': 'btn-group col-sm-12','data-toggle':'buttons'});
                    for(var i = 0; i < fdata.opt_txt.length; i++){
                        var opts = $('<input>').attr({  'class': 'btn_radio',
                                                        'type': 'radio',
                                                        'name': fdata.q_id + '_' + fdata.q_sn,
                                                        'value': fdata.opt_value[i],
                                                        'note': (fdata.note[i] == 1 ?1 :0),
                                                        'style': 'display: none',
                                                         required: true});
                        var lbl = $('<label>').html(fdata.opt_txt[i]).attr({'class': 'btn btn-default radio_lbl', 'style':'width:50%;text-align:left'})
                                  
                        var tmp_div = $('<div>').attr({'class': 'col-sm-12'})
                        opts.appendTo(lbl)
                        lbl.appendTo(tmp_div)
                        tmp_div.appendTo(btndiv)
                    }
                }
                
                for(var nt_i = 0; nt_i < fdata.note.length; nt_i++){
                    
                    if(fdata.note[nt_i] =='1'){

                        var note_tmp = $('<div>').attr({'class': 'col-sm-12'}).append($("<input>").attr({'id':'input_note_' + fdata.q_id + '_' + fdata.q_sn + '_' + fdata.opt_value[nt_i],
                                                         'disabled': true,
                                                         'class': 'form-control',
                                                         'type': 'text',
                                                         'style':'width:50%;display:none;margin-top:1em;margin-left:0em;',
                                                         'placeholder':'請輸入說明文字',
                                                         'name': fdata.q_id + '_' + fdata.q_sn}))
                    }else if(fdata.note[nt_i] =='2'){
                        var note_tmp = $('<div>').attr({'class': 'col-sm-12'}).append($("<input>").attr({'id':'input_note_' + fdata.q_id + '_' + fdata.q_sn + '_' + fdata.opt_value[nt_i],
                                                         'disabled': true,
                                                         'class': 'form-control',
                                                         'type': 'number',
                                                         'style':'width:50%;display:none;margin-top:1em;margin-left:0em;',
                                                         'placeholder':'請輸入數值',
                                                         'name': fdata.q_id + '_' + fdata.q_sn}))
                    }else{
                        var note_tmp = $("<span>")
                    }
                    
                    note_tmp.appendTo(btndiv)
                }
                // console.log(fdata.note,_.chain(fdata.note).uniq().includes('1').value())
                // var input_note =$('<div>').attr({'class': 'col-sm-12'}).append(
                //                     (_.chain(fdata.note).uniq().includes('1').value() == false  ? $("<span>")
                //                                                                                 : $("<input>").attr({'id':'input_note_' + fdata.q_id + '_' + fdata.q_sn,
                //                                                                                                      'disabled': true,
                //                                                                                                      'class': 'form-control',
                //                                                                                                      'type': 'text',
                //                                                                                                      'style':'width:50%;display:none;margin-top:1em;margin-left:0em;',
                //                                                                                                      'placeholder':'請輸入說明文字',
                //                                                                                                      'name': fdata.q_id + '_' + fdata.q_sn})
                //                     )
                //                 )
                // input_note.appendTo(btndiv)
                // console.log(btndiv)
                return btndiv;
            }else if(fdata.type == 1){//複選
                var btndiv = $('<div>').attr({'class': 'col-sm-12'});
                for(var i = 0; i < fdata.opt_txt.length; i++){
                    var opts = $('<input>').attr({  'class': '',
                                                    'type': 'checkbox',
                                                    'name': fdata.q_id + '_' + fdata.q_sn,
                                                    'value': fdata.opt_value[i],
                                                    'note': (fdata.note[i] == 1 ?1 :0),
                                                     required: true});
                    var lbl = $('<label>').attr({'class': 'checkbox_lbl col-sm-12', 'style': 'display: -webkit-box;'})
                    var tmp_div = $('<div>').attr({'class': 'col-sm-12'})
                    


                    if(fdata.note[i] =='1'){

                        var note_tmp = $("<input>").attr({ 'id':'input_note_' + fdata.q_id + '_' + fdata.q_sn,
                                                             'class': 'form-control',
                                                             'type': 'text',
                                                             'style':'width: 50%; margin-left:0.5em;',
                                                             'placeholder':'請輸入說明文字',
                                                             'style':'display:none',
                                                             'name': fdata.q_id + '_' + fdata.q_sn})
                    }else if(fdata.note[i] =='2'){
                        var note_tmp = $("<input>").attr({ 'id':'input_note_' + fdata.q_id + '_' + fdata.q_sn,
                                                             'class': 'form-control',
                                                             'type': 'number',
                                                             'style':'width: 50%; margin-left:0.5em;',
                                                             'placeholder':'請輸入數值',
                                                             'style':'display:none',
                                                             'name': fdata.q_id + '_' + fdata.q_sn})
                    }else{
                        var note_tmp = $("<span>")
                    }
                    
                    lbl.append(opts).append($("<span>").html(fdata.opt_txt[i])).append(note_tmp)

                    // lbl.append(opts).append(
                    //     $("<span>").html(fdata.opt_txt[i])
                    // ).append(
                    //     (fdata.note[i] == 1 ?$("<input>").attr({ 'id':'input_note_' + fdata.q_id + '_' + fdata.q_sn,
                    //                                              'class': 'form-control',
                    //                                              'type': 'text',
                    //                                              'style':'width: 50%; margin-left:0.5em;',
                    //                                              'placeholder':'請輸入說明文字',
                    //                                              'style':'display:none',
                    //                                              'name': fdata.q_id + '_' + fdata.q_sn})
                    //                         :$("<span>")
                    //     )
                    // )
                    lbl.appendTo(tmp_div)
                    tmp_div.appendTo(btndiv)
                }
                
                return btndiv;
            }else if(fdata.type == 2){//數值

                var inputdiv = $('<div>').attr({'class': 'col-sm-12'}).append(
                                    $('<input>').attr({ 'class': 'form-control ValidateNumber',
                                                        'type': 'number',
                                                        'name':fdata.q_id + '_' + fdata.q_sn,
                                                        'data_min': fdata.range_min,
                                                        'data_max': fdata.range_max,
                                                        'placeholder': '請輸入數字',
                                                        'style': 'height: 2em; width: 15em;'})
                                )
                return inputdiv;
            }else if(fdata.type == 4){//縣市
                var inputdiv_InOrOut = $('<div>').attr({'class': 'col-sm-12'})
                var inputdiv = $('<div>').attr({'class': 'col-sm-12 stv', 'name':fdata.q_id + '_' + fdata.q_sn, 'style': 'padding-left: 0; display: none;'})

                var InOrOut = $('<select>').attr({'class': 'selectpicker InOrOut',
                                                'name':fdata.q_id + '_' + fdata.q_sn}).append(
                                                    $('<option>', {value:'',text:'國內 / 國外'})
                                                ).append(
                                                    $('<option>', {value:0,text:'國內'})
                                                ).append(
                                                    $('<option>', {value:1,text:'國外'})
                                                )

                var inputText = $('<div>').attr({'class': 'col-sm-12', 'style': 'padding-left: 0'}).append(
                                    $('<input>').attr({ 'class': 'form-control outText',
                                                        'type': 'text',
                                                        'name':fdata.q_id + '_' + fdata.q_sn,
                                                        'data_min': fdata.range_min,
                                                        'data_max': fdata.range_max,
                                                        'placeholder': '請輸入國外地區',
                                                        'style': 'height: 2.5em; width: 30em;display: none;'})
                                )



                var selectCity = $('<select>').attr({'class': 'selectpicker city test_'+fdata.q_id + '_' + fdata.q_sn,
                                                'name':fdata.q_id + '_' + fdata.q_sn,
                                                'data-size': '5',
                                                'title': '請選擇縣市'})
                for(key in city){
                    
                    opt1 = $('<option>', {value:city[key].city,text:city[key].city})
                    selectCity.append( 
                        $(opt1)
                    );
                }
                selectCity.append($('<option>', {value:'999',text:'不知道 / 拒答'}));

                var selectTown = $('<select>').attr({'class': 'selectpicker  town test_'+fdata.q_id + '_' + fdata.q_sn,
                                                'name':fdata.q_id + '_' + fdata.q_sn,
                                                'data-size': '5',
                                                'title': '請選擇區'})
                

                // var selectVillage = $('<select>').attr({'class': 'selectpicker  village test_'+fdata.q_id + '_' + fdata.q_sn,
                //                                 'name':fdata.q_id + '_' + fdata.q_sn}).append(
                //                                     $('<option>', {value:'',text:'請選擇鄉鎮'})
                //                                 )
                
                inputdiv_InOrOut.append(InOrOut).append(inputText).append(inputdiv.append(selectCity).append(selectTown))
                    // .append(selectVillage))
                
            
                return inputdiv_InOrOut;
            }else if(fdata.type == 3){//簡答題
                var inputdiv = $('<div>').attr({'class': 'col-sm-12'}).append(
                                    $('<input>').attr({ 'class': 'form-control',
                                                        'type': 'text',
                                                        'name':fdata.q_id + '_' + fdata.q_sn,
                                                        'placeholder': '請輸入字串',
                                                        'style': 'height: 2.5em; width: 40em;'})
                                )
                return inputdiv;
            }else if(fdata.type == 6 && fdata.q_sn == 1 && fdata.sub_type == 0){
                var btndiv = $('<div>').attr({'class': 'btn-group col-sm-12','data-toggle':'buttons'});
                for(var i = 0; i < fdata.opt_txt.length; i++){
                    var opts = $('<input>').attr({  'class': 'ChangeNameLength',
                                                    'type': 'radio',
                                                    'name': fdata.q_id + '_' + fdata.q_sn,
                                                    'value': fdata.opt_value[i],
                                                    'note': (fdata.note[i] == 1 ?1 :0),
                                                    'style': 'display: none',
                                                     required: true});
                    var lbl = $('<label>').html(fdata.opt_txt[i]).attr({'class': 'btn btn-default radio_lbl', 'style':'width:50%;text-align:left'})
                              
                    var tmp_div = $('<div>').attr({'class': 'col-sm-12'})
                    opts.appendTo(lbl)
                    lbl.appendTo(tmp_div)
                    tmp_div.appendTo(btndiv)
                }
                var input_note =$('<div>').attr({'class': 'col-sm-12'}).append(
                                    (_.chain(fdata.note).uniq().includes('1').value() == false  ? $("<span>")
                                                                                                : $("<input>").attr({'id':'input_note_' + fdata.q_id + '_' + fdata.q_sn,
                                                                                                                     'class': 'form-control',
                                                                                                                     'type': 'text',
                                                                                                                     'style':'width:50%;display: none;',
                                                                                                                     'placeholder':'請輸入說明文字',
                                                                                                                     'name': fdata.q_id + '_' + fdata.q_sn})
                                    )
                                )
                input_note.appendTo(btndiv)
                return btndiv;
            }else if(fdata.type == 6 && fdata.sub_type == 7){
                var namediv = $('<div>').attr({'class': 'col-sm-12', 'style': 'display: inline-flex;'})
                for (var j = 0; j < 5; j++){
                    var divtmp    = $('<div>').attr({'style': 'display: inline',
                                                     'class': 'dynamic_display col-sm-3',
                                                     'id': 'div_dynamic_'+ fdata.q_id + '_' + fdata.q_sn + '_' + j})
                    if(fdata.special_code != ""){
                        var btn_specode_sub =   $("<button>").attr({'id': 'btn_specode_'+ fdata.q_id + '_' + fdata.q_sn + '_' + j ,
                                                                    'class': 'btn btn-default btn-sm btn-specialcodes',
                                                                    'style': 'width: 100%;'}).append(
                                                                    $("<span>").attr({'class' : 'glyphicon glyphicon-info-sign'})
                                                )
                        divtmp.append(btn_specode_sub);
                    }
                    var inputdiv =  $('<input>').attr({ 'class': 'form-control input_names dynamic_display',
                                                        'type': 'text',
                                                        'id': 'input_names_' + fdata.q_id + '_' + fdata.q_sn + '_' + j,
                                                        'name': fdata.q_id + '_' + fdata.q_sn + '_' + j,
                                                        'placeholder': '請輸入人名',
                                                        'style': 'height: 2.5em; width: 100%; display: inline'})
                    divtmp.append(inputdiv)
                    divtmp.appendTo(namediv)
                }
                return namediv;
            }else if (fdata.type == 6 && fdata.sub_type == 0 ){
                var inputdiv = $('<div>').attr({'class': 'col-sm-12', 'style': 'display: inline-flex;'})
                for (var j = 0; j < 5; j++){
                    var btndiv = $('<div>').attr({'class': 'col-sm-3 btn-group dynamic_display',
                                                  'id': 'div_dynamic_'+ fdata.q_id + '_' + fdata.q_sn + '_' + j,
                                                  'data-toggle': 'buttons',
                                                  'style': 'display: inline'})
                    var span_name = $('<span>').attr({'class': 'fillname',
                                                      'id': 'span_name_'+ fdata.q_id + '_' + fdata.q_sn + '_' + j })
                    btndiv.append(span_name);
                    if(fdata.special_code != ""){
                        var btn_specode_sub =   $("<button>").attr({'id': 'btn_specode_'+ fdata.q_id + '_' + fdata.q_sn + '_' + j ,
                                                                    'class': 'btn btn-default btn-sm btn-specialcodes',
                                                                    'style': 'width: 100%;'}).append(
                                                                    $("<span>").attr({'class' : 'glyphicon glyphicon-info-sign'})
                                                )
                        btndiv.append(btn_specode_sub);
                    }
                    for(var i = 0; i < fdata.opt_txt.length; i++){
                        var opts = $('<input>').attr({  'class': 'btn_radio',
                                                        'type': 'radio',
                                                        'name': fdata.q_id + '_' + fdata.q_sn + '_' + j,
                                                        'value': fdata.opt_value[i],
                                                        'note': (fdata.note[i] == 1 ?1 :0),
                                                        'style': 'display: none',
                                                         required: true});
                        var lbl = $('<label>').html(fdata.opt_txt[i]).attr({'class': 'btn btn-default radio_lbl', 'style':'width:100%;text-align:left'})
                                  
                        var tmp_div = $('<div>')
                        opts.appendTo(lbl)
                        lbl.appendTo(tmp_div)
                        tmp_div.appendTo(btndiv)
                    }
                    btndiv.appendTo(inputdiv)
                    var input_note =$('<div>').append(
                                    (_.chain(fdata.note).uniq().includes('1').value() == false  ? $("<span>")
                                                                                                : $("<input>").attr({'id':'input_note_' + fdata.q_id + '_' + fdata.q_sn + '_' + j,
                                                                                                                     'class': 'form-control',
                                                                                                                     'type': 'text',
                                                                                                                     'style':'width:100%;display: none;',
                                                                                                                     'placeholder':'請輸入說明文字',
                                                                                                                     'name': fdata.q_id + '_' + fdata.q_sn})
                                    )
                                )
                    input_note.appendTo(btndiv)
                }

                return inputdiv;
            }else if (fdata.type == 6 && fdata.sub_type == 2){
                var inputdiv = $('<div>').attr({'class': 'col-sm-12', 'style': 'display: inline-flex;'})
                for (var j = 0; j < 5; j++){
                    var divtmp    = $('<div>').attr({'style': 'display: inline',
                                                     'class': 'dynamic_display col-sm-3',
                                                     'id': 'div_dynamic_'+ fdata.q_id + '_' + fdata.q_sn + '_' + j})
                    var span_name = $('<span>').attr({'class': 'fillname',
                                                      'id': 'span_name_'+ fdata.q_id + '_' + fdata.q_sn + '_' + j })
                    divtmp.append(span_name);
                    if(fdata.special_code != ""){
                        var btn_specode_sub =   $("<button>").attr({'id': 'btn_specode_'+ fdata.q_id + '_' + fdata.q_sn + '_' + j ,
                                                                    'class': 'btn btn-default btn-sm btn-specialcodes',
                                                                    'style': 'width: 100%;'}).append(
                                                                    $("<span>").attr({'class' : 'glyphicon glyphicon-info-sign'})
                                                )
                        divtmp.append(btn_specode_sub);
                    }
                    var inputtmp =  $('<input>').attr({ 'class': 'form-control ValidateNumber dynamic_display',
                                                        'type': 'number',
                                                        'name':fdata.q_id + '_' + fdata.q_sn + '_' + j,
                                                        'id': 'input_number_' + fdata.q_id + '_' + fdata.q_sn + '_' + j,
                                                        'data_min': fdata.range_min,
                                                        'data_max': fdata.range_max,
                                                        'placeholder': '請輸入數字',
                                                        'style': 'height: 2em; width: 100%;'})

                    divtmp.append(inputtmp)
                    divtmp.appendTo(inputdiv)
                }
                return inputdiv;
            }else if (fdata.type == 6 && fdata.sub_type == 3){
                var inputdiv = $('<div>').attr({'class': 'col-sm-12', 'style': 'display: inline-flex;'})
                for (var j = 0; j < 5; j++){
                    var divtmp    = $('<div>').attr({'style': 'display: inline',
                                                     'class': 'dynamic_display col-sm-3',
                                                     'id': 'div_dynamic_'+ fdata.q_id + '_' + fdata.q_sn + '_' + j})
                    var span_name = $('<span>').attr({'class': 'fillname',
                                                      'id': 'span_name_'+ fdata.q_id + '_' + fdata.q_sn + '_' + j })
                    divtmp.append(span_name);
                    if(fdata.special_code != ""){
                        var btn_specode_sub =   $("<button>").attr({'id': 'btn_specode_'+ fdata.q_id + '_' + fdata.q_sn + '_' + j ,
                                                                    'class': 'btn btn-default btn-sm btn-specialcodes',
                                                                    'style': 'width: 100%;'}).append(
                                                                    $("<span>").attr({'class' : 'glyphicon glyphicon-info-sign'})
                                                )
                        divtmp.append(btn_specode_sub);
                    }
                    var inputtmp =  $('<input>').attr({ 'class': 'form-control',
                                                        'type': 'text',
                                                        'name':fdata.q_id + '_' + fdata.q_sn + '_' + j,
                                                        'placeholder': '請輸入文字',
                                                        'style': 'height: 2em; width: 100%;'})
                    divtmp.append(inputtmp)
                    divtmp.appendTo(inputdiv)
                }
                return inputdiv;
            }else if (fdata.type == 6 && fdata.sub_type == 8){
                var inputdiv =  $('<div>').attr({'class': 'col-sm-12'}).append(
                                    $('<button>').html('點此載入對象清單').attr(
                                        {'id': 'btn_familiar_pairs_' + fdata.q_id + '_' + fdata.q_sn,
                                        'class': 'btn btn-warning btn_familiar_pairs'
                                    })
                                ).append(
                                    $('<div>').attr({'id': 'div_familiar_pairs_' + fdata.q_id + '_' + fdata.q_sn})
                                )
                return inputdiv;
            }
        }

        function ChangeDiv(eleID, escape, target, lastNum, ck_type) {
            
            var arr_tmp = eleID.split('_');
            var type    = $(arr_tmp).get(-2);
            
            if(escape == 1){
                id = target
                
            }else{
                if(type == "last"){
                    // var id = Number($(arr_tmp).get(-1)) - 1;
                    var id = lastNum;
                }else if(type == "next"){
                    var id = Number($(arr_tmp).get(-1)) + 1;

                }else{
                    // console.log(Number($(arr_tmp).get(-1)))
                    var id = Number($(arr_tmp).get(-1));
                }
            }
            
            // get all pages, loop through them and hide them
            var pages = document.getElementsByClassName('page');

            for(var i = 0; i < pages.length; i++) {
                pages[i].style.display = 'none';
            }

            // // then show the requested page
            var id_targetDiv = "div_" + id;
            // console.log('id_targetDiv:', id_targetDiv)
            var ele = document.getElementById(id_targetDiv);
            ele.style.display = 'block';
            // $('#div_'+id).scrollView()
        }
        
        function CheckTargetinfo(evttype){
            
            if(tmp_project.style == "0"){
                console.log("是否已進行戶中抽樣", sample_status)

                // if(sample_status == 0){
                var household_total = $("input[name='household_total']").parent('.active').find("input[name='household_total']").val()
                var target_name     = $("input[name='target_name']").val()
                var istarget        = $("input[name='istarget']").parent('.active').find("input[name='istarget']").val()
                var istarget2        = $("input[name='istarget2']").parent('.active').find("input[name='istarget2']").val()
                var target_tel      = $("input[name='target_tel']").val()
                var target_ext      = $("input[name='target_ext']").val()
                var target_mobile   = $("input[name='target_mobile']").val()
                console.log(istarget, istarget2)
                
                if(evttype == "SurveyStart"){
                    // console.log('test:', 123)
                    if(household_total == null){
                        $.alert('尚未進行戶中抽樣')
                        return false;
                    }else if(target_name == ""){
                        $.alert('請問中選者姓名?')
                        return false;
                    }else if(istarget == null){
                        $.alert('請問應門者是否為中選者?')
                        return false;
                    }else if(istarget2 == 0 ){
                        if(!ValidateTel(target_tel) || !ValidateExt(target_ext)){
                            $.alert('例: 0226525100，無則輸入0')
                            return false;    
                        }else if(!ValidateMobile(target_mobile)){
                            $.alert('例: 0912345678，無則輸入0')
                            return false;
                        }else if(target_tel == 0 && target_mobile == 0){
                            $.alert('市話或手機請至少擇一輸入')
                            return false;
                        }
                    }
                    
                    SaveTargetinfo();
                    
                }else if(evttype == "SurveyTerminate" && household_total != null ){
                    // 有進行戶中抽樣
                    console.log("有進行戶中抽樣，終止訪問")
                    if(target_name == ""){
                        $.alert('請問中選者姓名?')
                        return false;
                    }else if(istarget == null){
                        $.alert('請問應門者是否為中選者?')
                        return false;
                    }else if(istarget2 == 0 ){
                        if(!ValidateTel(target_tel) || !ValidateExt(target_ext)){
                            $.alert('例: 0226525100，無則輸入0')
                            return false;    
                        }else if(!ValidateMobile(target_mobile)){
                            $.alert('例: 0912345678，無則輸入0')
                            return false;
                        }else if(target_tel == 0 && target_mobile == 0){
                            $.alert('市話或手機請至少擇一輸入')
                            return false;
                        }
                    }

                    SaveTargetinfo();
                    
                }else{
                    // 未進行戶中抽樣，不須存取抽樣結果
                    console.log('test:', 456)
                }
                
                
                
            // }else{
            //     console.log(123)
            //     var household_total = $("input[name='household_total']:checked").val()
            //     var target_name     = $("input[name='target_name']").val()
            //     var istarget        = $("input[name='istarget']:checked").val()
            //     var target_tel      = $("input[name='target_tel']").val()
            //     var target_ext      = $("input[name='target_ext']").val()
            //     var target_mobile   = $("input[name='target_mobile']").val()
            // }
            function SaveTargetinfo(){
                // console.log("SaveTargetinfo")
                sample_info.push(household_total)
                sample_info.push(target_name)
                sample_info.push(istarget)
                sample_info.push(istarget2)
                sample_info.push(target_tel)
                sample_info.push(target_ext)
                sample_info.push(target_mobile)
                sample_info.push(sample_clicktimes)
                // sample_info.push(localStorage['current_position'].split(',')[0])
                // sample_info.push(localStorage['current_position'].split(',')[1])
                localStorage['sample_info'] = JSON.stringify(sample_info);
                // console.log('test:', localStorage)
                // Both online and offline need to save in IDB
                // Params
                const myParams = JSON.parse(localStorage.getItem('current_case'));
                const myParam_prjid = myParams.prjid
                const myParam_Sid   = myParams.sid

                var db = new Dexie("CAPI");
                db.version(1).stores({
                    project: "prjid",                       // 專案清單
                    prjwork: "[prjid+Sid]",            // 樣本清單
                    questionnaire: "++tkey, [prjid+qid]",   // 問卷設定清單
                    sample_status: "[prjid+Sid]",                   // 訪問代碼
                    ans: '[prjid+Sid]',                             // 問卷結果
                    vill_list: "++tkey"                     // 村里清單
                });
                
                db.prjwork.where('[prjid+Sid]').equals([myParam_prjid, myParam_Sid]).modify(
                    {
                     "sample_household_total": household_total,
                     "target_name": target_name,
                     "istarget": istarget,
                     "access": istarget2,
                     "target_tel": target_tel,
                     "target_ext": target_ext,
                     "target_mobile": target_mobile,
                     "sample_clicktimes": Number(sample_clicktimes),
                     

                    }
                )

                db.sample_status.put(
                    {
                     "Sid": myParam_Sid,
                     "sample_household_total": household_total,
                     "target_name": target_name,
                     "istarget": istarget,
                     "access": istarget2,
                     "target_tel": target_tel,
                     "target_ext": target_ext,
                     "target_mobile": target_mobile,
                     "sample_clicktimes": Number(sample_clicktimes)
                    }
                )
                
            }
            }
            
        }

        function ValidateTel(tel){
            var re = /^[0-9]{9,10}|[0]{1}$/;
            return re.test(tel);
        }

        function ValidateExt(ext){
            // var re = /^[0-9]{5}|[0]{1}$/;;
            var re = /^\d+$/;
            return re.test(ext);
        }

        function ValidateMobile(mobile) {
          // var re = /^\d{10}$/;
          var re = /^[0]{1}[9]{1}[0-9]{8}|[0]{1}$/;
          return re.test(mobile);
        }

        function pairwise(list) {
          if (list.length < 2) { return []; }
          var first = _.first(list),
              rest  = list.slice(1),
              pairs = rest.map(function (x) { return [first, x]; });
          return pairs.concat(pairwise(rest));
        }
    </script>
    <style type="text/css">
        /* 阻止下拉重整 */
        body {
          overscroll-behavior: contain;
        }
       
        /* For nav */
        html {
            position: relative;
            min-height: 100%;
            
        }
        body {
            min-height: 100%;
            /*Avoid nav bar overlap web content*/
            padding-top: 70px; 
            /* Margin bottom by footer height ，avoid footer overlap web content*/
            margin-bottom: 60px;
            font-size: 18px;
        }
        /* Panel Content */
        .panel-primary{
            border-width: 2px;
        }
        #case_info{
            margin: 1em auto;
            /*width: 80%;*/
        }
        .panel-body{
            font-size: 1.2em;
            line-height: 2em;
        }
        /* Options */
        .checkbox_lbl{
            font-weight: 400;
            margin-right: 1em;
        }
        /* Buttons */
        div.col-sm-12 > #Goback,div.col-sm-12 > #SurveyTerminate,div.col-sm-12 > #SurveyStart{
            width: 10em; 
            margin-left: 1em;
            margin-right: 1em;
            /*cursor: pointer;*/
        } 
        .btn{
            font-size: 18px;
        }
        .btn_last,.btn_next,.btn_stop,.btn_finish{
            width: 10em;
            margin-left: 1em;
            margin-right: 1em;
        }
        /* Background-color */
        html,body,.panel-body{
            background-color: #fff7e6;
            overscroll-behavior-y: contain;
        }
        /* Sampling */
        hr{
            border: 1px solid #ccc;
            margin: 0.5em;
        }
        .form-control{
            width: 45%

        }
        input[type="text"]{
            font-size: 18px;
        }
        input[type="number"]{
            font-size: 18px;
        }
        /* Terminate */
        .bootstrap-select.btn-group {
            width: -webkit-fill-available;
        }
        .open{
            min-width: 14em;
        }
        .Terminate_note{
            width: 100%
        }
        .select{
            margin-bottom: 1em;
        }
        /* */
        .btn_find{
            width: 80%;
            text-align: left;
        }
        .col-sm-12{
            margin: 0.1em 0;
        }
        .btn_submit_specode,.btn_cancel_specode{
            margin: 0.5em;
        }
        /* Media Query For mobile  */
        @media screen and (max-width: 767px) {
            div.col-sm-12 > #Goback, div.col-sm-12 > #SurveyTerminate, div.col-sm-12 > #SurveyStart{
                width: 6em; 
            } 
            .btn_last,.btn_next,.btn_stop,.btn_finish{
                font-size: 0.8em;
                width: 5.5em;
                margin-left: 0.7em;
                margin-right: 0.7em;
                padding: 0.3em 0.3em;
            }
            .form-control{
                width: 100%
            }
        }
        .btn-default.active,.btn-default:hover,.btn-default.active:hover{
            background-color: #ffe680;
        }
        /* Click Event For IOS */
        /*button{ 
            cursor: pointer; 
        }*/
    </style>
</head>
<body>
    
    <div class="container">
        <div id="case_info" class="panel panel-primary" >
          <div class="panel-heading">樣本資訊</div>
          <div class="panel-body"></div>
        </div>
        <div></div>
    </div>
    <div class="modal fade" id="Md_SpecialCodes"  role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"  data-backdrop="static" data-keyboard="false">
      <div class="modal-dialog modal-md">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title">請點選以下特殊碼</h4>
          </div>
          <div class="modal-body" align="center">
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="Md_Editanswer"  role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"  data-backdrop="static" data-keyboard="false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title">請選擇要更改哪一題的答案</h4>
          </div>
          <div class="modal-body">
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="Md_Terminate"  role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"  data-backdrop="static" data-keyboard="false">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
            <h4 class="modal-title">請選擇適當的訪問結果代碼</h4>
          </div>
          <div class="modal-body">
            <select class="selectpicker select" name="Terminate_code" title="請選擇適當的訪問結果代碼" data-width="100%">
                <optgroup label="需再訪">
                    <option value=401>401無人在家_確定受訪者居住該處</option>
                    <option value=501>501無人在家_不確定受訪者居住該處</option>
                    <option value=402>402管理員阻止_確定受訪者居住該處 (請出示所有公文或留本計畫聯絡電話)</option>
                    <option value=502>502管理員阻止_不確定受訪者居住該處 (請出示所有公文或留本計畫聯絡電話)</option>
                    <option value=403>403外出，調查期間會回來</option>
                    <option value=302>302暫時不方便接受訪問 (受訪者在家且還沒開始訪問，可再探訪)</option>
                    <option value=509>509語言不通_非受訪者</option>
                    <option value=202>202受訪者以外的人代為拒訪(未開始詢問題目前，請設法接觸到受訪者本人)</option>
                    <option value=213>213因故中止訪問 (已訪問部份題目，可再探訪)</option>
                </optgroup>
                <optgroup label="不住原址" class="style0">
                    <option value="521">521「需再訪」：受訪者不住在原地址，「有」問到新電話與新地址</option> 
                                                <option value="522">522「需再訪」：受訪者不住在原地址，「只有」問到新電話或新地址</option> 
                                                <option value="523">523「不需再訪」：受訪者不住在原地址，「沒有」問到新電話及新地址，且上次調查所留電話無法連到受訪者</option> 
                </optgroup>
                <optgroup label="不需再訪">
                    <option class="style1" value="200">200該地址沒有常住人口</option>
                                                    <option value="201">201受訪者拒訪</option>
                                                    <option value="211">211受訪者中途拒訪</option>
                                                    <option value="212">212受訪者以外的人中途拒訪</option>
                                                    <option value="303">303語言不通_受訪者</option>
                                                    <option value="602">602無法清楚理解問題或意思表達，無法訪問</option>
                                                    <option value="503">503外出，調查期間不會回來，請說明可能回來時間</option>
                                                    <option value="504">504外出，不知去向</option>
                                                    <option value="603">603服兵役</option>
                                                    <option value="604">604服刑</option">
                                                    <option class="style0" value="601">601死亡_調查開始前</option>
                                                    <option value="301">301死亡_調查期間</option>
                                                    <option value="611">611空屋_調查開始前</option>
                                                    <option value="511">511空屋_調查期間</option>"
                                                    <option value="512">512查無此地址</option>
                                                    <option value="513">513環境惡劣無法抵達</option>
                                                    <option class="style0" value="514">514查無此人</option>
                                                    <option value="612">612政府機構、軍事單位、醫療院所、學校等單位</option>
                                                    <!-- <option class="style0" value="607">607出生年次不符合調查條件</option> -->
                                                    <option class="style0" value="605">605受訪者搬離原地址</option>
                                                    <option value="311">311受訪者要求刪除資料</option>
                                                    <option value="209">209受訪者或代理人於訪員調查前，即告知拒訪</option>
                                                    <option class="style1" value="998" >998應門者拒絕戶抽</option>
                </optgroup>
                <optgroup label="其他">
                    <option value=999>999其他無法歸類之項目（請聯絡計畫助理確認是否應使用此代碼），請說明</option>
                </optgroup>
            </select>
            <div id="div_Terminate_code_reject" style="display: none;">
                <select class="selectpicker select" name="Terminate_code_reject" data-width="100%" >
                    <option value="">請選擇拒訪原因</option>
                    <optgroup label="拒訪原因">
                        <option value="01">01訪函上寫「您都有權利退出計畫」</option> 
                        <option value="02">02沒空/太忙（含時間無法配合）</option>
                        <option value="03">03訪問主題沒興趣</option> 
                        <option value="04">04禮券（商品卡）金額太少</option> 
                        <option value="05">05擔心個資外洩</option> 
                        <option value="06">06不願意接受任何訪問</option> 
                        <option value="07">07家人反對</option> 
                        <option value="08">08本人或家人身體不適</option> 
                        <option value="09">09無任何理由拒絕</option> 
                        <option value="10">10其他，請說明</option> 
                    </optgroup>
                </select>
            </div>
            <div><input type="text" id="Terminate_note" name="Terminate_note" class="form-control Terminate_note" placeholder="備註說明請於此輸入"></div>
          </div>
          <div class="modal-footer">
            <button id="Terminate_cancel" class="btn btn-info">取消</button>
            <button id="Terminate_submit" class="btn btn-danger">確定終止訪問</button>
          </div>
        </div>
      </div>
    </div>
    <div>
        <input id='lat' style="display: none;"></input>
        <input id='lng' style="display: none;"></input>
    </div>
    <script>
        if('serviceWorker' in navigator) {
            navigator.serviceWorker
                   .register('https://capi.geohealth.tw/sw.js')
                   .then(function() { 
                        // console.log("Service Worker Registered"); 
                    }).catch((err) => {
                        // alert('no service worker');
                        console.log('ServiceWorker failed: ', err);
                    });
        }
    </script>
</body>
</html>
