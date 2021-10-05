<?php

Class Message_log{
    //init required var
    public $date_from;
    public $date_to;
    public $cnt_id;
    public $usr_id;
    private $conn;

    //init values
    public function __construct($date_from,$date_to,$cnt_id='',$usr_id='')
    {
        //Checking validation of Date string
        if($this->validateDate($date_from)){
            $this->date_from = $date_from;
        }else{
            throw new Exception("date_from from must be a date format Y-m-d");
        }
        if($this->validateDate($date_to)){
            $this->date_to = date('Y-m-d', strtotime($date_to) + 60*60*24);
        }else{
            throw new Exception("date_from from must be a date format Y-m-d");
        }

        $this->cnt_id = $cnt_id;
        $this->usr_id = $usr_id;
        $this->conn = $this->getdbconnect();
    }

    //Validate date format
    private function validateDate($date, $format = 'Y-m-d'){
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    //Database connection
    private function getdbconnect(){
        $conn = mysqli_connect("localhost","root","","dumb_data") or die("Couldn't connect");
        return $conn;
    }

    //Fetching send_log Data
    public function get_log(){
        $final_result = [];
        
        $stmt= mysqli_stmt_init($this->conn);

        if($this->cnt_id && $this->usr_id){ //cnt_id, usr_id inserted
            
            $sql = 'SELECT send_log.*, numbers.cnt_id
                FROM send_log
                INNER JOIN numbers ON send_log.num_id = numbers.num_id
                WHERE log_created BETWEEN ? AND ? AND usr_id = ? AND cnt_id = ?;';
            
            mysqli_stmt_prepare($stmt,$sql);
            mysqli_stmt_bind_param($stmt,"ssss",$this->date_from ,$this->date_to,$this->usr_id,$this->cnt_id);

        }elseif($this->cnt_id){ //cnt_id
            
            $sql = 'SELECT send_log.*, numbers.cnt_id
                FROM send_log
                INNER JOIN numbers ON send_log.num_id = numbers.num_id
                WHERE log_created BETWEEN ? AND ? AND cnt_id = ?;';

            mysqli_stmt_prepare($stmt,$sql);
            mysqli_stmt_bind_param($stmt,"sss",$this->date_from,$this->date_to,$this->cnt_id);

        }elseif($this->cnt_id){ //usr_id inserted
            
            $sql = 'SELECT * FROM send_log WHERE log_created BETWEEN ? AND ? WHERE usr_id=?';

            mysqli_stmt_prepare($stmt,$sql);
            mysqli_stmt_bind_param($stmt,"sss",$this->date_from ,$this->date_to,$this->usr_id);

        }else{ //no filters
            
            $sql = "SELECT * FROM send_log WHERE log_created BETWEEN ++? AND ?";

            mysqli_stmt_prepare($stmt,$sql);
            mysqli_stmt_bind_param($stmt,"ss",$this->date_from ,$this->date_to);
            
        }

        //query execute
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_all($result,MYSQLI_ASSOC);

        //Getting dates between date_from and date_to
        $period = new DatePeriod(
            new DateTime($this->date_from),
            new DateInterval('P1D'),
            new DateTime($this->date_to)
        );

        //Inserting wanted data
        foreach ($period as $value) {
            $current = $value->format('Y-m-d');
            $success = count(array_filter($data,fn($each)=> date('Y-m-d', strtotime($each['log_created'])) === $current && $each['log_success'] ));
            $fail = count(array_filter($data,fn($each)=> date('Y-m-d', strtotime($each['log_created'])) === $current && !$each['log_success'] ));
            $final_result[$current] = ['success'=>$success,"fail"=>$fail];
        }
        return $final_result;
    }

    //get from-to dates
    public function get_dates(){
        return ['from'=>$this->date_from,'to'=>$this->date_to];
    }

    //fetching user data
    public function get_user(){
        $sql = "SELECT usr_name FROM users WHERE usr_id = ?";
        
        $stmt= mysqli_stmt_init($this->conn);
        mysqli_stmt_prepare($stmt,$sql);
        mysqli_stmt_bind_param($stmt,"s",$this->usr_id );
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        return $result->num_rows ? mysqli_fetch_assoc($result)['usr_name'] : '';
        
    }

    //fetching country data
    public function get_country(){
        $sql = "SELECT cnt_title FROM countries WHERE cnt_id = ?";
        
        $stmt= mysqli_stmt_init($this->conn);
        mysqli_stmt_prepare($stmt,$sql);
        mysqli_stmt_bind_param($stmt,"s",$this->cnt_id );
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return $result->num_rows ? mysqli_fetch_assoc($result)['cnt_title'] : '';
    }
}
