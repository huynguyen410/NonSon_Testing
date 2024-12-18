<?php
use PHPUnit\Framework\TestCase;

class accountListTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        // Kết nối đến cơ sở dữ liệu ảo
        $this->conn = new mysqli("localhost", "root", "", "website_test");

        // Tạo bảng và thêm dữ liệu mẫu nếu chưa tồn tại
        $this->conn->query("CREATE TABLE IF NOT EXISTS accounts (
            ID INT AUTO_INCREMENT PRIMARY KEY,
            USERNAME VARCHAR(255) NOT NULL,
            PASSWORD VARCHAR(255) NOT NULL,
            ROLE TINYINT(1) NOT NULL,
            STATUS TINYINT(1) DEFAULT 1
        )");

        // Xóa dữ liệu cũ và thêm dữ liệu mới
        $this->conn->query("TRUNCATE TABLE accounts");
        $this->conn->query("INSERT INTO accounts (USERNAME, PASSWORD, ROLE, STATUS) 
                            VALUES ('user1', 'pass1', 1, 1), 
                                   ('user2', 'pass2', 0, 0)");
    }

    protected function tearDown(): void
    {
        // Xóa bảng sau khi kiểm tra xong
        $this->conn->query("DROP TABLE IF EXISTS accounts");
        $this->conn->close();
    }

    public function testGetAccountList()
    {
        $result = $this->conn->query("SELECT * FROM accounts");
        $this->assertEquals(2, $result->num_rows, "Account list count does not match");
        
        $accounts = [];
        while ($row = $result->fetch_assoc()) {
            $accounts[] = $row;
        }

        $this->assertEquals('user1', $accounts[0]['USERNAME']);
        $this->assertEquals(1, $accounts[0]['ROLE']);
    }

    public function testUpdateAccountStatus()
    {
        $this->conn->query("UPDATE accounts SET STATUS = 0 WHERE USERNAME = 'user1'");
        $result = $this->conn->query("SELECT STATUS FROM accounts WHERE USERNAME = 'user1'");
        $row = $result->fetch_assoc();

        $this->assertEquals(0, $row['STATUS'], "Failed to update account status");
    }

    public function testAddAccount()
    {
        $this->conn->query("INSERT INTO accounts (USERNAME, PASSWORD, ROLE, STATUS) 
                            VALUES ('user3', 'pass3', 0, 1)");

        $result = $this->conn->query("SELECT * FROM accounts WHERE USERNAME = 'user3'");
        $this->assertEquals(1, $result->num_rows, "Failed to add new account");

        $row = $result->fetch_assoc();
        $this->assertEquals('0', $row['ROLE'], "Account role does not match");
    }

    public function testEditAccount()
    {
        $this->conn->query("UPDATE accounts SET PASSWORD = 'newpass1', ROLE = '0' WHERE USERNAME = 'user1'");

        $result = $this->conn->query("SELECT * FROM accounts WHERE USERNAME = 'user1'");
        $row = $result->fetch_assoc();

        $this->assertEquals('newpass1', $row['PASSWORD'], "Failed to update account password");
        $this->assertEquals('0', $row['ROLE'], "Failed to update account role");
    }
}
