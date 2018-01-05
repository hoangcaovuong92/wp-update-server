<?php 
if (!class_exists('WD_Server_Database')) {
    class WD_Server_Database{
        protected $server     = 'localhost';
        protected $user       = 'root';
        protected $pass       = '';
        protected $db         = 'wd_purchase_code';

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
            //$this->insert('abc', '123');
            //$this->checker('abcd', '123');
            //$this->update('abc', 'xxxx');
            //$this->delete('abcd');
            $this->show_list();
        }
        protected function connect() {
            return new mysqli($this->server, $this->user, $this->pass, $this->db);
        }

        protected function insert($purchase_code, $url) {
            $mysqli         = $this->connect();
            $update_date    = time();
            $sql            = "INSERT INTO purchase_list (code, url, update_date) VALUES ('$purchase_code', '$url', '$update_date')";
            echo ($mysqli->query($sql) === TRUE) ? "New record created successfully" : "Error: " . $sql . "<br>" . $mysqli->error;
            
        }
        protected function update($purchase_code, $url) {
            $mysqli         = $this->connect();
            $update_date    = time();
            $sql            = "UPDATE purchase_list SET url = '$url', update_date = '$update_date' WHERE code = '$purchase_code'";
            echo ($mysqli->query($sql) === TRUE) ? "Update record successfully" : "Error: " . $sql . "<br>" . $mysqli->error;
        }
        protected function delete($url) {
            $mysqli         = $this->connect();
            $update_date    = time();
            $sql            = "DELETE FROM purchase_list WHERE url = '$url' LIMIT 1";
            echo ($mysqli->query($sql) === TRUE) ? "Delete record successfully" : "Error: " . $sql . "<br>" . $mysqli->error;
        }

        protected function checker($purchase_code, $url) {
            $mysqli = $this->connect();
            $result = false;
            $sql    = "SELECT * FROM purchase_list where code = '$purchase_code' and url = '$url'";
            if ($data = $mysqli->query($sql)){
                if ($data->num_rows > 0){
                   $result = true;
                }
            }
            $mysqli->close();
            var_dump($result);
            return $result;
        }

        protected function show_list() {
            $mysqli = $this->connect();
            $sql    = "SELECT * FROM purchase_list";
            if ($result = $mysqli->query($sql)){
                if ($result->num_rows > 0){
                    echo "<table border='1' cellpadding='10'>";
                    echo "<tr><th>Purchase Code</th><th>URL</th><th>Last Update</th>";
                    while ($row = $result->fetch_object()){
                        echo "<tr>";
                        echo "<td>" . $row->code . "</td>";
                        echo "<td><a href='" . $row->url . "' target='_blank'>".$row->url."</td>";
                        echo "<td>" . date('d/m/Y', $row->update_date) . "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }else{
                    echo "No results to display!";
                }
            }else{
                echo "Error: " . $mysqli->error;
            }

            $mysqli->close();
        }
    }
    //WD_Server_Database::get_instance();
} ?>