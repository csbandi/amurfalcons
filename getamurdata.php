<?php
# author: cs.bandi@gmail.com
# The data of two amur falcons being tracking by scientists is being shared with public. 
# However the website is data intensive with results being shown on google map layout
# The challenge is when people in the field with poor connectivity tries to access, it consumes lot of data and takes time to load.
# The idea is to develop a webpage that does the task quickly displaying last publicly known location of falcons in text formatted_address
# Hence saving time and money.
#echo phpinfo(); 
#Test tracking URL - "http://satellitetracking.eu/inds/getmarkers/143,145/1414260326";
#Test google URL - "https://maps.googleapis.com/maps/api/geocode/json?latlng=40.714224,-73.961452&key=AIzaSyDjTQw3WL9SR57GFl5HMZmWZN3kIG5mt6k

#Initialising variables
$amursite_url = "http://satellitetracking.eu/inds/getmarkers/143,145/";
$amursite_url = $amursite_url . time(); # appending timestamp
$amur_XML = 'amurdata.xml';
$api_key = "&key="."<your google api key>"; #ATTENTION: replace with your API key
$goglApi_url= "https://maps.googleapis.com/maps/api/geocode/xml?latlng=";
#End of variable initialisation


# Step 1: Fetch data from the satellite tracking website for the two amur falcons being tracked
FetchData($amursite_url,$amur_XML, false);

# Step 2: Process the data and retrieve the latitude and longitude of the birds
$x = simplexml_load_file($amur_XML);
$falcon1['Name'] = trim($x->marker["0"]["ind_name"]);
$falcon1['latitude'] = trim($x->marker["0"]["lat"]);
$falcon1['longitude'] = trim($x->marker["0"]["lng"]);
$falcon2['Name'] = trim($x->marker["1"]["ind_name"]);
$falcon2['latitude'] = trim($x->marker["1"]["lat"]);
$falcon2['longitude'] =trim($x->marker["1"]["lng"]);
echo("\n lat, long of falcon -". $falcon1['Name']. " are :". $falcon1['latitude'] . ", ". $falcon1['longitude']);
echo("\n lat, long of falcon -". $falcon2['Name']. " are :". $falcon2['latitude'] . ", ". $falcon2['longitude']);
#Finished finding falcons current location

#Step 3: Start of google reverse geocoding api calls to retrieve the name of the location of the falcons 

# Step 3.1 : Building urls for API 
$goglApi_url1 = $goglApi_url . $falcon1['latitude'] . "," . $falcon1['longitude']. $api_key;
$goglApi_url2 =  $goglApi_url . $falcon2['latitude'] . "," . $falcon2['longitude']. $api_key;
#echo $goglApi_url;
#Completed building api URL

#Step 3.2: Calling google reverse geocoding APIs to fetch the location information based on lat and long
FetchData($goglApi_url1,$falcon1['Name'], true);
FetchData($goglApi_url2,$falcon2['Name'], true);


#Step 4: Processing the google api returned data
$gxml1 = simplexml_load_file($falcon1['Name']);
$gxml2 = simplexml_load_file($falcon2['Name']);
$falcon1['Address'] = $gxml1->result[0]->formatted_address;
$falcon2['Address'] = $gxml2->result[0]->formatted_address;

echo("\n Location  of falcon -". $falcon1['Name']. " is :". $falcon1['Address']);
echo("\n Location of falcon -". $falcon2['Name']. " is :". $falcon2['Address']);

#End of main code

# Function that fetches the data from satellitetracking and google api. Used PHP and CURL
function FetchData($siteurl,$targetXML, $isHTTPS)
{
	#targetXML = targetXML.".xml";
	$hcurl = curl_init();
	if ($isHTTPS == true)
	{	#Extra steps for google api to enable https
		curl_setopt($hcurl, CURLOPT_PORT , 443); 
		curl_setopt($hcurl,CURLOPT_SSL_VERIFYPEER, false);
		#echo "using https for :". $siteurl;
	}
	curl_setopt($hcurl, CURLOPT_TIMEOUT, 400);
	curl_setopt ($hcurl, CURLOPT_URL, $siteurl);    
	curl_setopt($hcurl,CURLOPT_RETURNTRANSFER,true);
	curl_setopt ($hcurl, CURLOPT_HEADER,0);
	$resp = curl_exec ($hcurl);
	#echo "RESPONSE:::".trim(trim($resp));
	$fp = fopen($targetXML, 'w');
	fwrite($fp, trim($resp));#TODO: Add response check and publish error 
	fclose($fp);
	if(!curl_errno($siteurl))
	{ 
		echo curl_error($siteurl);
	}
	curl_close ($hcurl);
	#validate http return code and return error if failed to get response
}
#End of code
?>
