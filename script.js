$(function() {
	var hash = window.location.hash,
	    field = document.banggomat.namme,
        api_url = "http://3tageswoche.ch/apps/banggomat/bangg";

	if (hash) {
		hash = hash.substring(1);
        $.get(api_url + "/" + hash, function(bangg){
            showBangg(bangg);
        });
	}

	$('#searchform').submit(function(e) {
		// Stop the form from sending and reloading the page
		e.preventDefault();
		// Do the magic!
		var myUser = document.banggomat.user.value;
		banggIt(myUser);
	});
	
	var colors = ["yellow", "green", "blue", "pink", "red", "aqua", "fuchsia"];

    $.get(api_url, function(banggs){
        // get list of Schnitzelbänggs
        var newest = $("#newest_banggs");
        for (var i=0; i<banggs.length; i++) {
            var otherbangg = $('<a class="otherbangg"/>');
            var bangg = banggs[i];
            newest.append(otherbangg);
            otherbangg.text(bangg.person);
            otherbangg.attr("href", "#"+bangg.id);
            otherbangg.attr("data-bangg-person", bangg.person); 
            otherbangg.attr("data-bangg-text", bangg.text);
            otherbangg.addClass(colors[Math.floor(Math.random()*colors.length)]);
            if (i > 11) {otherbangg.addClass('mobile-hide')};
        }

    });

    $('#newest_banggs a').live('click', function() {
       var banggId = $(this).attr('data-bangg-id');
       var banggPerson = $(this).attr('data-bangg-person');
       var banggText = $(this).attr('data-bangg-text');
       showBangg({id: banggId, person:banggPerson, text:banggText});
    });

    function showBangg(bangg) {
    	
        window.location.hash = bangg.id;
        document.banggomat.user.value = bangg.person;
        var fbShare = "Uff Feyssbugg daile: <iframe src='//www.facebook.com/plugins/like.php?href=" + window.location + "&amp;send=false&amp;layout=button_count&amp;width=80&amp;show_faces=false&amp;font=arial&amp;colorscheme=light&amp;action=like&amp;height=25&amp;appId=204329636307540' scrolling='no' frameborder='0' style='border:none; overflow:hidden; width:100px; height:25px;' allowTransparency='true'></iframe> ";
        var twShare = "Uff Dwiddr daile: <a href='https://twitter.com/share' class='twitter-share-button' data-text='Habe mit dem Banggomat der @tageswoche zur #fasnachtBS einen Schnitzelbangg kreiert: '>Tweet</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='//platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','twitter-wjs');</script>";
        var mailShare = "Schigg en per <a href='mailto:?Subject=Mii Bangg&BODY=Ich ha bi dr DaagesWuche e Bangg geschriibe. Lueg en aa und mach au eine: " + window.location + "'>Mail</a>";
        var share = fbShare + "<br/>" + twShare + "<br/>" + mailShare;
         
        
        $('#zeedl').fadeOut(600, function() {
            $('#zeedl').removeClass().addClass(colors[Math.floor(Math.random()*colors.length)]).fadeIn();
            $('#bangg').html("");
            $('#share').fadeOut(1);
            $("#bangg").html(bangg.text).fadeIn();
            $('#share').html(share).fadeIn();
        });
        	
        // $('#bangg').html(share). fadeOut(function() {
            
        // });
    }

    function banggIt(myUser) {
        $.post(api_url,
                JSON.stringify({"person": myUser}), function(bangg) {
            showBangg(bangg);
            
        });
    };

}); //finish
