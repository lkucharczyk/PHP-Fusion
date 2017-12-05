<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: all_functions_include.php
| Author: Takács Ákos (Rimelek)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/

use PHPFusion\Database\AbstractDatabaseDriver;
use PHPFusion\Database\DatabaseFactory;
use PHPFusion\Database\Exception\SelectionException;

/*
 * It will be called after everything else even if the script is
 * halted by exit(), die(), fatal error or exception.
 *
 * It shows all sent query per connection
 */
register_shutdown_function(function () {
    if (DatabaseFactory::isDebug()) {
        $log = AbstractDatabaseDriver::getGlobalQueryLog();
        foreach ($log as $connectionid => $value) {
            if (!DatabaseFactory::isDebug($connectionid)) {
                unset($log[$connectionid]);
            }
        }
        //print_p($log);

        $html = "<a href='#queries' class='queries-btn btn btn-primary pull-left'>View Queries</a>\n";
        $html .= "<div id='queries' class='well queries-log' style='display: none'>\n";
        foreach ($log as $connID => $queries) {
            $html .= "Database connection ID: '<strong>".$connID."</strong>'<br/>\n";

            $queries_time = 0;
            $queries_log = '';
            foreach ($queries as $key => $query) {
                $queries_time = $queries_time + $query[0];
                // The time and the query
                $queries_log .= "<strong>#".($key + 1)." Time: ".$query[0]."</strong><br/>\n".stripinput($query[1])."<br />\n";
                /*
                 // The query explained
                $exp_title = "";
                $exp_value = "";
                $queries_log .= "<table class='tbl-border'>";
                // For more info on this see: http://dev.mysql.com/doc/refman/5.0/en/explain-output.html
                // and http://dev.mysql.com/doc/refman/5.0/en/explain-extended.html
                foreach (dbarray(dbquery("EXPLAIN EXTENDED ".$query[1]."")) as $key => $value) {
                    $exp_title .= "<td class='tbl1 panel panel-default'>".$key."</td>";
                    $exp_value .= "<td class='tbl2 panel panel-default'>".($value == '' ? '-' : $value)."</td>";
                }
                $queries_log .= "<tr>".$exp_title."</tr><tr>".$exp_value."</tr>";
                $queries_log .= "</table>";

                // For more info on this please see: http://dev.mysql.com/doc/refman/5.0/en/show-warnings.html
                $more_info = dbarray(dbquery("SHOW WARNINGS"));
                if ($more_info) {
                    $queries_log .= "<a class='query-more-btn' href='#'>View additional info on this query generated by SHOW WARNINGS</a>";
                    $queries_log .= "<table class='tbl-border query-more-info' style='display:none'>";
                    $queries_log .= "<tr><td class='tbl1 panel panel-default'>Code</td>
                                        <td class='tbl1 panel panel-default'>Level</td>
                                        <td class='tbl1 panel panel-default'>Message</td>
                                    </tr>";
                    $queries_log .= "<tr><td class='tbl2 panel panel-default'>".$more_info['Code']."</td>
                                        <td class='tbl2 panel panel-default'>".$more_info['Level']."</td>
                                        <td class='tbl2 panel panel-default'>".stripinput($more_info['Message'])."</td>
                                    </tr>";
                    $queries_log .= "</table>";
                }
                $queries_log .= "<hr />\n";
                */
            }

            $html .= "Total time taken by queries to execute in this connection: <strong>".$queries_time."</strong> seconds<br/>\n";
            $html .= "<code>".$queries_log."</code>\n";
        }
        $html .= "</div>\n";
        $html .= "<style>.queries-log code {white-space: normal} .queries-log hr {border-color: #ccc}</style>\n";
        $html .= "<script>
        $('.query-more-btn').click(function(e){
            e.preventDefault();
            $(this).next('table').toggle();
        });
        $('.queries-btn').click(function(){
            $(this).hide();
            $('.queries-log').toggle();
        })
        </script>";

        echo $html;
    }
});

/**
 * Send a database query
 *
 * @param string $query SQL
 * @param array  $parameters
 * @return mixed The result of query or FALSE on error
 */
function dbquery($query, array $parameters = []) {
    // Temporary check to detect the bug in installer
    return DatabaseFactory::getConnection('default')->query($query, $parameters);
}

/**
 * Count the number of rows in a table filtered by conditions
 *
 * @param string $field Parenthesized field name
 * @param string $table Table name
 * @param string $conditions conditions after "where"
 * @param array  $parameters
 * @return boolean
 */
function dbcount($field, $table, $conditions = "", array $parameters = []) {
    return DatabaseFactory::getConnection('default')->count($field, $table, $conditions, $parameters);
}

/**
 * Fetch the first column of a specific row
 *
 * @param mixed $result
 * @param int   $row
 * @return mixed
 */
function dbresult($result, $row) {
    return DatabaseFactory::getConnection('default')->fetchFirstColumn($result, $row);
}

/**
 * Count the number of affected rows by the given query
 *
 * @param mixed $result
 * @return int
 */
function dbrows($result) {
    return DatabaseFactory::getConnection('default')->countRows($result);
}

/**
 * Fetch one row as an associative array
 *
 * @param mixed $result
 * @return array Associative array
 */
function dbarray($result) {
    return DatabaseFactory::getConnection('default')->fetchAssoc($result);
}

/**
 * Fetch one row as a numeric array
 *
 * @param mixed $result
 * @return array Numeric array
 */
function dbarraynum($result) {
    return DatabaseFactory::getConnection('default')->fetchRow($result);
}

/**
 * Connect to the database
 *
 * @param string  $db_host
 * @param string  $db_user
 * @param string  $db_pass
 * @param string  $db_name
 * @param boolean $halt_on_error If it is TRUE, the script will halt in case of error
 * @return array
 */
function dbconnect($db_host, $db_user, $db_pass, $db_name, $halt_on_error = FALSE) {
    $connection_success = TRUE;
    $dbselection_success = TRUE;
    try {
        DatabaseFactory::connect($db_host, $db_user, $db_pass, $db_name, [
            'debug' => DatabaseFactory::isDebug('default')
        ]);
    } catch (\Exception $e) {
        $connection_success = $e instanceof SelectionException;
        $dbselection_success = FALSE;
        if ($halt_on_error and !$connection_success) {
            die("<strong>Unable to establish connection to MySQL</strong><br />".$e->getCode()." : ".$e->getMessage());
        } else if ($halt_on_error) {
            die("<strong>Unable to select MySQL database</strong><br />".$e->getCode()." : ".$e->getMessage());
        }

    }

    return [
        'connection_success'  => $connection_success,
        'dbselection_success' => $dbselection_success
    ];
}

/**
 * Get the next auto_increment id of a table
 *
 * Try to avoid the use of it! {@link dblastid()} after insert
 * is more secure way to get the id of an existing record than
 * get just a potential id.
 *
 * @param string $table
 * @return int|false
 */
function dbnextid($table) {
    return DatabaseFactory::getConnection('default')->getNextId($table);
}

/**
 * Get the last inserted auto increment id
 *
 * @return int
 */
function dblastid() {
    return (int)DatabaseFactory::getConnection('default')->getLastId();
}

/**
 * Get the AbstractDatabase instance
 *
 * @return AbstractDatabaseDriver
 */
function dbconnection() {
    try {
        return DatabaseFactory::getConnection('default');
    } catch (\Exception $e) {
        ## Do nothing to hide all errors
        ini_set('display_errors', FALSE);

        return NULL;
    }
}
