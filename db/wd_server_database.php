<?php 
if (!class_exists('WD_Server_Database')) {
    class WD_Server_Database{
        protected $server     = 'wp-demo.wpdance.com';
        protected $user       = 'wpdance_laparis';
        protected $pass       = 'ncz8731nv99qmz1';
        protected $db         = 'laparis_wpdance_demo_update_2018';
        protected $table_name = 'wd_activated_customer_list';
        protected $method     = 'database'; //txt_file or database
        protected $txt_file   =  'result.txt';

        /**
         * Refers to a single instance of this class.
         */
        private static $instance = null;

        public static function get_instance() {
            if ( null == self::$instance ) {
                self::$instance = new self();
            }

            return self::$instance;
        }
        
        public function __construct(){
            $this->create_table_sql();
            //$this->insert('abcd', '123');
            //$this->checker('abcd', '123');
            //$this->update('abcd', 'xxxx');
            //$this->delete('abcd');
            //$this->get_customer_info_by_purchase_code();
        }
        protected function connect() {
            if ($this->method == 'database') {
                $connect = new mysqli($this->server, $this->user, $this->pass, $this->db);
            }else{
                $connect = fopen($this->txt_file, 'w+');
            }
            return $connect;

        }

        public function create_table_sql() {
            $handle = $this->connect();
            if ($handle) {
                //$query            = "SELECT * FROM $this->table_name";
                $table_exist_sql    = "DESCRIBE $this->table_name";
                $dop_table_sql      = "DROP TABLE $this->table_name";
                $result             = $handle->query($table_exist_sql);
                if (!$result) {
                    $create_table_sql = "CREATE TABLE $this->table_name (
                        purchase_code VARCHAR(50) PRIMARY KEY, 
                        url VARCHAR(250) NOT NULL,
                        update_date VARCHAR(50) NOT NULL,
                    )";

                    $handle->query($create_table_sql);
                    /*if ($handle->query($create_table_sql) === TRUE) {
                        echo "Table MyGuests created successfully";
                    } else {
                        echo "Error creating table: " . $handle->error. ' '. $create_table_sql;
                    }*/
                }
                $handle->close();
            }
        }

        public function get_txt_file_content() {
            return unserialize(file_get_contents($this->txt_file));
        }
        
        public function encode_content($content) {
            return serialize($content);
        }

        public function decode_content($content) {
            return unserialize($content);
        }

        public function get_purchase_code_info($purchase_code = ''){
            if (!$purchase_code) return false;
            $username = 'tvlgiao';
            // Set API Key  
            $api_key = '7qplew3cz4di546ozrs8dyzyzbpjruza';
            
            // Open cURL channel
            $ch = curl_init();
             
            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, "http://marketplace.envato.com/api/edge/". $username ."/". $api_key ."/verify-purchase:". $purchase_code .".json");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            //Set the user agent
            $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
            curl_setopt($ch, CURLOPT_USERAGENT, $agent);    

            // Decode returned JSON
            $output = json_decode(curl_exec($ch), true);
            // Close Channel
            curl_close($ch);
            // Return output
            //printf(json_encode($output));
            return $output;
        }

        public function insert($purchase_code, $url) {
            $handle         = $this->connect();
            if ($handle) {
                $update_date    = time();
                if ($this->method == 'database') {
                    $sql        = "INSERT INTO $this->table_name (purchase_code, url, update_date) VALUES ('$purchase_code', '$url', '$update_date')";
                    $handle->query($sql);
                    //echo ($handle->query($sql) === TRUE) ? "New record created successfully" : "Error: " . $sql . "<br>" . $handle->error;
                    $handle->close();
                }else{
                    $data           = $this->get_txt_file_content();
                    $data[$purchase_code] = array(
                        'url'           => $url,
                        'update_date'   => $update_date,
                    );
                    //printf(json_encode($data));
                    $data = $this->encode_content($data);
                    fwrite($handle, $data);
                    fclose($handle);
                }
            }
        }

        public function update($purchase_code, $url) {
            $handle         = $this->connect();
            if ($handle) {
                $update_date    = time();
                if ($this->method == 'database') {
                    $sql            = "UPDATE $this->table_name SET url = '$url', update_date = '$update_date' WHERE purchase_code = '$purchase_code'";
                    $handle->query($sql);
                    //echo ($handle->query($sql) === TRUE) ? "Update record successfully" : "Error: " . $sql . "<br>" . $handle->error;
                    $handle->close();
                }else{
                    $data                                   = $this->get_txt_file_content();
                    $data[$purchase_code]['url']            = $url;
                    $data[$purchase_code]['update_date']    = $update_date;
                    $data = $this->encode_content($data);

                    fwrite($handle, $data);
                    fclose($handle);
                }
            }
        }
        
        public function delete($url) {
            $handle         = $this->connect();
            if ($handle) {
                $update_date    = time();
                if ($this->method == 'database') {
                    $sql            = "DELETE FROM $this->table_name WHERE url = '$url' LIMIT 1";
                    $handle->query($sql);
                    //echo ($handle->query($sql) === TRUE) ? "Delete record successfully" : "Error: " . $sql . "<br>" . $handle->error;
                    $handle->close();
                }else{
                    $data           = $this->get_txt_file_content();
                    if (count($data) > 0) {
                        foreach ($data as $key => $value) {
                            if ($value['url'] == $url) {
                                unset($data[$key]);
                            }
                        }
                    }
                    $data = $this->encode_content($data);

                    fwrite($handle, $data);
                    fclose($handle);
                }
            }
        }

        public function get_connection_status() {
            $handle = $this->connect();
            if ($handle->client_info != '') {
                $status = true;
                $handle->close();
            }else{
                $status = false;
            }
            return $status;
        }

        public function get_customer_info_by_purchase_code($purchase_code = '') {
            $handle = $this->connect();
            $array_return = array();
             if ($handle) {
                if ($this->method == 'database') {
                    $sql    = "SELECT * FROM $this->table_name WHERE purchase_code = '$purchase_code'";
                    if ($result = $handle->query($sql)){
                        if ($result->num_rows > 0){
                            $purchase_info  = $this->get_purchase_code_info($purchase_code)['verify-purchase'];
                            while ($row = $result->fetch_object()){
                                $array_return[$row->purchase_code]['url']              = $row->url;
                                $array_return[$row->purchase_code]['update_date']      = date('d/m/Y', $row->update_date);
                                $array_return[$row->purchase_code]['purchase_info']    = $purchase_info;
                            }
                        }else{
                            $array_return['error'] = "No results to display!";
                        }
                    }else{
                        $array_return['error'] = "Error: " . $handle->error;
                    }
                    $handle->close();
                }else{
                    $data   = $this->get_txt_file_content();
                    if (count($data) > 0){
                        foreach ($data as $key => $value) {
                            if ($key == $purchase_code) {
                                 $array_return[]['purchase_code']    = $key;
                                $array_return[]['url']              = $value['url'];
                                $array_return[]['update_date']      = date('d/m/Y', $value['update_date']);
                                $array_return[]['purchase_info']    = $value['purchase_info'];
                            }
                        }
                    }else{
                        $array_return['error'] = "No results to display!";
                    }
                }
            }
            return $array_return;
        }

        public function checker_purchase_code($purchase_code) {
            $handle = $this->connect();
            $result = false;
            if ($handle) {
                $count  = 0;
                if ($this->method == 'database') {
                    $sql    = "SELECT * FROM $this->table_name where purchase_code = '$purchase_code'";
                    if ($data = $handle->query($sql)){
                        if ($data->num_rows > 0){
                           $result = true;
                        }
                    }
                    $handle->close();
                }else{
                    $data   = $this->get_txt_file_content();
                    if (count($data) > 0) {
                        foreach ($data as $key => $value) {
                            if ($key == $purchase_code) {
                                $count ++;
                            }
                        }
                        $result = ($count > 0) ? true : false;
                    }
                    $data = $this->encode_content($data);

                    fwrite($handle, $data);
                    fclose($handle);
                }
            }
            return $result;
        }

        public function checker_url($url) {
            $handle = $this->connect();
            $result = false;
            if ($handle) {
                $count  = 0;
                if ($this->method == 'database') {
                    $sql    = "SELECT * FROM $this->table_name where url = '$url'";
                    if ($data = $handle->query($sql)){
                        if ($data->num_rows > 0){
                           $result = true;
                        }
                    }
                    $handle->close();
                }else{
                    $data   = $this->get_txt_file_content();
                    if (count($data) > 0) {
                        foreach ($data as $key => $value) {
                            if ($value[$value]['url'] == $url) {
                                $count ++;
                            }
                        }
                        $result = ($count > 0) ? true : false;
                    }
                    $data = $this->encode_content($data);

                    fwrite($handle, $data);
                    fclose($handle);
                }
            }
            return $result;
        }

        public function checker_purchase_url($purchase_code, $url) {
            $handle = $this->connect();
            $result = false;
            if ($handle) {
                if ($this->method == 'database') {
                    $sql    = "SELECT * FROM $this->table_name where purchase_code = '$purchase_code' and url = '$url'";
                    if ($data = $handle->query($sql)){
                        if ($data->num_rows > 0){
                           $result = true;
                        }
                    }
                    $handle->close();
                }else{
                    $data   = $this->get_txt_file_content();
                    if (count($data) > 0) {
                        foreach ($data as $key => $value) {
                            if ($key == $purchase_code && $value[$value]['url'] == $url) {
                                $count ++;
                            }
                        }
                        $result = ($count > 0) ? true : false;
                    }
                    $data = $this->encode_content($data);

                    fwrite($handle, $data);
                    fclose($handle);
                }
            }
            return $result;
        }
    }
   WD_Server_Database::get_instance();
} ?>