@web @project_page
Feature: Project title, description and credits should be translatable via a button

  Background:
    Given there are users:
      | id | name     |
      | 1  | Catrobat |
    And there are projects:
      | id | name     | owned by |
      | 1  | project1 | Catrobat |
    And there are comments:
      | id | program_id | user_id | text |
      | 1  | 1          | 1       | c1   |
      | 2  | 1          | 1       | c2   |

  Scenario: Translate button should translate the corresponding comment
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#comment-translation-button-1" should exist
    When I click "#comment-translation-button-1"
    And the element "#comment-translation-loading-spinner-1" should be visible
    And I wait for AJAX to finish
    Then I wait for the element "#remove-comment-translation-button-1" to be visible
    And the element "#comment-translation-wrapper-1" should be visible
    When I click "#remove-comment-translation-button-1"
    Then the element "#comment-translation-button-1" should be visible
    And the element "#comment-text-wrapper-1" should be visible
  
  Scenario: Comment should only be translated by API once
    Given I am on "/app/project/1"
    And I wait for the page to be loaded
    Then the element "#comment-translation-button-2" should exist
    When I click "#comment-translation-button-2"
    And the element "#comment-translation-loading-spinner-2" should be visible
    And I wait for AJAX to finish
    Then I wait for the element "#remove-comment-translation-button-2" to be visible
    And the element "#comment-translation-wrapper-2" should be visible
    When I click "#remove-comment-translation-button-2"
    Then the element "#comment-translation-button-2" should be visible
    And the element "#comment-text-wrapper-2" should be visible
    When I click "#comment-translation-button-2"
    Then the element "#remove-comment-translation-button-2" should be visible
    And the element "#comment-translation-wrapper-2" should be visible