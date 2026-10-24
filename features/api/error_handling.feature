Feature: Error Handling
  As an API consumer
  I want to receive proper error responses

  Scenario: Invalid JSON on create returns 400
    When I send a POST request to "/api/books" with body:
      """
      {invalid json}
      """
    Then the status code should be 400

  Scenario: Invalid JSON on update returns 400
    When I send a PUT request to "/api/books/1" with body:
      """
      {invalid json}
      """
    Then the status code should be 400

  Scenario: Missing required fields on create returns 422
    When I send a POST request to "/api/books" with body:
      """
      {}
      """
    Then the status code should be 422
    And the response should have key "violations"
