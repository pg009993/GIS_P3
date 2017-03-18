<?php
     // Get lat and long by address         
        $address = $_GET['location']; 
        $address = str_replace(' ','+',$address);
        $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$address.'&sensor=false');
        
        $output= json_decode($geocode);
        $latitude = $output->results[0]->geometry->location->lat;
        $longitude = $output->results[0]->geometry->location->lng;
    

    $location = $longitude . "," . $latitude; 
    //other variables from the form
    $q = $_GET['q'];
    $locationRadius = $_GET['locationRadius'];
    $maxResults = '10';
    

if (isset($q != null)){
  /*
   * Set $DEVELOPER_KEY to the "API key" value from the "Access" tab of the
  * {{ Google Cloud Console }} <{{ https://cloud.google.com/console }}>
  * Please ensure that you have enabled the YouTube Data API for your project.
  */
  $DEVELOPER_KEY = 'AIzaSyCpkNDWxmHvj7Nk6Fev3ZJ0XljsO6X69eY';

  $client = new Google_Client();
  $client->setDeveloperKey($DEVELOPER_KEY);

  // Define an object that will be used to make all API requests.
  $youtube = new Google_Service_YouTube($client);

  try {
    // Call the search.list method to retrieve results matching the specified
    // query term.
    $searchResponse = $youtube->search->listSearch('id,snippet', array(
        'type' => 'video',
        'q' => $q,
        'location' =>  $location,
        'locationRadius' =>  $locationRadius,
        'maxResults' => $maxresults,
    ));

    $videoResults = array();
    # Merge video ids
    foreach ($searchResponse['items'] as $searchResult) {
      array_push($videoResults, $searchResult['id']['videoId']);
    }
    $videoIds = join(',', $videoResults);

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

      
    //  echo $videos; 
      
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
    <html lang="">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title></title>
    </head>

    <body>
        <!-- Form goes here, use the YT API to search for everything except location, use Geolate with GM API-->
        <form>
            <div> Search Term:
                <input type="search" id="q" name="q" placeholder="Enter Search Term"> </div>
            <div> Location:
                <input type="text" id="location" name="location" placeholder="Athens, GA"> </div>
            <div> Location Radius:
                <input type="text" id="locationRadius" name="locationRadius" placeholder="5km"> </div>
            <!-- <div> Max Results:
                <input type="number" id="maxResults" name="maxResults" min="1" max="10" step="1" value="1-10"> </div> -->
            <input type="submit" value="Search"> </form>
        <!----------------------------------------------TO DO-------------------------------------------------------
[done] Take out max results and just have it set to 10.
Take the output thats returned from the youtube api and put it into an array and then covert to a json file
Use googles api and populate a map using the json file created (it will have long and lat of the videos)
Edit markers 
Submit
!----------------------------------------------------------------------------------------------------------->
        <?=$htmlBody?>
    </body>

    </html>