@format @format_onetopic
Feature: Testing subtopics_visibility in format_onetopic
  In order to keep subtopics aligned with their parents
  As a student I should not see subtopics of hidden parent sections

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format   | coursedisplay | numsections | hiddensections |
      | Course 1 | C1        | onetopic | 0             | 5           | 1              |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And I log in as "teacher1"

  @javascript
  Scenario: Subtopics of hidden parent sections are not visible to students
    Given I am on "Course 1" course homepage with editing mode on
    When I click on "Topic 2" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    And I edit the section "2" and I fill the form with:
      | Level | Child of previous tab |
    And I click on "Topic 4" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    And I edit the section "4" and I fill the form with:
      | Level | Child of previous tab |
    And I click on "Topic 3" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    And I open section "3" edit menu
    And I click on "Hide" "link"
    And I log out
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I click on "Topic 1" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    Then I should see "Topic 2" in the "#page-content .onetopic-tab-body" "css_element"
    And I should not see "Topic 4" in the "#page-content .onetopic-tab-body" "css_element"
