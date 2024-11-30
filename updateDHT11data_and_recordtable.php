<?php
  // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> updateDHT11data_and_recordtable.php
  // PHP code to update and record DHT11 sensor data in table.
  require 'database.php';
  
  //---------------------------------------- Condition to check that POST value is not empty.
  if (!empty($_POST)) {
    //........................................ keep track POST values
    $id = $_POST['id'];
    $temperature = $_POST['temperature'];
    $humidity = $_POST['humidity'];
    $status_read_sensor_dht11 = $_POST['status_read_sensor_dht11'];
    //........................................
    
    //........................................ Get the time and date.
    date_default_timezone_set("America/Mexico_City");
    $tm = date("H:i:s");
    $dt = date("Y-m-d");
    //........................................
    
    //........................................ Updating the data in the table.
    $pdo = Database::connect();
    // PDO::ATTR_ERRMODE: This attribute defines how PDO should report errors. 
    // PDO::ERRMODE_EXCEPTION: This mode tells PDO to throw exceptions whenever a database error occurs.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // This table is used to store DHT11 sensor data updated by ESP32. 
    // This table is operated with the "UPDATE" command, so this table will only contain one row.
    $sql = "UPDATE esp32_table_dht11_update SET temperature = ?, humidity = ?, 
            status_read_sensor_dht11 = ?, time = ?, date = ? WHERE id = ?";
    // This line prepares the SQL query stored in $sql for execution.
    // The ? placeholders allow data to be securely inserted without being directly 
    // embedded in the query, which helps prevent SQL injection attacks.
    $q = $pdo->prepare($sql);
    // This line actually executes the prepared query and replaces each ? placeholder 
    // with values provided in the array.
    // The execute method takes an array as an argument, where each item corresponds to 
    // a ? placeholder in the prepared statement, in the order they appear.
    $q->execute(array($temperature,$humidity,$status_read_sensor_dht11,$tm,$dt,$id));
    Database::disconnect();
    //........................................ 
    
    //........................................ Entering data into a table.
    $id_key;
    $board = $_POST['id'];
    $found_empty = false;
    
    $pdo = Database::connect();
    
    //:::::::: Process to check if "id" is already in use.
    while ($found_empty == false) {
      $id_key = generate_string_id(10);
      // This table is used to store and record DHT11 sensor data updated by ESP32. 
      // This table is operated with the "INSERT" command, so this table will contain many rows.
      // Before saving and recording data in this table, the "id" will be checked first, 
      // to ensure that the "id" that has been created has not been used in the table.
      $sql = 'SELECT * FROM esp32_table_dht11_record WHERE id="' . $id_key . '"';
      $q = $pdo->prepare($sql);
      $q->execute();
      // The fetch method attempts to retrieve a result row from the query.
      // If fetch returns false, it means no row was found with the same id_key, 
      // so the id_key is unique.
      // When this happens, $found_empty is set to true, 
      // which breaks the loop and confirms that $id_key can be used as 
      // the unique identifier for the new record.
      if (!$data = $q->fetch()) {
        $found_empty = true;
      }
    }
    //::::::::
    
    //:::::::: The process of entering data into a table.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // This table is used to store and record DHT11 sensor data updated by ESP32. 
    // This table is operated with the "INSERT" command, so this table will contain many rows.
    // This SQL query is used to insert a new row into the esp32_table_dht11_record table. 
    // The ? placeholders represent values that will be filled in by the execute method.
		$sql = "INSERT INTO esp32_table_dht11_record (id,board,temperature,humidity,
            status_read_sensor_dht11,time,date) values(?, ?, ?, ?, ?, ?, ?)";
    // Prepares the SQL query to be executed. Using prepare allows for the use of 
    // placeholders (?) in the SQL statement, improving security by protecting against SQL injection.
		$q = $pdo->prepare($sql);
    // Executes the prepared statement, replacing each ? placeholder with 
    // the corresponding value in the array.
		$q->execute(array($id_key,$board,$temperature,$humidity,$status_read_sensor_dht11,$tm,$dt));
    //::::::::
    
    Database::disconnect();
    //........................................ 
  }
  //---------------------------------------- 
  
  //---------------------------------------- Function to create "id" based on numbers and characters.
  function generate_string_id($strength = 16) {
    // If not provided, it defaults to 16 characters.
    $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    // stores the length of $permitted_chars, which is 62 
    // (10 digits + 26 lowercase + 26 uppercase characters).
    $input_length = strlen($permitted_chars);
    $random_string = '';
    for($i = 0; $i < $strength; $i++) {
      // generates a random integer between 0 and input_length - 1 (0 to 61), 
      // which is used as an index to select a random character from $permitted_chars.
      $random_character = $permitted_chars[mt_rand(0, $input_length - 1)];
      $random_string .= $random_character;
    }
    return $random_string;
  }
  //---------------------------------------- 
