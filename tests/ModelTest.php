<?php
use PHPUnit\Framework\TestCase;
use ORM\Models\User;
use ORM\Database;

class ModelTest extends TestCase
{
    protected static int $createdUserId;

    public static function setUpBeforeClass(): void
    {
        // DB bağlantısını test için hazırla
        Database::getConnection();
    }

    public function testCreateUser()
    {
        $data = [
            'name' => 'TestUser',
            'email' => 'testuser@example.com',
            'status' => 'active'
        ];

        $id = User::create($data);
        self::$createdUserId = $id;

        $this->assertIsNumeric($id, "Created user ID should be numeric");
    }

    public function testFindUser()
    {
        $user = User::find(self::$createdUserId);
        $this->assertNotNull($user, "User should be found");
        $this->assertEquals('TestUser', $user['name']);
    }

    public function testWhereQuery()
    {
        $users = User::where('status', '=', 'active')->limit(10)->get();
        $this->assertIsArray($users, "Users result should be array");
        $this->assertNotEmpty($users, "There should be at least one active user");
    }

    public function testAllUsers()
    {
        $allUsers = User::all();
        $this->assertIsArray($allUsers, "All users result should be array");
        $this->assertGreaterThanOrEqual(1, count($allUsers));
    }

    public function testDeleteUser()
    {
        $deleted = User::delete(self::$createdUserId);
        $this->assertTrue($deleted, "User should be deleted");

        $user = User::find(self::$createdUserId);
        $this->assertNull($user, "Deleted user should not exist");
    }
}
