<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * PDF export for PMD
 *
 * @package PhpMyAdmin-Designer
 */

require_once './libraries/common.inc.php';
require_once 'libraries/pmd_common.php';

/**
 * Validate vulnerable POST parameters
 */
if (isset($_POST['scale']) && ! PMA_isValid($_POST['scale'], 'numeric')) {
    die('Attack stopped');
}

/**
  * Sets globals from $_POST
  */
$post_params = array(
    'db'
);

foreach ($post_params as $one_post_param) {
    if (isset($_POST[$one_post_param])) {
        $GLOBALS[$one_post_param] = $_POST[$one_post_param];
    }
}

/**
 * If called directly from the designer, first save the positions
 */
if (! isset($_POST['scale'])) {
    include_once 'pmd_save_pos.php';
}

if (isset($_POST['mode'])) {
    if ('create_export' != $_POST['mode'] && empty($_POST['pdf_page_number'])) {
        die("<script>alert('Pages not found!');history.go(-2);</script>");
    }

    $pmd_table = PMA_Util::backquote($GLOBALS['cfgRelation']['db']) . '.'
        . PMA_Util::backquote($GLOBALS['cfgRelation']['designer_coords']);
    $pma_table = PMA_Util::backquote($GLOBALS['cfgRelation']['db']) . '.'
        . PMA_Util::backquote($cfgRelation['table_coords']);
    $scale_q = PMA_Util::sqlAddSlashes($_POST['scale']);

    if ('create_export' == $_POST['mode']) {
        $pdf_page_number = PMA_REL_createPage($_POST['newpage'], $cfgRelation, $db);
        if ($pdf_page_number > 0) {
            $message = PMA_Message::success(__('Page has been created.'));
            $_POST['mode'] = 'export';
        } else {
            $message = PMA_Message::error(__('Page creation has failed!'));
        }
    } else {
        $pdf_page_number = $_POST['pdf_page_number'];
    }

    $pdf_page_number_q = PMA_Util::sqlAddSlashes($pdf_page_number);

    if ('export' == $_POST['mode']) {
        $sql = "REPLACE INTO " . $pma_table
            . " (db_name, table_name, pdf_page_number, x, y)"
            . " SELECT db_name, table_name, " . $pdf_page_number_q . ","
            . " ROUND(x/" . $scale_q . ") , ROUND(y/" . $scale_q . ") y"
            . " FROM " . $pmd_table
            . " WHERE db_name = '" . PMA_Util::sqlAddSlashes($db) . "'";

        PMA_queryAsControlUser($sql, true, PMA_DatabaseInterface::QUERY_STORE);
    }

    if ('import' == $_POST['mode']) {
        PMA_queryAsControlUser(
            'UPDATE ' . $pma_table . ',' . $pmd_table .
            ' SET ' . $pmd_table . '.`x`= ' . $pma_table . '.`x` * ' . $scale_q . ',
            ' . $pmd_table . '.`y`= ' . $pma_table . '.`y` * ' . $scale_q . '
            WHERE
            ' . $pmd_table . '.`db_name`=' . $pma_table . '.`db_name`
            AND
            ' . $pmd_table . '.`table_name` = ' . $pma_table . '.`table_name`
            AND
            ' . $pmd_table . '.`db_name`=\'' . PMA_Util::sqlAddSlashes($db) . '\'
            AND pdf_page_number = ' . $pdf_page_number_q . ';',
            true, PMA_DatabaseInterface::QUERY_STORE
        );
    }
}

$response = PMA_Response::getInstance();
$response->getFooter()->setMinimal();

echo '<br/>';
echo '<div>';
if (! empty($message)) {
    $message->display();
}
echo '<form name="form1" method="post" action="pmd_pdf.php">';
echo PMA_URL_getHiddenInputs($db);
echo '<div>';
echo '<fieldset><legend>' . __('Import/Export coordinates for relational schema') . '</legend>';

$choices = array();

$table_info_result = PMA_queryAsControlUser(
    'SELECT * FROM ' . PMA_Util::backquote($GLOBALS['cfgRelation']['db'])
    . '.' . PMA_Util::backquote($cfgRelation['pdf_pages'])
    . ' WHERE db_name = \'' . PMA_Util::sqlAddSlashes($db) . '\''
);

if ($GLOBALS['dbi']->numRows($table_info_result) > 0) {
    echo '<p>' . __('Page:');
    echo '<select name="pdf_page_number">';

    while ($page = $GLOBALS['dbi']->fetchAssoc($table_info_result)) {
        echo '<option value="' . $page['page_nr'] . '">';
        echo htmlspecialchars($page['page_descr']);
        echo '</option>';
    }
    echo '</select>';
    echo '</p>';
    $choices['import'] = __('Import from selected page.');
    $choices['export'] = __('Export to selected page.');
}
$choices['create_export'] = __('Create a page and export to it.');

if (1 == count($choices)) {
    echo $choices['create_export'];
    echo '<input type="hidden" name="mode" value="create_export" />';
} else {
    echo PMA_Util::getRadioFields(
        'mode', $choices, $checked_choice = '', $line_break = true,
        $escape_label = false, $class = ''
    );
}
echo '<br />';
echo '<label for="newpage">' . __('New page name: ') . '</label>';
echo '<input id="newpage" type="text" name="newpage" />';

echo '<p>' . __('Export/Import to scale:');
echo '<select name="scale">';
echo '<option value="1">1:1</option>';
echo '<option value="2">1:2</option>';
echo '<option value="3" selected="selected">1:3 ('
    . __('recommended') . ')</option>';
echo '<option value="4">1:4</option>';
echo '<option value="5">1:5</option>';
echo '</select>';
echo '</p>';

echo '<input type="submit" value="' . __('Go') . '"/>';

echo '</fieldset>';
echo '</div>';
echo '</form>';
echo '</div>';
?>
