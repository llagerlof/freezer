<?php
/**
 * Freezer
 *
 * Freezer is a tool to help developers to discover which database inserts are made by other programs.
 *
 * @package    Freezer
 * @version    0.11.0
 * @author     Lawrence Lagerlof <llagerlof@gmail.com>
 * @copyright  2020 Lawrence Lagerlof
 * @link       http://github.com/llagerlof/freezer
 * @license    https://opensource.org/licenses/MIT MIT
 */
class Freezer
{
    /**
     * The PDO connection
     *
     * @var PDO
     */
    private $PDO = null;

    /**
     * The database configuration data
     *
     * @var array
     */
    private $config = null;

    /**
     * The last SELECT executed
     *
     * @var string
     */
    private $last_query = null;

    /**
     * The result of the last SELECT
     *
     * @var array
     */
    private $last_result = null;

    /**
     * The tables' metadata
     *
     * @var array
     */
    private $tables = null;

    /**
     * The inserted records between a save()('Freeze' on index.htm) and a load()('What is New' on index.htm)
     *
     * @var array
     */
    private $diff = null;

    /**
     * A list of ignored tables
     *
     * @var array
     */
    private $tables_without_max_field = array();

    /**
     * The database name
     *
     * @var string
     */
    private $dbname = null;

    /**
     * The database encoding
     *
     * @var string
     */
    private $encoding = null;

    /**
     * An array containing all errors
     *
     * @var array
     */
    private $errors = null;

    /**
     * An array containing all messages
     *
     * @var array
     */
    private $messages = null;

    /**
     * Constructor
     *
     * @param string $config The database configuration. Must match the middle of the filename. eg: db config file='freezer.mydb.php' then $config='mydb'
     */
    function __construct($config)
    {
        $this->loadConfig($config);

        try {
            $this->PDO = new PDO($this->config['db']['statement'], $this->config['db']['username'], $this->config['db']['password']);
        } catch (Exception $e) {
            $this->errors[] = 'Could not connect to database. Check configuration file.';

            return false;
        }
    }

    /**
     * Load the database configuration from the config file.
     *
     * @param string $config This configuration must match the middle of the filename. eg: db config file='freezer.mydb.php' then $config='mydb'
     *
     * @return array The configuration data
     */
    private function loadConfig($config)
    {
        $this->config = require(dirname(__FILE__) . '/../config/freezer.' . $config . '.php');
        $ini_statement = parse_ini_string(str_replace(';', "\n", $this->config['db']['statement']));
        $this->dbname = $ini_statement['dbname'];
        $this->temp_file = sys_get_temp_dir() . '/freezer.' . $this->dbname . '.saved.temp';
        $this->encoding = isset($this->config['encoding']) ? $this->config['encoding'] : 'UTF-8';

        return $this->config;
    }

    /**
     * Generic method to execute database queries
     *
     * @param string $sql The SELECT statement
     *
     * @return array The resultset
     */
    private function query($sql)
    {
        if (isset($this->PDO) && !is_null($this->PDO)) {
            $ps = $this->PDO->prepare($sql);
        } else {
            $this->errors[] = 'Could not execute a prepared statement. Database connection problem?';

            return false;
        }

        $this->last_query = $sql;
        $result = $ps->execute();
        if (!$result) {
            $error_message = $ps->errorInfo();
            $this->errors[] = "Database query error: " . trim($error_message[2]);

            return false;
        }
        $this->last_result = $ps->fetchAll(PDO::FETCH_ASSOC);

        return $this->last_result;
    }

    /**
     * Load the tables info. The primary reason is to identify each auto_increment field.
     *
     * @return array All tables details
     */
    private function getTables()
    {
        $tables = $this->query('show tables');
        if (!$tables) {
            $this->errors[] = 'Could not execute: "SHOW TABLES"';

            return false;
        };

        foreach ($tables as $table) {
            $ini = parse_ini_string(str_replace(';', "\n", $this->config['db']['statement']));
            if (!$ini) {
                $this->errors[] = 'Could not parse database statement. Check the configuration file.';

                return false;
            }

            $table_structure = $this->query("desc " . $table['Tables_in_' . strtolower($this->dbname)]);
            if (!$table_structure) {
                $this->errors[] = 'Could not execute DESC on table "' . $table['Tables_in_' . strtolower($this->dbname)] . '"';

                return false;
            }

            $this->tables[$table['Tables_in_' . strtolower($this->dbname)]] = $table_structure;
        }

        return $this->tables;
    }

    /**
     * Return the auto_increment or user defined field (eg. a datetime field) to use in select max()
     *
     * @param string $tablename The name of the table
     *
     * @return string The name of the auto_increment or user defined field to use in select max()
     */
    private function getMaxField($tablename)
    {
        if (!isset($this->config['tables'][$tablename]['max_field'])) {
            $table = $this->tables[$tablename];
            foreach ($table as $field) {
                if ($field['Extra'] == 'auto_increment') {
                    return $field['Field'];
                }
            }
        } else {
            return $this->config['tables'][$tablename]['max_field'];
        }
        $this->tables_without_max_field[] = $tablename;

        return null;
    }

    /**
     * Return the max id (or user defined field) from a table
     *
     * @param string $tablename The name of the table
     *
     * @return integer The last id of the table (or max user defined field)
     */
    private function getLastRecordId($tablename)
    {
        $id_field = $this->getMaxField($tablename);
        $last_record_id = !empty($id_field) ? $this->query('select max(' . $id_field . ') as last from ' . $tablename) : null;

        return !empty($last_record_id) ? $last_record_id[0]['last'] : null;
    }

    /**
     * Return the connection encoding set in configuration file. See file config/freezer.example.php
     *
     * @return string The database encoding
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Return all logged error messages
     *
     * @return array Error messages
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Return all logged messages
     *
     * @return array Messages
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Return messages and errors to be sent as json on ajax request
     *
     * @return StdClass Messages
     */
    public function getResponse()
    {
        $response = new StdClass();
        $response->messages = $this->messages;
        $response->errors = $this->errors;

        return $response;
    }

    /**
     * Write a temporary file to store the last tables' IDs
     *
     * @return bool The number of bytes written to the temporary file
     */
    public function save()
    {
        $tables = $this->getTables();
        if (!$tables) {
            $this->errors[] = 'Failed to retrieve tables list.';

            return false;
        }
        $table_last_ids = array();
        foreach ($tables as $tablename => $tabledetail) {
            $last_record_id = $this->getLastRecordId($tablename);

            if (!in_array($tablename, $this->tables_without_max_field)) {
                $this->tables[$tablename]['last_record_id'] = $last_record_id;
                $table_last_ids[$tablename] = $last_record_id;
            }
        }

        $bytes_written = file_put_contents($this->temp_file, serialize($table_last_ids));
        if (!$bytes_written) {
            $this->errors[] = 'Could not write to temporary file.';

            return false;
        }

        $this->messages[] = count($table_last_ids) . ' frozen tables. ' . $bytes_written . ' bytes written to temporary file. Add something to the database and click in [What is New].';

        return $bytes_written;
    }

    /**
     * Load from the temporary file the last tables' ids and query all tables to find what changed
     *
     * @return array All new records
     */
    public function load()
    {
        $previous_ids = unserialize(file_get_contents($this->temp_file));
        if (!$previous_ids) {
            $this->errors[] = 'Could not read from temporary file.';

            return false;
        }

        $tables = $this->getTables();
        if (!$tables) {
            $this->errors[] = 'Failed to retrieve tables list.';

            return false;
        }

        foreach ($previous_ids as $tablename => $last_id) {
            $max_field = $this->getMaxField($tablename);
            $where = $last_id ? ' where ' . $max_field . ' > \'' . $last_id . '\'' : '';
            $table_current_data = $this->query('select * from ' . $tablename . $where);
            if (!is_array($table_current_data)) {
                $this->errors[] = 'Failed to select the last record from table "' . $tablename . '"';

                return false;
            }

            if (!empty($table_current_data)) {
                $this->diff[$tablename] = $table_current_data;
            }
        }

        return $this->diff;
    }
}
