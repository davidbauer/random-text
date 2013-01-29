$(function() {

	var gap1 = [];
		
	//load spreadsheet data via tabletop
	
	window.onload = function() { init() };

  var public_spreadsheet_url = 'https://docs.google.com/spreadsheet/pub?hl=en_US&hl=en_US&key=0AhM9lBUdMo93dEpzanh0anc5VlU5REVFZlZBMnJHYnc&output=html';

  function init() {
    Tabletop.init( { key: public_spreadsheet_url,
                     callback: showInfo,
                     simpleSheet: true } )
  }

  function showInfo(data, tabletop) {
    console.log(data); // bugfixing
    
    for (i=0; i < data.length;i++) {
    	if (data[i].gap == 1) {
		gap1.push(data[i].option);
		};
	};
    console.log(gap1); // bugfixing
 }
	
	
	var hash = window.location.hash,
	    field = document.banggomat.namme;

	if (hash) {
		hash = hash.substring(1);

		// Fill field
		field.value = hash;

		// Do the magic
		banggIt(hash);
	}
	
	
	$('#searchform').submit(function(e) {
		// Stop the form from sending and reloading the page
		e.preventDefault();
		
		// Do the magic!
		var myUser = document.banggomat.user.value;
		banggIt(myUser);
		// Update URL
		window.location.hash = myUser;
	});

function banggIt(myUser, gap1) {
	$('#bangg').html("");
	var fbShare = "Uff Feyssbugg daile: <iframe src='//www.facebook.com/plugins/like.php?href=http%3A%2F%2Flabs.davidbauer.ch&amp;send=false&amp;layout=button_count&amp;width=80&amp;show_faces=false&amp;font=arial&amp;colorscheme=light&amp;action=like&amp;height=21&amp;appId=320131728095347' scrolling='no' frameborder='0' style='border:none; overflow:hidden; width:80px; height:21px;' allowTransparency='true'></iframe> ";
	var twShare = "Uff Dwiddr daile: <a href='https://twitter.com/share' class='twitter-share-button' data-via='davidbauer'>Tweet</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='//platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','twitter-wjs');</script>";
	var text = "<p>Dr <span style='color:red;font-weight:bold;'>" + myUser + "</span> isch e <span style='color:red;font-weight:bold;'>" + gap1[Math.floor(Math.random()*(gap1.length))] + "</span> siech,<br/>als wenn's kai andere gäbt wo's besser miech.</p>" + fbShare + "<br/>" + twShare;
	
	$('#zeedl').fadeOut(function() {
  $('#zeedl').fadeIn();
  });
	$('#bangg').fadeOut(function() {
  $(this).html(text).fadeIn();
  });
	
	
};

}); //finish