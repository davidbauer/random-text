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

    $.get(api_url, function(banggs){
        // get list of Schnitzelbänggs
        var newest = $("#newest_banggs");
        for (var i=0; i<banggs.length; i++) {
            var bangg = banggs[i];
            newest.append("<a href='#"+bangg.id+"' data-bangg-id='"+bangg.id+"' data-bangg-person='"+ bangg.person +"' data-bangg-text='" + bangg.text + "'>" + bangg.person + "</a><br />");
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
        var fbShare = "Uff Feyssbugg daile: <iframe src='//www.facebook.com/plugins/like.php?href=http%3A%2F%2Flabs.davidbauer.ch&amp;send=false&amp;layout=button_count&amp;width=80&amp;show_faces=false&amp;font=arial&amp;colorscheme=light&amp;action=like&amp;height=21&amp;appId=320131728095347' scrolling='no' frameborder='0' style='border:none; overflow:hidden; width:80px; height:21px;' allowTransparency='true'></iframe> ";
        var twShare = "Uff Dwiddr daile: <a href='https://twitter.com/share' class='twitter-share-button' data-via='davidbauer'>Tweet</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='//platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','twitter-wjs');</script>";
        var mailShare = "Schigg en per <a href='mailto:?Subject=Mii Bangg&BODY=Ich ha bi dr DaagesWuche e Bangg geschriibe. Lueg en aa und mach au eine: " + window.location.href + "'>Mail</a>";
        var share = fbShare + "<br/>" + twShare + "<br/>" + mailShare;

        $('#bangg').html("");

        $('#zeedl').fadeOut(function() {
            $('#zeedl').fadeIn();
        });
        $('#bangg').fadeOut(function() {
            $("#bangg").html(bangg.text + share).fadeIn();
        });
    }

    function banggIt(myUser) {
        $.post(api_url,
                JSON.stringify({"person": myUser}), function(bangg) {
            showBangg(bangg);
        });
    };

}); //finish
