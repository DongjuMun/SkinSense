<?php
    require_once('database.php'); // Include the Database class

    // Function to generate a random string ID
    function generate_string_id($strength = 16) {
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $input_length = strlen($permitted_chars);
        $random_string = '';
        for ($i = 0; $i < $strength; $i++) {
            $random_character = $permitted_chars[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
        return $random_string;
    }

    // Fetch UV index from OpenWeatherMap API
    $apiKey = ""; //your api key to One Call API 3.0
    $latitude = ""; //Latitude of your city
    $longitude = "";//Logitud of your city
    $apiUrl = "https://api.openweathermap.org/data/3.0/onecall?lat=$latitude&lon=$longitude&exclude=minutely,hourly,daily,alerts&appid=$apiKey";

    $response = file_get_contents($apiUrl);
    if ($response === FALSE) {
        die("Error fetching UV index data.");
    }
    $data = json_decode($response, true);
    $uvIndex = $data['current']['uvi'];

    // Get the current date and time
    date_default_timezone_set("America/Mexico_City");
    $currentDate = date("Y-m-d");
    $currentTime = date("H:i:s");
    $apiName = "OpenWeatherMap";

    try {
        $pdo = Database::connect(); // Use Database class to connect

        // Update the uv_index_table_update table
        $sqlUpdate = "UPDATE uv_index_table_update 
                    SET uv_index = ?, time = ?, date = ? 
                    WHERE id = ?";
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute(array($uvIndex,$currentTime,$currentDate,$apiName));


        // Insert the same data into uv_index_table_record
        $randomID = generate_string_id();
        
        $sqlRecord = "INSERT INTO uv_index_table_record (id, uv_index, time, date, api) 
                    VALUES (?, ?, ?, ?, ?)";
        $stmtRecord = $pdo->prepare($sqlRecord);
        $stmtRecord->execute(array($randomID,$uvIndex,$currentTime,$currentDate,$apiName));
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    } finally {
        Database::disconnect(); // Disconnect after use
    }
