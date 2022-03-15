COURSE FORMAT Onetopic
============================

Package tested in: moodle 3.9+ and 3.10+.

QUICK INSTALL
==============
Download zip package, extract the onetopic folder and upload this folder into course/format/.

ABOUT
=============
Developed by: David Herney - davidherney at gmail dot com
GIT: https://github.com/davidherney/moodle-format_onetopic
Powered by: https://bambuco.co/

IN VERSION
=============
2020122703:
Changed the add new tab feature, now the tabs is inserted to the rigth of current selected tab.
New buttom (+) to add new subtabs (powered by USC Cali Colombia - https://www.usc.edu.co/).
New 'tabsview' option, with special views:
 - Vertical tabs: show tabs in vertical direction. Tabs on the left and content on the right.
 - One single line: all tabs are displayed in a single line with horizontal scroll. Useful if
    too many tabs are used (powered by USC Cali Colombia - https://www.usc.edu.co/).
Drop deprecated print_error (by james-cnz).
Child tabs inherit parent tabs visibility (by james-cnz).

2020122700:
Compatibility with moodle 3.9 and 3.10.
When duplicating sections, exclude deleted recources/activities (by james-cnz)
Added anchor #tabs-tree-start to tabs links in order to change the page position between page loads.

2018010607:
Compatibility with moodle 3.7 and 3.8.
Added hiddensectionshelp option used to show a help icon with available section message, if it apply.

2018010606:
Compatibility with moodle 3.6.
Added privacy provider.
Included inner completion check into summary templates.

2018010605:
Selected visible tab according to the different tabs properties (available, visible, highlighted) and the course options

2018010604:
Compatibility with moodle 3.5

2018010603:
Compatibility with moodle 3.4 checked
Fix: Summary images copy when a tab is duplicated.

2018010602:
Seted the marked section as the default section
Fixed: error duplicating the last section

2018010600:
Compatibility with moodle 3.3
Supports "stealth" activities mode
Option "Number of sections" (numsections) was removed according to the core formats proposal

2016071402:
Course format supports the creation of a news forum.

2016071401:
Fixed: Error code: sectionnotexist
Apply Moodle coding style
Compatibility with moodle 3.2

2016071400:
Compatibility with moodle 3.1
Fixed: Issue in current section navigation

2016020501:
Added functionality: Summary topic as template

2016020500:
Compatibility with moodle 3.0
Added section edit menu

2015051700:
Compatibility with moodle 2.9

2015011805:
Added functionality: Adds a configuration option at section edit screen to allow people to change the text of the first tab of the sublevel tabs (By Daniel Neis Araujo).

2015011804:
This release was powered by Loyola Leadership School of Universidad Loyola Andalucia
Added functionality: Duplicate current section
Added functionality: Change tab style properties: font color, background color and new field to change other CSS properties

2015011803:
Added functionality: Sub-tabs, in order to have tabs in two levels
It was replaced the "<font>" tag in a tab, It was changed by "<div>" tag

2015011802 (MATURITY BETA):
Added functionality: Reduce and increment number of sections.

2015011801 (MATURITY ALPHA):
Compatibility with moodle 2.8

2014092802:
Fixed language -en- error and fixed double visualization of topic 0 when all topics are hidden

2014092801;
Fixed visualization of hidden/unavailable topics/tabs. Fixed with Mike Grant help.
Added "Disable/enable asynchronous edit functions" in order to make it possible to move the resources between tabs.

2014070301 (MATURITY ALPHA):
Compatibility with moodle 2.7

2014012001:
Fixed "overflow hidden" problem and line visibility in tabs.

2014012000:
Compatibility with moodle 2.6
Include hide and mark functionalities to "section 0"
The class "mark" has been added to the tab in accordance to the checked section. Italic font style has been apply to.
Added "move current topic" functionality, it is located on buttom of the page.
Added language "Euskara" by Iñigo Zendegi

2013052002:
Correction to "overflow hidden" problem.

2013052001:
Compatibility with moodle 2.5

2012112602:
Fixed an error when not section exists

2012112601:
Compatibility with moodle 2.4
Added the "hide tabs bar" option. Default is not hidden.

2012062605:
Fixed an error in return links when is edited a resource

2012062603:
Fixed an error in navigation links when is adding a new topic and the current section is the end

2012062602:
Fixed an error when is add new topics

2012062601:
Compatibility with moodle 2.3
Option coursedisplay used to show/hide section 0 in the page top

2012021301:
Compatibility with moodle 2.2

2011030101:
Change in style properties and guest access.
