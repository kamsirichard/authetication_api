<?php

namespace Config;

use DatabaseCall\Users_Table;

/**
 * View 
 *
 * PHP version 5.4
 */
class Utility_Functions  extends DB_Connect
{
    public static function escape($data)
    {
        $conn = static::getDB();
        $input = $data;
        // This removes all the HTML tags from a string. This will sanitize the input string, and block any HTML tag from entering into the database.
        // filter_var($geeks, FILTER_SANITIZE_STRING);
        $input = htmlspecialchars($input);
        $input = trim($input, " \t\n\r");
        // htmlspecialchars() convert the special characters to HTML entities while htmlentities() converts all characters.
        // Convert the predefined characters "<" (less than) and ">" (greater than) to HTML entities:
        $input = htmlspecialchars($input, ENT_QUOTES,'UTF-8');
        // prevent javascript codes, Convert some characters to HTML entities:
        $input = htmlentities($input, ENT_QUOTES, 'UTF-8');
        $input = stripslashes(strip_tags($input));
        $input = mysqli_real_escape_string($conn, $input);

        return $input;
    }
   
    public static function validateEmail($email) {
        if ( filter_var($email, FILTER_VALIDATE_EMAIL) ){
            return true;
        }else{
            return false;
        }
    }

    public static function validatePassword($password){
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number    = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\?]@', $password);

        if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
           return false;
        }else{
            return true;
        }

    }
    public static function generateShortKey($strength){
        $input = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $output = static::generate_string($input, $strength);

        return $output;
    }
    public static function generate_string($input, $strength){
        $input_length = strlen($input);
        $random_string = '';
        for ($i = 0; $i < $strength; $i++) {
            $random_character = $input[mt_rand(0, $input_length - 1)];
            $random_string .= $random_character;
        }
    
        return $random_string;
    }
    public static function checkIfCodeisInDB($tableName, $field ,$pubkey) {
        $connect = static::getDB();
        $alldata = [];
        // Check if the email or phone number is already in the database
        $query = "SELECT $field FROM $tableName WHERE $field = ?";
        $stmt = $connect->prepare($query);
        $stmt->bind_param("s", $pubkey);
        $stmt->execute();
        $result = $stmt->get_result();
        $num_row = $result->num_rows;

        if ($num_row > 0){
            return true;
        }

        return false;
        
    }
    public static function generateUniqueShortKey($tableName, $field){
        $loop = 0;
        while ($loop == 0){
            $userKey = "SVT".static::generateShortKey(5);
            if ( static::checkIfCodeisInDB($tableName, $field ,$userKey) ){
                $loop = 0;
            }else {
                $loop = 1;
                break;
            }
        }

        return $userKey;
    }
    
    public static function generatePubKey($strength){
        $input = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $output = static::generate_string($input, $strength);

return $output;
    }
    public static function generateUniquePubKey($tableName, $field){
        //add role to your generate function
        //if user (checkIfPubKeyisInDB), return $userkey
        //else if admin (checkIfIsAdmin), return $adminkey
        //else (checkIfPubKeyisInDB), so we wont edit all api
        $loop = 0;
        while ($loop == 0){
            $userKey = "SVT".static::generatePubKey(37). $tableName;
            if ( static::checkIfCodeisInDB($tableName,$field,$userKey) ){
                $loop = 0;
            }else {
                $loop = 1;
                break;
            }
        }

        return $userKey;
    }
    public static function getCurrentFullURL(){
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,strpos( $_SERVER["SERVER_PROTOCOL"],'/'))).'://';
        // Get the server name and port
        $servername = $_SERVER['SERVER_NAME'];
        $port = $_SERVER['SERVER_PORT'];
        // Get the path to the current script
        $path = $_SERVER['PHP_SELF'];
        // Combine the above to form the full URL
        $endpoint = $protocol . $servername . ":" . $port . $path;
        return $endpoint;
    }
    public static function validate_input($data)
    {
        $incorrectdata=false;
        if(strlen($data)==0){
            $incorrectdata=true;
        }else if($data==null){
            $incorrectdata=true;
        }else if(empty($data)){
            $incorrectdata=true;
        }

        return $incorrectdata;
    }

    public static function Password_encrypt($password){
        $BlowFish_Format="$2y$10$";
        $salt_len=24;
        $salt= static::Get_Salt($salt_len);
        $the_format=$BlowFish_Format . $salt;
        
        $hash_pass=crypt($password, $the_format);
        return $hash_pass;
    }
   
    public static  function Get_Salt($size){
        $Random_string= md5(uniqid(mt_rand(), true));
        
        $Base64_String= base64_encode($Random_string);
        
        $change_string=str_replace('+', '.', $Base64_String);
        
        $salt=substr($change_string, 0, $size);
        
        return $salt;
    }
   
    public static function inputData($data, $key) {
        return isset($data->$key) ? self::escape($data->$key) : "";
    }   
   
    public static  function greetUsers(){
        $welcome_string="Welcome!";
        $numeric_date=date("G");

        //Start conditionals based on military time
        if($numeric_date>=0&&$numeric_date<=11)
        $welcome_string="🌅 Good Morning";
        else if($numeric_date>=12&&$numeric_date<=17)
        $welcome_string="☀️ Good Afternoon";
        else if($numeric_date>=18&&$numeric_date<=23)
        $welcome_string="😴 Good Evening";

        return $welcome_string;
    }
    public static function exceptionHandler($exception)
    {
        // Code is 404 (not found) or 500 (general error)
        $code = $exception->getCode();
        if ($code != 404) {
            $code = 500; 
        }
        http_response_code($code);

        $error = error_get_last();
        $errno   ="";
        $errfile = "";
        $errline = "";
        $errstr  = "";
        if ($error !== null) {
            $errno   = $error["type"];
            $errfile = $error["file"];
            $errline = $error["line"];
            $errstr  = $error["message"];
        }
 
        if (Constants::SHOW_ERRORS) {
            echo "<h1>Fatal error</h1>";
            echo "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
            echo "<p>Message: '" . $exception->getMessage() . "'</p>";
            echo "<p>Stack trace:<pre>" . $exception->getTraceAsString() . "</pre></p>";
            echo "<p>Thrown in '" . $exception->getFile() . "' on line " . $exception->getLine() . "</p>";
        } else {
            $log = dirname(DIR) . '/logs/' . date('Y-m-d') . '.txt';
            ini_set('error_log', $log);

$message = "Uncaught exception: '" . get_class($exception) . "'";
            $message .= " with message '" . $exception->getMessage() . "'";
            $message .= "\nStack trace: " . $exception->getTraceAsString();
            $message .= "\nThrown in '" . $exception->getFile() . "' on line " . $exception->getLine();
            $message .=  "\nOTHER ERRORS'" .$errno." ".$errfile." ".$errline." ". $errstr;

            error_log($message);
        }
    }
    public static function errorHandler($level, $message, $file, $line)
    {
        if (error_reporting() !== 0) {  // to keep the @ operator working
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }
    public static function safeEscape($data, $key) {
        return isset($data->$key) ? self::escape($data->$key) : "";
    }
    
    public static function updateUserProfile($new_name, $new_email, $new_deparment, $new_description, $user_pubkey)
    {
        $connect = static::getDB();

        // Prepare the SQL statement for updating the user profile
        $updateProfile = $connect->prepare("UPDATE users SET fullname = ?, email = ?, department = ?, job_description = ? WHERE pub_key = ?");

        
        // Bind parameters and values
        $updateProfile->bind_param("sssss", $new_name, $new_email, $new_deparment,  $new_description, $user_pubkey);

        // Execute the update query
        if ($updateProfile->execute()) {
            // Return true to indicate success
            return true;
        } else {
            // Handle the case where the update query fails (e.g., return false or an error code)
            return false;
        }
    }

    public static function adminUpdateUserProfile($newName, $newEmail, $newDepartment, $newGender, $newStatus, $user_pubkey)
    {
        $connect = static::getDB();

        // Prepare the SQL statement for updating the user profile
        $updateProfile = $connect->prepare("UPDATE users SET fullname = ?, email = ?, department = ?, gender = ?,status = ? WHERE pub_key = ?");

      
        // Bind parameters and values
        $updateProfile->bind_param("ssssss", $newName, $newEmail, $newDepartment, $newGender, $newStatus, $user_pubkey);

        // Execute the update query
        if ($updateProfile->execute()) {
            // Return true to indicate success
            return true;
        } else {
            // Handle the case where the update query fails (e.g., return false or an error code)
            return false;
        }
    }
    public static function updateAdminProfilePic($photopath, $email)
    {
        $connect = static::getDB();

        // Prepare the SQL statement for updating the user profile
        $updateProfile = $connect->prepare("UPDATE admins SET profile_pic = ? WHERE email = ?");

      
        // Bind parameters and values
        $updateProfile->bind_param("ss", $photopath, $email);

        // Execute the update query
        if ($updateProfile->execute()) {
            // Return true to indicate success
            return true;
        } else {
            // Handle the case where the update query fails (e.g., return false or an error code)
            return false;
        }
    }
    public static function updateUserProfilePic($photopath, $email)
    {
        $connect = static::getDB();

        // Prepare the SQL statement for updating the user profile
        $updateProfile = $connect->prepare("UPDATE users SET profile_pic = ? WHERE email = ?");

      
        // Bind parameters and values
        $updateProfile->bind_param("ss", $photopath, $email);

        // Execute the update query
        if ($updateProfile->execute()) {
            // Return true to indicate success
            return true;
        } else {
            // Handle the case where the update query fails (e.g., return false or an error code)
            return false;
        }
    }

public static function restrictUser($userid)
    {
        $conn = static::getDB();
        $query = "UPDATE users SET status = 0 WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        // Bind the parameter to the statement
        $stmt->bind_param("s", $userid);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }
    public static function restrictAdmin($userid)
    {
        $conn = static::getDB();
        $query = "UPDATE admins SET status = 0 WHERE company_id = ?";
        $stmt = $conn->prepare($query);
        // Bind the parameter to the statement
        $stmt->bind_param("s", $userid);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public static function addNotification($userid, $message)
    {
        $conn = static::getDB();

        $query = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $userid, $message);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public static function addMessage($userid, $message, $email)
    {
        $conn = static::getDB();

        $query = "INSERT INTO messages (user_id, message, email) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $userid, $message, $email);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    public static function getAdminMessages($email)
    {
        $conn = static::getDB();

        $query = "SELECT * FROM messages WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        $unreadNotifications = [];
        while ($row = $result->fetch_assoc()) {
            $unreadNotifications[] = $row;
        }

        $stmt->close();

        return $unreadNotifications;
    }
    public static function getNotificationsForPreviousDay($userid)
    {
        $conn = static::getDB();

        // Assuming 'notification_date' is the column representing the date of the notification
        $query = "SELECT * FROM notifications WHERE user_id = ? AND DATE(dateandtime) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $userid);
        $stmt->execute();

        $result = $stmt->get_result();

        $notificationsForPreviousDay = [];
        while ($row = $result->fetch_assoc()) {
            $notificationsForPreviousDay[] = $row;
        }

        $stmt->close();

        return $notificationsForPreviousDay;
    }
    public static function getNotificationsForCurrentDay($userid)
    {
        $conn = static::getDB();

        // Assuming 'notification_date' is the column representing the date of the notification
        $query = "SELECT * FROM notifications WHERE user_id = ? AND DATE(dateandtime) = CURDATE()";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $userid);
        $stmt->execute();

        $result = $stmt->get_result();

        $notificationsForCurrentDay = [];
        while ($row = $result->fetch_assoc()) {
            $notificationsForCurrentDay[] = $row;
        }

        $stmt->close();

        return $notificationsForCurrentDay;
    }
    public static function getNotificationsForCurrentWeek($userid)
    {
        $conn = static::getDB();

        // Assuming 'notification_date' is the column representing the date of the notification
        $query = "SELECT * FROM notifications WHERE user_id = ? AND YEARWEEK(dateandtime) = YEARWEEK(CURDATE())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $userid);
        $stmt->execute();

        $result = $stmt->get_result();

        $notificationsForCurrentWeek = [];
        while ($row = $result->fetch_assoc()) {
            $notificationsForCurrentWeek[] = $row;
        }

        $stmt->close();

        return $notificationsForCurrentWeek;
    }

public static function getUnreadNotifications($userid)
    {
        $conn = static::getDB();

        $query = "SELECT * FROM notifications WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $userid);
        $stmt->execute();

        $result = $stmt->get_result();

        $unreadNotifications = [];
        while ($row = $result->fetch_assoc()) {
            $unreadNotifications[] = $row;
        }

        $stmt->close();

        return $unreadNotifications;
    }

    public static function markNotificationsAsRead($notifications)
    {
        $conn = static::getDB();

        // Check if there are notifications to mark as read
        if (empty($notifications)) {
            return; // No notifications to mark as read, so we can return early.
        }

        // Create an array of IDs from the $notifications array
        $notificationIds = array_map(function ($notification) {
            return $notification['id'];
        }, $notifications);

        // Generate placeholders for the IN clause
        $placeholders = implode(',', array_fill(0, count($notificationIds), '?'));

        // Prepare the SQL query
        $query = "UPDATE notifications SET is_read = 1 WHERE id IN ($placeholders)";

        // Prepare and execute the statement
        $stmt = $conn->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param(str_repeat('i', count($notificationIds)), ...$notificationIds);
            $stmt->execute();
            $stmt->close();
        }
    }

    public static function adminLogout($userPubkey) {
        
            $admin = Users_Table::getAdminUserByKey($userPubkey);
            if ($admin) {
                Users_Table::updateAdminUserStatus($admin['id'], 'logged_out');
            }
        }

    }


    ?>
