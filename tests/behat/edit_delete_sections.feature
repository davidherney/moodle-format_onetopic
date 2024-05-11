@format @format_onetopic
Feature: Sections can be edited and deleted in Onetopic format
  In order to rearrange my course contents
  As a teacher
  I need to edit and delete topics

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format   | coursedisplay | numsections |
      | Course 1 | C1        | onetopic | 0             | 5           |
    And the following "activities" exist:
      | activity   | name                   | intro                         | course | idnumber    | section |
      | assign     | Test assignment name   | Test assignment description   | C1     | assign1     | 0       |
      | book       | Test book name         | Test book description         | C1     | book1       | 1       |
      | lesson     | Test lesson name       | Test lesson description       | C1     | lesson1     | 4       |
      | choice     | Test choice name       | Test choice description       | C1     | choice1     | 5       |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on

  Scenario: View the default name of the general section in Onetopic format
    When I edit the section "0"
    Then the field "Section name" matches value ""
    And I should see "General"

  Scenario: Edit the default name of the general section in Onetopic format
    When I edit the section "0" and I fill the form with:
      | Section name      | This is the general section |
    Then I should see "This is the general section" in the ".format_onetopic-tabs .tab_position_0 .nav-link.active" "css_element"

  Scenario: View the default name of the second section in Onetopic format
    When I click on "Topic 2" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    And I edit the section "2"
    Then the field "Section name" matches value ""
    And I should see "Topic 2"

  Scenario: Edit section description in Onetopic format
    When I click on "Topic 2" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    And I edit the section "2" and I fill the form with:
      | Description | Welcome to section 2 |
    Then I should see "Welcome to section 2" in the "#page-content li#section-2" "css_element"

  Scenario: Edit section default name in Onetopic format
    When I click on "Topic 2" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    And I edit the section "2" and I fill the form with:
      | Section name      | This is the second topic |
    Then I should see "This is the second topic" in the ".format_onetopic-tabs .tab_position_2 .nav-link.active" "css_element"
    And I should not see "Topic 2" in the ".format_onetopic-tabs .tab_position_2 .nav-link.active" "css_element"

  Scenario: Deleting the last section in Onetopic format
    When I click on "Topic 5" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    And I delete section "5"
    Then I should see "Are you absolutely sure you want to completely delete \"Topic 5\" and all the activities it contains?"
    And I press "Delete"
    And I should not see "Topic 5"
    And I should see "Topic 4"

  Scenario: Deleting the middle section in Onetopic format
    When I click on "Topic 4" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    And I delete section "4"
    Then I should see "Are you absolutely sure you want to completely delete \"Topic 4\" and all the activities it contains?"
    And I press "Delete"
    And I should not see "Topic 5"
    And I should see "Topic 3" in the ".format_onetopic-tabs .tab_position_3 .nav-link.active" "css_element"
    And I click on "Topic 4" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    And I should not see "Test lesson name"
    And I should see "Test choice name" in the "#page-content li#section-4" "css_element"
    And I should see "Topic 4"

  Scenario: Adding a section in Onetopic format
    When I follow "Add a section after the currently selected section"
    Then I should see "Topic 6"

  Scenario: Adding a section in middle of tabs for Onetopic format
    When I click on "Topic 5" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    And I should see "Test choice name" in the "#page-content li#section-5" "css_element"
    And I click on "Topic 4" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    And I follow "Add a section after the currently selected section"
    And I click on "Topic 5" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    Then I should see "Topic 6"
    And I should not see "Test choice name" in the "#page-content li#section-5" "css_element"
    And ".format_onetopic-tabs .tab_position_7 .nav-link" "css_element" should not exist

  @javascript
  Scenario: Copy section permalink URL to clipboard
    When I click on "Topic 1" "link" in the "#page-content ul.nav.nav-tabs" "css_element"
    And I open section "1" edit menu
    And I click on "Permalink" "link"
    And I click on "Copy to clipboard" "link" in the "Permalink" "dialogue"
    Then I should see "Text copied to clipboard"
