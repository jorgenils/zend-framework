Welcome to the ZFDemo Tutorial.

Feedback and suggestions are most welcome!
I can be reached at: gavin@zend.com

In this tutorial, a tiny demo application will be constructed to
highlight key features of the Zend Framework and demonstrate how
its components could be integrated and used together. Although
the demo application is not intended for production use, the
working application provides a robust skeleton for building Zend
Framework applications. Even advanced PHP developers may find
some parts of the demo enlightening to see how the Zend Framework
might differ from other frameworks in its use and application of
design patterns and components providing popular functionality.

Currently, the tutorial is available online at:
http://framework.zend.com/wiki/x/1E8

Later, the tutorial will be released in a format suitable for
reading offline.


Each lesson in this tutorial may be installed by copying the
"index.php" file in a lesson directory to your web server's
document root directory (often "www" or "public_html").

Lesson Sections/Directories
==============================
section1_install - Try first, to test compatiblity of your server!
                   Also helps debug installation issues.
                   http://framework.zend.com/wiki/x/1E8

section2_intro   - Introduction to MVC and the ZF
                   http://framework.zend.com/wiki/x/2E8 

section3_topo    - Application Topography and Configuration
                   http://framework.zend.com/wiki/x/6k8

section4_mvc     - Front Controller and Action Controllers
                   http://framework.zend.com/wiki/x/L0s

section5_except  - Dispatch Exceptions
                   http://framework.zend.com/wiki/x/7U8

section6_session - Anonymous Sessions
                   http://framework.zend.com/wiki/x/7U8

section7_auth    - Identity and Authentication
                   http://framework.zend.com/wiki/x/fUw

section8_acl     - Access Control
                   http://framework.zend.com/wiki/x/8E8

section9_filter  - Filtering and Validating User Input
                   http://framework.zend.com/wiki/x/708

section10_db     - Using DB Table and Row Patterns
                   http://framework.zend.com/wiki/x/7k8

section11_l10n   - Localization
                   http://framework.zend.com/wiki/x/8U8

section12_i18n   - Internationalization and Multilingual Support
                   http://framework.zend.com/wiki/x/8k8

section13_http   - Retrieving Web Content
                   http://framework.zend.com/wiki/x/808

section14_log    - Logging
                   http://framework.zend.com/wiki/x/9E8

section15_email  - Email
                   http://framework.zend.com/wiki/x/9U8 

section16_search - Searching
                   http://framework.zend.com/wiki/x/9k8

section17_perf   - Performance
                   http://framework.zend.com/wiki/x/G0s
                  
section18_ajax   - Ajax (TODO after ZF 1.0)
                   http://framework.zend.com/wiki/x/_k8

/**
 * Zend Framework ZFDemo Tutorial
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
 * @copyright  Copyright (c) 2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: README.txt 121 2007-04-12 21:48:01Z gavin $
 */
