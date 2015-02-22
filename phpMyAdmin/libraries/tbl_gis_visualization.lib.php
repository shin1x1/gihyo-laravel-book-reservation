<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Functions used to generate GIS visualizations.
 *
 * @package PhpMyAdmin
 */
if (!defined('PHPMYADMIN')) {
    exit;
}

require_once 'libraries/sql.lib.php';

/**
 * Returns a modified sql query with only the label column
 * and spatial column(wrapped with 'ASTEXT()' function).
 *
 * @param string $sql_query             original sql query
 * @param array  $visualizationSettings settings for the visualization
 *
 * @return string the modified sql query.
 */
function PMA_GIS_modifyQuery($sql_query, $visualizationSettings)
{
    $modified_query = 'SELECT ';
    // If label column is chosen add it to the query
    if (! empty($visualizationSettings['labelColumn'])) {
        $modified_query .= PMA_Util::backquote($visualizationSettings['labelColumn'])
            . ', ';
    }
    // Wrap the spatial column with 'ASTEXT()' function and add it
    $modified_query .= 'ASTEXT('
        . PMA_Util::backquote($visualizationSettings['spatialColumn'])
        . ') AS ' . PMA_Util::backquote($visualizationSettings['spatialColumn'])
        . ', ';

    // Get the SRID
    $modified_query .= 'SRID('
        . PMA_Util::backquote($visualizationSettings['spatialColumn'])
        . ') AS ' . PMA_Util::backquote('srid') . ' ';

    // Append the original query as the inner query
    $modified_query .= 'FROM (' . $sql_query . ') AS '
        . PMA_Util::backquote('temp_gis');

    return $modified_query;
}

/**
 * Formats a visualization for the GIS query results.
 *
 * @param array  $data                   Data for the status chart
 * @param array  &$visualizationSettings Settings used to generate the chart
 * @param string $format                 Format of the visulaization
 *
 * @return string|void HTML and JS code for the GIS visualization
 */
function PMA_GIS_visualizationResults($data, &$visualizationSettings, $format)
{
    include_once './libraries/gis/GIS_Visualization.class.php';
    include_once './libraries/gis/GIS_Factory.class.php';

    if (! isset($data[0])) {
        // empty data
        return __('No data found for GIS visualization.');
    } else {
        $visualization = new PMA_GIS_Visualization($data, $visualizationSettings);
        if ($visualizationSettings != null) {
            foreach ($visualization->getSettings() as $setting => $val) {
                if (! isset($visualizationSettings[$setting])) {
                    $visualizationSettings[$setting] = $val;
                }
            }
        }
        if ($format == 'svg') {
            return $visualization->asSvg();
        } elseif ($format == 'png') {
            return $visualization->asPng();
        } elseif ($format == 'ol') {
            return $visualization->asOl();
        }
    }
}

/**
 * Generate visualization for the GIS query results and save it to a file.
 *
 * @param array  $data                  data for the status chart
 * @param array  $visualizationSettings settings used to generate the chart
 * @param string $format                format of the visulaization
 * @param string $fileName              file name
 *
 * @return file File containing the visualization
 */
function PMA_GIS_saveToFile($data, $visualizationSettings, $format, $fileName)
{
    include_once './libraries/gis/GIS_Visualization.class.php';
    include_once './libraries/gis/GIS_Factory.class.php';

    if (isset($data[0])) {
        $visualization = new PMA_GIS_Visualization($data, $visualizationSettings);

        if ($format == 'svg') {
            $visualization->toFileAsSvg($fileName);
        } elseif ($format == 'png') {
            $visualization->toFileAsPng($fileName);
        } elseif ($format == 'pdf') {
            $visualization->toFileAsPdf($fileName);
        }
    }
}

/**
 * Function to get html for the lebel column and spatial column
 *
 * @param string $column                the column type. i.e either "labelColumn"
 *                                      or "spatialColumn"
 * @param array  $columnCandidates      the list of select options
 * @param array  $visualizationSettings visualization settings
 *
 * @return string  $html
 */
function PMA_getHtmlForColumn($column, $columnCandidates, $visualizationSettings)
{
    $html = '<tr><td><label for="labelColumn">';
    $html .= ($column=="labelColumn") ? __("Label column") : __("Spatial column");
    $html .= '</label></td>';

    $html .= '<td><select name="visualizationSettings[' . $column . ']" id="'
        . $column . '">';

    if ($column == "labelColumn") {
        $html .= '<option value="">' . __("-- None --") . '</option>';
    }

    $html .= PMA_getHtmlForOptionsList(
        $columnCandidates, array($visualizationSettings[$column])
    );

    $html .= '</select></td>';
    $html .= '</tr>';

    return $html;
}

/**
 * Function to get HTML for the option of using open street maps
 *
 * @param boolean $isSelected the default value
 *
 * @return string HTML string
 */
function PMA_getHtmlForUseOpenStreetMaps($isSelected)
{
    $html = '<tr><td class="choice" colspan="2">';
    $html .= '<input type="checkbox" name="visualizationSettings[choice]"'
        . 'id="choice" value="useBaseLayer"';
    if ($isSelected) {
        $html .= ' checked="checked"';
    }
    $html .= '/>';
    $html .= '<label for="choice">';
    $html .= __("Use OpenStreetMaps as Base Layer");
    $html .= '</label>';
    $html .= '</td></tr>';

    return $html;
}

/**
 * Function to generate HTML for the GIS visualization page
 *
 * @param array   $url_params            url parameters
 * @param array   $labelCandidates       list of candidates for the label
 * @param array   $spatialCandidates     list of candidates for the spatial column
 * @param array   $visualizationSettings visualization settings
 * @param String  $sql_query             the sql query
 * @param String  $visualization         HTML and js code for the visualization
 * @param boolean $svg_support           whether svg download format is supported
 * @param array   $data                  array of visualizing data
 *
 * @return string HTML code for the GIS visualization
 */
function PMA_getHtmlForGisVisualization(
    $url_params, $labelCandidates, $spatialCandidates, $visualizationSettings,
    $sql_query, $visualization, $svg_support, $data
) {
    $html = '<div id="div_view_options">';
    $html .= '<fieldset>';
    $html .= '<legend>' . __('Display GIS Visualization') . '</legend>';

    $html .= '<div style="width: 400px; float: left;">';
    $html .= '<form method="post" action="tbl_gis_visualization.php">';
    $html .= PMA_URL_getHiddenInputs($url_params);
    $html .= '<table class="gis_table">';

    $html .= PMA_getHtmlForColumn(
        "labelColumn", $labelCandidates, $visualizationSettings
    );

    $html .= PMA_getHtmlForColumn(
        "spatialColumn", $spatialCandidates, $visualizationSettings
    );

    $html .= '<tr><td></td>';
    $html .= '<td class="button"><input type="submit"';
    $html .= ' name="displayVisualizationBtn" value="';
    $html .= __('Redraw');
    $html .= '" /></td></tr>';

    if (! $GLOBALS['PMA_Config']->isHttps()) {
        $isSelected = isset($visualizationSettings['choice']) ? true : false;
        $html .= PMA_getHtmlForUseOpenStreetMaps($isSelected);
    }

    $html .= '</table>';
    $html .= '<input type="hidden" name="displayVisualization" value="redraw">';
    $html .= '<input type="hidden" name="sql_query" value="';
    $html .= htmlspecialchars($sql_query) . '" />';
    $html .= '</form>';
    $html .= '</div>';

    $html .= '<div  style="float:left;">';
    $html .= '<form method="post" class="disableAjax"';
    $html .= ' action="tbl_gis_visualization.php">';
    $html .= PMA_URL_getHiddenInputs($url_params);
    $html .= '<table class="gis_table">';
    $html .= '<tr><td><label for="fileName">';
    $html .= __("File name") . '</label></td>';
    $html .= '<td><input type="text" name="fileName" id="fileName" /></td></tr>';

    $html .= '<tr><td><label for="fileFormat">';
    $html .= __("Format") . '</label></td>';
    $html .= '<td><select name="fileFormat" id="fileFormat">';
    $html .= '<option value="png">PNG</option>';
    $html .= '<option value="pdf">PDF</option>';

    if ($svg_support) {
        $html .= '<option value="svg" selected="selected">SVG</option>';
    }
    $html .= '</select></td></tr>';

    $html .= '<tr><td></td>';
    $html .= '<td class="button"><input type="submit" name="saveToFileBtn" value="';
    $html .= __('Download') . '" /></td></tr>';
    $html .= '</table>';

    $html .= '<input type="hidden" name="saveToFile" value="download">';
    $html .= '<input type="hidden" name="sql_query" value="';
    $html .= htmlspecialchars($sql_query) . '" />';
    $html .= '</form>';
    $html .= '</div>';

    $html .= '<div style="clear:both;">&nbsp;</div>';

    $html .= '<div id="placeholder" style="width:';
    $html .= htmlspecialchars($visualizationSettings['width']) . 'px;height:';
    $html .= htmlspecialchars($visualizationSettings['height']) . 'px;">';
    $html .= $visualization;
    $html .= '</div>';

    $html .= '<div id="openlayersmap"></div>';
    $html .= '<input type="hidden" id="pmaThemeImage" value="';
    $html .= $GLOBALS['pmaThemeImage'] . '" />';
    $html .= '<script language="javascript" type="text/javascript">';
    $html .= 'function drawOpenLayers()';
    $html .= '{';

    if (! $GLOBALS['PMA_Config']->isHttps()) {
        $html .= PMA_GIS_visualizationResults($data, $visualizationSettings, 'ol');
    }
    $html .= '}';
    $html .= '</script>';
    $html .= '</fieldset>';
    $html .= '</div>';

    return $html;
}
?>
