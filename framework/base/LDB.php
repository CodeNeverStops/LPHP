<?php
/**
 * 
 * @author loki
 *
 */
class LDB
{
    protected $tablepre = '';
    protected $version = '';
    protected $querynum = 0;
    protected $curlink;
    protected $link = array();
    protected $config = array();
    protected $sqldebug = array();
    protected $map = array();
   
    /**
     * 
     * @return LDB
     */
    public static function getInstance ($config = array())
    {
        static $_db = null;
        if (null === $_db) {
            $_db = new LDB($config);
        }
        return $_db;
    }
    private function __construct ($config = array())
    {
        if (! empty($config)) {
            $this->set_config($config);
        }
    }
    public function set_config($config)
    {
        $this->config = &$config;
        $this->tablepre = $config['1']['tablepre'];
        if (! empty($this->config['map'])) {
            $this->map = $this->config['map'];
        }
    }
    public function connect ($serverid = 1)
    {
        if (empty($this->config) || empty($this->config[$serverid])) {
            $this->halt('config_db_not_found');
        }
        $this->link[$serverid] = $this->_dbconnect(
        $this->config[$serverid]['dbhost'], 
        $this->config[$serverid]['dbuser'], 
        $this->config[$serverid]['dbpw'], 
        $this->config[$serverid]['dbcharset'], 
        $this->config[$serverid]['dbname'], 
        $this->config[$serverid]['pconnect']);
        $this->curlink = $this->link[$serverid];
    }
    protected function _dbconnect ($dbhost, $dbuser, $dbpw, $dbcharset, $dbname, $pconnect)
    {
        $link = null;
        $func = empty($pconnect) ? 'mysql_connect' : 'mysql_pconnect';
        if (! $link = @$func($dbhost, $dbuser, $dbpw, 1)) {
            $this->halt('notconnect');
        } else {
            $this->curlink = $link;
            if ($this->version() > '4.1') {
                $dbcharset = $dbcharset ? $dbcharset : $this->config[1]['dbcharset'];
                $serverset = $dbcharset ? 'character_set_connection=' .$dbcharset .', character_set_results=' .$dbcharset . ', character_set_client=binary' : '';
                $serverset .= $this->version() >'5.0.1' ? ((empty($serverset) ? '' : ',') .'sql_mode=\'\'') : '';
                $serverset && mysql_query("SET $serverset", $link);
            }
            $dbname && @mysql_select_db($dbname, $link);
        }
        return $link;
    }
    public function table_name ($tablename)
    {
        if (! empty($this->map) && ! empty($this->map[$tablename])) {
            $id = $this->map[$tablename];
            if (! $this->link[$id]) {
                $this->connect($id);
            }
            $this->curlink = $this->link[$id];
        } else {
            $this->curlink = $this->link[1];
        }
        return $this->tablepre . $tablename;
    }
    public function select_db ($dbname)
    {
        return mysql_select_db($dbname, $this->curlink);
    }
    public function fetch_array ($query, $result_type = MYSQL_ASSOC)
    {
        return mysql_fetch_array($query, $result_type);
    }
    public function fetch_first ($sql)
    {
        return $this->fetch_array($this->query($sql));
    }
    public function result_first ($sql)
    {
        return $this->result($this->query($sql), 0);
    }
    public function query ($sql)
    {
        if (defined('ROM_DEBUG') && ROM_DEBUG) {
            $starttime = dmicrotime();
        }
        $query = mysql_query($sql, $this->curlink);
        if (defined('ROM_DEBUG') && ROM_DEBUG) {
            $this->sqldebug[] = array(
                $sql, 
                number_format((dmicrotime() - $starttime), 6), 
                debug_backtrace()
            );
        }
        $this->querynum ++;
        return $query;
    }
    public function affected_rows ()
    {
        return mysql_affected_rows($this->curlink);
    }
    public function error ()
    {
        return (($this->curlink) ? mysql_error($this->curlink) : mysql_error());
    }
    public function errno ()
    {
        return intval(($this->curlink) ? mysql_errno($this->curlink) : mysql_errno());
    }
    public function result ($query, $row = 0)
    {
        $query = @mysql_result($query, $row);
        return $query;
    }
    public function num_rows ($query)
    {
        $query = mysql_num_rows($query);
        return $query;
    }
    public function num_fields ($query)
    {
        return mysql_num_fields($query);
    }
    public function free_result ($query)
    {
        return mysql_free_result($query);
    }
    public function insert_id ()
    {
        return ($id = mysql_insert_id($this->curlink)) >= 0 ? $id : $this->result($this->query("SELECT last_insert_id()"), 0);
    }
    public function fetch_row ($query)
    {
        $query = mysql_fetch_row($query);
        return $query;
    }
    public function fetch_fields ($query)
    {
        return mysql_fetch_field($query);
    }
    public function version ()
    {
        if (empty($this->version)) {
            $this->version = mysql_get_server_info($this->curlink);
        }
        return $this->version;
    }
    public function close ()
    {
        return mysql_close($this->curlink);
    }
    public function halt ($message = '', $sql = '')
    {
        $dberror = $this->error();
        $dberrno = $this->errno();
        $phperror = '<table style="font-size:11px" cellpadding="0"><tr><td width="270">File</td><td width="80">Line</td><td>Function</td></tr>';
        foreach (debug_backtrace() as $error) {
            $error['file'] = str_replace(ROOT_PATH, '', $error['file']);
            $error['class'] = isset($error['class']) ? $error['class'] : '';
            $error['type'] = isset($error['type']) ? $error['type'] : '';
            $error['function'] = isset($error['function']) ? $error['function'] : '';
            $phperror .= "<tr><td>$error[file]</td><td>$error[line]</td><td>$error[class]$error[type]$error[function]()</td></tr>";
        }
        $phperror .= '</table>';
        $helplink = '';
        @header('Content-Type: text/html; charset=utf-8');
        echo '<div style="position:absolute;font-size:11px;font-family:verdana,arial;background:#EBEBEB;padding:0.5em;line-height:1.5em">';
        echo "<b>PHP Backtrace</b><br />$phperror<br /></div>";
        echo "<br/>message:$message<br/>";
        echo "<br/>sql:$sql<br/>";
        exit();
    }
}
?>