<!DOCTYPE html>
<html lang="">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
</head>

<body>
    <?php
     // Get lat and long by address         
        $address = $_GET['location']; 
        $address = str_replace(' ','+',$address);
        $geocode=file_get_contents('https://maps.google.com/maps/api/geocode/json?address='.$address.'&sensor=false');
        
        $output= json_decode($geocode);
        $latitude = $output->results[0]->geometry->location->lat;
        $longitude = $output->results[0]->geometry->location->lng;
    

    // echo "Lat: " . $latitude;
    // echo "    Long: " .  $longitude;
    $address = $longitude . "," . $latitude; 
   
    
    //echo $address;
    
    //displays all info
    //echo"GEOCODE INFO: " .  $geocode;   
?>
        <!-- Form goes here, use the YT API to search for everything except location, use Geolate with GM API-->
        <form>
            <div> Search Term:
                <input type="search" id="q" name="q" placeholder="Enter Search Term"> </div>
            <div> Location:
                <input type="text" id="location" name="location" placeholder="Athens, GA"> </div>
            <div> Location Radius:
                <input type="text" id="locationRadius" name="locationRadius" placeholder="5km"> </div>
            <div> Max Results:
                <input type="number" id="maxResults" name="maxResults" min="1" max="10" step="1" value="1-10"> </div>
            <input type="submit" value="Search"> </form>
        <!----------------------------------------------TO DO-------------------------------------------------------
Take out max results and just have it set to 10.
Take the output thats returned from the youtube api and put it into an array and then covert to a json file
Use googles api and populate a map using the json file created (it will have long and lat of the videos)
Edit markers 
Submit
!----------------------------------------------------------------------------------------------------------->
</body>
</html>