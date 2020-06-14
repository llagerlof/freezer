<?php
/**
 * Freezer
 *
 * Freezer is a tool to help developers to discover which database inserts are made by other programs.
 *
 * @package    Freezer
 * @version    0.9.0
 * @author     Lawrence Lagerlof <llagerlof@gmail.com>
 * @copyright  2020 Lawrence Lagerlof
 * @link       http://github.com/llagerlof/Freezer
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
     * The last PDO error
     *
     * @var array
     */
    private $last_error = null;

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
    private $tables_without_max_field = null;

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
     * Constructor
     *
     * @param string $config The database configuration. Must match the middle of the filename. eg: db config file='freezer.mydb.php' then $config='mydb'
     */
    function __construct($config)
    {
        $this->loadConfig($config);
        $this->PDO = new PDO($this->config['db']['statement'], $this->config['db']['username'], $this->config['db']['password']);
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
     * Return the connection encoding set in configuration file. See file freezer.example.php
     *
     * @return string The database encoding
     */
    public function getEncoding()
    {
        return $this->encoding;
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
        $ps = $this->PDO->prepare($sql);
        $this->last_query = $sql;
        $result = $ps->execute();
        if (!$result) {
            $error_info = $ps->errorInfo();
            $this->last_error = $error_info;

            throw new Exception($error_info[2]);
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
        foreach ($tables as $table) {
            $ini = parse_ini_string(str_replace(';', "\n", $this->config['db']['statement']));
            $table_structure = $this->query("desc " . $table['Tables_in_' . $this->dbname]);
            $this->tables[$table['Tables_in_' . $this->dbname]] = $table_structure;
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
     * Write a temporary file to store the last tables' ids
     *
     * @return bool The number of bytes written to the temporary file
     */
    public function save()
    {
        $this->getTables();
        foreach ($this->tables as $tablename => $tabledetail) {
            $last_record_id = $this->getLastRecordId($tablename);
            if ($last_record_id) {
                $this->tables[$tablename]['last_record_id'] = $last_record_id;
                $table_last_ids[$tablename] = $last_record_id;
            }
        }

        return file_put_contents($this->temp_file, serialize($table_last_ids));
    }

    /**
     * Load from the temporary file the last tables' ids and query all tables to find what changed
     *
     * @return array All new records
     */
    public function load()
    {
        $previous_ids = unserialize(file_get_contents($this->temp_file));
        $this->getTables();
        foreach ($previous_ids as $tablename => $last_id) {
            $max_field = $this->getMaxField($tablename);
            $where = $last_id ? ' where ' . $max_field . ' > ' . $last_id : '';
            $table_current_data = $this->query('select * from ' . $tablename . $where);
            if (!empty($table_current_data)) {
                $this->diff[$tablename] = $table_current_data;
            }
        }

        return $this->diff;
    }
}
