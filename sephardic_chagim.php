<?php
//this the username for http://www.geonames.org/, used to locate time zone based on lat/long information
//you MUST update zmansettings.ini with your geonames account or the script won't work
$ini_array = parse_ini_file("zmansettings.ini");
$tzusername= $ini_array['tzusername'];

//set variables
$debug = 0;
$hebyear = $zipcode = $address = $lat = $latitude = $long = $longitude = $locstring = "";
$zipcode = $zipurl = $zipid = $get_zipinfo = "";
$address = $addurl = $addurlencoded = $get_addinfo = "";
$UTC = $newTZ = $UTCfrisunset = $UTCfrisunrise = "";
$zmanurl = $get_zmanim = $zmanresponse = "";
$monthname = $daynum = $chagdate = $chaginfo = "";
$chagdownum = $chagdowname = $chagnetz = $chagshkia = $chagtzet = $latemotzei = $chagalot = $chagshaa = $chagminchaged = $chagminchket = $chagshema = $chagplag = "";
$chagmincha = $chagarvit = $chagcandles = $chagextras = $chagprep = $havdallah = $chagmotzei = "";


//get location, year, and other common variables like you do with the weekly times
//get incoming variables
if(isset($_GET['hebyear'])) {$hebyear=stripcslashes($_GET['hebyear']);}
if(isset($_GET['zipcode'])) {$zipcode=stripcslashes($_GET['zipcode']); }
if(isset($_GET['country'])) {$country=stripcslashes($_GET['country']); }
if(isset($_GET['address'])) {$address=stripcslashes($_GET['address']); }
if(isset($_GET['lat'])) {$latitude=stripcslashes($_GET['lat']); }
if(isset($_GET['long'])) {$longitude=stripcslashes($_GET['long']); }
if(isset($_GET['debug'])) {$debug=stripcslashes($_GET['debug']); }

//sanitize some initial inputs
if ($hebyear){
    if (preg_match('/^[0-9]{4}$/', $hebyear)) {
    } else {
        echo("<H2>not a valid Hebrew year</h2>\n");
        exit(1);
    }
} else {
    //get year
    $zmanurl = "https://www.hebcal.com/converter?cfg=json";
    $get_zmanim = callAPI('GET', $zmanurl, false);
    $zmanresponse = json_decode($get_zmanim, true);
    $hebyear = $zmanresponse['hy'];
}
if ($zipcode){
    if (!$country) {
        echo("<H2>Zip Code also requires a valid <A HREF=\"https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes\">ISO-3166 Country code</a></h2>\n");   
        exit(1);
    }
    if (preg_match('/^[0-9]{5}$/', $zipcode)) {
    } else {
        echo("<H2>not a valid 5 digit zip code</h2>\n");
        exit(1);
    }
}
if ($country){
    if (!$zipcode) {
        echo("<H2>Country also requires a valid zip code</a></h2>\n");  
        exit(1);
    }
    if (preg_match('/^[a-z,A-Z]{2}$/', $country)) {
    } else {
        echo("<H2>not a valid 2 letter <A HREF=\"https://en.wikipedia.org/wiki/List_of_ISO_3166_country_codes\">ISO-3166 Country code</a>.</h2>\n");
        exit(1);
    }
}
if ($address) {
    $address = htmlspecialchars($address);
   $address = stripslashes($address);
   $address = trim($address);
}
if ($latitude){
    if ($latitude >= -90 && $latitude <= 90) {
    } else {
        echo("<H2>Not a valid latitude coordinate</h2>\n");
        exit(1);
    }
}
if ($longitude){
    if ($longitude >= -180 && $longitude <= 180) {
    } else {
        echo("<H2>Not a valid longitude coordinate</h2>\n");
        exit(1);
    }
}
if ($debug == 1 || $debug == 0) {
} else {
    echo("<H2>Debug must be 0 or 1</h2>\n");
    exit(1);
}


//set location
if ($zipcode != "") {
    $zipurl = "http://api.geonames.org/postalCodeSearchJSON?postalcode=$zipcode&country=$country&username=$tzusername";
    $get_zipinfo = callAPI('GET', $zipurl, false);
    $zipresponse = json_decode($get_zipinfo, true);
    $latitude = $zipresponse['postalCodes']['0']['lat'];
    $longitude = $zipresponse['postalCodes']['0']['lng'];
    $tzurl = "http://api.geonames.org/timezoneJSON?lat=$latitude&lng=$longitude&username=$tzusername";
    $get_tzname = callAPI('GET', $tzurl, false);
    $tzresponse = json_decode($get_tzname, true);
    $tzid = $tzresponse['timezoneId'];
    $geostring = "geo=pos&latitude=$latitude&longitude=$longitude&tzid=$tzid";
    $locstring = "Lat: $latitude, Long $longitude, Timezone $tzid";
} elseif ($address != "") {
    $addurlencoded = urlencode($address);
    $addurl = "http://api.geonames.org/geoCodeAddressJSON?q=\"$addurlencoded\"&username=$tzusername";
    $get_addinfo = callAPI('GET', $addurl, false);
    $addresponse = json_decode($get_addinfo, true);
    $latitude = $addresponse['address']['lat'];
    $longitude = $addresponse['address']['lng'];
    $tzurl = "http://api.geonames.org/timezoneJSON?lat=$latitude&lng=$longitude&username=$tzusername";
    $get_tzname = callAPI('GET', $tzurl, false);
    $tzresponse = json_decode($get_tzname, true);
    $tzid = $tzresponse['timezoneId'];
    $geostring = "geo=pos&latitude=$latitude&longitude=$longitude&tzid=$tzid";
    $locstring = "Lat: $latitude, Long $longitude, Timezone $tzid";
} elseif ($latitude  != "" && $longitude != "") {
    $tzurl = "http://api.geonames.org/timezoneJSON?lat=$latitude&lng=$longitude&username=$tzusername";
    $get_tzname = callAPI('GET', $tzurl, false);
    $tzresponse = json_decode($get_tzname, true);
    $tzid = $tzresponse['timezoneId'];
    $geostring = "geo=pos&latitude=$latitude&longitude=$longitude&tzid=$tzid";
    $locstring = "Lat: $latitude, Long $longitude, Timezone $tzid";
} else {
    $latitude = "41.4939407";
    $longitude = "-81.516709";
    $tzid = "America/New_York";
    $geostring = "geo=pos&latitude=$latitude&longitude=$longitude&tzid=$tzid";
    $locstring = "BKCS Building";
}

//set up the web page
echo "<!DOCTYPE html>
<html>
<head>
    <title>Sephardic Congregation of Cleveland Zmanim</title>
</head>
<body>
<img src=\"header.png\" width=\"1100\">
<center><H1>Chag Zmanim for " . $hebyear . "</H1></center>
<P>";

//get Rosh Hashana- 1-2 Tishrei
$monthname = "Tishrei";
$daynum = "01";
$zmanurl = "https://www.hebcal.com/converter?cfg=json&hy=$hebyear&hm=$monthname&hd=$daynum&h2g=1";
$get_zmanim = callAPI('GET', $zmanurl, false);
$zmanresponse = json_decode($get_zmanim, true);
$chagdate = date('Y-m-d', mktime(0,0,0,$zmanresponse['gm'],$zmanresponse['gd'],$zmanresponse['gy']));
//get times
//Erev is chagdate - 1
$erev = date('Y-m-d', strtotime( $chagdate . " -1 days"));
$chagdone = 0;
$chaginfo = getzmanim($erev, $latitude, $longitude, $geostring, $tzid);
    $chagerevdownum = $chaginfo[0];
$chagzmanim = chagzmanim($chaginfo[0], $chagerevdownum, $chaginfo[3], $chagdone);

//print day info here
echo "<h3>Erev Rosh Hashana - " . $chaginfo[1] . " " . $erev . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//Rosh Hashana day 1 - 1 Tishrei
$chagdone = 0;    
$chaginfo = getzmanim($chagdate, $latitude, $longitude, $geostring, $tzid);
$chagzmanim = chagzmanim($chagdownum, $chagerevdownum, $chagshkia, $chagdone);

//print day info here
echo "<h3>Rosh Hashana 1 - " . $chaginfo[1] . " " .  $chagdate . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//Rosh Hashana day 2 - 2 Tishrei
$chagdate2 = date('Y-m-d', strtotime( $chagdate . " +1 days"));
$chagdone = 1;
$chaginfo = getzmanim($chagdate2, $latitude, $longitude, $geostring, $tzid);
$chagzmanim = chagzmanim($chagdownum, $chagerevdownum, $chagshkia, $chagdone);

//print day info here
echo "<h3>Rosh Hashana 2 - " . $chaginfo[1] . " " . $chagdate2 . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
//    echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//Tzom Gedalia - 3 Tishrei
$chagdate3 = date('Y-m-d', strtotime( $chagdate . " +2 days"));
$chagdone = 0;
$chaginfo = getzmanim($chagdate3, $latitude, $longitude, $geostring, $tzid);
$chagzmanim = chagzmanim($chagdownum, $chagerevdownum, $chagshkia, $chagdone);

//print day info here
echo "<h3>Tzom Gedalia - " . $chaginfo[1] . " " . $chagdate3 . "</h3>";
    echo "Fast begins: " . $chaginfo[6] . "<br>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    //echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    echo "Fast ends: " . date('g:ia', strtotime( $chaginfo[3] . " +45 minutes")) . "<br>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

    //echo "Candles: " . $chagcandles . "<br/>";

//get Yom Kippur- 10 Tishrei
$monthname = "Tishrei";
$daynum = "10";
$zmanurl = "https://www.hebcal.com/converter?cfg=json&hy=$hebyear&hm=$monthname&hd=$daynum&h2g=1";
$get_zmanim = callAPI('GET', $zmanurl, false);
$zmanresponse = json_decode($get_zmanim, true);
$chagdate = date('Y-m-d', mktime(0,0,0,$zmanresponse['gm'],$zmanresponse['gd'],$zmanresponse['gy']));    
//Erev is chagdate - 1
$erev = date('Y-m-d', strtotime( $chagdate . " -1 days"));
//get times
$chagdone = 0;
$chaginfo = getzmanim($erev, $latitude, $longitude, $geostring, $tzid);
    $chagerevdownum = $chaginfo[0];
$chagzmanim = chagzmanim($chaginfo[0], $chagerevdownum, $chaginfo[3], $chagdone);

//print day info here
echo "<h3>Erev Yom Kippur - " . $chaginfo[1] . " " . $erev . "</h3>";
    echo "Fast begins: " . $chaginfo[6] . "<br>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    echo "Fast begins: " . $chaginfo[3] . "<br>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//Yom Kippur day
//get times
$chagdone = 1;
$chaginfo = getzmanim($chagdate, $latitude, $longitude, $geostring, $tzid);
$chagzmanim = chagzmanim($chagdownum, $chagerevdownum, $chagshkia, $chagdone);

//print day info here
echo "<h3>Yom Kippur - " . $chaginfo[1] . " " . $erev . "</h3>";
    echo "Fast begins: " . $chaginfo[6] . "<br>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    //echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    echo "Fast ends: " . date('g:ia', strtotime( $chaginfo[3] . " +45 minutes")) . "<br>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//get Sukkot - 15-16 Tishrei
$monthname = "Tishrei";
$daynum = "15";
$zmanurl = "https://www.hebcal.com/converter?cfg=json&hy=$hebyear&hm=$monthname&hd=$daynum&h2g=1";
$get_zmanim = callAPI('GET', $zmanurl, false);
$zmanresponse = json_decode($get_zmanim, true);
$chagdate = date('Y-m-d', mktime(0,0,0,$zmanresponse['gm'],$zmanresponse['gd'],$zmanresponse['gy']));
//get times
//Erev is chagdate - 1
$erev = date('Y-m-d', strtotime( $chagdate . " -1 days"));
$chagdone = 0;
$chaginfo = getzmanim($erev, $latitude, $longitude, $geostring, $tzid);
    $chagerevdownum = $chaginfo[0];
$chagzmanim = chagzmanim($chaginfo[0], $chagerevdownum, $chaginfo[3], $chagdone);

//print day info here
echo "<h3>Erev Sukkot - " . $chaginfo[1] . " " . $erev . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//Sukkot day 1 - 15 Tishrei
$chagdone = 0;    
$chaginfo = getzmanim($chagdate, $latitude, $longitude, $geostring, $tzid);
$chagzmanim = chagzmanim($chagdownum, $chagerevdownum, $chagshkia, $chagdone);

//print day info here
echo "<h3>Sukkot 1 - " . $chaginfo[1] . " " .  $chagdate . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//Sukkot day 2 - 16 Tishrei
$chagdate2 = date('Y-m-d', strtotime( $chagdate . " +1 days"));
$chagdone = 1;
$chaginfo = getzmanim($chagdate2, $latitude, $longitude, $geostring, $tzid);
$chagzmanim = chagzmanim($chagdownum, $chagerevdownum, $chagshkia, $chagdone);

//print day info here
echo "<h3>Sukkot 2 - " . $chaginfo[1] . " " . $chagdate2 . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//Sukkot 7 Hoshana Raba - 21 Tishrei
$monthname = "Tishrei";
$daynum = "22"; //effectively "erev" of the sukkot chag
$zmanurl = "https://www.hebcal.com/converter?cfg=json&hy=$hebyear&hm=$monthname&hd=$daynum&h2g=1";
$get_zmanim = callAPI('GET', $zmanurl, false);
$zmanresponse = json_decode($get_zmanim, true);
$chagdate = date('Y-m-d', mktime(0,0,0,$zmanresponse['gm'],$zmanresponse['gd'],$zmanresponse['gy']));
//Erev is chagdate - 1
$erev = date('Y-m-d', strtotime( $chagdate . " -1 days"));
//get times
$chagdone = 0;
$chaginfo = getzmanim($erev, $latitude, $longitude, $geostring, $tzid);
    $chagerevdownum = $chaginfo[0];
$chagzmanim = chagzmanim($chaginfo[0], $chagerevdownum, $chaginfo[3], $chagdone);

//print day info here
echo "<h3>Hoshana Raba - " . $chaginfo[1] . " " . $erev . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//Sukkot 8 Shmini Atzeret - 22 Tishrei
$chagdone = 0;    
$chaginfo = getzmanim($chagdate, $latitude, $longitude, $geostring, $tzid);
$chagzmanim = chagzmanim($chagdownum, $chagerevdownum, $chagshkia, $chagdone);

//print day info here
echo "<h3>Shmini Atzseret - " . $chaginfo[1] . " " .  $chagdate . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//Simchat Torah - 23 Tishrei
$chagdate2 = date('Y-m-d', strtotime( $chagdate . " +1 days"));
$chagdone = 1;
$chaginfo = getzmanim($chagdate2, $latitude, $longitude, $geostring, $tzid);
$chagzmanim = chagzmanim($chagdownum, $chagerevdownum, $chagshkia, $chagdone);

//print day info here
echo "<h3>Simchat Torah - " . $chaginfo[1] . " " . $chagdate2 . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    //echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

// get Asara B'Tevet 10 Tevet
$monthname = "Tevet";
$daynum = "10";
$zmanurl = "https://www.hebcal.com/converter?cfg=json&hy=$hebyear&hm=$monthname&hd=$daynum&h2g=1";
$get_zmanim = callAPI('GET', $zmanurl, false);
$zmanresponse = json_decode($get_zmanim, true);
$aseret1 = date('Y-m-d', mktime(0,0,0,$zmanresponse['gm'],$zmanresponse['gd'],$zmanresponse['gy']));
//get times
$chagdone = 0;
$chaginfo = getzmanim($erev, $latitude, $longitude, $geostring, $tzid);
    $chagerevdownum = $chaginfo[0];
$chagzmanim = chagzmanim($chaginfo[0], $chagerevdownum, $chaginfo[3], $chagdone);

//print day info here
echo "<h3>Aseret b'Tevet - " . $chaginfo[1] . " " . $erev . "</h3>";
    echo "Fast begins: " . $chaginfo[6] . "<br>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    //echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    echo "Fast ends: " . date('g:ia', strtotime( $chaginfo[3] . " +45 minutes")) . "<br>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

// get Purim - 14 Adar
$monthname = "Adar";
$daynum = "14";
$zmanurl = "https://www.hebcal.com/converter?cfg=json&hy=$hebyear&hm=$monthname&hd=$daynum&h2g=1";
$get_zmanim = callAPI('GET', $zmanurl, false);
$zmanresponse = json_decode($get_zmanim, true);
$purim1 = date('Y-m-d', mktime(0,0,0,$zmanresponse['gm'],$zmanresponse['gd'],$zmanresponse['gy']));
//Erev is chagdate - 1
$erev = date('Y-m-d', strtotime( $chagdate . " -1 days"));
//get times
$chagdone = 0;
$chaginfo = getzmanim($erev, $latitude, $longitude, $geostring, $tzid);
    $chagerevdownum = $chaginfo[0];
$chagzmanim = chagzmanim($chaginfo[0], $chagerevdownum, $chaginfo[3], $chagdone);

//print day info here
echo "<h3>Taanit Esther - " . $chaginfo[1] . " " . $erev . "</h3>";
    echo "Fast begins: " . $chaginfo[6] . "<br>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    //echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//Purim Day
$chagdone = 1;
$chaginfo = getzmanim($purim1, $latitude, $longitude, $geostring, $tzid);
$chagzmanim = chagzmanim($chagdownum, $chagerevdownum, $chagshkia, $chagdone);

//print day info here
echo "<h3>Purim - " . $chaginfo[1] . " " . $erev . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    //echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

// //Pesach 15-16 Nisan 
$monthname = "Nisan";
$daynum = "15";
$zmanurl = "https://www.hebcal.com/converter?cfg=json&hy=$hebyear&hm=$monthname&hd=$daynum&h2g=1";
$get_zmanim = callAPI('GET', $zmanurl, false);
$zmanresponse = json_decode($get_zmanim, true);
$chagdate = date('Y-m-d', mktime(0,0,0,$zmanresponse['gm'],$zmanresponse['gd'],$zmanresponse['gy']));
//Erev is chagdate - 1
//erev is taanit bechorot
$erev = date('Y-m-d', strtotime( $chagdate . " -1 days"));
$chagdone = 0;
$chaginfo = getzmanim($erev, $latitude, $longitude, $geostring, $tzid);
    $chagerevdownum = $chaginfo[0];
$chagzmanim = chagzmanim($chaginfo[0], $chagerevdownum, $chaginfo[3], $chagdone);

//print day info here
echo "<h3>Erev Pesach - " . $chaginfo[1] . " " . $erev . "</h3>";
    echo "Fast begins: " . $chaginfo[6] . "<br>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//Pesach 1 - 15 Nisan
$chagdone = 0;
$chaginfo = getzmanim($chagdate, $latitude, $longitude, $geostring, $tzid);
$chagzmanim = chagzmanim($chagdownum, $chagerevdownum, $chagshkia, $chagdone);

//print day info here
echo "<h3>Pesach 1 - " . $chaginfo[1] . " " .  $chagdate . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//Pesach 2 - 16 Nisan
$chagdate2 = date('Y-m-d', strtotime( $chagdate . " +1 days"));
$chagdone = 1;
$chaginfo = getzmanim($erev, $latitude, $longitude, $geostring, $tzid);
$chagzmanim = chagzmanim($chaginfo[0], $chagerevdownum, $chaginfo[3], $chagdone);

//print day info here
echo "<h3>Pesach 2 - " . $chaginfo[1] . " " . $chagdate2 . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    //echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//End of Passover 21-22 Nisan (Passover)
$monthname = "Nisan";
$daynum = "21";
$zmanurl = "https://www.hebcal.com/converter?cfg=json&hy=$hebyear&hm=$monthname&hd=$daynum&h2g=1";
$get_zmanim = callAPI('GET', $zmanurl, false);
$zmanresponse = json_decode($get_zmanim, true);
$chagdate = date('Y-m-d', mktime(0,0,0,$zmanresponse['gm'],$zmanresponse['gd'],$zmanresponse['gy']));
//Erev is chagdate - 1
$erev = date('Y-m-d', strtotime( $chagdate . " -1 days"));
$chagdone = 0;
$chaginfo = getzmanim($erev, $latitude, $longitude, $geostring, $tzid);
    $chagerevdownum = $chaginfo[0];
$chagzmanim = chagzmanim($chaginfo[0], $chagerevdownum, $chaginfo[3], $chagdone);

//print day info here
echo "<h3>Erev Pesach VII - " . $chaginfo[1] . " " . $erev . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//Pesach VII - 21 Nisan
$chagdone = 0;
$chaginfo = getzmanim($erev, $latitude, $longitude, $geostring, $tzid);
$chagzmanim = chagzmanim($chaginfo[0], $chagerevdownum, $chaginfo[3], $chagdone);

//print day info here
echo "<h3>Pesach VII - " . $chaginfo[1] . " " .  $chagdate . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//Pesach VIII - 22 Nisan
$chagdate2 = date('Y-m-d', strtotime( $chagdate . " +1 days"));
$chagdone = 1;
$chaginfo = getzmanim($erev, $latitude, $longitude, $geostring, $tzid);
$chagzmanim = chagzmanim($chaginfo[0], $chagerevdownum, $chaginfo[3], $chagdone);

//print day info here
echo "<h3>Pesach VIII - " . $chaginfo[1] . " " . $chagdate2 . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    //echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

// // get Shavuot 6-7 Sivan
$monthname = "Sivan";
$daynum = "06";
$zmanurl = "https://www.hebcal.com/converter?cfg=json&hy=$hebyear&hm=$monthname&hd=$daynum&h2g=1";
$get_zmanim = callAPI('GET', $zmanurl, false);
$zmanresponse = json_decode($get_zmanim, true);
$chagdate = date('Y-m-d', mktime(0,0,0,$zmanresponse['gm'],$zmanresponse['gd'],$zmanresponse['gy']));
//Erev is chagdate - 1
$erev = date('Y-m-d', strtotime( $chagdate . " -1 days"));
$chagdone = 0;
$chaginfo = getzmanim($erev, $latitude, $longitude, $geostring, $tzid);
    $chagerevdownum = $chaginfo[0];
$chagzmanim = chagzmanim($chaginfo[0], $chagerevdownum, $chaginfo[3], $chagdone);

//print day info here
echo "<h3>Erev Shavuot - " . $chaginfo[1] . " " . $erev . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//Shavuot 1 - 6 Sivan
$chagdone = 0;
$chaginfo = getzmanim($erev, $latitude, $longitude, $geostring, $tzid);
$chagzmanim = chagzmanim($chaginfo[0], $chagerevdownum, $chaginfo[3], $chagdone);

//print day info here
echo "<h3>Shavuot 1 - " . $chaginfo[1] . " " .  $chagdate . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//Shavuot 2 - 7 Sivan
$chagdate2 = date('Y-m-d', strtotime( $chagdate . " +1 days"));
$chagdone = 1;
$chaginfo = getzmanim($erev, $latitude, $longitude, $geostring, $tzid);
$chagzmanim = chagzmanim($chaginfo[0], $chagerevdownum, $chaginfo[3], $chagdone);

//print day info here
echo "<h3>Pesach 2 - " . $chaginfo[1] . " " . $chagdate2 . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    //echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }


// // get Tzom Tamuz - 17 Tamuz
$monthname = "Tamuz";
$daynum = "17";
$zmanurl = "https://www.hebcal.com/converter?cfg=json&hy=$hebyear&hm=$monthname&hd=$daynum&h2g=1";
$get_zmanim = callAPI('GET', $zmanurl, false);
$zmanresponse = json_decode($get_zmanim, true);
$chagdate = date('Y-m-d', mktime(0,0,0,$zmanresponse['gm'],$zmanresponse['gd'],$zmanresponse['gy']));
$chagdone = 0;
$chaginfo = getzmanim($erev, $latitude, $longitude, $geostring, $tzid);
    $chagerevdownum = $chaginfo[0];
$chagzmanim = chagzmanim($chaginfo[0], $chagerevdownum, $chaginfo[3], $chagdone);

//print day info here
echo "<h3>Tzom Tammuz - " . $chaginfo[1] . " " . $chagdate . "</h3>";
    echo "Fast begins: " . $chaginfo[6] . "<br>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    //echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    echo "Fast ends: " . date('g:ia', strtotime( $chaginfo[3] . " +45 minutes")) . "<br>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

// // get Tisha B'av - 9 Av
$monthname = "Av";
$daynum = "09";
$zmanurl = "https://www.hebcal.com/converter?cfg=json&hy=$hebyear&hm=$monthname&hd=$daynum&h2g=1";
$get_zmanim = callAPI('GET', $zmanurl, false);
$zmanresponse = json_decode($get_zmanim, true);
$chagdate = date('Y-m-d', mktime(0,0,0,$zmanresponse['gm'],$zmanresponse['gd'],$zmanresponse['gy']));    
//Erev is chagdate - 1
$erev = date('Y-m-d', strtotime( $chagdate . " -1 days"));
//get times
$chagdone = 0;
$chaginfo = getzmanim($erev, $latitude, $longitude, $geostring, $tzid);
    $chagerevdownum = $chaginfo[0];
$chagzmanim = chagzmanim($chaginfo[0], $chagerevdownum, $chaginfo[3], $chagdone);

//print day info here
echo "<h3>Erev Tisha B'Av - " . $chaginfo[1] . " " . $erev . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    echo "Fast begins: " . $chaginfo[3] . "<br>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//Tisha B'Av day - 9 Av
//get times
$chagdone = 1;
$chaginfo = getzmanim($erev, $latitude, $longitude, $geostring, $tzid);
$chagzmanim = chagzmanim($chaginfo[0], $chagerevdownum, $chaginfo[3], $chagdone);

//print day info here
echo "<h3>Tisha B'Av - " . $chaginfo[1] . " " . $erev . "</h3>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Sof Zman Kria Shema: " . $$chaginfo[10] . "<br/>";
    echo "Mincha: " . $chagzmanim[0] . "<br/>";
    echo "Arvit: " . $chagzmanim[1] . "<br/>";
    //echo "Candles: " . $chagzmanim[2] . "<br/>";
    if ($chagzmanim[4]) {echo "Preparation: " . $chagzmanim[4] . "<br/>";}
    if ($chagzmanim[3]) {echo "Notes: " . $chagzmanim[3] . "<br/>";}
    if ($chagzmanim[5]) {echo "Havdallah: " . $chagzmanim[5] . "<br/>";}
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    echo "Fast ends: " . date('g:ia', strtotime( $chaginfo[3] . " +45 minutes")) . "<br>";
    if ($chagzmanim[6]) {echo "Motzei Chag: " . $chagzmanim[6] . "<br/>";}
//end day print info

if ($debug == 1) { 
    printdebug($chaginfo, $chagzmanim, $zmanurl); 
    }

//close out the web page
echo "</table>
<P>NOTE: Times are calculated automatically based on the location informatin provided. Because zip codes can cover a large area; and because of variations in things like the source of sunrise/sunset, height of elevation, rounding seconds to minutes, etc. times may be off by as much as 2 minutes. Please plan accordingly.</P>
</body>
</html>";

//Function Junction
function getzmanim($chagdate, $latitude, $longitude, $geostring, $tzid){
    //set timezone offset
    $tz = new DateTimeZone($tzid);
    $datetime = date_create($chagdate, new DateTimeZone("GMT"));
    $tzoff = timezone_offset_get($tz, $datetime );
    $offset = $tzoff/3600;

    $chagdownum = date('w', strtotime($chagdate));
    $chagdowname = date('l', strtotime($chagdate));
    $chagnetz = date_sunrise(strtotime($chagdate),SUNFUNCS_RET_STRING,$latitude,$longitude,90.83,$offset);
    $chagnetz = date('g:ia', strtotime($chagnetz));
    $chagshkia = date_sunset(strtotime($chagdate),SUNFUNCS_RET_STRING,$latitude,$longitude,90.83,$offset);
    $chagshkia = date('g:ia', strtotime($chagshkia));

//Is this early or late?
if(strtotime($chagshkia) <= strtotime("7:35pm")) {
        $isearly=0;
    } else {
        $isearly=1;
    }

//ADJUST THIS with logic for what DOW and chag type it is (if shabbat, if chag, etc.)
    //SIMPLE CALCULATIONS
    // tzet hakochavim = shkia + 45
    // early Motzi Shabbat is the same as tzet
        $chagtzet = date('g:ia', strtotime( $chagshkia . " +45 minutes"));
    // Late Motzi Shabbat Shkia+72 
        $latemotzei = date('g:ia', strtotime( $chagshkia . " +72 minutes"));
    // Alot Hashachar ("alot") = netz-((shkia-netz)/10)
        $chagalot = date('g:ia', strtotime($chagnetz)-((strtotime($chagshkia) - strtotime($chagnetz))/10));
    // Sha'a (halachic hour) = (tzait - Alot) / 12 
        $chagshaa = (strtotime($chagtzet)-strtotime($chagalot))/12;

    //COMPOUND CALCULATIONS
    // Mincha Gedola = 6.5 sha’a after ‘alot 
        $chagminchaged = date('g:ia', strtotime($chagalot)+(((strtotime($chagtzet)-strtotime($chagalot))/12)*6.5));
    // Mincha ketana = 9.5 sha’a after ‘alot 
        $chagminchket = date('g:ia', strtotime($chagalot)+(((strtotime($chagtzet)-strtotime($chagalot))/12)*9.5));
    // Sof zman kria shema (latest time for shema in the morning = Alot + (sha'a * 3)
        $chagshema = date('g:ia', strtotime($chagalot)+(((strtotime($chagtzet)-strtotime($chagalot))/12)*3));
    // Plag Hamincha ("plag") = mincha ketana+((tzet - mincha ketana) / 2)
        $chagplag = date('g:ia', strtotime($chagminchket)+(((strtotime($chagtzet))-strtotime($chagminchket))/2));

return [$chagdownum, $chagdowname, $chagnetz, $chagshkia, $chagtzet, $latemotzei, $chagalot, $chagshaa, $chagminchaged, $chagminchket, $chagshema, $chagplag, $isearly];
}

function chagzmanim($chagdownum, $chagerevdownum, $chagshkia, $chagdone) {
// set defaults
    $chagmincha = date('g:ia', strtotime( $chagshkia . " -20 minutes"));
    $chagminchamath = "shkia-20";
    $chagarvit = "to follow";
    $chagcandles = date('g:ia', strtotime( $chagshkia . " -18 minutes"));
    $chagcandlemath = "shkia-18";
    $chagextras = "";
    $chagprep = "";
    if ($chagdone == 1) {
        $chagarvit = date('g:ia', strtotime( $chagshkia . " +30 minutes"));
        $chagarvitmath = "shkia+30";
        $havdallah = "wine and hamavdil";
        $chagmotzei = date('g:ia', strtotime( $chagshkia . " +45 minutes"));
        $chagmotzeimath = "shkia+45";
    }

//if erev is Wednesday
if ($chagerevdownum == 4) {
    if ($chagdownum == 4) {
        $chagextras = "Remember to make an Erev Tavshillin!";
    }
    if ($chagdownum == 5) {
        $chagcandles = date('g:ia', strtotime( $chagshkia . " +45 minutes"));
        $chagcandlemath = "shkia+45";
        $chagprep = "Preparations no earlier than " . $chagcandles;
    }
    if ($chagdownum == 6) {
        $chagprep = "Preparations no earlier than " . $chagcandles;
    }
}

//if erev is Thursday
if ($chagerevdownum == 5) {
    if ($chagdownum == 5) {
        $chagextras = "Remember to make an Erev Tavshillin!";
    }
    if ($chagdownum == 6) {
        $chagcandles = date('g:ia', strtotime( $chagshkia . " +45 minutes"));
        $chagcandlemath = "shkia+45";
        $chagprep = "Preparations no earlier than " . $chagcandles;
    }
    if ($chagdownum == 7) {
        $chagarvit = date('g:ia', strtotime( $chagshkia . " +50 minutes"));
        $chagarvitmath = "shkia+50";
        $chagprep = "Preparations no earlier than " . $chagcandles;
        $chagmotzei = date('g:ia', strtotime( $chagshkia . " +50 minutes")) . " / " . date('g:ia', strtotime( $chagshkia . " +72 minutes"));
        $chagmotzeimath = "shkia+50/72";
    }
}
//if erev is Friday
if ($chagerevdownum == 6) {
    if ($chagdownum == 6) {
    }
    if ($chagdownum == 7) {
        $chagmincha = date('g:ia' , strtotime("2:30pm"));
        $chagminchamath = "2:30pm";
        $havdallah = "said in kiddush";
        $chagcandles = $chagcandles = date('g:ia', strtotime( $chagshkia . " +50 minutes")) . " / " . date('g:ia', strtotime( $chagshkia . " +72 minutes"));
        $chagcandlemath = "shkia+50/72";
        $chagprep = "Preparations no earlier than " . $chagcandles = date('g:ia', strtotime( $chagshkia . " +50 minutes")) . " / " . date('g:ia', strtotime( $chagshkia . " +72 minutes"));
    }
    if ($chagdownum == 0) {
    }
}

//if erev is Saturday
if ($chagerevdownum == 7) {
    if ($chagdownum == 7) {
    }
    if ($chagdownum == 0) {
    }
    if ($chagdownum == 1) {
    }
}
return [$chagmincha, $chagarvit, $chagcandles, $chagextras, $chagprep, $havdallah, $chagmotzei, $chagcandlemath, $chagminchamath, $chagarvitmath, $chagmotzeimath];
}

function printdebug(&$chaginfo, &$chagzmanim, $zmanurl) {
//chaginfo
    // $chagdownum = $chaginfo[0];
    // $chagerevdownum = $chaginfo[0];
    // $chagdowname = $chaginfo[1];
    // $chagnetz = $chaginfo[2];
    // $chagshkia = $chaginfo[3];
    // $chagtzet = $chaginfo[4];
    // $latemotzei = $chaginfo[5];
    // $chagalot = $chaginfo[6];
    // $chagshaa = $chaginfo[7];
    // $chagminchaged = $chaginfo[8];
    // $chagminchket = $chaginfo[9];
    // $chagshema = $chaginfo[10];
    // $chagplag = $chaginfo[11];
    // $isearly = $chaginfo[12];
// $chagzmanim
    // $chagmincha = $chagzmanim[0];
    // $chagarvit = $chagzmanim[1];
    // $chagcandles = $chagzmanim[2];
    // $chagextras = $chagzmanim[3];
    // $chagprep = $chagzmanim[4];
    // $havdallah = $chagzmanim[5];
    // $chagmotzei = $chagzmanim[6];
    // $chagcandlemath = $chagzmanim[7];
    // $chagminchamath = $chagzmanim[8];
    // $chagarvitmath = $chagzmanim[9];
    // $chagmotzeimath = $chagzmanim[10];

    echo "*********************************<br>";
    echo "<h3>Debug and Detailed information</h3><br>";
    echo "zmanurl:" . $zmanurl . "<br/>";
    echo "Day: " . $chaginfo[1] . "<br/>";
    echo "Day #: " . $chaginfo[0] . "<br/>";
    echo "Is Early?: " . $chaginfo[12] . "<br/>";
    echo "Netz: " . $chaginfo[2] . "<br/>";
    echo "Alot haShachar: " . $chaginfo[6] . "<br/>";
    echo "Sof Kria Shema: " . $chaginfo[10] . "<br/>";
    echo "Mincha Gedola: " . $chaginfo[8] . "<br/>";
    echo "Mincha Ketana: " . $chaginfo[9] . "<br/>";
    echo "Plag haMincha: " . $chaginfo[11] . "<br/>";
    echo "Shkia: " . $chaginfo[3] . "<br/>";
    echo "Tzeit haKochavim: " . $chaginfo[4] . "<br/>";
    echo "Motzei late: " . $chaginfo[5] . "<br/>";
    echo "Sha'a: " . $chaginfo[7] . "<br/>";
    echo "chagmincha: " . $chagzmanim[0] . " (". $chagzmanim[8] . ")<br/>";
    echo "chagarvit: " . $chagzmanim[1] . " (". $chagzmanim[9] . ")<br/>";
    echo "chagcandles: " . $chagzmanim[2] . " (". $chagzmanim[7] . ")<br/>";
    echo "chagextras: " . $chagzmanim[3] . "<br/>";
    echo "chagprep: " . $chagzmanim[4] . "<br/>";
    echo "havdallah: " . $chagzmanim[5] . "<br/>";
    echo "chagmotzei: " . $chagzmanim[6] . " (". $chagzmanim[10] . ")<br/>";
}

function callAPI($method, $url, $data){
   $curl = curl_init();
   switch ($method){
      case "POST":
         curl_setopt($curl, CURLOPT_POST, 1);
         if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
         break;
      case "PUT":
         curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
         if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);                              
         break;
      default:
         if ($data)
            $url = sprintf("%s?%s", $url, http_build_query($data));
   }
   // OPTIONS:
   curl_setopt($curl, CURLOPT_URL, $url);
   curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'APIKEY: 111111111111111111111',
      'Content-Type: application/json',
   ));
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
   // EXECUTE:
   $result = curl_exec($curl);
   if(!$result){die("Connection Failure");}
   curl_close($curl);
   return $result;
}
?>