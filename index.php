<?php
/*
Plugin Name: Really Simple Flickr Gallery
Plugin URI: http://www.sumitkumar.info/plugins/RSFG/
Description: Display all your Flickr photos and photosets on your own site
Version: 1.0.6.2
Author: Sumit Kumar
Author URI: http://www.sumitkumar.info
*/

/*  Copyright 2011  SUMIT KUMAR  (email : contact@sumitkumar.info)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function RSFG_admin() {  
    include "details.php";
}  

function RSFG_add_menu() {
	add_options_page("Really Simple Flickr Gallery", "Really Simple Flickr Gallery", 1, "RSFG", "RSFG_admin");
}

function disp_photo_page() {
	$userid=get_option("RSFG_userid");
	$apikey=get_option("RSFG_apikey");
	$apisecret=get_option("RSFG_apisecret");
	
	$pag=curPageURL();
	
	if(substr($pag,-1)!='/') $pag='/';
	$exp=explode("/",$pag);
	$i=0;
	$tex="";
	$rooturl="";
	while($exp[$i] || $exp[$i+1]) {
		$pslug=$exp[$i];
		if(!is_numeric($pslug)) $rooturl.=$exp[$i]."/";
		else $pid=$exp[$i];
		$i++;
	}
	
	updaterooturl($rooturl);
	
	$pageslug=$pslug;

	
	if (!$pid) {
		$photoquery="http://api.flickr.com/services/rest/?method=flickr.people.getPublicPhotos&api_key=$apikey&user_id=$userid&per_page=12&format=php_serial&page=$pg";
		$res=fgcflick($photoquery);
		$pics=unserialize($res);

		$cnt=0;
		$ret="";
		for ($i = 0; $i < 12 ; $i++){
			//print_r($prow);
			$tit=$pics['photos']['photo'][$i]['title'];
			$pid=$pics['photos']['photo'][$i]['id'];
			
			$fid=$pics['photos']['photo'][$i]['farm'];
			$sid=$pics['photos']['photo'][$i]['server'];
			$secret=$pics['photos']['photo'][$i]['secret'];
			$picurl="http://farm$fid.static.flickr.com/$sid/$pid"."_$secret"."_z.jpg";
				
			$ret.= "<h2>$tit</h2><a href=\"$rooturl$pid/\"><img src=\"$picurl\" alt=\"$tit\"></a>";
			
			$picquery="http://www.flickr.com/services/rest/?method=flickr.photos.getInfo&photo_id=$pid&api_key=$apikey&secret=$apisecret&format=php_serial";

			$response=fgcflick($picquery);
			$sete = unserialize($response);
			$des=$sete['photo']['description']['_content'];
			$des = str_replace(array("\r", "\r\n", "\n"),"<br />", $des);
			$dttm=$sete['photo']['dates']['taken'];
			$posted=$sete['photo']['dates']['posted'];
			$tme=sqltounixtime($dttm);		
			$upl=date("F j, Y",$posted);
			$tak=date("F j, Y",$tme);
			$ret.="<p style=\"font-size:x-small;\"><b>Taken:</b> $tak | <b>Posted: </b> $upl</p>";
			$ret.="<p>$des</p><hr>";
		}
	

	}
	else {
	
		$pagewidth=get_option("RSFG_pagewidth");
		if(!$pagewidth) $pagewidth=800;
		$picquery="http://www.flickr.com/services/rest/?method=flickr.photos.getInfo&photo_id=$pid&api_key=$apikey&secret=$apisecret&format=php_serial";
		$response=fgcflick($picquery);
		$sete = unserialize($response);
		
		$exifquery="http://api.flickr.com/services/rest/?method=flickr.photos.getExif&api_key=$apikey&photo_id=$pid&format=php_serial";
		$exres=fgcflick($exifquery);
		$exif_data = unserialize($exres);
		$views=updateviews($pid);
		$tit=$sete['photo']['title']['_content'];
		$ret.= "<table width=$pagewidth>";
		$ret.= "<tr><td colspan=3 style=\"width:$pagewidth"."px\"><h2>$tit</td></tr>";
		$fid=$sete['photo']['farm'];
		$sid=$sete['photo']['server'];
		$id=$sete['photo']['id'];
		$secret=$sete['photo']['secret'];
		$picurl="http://farm$fid.static.flickr.com/$sid/$id"."_$secret"."_b.jpg";
		$thmbcurl="http://farm$fid.static.flickr.com/$sid/$id"."_$secret"."_m.jpg";
		
		$des=$sete['photo']['description']['_content'];
		
		$newdes = str_replace(array("\r", "\r\n", "\n"),"<br />", $des);

		
		$dttm=$sete['photo']['dates']['taken'];
		$posted=$sete['photo']['dates']['posted'];
		//Local Posting Time
		$postedform=date("Y-m-d H:i:s",$posted);
		$postgmtime=get_date_from_gmt($postedform);
		$postedlocal=mysql2date("F j, Y, g:i a", $postgmtime, true);
		
		$lat=$sete['photo']['location']['latitude'];
		$long=$sete['photo']['location']['longitude'];
		if($sete['photo']['location']['neighbourhood']) $loc=$sete['photo']['location']['neighbourhood']['_content'];
		else if($sete['photo']['location']['locality']) $loc=$sete['photo']['location']['locality']['_content'];
		else $loc="";	
		$city=$sete['photo']['location']['county']['_content'];
		$stat=$sete['photo']['location']['region']['_content'];
		$count=$sete['photo']['location']['country']['_content'];
		$pagurl=$sete['photo']['urls']['url'][0]['_content'];
		
		$ret.="<tr><td colspan=3 valign=top><a href=\"$pagurl\" target=\"new-window\"><img src=\"$picurl\" width=\"$pagewidth"."px\"></a><br><hr><p> $newdes</p><br><hr>";
		
//FACEBOOK Comments
	$ret.="<table><tr><td>";
	if(get_option("RSFG_fblike")=="yes") {
	$ret.="
	
	<script src=\"http://connect.facebook.net/en_US/all.js#xfbml=1\"></script><fb:like href=\"$pag\" show_faces=\"true\" width=\"450\" font=\"\"></fb:like>
	<div id=\"fb-root\"></div>";
	}
	if(get_option("RSFG_fbcomm")=="yes") {
	$ret.="
	<script src=\"http://connect.facebook.net/en_US/all.js#appId=000&amp;xfbml=1\"></script><fb:comments xid=\"$pid\" href=\"$pag\" num_posts=\"5\" width=\"500\" colosrcheme=\"dark\"></fb:comments>
	</td><td>";
	}
	if(get_option("RSFG_fbpage")) {
	$fbpage=get_option("RSFG_fbpage");
	$ret.="
	<script src=\"http://connect.facebook.net/en_US/all.js#xfbml=1\"></script><fb:like-box href=\"$fbpage\" width=\"292\" colorscheme=\"dark\" show_faces=\"true\" stream=\"false\" header=\"false\"></fb:like-box>";
	}
	$ret.="</td></tr></table>";
	
//FACEBOOK Ends
		
		$tme=sqltounixtime($dttm);
		$tak=date("F j, Y, g:i a",$tme);
		
		if($loc) $place="$loc, $city, $stat, $count";
		else $place="$city, $stat, $count";
		
		$conquery="http://www.flickr.com/services/rest/?method=flickr.photos.getContext&photo_id=$pid&api_key=$apikey&secret=$apisecret&format=php_serial";
		$cres=fgcflick($conquery);
		$ctxt=unserialize($cres);
		$ppicurl=$ctxt['prevphoto']['thumb'];
		$previd=$ctxt['prevphoto']['id'];
		$npicurl=$ctxt['nextphoto']['thumb'];
		$nextid=$ctxt['nextphoto']['id'];
		$mapwidth=$pagewidth/3;
		if($pagewidth>=640) {
			$nextwidth=245;
			$mapwidth=($pagewidth-245)/2;
			$prevthmb=substr($ppicurl, 0, strlen($ppicurl)-5)."m.jpg"; 
			$nextthmb=substr($npicurl, 0, strlen($npicurl)-5)."m.jpg";
		}
		else {
			$nextwidth=105;
			$mapwidth=($pagewidth-105)/2;
			$prevthmb=substr($ppicurl, 0, strlen($ppicurl)-5)."t.jpg"; 
			$nextthmb=substr($npicurl, 0, strlen($npicurl)-5)."t.jpg";
		}
			

		$ret.="<tr><td width=$mapwidth><b>Taken on </b>$tak<b> at </b><a href=\"http://maps.google.com/maps?q=$lat,$long\" target=\"new\">$place</a><hr>";
		
			
//************MAPS
		$zoom='14';
		$type='TERRAIN';
		$ret.="
		<script type='text/javascript' src='http://maps.google.com/maps/api/js?sensor=false'></script>
		<script type='text/javascript'>
		function makeMap() {
			var latlng = new google.maps.LatLng(".$lat.", ".$long.")
			
			var myOptions = {
					zoom: ".$zoom.",
					center: latlng,
					mapTypeControl: true,
					mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU},
					navigationControl: true,
					navigationControlOptions: {style: google.maps.NavigationControlStyle.SMALL},
					mapTypeId: google.maps.MapTypeId.".$type."
			};
			var map = new google.maps.Map(document.getElementById('SGM'), myOptions);
			
			var marker = new google.maps.Marker({
					position: latlng,
					map: map,
					title: ''
			});
			
			google.maps.event.addListener(marker, 'click', function() {
				  infowindow.open(map,marker);
			});		
		
		
		}
		window.onload = makeMap;
		</script>
		<div id='SGM' style=\"width:$mapwidth"."px; height:100;\"></div>
		";
				
			
//************MAP ENDS
			
			
		$ret.="<hr><b>Posted: </b>$postedlocal<hr><b>Views:  </b>$views</td><td width=$nextwidth>";
		if($previd) {
			$ret.="<a href=\"$rooturl$previd/\">< Previous<br><img src=\"$prevthmb\"></a>";
		}	
		if($nextid) {
			$ret.="<a href=\"$rooturl$nextid/\"><br><img src=\"$nextthmb\"><br>Next ></a>";
		} 
		$ret.="</td><td width=$mapwidth><h3>Technicalities!</h3>";
		foreach($exif_data['photo']['exif'] as $extag) {
			$label=$extag['label'];
			$value=$extag['raw']['_content'];
			$cleanvalue=$extag['clean']['_content'];
			if($cleanvalue) $value=$cleanvalue;
			if($label=="Model") $ret.= "<b>Camera Model : </b> $value <br>";
			else if($label=="Exposure") $ret.= "<b>$label : </b> $value <br>";
			else if($label=="Aperture") $ret.= "<b>$label : </b> $value <br>";
			else if($label=="Exposure Program") $ret.= "<b>$label : </b> $value <br>";
			else if($label=="ISO Speed") $ret.= "<b>$label : </b> $value <br>";
			else if($label=="Exposure Bias") $ret.= "<b>$label : </b> $value <br>";
			else if($label=="Metering Mode") $ret.= "<b>$label : </b> $value <br>";
			else if($label=="Flash") $ret.= "<b>$label : </b> $value <br>";
			else if($label=="Focal Length") $ret.= "<b>$label : </b> $value <br>";
			else if($label=="White Balance") $ret.= "<b>$label : </b> $value <br>";
			else if($label=="Software") $ret.= "<b>Post-processing : </b> $value <br>";
		}
		$adcode=get_option("RSFG_adcode");
		$ret.="<hr>".$adcode;
		$ret.="</td></tr><tr>";
		$ret.="<td colspan=3>".photoroll($userid,$apikey,$rooturl)."</td></tr>";
		$ret.="</table>";

		$ret.="<h3><b>Comments on <a href=\"$pagurl\" target=\"new\">Flickr</a></b></h3>";
		
		$comquery="http://api.flickr.com/services/rest/?method=flickr.photos.comments.getList&api_key=$apikey&photo_id=$pid&format=php_serial";
		$comres=fgcflick($comquery);
		$comments=unserialize($comres);
		
		$j=0;
		$commwidth=0.8*$pagewidth;
		$ret.= "<table width=$commwidth>";
		while($comments['comments']['comment'][$j]) {
			$commid=$comments['comments']['comment'][$j]['author'];
			$commname=$comments['comments']['comment'][$j]['authorname'];
			$commtext=$comments['comments']['comment'][$j]['_content'];
			$commtext= str_replace(array("\r", "\r\n", "\n"),"<br />", $commtext);
			$commtime=$comments['comments']['comment'][$j]['datecreate'];
			
			$uquery="http://api.flickr.com/services/rest/?method=flickr.people.getInfo&api_key=$apikey&user_id=$commid&format=php_serial";
			$ures=fgcflick($uquery);
			$userinfo=unserialize($ures);
			
			$rname=$userinfo['person']['realname']['_content'];
			if(!$rname) $rname=$commname;
			$rurl=$userinfo['person']['photosurl']['_content'];
			
			if($comments['comments']['comment'][$j]['iconfarm']) {
				$ifarm=$comments['comments']['comment'][$j]['iconfarm'];
				$iser=$comments['comments']['comment'][$j]['iconserver'];
				$buddicon="http://farm$ifarm.static.flickr.com/$iser/buddyicons/$commid.jpg";
			}
			else $buddicon="http://www.flickr.com/images/buddyicon.jpg";
			
			//Flickr returns the Comment time in GMT, need to convert it to the local time using Wordpress functions
			
			$commtd=date("Y-m-d H:i:s",$commtime);
			$gmtime=get_date_from_gmt($commtd);
			$mysqtime=mysql2date("F j, Y, g:i a", $gmtime, true);
			$ret.="<tr><td><a href=\"$rurl\" target=\"new\"><img src=\"$buddicon\"></a></td>";
			$ret.="<td><a href=\"$rurl\"><b>$rname</b></a> commented on <i>$mysqtime</i><p>$commtext</p></td></tr>";
			$j++;
		}	
		$ret.="</table>";	

		$setquery="http://www.flickr.com/services/rest/?method=flickr.photos.getAllContexts&photo_id=$pid&api_key=$apikey&secret=$apisecret&format=php_serial";
		$sres=fgcflick($setquery);
		$sets=unserialize($sres);
		//print_r($sets);
		$j=0;
		while($sets['set'][$j]['id']) {
			$setid=$sets['set'][$j]['id'];
			$ret.=dispset($setid,$apikey,$rooturl);
			$j++;
		}	
	}
	$ret.="<h3>Powered by <b><a href=\"http://www.sumitkumar.info/plugins/RSFG\">Really Simple Flickr Gallery</a></b>.</h3>";
	
	return $ret;
	
}

function disp_all_photos(){
			
	$userid=get_option("RSFG_userid");
	$apikey=get_option("RSFG_apikey");
	$apisecret=get_option("RSFG_apisecret");
	
	$photopageurl=get_option("RSFG_photopageurl");
	$allphotopageurl=get_option("RSFG_allphotopageurl");
	
	$pag=curPageURL();
	$exp=explode("/",$pag);
	$i=0;
	$rooturl="";
	while($exp[$i] || $exp[$i+1]) {
		$pslug=$exp[$i];
		if(!is_numeric($pslug)) $rooturl.=$exp[$i]."/";
		else $pn=$exp[$i];
		$i++;
	}
	updateallpageurl($rooturl);
	
	$photoquery="http://api.flickr.com/services/rest/?method=flickr.people.getPublicPhotos&api_key=$apikey&user_id=$userid&per_page=12&format=php_serial&page=$pn";
	$res=fgcflick($photoquery);
	$pics=unserialize($res);
	$totalpg=$pics['photos']['pages'];
	$cnt=0;

	$ret = "<table><tr></tr>";
	$m=1;
	for ($i = 0; $i < 12 ; $i++){
		$tit=$pics['photos']['photo'][$i]['title'];
		$pid=$pics['photos']['photo'][$i]['id'];
		
		$fid=$pics['photos']['photo'][$i]['farm'];
		$sid=$pics['photos']['photo'][$i]['server'];
		$secret=$pics['photos']['photo'][$i]['secret'];
		$thmburl="http://farm$fid.static.flickr.com/$sid/$pid"."_$secret"."_m.jpg";
		
		$ret.= "<td><a href=\"$photopageurl$pid/\"><img src=\"$thmburl\" alt=\"$tit\"><br>$tit</a></td>";
		if($m==3) {
			$ret.= "</tr><tr>";
			$m=0;
		}
		$m++;
	}
	$ret.= "</tr></table>";
	$ret.= " <h2> Pages << ";
	for($c=1;$c<=$totalpg;$c++) {
		if($c==$pn) $ret.= "<b>$c </b>";
		else $ret.= "<a href=\"$allphotopageurl$c/\">$c </a>";
	}
	$ret.= " >></h2>";
	$ret.="<h3>Powered by <b><a href=\"http://www.sumitkumar.info/plugins/RSFG\">Really Simple Flickr Gallery</a></b>.</h3>";
	
	return $ret;
}

function disp_slideshow(){
			
	$userid=get_option("RSFG_userid");
	$apikey=get_option("RSFG_apikey");
	$apisecret=get_option("RSFG_apisecret");
	
	
	$pag=curPageURL();
	
	$slidepageurl=get_option("RSFG_slidepageurl");
	
	$pag=curPageURL();
	$exp=explode("/",$pag);
	$i=0;
	$rooturl="";
	$ret="";
	while($exp[$i] || $exp[$i+1]) {
		$pslug=$exp[$i];
		if(!is_numeric($pslug)) $rooturl.=$exp[$i]."/";
		else $setid=$exp[$i];
		$i++;
	}
	$userquery="http://api.flickr.com/services/rest/?method=flickr.urls.getUserPhotos&api_key=$apikey&user_id=$userid&format=php_serial";
	$res=fgcflick($userquery);
	$user=unserialize($res);
	
	$userpageurl=$user['user']['url'];
	$exp=explode("/",$userpageurl);
	$pagename=$exp[4];
	
	if($setid) {
		$ret.="<object width=\"800\" height=\"600\"> <param name=\"flashvars\" value=\"offsite=true&lang=en-us&page_show_url=%2Fphotos%2F$pagename%2Fsets%2F$setid%2Fshow%2F&page_show_back_url=%2Fphotos%2F$pagename%2Fsets%2F$setid%2F&set_id=$setid&jump_to=\"></param> <param name=\"movie\" value=\"http://www.flickr.com/apps/slideshow/show.swf?v=71649\"></param> <param name=\"allowFullScreen\" value=\"true\"></param><embed type=\"application/x-shockwave-flash\" src=\"http://www.flickr.com/apps/slideshow/show.swf?v=71649\" allowFullScreen=\"true\" flashvars=\"offsite=true&lang=en-us&page_show_url=%2Fphotos%2F$pagename%2Fsets%2F$setid%2Fshow%2F&page_show_back_url=%2Fphotos%2F$pagename%2Fsets%2F$setid%2F&set_id=$setid&jump_to=\" width=\"800\" height=\"600\"></embed></object>";
	}    	        
	else {
		$ret.="<object width=\"800\" height=\"600\"> <param name=\"flashvars\" value=\"offsite=true&lang=en-us&page_show_url=%2Fphotos%2F$pagename%2Fshow%2F&page_show_back_url=%2Fphotos%2F$pagename%2F&user_id=$userid&jump_to=\"></param> <param name=\"movie\" value=\"http://www.flickr.com/apps/slideshow/show.swf?v=71649\"></param> <param name=\"allowFullScreen\" value=\"true\"></param><embed type=\"application/x-shockwave-flash\" src=\"http://www.flickr.com/apps/slideshow/show.swf?v=71649\" allowFullScreen=\"true\" flashvars=\"offsite=true&lang=en-us&page_show_url=%2Fphotos%2F$pagename%2Fshow%2F&page_show_back_url=%2Fphotos%2F$pagename%2F&user_id=$userid&jump_to=\" width=\"800\" height=\"600\"></embed></object>";
		      
	}
	return $ret;
}

 
function photoroll($uid, $key,$rooturl){

	$photoquery="http://api.flickr.com/services/rest/?method=flickr.people.getPublicPhotos&api_key=$key&user_id=$uid&per_page=8&format=php_serial&page=$pg";
	$res=fgcflick($photoquery);
	$pics=unserialize($res);
	
	$rooturl=get_option("RSFG_photopageurl");
	$allphotopageurl=get_option("RSFG_allphotopageurl");
	$pagewidth=get_option("RSFG_pagewidth");
	$cnt=0;
	$ret= "<table><tr>";
	$ret.= "<td colspan=\"8\"><h2>Recent Photos</h2></td></tr><tr>";
	for ($i = 7; $i >-1 ; $i--){
		//print_r($prow);
		$tit=$pics['photos']['photo'][$i]['title'];
		$pid=$pics['photos']['photo'][$i]['id'];
		
		$fid=$pics['photos']['photo'][$i]['farm'];
		$sid=$pics['photos']['photo'][$i]['server'];
		$secret=$pics['photos']['photo'][$i]['secret'];
		$thpicurl="http://farm$fid.static.flickr.com/$sid/$pid"."_$secret"."_t.jpg";
        	//$colwidth=$pagewidth/8;
		$ret.= "<td><a href=\"$rooturl$pid/\"><img src=\"$thpicurl\" alt=\"$tit\" ><p style=\"font-size:small;\">$tit</p></a></td>";
		
		$picquery="http://www.flickr.com/services/rest/?method=flickr.photos.getInfo&photo_id=$pid&api_key=$flickr_api_key&secret=$secr&format=php_serial";          
	
	}

        $ret.= "</tr><tr><td colspan=\"8\"><h4><a href=\"$allphotopageurl\">All Photos</a></h4></td></tr></table>";
	return $ret;

}

function fgcflick($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);

	$response = curl_exec($ch);
	return $response;
}
 
function sqltounixtime($dttm) {
	$yr=substr($dttm,0,4);	
	$mth=substr($dttm,5,2);
	$day=substr($dttm,8,2);
	$hr=substr($dttm,11,2);	
	$min=substr($dttm,14,2);
	$sec=substr($dttm,17,2);
	$tme=mktime($hr,$min,$sec,$mth,$day,$yr);
	return $tme;

}

function dispset($setid,$apikey,$rooturl) {
	$setinfo="http://api.flickr.com/services/rest/?method=flickr.photosets.getInfo&api_key=$apikey&photoset_id=$setid&format=php_serial";
	$nam=unserialize(fgcflick($setinfo));
	$sname=$nam['photoset']['title']['_content'];
	$slidepageurl=get_option("RSFG_slidepageurl");
	$pagewidth=get_option("RSFG_pagewidth");
	$sret="<table width=$pagewidth><tr><td colspan=8>All Photos from <b>$sname</b></td></tr>";	
	if(get_option("RSFG_slidepageurl")) $sret.="<tr><td colspan=8><b><a href=\"$slidepageurl$setid/\">Slideshow</a></b></td></tr>";
	
	$setquery="http://api.flickr.com/services/rest/?method=flickr.photosets.getPhotos&api_key=$apikey&photoset_id=$setid&format=php_serial";
	$sres=fgcflick($setquery);
	$sets=unserialize($sres);
	
	if($pagewidth) {
		$colwidth=$pagewidth/8.2;
		$thmbwidth=$colwidth*0.9;
	}
	$num=$sets['photoset']['total'];
	$m=1;
	$sret.="<tr>";
	for($u=0;$u<$num;$u++) {
		$fid=$sets['photoset']['photo'][$u]['farm'];
		$sid=$sets['photoset']['photo'][$u]['server'];
		$id=$sets['photoset']['photo'][$u]['id'];
		$secret=$sets['photoset']['photo'][$u]['secret'];
		$picurl="http://farm$fid.static.flickr.com/$sid/$id"."_$secret"."_s.jpg";
		/*
		if($pagewidth) {
			$sret.="<td width=$colwidth><a href=\"$rooturl$id/\"><img src=\"$picurl\" width=\"$thmbwidth\"></a></td>";
		}
		else {
		*/
			$sret.="<td><a href=\"$rooturl$id/\"><img src=\"$picurl\"></a></td>";
		//}
		if($m==8) {
			$sret.="</tr><tr>";
			$m=0;
		}
		$m++;
	}		
		
	$sret.="</table>";
	return $sret;
}


function curPageURL() {
 $pageURL = 'http';
 if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 if(substr($pageURL,-1)!='/') $pageURL.="/";
 return $pageURL;
}

function updateviews($pid) {
	$viewopt="RSFG_views_".$pid;
	
	if(get_option($viewopt)) {
		$cnt=get_option($viewopt);
		if (!current_user_can('edit_users')) {
			update_option($viewopt,$cnt+1);
		}
		return $cnt+1;
	}	
	else {
		if (!current_user_can('edit_users')) {
			add_option($viewopt, 1,'','yes');
			return 1;
		}
		return 0;
	}	

}

function getviews($pid) {
	$viewopt="RSFG_views_".$pid;
	
	if(get_option($viewopt)) {
		return get_option($viewopt)+1;
	}	
	
}



function mytitle() {
	$userid=get_option("RSFG_userid");
	$apikey=get_option("RSFG_apikey");
	$apisecret=get_option("RSFG_apisecret");
	
	$pag=curPageURL();
	$exp=explode("/",$pag);
	$i=0;
	$tex="";
	$rooturl="";
	while($exp[$i] || $exp[$i+1]) {
		$pslug=$exp[$i];
		if(!is_numeric($pslug)) $rooturl.=$exp[$i]."/";
		else $pid=$exp[$i];
		$i++;
	}
	if($pid) {
		$picquery="http://www.flickr.com/services/rest/?method=flickr.photos.getInfo&photo_id=$pid&api_key=$apikey&secret=$apisecret&format=php_serial";
		$response=fgcflick($picquery);
		$sete = unserialize($response);
		
		$tit=$sete['photo']['title']['_content'];
		return $tit." | ";
	}
	else {
		return the_title('','',false)." &raquo; ";
	
	}
}

function updaterooturl($rooturl) {
	if(!get_option("RSFG_photopageurl")) add_option("RSFG_photopageurl", $rooturl,'','yes');
	else update_option("RSFG_photopageurl", $rooturl);
}

function updateallpageurl($allurl) {
	if(!get_option("RSFG_allphotopageurl")) add_option("RSFG_allphotopageurl", $allurl,'','yes');
	else update_option("RSFG_allphotopageurl", $allurl);
}

add_filter('wp_title', 'mytitle', 1, 1);

add_action('admin_menu', 'RSFG_add_menu');
add_shortcode("RSFG_gallery","disp_photo_page");
add_shortcode("RSFG_allphotos","disp_all_photos");
add_shortcode("RSFG_slideshow","disp_slideshow");
?>