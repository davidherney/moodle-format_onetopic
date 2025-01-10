# COURSE FORMAT Onetopic

Package tested in: moodle 4.4+ and 4.5+.

## QUICK INSTALL
Download zip package, extract the onetopic folder and upload this folder into course/format/.

## ABOUT
* **Developed by:** David Herney - davidherney at gmail dot com
* **GIT:** https://github.com/davidherney/moodle-format_onetopic
* **Powered by:** [BambuCo](https://bambuco.co/)
* **Documentation:** [En](https://bambuco.co/onetopic-en/) - [Es](https://bambuco.co/onetopic/)

## COMING SOON
* Fix: the topics bar is not refresh when change (move a section in boost sections bar) or delete a section.

## IN VERSION

### 2024050905:
* New tab styles editor when editing each section.
* Select icon by tab and tab state.
* New background option by section.
* Compatibility with moodle 4.5
* Do not show new subsections in tabs (Moodle 4.5). In the future these subsections become second-level tabs.

### 2024050904:
* New "sectionname" parameter to navigate to a tab directly using its name.
* New default course settings in site level.
* The courseindex option has been removed from the tab view option. Courses that had it set will continue to work until the option is overridden. It is being removed because there is no way to customize its behavior from the course format and some serious UX bugs are occurring.

### 2024050903:
* Fixed Load previously browsed section when section is not specified in URL.

### 2024050901:
* Compatibility with moodle 4.4

### 2024050303:
* Update section control menu for Moodle 4.2 and stabilization improvements.

### 2024050301:
* Support bulk edit tools.

### 2022081610:
* New tabs view option: course index
  * ![Tabs view Course index](https://boa.nuestroscursos.net/api/c/web/resources/NDU1MEVCNjAtODQ4Qy00RTk3LUI2NzUtOUJBN0E5ODk0QTkyQGJvYS51ZGVhLmVkdS5jbw==/!/onetopic/tabsview_courseindex.png)
* New scope to show tabs: modules. Included admin setting to enable it. Funded by [Ecole hôtelière de Lausanne](https://www.ehl.edu/)
  * ![Scope modules](https://boa.nuestroscursos.net/api/c/web/resources/NDU1MEVCNjAtODQ4Qy00RTk3LUI2NzUtOUJBN0E5ODk0QTkyQGJvYS51ZGVhLmVkdS5jbw==/!/onetopic/tabs_scopemodules.png)

### 2022081609:
* New tabs style editor in site settings. Funded by [Ecole hôtelière de Lausanne](https://www.ehl.edu/)
  * ![Editor preview](https://boa.nuestroscursos.net/api/c/web/resources/NDU1MEVCNjAtODQ4Qy00RTk3LUI2NzUtOUJBN0E5ODk0QTkyQGJvYS51ZGVhLmVkdS5jbw==/!/onetopic/tabs_styles_editor.png)
* Show "Availability information" in tabs and in the template mode.
  1. ![Availability_information](https://boa.nuestroscursos.net/api/c/web/resources/NDU1MEVCNjAtODQ4Qy00RTk3LUI2NzUtOUJBN0E5ODk0QTkyQGJvYS51ZGVhLmVkdS5jbw==/!/onetopic/tpl_availability_information.png)
  2. ![Availability_information window](https://boa.nuestroscursos.net/api/c/web/resources/NDU1MEVCNjAtODQ4Qy00RTk3LUI2NzUtOUJBN0E5ODk0QTkyQGJvYS51ZGVhLmVkdS5jbw==/!/onetopic/tpl_availability_information_window.png)

### 2022081608:
* Navigation options: Next/previous section, with different display options.
* A site setting option to define if use an anchor to navigate to the top of tabs when click in a tab.
* New course setting to hide the course index bar.

### 2022081607:
* Duplicate section feature.

### 2022081606:
* Enable/disable custom styles in site level
* Chevron icon for tabs with child's
* New CSS class for tabs with child's: haschilds

### 2022081605:
* Check compatibility with moodle 4.1
* Add section move controls
* Notice in the tab bar when a course is being edited and the tabs are hidden from students

### 2022081604:
* Stabilization

### 2022081602:
* Compatibility with moodle 4.0
