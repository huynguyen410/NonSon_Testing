<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    // Mock Database object
    protected $mockDb;

    // Set up the mock database before each test
    protected function setUp(): void
    {
        // Mock Database connection
        $this->mockDb = $this->createMock(mysqli::class);
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Simulate cart items from the database
        $_SESSION['cart'] = $this->createCart();
    }

    // Simulate fetching cart items from the database
    protected function createCart()
    {
        return [
            1 => ['id' => 1, 'quantity' => 1], // Product 1, quantity 1
            2 => ['id' => 2, 'quantity' => 2], // Product 2, quantity 2
        ];
    }

    /** @test */
    public function testRemoveItem()
    {
        // Simulate POST data to remove product
        $_POST['id'] = 1;

        // Call the remove_item.php script
        ob_start();
        include './src/remove_item.php';
        ob_end_clean();

        // Assert product is removed from cart
        $this->assertArrayNotHasKey(1, $_SESSION['cart'], 'Product was not removed from cart');
    }

    /** @test */
    public function testClearCart()
    {
        // Đảm bảo phiên làm việc được khởi tạo
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Khởi tạo cart trước khi kiểm tra
        $_SESSION['cart'] = $this->createCart();

        // Gọi script clear_cart.php
        ob_start();
        include './src/clear_cart.php';
        ob_end_clean();

        $this->assertEmpty($_SESSION['cart'], 'Cart should be empty after clear_cart.php');
    }

    // Clean up after each test
    protected function tearDown(): void
    {
        // End session after each test
        session_unset();
        session_destroy();
    }
}
?>
