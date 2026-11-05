<?php
/**
 * Database Connection using PDO
 * Connects natively to local MySQL instance.
 */

class Database {
    private static ?PDO $instance = null;

    private const DB_HOST = '127.0.0.1';
    private const DB_PORT = '3306';
    private const DB_NAME = 'db';
    private const DB_USER = 'root';
    private const DB_PASS = 'chin0824';
    private const DB_CHARSET = 'utf8mb4';

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                getenv('DB_HOST') ?: self::DB_HOST,
                getenv('DB_PORT') ?: self::DB_PORT,
                getenv('DB_NAME') ?: self::DB_NAME,
                getenv('DB_CHARSET') ?: self::DB_CHARSET
            );

            $user = getenv('DB_USER') ?: self::DB_USER;
            $passwordsToTry = [
                getenv('DB_PASS') !== false ? getenv('DB_PASS') : self::DB_PASS,
                ''
            ];

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            $lastException = null;
            foreach (array_unique($passwordsToTry) as $pass) {
                try {
                    self::$instance = new PDO($dsn, $user, $pass, $options);
                    break;
                } catch (PDOException $e) {
                    $lastException = $e;
                }
            }

            if (self::$instance === null && $lastException !== null) {
                // If database does not exist yet or connection fails, output a clean message
                die("<div style='font-family: sans-serif; padding: 20px; background: #fff0f0; border-left: 5px solid #d32f2f; margin: 20px;'>
                        <h2 style='color: #d32f2f; margin-top:0;'>Database Connection Error</h2>
                        <p><strong>Failed to connect to MySQL database:</strong> " . htmlspecialchars($lastException->getMessage()) . "</p>
                        <p>Please ensure MySQL is running and the database schema from <code>database.sql</code> is imported into database <code>" . htmlspecialchars(self::DB_NAME) . "</code>.</p>
                        <pre style='background:#f5f5f5; padding:10px;'>mysql -u " . htmlspecialchars(self::DB_USER) . " -p " . htmlspecialchars(self::DB_NAME) . " &lt; database.sql</pre>
                     </div>");
            }
        }
        return self::$instance;
    }
}
