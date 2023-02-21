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
$monthname = "Elul";
$daynum = "29";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 0;
$oldhebyear = $hebyear-1;
$times = getzmanim($oldhebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Erev Rosh Hashana - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
    echo "Candles: " . $times[12] . "<br/>";
    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//Rosh Hashana day 1 - 1 Tishrei
$monthname = "Tishrei";
$daynum = "01";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 1;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Rosh Hashana 1 - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
#    echo "Candles: " . $times[12] . "<br/>";
    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//Rosh Hashana day 2 - 2 Tishrei
$monthname = "Tishrei";
$daynum = "02";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 2;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Rosh Hashana 2 - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
#    echo "Candles: " . $times[12] . "<br/>";
    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//Tzom Gedalia - 3 Tishrei
$monthname = "Tishrei";
$daynum = "03";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 3;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Tzom Gedalia - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Fast begins: " . $times[4] . "<br>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
#    echo "Candles: " . $times[12] . "<br/>";
    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    echo "Fast ends: " . date('g:ia', strtotime( $times[15] . " +45 minutes")) . "<br>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//get Yom Kippur- 10 Tishrei
$monthname = "Tishrei";
$daynum = "09";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 0;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Erev Yom Kippur - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
    echo "Candles: " . $times[12] . "<br/>";
    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//Yom Kippur day
$monthname = "Tishrei";
$daynum = "10";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 1;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Yom Kippur - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
    echo "Candles: " . $times[12] . "<br/>";
    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    echo "Fast ends: " . date('g:ia', strtotime( $times[15] . " +45 minutes")) . "<br>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//get Sukkot - 15-16 Tishrei
$monthname = "Tishrei";
$daynum = "14";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 0;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Erev Sukkot - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
    echo "Candles: " . $times[12] . "<br/>";
    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//Sukkot day 1 - 15 Tishrei
$monthname = "Tishrei";
$daynum = "15";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 1;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Sukkot 1 - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
    echo "Candles: " . $times[12] . "<br/>";
    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//Sukkot day 2 - 16 Tishrei
$monthname = "Tishrei";
$daynum = "16";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 2;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Sukkot 2 - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
#    echo "Candles: " . $times[12] . "<br/>";
#    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//Sukkot 7 Hoshana Raba - 21 Tishrei
$monthname = "Tishrei";
$daynum = "21";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 0;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Hoshana Raba - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
    echo "Candles: " . $times[12] . "<br/>";
    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//Sukkot 8 Shmini Atzeret - 22 Tishrei
$monthname = "Tishrei";
$daynum = "22";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 1;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Shmini Atzseret - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
    echo "Candles: " . $times[12] . "<br/>";
    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//Simchat Torah - 23 Tishrei
$monthname = "Tishrei";
$daynum = "23";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 2;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Simchat Torah - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
#    echo "Candles: " . $times[12] . "<br/>";
#    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

// get Asara B'Tevet 10 Tevet
$monthname = "Tevet";
$daynum = "10";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 0;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Aseret b'Tevet - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Fast begins: " . $times[4] . "<br>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
#    echo "Candles: " . $times[12] . "<br/>";
#    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    echo "Fast ends: " . date('g:ia', strtotime( $times[15] . " +45 minutes")) . "<br>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

// get Purim - 14 Adar
$monthname = "Adar";
$daynum = "13";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 0;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Taanit Esther - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
#    echo "Candles: " . $times[12] . "<br/>";
    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//Purim Day
$monthname = "Adar";
$daynum = "14";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 1;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Purim - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
#    echo "Candles: " . $times[12] . "<br/>";
#    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//Pesach 15-16 Nisan 
$monthname = "Nisan";
$daynum = "14";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 0;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

//print day info here
echo "<h3>Erev Pesach - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Fast begins: " . $times[4] . "<br>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
    echo "Candles: " . $times[12] . "<br/>";
    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//Pesach 1 - 15 Nisan
$monthname = "Nisan";
$daynum = "15";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 1;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);
print_r($times);

echo "<h3>Pesach 1 - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
#    echo "Candles: " . $times[12] . "<br/>";
    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//Pesach 2 - 16 Nisan
$monthname = "Nisan";
$daynum = "16";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 2;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

echo "<h3>Pesach 2 - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
#    echo "Candles: " . $times[12] . "<br/>";
#    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//End of Passover 21-22 Nisan (Passover)
$monthname = "Nisan";
$daynum = "20";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 0;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Erev Pesach VII - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
    echo "Candles: " . $times[12] . "<br/>";
    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//Pesach VII - 21 Nisan
$monthname = "Nisan";
$daynum = "21";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 1;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Pesach VII - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
#    echo "Candles: " . $times[12] . "<br/>";
    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//Pesach VIII - 22 Nisan
$monthname = "Nisan";
$daynum = "22";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 2;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Pesach VIII - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
#    echo "Candles: " . $times[12] . "<br/>";
#    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

// get Shavuot 6-7 Sivan
$monthname = "Sivan";
$daynum = "05";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 0;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Erev Shavuot - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
    echo "Candles: " . $times[12] . "<br/>";
    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//Shavuot 1 - 6 Sivan
$monthname = "Sivan";
$daynum = "06";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 1;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Shavuot 1 - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
#    echo "Candles: " . $times[12] . "<br/>";
    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//Shavuot 2 - 7 Sivan
$monthname = "Sivan";
$daynum = "07";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 2;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Shavuot 2 - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
#    echo "Candles: " . $times[12] . "<br/>";
#    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

// get Tzom Tamuz - 17 Tamuz
$monthname = "Tamuz";
$daynum = "17";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 1;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Tzom Tammuz - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Fast begins: " . $times[4] . "<br>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
#    echo "Candles: " . $times[12] . "<br/>";
#    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    echo "Fast ends: " . date('g:ia', strtotime( $times[15] . " +45 minutes")) . "<br>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

// get Tisha B'av - 9 Av
$monthname = "Av";
$daynum = "08";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 0;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Erev Tisha B'Av - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
    echo "Candles: " . $times[12] . "<br/>";
    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    echo "Fast begins: " . $times[15] . "<br>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }

//Tisha B'Av day - 9 Av
$monthname = "Av";
$daynum = "09";
//dayseq 0 = erev, 1=1st day, 2=2nd day, 3=3rd day
$dayseq = 1;
$times = getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid);

echo "<h3>Tisha B'Av - " . $times[2] . " " . $times[0] . "</h3>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Ashrei: " . $times[12] . "<br/>";    
    echo "Mincha: " . $times[9] . "<br/>";
    echo "Arvit: " . $times[11] . "<br/>";
    echo "Candles: " . $times[12] . "<br/>";
#    if ($times[21]) {echo "Preparation: " . $times[21] . "<br/>";}
    if ($times[20]) {echo "Notes: " . $times[20] . "<br/>";}
    if ($times[13]) {echo "Havdallah: " . $times[13] . "<br/>";}
    echo "Shkia: " . $times[15] . "<br/>";
    echo "Fast ends: " . date('g:ia', strtotime( $times[15] . " +45 minutes")) . "<br>";
    if ($times[14]) {echo "Motzei Chag: " . $times[14] . "<br/>";}
//end day print info

//print day info here
if ($debug == 1) { 
    printdebug($times); 
    }


//close out the web page
echo "</table>
<P>NOTE: Times are calculated automatically based on the location informatin provided. Because zip codes can cover a large area; and because of variations in things like the source of sunrise/sunset, height of elevation, rounding seconds to minutes, etc. times may be off by as much as 2 minutes. Please plan accordingly.</P>
</body>
</html>";

//Function Junction
//merge gettimes, getzmanim, and chagzmanim into a single function since gettimes was just calling the other 2 anyway.
//formerly function gettimes:
function getzmanim($hebyear, $monthname, $daynum, $dayseq, $latitude, $longitude, $geostring, $tzid) {
    //zero everything out:
    $chagdate =  $chagdownum =  $chagdowname =  $zmanurl =  $chagalot =  $chagnetz =  $chagshema =  $chagminchaged =  $chagminchket =  $chagmincha =  $chagplag =  $chagarvit =  $chagcandles =  $havdallah =  $chagmotzei =   $chagshkia =  $chagtzet =  $latemotzei =  $chagshaa =  $isearly =  $chagextras =  $chagprep =  $chagcandlemath =  $chagminchamath =  $chagarvitmath =  $chagmotzeimath =  $zmanurl = "";

    $zmanurl = "https://www.hebcal.com/converter?cfg=json&hy=$hebyear&hm=$monthname&hd=$daynum&h2g=1";
    #print "zmanurl is $zmanurl<br/>";
    $get_zmanim = callAPI('GET', $zmanurl, false);
    $zmanresponse = json_decode($get_zmanim, true);
    $chagdate = date('Y-m-d', mktime(0,0,0,$zmanresponse['gm'],$zmanresponse['gd'],$zmanresponse['gy']));
    $chagerevdownum = date('w', strtotime( $chagdate . " -" . $dayseq . " days"));
    $chagdownum = date('w', strtotime($chagdate));
    $chagdowname = date('l', strtotime($chagdate));
    if ($dayseq == 2) {$chagdone = 1;}

    //set timezone offset
    $tz = new DateTimeZone($tzid);
    $datetime = date_create($chagdate, new DateTimeZone("GMT"));
    $tzoff = timezone_offset_get($tz, $datetime );
    $offset = $tzoff/3600;

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
// set defaults
    $chagmincha = date('g:ia', strtotime( $chagshkia . " -23 minutes"));
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

$preptime45 = date('g:ia', strtotime($chagshkia. ' +72 minutes'));
$preptime50 = date('g:ia', strtotime($chagshkia. ' +50 minutes'));
$preptime72 = date('g:ia', strtotime($chagshkia. ' +72 minutes'));

//if it's between day 1 and 2
    if ($dayseq == 1) {
        $chagprep = "Candles, prep, etc no earlier than $preptime45";
        $chagcandles = "";
}
//if erev is Wednesday
if ($chagerevdownum == 3) { 
    if ($chagdownum == 3) {
        $chagextras = "Remember to make an Erev Tavshillin!";
    }
    if ($chagdownum == 4) {
        $chagprep = "Candles, Prep, etc. no earlier than $preptime45";
    }
    if ($chagdownum == 5) {
        $chagprep = "Candles, Prep, etc. no earlier than $preptime45";
    }
}

//if erev is Thursday
if ($chagerevdownum == 4) { 
    if ($chagdownum == 4) {
        $chagextras = "Remember to make an Erev Tavshillin!";
    }
    if ($chagdownum == 5) {
        $chagprep = "Candles, Prep, etc. no earlier than $chagcandles";
    }
    if ($chagdownum == 6) {
        $chagarvitmath = "per shabbat";
        $chagmotzei = $chagmotzei . "/" . $preptime72;
        $chagmotzeimath = "per Shabbat";
    }
}
//if erev is Friday
if ($chagerevdownum == 5) { 
    if ($chagdownum == 5) {
    }
    if ($chagdownum == 6) {
        $chagmincha = date('g:ia' , strtotime("2:30pm"));
        $chagminchamath = "fixed at 2:30pm";
        $havdallah = "said in kiddush";
        $chagcandlemath = "shkia+50/72";
        $chagprep = "Preparations no earlier than $preptime50 / $preptime72";
    }
    if ($chagdownum == 0) {
        $havdallah = "wine, hamavdil";
        $chagarvit = date('g:ia' , strtotime($chagshkia. " +30 Minutes"));
        $chagarvitmath = "shkia+30";
    }
}

//if erev is Saturday
if ($chagerevdownum == 6) { 
    if ($chagdownum == 6) {
        $chagmincha = date('g:ia' , strtotime("2:30pm"));
        $chagminchamath = "2:30pm";
        $chagarvit = date('g:ia' , strtotime($chagshkia. " +30 Minutes"));
        $chagarvitmath = "shkia+30";
        $havdallah = "said in kiddush";
        $chagcandlemath = "shkia+50/72";
        $chagprep = "Preparations no earlier than $preptime50 / $preptime72";
    }
    if ($chagdownum == 0) {
        $chagprep = "Candles, Prep, etc. no earlier than $preptime45";
    }
    if ($chagdownum == 1) {
        $havdallah = "wine and hamavdil";
    }

}

return [$chagdate, $chagdownum, $chagdowname, $zmanurl, $chagalot, $chagnetz, $chagshema, $chagminchaged, $chagminchket, $chagmincha, $chagplag, $chagarvit, $chagcandles, $havdallah, $chagmotzei,  $chagshkia, $chagtzet, $latemotzei, $chagshaa, $isearly, $chagextras, $chagprep, $chagcandlemath, $chagminchamath, $chagarvitmath, $chagmotzeimath, $zmanurl];
}

function printdebug(&$times) {

#times[]:
# 0-$chagdate, 1-$chagdownum, 2-$chagdowname, 3-$zmanurl, 4-$chagalot, 5-$chagnetz, 6-$chagshema, 7-$chagminchaged, 8-$chagminchket, 9-$chagmincha, 10-$chagplag, 11-$chagarvit, 12-$chagcandles, 13-$havdallah, 14-$chagmotzei, 15-$chagshkia, 16-$chagtzet, 17-$latemotzei, 18-$chagshaa, 19-$isearly, 20-$chagextras, 21-$chagprep, 22-$chagcandlemath, 23-$chagminchamath, 24-$chagarvitmath, 25-$chagmotzeimath, 26-$zmanurl

    echo "*********************************<br>";
    echo "<h3>Debug and Detailed information</h3>";
    echo "zmanurl:" . $times[26] . "<br/>";
    echo "Day: " . $times[2] . "<br/>";
    echo "Day #: " . $times[1] . "<br/>";
    echo "Is Early?: " . $times[19] . "<br/>";
    echo "Netz: " . $times[5] . "<br/>";
    echo "Alot haShachar: " . $times[4] . "<br/>";
    echo "Sof Kria Shema: " . $times[6] . "<br/>";
    echo "Mincha Gedola: " . $times[7] . "<br/>";
    echo "Mincha Ketana: " . $times[8] . "<br/>";
    echo "Plag haMincha: " . $times[10] . "<br/>";
    echo "Shkia: " . $times[15] . "<br/>";
    echo "Tzeit haKochavim: " . $times[16] . "<br/>";
    echo "Motzei late: " . $times[17] . "<br/>";
    echo "Sha'a: " . $times[18] . "<br/>";
    echo "chagmincha: " . $times[9] . " (". $times[23] . ")<br/>";
    echo "chagarvit: " . $times[11] . " (". $times[24] . ")<br/>";
    echo "chagcandles: " . $times[12] . " (". $chagzmanim[22] . ")<br/>";
    echo "chagextras: " . $times[20] . "<br/>";
    echo "chagprep: " . $times[21] . "<br/>";
    echo "havdallah: " . $times[13] . "<br/>";
    echo "chagmotzei: " . $times[14] . " (". $times[25] . ")<br/>";
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