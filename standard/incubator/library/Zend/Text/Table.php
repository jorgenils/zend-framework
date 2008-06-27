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
 * Zend_Text_Table enables developers to create tables out of characters
 *
 * @category  Zend
 * @package   Zend_Text_Table
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Text_Table
{
    /**
     * Decorator used for the table borders
     *
     * @var Zend_Text_Table_Decorator_Interface
     */
    protected $_decorator;

    /**
     * List of all column widths
     *
     * @var array
     */
    protected $_columnWidths;

    /**
     * Rows of the table
     *
     * @var array
     */
    protected $_rows = array();

    /**
     * Create a basic table object
     *
     * @param  array                               $columnsWidths List of all column widths
     * @param  Zend_Text_Table_Decorator_Interface $decorator     A decorator used for the table borders
     * @throws Zend_Text_Table_Exception When no columns were supplied
     * @throws Zend_Text_Table_Exception When a column has an invalid width
     */
    public function __construct(array $columnWidths,
                                Zend_Text_Table_Decorator_Interface $decorator = null)
    {
        // First validate the column widths array
        if ($columnWidths === null or count($columnWidths) === 0) {
            throw new Zend_Text_Table_Exception('You must supply at least one column');
        } else {
            foreach ($columnWidths as $columnNum => $columnWidth) {
                if (is_int($columnWidth) === false or $columnWidth < 1) {
                    throw new Zend_Text_Table_Exception('Column ' . $columnNum . ' has an invalid'
                                                        . ' columnd width');
                }
            }

            $this->_columnWidths = $columnWidths;
        }

        // If no decorator was given, use default unicode decorator
        if ($decorator === null) {
            $this->_decorator = new Zend_Text_Table_Decorator_Unicode();
        } else {
            $this->_decorator = $decorator;
        }
    }

    /**
     * Append a row to the table
     *
     * @param  Zend_Text_Table_Row $row The row to append to the table
     * @throws InvalidArgumentException When $row is null
     * @return void
     */
    public function appendRow(Zend_Text_Table_Row $row)
    {
        if ($row === null) {
            throw new InvalidArgumentException('$row may not be null');
        }

        $this->_rows[] = $row;
    }

    /**
     * Render the table
     *
     * @throws Zend_Text_Table_Exception When no rows were added to the table
     * @return string
     */
    public function render()
    {
        // There should be at least one row
        if (count($this->_rows) === 0) {
            throw new Zend_Text_Table_Exception('No rows were added to the table yet');
        }

        // Initiate the result variable
        $result = '';

        // Now render all rows, starting from the first one
        $numRows = count($this->_rows);
        foreach ($this->_rows as $rowNum => $row) {
            // Get all column widths
            if (isset($columnWidths) === true) {
                $lastColumnWidths = $columnWidths;
            }

            $columnWidths = $row->getColumnWidths($this->_columnWidths);
            $numColumns   = count($columnWidths);

            // Check what we have to draw
            if ($rowNum === 0) {
                // If this is the first row, draw the table top
                $result .= $this->_decorator->getTopLeft();

                foreach ($columnWidths as $columnNum => $columnWidth) {
                    $result .= str_repeat($this->_decorator->getHorizontal(),
                                          $columnWidth);

                    if ($columnNum === $numColumns) {
                        $result .= $this->_decorator->getTopRight();
                    } else {
                        $result .= $this->_decorator->getHorizontalDown();
                    }
                }

                $result .= "\n";
            } else {
                // Else draw the row seperator
                $result .= $this->_decorator->getVerticalRight();

                $currentUpperColumn = 0;
                $currentLowerColumn = 0;
                $currentUpperWidth  = 0;
                $currentLowerWidth  = 0;

                // Loop through all column widths
                foreach ($this->_columnWidths as $columnNum => $columnWidth) {
                    // Add the horizontal line
                    $result .= str_repeat($this->_decorator->getHorizontal(),
                                          $columnWidth);

                    // If this is the last line, break out
                    if ($columnNum === $numColumns) {
                        break;
                    }

                    // Else check, which connector style has to be used
                    $connector          = 0x0;
                    $currentUpperWidth += $columnWidth;
                    $currentLowerWidth += $columnWidth;

                    if ($lastColumnWidths[$currentUpperColumn] === $currentUpperWidth) {
                        $connector          |= 0x1;
                        $currentUpperColumn += 1;
                        $currentUpperWidth   = 0;
                    } else {
                        $currentUpperWidth += 1;
                    }

                    if ($columnWidths[$currentLowerColumn] === $currentLowerWidth) {
                        $connector          |= 0x2;
                        $currentLowerColumn += 1;
                        $currentLowerWidth   = 0;
                    } else {
                        $currentLowerWidth += 1;
                    }

                    switch ($connector) {
                        case 0x0:
                            $result .= $this->_decorator->getHorizontal();
                            break;

                        case 0x1:
                            $result .= $this->_decorator->getHorizontalUp();
                            break;

                        case 0x2:
                            $result .= $this->_decorator->getHorizontalDown();
                            break;

                        case 0x3:
                            $result .= $this->_decorator->getCross();
                            break;

                        default:
                            // This should *never* happen!
                            break;
                    }
                }

                $result .= $this->_decorator->getVerticalLeft() . "\n";
            }

            // Add the rendered row to the result
            $result .= $row->render($this->_columnWidths, $this->_decorator) . "\n";

            // If this is the last row, draw the table bottom
            if ($rowNum === $numRows) {
                $result .= $this->_decorator->getBottomLeft();

                foreach ($columnWidths as $columnNum => $columnWidth) {
                    $result .= str_repeat($this->_decorator->getHorizontal(),
                                          $columnWidth);

                    if ($columnNum === $numColumns) {
                        $result .= $this->_decorator->getBottomRight();
                    } else {
                        $result .= $this->_decorator->getHorizontalUp();
                    }
                }

                $result .= "\n";
            }
        }

        return $result;
    }
}
