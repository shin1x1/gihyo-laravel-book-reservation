<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Functions used to generate table tracking
 *
 * @package PhpMyAdmin
 */

/**
 * Filters tracking entries
 *
 * @param array  $data           the entries to filter
 * @param string $filter_ts_from "from" date
 * @param string $filter_ts_to   "to" date
 * @param string $filter_users   users
 *
 * @return array filtered entries
 */
function PMA_filterTracking(
    $data, $filter_ts_from, $filter_ts_to, $filter_users
) {
    $tmp_entries = array();
    $id = 0;
    foreach ($data as $entry) {
        $timestamp = strtotime($entry['date']);
        $filtered_user = in_array($entry['username'], $filter_users);
        if ($timestamp >= $filter_ts_from
            && $timestamp <= $filter_ts_to
            && (in_array('*', $filter_users) || $filtered_user)
        ) {
            $tmp_entries[] = array(
                'id'        => $id,
                'timestamp' => $timestamp,
                'username'  => $entry['username'],
                'statement' => $entry['statement']
            );
        }
        $id++;
    }
    return($tmp_entries);
}

/**
 * Function to get html for data definition and data manipulation statements
 *
 * @param string $url_query    url query
 * @param int    $last_version last version
 *
 * @return string
 */
function PMA_getHtmlForDataDefinitionAndManipulationStatements($url_query,
    $last_version
) {
    $html = '<div id="div_create_version">';
    $html .= '<form method="post" action="tbl_tracking.php?' . $url_query . '">';
    $html .= PMA_URL_getHiddenInputs($GLOBALS['db'], $GLOBALS['table']);
    $html .= '<fieldset>';
    $html .= '<legend>';
    $html .= sprintf(
        __('Create version %1$s of %2$s'),
        ($last_version + 1),
        htmlspecialchars($GLOBALS['db'] . '.' . $GLOBALS['table'])
    );
    $html .= '</legend>';
    $html .= '<input type="hidden" name="version" value="' . ($last_version + 1)
        . '" />';
    $html .= '<p>' . __('Track these data definition statements:')
        . '</p>';
    $html .= '<input type="checkbox" name="alter_table" value="true"'
        . ' checked="checked" /> ALTER TABLE<br/>';
    $html .= '<input type="checkbox" name="rename_table" value="true"'
        . ' checked="checked" /> RENAME TABLE<br/>';
    $html .= '<input type="checkbox" name="create_table" value="true"'
        . ' checked="checked" /> CREATE TABLE<br/>';
    $html .= '<input type="checkbox" name="drop_table" value="true"'
        . ' checked="checked" /> DROP TABLE<br/>';
    $html .= '<br/>';
    $html .= '<input type="checkbox" name="create_index" value="true"'
        . ' checked="checked" /> CREATE INDEX<br/>';
    $html .= '<input type="checkbox" name="drop_index" value="true"'
        . ' checked="checked" /> DROP INDEX<br/>';
    $html .= '<p>' . __('Track these data manipulation statements:') . '</p>';
    $html .= '<input type="checkbox" name="insert" value="true"'
        . ' checked="checked" /> INSERT<br/>';
    $html .= '<input type="checkbox" name="update" value="true"'
        . ' checked="checked" /> UPDATE<br/>';
    $html .= '<input type="checkbox" name="delete" value="true"'
        . ' checked="checked" /> DELETE<br/>';
    $html .= '<input type="checkbox" name="truncate" value="true"'
        . ' checked="checked" /> TRUNCATE<br/>';
    $html .= '</fieldset>';

    $html .= '<fieldset class="tblFooters">';
    $html .= '<input type="hidden" name="submit_create_version" value="1" />';
    $html .= '<input type="submit" value="' . __('Create version') . '" />';
    $html .= '</fieldset>';

    $html .= '</form>';
    $html .= '</div>';

    return $html;
}

/**
 * Function to get html for activate tracking
 *
 * @param string $url_query    url query
 * @param int    $last_version last version
 *
 * @return string
 */
function PMA_getHtmlForActivateTracking($url_query, $last_version)
{
    $html = '<div id="div_activate_tracking">';
    $html .= '<form method="post" action="tbl_tracking.php?' . $url_query . '">';
    $html .= '<fieldset>';
    $html .= '<legend>';
    $html .= sprintf(
        __('Activate tracking for %s'),
        htmlspecialchars($GLOBALS['db'] . '.' . $GLOBALS['table'])
    );
    $html .= '</legend>';
    $html .= '<input type="hidden" name="version" value="' . $last_version . '" />';
    $html .= '<input type="hidden" name="submit_activate_now" value="1" />';
    $html .= '<input type="submit" value="' . __('Activate now') . '" />';
    $html .= '</fieldset>';
    $html .= '</form>';
    $html .= '</div>';

    return $html;
}

/**
 * Function to get html for deactivating tracking
 *
 * @param string $url_query    url query
 * @param int    $last_version last version
 *
 * @return string
 */
function PMA_getHtmlForDeactivateTracking($url_query, $last_version)
{
    $html = '<div id="div_deactivate_tracking">';
    $html .= '<form method="post" action="tbl_tracking.php?' . $url_query . '">';
    $html .= '<fieldset>';
    $html .= '<legend>';
    $html .= sprintf(
        __('Deactivate tracking for %s'),
        htmlspecialchars($GLOBALS['db'] . '.' . $GLOBALS['table'])
    );
    $html .= '</legend>';
    $html .= '<input type="hidden" name="version" value="' . $last_version . '" />';
    $html .= '<input type="hidden" name="submit_deactivate_now" value="1" />';
    $html .= '<input type="submit" value="' . __('Deactivate now') . '" />';
    $html .= '</fieldset>';
    $html .= '</form>';
    $html .= '</div>';

    return $html;
}

/**
 * Function to get the list versions of the table
 *
 * @return array
 */
function PMA_getListOfVersionsOfTable()
{
    $sql_query = " SELECT * FROM " .
         PMA_Util::backquote($GLOBALS['cfg']['Server']['pmadb']) . "." .
         PMA_Util::backquote($GLOBALS['cfg']['Server']['tracking']) .
         " WHERE db_name = '" . PMA_Util::sqlAddSlashes($_REQUEST['db']) . "' " .
         " AND table_name = '" . PMA_Util::sqlAddSlashes($_REQUEST['table']) . "' " .
         " ORDER BY version DESC ";

    return PMA_queryAsControlUser($sql_query);
}

/**
 * Function to get html for displaying last version number
 *
 * @param array  $sql_result   sql result
 * @param int    $last_version last version
 * @param array  $url_params   url parameters
 * @param string $url_query    url query
 *
 * @return string
 */
function PMA_getHtmlForTableVersionDetails($sql_result, $last_version, $url_params,
    $url_query
) {
    $html = '<table id="versions" class="data">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th>' . __('Database') . '</th>';
    $html .= '<th>' . __('Table') . '</th>';
    $html .= '<th>' . __('Version') . '</th>';
    $html .= '<th>' . __('Created') . '</th>';
    $html .= '<th>' . __('Updated') . '</th>';
    $html .= '<th>' . __('Status') . '</th>';
    $html .= '<th>' . __('Show') . '</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    $style = 'odd';
    $GLOBALS['dbi']->dataSeek($sql_result, 0);
    while ($version = $GLOBALS['dbi']->fetchArray($sql_result)) {
        if ($version['tracking_active'] == 1) {
            $version_status = __('active');
        } else {
            $version_status = __('not active');
        }
        if ($version['version'] == $last_version) {
            if ($version['tracking_active'] == 1) {
                $tracking_active = true;
            } else {
                $tracking_active = false;
            }
        }
        $html .= '<tr class="noclick ' . $style . '">';
        $html .= '<td>' . htmlspecialchars($version['db_name']) . '</td>';
        $html .= '<td>' . htmlspecialchars($version['table_name']) . '</td>';
        $html .= '<td>' . htmlspecialchars($version['version']) . '</td>';
        $html .= '<td>' . htmlspecialchars($version['date_created']) . '</td>';
        $html .= '<td>' . htmlspecialchars($version['date_updated']) . '</td>';
        $html .= '<td>' . $version_status . '</td>';
        $html .= '<td><a href="tbl_tracking.php';
        $html .= PMA_URL_getCommon(
            $url_params + array(
                'report' => 'true', 'version' => $version['version']
            )
        );
        $html .= '">' . __('Tracking report') . '</a>';
        $html .= '&nbsp;|&nbsp;';
        $html .= '<a href="tbl_tracking.php';
        $html .= PMA_URL_getCommon(
            $url_params + array(
                'snapshot' => 'true', 'version' => $version['version']
            )
        );
        $html .= '">' . __('Structure snapshot') . '</a>';
        $html .= '</td>';
        $html .= '</tr>';

        if ($style == 'even') {
            $style = 'odd';
        } else {
            $style = 'even';
        }
    }

    $html .= '</tbody>';
    $html .= '</table>';

    if ($tracking_active) {
        $html .= PMA_getHtmlForDeactivateTracking($url_query, $last_version);
    } else {
        $html .= PMA_getHtmlForActivateTracking($url_query, $last_version);
    }

    return $html;
}

/**
 * Function to get the last version number of a table
 *
 * @param array $sql_result sql result
 *
 * @return int
 */
function PMA_getTableLastVersionNumber($sql_result)
{
    $maxversion = $GLOBALS['dbi']->fetchArray($sql_result);
    $last_version = $maxversion['version'];

    return $last_version;
}

/**
 * Function to get sql results for selectable tables
 *
 * @return array
 */
function PMA_getSQLResultForSelectableTables()
{
    include_once 'libraries/relation.lib.php';

    $sql_query = " SELECT DISTINCT db_name, table_name FROM " .
             PMA_Util::backquote($GLOBALS['cfg']['Server']['pmadb']) . "." .
             PMA_Util::backquote($GLOBALS['cfg']['Server']['tracking']) .
             " WHERE db_name = '" . PMA_Util::sqlAddSlashes($GLOBALS['db']) . "' " .
             " ORDER BY db_name, table_name";

    return PMA_queryAsControlUser($sql_query);
}

/**
 * Function to get html for selectable table rows
 *
 * @param array  $selectable_tables_sql_result sql results for selectable rows
 * @param string $url_query                    url query
 *
 * @return string
 */
function PMA_getHtmlForSelectableTables($selectable_tables_sql_result, $url_query)
{
    $html = '<form method="post" action="tbl_tracking.php?' . $url_query . '">';
    $html .= '<select name="table">';
    while ($entries = $GLOBALS['dbi']->fetchArray($selectable_tables_sql_result)) {
        if (PMA_Tracker::isTracked($entries['db_name'], $entries['table_name'])) {
            $status = ' (' . __('active') . ')';
        } else {
            $status = ' (' . __('not active') . ')';
        }
        if ($entries['table_name'] == $_REQUEST['table']) {
            $s = ' selected="selected"';
        } else {
            $s = '';
        }
        $html .= '<option value="' . htmlspecialchars($entries['table_name'])
            . '"' . $s . '>' . htmlspecialchars($entries['db_name']) . ' . '
            . htmlspecialchars($entries['table_name']) . $status . '</option>'
            . "\n";
    }
    $html .= '</select>';
    $html .= '<input type="hidden" name="show_versions_submit" value="1" />';
    $html .= '<input type="submit" value="' . __('Show versions') . '" />';
    $html .= '</form>';

    return $html;
}

/**
 * Function to get html for tracking report and tracking report export
 *
 * @param string  $url_query        url query
 * @param array   $data             data
 * @param array   $url_params       url params
 * @param boolean $selection_schema selection schema
 * @param boolean $selection_data   selection data
 * @param boolean $selection_both   selection both
 * @param int     $filter_ts_to     filter time stamp from
 * @param int     $filter_ts_from   filter time stamp tp
 * @param array   $filter_users     filter users
 *
 * @return string
 */
function PMA_getHtmlForTrackingReport($url_query, $data, $url_params,
    $selection_schema, $selection_data, $selection_both, $filter_ts_to,
    $filter_ts_from, $filter_users
) {
    $html = '<h3>' . __('Tracking report')
        . '  [<a href="tbl_tracking.php?' . $url_query . '">' . __('Close')
        . '</a>]</h3>';

    $html .= '<small>' . __('Tracking statements') . ' '
        . htmlspecialchars($data['tracking']) . '</small><br/>';
    $html .= '<br/>';

    list($str1, $str2, $str3, $str4, $str5) = PMA_getHtmlForElementsOfTrackingReport(
        $selection_schema, $selection_data, $selection_both
    );

    // Prepare delete link content here
    $drop_image_or_text = '';
    if (PMA_Util::showIcons('ActionsLinksMode')) {
        $drop_image_or_text .= PMA_Util::getImage(
            'b_drop.png', __('Delete tracking data row from report')
        );
    }
    if (PMA_Util::showText('ActionLinksMode')) {
        $drop_image_or_text .= __('Delete');
    }

    /*
     *  First, list tracked data definition statements
     */
    if (count($data['ddlog']) == 0 && count($data['dmlog']) == 0) {
        $msg = PMA_Message::notice(__('No data'));
        $msg->display();
    }

    $html .= PMA_getHtmlForTrackingReportExportForm1(
        $data, $url_params, $selection_schema, $selection_data, $selection_both,
        $filter_ts_to, $filter_ts_from, $filter_users, $str1, $str2, $str3,
        $str4, $str5, $drop_image_or_text
    );

    $html .= PMA_getHtmlForTrackingReportExportForm2(
        $url_params, $str1, $str2, $str3, $str4, $str5
    );

    $html .= "<br/><br/><hr/><br/>\n";

    return $html;
}

/**
 * Generate HTML element for report form
 *
 * @param boolean $selection_schema selection schema
 * @param boolean $selection_data   selection data
 * @param boolean $selection_both   selection both
 *
 * @return array
 */
function PMA_getHtmlForElementsOfTrackingReport(
    $selection_schema, $selection_data, $selection_both
) {
    $str1 = '<select name="logtype">'
        . '<option value="schema"'
        . ($selection_schema ? ' selected="selected"' : '') . '>'
        . __('Structure only') . '</option>'
        . '<option value="data"'
        . ($selection_data ? ' selected="selected"' : '') . '>'
        . __('Data only') . '</option>'
        . '<option value="schema_and_data"'
        . ($selection_both ? ' selected="selected"' : '') . '>'
        . __('Structure and data') . '</option>'
        . '</select>';
    $str2 = '<input type="text" name="date_from" value="'
        . htmlspecialchars($_REQUEST['date_from']) . '" size="19" />';
    $str3 = '<input type="text" name="date_to" value="'
        . htmlspecialchars($_REQUEST['date_to']) . '" size="19" />';
    $str4 = '<input type="text" name="users" value="'
        . htmlspecialchars($_REQUEST['users']) . '" />';
    $str5 = '<input type="hidden" name="list_report" value="1" />'
        . '<input type="submit" value="' . __('Go') . '" />';
    return array($str1, $str2, $str3, $str4, $str5);
}

/**
 * Generate HTML for export form
 *
 * @param array   $data               data
 * @param array   $url_params         url params
 * @param boolean $selection_schema   selection schema
 * @param boolean $selection_data     selection data
 * @param boolean $selection_both     selection both
 * @param int     $filter_ts_to       filter time stamp from
 * @param int     $filter_ts_from     filter time stamp tp
 * @param array   $filter_users       filter users
 * @param string  $str1               HTML for logtype select
 * @param string  $str2               HTML for "from date"
 * @param string  $str3               HTML for "to date"
 * @param string  $str4               HTML for user
 * @param string  $str5               HTML for "list report"
 * @param string  $drop_image_or_text HTML for image or text
 *
 * @return string HTML for form
 */
function PMA_getHtmlForTrackingReportExportForm1(
    $data, $url_params, $selection_schema, $selection_data, $selection_both,
    $filter_ts_to, $filter_ts_from, $filter_users, $str1, $str2, $str3,
    $str4, $str5, $drop_image_or_text
) {
    $html = '<form method="post" action="tbl_tracking.php'
        . PMA_URL_getCommon(
            $url_params + array(
                'report' => 'true', 'version' => $_REQUEST['version']
            )
        )
        . '">';

    $html .= sprintf(
        __('Show %1$s with dates from %2$s to %3$s by user %4$s %5$s'),
        $str1, $str2, $str3, $str4, $str5
    );

    if ($selection_schema || $selection_both && count($data['ddlog']) > 0) {
        list($temp, $ddlog_count) = PMA_getHtmlForDataDefinitionStatements(
            $data, $filter_users, $filter_ts_from, $filter_ts_to, $url_params,
            $drop_image_or_text
        );
        $html .= $temp;
        unset($temp);
    } //endif

    /*
     *  Secondly, list tracked data manipulation statements
     */
    if (($selection_data || $selection_both) && count($data['dmlog']) > 0) {
        $html .= PMA_getHtmlForDataManipulationStatements(
            $data, $filter_users, $filter_ts_from, $filter_ts_to, $url_params,
            $ddlog_count, $drop_image_or_text
        );
    }
    $html .= '</form>';
    return $html;
}

/**
 * Generate HTML for export form
 *
 * @param array  $url_params Parameters
 * @param string $str1       HTML for logtype select
 * @param string $str2       HTML for "from date"
 * @param string $str3       HTML for "to date"
 * @param string $str4       HTML for user
 * @param string $str5       HTML for "list report"
 *
 * @return string HTML for form
 */
function PMA_getHtmlForTrackingReportExportForm2(
    $url_params, $str1, $str2, $str3, $str4, $str5
) {
    $html = '<form method="post" action="tbl_tracking.php'
        . PMA_URL_getCommon(
            $url_params + array(
                'report' => 'true', 'version' => $_REQUEST['version']
            )
        )
        . '">';
    $html .= sprintf(
        __('Show %1$s with dates from %2$s to %3$s by user %4$s %5$s'),
        $str1, $str2, $str3, $str4, $str5
    );
    $html .= '</form>';

    $html .= '<form class="disableAjax" method="post" action="tbl_tracking.php'
        . PMA_URL_getCommon(
            $url_params
            + array('report' => 'true', 'version' => $_REQUEST['version'])
        )
        . '">';
    $html .= '<input type="hidden" name="logtype" value="'
        . htmlspecialchars($_REQUEST['logtype']) . '" />';
    $html .= '<input type="hidden" name="date_from" value="'
        . htmlspecialchars($_REQUEST['date_from']) . '" />';
    $html .= '<input type="hidden" name="date_to" value="'
        . htmlspecialchars($_REQUEST['date_to']) . '" />';
    $html .= '<input type="hidden" name="users" value="'
        . htmlspecialchars($_REQUEST['users']) . '" />';

    $str_export1 = '<select name="export_type">'
        . '<option value="sqldumpfile">' . __('SQL dump (file download)')
        . '</option>'
        . '<option value="sqldump">' . __('SQL dump') . '</option>'
        . '<option value="execution" onclick="alert(\''
        . PMA_escapeJsString(
            __('This option will replace your table and contained data.')
        )
        . '\')">' . __('SQL execution') . '</option>' . '</select>';

    $str_export2 = '<input type="hidden" name="report_export" value="1" />'
        . '<input type="submit" value="' . __('Go') . '" />';

    $html .= "<br/>" . sprintf(__('Export as %s'), $str_export1)
        . $str_export2 . "<br/>";
    $html .= '</form>';
    return $html;
}

/**
 * Function to get html for data manipulation statements
 *
 * @param array  $data               data
 * @param array  $filter_users       filter users
 * @param int    $filter_ts_from     filter time staml from
 * @param int    $filter_ts_to       filter time stamp to
 * @param array  $url_params         url parameters
 * @param int    $ddlog_count        data definition log count
 * @param string $drop_image_or_text drop image or text
 *
 * @return string
 */
function PMA_getHtmlForDataManipulationStatements($data, $filter_users,
    $filter_ts_from, $filter_ts_to, $url_params, $ddlog_count,
    $drop_image_or_text
) {
    $i = $ddlog_count;
    $html = '<table id="dml_versions" class="data" width="100%">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th width="18">#</th>';
    $html .= '<th width="100">' . __('Date') . '</th>';
    $html .= '<th width="60">' . __('Username') . '</th>';
    $html .= '<th>' . __('Data manipulation statement') . '</th>';
    $html .= '<th>' . __('Delete') . '</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    $style = 'odd';
    foreach ($data['dmlog'] as $entry) {
        $html .= PMA_getHtmlForDataManipulationStatement(
            $entry, $filter_users, $filter_ts_from, $filter_ts_to, $style, $i,
            $url_params, $ddlog_count, $drop_image_or_text
        );
        if ($style == 'even') {
            $style = 'odd';
        } else {
            $style = 'even';
        }
        $i++;
    }
    $html .= '</tbody>';
    $html .= '</table>';

    return $html;
}

/**
 * Function to get html for one data manipulation statement
 *
 * @param array  $entry              entry
 * @param array  $filter_users       filter users
 * @param int    $filter_ts_from     filter time stamp from
 * @param int    $filter_ts_to       filter time stamp to
 * @param string $style              style
 * @param int    $i                  field number
 * @param array  $url_params         url parameters
 * @param int    $ddlog_count        data definition log count
 * @param string $drop_image_or_text drop image or text
 *
 * @return string
 */
function PMA_getHtmlForDataManipulationStatement($entry, $filter_users,
    $filter_ts_from, $filter_ts_to, $style, $i, $url_params, $ddlog_count,
    $drop_image_or_text
) {
    $statement  = PMA_Util::formatSql($entry['statement'], true);
    $timestamp = strtotime($entry['date']);
    $filtered_user = in_array($entry['username'], $filter_users);
    $html = null;

    if ($timestamp >= $filter_ts_from
        && $timestamp <= $filter_ts_to
        && (in_array('*', $filter_users) || $filtered_user)
    ) {
        $html = '<tr class="noclick ' . $style . '">';
        $html .= '<td><small>' . $i . '</small></td>';
        $html .= '<td><small>'
            . htmlspecialchars($entry['date']) . '</small></td>';
        $html .= '<td><small>'
            . htmlspecialchars($entry['username']) . '</small></td>';
        $html .= '<td>' . $statement . '</td>';
        $html .= '<td class="nowrap"><a href="tbl_tracking.php?'
            . PMA_URL_getCommon(
                $url_params + array(
                    'report' => 'true',
                    'version' => $_REQUEST['version'],
                    'delete_dmlog' => ($i - $ddlog_count),
                )
            )
            . '">'
            . $drop_image_or_text
            . '</a></td>';
        $html .= '</tr>';
    }

    return $html;
}
/**
 * Function to get html for data definition statements in schema snapshot
 *
 * @param array  $data               data
 * @param array  $filter_users       filter users
 * @param int    $filter_ts_from     filter time stamp from
 * @param int    $filter_ts_to       filter time stamp to
 * @param array  $url_params         url parameters
 * @param string $drop_image_or_text drop image or text
 *
 * @return string
 */
function PMA_getHtmlForDataDefinitionStatements($data, $filter_users,
    $filter_ts_from, $filter_ts_to, $url_params, $drop_image_or_text
) {
    $i = 1;
    $html  = '<table id="ddl_versions" class="data" width="100%">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th width="18">#</th>';
    $html .= '<th width="100">' . __('Date') . '</th>';
    $html .= '<th width="60">' . __('Username') . '</th>';
    $html .= '<th>' . __('Data definition statement') . '</th>';
    $html .= '<th>' . __('Delete') . '</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    $style = 'odd';
    foreach ($data['ddlog'] as $entry) {
        $html .= PMA_getHtmlForDataDefinitionStatement(
            $entry, $filter_users, $filter_ts_from, $filter_ts_to, $style, $i,
            $url_params, $drop_image_or_text
        );
        if ($style == 'even') {
            $style = 'odd';
        } else {
            $style = 'even';
        }
        $i++;
    }
    $html .= '</tbody>';
    $html .= '</table>';

    return array($html, $i);
}
/**
 * Function to get html for a data definition statement in schema snapshot
 *
 * @param array  $entry              entry
 * @param array  $filter_users       filter users
 * @param int    $filter_ts_from     filter time stamp from
 * @param int    $filter_ts_to       filter time stamp to
 * @param string $style              style
 * @param int    $i                  column number
 * @param array  $url_params         url parameters
 * @param string $drop_image_or_text drop image or text
 *
 * @return string
 */
function PMA_getHtmlForDataDefinitionStatement($entry, $filter_users,
    $filter_ts_from, $filter_ts_to, $style, $i, $url_params, $drop_image_or_text
) {
    $statement  = PMA_Util::formatSql($entry['statement'], true);
    $timestamp = strtotime($entry['date']);
    $filtered_user = in_array($entry['username'], $filter_users);
    $html = null;

    if ($timestamp >= $filter_ts_from
        && $timestamp <= $filter_ts_to
        && (in_array('*', $filter_users) || $filtered_user)
    ) {
        $html = '<tr class="noclick ' . $style . '">';
        $html .= '<td><small>' . $i . '</small></td>';
        $html .= '<td><small>'
            . htmlspecialchars($entry['date']) . '</small></td>';
        $html .= '<td><small>'
            . htmlspecialchars($entry['username']) . '</small></td>';
        $html .= '<td>' . $statement . '</td>';
        $html .= '<td class="nowrap"><a href="tbl_tracking.php'
            . PMA_URL_getCommon(
                $url_params + array(
                    'report' => 'true',
                    'version' => $_REQUEST['version'],
                    'delete_ddlog' => ($i - 1),
                )
            )
            . '">' . $drop_image_or_text
            . '</a></td>';
        $html .= '</tr>';
    }

    return $html;
}
/**
 * Function to get html for schema snapshot
 *
 * @param string $url_query url query
 *
 * @return string
 */
function PMA_getHtmlForSchemaSnapshot($url_query)
{
    $html = '<h3>' . __('Structure snapshot')
        . '  [<a href="tbl_tracking.php?' . $url_query . '">' . __('Close')
        . '</a>]</h3>';
    $data = PMA_Tracker::getTrackedData(
        $_REQUEST['db'], $_REQUEST['table'], $_REQUEST['version']
    );

    // Get first DROP TABLE/VIEW and CREATE TABLE/VIEW statements
    $drop_create_statements = $data['ddlog'][0]['statement'];

    if (strstr($data['ddlog'][0]['statement'], 'DROP TABLE')
        || strstr($data['ddlog'][0]['statement'], 'DROP VIEW')
    ) {
        $drop_create_statements .= $data['ddlog'][1]['statement'];
    }
    // Print SQL code
    $html .= PMA_Util::getMessage(
        sprintf(
            __('Version %s snapshot (SQL code)'),
            htmlspecialchars($_REQUEST['version'])
        ),
        $drop_create_statements
    );

    // Unserialize snapshot
    $temp = unserialize($data['schema_snapshot']);
    $columns = $temp['COLUMNS'];
    $indexes = $temp['INDEXES'];
    $html .= PMA_getHtmlForColumns($columns);

    if (count($indexes) > 0) {
        $html .= PMA_getHtmlForIndexes($indexes);
    } // endif
    $html .= '<br /><hr /><br />';

    return $html;
}

/**
 * Function to get html for displaying columns in the schema snapshot
 *
 * @param array $columns columns
 *
 * @return string
 */
function PMA_getHtmlForColumns($columns)
{
    $html = '<h3>' . __('Structure') . '</h3>';
    $html .= '<table id="tablestructure" class="data">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th>' . __('Column') . '</th>';
    $html .= '<th>' . __('Type') . '</th>';
    $html .= '<th>' . __('Collation') . '</th>';
    $html .= '<th>' . __('Null') . '</th>';
    $html .= '<th>' . __('Default') . '</th>';
    $html .= '<th>' . __('Extra') . '</th>';
    $html .= '<th>' . __('Comment') . '</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';
    $style = 'odd';
    foreach ($columns as $field) {
        $html .= PMA_getHtmlForField($field, $style);
        if ($style == 'even') {
            $style = 'odd';
        } else {
            $style = 'even';
        }
    }

    $html .= '</tbody>';
    $html .= '</table>';

    return $html;
}

/**
 * Function to get html for field
 *
 * @param array  $field field
 * @param string $style style
 *
 * @return string
 */
function PMA_getHtmlForField($field, $style)
{
    $html = '<tr class="noclick ' . $style . '">';
    if ($field['Key'] == 'PRI') {
        $html .= '<td><b><u>' . htmlspecialchars($field['Field']) . '</u></b></td>';
    } else {
        $html .= '<td><b>' . htmlspecialchars($field['Field']) . '</b></td>';
    }
    $html .= "\n";
    $html .= '<td>' . htmlspecialchars($field['Type']) . '</td>';
    $html .= '<td>' . htmlspecialchars($field['Collation']) . '</td>';
    $html .= '<td>' . (($field['Null'] == 'YES') ? __('Yes') : __('No')) . '</td>';
    $html .= '<td>';
    if (isset($field['Default'])) {
        $extracted_columnspec = PMA_Util::extractColumnSpec($field['Type']);
        if ($extracted_columnspec['type'] == 'bit') {
            // here, $field['Default'] contains something like b'010'
            $html .= PMA_Util::convertBitDefaultValue($field['Default']);
        } else {
            $html .= htmlspecialchars($field['Default']);
        }
    } else {
        if ($field['Null'] == 'YES') {
            $html .= '<i>NULL</i>';
        } else {
            $html .= '<i>' . _pgettext('None for default', 'None') . '</i>';
        }
    }
    $html .= '</td>';
    $html .= '<td>' . htmlspecialchars($field['Extra']) . '</td>';
    $html .= '<td>' . htmlspecialchars($field['Comment']) . '</td>';
    $html .= '</tr>';

    return $html;
}

/**
 * Fuunction to get html for the indexes in schema snapshot
 *
 * @param array $indexes indexes
 *
 * @return string
 */
function PMA_getHtmlForIndexes($indexes)
{
    $html = '<h3>' . __('Indexes') . '</h3>';
    $html .= '<table id="tablestructure_indexes" class="data">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th>' . __('Keyname') . '</th>';
    $html .= '<th>' . __('Type') . '</th>';
    $html .= '<th>' . __('Unique') . '</th>';
    $html .= '<th>' . __('Packed') . '</th>';
    $html .= '<th>' . __('Column') . '</th>';
    $html .= '<th>' . __('Cardinality') . '</th>';
    $html .= '<th>' . __('Collation') . '</th>';
    $html .= '<th>' . __('Null') . '</th>';
    $html .= '<th>' . __('Comment') . '</th>';
    $html .= '</tr>';
    $html .= '<tbody>';

    $style = 'odd';
    foreach ($indexes as $index) {
        $html .= PMA_getHtmlForIndex($index, $style);
        if ($style == 'even') {
            $style = 'odd';
        } else {
            $style = 'even';
        }
    }
    $html .= '</tbody>';
    $html .= '</table>';
    return $html;
}

/**
 * Funtion to get html for an index in schema snapshot
 *
 * @param array  $index index
 * @param string $style style
 *
 * @return string
 */
function PMA_getHtmlForIndex($index, $style)
{
    if ($index['Non_unique'] == 0) {
        $str_unique = __('Yes');
    } else {
        $str_unique = __('No');
    }
    if ($index['Packed'] != '') {
        $str_packed = __('Yes');
    } else {
        $str_packed = __('No');
    }

    $html  = '<tr class="noclick ' . $style . '">';
    $html .= '<td><b>' . htmlspecialchars($index['Key_name']) . '</b></td>';
    $html .= '<td>' . htmlspecialchars($index['Index_type']) . '</td>';
    $html .= '<td>' . $str_unique . '</td>';
    $html .= '<td>' . $str_packed . '</td>';
    $html .= '<td>' . htmlspecialchars($index['Column_name']) . '</td>';
    $html .= '<td>' . htmlspecialchars($index['Cardinality']) . '</td>';
    $html .= '<td>' . htmlspecialchars($index['Collation']) . '</td>';
    $html .= '<td>' . htmlspecialchars($index['Null']) . '</td>';
    $html .= '<td>' . htmlspecialchars($index['Comment']) . '</td>';
    $html .= '</tr>';

    return $html;
}

/**
 * Function to handle the tracking report
 *
 * @param array &$data tracked data
 *
 * @return void
 */
function PMA_deleteTrackingReportRows(&$data)
{
    if (isset($_REQUEST['delete_ddlog'])) {
        // Delete ddlog row data
        PMA_handleDeleteDataDefinitionsLog($data);
    }

    if (isset($_REQUEST['delete_dmlog'])) {
        // Delete dmlog row data
        PMA_handleDeleteDataManipulationLog($data);
    }
}

/**
 * Function to handle the delete ddlog row data
 *
 * @param array &$data tracked data
 *
 * @return void
 */
function PMA_handleDeleteDataDefinitionsLog(&$data)
{
    $delete_id = $_REQUEST['delete_ddlog'];

    // Only in case of valable id
    if ($delete_id == (int)$delete_id) {
        unset($data['ddlog'][$delete_id]);

        $successfullyDeleted = PMA_Tracker::changeTrackingData(
            $_REQUEST['db'], $_REQUEST['table'],
            $_REQUEST['version'], 'DDL', $data['ddlog']
        );
        if ($successfullyDeleted) {
            $msg = PMA_Message::success(
                __('Tracking data definition successfully deleted')
            );
        } else {
            $msg = PMA_Message::rawError(__('Query error'));
        }
        $msg->display();
    }
}

/**
 * Function to handle the delete of fmlog rows
 *
 * @param array &$data tracked data
 *
 * @return void
 */
function PMA_handleDeleteDataManipulationLog(&$data)
{
    $delete_id = $_REQUEST['delete_dmlog'];

    // Only in case of valable id
    if ($delete_id == (int)$delete_id) {
        unset($data['dmlog'][$delete_id]);

        $successfullyDeleted = PMA_Tracker::changeTrackingData(
            $_REQUEST['db'], $_REQUEST['table'],
            $_REQUEST['version'], 'DML', $data['dmlog']
        );
        if ($successfullyDeleted) {
            $msg = PMA_Message::success(
                __('Tracking data manipulation successfully deleted')
            );
        } else {
            $msg = PMA_Message::rawError(__('Query error'));
        }
        $msg->display();
    }
}

/**
 * Function to export as sql dump
 *
 * @param array $entries entries
 *
 * @return void
 */
function PMA_exportAsSQLDump($entries)
{
    $new_query = "# "
        . __(
            'You can execute the dump by creating and using a temporary database. '
            . 'Please ensure that you have the privileges to do so.'
        )
        . "\n"
        . "# " . __('Comment out these two lines if you do not need them.') . "\n"
        . "\n"
        . "CREATE database IF NOT EXISTS pma_temp_db; \n"
        . "USE pma_temp_db; \n"
        . "\n";

    foreach ($entries as $entry) {
        $new_query .= $entry['statement'];
    }
    $msg = PMA_Message::success(
        __('SQL statements exported. Please copy the dump or execute it.')
    );
    $msg->display();

    $db_temp = $GLOBALS['db'];
    $table_temp = $GLOBALS['table'];

    $GLOBALS['db'] = $GLOBALS['table'] = '';
    include_once './libraries/sql_query_form.lib.php';

    PMA_getHtmlForSqlQueryForm($new_query, 'sql');

    $GLOBALS['db'] = $db_temp;
    $GLOBALS['table'] = $table_temp;
}

/**
 * Function to export as sql execution
 *
 * @param array $entries entries
 *
 * @return array
 */
function PMA_exportAsSQLExecution($entries)
{
    foreach ($entries as $entry) {
        $sql_result = $GLOBALS['dbi']->query("/*NOTRACK*/\n" . $entry['statement']);
    }
    $msg = PMA_Message::success(__('SQL statements executed.'));
    $msg->display();

    return $sql_result;
}

/**
 * Function to export as entries
 *
 * @param array $entries entries
 *
 * @return void
 */
function PMA_exportAsFileDownload($entries)
{
    @ini_set('url_rewriter.tags', '');

    $dump = "# " . sprintf(
        __('Tracking report for table `%s`'), htmlspecialchars($_REQUEST['table'])
    )
    . "\n" . "# " . date('Y-m-d H:i:s') . "\n";
    foreach ($entries as $entry) {
        $dump .= $entry['statement'];
    }
    $filename = 'log_' . htmlspecialchars($_REQUEST['table']) . '.sql';
    PMA_downloadHeader($filename, 'text/x-sql', strlen($dump));

    $response = PMA_Response::getInstance();
    $response->addHTML($dump);

    exit();
}

/**
 * Function to activate tracking
 *
 * @return void
 */
function PMA_activateTracking()
{
    $activated = PMA_Tracker::activateTracking(
        $GLOBALS['db'], $GLOBALS['table'], $_REQUEST['version']
    );
    if ($activated) {
        $msg = PMA_Message::success(
            sprintf(
                __('Tracking for %1$s was activated at version %2$s.'),
                htmlspecialchars($GLOBALS['db'] . '.' . $GLOBALS['table']),
                htmlspecialchars($_REQUEST['version'])
            )
        );
        $msg->display();
    }
}

/**
 * Function to deactivate tracking
 *
 * @return void
 */
function PMA_deactivateTracking()
{
    $deactivated = PMA_Tracker::deactivateTracking(
        $GLOBALS['db'], $GLOBALS['table'], $_REQUEST['version']
    );
    if ($deactivated) {
        $msg = PMA_Message::success(
            sprintf(
                __('Tracking for %1$s was deactivated at version %2$s.'),
                htmlspecialchars($GLOBALS['db'] . '.' . $GLOBALS['table']),
                htmlspecialchars($_REQUEST['version'])
            )
        );
        $msg->display();
    }
}

/**
 * Function to get tracking set
 *
 * @return string
 */
function PMA_getTrackingSet()
{
    $tracking_set = '';

    if ($_REQUEST['alter_table'] == true) {
        $tracking_set .= 'ALTER TABLE,';
    }
    if ($_REQUEST['rename_table'] == true) {
        $tracking_set .= 'RENAME TABLE,';
    }
    if ($_REQUEST['create_table'] == true) {
        $tracking_set .= 'CREATE TABLE,';
    }
    if ($_REQUEST['drop_table'] == true) {
        $tracking_set .= 'DROP TABLE,';
    }
    if ($_REQUEST['create_index'] == true) {
        $tracking_set .= 'CREATE INDEX,';
    }
    if ($_REQUEST['drop_index'] == true) {
        $tracking_set .= 'DROP INDEX,';
    }
    if ($_REQUEST['insert'] == true) {
        $tracking_set .= 'INSERT,';
    }
    if ($_REQUEST['update'] == true) {
        $tracking_set .= 'UPDATE,';
    }
    if ($_REQUEST['delete'] == true) {
        $tracking_set .= 'DELETE,';
    }
    if ($_REQUEST['truncate'] == true) {
        $tracking_set .= 'TRUNCATE,';
    }
    $tracking_set = rtrim($tracking_set, ',');

    return $tracking_set;
}

/**
 * Function to create the tracking version
 *
 * @return void
 */
function PMA_createTrackingVersion()
{
    $tracking_set = PMA_getTrackingSet();

    $versionCreated = PMA_Tracker::createVersion(
        $GLOBALS['db'],
        $GLOBALS['table'],
        $_REQUEST['version'],
        $tracking_set,
        PMA_Table::isView($GLOBALS['db'], $GLOBALS['table'])
    );
    if ($versionCreated) {
        $msg = PMA_Message::success(
            sprintf(
                __('Version %1$s was created, tracking for %2$s is active.'),
                htmlspecialchars($_REQUEST['version']),
                htmlspecialchars($GLOBALS['db'] . '.' . $GLOBALS['table'])
            )
        );
        $msg->display();
    }
}

/**
 * Function to get the entries
 *
 * @param array $data           data
 * @param int   $filter_ts_from filter time stamp from
 * @param int   $filter_ts_to   filter time stamp to
 * @param array $filter_users   filter users
 *
 * @return array
 */
function PMA_getEntries($data, $filter_ts_from, $filter_ts_to, $filter_users)
{
    $entries = array();
    // Filtering data definition statements
    if ($_REQUEST['logtype'] == 'schema'
        || $_REQUEST['logtype'] == 'schema_and_data'
    ) {
        $entries = array_merge(
            $entries,
            PMA_filterTracking(
                $data['ddlog'], $filter_ts_from, $filter_ts_to, $filter_users
            )
        );
    }

    // Filtering data manipulation statements
    if ($_REQUEST['logtype'] == 'data'
        || $_REQUEST['logtype'] == 'schema_and_data'
    ) {
        $entries = array_merge(
            $entries,
            PMA_filterTracking(
                $data['dmlog'], $filter_ts_from, $filter_ts_to, $filter_users
            )
        );
    }

    // Sort it
    $ids = $timestamps = $usernames = $statements = array();
    foreach ($entries as $key => $row) {
        $ids[$key]        = $row['id'];
        $timestamps[$key] = $row['timestamp'];
        $usernames[$key]  = $row['username'];
        $statements[$key] = $row['statement'];
    }

    array_multisort(
        $timestamps, SORT_ASC, $ids, SORT_ASC, $usernames,
        SORT_ASC, $statements, SORT_ASC, $entries
    );

    return $entries;
}
?>
