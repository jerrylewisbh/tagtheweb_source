<?php
include('DBConfig.php');
define('DSN', "pgsql:host=$host;port=5432;dbname=$db;user=$username;password=$password");

class Connection extends PDO
{
    
    private static $dsn = DSN;
    /**
     * Returns the sinlge instance of the connection class.
     * @staticvar Singleton $instance The instance .
     */
    
    
    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
            try {
                // create a PostgreSQL database connection
                $instance = new static(self::$dsn);
            }
            catch (PDOException $e) {
                // report error message
                echo $e->getMessage();
            }
        }
        return $instance;
    }
    
}

?>