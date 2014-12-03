<?php
include_once 'config.php';
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
    #addKeyword_dialog{
        width: 300px;
    }
</style>
<div class="shell">
    <div id="rightTopSection" class="cmnTopSection">
        <div class="brow">
            <div class="col-sm-6">
                <ul class="nav-tabs" role="tablist">
                    <li><a href="#lci" onClick="_load('lci')">Inbox</a></li>
                    <li class="active"><a href="#lci_keyword" onClick="_load('lci_keyword')">Keyword</a></li>
                </ul>
            </div>
            <div class="col-sm-6">
                <?php //include_once("inc-header.php"); ?>
                <span id="lngBal">Balance:<span id="LCBalance">1036</span></span>
            </div>
        </div>
    </div>
    
    <div id="" class="content">

        <div class="clearfix mrT2 pdL2 pdR2 ">
            
            <button id="addKeyword" class='btn btn-medium btn-success'>Add</button>
            
        </div>

        <div class="mrT1">
            
            <table class="cmntbl" cellpadding="0" width="100%" cellspacing="0">
                <thead>
                    <tr>                                            
                        <th class="frst" width="15%">Keywords</th>
                        <th width="20%">Callback URL</th>
                        <th width="20%">Reply Message</th>
                        <th width="15%">Email ID</th>
                        <th width="15%">Last 7 days</th>
                        <th width="15%">Action</th>
                    </tr>
                </thead>
                <tbody id="">
                    <tr>
                        <td class="frst">Stop <span class="small">91922922424</span></td>
                        <td>Not set yet <a class="und callback" href="javascript:void(0)">set now</a></td>
                        <td>Not set yet <a class="und rep_msg" href="javascript:void(0)">set now</a></td>
                        <td>Not set yet <a class="und email" href="javascript:void(0)">set now</a></td>
                        <td>100</td>
                        <td><a class="und themeLink" href="javascript:void(0)">delete</a></td>
                    </tr>
                    <?php
                     for ($i=0; $i < 5; $i++) { 
                        echo '
                        <tr>
                            <td class="frst">Stop <span class="small">91922922424</span></td>
                            <td><a class="nrmLink" title="Click to edit" href="javascript:void(0)">http://local.vtermination.com/test.php</a></td>
                            <td><a class="nrmLink" title="Click to edit" href="javascript:void(0)">Thank you for contacting us will get back to you soon</a></td>
                            <td><a class="nrmLink" title="Click to edit" href="javascript:void(0)">jessey@example.com</a></td>
                            <td>300</td>
                            <td><a class="und themeLink" href="javascript:void(0)">delete</a></td>
                        </tr>
                        ';
                     }
                    ?>
                    
                </tbody>
            </table>
                
        </div>
    </div> <!-- end of content -->

        
</div> <!-- end of shell -->

<!-- add new keyword content start -->
<div id="addKeyword_dialog" class="white-popup topbar closeOut zoom-anim-dialog mfp-hide">
    <button class="mfp-close" type="button" title="Close (Esc)">×</button>
    <h3>Add New keyword</h3>
    <div class="pd2">
        <div class="row-hrt">
            <select id="longCode" name="longCode" onchange="showlongcode()">
                <option value="">Select Number</option>
                <option value="9229224424">9229224424</option>
                <option value="447781470658">447781470658</option>
            </select>
        </div>
        <div class="row-hrt">
            <input type="text" name="fname" id="fname" value="">
        </div>
        <div class="row-hrt">
            <input type="button" class='btn btn-large btn-success ls100 alC' value='Request keyword' />
        </div>
    </div>
</div>
<!-- end of add new keyword -->

<!-- callback url dialog content start -->
<div id="callback_dialog" class="white-popup topbar closeOut zoom-anim-dialog mfp-hide">
    <button class="mfp-close" type="button" title="Close (Esc)">×</button>
    <h3>Callback URL</h3>
    <div class="pd2">
        <div class="row-hrt">
            <input placeholder="yourdomain" type="text" name="url" id="url">
        </div>
        <div class="row-hrt">
            <p>
                For example we hit url like below<br>
                yourdomain?number=999999999&amp;message=urlencoded%20sms&amp;keyword=
            </p>
        </div>
        <div class="row-hrt">
            <input type="button" class='btn btn-large btn-success alC' value='Add' />
        </div>
    </div>
</div>
<!-- end of callback url dialog -->

<!-- email  dialog content start -->
<div id="email_dialog" class="white-popup topbar closeOut zoom-anim-dialog mfp-hide">
    <button class="mfp-close" type="button" title="Close (Esc)">×</button>
    <h3>Email Alert</h3>
    <div class="pd2">
        <div class="row-hrt">
            <input placeholder="jessey@example.com" type="text" name="email_id" id="email_id">
        </div>
        <div class="row-hrt">
            <p>
                Lorum ipsum dolor sit amet consquer ipsum dolor sit amet consquer
            </p>
        </div>
        <div class="row-hrt">
            <input type="button" class='btn btn-large btn-success alC' value='Add' />
        </div>
    </div>
</div>
<!-- end of email  dialog -->

<!-- email  dialog content start -->
<div id="rep_msg_dialog" class="white-popup topbar closeOut zoom-anim-dialog mfp-hide">
    <button class="mfp-close" type="button" title="Close (Esc)">×</button>
    <h3>Reply Message</h3>
    <div class="pd2">
        <div class="row-hrt">
            <input placeholder="Sender ID" type="text" value="" maxlength="6" name="sender_id" id="sender_id">
        </div>
        <div class="row-hrt">
            <textarea placeholder="Enter Reply Message " name="message" id="message"></textarea>
        </div>
        <div class="row-hrt">
            <p>Note: Sender ID should be six character only, and auto reply will send sms from route 4 to ensure better sms delivery</p>
        </div>
        <div class="row-hrt">
            <input type="button" class='btn btn-large btn-success alC' value='Add' />
        </div>
    </div>
</div>
<!-- end of email  dialog -->


<script type="text/javascript">
$(document).ready(function(){
    $(".tip").tipTip({maxWidth: "200px", edgeOffset: 10});
    $('#st_date').datepicker({dateFormat: 'yy-mm-dd'});
    $('#end_date').datepicker({dateFormat: 'yy-mm-dd'});

    $('#keyword_filter').selectric();
    dateDesign();

    //init popup for add new keyword
    $('#addKeyword').magnificPopup({
        items: {
            src: '#addKeyword_dialog',
            type: 'inline',
        },
        closeBtnInside: true,
        //overflowY: 'auto',
        modal: true,
        midClick: true,
        removalDelay: 300,
        mainClass: 'my-mfp-zoom-in'
    });
    //init callback_dialog popup 
    $('.callback').magnificPopup({
        items: {
            src: '#callback_dialog',
            type: 'inline',
        },
        closeBtnInside: true,
        //overflowY: 'auto',
        modal: true,
        midClick: true,
        removalDelay: 300,
        mainClass: 'my-mfp-zoom-in'
    });

    // rep_msg dialog popup
    $('.rep_msg').magnificPopup({
        items: {
            src: '#rep_msg_dialog',
            type: 'inline',
        },
        closeBtnInside: true,
        //overflowY: 'auto',
        modal: true,
        midClick: true,
        removalDelay: 300,
        mainClass: 'my-mfp-zoom-in'
    });


    //init email_dialog popup 
    $('.email').magnificPopup({
        items: {
            src: '#email_dialog',
            type: 'inline',
        },
        closeBtnInside: true,
        //overflowY: 'auto',
        modal: true,
        midClick: true,
        removalDelay: 300,
        mainClass: 'my-mfp-zoom-in'
    });


    //closing popup on click event
    $(document).on('click', '.mfp-close', function (e) {
        e.preventDefault();
        $.magnificPopup.close();
    });

});    
</script>