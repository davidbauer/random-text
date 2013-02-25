$(function() {
	var hash = window.location.hash,
	    field = document.banggomat.namme,
        api_url = "/bangg";

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
        // get list of texts
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
        var twShare = "Share on Twitter <a href='https://twitter.com/share' class='twitter-share-button'>Tweet</a><script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src='//platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document,'script','twitter-wjs');</script>";
        var mailShare = "Share via <a href='mailto:?Subject=<<YOURSUBJECT>>&BODY=<<YOURTEXT>>" + window.location + "'>e-mail</a>";
        var share = twShare + "<br/>" + mailShare;
         
        
        $('#zeedl').fadeOut(600, function() {
            $('#zeedl').removeClass().addClass(colors[Math.floor(Math.random()*colors.length)]).fadeIn();
            $('#bangg').html("");
            $('#share').fadeOut(1);
            $("#bangg").html(bangg.text).fadeIn();
            $('#share').html(share).fadeIn();
        });
    }

    function banggIt(myUser) {
        $.post(api_url,
                JSON.stringify({"person": myUser}), function(bangg) {
            showBangg(bangg);
            
        });
    };

}); //finish