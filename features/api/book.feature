Feature: Book CRUD Operations
  As a book manager
  I want to create, read, update, and delete books

  Scenario: Create a new book
    When I send a POST request to "/api/books" with body:
      """
      {
        "title": "Behat Test Book",
        "author": "Test Author",
        "description": "Created via Behat"
      }
      """
    Then the status code should be 201
    And the response should have key "id"
    And the response should have key "title" with value "Behat Test Book"

  Scenario: Create book with missing title returns 422
    When I send a POST request to "/api/books" with body:
      """
      {
        "author": "Test Author"
      }
      """
    Then the status code should be 422
    And the response should have key "violations"

  Scenario: Create book with invalid JSON returns 400
    When I send a POST request to "/api/books" with body:
      """
      not valid json
      """
    Then the status code should be 400

  Scenario: Show a book by ID
    When I send a POST request to "/api/books" with body:
      """
      {
        "title": "Show Test",
        "author": "Show Author",
        "description": "For show test"
      }
      """
    Then the status code should be 201
    When I send a GET request to "/api/books/1"
    Then the status code should be 200

  Scenario: Show non-existent book returns 404
    When I send a GET request to "/api/books/00000000-0000-0000-0000-000000000000"
    Then the status code should be 404

  Scenario: Update a book
    When I send a POST request to "/api/books" with body:
      """
      {
        "title": "Original Title",
        "author": "Original Author"
      }
      """
    Then the status code should be 201
    When I send a PUT request to "/api/books/1" with body:
      """
      {
        "title": "Updated Title"
      }
      """
    Then the status code should be 200
    And the response should have key "title" with value "Updated Title"

  Scenario: Update non-existent book returns 404
    When I send a PUT request to "/api/books/00000000-0000-0000-0000-000000000000" with body:
      """
      {
        "title": "Updated"
      }
      """
    Then the status code should be 404

  Scenario: Delete a book
    When I send a POST request to "/api/books" with body:
      """
      {
        "title": "Delete Me",
        "author": "Delete Author"
      }
      """
    Then the status code should be 201
    When I send a DELETE request to "/api/books/1"
    Then the status code should be 200
    And the response should have key "success" with value true

  Scenario: Delete non-existent book returns 404
    When I send a DELETE request to "/api/books/00000000-0000-0000-0000-000000000000"
    Then the status code should be 404
