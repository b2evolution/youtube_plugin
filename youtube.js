$(function() {
	$('#youtube_under_button').click(function() {
		$('#yttoolbar').slideToggle()
	})
	$('#youtube_close').click(function() {
		$('#yttoolbar').slideToggle()
	})
	$('#youtube_othersites').click(function() {
		othersites()
	})
})

function sndReq(page) {
	var tag = document.getElementById('yttag').value;
	var youtube_url_full = youtube_url + '&yttag=' + urlencode(tag) + '&ytpage=' + page;
	$('#ytloading').fadeIn()
	$('#ytresults').load(youtube_url_full, function() {
		$('#ytloading').hide()
		if ($(this).css('display') == 'none') {
			$(this).fadeIn()
		} else {
			$(this).fadeTo('fast', 1)
		}
	})
}

function nextpage()
{
	page = document.getElementById('ytpagestore').innerHTML;
	++page;
	$('#ytresults').fadeTo('fast', 0.1);
	sndReq(page);
}
function prevpage()
{
	page = document.getElementById('ytpagestore').innerHTML;
	--page;
	$('#ytresults').fadeTo('fast', 0.1);
	sndReq(page);
}

function othersites()
{
  var vidbuttons = "<p style='margin: 3px 6px;text-align:center'><span class='notes'>You may insert clips from other video sites.  Choose the video site, then paste the clip's id between the tags that are inserted into your post.</span><br /></p><div style='text-align:center'>";
  vidsites = new Array();
  vidsites[0] = 'blip|Blip.tv|';
  vidsites[1] = 'current|Current.tv|';
  vidsites[2] = 'dm|Daily Motion|';
  vidsites[3] = 'gvid|Google Video|';
  vidsites[4] = 'ifilm|iFilm|';
  vidsites[5] = 'jumpcut|Jumpcut|';
  vidsites[6] = 'revver|Revver|';
  vidsites[7] = 'vsocial|vSocial|';
  vidsites[8] = 'youtube|YouTube|';
  vidsites[9] = 'metacafe|metacafe|id/file_name.swf';
  vidsites[10] = 'break|Break.com|';
  vidsites[11] = 'livevideo|LiveVideo.com|';
  
  for (var i = 0; i < vidsites.length; i++)
  {
    var site = vidsites[i];
    var n = site.split("|");
    vidbuttons = vidbuttons + '<input type="button" value="' + n[1] + '" onclick="textarea_replace_selection(document.getElementById(\'itemform_post_content\'), \'[' + n[0] + ']' + n[2] + '[/' + n[0] + ']\', document)" />';
  }
  vidbuttons = vidbuttons + "</div>";
  document.getElementById('ytresults').innerHTML = vidbuttons;
	$('#ytresults').toggle();
}

function urlencode(str) {
	str = escape(str);
	str = str.replace('+', '%2B');
	str = str.replace('%20', '+');
	str = str.replace('*', '%2A');
	str = str.replace('/', '%2F');
	str = str.replace('@', '%40');
	return str;
}

