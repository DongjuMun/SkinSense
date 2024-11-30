<?php
    // >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> getdata.php
    // PHP file to get DHT11 sensor data stored in table in database to display on "index.php" page.
  include 'database.php';
  
  //---------------------------------------- Condition to check that POST value is not empty.
  if (!empty($_POST)) {
    // keep track post values
    $id = $_POST['id'];
    
    $myObj = (object)array(); //to convert it later in JSON
    
    //........................................ 
    $pdo = Database::connect(); //get the PHP Database Object
    // This table is used to store DHT11 sensor data updated by ESP32. 
    // To store data, this table is operated with the "UPDATE" command, so this table contains only one row.
    $sql = 'SELECT * FROM esp32_table_dht11_update WHERE id="' . $id . '"';
    foreach ($pdo->query($sql) as $row) {
      $date = date_create($row['date']);
      $dateFormat = date_format($date,"d-m-Y");
      
      $myObj->id = $row['id'];
      $myObj->temperature = $row['temperature'];
      $myObj->humidity = $row['humidity'];
      $myObj->status_read_sensor_dht11 = $row['status_read_sensor_dht11'];
      $myObj->ls_time = $row['time'];
      $myObj->ls_date = $dateFormat;
      
      $myJSON = json_encode($myObj);
      
      echo $myJSON; //outputs the content of JSON to the client/browser
    }
    Database::disconnect();
    //........................................ 
  }
  //---------------------------------------- 
