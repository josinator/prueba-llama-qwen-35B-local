Feature: Dashboard API Endpoints
  As a dashboard user
  I want to access stats, search, and book management APIs

  Scenario: Get statistics
    When I send a GET request to "/dashboard/api/stats"
    Then the status code should be 200
    And the response should have key "totalBooks"
    And the response should have key "booksWithDescription"
    And the response should have key "createdToday"
    And the response should have key "updatedToday"
    And the response should have key "authorsCount"

  Scenario: Search books with query
    When I send a GET request to "/dashboard/api/search?q=test&page=1&limit=20"
    Then the status code should be 200
    And the response should have key "books"
    And the response should have key "total"
    And the response should have key "page"
    And the response should have key "totalPages"

  Scenario: Search books with empty query
    When I send a GET request to "/dashboard/api/search?page=1&limit=20"
    Then the status code should be 200
    And the response should have key "books"

  Scenario: Dashboard index page loads
    When I send a GET request to "/dashboard"
    Then the status code should be 200
