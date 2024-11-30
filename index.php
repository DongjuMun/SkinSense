<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkinSense</title>
    <link rel="stylesheet" href="main.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@200..700&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
</head>
<body>
    <?php
        // Include the scripts to fetch and save UV index data
        require_once('record_and_update_uv_data.php'); // Script to update the latest UV index data
    ?>
    <div class="container">
        <header>
            <div class="menu-icon" onclick="toggleMenu()">☰</div>
            <nav id="menu">
                <ul>
                    <li><a href="index.html" style="text-decoration: none; color: black;">Home</a></li>
                    <li><a href="info.html" style="text-decoration: none; color: black;">Info</a></li>
                    <li><a href="products.html" style="text-decoration: none; color: black;">Products</a></li>
                </ul>
            </nav>
        </header>
        <main>
            <h1>SkinSense</h1>
        </main>
    </div>

    <div class="whole_info">
        <div class="data">
            <div class="data-set-items">
                <div class="data-item">Temperature: <span id="ESP32_01_Temp"></span> &deg;C</div>
                <div class="data-item">Humidity: <span id="ESP32_01_Humd"></span> &percnt;</div>
                <div class="data-item" id="uv">
                    <?php
                        require_once('database.php');

                        try {
                            $pdo = Database::connect();

                            // Fetch the most recent UV index from uv_index_table_update
                            $sqlUpdate = "SELECT uv_index FROM uv_index_table_update";
                            $stmt = $pdo->query($sqlUpdate);
                            $latest = $stmt->fetch(PDO::FETCH_ASSOC);

                            if ($latest) {
                                $uvIndex = (float) $latest['uv_index']; // Cast to float
                                echo "UV Index: " . htmlspecialchars($uvIndex);
                    
                                // Embed the UV index in a JavaScript variable as a float
                                echo "<script>const uv = $uvIndex;</script>";
                            } else {
                                echo "No data available.";
                                echo "<script>const uv = null;</script>";
                            }
                        } catch (PDOException $e) {
                            echo "Error fetching data: " . htmlspecialchars($e->getMessage());
                            echo "<script>const uv = null;</script>";
                        } finally {
                            Database::disconnect();
                        }
                    ?>
                </div>
            </div>
            <div class="data-set-t-n-d">
                <div class="data-time-and-date">Last Updated (DHT11): <span id="ESP32_01_LTRD"></span> </div>
                <div class="data-time-and-date">
                    <?php
                        require_once('database.php');

                        try {
                            $pdo = Database::connect();

                            // Fetch the most recent UV index from uv_index_table_update
                            $sqlUpdate = "SELECT time, date FROM uv_index_table_update ORDER BY date DESC, time DESC LIMIT 1";
                            $stmt = $pdo->query($sqlUpdate);
                            $latest = $stmt->fetch(PDO::FETCH_ASSOC);

                            if ($latest) {
                                $formattedDate = date("d-m-Y", strtotime($latest['date']));
                                echo "Last Updated (UVI): " 
                                    . htmlspecialchars($formattedDate) . " at " . htmlspecialchars($latest['time']);

                            } else {
                                echo "No data available.";
                            }
                        } catch (PDOException $e) {
                            echo "Error fetching data: " . htmlspecialchars($e->getMessage());
                        } finally {
                            Database::disconnect();
                        }
                    ?>
                </div>
            </div>
        </div>

        <div class="skin-type-selector">
            <label for="skinType">Selecciona tu tipo de piel:</label>
            <select id="skinType" name="skinType" onchange="mostrarProductos()">
                <option value="nada">...</option>
                <option value="grasa">Oily</option>
                <option value="media">Mixed</option>
                <option value="seca">Dry</option>
            </select>
        </div>
        <div class="products" id="productsContainer"></div>
    </div>

    <script>
        function toggleMenu() {
            const menu = document.getElementById('menu');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }

         //------------------------------------------------------------
        document.getElementById("ESP32_01_Temp").innerHTML = "NN"; 
        document.getElementById("ESP32_01_Humd").innerHTML = "NN";
        document.getElementById("ESP32_01_LTRD").innerHTML = "NN";
        //------------------------------------------------------------
        
        Get_Data("esp32_01");
        
        setInterval(myTimer, 5000);
        
        //------------------------------------------------------------
        function myTimer() {
            Get_Data("esp32_01");
        }
        //------------------------------------------------------------
        
        //------------------------------------------------------------
        function Get_Data(id) {
            // If everything goes well, it assigns a name to the object 
            // that will be used for "talking with the server".
            if (window.XMLHttpRequest) {
            // code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
            // If this throws an error, it means that the user has 
            // an older browser (IE 5 or IE6), so instead it tries 
            // to create an ActiveXObject which is essentially the same 
            // but works only for these older browsers.  
            } else {
            // code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            }
            // The onreadystatechange property specifies a function to be 
            // executed every time the status of the XMLHttpRequest object changes
            xmlhttp.onreadystatechange = function() {
                // readyState 4: request finished and response is ready 
                // status 200: success
                if (this.readyState == 4 && this.status == 200) {
                    const myObj = JSON.parse(this.responseText);
                    if (myObj.id == "esp32_01") {
                    document.getElementById("ESP32_01_Temp").innerHTML = myObj.temperature;
                    document.getElementById("ESP32_01_Humd").innerHTML = myObj.humidity;
                    document.getElementById("ESP32_01_LTRD").innerHTML = myObj.ls_date + " at " + myObj.ls_time;
                    }
                }
            };
            xmlhttp.open("POST","getdata.php",true);
            xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xmlhttp.send("id="+id);
		}
    
        function mostrarProductos() {
            const skinType = document.getElementById("skinType").value;
    
            if (skinType === "nada") {
                alert("Por favor, selecciona un tipo de piel.");
                return;
            }
    
            //uv variable is already defined, embedded in the php code
            const temperature = parseFloat(document.getElementById("ESP32_01_Temp").innerHTML);
            const humidity = parseFloat(document.getElementById("ESP32_01_Humd").innerHTML);

            let sunscreen = {};
            let moisturizer = {};
    
            if (uv > 6) {
                switch (skinType) {
                    case "grasa":
                        sunscreen = { name: "Beauty Of Joseon - Barra Solar Mate", 
                        img: "PhotosIOT/01-S-6ABOVE-OILY.png",
                        desc: "<ul><li>SPF 50</li><li>PAA ++++</li><li>Oily skin</li></ul>" };
                        break;
                    case "media":
                        sunscreen = { name: "ISDIN eryfotona ageless ultralight emulsion spf 50", 
                        img: "PhotosIOT/01-S-6ABOVE-MIXED.png", 
                        desc: "<ul><li>SPF 50</li><li>PA +++</li><li>For all skin types, specialized in mixed</li></ul>" };
                        break;
                    case "seca":
                        sunscreen = { name: "Eucerin Sun Advanced Hydration SPF 50 Sunscreen Lotion",
                        img: "PhotosIOT/01-S-6ABOVE-DRY.png", 
                        desc: "<ul><li>SPF 50</li><li>PA +++</li><li>Dry Skin</li><li>Lightweight</li></ul>"};
                        break;
                }
            } else if (uv < 6) {
                switch (skinType) {
                    case "grasa":
                        sunscreen = { name: "Bioré UV Aqua Rich SPF 50 Moisturizing Sunscreen", 
                        img: "PhotosIOT/06-S-6BELOW-OILY.png",
                        desc: "<ul><li>SPF 50</li><li>PA ++++</li><li>Oily Skin</li><li>Oil Free</li><li>Lightweight</li></ul>" };
                        break;
                    case "media":
                        sunscreen = { name: "Le Prunier plumscreen spf 3", 
                        img: "PhotosIOT/06-S-6BELOW-MIXED.png", 
                        desc: "<ul><li>PA +++</li><li>SPF 31</li><li>Mixed Skin</li></ul>" };
                        break;
                    case "seca":
                        sunscreen = { name: "CeraVe Hydrating Sheer Sunscreen SPF 30",
                        img: "PhotosIOT/06-S-6BELOW-DRY.png", 
                        desc: "<ul><li>SPF 30+</li><li>PA +++</li><li>Dry skin and sensitive skin</li><li>Light weight</li></ul>"};
                        break;
                }
            }
    
            if (temperature < 15 || humidity < 30) {
                switch (skinType) {
                    case "grasa":
                        moisturizer = { name: "Beauty of Joseon - Gel de Agua Red Bean", 
                        img: "PhotosIOT/02-M-HOTHUMID-OILY.png",
                        desc: "<ul><li>Oily Skin</li><li>Water Gel Based</li><li>Hydrates the Skin</li><li>Light Texture</li></ul>"};
                        break;
                    case "media":
                        moisturizer = { name: "Centella Calming Gel Cream",
                        img: "PhotosIOT/02-M-HOTHUMID-MIXED.png",
                        desc: "<ul><li>Sensitive Skin</li><li>Calming Formula</li><li>Mixed Skin</li><li>Gel Based</li></ul>" };
                        break;
                    case "seca":
                        moisturizer = { name: "DRUNK ELEPHANT b-hydra intensive hydration serum",
                        img: "PhotosIOT/02-M-HOTHUMID-DRY.png", 
                        desc: "<ul><li>Hydrates</li><li>Brightens</li><li>Improves skin texture</li><li>Fixes Dryness</li><li>Water base</li></ul>" };
                        break;
                }
            } else if (temperature >= 15 && temperature <= 25 && humidity >= 30 && humidity <= 60) {
                switch (skinType) {
                    case "grasa":
                        moisturizer = { name: "Cetaphil daily oil-free hydrating lotion",
                        img: "PhotosIOT/04-M-COLDHUMID-OILY.png",
                        desc: "<ul><li>Oil Free</li><li>Oily Skin</li><li>Hypoallergenic</li><li>For Sensitive Skin</li><li>Paraben Free</li></ul>"};
                        break;
                    case "media":
                        moisturizer = { name: "Belif The True Cream Aqua Bomb",
                        img: "PhotosIOT/04-M-COLDHUMID-MIXED.png",
                        desc: "<ul><li>For all skin types specialized in mixed</li><li>Lightweight formula</li><li>Fixes Dryness</li><li>Gel Based Cream</li><li>Hydrates</li></ul>"};
                        break;
                    case "seca":
                        moisturizer = { name: "Intense Hydration Day Lotion", 
                        img: "PhotosIOT/03-M-HOTDRY-DRY.png", 
                        desc: "<ul><li>Dry Skin</li><li>Hydrates</li><li>Non-greasy</li><li>Cream Base</li></ul>" };
                        break;
                }
            } else if (temperature > 25 && humidity > 60) {
                switch (skinType) {
                    case "grasa":
                        moisturizer = { name: "Neutrogena hydro boost water gel", 
                        img: "PhotosIOT/05-M-COLDDRY-OILY.png", 
                        desc: "<ul><li>Light Texture</li><li>Water Based</li><li>Oily Skin</li><li>Hyaluronic Acid</li><li>Hypoallergenic</li><li>Oil Free</li></ul>" };
                        break;
                    case "media":
                        moisturizer = { name: "First Aid Beauty Ultra Repair Cream-6 oz",
                        img: "PhotosIOT/05-M-COLDDRY-MIXED.png", 
                        desc: "<ul><li>Sensitive Skin</li><li>Hydrates</li><li>Soothes skin</li><li>Rich in Vitamins</li><li>Cream Base</li></ul>" };
                        break;
                    case "seca":
                        moisturizer = { name: "Kiehl’s Ultra Facial Cream", 
                        img: "PhotosIOT/05-M-COLDDRY-DRY.png", 
                        desc: "Use a heavy-duty moisturizer for cold temperatures." };
                        break;
                }
            }
    
            const productsContainer = document.getElementById("productsContainer");
            productsContainer.innerHTML = `
                <div class="product-pair">
                    <div class="product-pair-image">
                        <img src="${sunscreen.img}" alt="${sunscreen.name}">
                    </div>
                    <div class="product-info">
                        <h2>${sunscreen.name}</h2>
                        <p>${sunscreen.desc}</p>
                    </div>
                </div>
                <div class="product-pair">
                    <div class="product-pair-image">
                        <img src="${moisturizer.img}" alt="${moisturizer.name}">
                    </div>
                    <div class="product-info">
                        <h2>${moisturizer.name}</h2>
                        <p>${moisturizer.desc}</p>
                    </div>
                </div>
            `;
        }
    </script>    
</body>
</html>





