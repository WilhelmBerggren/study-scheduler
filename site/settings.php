<!DOCTYPE html>
<html>
<title>Calendar</title>
<link href="menubar.css" rel="stylesheet">
<link href="settings.css" rel="stylesheet">
<script src="../site/jquery.min.js"></script>
<body>
<?php include_once "../site/menubar.php";?>
<h1>Settings</h1>
<div id="settingBar">
  <div class="pSettings" id="pSettings">Personal settings</div>
    <div id="psettings">
      <div class="menuS" id="whatSettings">Change privacy settings</div>
      <div class="menuS">Korv2</div>
      <div class="menuS">Korv3</div>
    </div>
  <div class="pSettings" id="secSettings">Schedule</div>
    <div id="secsettings">
      <div class="menuS" id="runAlgo">load algorithm</div>
      <div class="menuS">Hej2</div>
      <div class="menuS">Hej3</div>
    </div>
  <div class="pSettings" id="dataPolicy">Data policy</div>
    <div id="datapolicy">
      <div class="menuS" id="whatGather">What type of information do we gather?</div>
      <div class="menuS" id="whatUse">How do we use this information?</div>
      <div class="menuS" id="whatShare">How is this information shared?</div>
    </div>
</div>
<div id="displaySettings">
<form id='submitKTHlink' action='../scripts/createCal.php' method='POST'>
	KTHlink:<input type='text' name='KTHlink'/>
	<input type='hidden' name='uuid' value='<?php if(isset($_SESSION['uuid'])) echo $_SESSION['uuid'];?>'/>
	<input type='submit'/>
</form>
</div>
<script>
var menuVis1 = false;
var menuVis2 = false;
var menuPersData = false;
$(document).ready(function(){
    //Show hide first menu
    $("#pSettings").click(function(){
      if (menuVis1) {
        $('#psettings').css({'display':'none'});
        menuVis1 = false;
        return;
      }
      $('#psettings').css({'display':'block'});
      menuVis1 = true;
    });
    //Show hide second menu
    $("#secSettings").click(function(){
      if (menuVis2) {
        $('#secsettings').css({'display':'none'});
        menuVis2 = false;
        return;
      }
      $('#secsettings').css({'display':'block'});
      menuVis2 = true;
    });
    //Show hide information handeling settings
    $("#dataPolicy").click(function(){
      if (menuPersData) {
        $('#datapolicy').css({'display':'none'});
        menuPersData = false;
        return;
      }
      $('#datapolicy').css({'display':'block'});
      menuPersData = true;
    });
    $("#runAlgo").click(function(){
        $("#displaySettings").load('runAlgorithm.php');
    });
    $("#whatGather").click(function(){
        $("#displaySettings").load('infoSiteGather.php');
    });
    $("#whatUse").click(function(){
        $("#displaySettings").load('infoSiteUse.php');
    });
    $("#whatShare").click(function(){
        $("#displaySettings").load('infoSiteShare.php');
    });
    $("#whatSettings").click(function(){
        $("#displaySettings").load('dataSettingsForm.php');
    });
});

$(document).on('submit','#submitKTHlink', function(event) {
	event.preventDefault();
	console.log($(this).serialize());
	$.ajax ({
		type: $(this).attr('method'),
		url: $(this).attr('action'),
		data: $(this).serialize(),
		success: function(data){
			console.log(data);
			document.getElementById('submitKTHlink').innerHTML += data;
		}
	})
});
</script>
</body>
