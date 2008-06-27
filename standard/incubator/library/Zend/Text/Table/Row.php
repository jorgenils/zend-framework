<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category  Zend
 * @package   Zend_Text_Table
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id$
 */

/**
 * Row class for Zend_Text_Table
 *
 * @category  Zend
 * @package   Zend_Text_Table
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Text_Table_Row
{
    /**
     * List of all columns
     *
     * @var array
     */
    protected $_columns = array();

    /**
     * Append a column to the row
     *
     * @param  Zend_Text_Table_Column $column The column to append to the row
     * @throws InvalidArgumentException When $column is null
     * @return void
     */
    public function appendColumn(Zend_Text_Table_Column $column)
    {
        if ($column === null) {
            throw new InvalidArgumentException('$column may not be null');
        }

        $this->_columns[] = $column;
    }

    /**
     * Render the row
     *
     * @param  array                               $columnWidths Width of all columns
     * @param  Zend_Text_Table_Decorator_Interface $decorator    Decorator for the row borders
     * @return string
     */
    public function render(array $columnWidths,
                           Zend_Text_Table_Decorator_Interface $decorator)
    {
        // First we have to render all columns, to get the maximum height
        $renderedColumns = array();
        $maxHeight       = 0;
        $colNum          = 0;
        foreach ($this->_columns as $column) {
            // Get the colspan of the column
            $colSpan = $column->getColSpan();

            // Verify if there are enough column widths defined
            if (($colNum + $colSpan) > count($columnWidths)) {
                throw new Zend_Text_Table_Exception('Too many columns');
            }

            // Calculate the column width
            $columnWidth = ($colSpan - 1 + array_sum(array_slice($columnWidths,
                                                                 $colNum,
                                                                 $colSpan)));

            // Render the column and split it's lines into an array
            $result = explode("\n", $column->render($columnWidth));

            // Store the rendered column and calculate the new max height
            $renderedColumns[] = $result;
            $maxHeight         = max($maxHeight, (count($result) + 1));

            // Set up the internal column number
            $colNum += $colSpan;
        }

        // If the row doesnt contain enough columns to fill the entire row, fill
        // it with an empty column
        if ($colNum < count($columnWidths)) {
            $remainingWidth = (count($columnWidths) - $colNum - 1) +
                               array_sum(array_slice($columnWidths,
                                                     $colNum));
            $renderedColumns[] = str_repeat(' ', $remainingWidth);
        }

        // Add each single column line to the resul
        $result = '';
        for ($line = 0; $line < $maxHeight; $line++) {
            $result .= $decorator->getHorizontal();

            foreach ($renderedColumns as $renderedColumn) {
                if (isset($renderedColumn[$line]) === true) {
                    $result .= $renderedColumn[$line];
                } else {
                    $result .= str_repeat(' ', strlen($renderedColumn[0]));
                }

                $result .= $decorator->getHorizontal();
            }
        }

        return $result;
    }
}
