<?php
include_once 'config.php';


include_once(CLASS_DIR . "ClassLongCode.php");
include_once(CLASS_DIR . "panelTime.php");

$panelTime = new panelTime();
if (!$lcodeobj->check_reseller() && !$lcodeobj->check_user() && !$lcodeobj->check_admin()) {
    $lcodeobj->expire();
}

/* User inbox balance*/
$lcBalance = $lcodeobj->getLongCodeInboxBalance($_SESSION['id']);

$lcDataArr = array();
$lcInboxTotal = $isDedicated = $lcInboxUnread = 0;

$start = 0;
$limit = 50;
/*All longcode*/
$longCodeCursor = $lcodeobj->get_user_long_code($_SESSION['id']);
$longCode = '';

/* Get list of keywords*/
$keywordCursor = $lcodeobj->getLongCodeKeyWordsRow();
while($row = mysql_fetch_array($keywordCursor))
{
    $keywords[] = $row['keyword'];
}

/*Longcode number of the user*/
if (is_array($longCodeCursor)) {
    foreach ($longCodeCursor as $tonum) {
        $longCode .= "91" . $tonum . ", ";
    }
    $longCode = substr($longCode, 0, -2);
} else {
    $longCode = $longCodeCursor;
}

 

/*LongCode provided by us*/
if (strstr($longCode, "9229224424")) {
    /*Longcode inbox details*/
    $lcDataCursor = $lcodeobj->getLongCodeInboxRow($_SESSION['id'], $start, $limit);
    $lcInboxTotal = $lcodeobj->get_Total_LongCodeInboxRow($_SESSION['id']);
    
    /* Totol unread message for this user*/
    $lcInboxUnread = $lcodeobj->get_unread_messages($_SESSION['id']);
    
    
} else {
    /* Dedicated longcode number of user*/
    $isDedicated = 1;
    $lcDataCursor = $lcodeobj->getLongCodeInboxRowDedicated($_SESSION['id'], $longCode, $start, $limit);
    $lcInboxTotal = $lcodeobj->get_Total_LongCodeInboxRowDedicated($_SESSION['id'], $longCode);
}

/* Fetch data from the sql cursor*/
$totalNumbers = mysql_num_rows($lcDataCursor);
while ($inboxData = mysql_fetch_array($lcDataCursor,MYSQL_ASSOC)) {
    $lcDataArr[] = $inboxData;
}
/*Number of pages*/
$pages = ceil($lcInboxTotal / $limit);

?>
<style>
    .cmntbl th.frst, .cmntbl td.frst{
        padding: 8px 20px;
    }
    #lngBal{
        float: right;
        line-height: 44px;
        font-size: 1.2em;
        margin-right: 10px;
        color: #707070;
    }
    .small{
        color: #ccc;
        font-size: 12px;
    }
    #lcdata_region_wise{
        height: 500px;
    }
</style>
<div class="shell">
    <div id="rightTopSection" class="cmnTopSection">
        <div class="brow">
            <div class="col-sm-6">
                <ul class="nav-tabs" role="tablist">
                    <li class="active"><a href="#lci" onClick="_load('lci')">Inbox</a></li>
                    <li><a href="#lci_keyword" onClick="_load('lci_keyword')">Keyword</a></li>
                </ul>
            </div>
            <div class="col-sm-6">

<?php //include_once("inc-header.php");  ?>
                <span id="lngBal">Balance:<span id="LCBalance"><?php echo $lcBalance; ?></span></span>
            </div>
        </div>
    </div>

    <div id="" class="content">
        <div class="brow">
            <div class="col-sm-7">
                <div class="clearfix pd2">
                    <div class="fl mrR1">
                        <select class="ht40" name="" id="keyword_filter" >
                            <option  value="all">All keywords</option>
                                <?php foreach ($keywords as $keyword) { ?>
                                <option  value="<?php echo $keyword; ?>"> <?php echo $keyword; ?> </option>
                                <?php } ?>
                        </select>
                    </div>
                    <div class="fl">
                        <input id="st_date" type="text" class="fw100 mrR1" placeholder="From" />
                        <input id="end_date" type="text" class="fw100 mrR1" placeholder="To" />
                        <input type="button" class="btn btn-large btn-normal" value="Show" onclick="filterInboxData()"/>
                    </div>
                </div> <!-- end of heading -->
                <div class="pdL2">
                    <table class="cmntbl" cellpadding="0" width="100%" cellspacing="0">
                        <thead>
                            <tr>                                            
                                <th width="30%">Keywords</th>
                                <th width="20%">Sender</th>
                                <th width="30%">Messages</th>
                                <th width="20%">Time</th>
                            </tr>
                        </thead>
                        <tbody id="longcodeDataTbl">
                            <?php
                            if ($totalNumbers > 0) {
                                foreach ($lcDataArr as $lcData) {
                                    ?>
                                    <tr>
                                        <td><?php echo $lcData['keyword'];?> <span class="small"><?php echo $lcData['tonum']; ?></span></td>
                                        <td><?php echo $lcData['from'];?></td>
                                        <td><?php echo $lcData['message'];?></td>
                                        <td><p class="tip dateClass" patt="1" title=""><?php echo $lcData['receive_time'];?></p> </td>
                                    </tr>
                                    <?php }
                                } else { ?>
                                    <tr><td colspan="4">No record found</td></tr>
                            <?php } ?>

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-sm-5">
                <div class="clearfix">
                    <div class="pd2 clearfix">
                        <div class="fl lh40" id="receivedSMS">
                            <?php echo $lcInboxTotal; ?> SMS Recieved
                        </div>
                        <div class="fl lh40" id="unreadSMS">
                            <?php echo $lcInboxUnread; ?> SMS Unread
                        </div>
                        <div class="fr">
                            <button class='btn btn-medium btn-success'>Export them</button>
                        </div>
                    </div>
                    <section class="pdL1 pdR1">
                        <div id="longcode_msg_detail">
                            <!-- Pie chart loaded in this div -->
                        </div>

                        <div class="mrT2" id="lcdata_region_wise">
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div> <!-- end of content -->


</div> <!-- end of shell -->

<script type="text/javascript">
    
    function filterInboxData()
    {
        var keyword = $('#keyword_filter').val();
        var startDate = $('#st_date').val();
        var endDate = $('#end_date').val();
        var isDedicated = '<?php echo $isDedicated;?>';
        var data = "&keyword="+keyword;
        if(isDedicated == 1)
        {
            var longCode = '<?php echo $longCode;?>';
            data += '&longCode='+longCode;
        }
        if(endDate != "")
        {
            data += '&eDate='+endDate;   
        }
        if(startDate != "")
        {
            data += "&sDate="+startDate;
        }
        
        $.ajax({
                type: "POST",
                url: "LongCode_layer.php?action=longCodeFilter",
                data: data,
                cache: false,
                dataType:'json',
                success: function(data)
                {
                    var html = "";
                    if(data.total > 0){
                        $.each(data.cursor, function(key, longcodeData) {
                            html += '<tr>\
                                        <td>'+longcodeData.keyword+'<span class="small">'+longcodeData.tonum+'</span></td>\
                                        <td>'+longcodeData.from+'</td>\
                                        <td>'+longcodeData.message+'</td>\
                                        <td><p class="tip dateClass" patt="1" title="">'+longcodeData.receive_time+'</p> </td>\
                                    </tr>';
                        });
                    }
                    else
                    {
                        html += '<tr><td colspan="4">No record found</td></tr>';
                    }
                    $('#longcodeDataTbl').html(html);
                    dateDesign();
                    $('#receivedSMS').html(data.total + " SMS Recieved");
                    $('#unreadSMS').html(data.unread + " SMS Unread");
                    }
            });
        
    }
    
    
    $(document).ready(function() {
        $(".tip").tipTip({maxWidth: "200px", edgeOffset: 10});
        $('#st_date').datepicker({dateFormat: 'yy-mm-dd'});
        $('#end_date').datepicker({dateFormat: 'yy-mm-dd'});

        $('#keyword_filter').selectric();
        dateDesign();



        //pie chart inint
        $("#longcode_msg_detail").highcharts({
            legend: {
                align: 'right',
                verticalAlign: 'top',
                layout: 'vertical',
                //x: 0,
                y: 100,
                borderWidth: 0,
                itemMarginBottom: 15,
            },
            credits: {
                enabled: false
            },
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false
            },
            title: {
                style: {
                    color: '#fff',
                    display: 'none'
                }
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: true
                }
            },
            series: [{
                    type: 'pie',
                    name: 'Browser share',
                    data: [
                        ['Test', 45.0],
                        ['Untest', 26.8],
                        {
                            name: 'others',
                            y: 12.8,
                            sliced: true,
                            selected: true
                        }
                    ]
                }]
        });
    });
</script>
<link rel="stylesheet" href="/css/jquery-jvectormap-1.2.2.css" type="text/css" media="screen"/>
<script src="/js/jquery-jvectormap-1.2.2.min.js"></script>
<script src="/js/jquery-jvectormap-in-mill-en.js"></script>
<script>
    var smsData = {
        "IN-MP": 16.63,
        "IN-GJ": 11.58,
        "IN-MH": 158.97,
    };
    var smsDataDeliver = {
        "IN-MP": 10.63,
        "IN-GJ": 5.58,
        "IN-MH": 15.97,
    };
    var smsDataFailed = {
        "IN-MP": 1.63,
//  "IN-GJ": 141.58,
        "IN-MH": 58.97,
    };
    $(function() {
        $('#lcdata_region_wise').vectorMap({map: 'in_mill_en',
            series: {
                regions: [{
                        values: smsData,
                        scale: ['#C8EEFF', '#0071A4'],
                        normalizeFunction: 'polynomial'
                    }]
            },
            onRegionLabelShow: function(e, el, code) {
                el.html(el.html() + ' (SMS Sent - ' + smsData[code] + '<br> SMS Deliver ' + smsDataDeliver[code] + '<br> SMS Failde ' + smsDataFailed[code]);
            }
        });
    });
</script>