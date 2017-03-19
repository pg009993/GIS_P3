<?php

/**
 * This sample lists videos that are associated with a particular keyword and are in the radius of
 *   particular geographic coordinates by:
 *
 * 1. Searching videos with "youtube.search.list" method and setting "type", "q", "location" and
 *   "locationRadius" parameters.
 * 2. Retrieving location details for each video with "youtube.videos.list" method and setting
 *   "id" parameter to comma separated list of video IDs in search result.
 *
 * @author Ibrahim Ulukaya
 */

/**
 * Library Requirements
 *
 * 1. Install composer (https://getcomposer.org)
 * 2. On the command line, change to this directory (api-samples/php)
 * 3. Require the google/apiclient library
 *    $ composer require google/apiclient:~2.0
 */
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
  throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
}

require_once __DIR__ . '/vendor/autoload.php';

$htmlBody = <<<END
<form method="GET">
  <div>
    Search Term: <input type="search" id="q" name="q" placeholder="Enter Search Term">
  </div>
  <div>
    Location: <input type="text" id="location" name="location" placeholder="Athens, GA">
  </div>
  <div>
    Location Radius: <input type="text" id="locationRadius" name="locationRadius" placeholder="5km">
  </div>
  <input type="submit" value="Search">
</form>
END;

// This code executes if the user enters a search query in the form
// and submits the form. Otherwise, the page displays the form above.
if (isset($_GET['q'])) {
  /*
   * Set $DEVELOPER_KEY to the "API key" value from the "Access" tab of the
  * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
  * Please ensure that you have enabled the YouTube Data API for your project.
  */
  $DEVELOPER_KEY = 'AIzaSyCpkNDWxmHvj7Nk6Fev3ZJ0XljsO6X69eY';
    
    
    //convert to long and lat via google maps api
    $address = $_GET['location']; 
    $address = str_replace(' ','+',$address);
    $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$address.'&sensor=false');
    
    $output= json_decode($geocode);
    $latitude = $output->results[0]->geometry->location->lat;
    $longitude = $output->results[0]->geometry->location->lng;
   
    //variables from the form
    $maxResults = '10';
    $location =  $latitude . "," . $longitude; 
    $locationRadius = $_GET['locationRadius'];

  $client = new Google_Client();
  $client->setDeveloperKey($DEVELOPER_KEY);

  // Define an object that will be used to make all API requests.
  $youtube = new Google_Service_YouTube($client);

  try {
    // Call the search.list method to retrieve results matching the specified
    // query term.
    $searchResponse = $youtube->search->listSearch('id,snippet', array(
        'type' => 'video',
        'q' => $_GET['q'],
        'location' =>  $location,
        'locationRadius' =>  $_GET['locationRadius'],
        'maxResults' => $maxResults,
    ));

    $videoResults = array();
    # Merge video ids
    foreach ($searchResponse['items'] as $searchResult) {
      array_push($videoResults, $searchResult['id']['videoId']);
    }
      
    //creating another json for video ids  
    $videoIds = join(',', $videoResults);
    //var_dump($searchResponse);
      
    # Call the videos.list method to retrieve location details for each video.
    $videosResponse = $youtube->videos->listVideos('snippet, recordingDetails', array(
    'id' => $videoIds,
    ));

    $videos = '';
    // Display the list of matching videos.
    foreach ($videosResponse['items'] as $videoResult) {
      $videos .= sprintf('<li>%s (%s,%s)</li>',
          $videoResult['snippet']['title'],
          $videoResult['recordingDetails']['location']['latitude'],
          $videoResult['recordingDetails']['location']['longitude']);
    }
      
  
    $gminfo = array();
    foreach ($videosResponse['items'] as $videoResult) {     
        $a = array( 'name' => $videoResult['snippet']['title'], 'lat' => $videoResult['recordingDetails']['location']['latitude'], 'lng' => $videoResult['recordingDetails']['location']['longitude'],'link'=> $videoResult['id']);
        $gminfo[] = $a;
        
    }
      
      //convert to JSON format
      $gmjson = json_encode($gminfo); 
       
      //empty the js file, clears out previous search results
      $empty = ''; 
      file_put_contents('googlemaps.js', $empty);
      
      //write new stuff to file
     file_put_contents('googlemaps.js', "var googlemaps = ", FILE_APPEND);
     file_put_contents('googlemaps.js', json_encode($gminfo), FILE_APPEND);
     file_put_contents('googlemaps.js', ";", FILE_APPEND);


    $htmlBody .= <<<END
    <h3>Results</h3>
    <ul>$videos</ul>
END;
  } catch (Google_Service_Exception $e) {
    $htmlBody .= sprintf('<p>A service error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
  } catch (Google_Exception $e) {
    $htmlBody .= sprintf('<p>An client error occurred: <code>%s</code></p>',
        htmlspecialchars($e->getMessage()));
  }
}
?>


<!DOCTYPE html>
<html>
  <head>
    <style>
       #map {
        height: 400px;
        width: 100%;
       }
    </style>
  </head>
  <body>
    <h3>Geolocate YouTube Videos</h3>
    <div id="map"></div>
      <script src="googlemaps.js"></script>
      <script src="videoIds.js"></script>
    <script>
        
      function initMap() {
        var uluru = {lat: 33.95,lng: -83.38333};
        var map = new google.maps.Map(document.getElementById('map'), {
          zoom: 4,
          center: uluru,
        });
          var markers =[];
          //populate map with videos , add information to the markers, play youtube videos
          for(x in googlemaps){          
            /*
            TO DO: 
            create a marker, within the marker just add 
            https://www.youtube.com/watch?v= + VIDEO ID , which are store in $videoResults
            done.            
            helpful link: https://developers.google.com/maps/documentation/javascript/infowindows     
            
            issue: sometime not all results are pinned to the map...? 
            */ 
                   
          var pos = {lat: googlemaps[x].lat ,lng: googlemaps[x].lng};
         //place marker on map
           
            markers[x] = new google.maps.Marker({
          animation: google.maps.Animation.DROP,
          title: googlemaps[x].name, 
          position: pos,
          map: map,
          url: "https://www.youtube.com/watch?v=" + googlemaps[x].link,
            id: x,
        });
        // markers[x].index = x;
        //markers.push(marker); 
        //console.log(googlemaps[x].link);
                 google.maps.event.addListener(markers[x], 'click', function() {
                    window.location.href = markers[this.id].url;
                    
            });
          }
      }
      

  
    
      
        
        
        
    
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCpkNDWxmHvj7Nk6Fev3ZJ0XljsO6X69eY&callback=initMap">
    </script>
      <?=$htmlBody?>
    
      </body>
</html>

