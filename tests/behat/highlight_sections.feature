@format @format_onetopic
Feature: Sections can be highlighted in Onetopic format
  In order to mark sections
  As a teacher
  I need to highlight and unhighlight sections

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format   | coursedisplay | numsections |
      | Course 1 | C1        | onetopic | 0             | 5           |
    And the following "activities" exist:
      | activity | name                 | intro                       | course | idnumber | section |
      | assign   | Test assignment name | Test assignment description | C1     | assign1  | 0       |
      | book     | Test book name       | Test book description       | C1     | book1    | 1       |
      | lesson   | Test lesson name     | Test lesson description     | C1     | lesson1  | 4       |
      | choice   | Test choice name     | Test choice description     | C1     | choice1  | 5       |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And I log in as "teacher1"

  @javascript
  Scenario: Highlight a section in Onetopic format
    Given I am on "Course 1" course homepage with editing mode on
    When I click on "Topic 2" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    And I open section "2" edit menu
    And I click on "Highlight" "link"
    Then I should see "Highlighted" in the "#page-content .course-section .course-section-header" "css_element"

  @javascript
  Scenario: Highlight a section when another section is already highlighted in Onetopic format
    Given I am on "Course 1" course homepage with editing mode on
    And I click on "Topic 3" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    And I open section "3" edit menu
    And I click on "Highlight" "link"
    And I should see "Highlighted" in the "#page-content .course-section .course-section-header" "css_element"
    When I click on "Topic 2" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    And I open section "2" edit menu
    And I click on "Highlight" "link"
    Then I should see "Highlighted" in the "#page-content .course-section .course-section-header" "css_element"
    When I click on "Topic 3" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    And I should not see "Highlighted" in the "#page-content .course-section .course-section-header" "css_element"

  @javascript
  Scenario: Unhighlight a section in Onetopic format
    Given I am on "Course 1" course homepage with editing mode on
    And I click on "Topic 3" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    And I open section "3" edit menu
    And I click on "Highlight" "link"
    And I should see "Highlighted" in the "#page-content .course-section .course-section-header" "css_element"
    When I open section "3" edit menu
    And I click on "Unhighlight" "link"
    Then I should not see "Highlighted" in the "#page-content .course-section .course-section-header" "css_element"
