<?php 

  function translate($text, $source_lang, $target_lang, $api_key) 
{
        $url = "https://api.weglot.com/translate";
        $data = [
            "text" => $text,
            "source" => $source_lang,
            "target" => $target_lang,
        ];
        $options = [
            "http" => [
                "header" => "Content-Type: application/json\r\nAuthorization: Bearer " . $api_key,
                "method" => "POST",
                "content" => json_encode($data),
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === FALSE) {
            return null;
        }
        $response = json_decode($result, true);
        return $response['translations'][0]['translatedText'];
    }

      function getEnglishToArabic($term) {
        // Set API endpoint and your API key
        $apiKey = 'wg_42c9daf242af8316a7b7d92e5a2aa0e55';
        $apiEndpoint = 'https://api.weglot.com/translate?api_key=' . $apiKey;
    
        // Prepare the JSON payload
        $data = [
            "l_to" => "ar",
            "l_from" => "en",
            "request_url" => "https://www.avenzur.com/",
            "words" => [
                ["w" => "$term", "t" => 1],
                ["w" => "Panadol", "t" => 1],
                ["w" => "Panadol extra", "t" => 1],
                ["w" => "Sulphad", "t" => 1]
            ]
        ];
    
        // Convert the payload to JSON format
        $jsonData = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
        // Initialize cURL session
        $ch = curl_init();
    
        // Set cURL options
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiEndpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ],
        ]);
    
        // Execute the POST request
        $response = curl_exec($ch);
    
        // Check for errors
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
            curl_close($ch);
            return null;
        } else {
            // Decode the response
            $responseData = json_decode($response, true);
            curl_close($ch);
    
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo 'JSON decode error: ' . json_last_error_msg();
                return "JSON decode error.";
            }
    
            // Debug: Print the decoded response
             var_dump($responseData['to_words'], $term);
    
            if (isset($responseData['to_words']) && is_array($responseData['to_words'])) {
                return $responseData['to_words'];
            } else {
                // Handle the case where the response doesn't have the expected data
                echo "Unexpected response format.";
                return "Translation error or unexpected response format.";
            }
        }
    }

      function update_product(){ 

        getEnglishToArabic('This is test'); exit; 


            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "pharma_with_data"; 
            $api_key = "wg_42c9daf242af8316a7b7d92e5a2aa0e55"; 
            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
         
            $sql = "SELECT id, name, product_details FROM sma_products  ORDER BY id ASC LIMIT 10";
            $result = $conn->query($sql);

            $products = [];

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $products[] = $row;
                }
            } 
        //    echo '<pre>'; print_r($products); exit; 
            foreach ($products as &$product) {
                getEnglishToArabic($product['name']); 
              //   getEnglishToArabic($product['name'], strip_tags($product['product_details'])); 
              //  $product['name_ar'] = translate($product['name'], "en", "ar", $api_key);
              //  $product['product_details_ar'] = translate(strip_tags($product['product_details']), "en", "ar", $api_key);
            }

            exit; 
         //  echo '<pre>'; print_r($products); exit; 
            foreach ($products as $product) {
                $id = $product['id'];
                $name_ar = $conn->real_escape_string($product['name_ar']);
                $product_details_ar = $conn->real_escape_string($product['product_details_ar']);

                $sql = "UPDATE sma_products SET name_ar='$name_ar', product_details_ar='$product_details_ar' WHERE id=$id";
                if (!$conn->query($sql)) {
                    echo "Error updating record: " . $conn->error;
                }
            }
         
            $conn->close();
        }

       update_product();
