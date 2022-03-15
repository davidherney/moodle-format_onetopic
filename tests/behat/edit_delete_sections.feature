@format @format_onetopic
Feature: Sections can be edited and deleted in Onetopic format
  In order to rearrange my course contents
  As a teacher
  I need to edit and Delete topics

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
      | chat       | Test chat name         | Test chat description         | C1     | chat1       | 4       |
      | choice     | Test choice name       | Test choice description       | C1     | choice1     | 5       |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on

  Scenario: View the default name of the general section in Onetopic format
    When I edit the section "0"
    Then the field "Custom" matches value "0"
    And the field "New value for Section name" matches value "General"

  Scenario: Edit the default name of the general section in Onetopic format
    When I edit the section "0" and I fill the form with:
      | Custom | 1                     |
      | New value for Section name      | This is the general section |
    Then I should see "This is the general section" in the "li#section-0" "css_element"

  Scenario: View the default name of the second section in Onetopic format
    When I click on "Topic 2" "link" in the "ul.nav.nav-tabs" "css_element"
    And I edit the section "2"
    Then the field "Custom" matches value "0"
    And the field "New value for Section name" matches value "Topic 2"

  Scenario: Edit section summary in Onetopic format
    When I click on "Topic 2" "link" in the "ul.nav.nav-tabs" "css_element"
    And I edit the section "2" and I fill the form with:
      | Summary | Welcome to section 2 |
    Then I should see "Welcome to section 2" in the "li#section-2" "css_element"

  Scenario: Edit section default name in Onetopic format
    When I click on "Topic 2" "link" in the "ul.nav.nav-tabs" "css_element"
    And I edit the section "2" and I fill the form with:
      | Custom | 1                      |
      | New value for Section name      | This is the second topic |
    Then I should see "This is the second topic" in the "li#section-2" "css_element"
    And I should not see "Topic 2" in the "li#section-2" "css_element"

  Scenario: Deleting the last section in Onetopic format
    When I click on "Topic 5" "link" in the "ul.nav.nav-tabs" "css_element"
    And I delete section "5"
    Then I should see "Are you absolutely sure you want to completely delete \"Topic 5\" and all the activities it contains?"
    And I press "Delete"
    And I should not see "Topic 5"
    And I should see "Topic 4"

  Scenario: Deleting the middle section in Onetopic format
    When I click on "Topic 4" "link" in the "ul.nav.nav-tabs" "css_element"
    And I delete section "4"
    And I press "Delete"
    Then I should not see "Topic 5"
    And I should see "Topic 3" in the "li#section-3" "css_element"
    And I click on "Topic 4" "link" in the "ul.nav.nav-tabs" "css_element"
    And I should not see "Test chat name"
    And I should see "Test choice name" in the "li#section-4" "css_element"
    And I should see "Topic 4"

  Scenario: Adding sections in Onetopic format
    When I follow "Add a section after the currently selected section"
    And I should see "Topic 6"
    And "li#section-7" "css_element" should not exist
    And I should not see "Topic 7"
    And I follow "Add a section after the currently selected section"
    And I should see "Topic 7"
    And "li#section-8" "css_element" should not exist
    And I should not see "Topic 8"
