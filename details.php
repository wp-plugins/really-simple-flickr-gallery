<html>
<body>
<?php
if(!$_POST) {
	?>
	<table>
	<tr>
	<td colspan=3>
	<h1>Enter Your Flickr Details:</h1>
	<?php $url=$_SERVER['REQUEST_URI']; ?>
	<form method="POST" action="<?php echo $url; ?>">
	<tr><td width="20%">Your Flickr User ID</td>   
	<td width="30%"><input type="text" name="userid" size="40" value="<?php echo get_option("RSFG_userid");?>"/></td>
	<td>If you do not know your Flickr USER ID, <a href="http://idgettr.com/">Click Here</a> to get it. <br><b><i>Do Not enter any USER ID other than yours, that will be a violation of terms of service of Flickr API.</i></b></td></tr>
	<tr><td>Flickr API Key</td>   
	<td><input type="text" name="apikey" size="50" value="<?php echo get_option("RSFG_apikey");?>" /></td>
	<td rowspan=2>Get a Flickr API Key by <a href="http://www.flickr.com/services/apps/create/apply/">applying here</a>. It is really simple. Copy the Flickr API Key and the API Secret and paste them here </td></tr>
	<tr><td>Flickr API Secret</td>   
	<td><input type="text" name="apisecret"  value="<?php echo get_option("RSFG_apisecret");?>"/></td></tr>
	
	<td colspan=3>
	<h1>Page Settings</h1>
	<tr><td>Page Width</td>   
	<td><input type="text" name="pagewidth" size="50" value="<?php echo get_option("RSFG_pagewidth");?>"/></td>
	<td>This is the pixel-width of the photo that will be displayed on your photo page. You can come back and change this if the photo shoots out of your central column, depending on your current Wordpress theme.</td></tr> 
	<td colspan=3>
	<h1>Facebook Settings</h1>
	<tr><td>Want to have a Facebook LIKE Button?</td>   
	<td>
<input type="radio" name="fblike" value="yes" checked/> Yes<br />
<input type="radio" name="fblike" value="no" /> No</td>
<td>Want to have Facebook "Like" Button</td></tr>
	<tr><td>Want to have Facebook Comment Box?</td>   
	<td>
<input type="radio" name="fbcomm" value="yes" checked/> Yes<br />
<input type="radio" name="fbcomm" value="no" /> No</td>
<td>Want to have a Facebook Comment Box?</td></tr>
	<tr><td>Have a Facebook Page to Like?</td>   
	<td><input type="text" name="fbpage" size="50" value="<?php echo get_option("RSFG_fbpage");?>"/></td>
	<td>Enter the URL of your Facebook Page for which you want to place a Like box. Note that this is a Facebook Page and <b>NOT</b> a profile page.</td></tr>  
	  
	<td colspan=3>
	<h1>Pages</h1>
	<tr><td>Slideshow Page URL <br><b> Please end with "/"</b></td>  
	<td><input type="text" name="slidepageurl" size="60" value="<?php echo get_option("RSFG_slidepageurl");?>"/></td>
	<td>Enter the URL of the page on which you are displaying your Slideshow. Create a new page and just enter the text "<b>[RSFG_slideshow]</b>" into it. Save it and enter the URL here. Leave this field blank if you do not wish to have a "Slideshow Page"] </td></tr> 
	<tr><td>All Photo Pages URL <br><b> Please end with "/"</b> </td>   
	<td><input type="text" name="allphotopageurl"  size="60" value="<?php echo get_option("RSFG_allphotopageurl");?>"/></td>
	<td>Enter the URL of the page on which you are displaying All Photos. Create a new page and just enter the text "<b>[RSFG_allphotos]</b>" into it. Save it and enter the URL here. Leave this field blank if you do not wish to have an "All Photos Page"] </td>
	<tr><td>Ads Code </td>   
	<td><textarea name="adcode"  value="<?php echo get_option("RSFG_adcode");?>"/></textarea></td>
	<td>Enter the code if you want to put an ad on the site </td></tr> 
	
	</table>
	<input type="submit" class="button" value="Enter">
	<input type="reset" class="button" value="Reset">

	</form>
	<?php
}
else {
$userid=$_POST['userid'];
$apikey=$_POST['apikey'];
$apisecret=$_POST['apisecret'];
$pagewidth=$_POST['pagewidth'];
$fblike=$_POST['fblike'];
$fbcomm=$_POST['fbcomm'];
$fbpage=$_POST['fbpage'];
$slidepageurl=$_POST['slidepageurl'];
$allphotopageurl=$_POST['allphotopageurl'];
$adcode=$_POST['adcode'];

if(get_option("RSFG_userid")) update_option("RSFG_userid", $userid);
else add_option("RSFG_userid", $userid,'','yes');
if(get_option("RSFG_apikey")) update_option("RSFG_apikey", $apikey);
else add_option("RSFG_apikey", $apikey,'','yes');
if(get_option("RSFG_apisecret")) update_option("RSFG_apisecret", $apisecret);
else add_option("RSFG_apisecret", $apisecret,'','yes');
if(get_option("RSFG_pagewidth")) update_option("RSFG_pagewidth", $pagewidth);
else add_option("RSFG_pagewidth", $pagewidth,'','yes');
if(get_option("RSFG_fblike")!="yes" || get_option("RSFG_fblike")!="no" ) update_option("RSFG_fblike", $fblike);
if(get_option("RSFG_fbcomm")!="yes" || get_option("RSFG_fbcomm")!="no" ) update_option("RSFG_fbcomm", $fbcomm);
if(get_option("RSFG_fbpage")) update_option("RSFG_fbpage", $fbpage);
else add_option("RSFG_fbpage", $fbpage,'','yes');

if(get_option("RSFG_allphotopageurl") || get_option("RSFG_allphotopageurl")=="") update_option("RSFG_allphotopageurl", $allphotopageurl);
else add_option("RSFG_allphotopageurl", $allphotopageurl,'','yes');
if(get_option("RSFG_slidepageurl") || get_option("RSFG_slidepageurl")=="") update_option("RSFG_slidepageurl", $slidepageurl);
else add_option("RSFG_slidepageurl", $slidepageurl,'','yes');
if(get_option("RSFG_adcode") || get_option("RSFG_adcode")=="") update_option("RSFG_adcode", $adcode);
else add_option("RSFG_adcode", $adcode,'','yes');


echo "<h2>Thanks, your details have been entered.</h2>";
}
?>
</body>
</html>