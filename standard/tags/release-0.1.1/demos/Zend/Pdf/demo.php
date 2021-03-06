<?php
/**
 * @package Zend_Pdf
 * @subpackage demo
 */

/** Zend_Pdf */
require_once 'Zend/Pdf.php';

if (!isset($argv[1])) {
    echo "USAGE: php demo.php <pdf_file> [<output_pdf_file>]\n";
    exit;
}

if (file_exists($argv[1])) {
    $pdf = Zend_Pdf::load($argv[1]);
} else {
    $pdf = new Zend_Pdf();
}

//------------------------------------------------------------------------------------
// Reverse page order
$pdf->pages = array_reverse($pdf->pages);

// Create new Style
$style = new Zend_Pdf_Style();
$style->setFillColor(new Zend_Pdf_Color_RGB(0, 0, 0.9));
$style->setLineColor(new Zend_Pdf_Color_GrayScale(0.2));
$style->setLineWidth(3);
$style->setLineDashingPattern(array(3, 2, 3, 4), 1.6);
$style->setFont(new Zend_Pdf_Font_Standard(Zend_Pdf_Const::FONT_HELVETICA_BOLD), 32);

// Create new image object
$stampImage = new Zend_Pdf_Image_JPEG(dirname(__FILE__) . '/stamp.jpg');

// Mark page as modified
foreach ($pdf->pages as $page){
    $page->saveGS();
    $page->setStyle($style);
    $page->rotate(0, 0, M_PI_2/3);

    $page->saveGS();
    $page->clipCircle(550, -10, 50);
    $page->drawImage($stampImage, 500, -60, 600, 40);
    $page->restoreGS();

    $page->drawText('Modified by Zend Framework!', 150, 0);
    $page->restoreGS();
}

// Add new page generated by Zend_Pdf object (page is attached to the specified the document)
$pdf->pages[] = ($page1 = $pdf->newPage('A4'));

// Add new page generated by Zend_Pdf_Page object (page is not attached to the document)
$pdf->pages[] = ($page2 = new Zend_Pdf_Page(Zend_Pdf_Const::PAGESIZE_LETTER_LANDSCAPE));

// Create new font
$font = new Zend_Pdf_Font_Standard(Zend_Pdf_Const::FONT_HELVETICA);

// Apply font and draw text
$page1->setFont($font, 36);
$page1->drawText('Helvetica 36 text string', 60, 500);

// Use font object for another page
$page2->setFont($font, 24);
$page2->drawText('Helvetica 24 text string', 60, 500);

// Use another font
$page2->setFont(new Zend_Pdf_Font_Standard(Zend_Pdf_Const::FONT_TIMES_ROMAN), 32);
$page2->drawText('Times-Roman 32 text string', 60, 450);

// Draw rectangle
$page2->setFillColor(new Zend_Pdf_Color_GrayScale(0.8));
$page2->setLineColor(new Zend_Pdf_Color_GrayScale(0.2));
$page2->setLineDashingPattern(array(3, 2, 3, 4), 1.6);
$page2->drawRectangle(60, 400, 400, 350);

// Draw circle
$page2->setLineDashingPattern(Zend_Pdf_Const::LINEDASHING_SOLID);
$page2->setFillColor(new Zend_Pdf_Color_RGB(1, 0, 0));
$page2->drawCircle(85, 375, 25);

// Draw sectors
$page2->drawCircle(200, 375, 25, 2*M_PI/3, -M_PI/6);
$page2->setFillColor(new Zend_Pdf_Color_CMYK(1, 0, 0, 0));
$page2->drawCircle(200, 375, 25, M_PI/6, 2*M_PI/3);
$page2->setFillColor(new Zend_Pdf_Color_RGB(1, 1, 0));
$page2->drawCircle(200, 375, 25, -M_PI/6, M_PI/6);

// Draw ellipse
$page2->setFillColor(new Zend_Pdf_Color_RGB(1, 0, 0));
$page2->drawEllipse(250, 400, 400, 350);
$page2->setFillColor(new Zend_Pdf_Color_CMYK(1, 0, 0, 0));
$page2->drawEllipse(250, 400, 400, 350, M_PI/6, 2*M_PI/3);
$page2->setFillColor(new Zend_Pdf_Color_RGB(1, 1, 0));
$page2->drawEllipse(250, 400, 400, 350, -M_PI/6, M_PI/6);

// Draw and fill polygon
$page2->setFillColor(new Zend_Pdf_Color_RGB(1, 0, 1));
$x = array();
$y = array();
for ($count = 0; $count < 8; $count++) {
    $x[] = 140 + 25*cos(3*M_PI_4*$count);
    $y[] = 375 + 25*sin(3*M_PI_4*$count);
}
$page2->drawPolygon($x, $y,
                    Zend_Pdf_Const::SHAPEDRAW_FILLNSTROKE,
                    Zend_Pdf_Const::FILLMETHOD_EVENODD);

// Draw line
$page2->setLineWidth(0.5);
$page2->drawLine(60, 375, 400, 375);
//------------------------------------------------------------------------------------

if (isset($argv[2])) {
    $pdf->save($argv[2]);
} else {
    $pdf->save($argv[1], true /* update */);
}
