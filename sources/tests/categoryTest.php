<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
use PHPUnit\Framework\TestCase;
putenv('APP_ENV=test');
class CategoryTest extends TestCase
{
    protected $conn;

    protected function setUp(): void
    {
        $conn = new mysqli("localhost", "root", "", "website_test");
        // Check if the connection is established
        if ($conn === null || $conn->connect_error) {
            $this->fail("Database connection failed: " . $conn->connect_error);
        }

        // Store the global connection object in the instance variable
        $this->conn = $conn;
    }

    // Test search functionality in 'sanpham' table by product name
    public function testSearchByProductName()
    {
        $searchTerm = 'napoli pug';  // Example search term

        // Prepare the SQL query to search for products by name
        $sql = "SELECT * FROM sanpham WHERE TEN_SP LIKE ?";
        $stmt = $this->conn->prepare($sql);  // Ensure $conn is available and not null
        if ($stmt === false) {
            $this->fail("Failed to prepare SQL statement.");
        }
        
        $searchQuery = "%" . $searchTerm . "%";
        $stmt->bind_param("s", $searchQuery);
        $stmt->execute();
        $result = $stmt->get_result();

        // Verify that the result contains the expected product(s)
        $this->assertGreaterThan(0, $result->num_rows, "No products found matching the search term");

        while ($row = $result->fetch_assoc()) {
            // Chuyển cả tên sản phẩm và từ khóa tìm kiếm về chữ thường trước khi kiểm tra
            $this->assertStringContainsString(
                strtolower($searchTerm), 
                strtolower($row['TEN_SP']), 
                "Product name does not contain search term"
            );
        }
    }

    // Test search functionality with invalid term (should return no results)
    public function testSearchWithInvalidTerm()
    {
        $searchTerm = 'NonExistentProduct';  // Example search term

        // Prepare the SQL query to search for products by name
        $sql = "SELECT * FROM sanpham WHERE TEN_SP LIKE ?";
        $stmt = $this->conn->prepare($sql);  // Ensure $conn is available and not null
        $searchQuery = "%" . $searchTerm . "%";
        $stmt->bind_param("s", $searchQuery);
        $stmt->execute();
        $result = $stmt->get_result();

        // Assert that no results are returned for invalid search
        $this->assertEquals(0, $result->num_rows, "Search should return no results for non-existent product");
    }

    // Test search with empty term (should return all products)
    public function testSearchWithEmptyTerm()
    {
        $searchTerm = '';  // Empty search term

        // Prepare the SQL query to search for products by name
        $sql = "SELECT * FROM sanpham WHERE TEN_SP LIKE ?";
        $stmt = $this->conn->prepare($sql);  // Ensure $conn is available and not null
        $searchQuery = "%" . $searchTerm . "%";
        $stmt->bind_param("s", $searchQuery);
        $stmt->execute();
        $result = $stmt->get_result();

        // Assert that the result contains some products
        $this->assertGreaterThan(0, $result->num_rows, "Search should return some products for an empty search term");
    }

    // Clean up after the test
    protected function tearDown(): void
    {
        $this->conn->close();
    }
}
?>
