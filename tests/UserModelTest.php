<?php

namespace BB8\Potatoes\Tests;

use BB8\Tests\Mocks\User;
use BB8\Tests\Mocks\InvalidUser;

class UserModelTest extends \PHPUnit_Framework_TestCase
{
    protected $user;

    public function setUp()
    {
        User::setUpDB();
    }

    /**
     * Test to ensure all returned values are correct.
     */
    public function testGetAll()
    {
        $allUsers = User::getAll();

        $this->assertInternalType('array', $allUsers);
        //Assert correct property values returned
        $this->assertSame('Hedy Copeland', $allUsers[0]->full_name);
        $this->assertSame('faucibus ut, nulla. Cras eu tellus eu augue', $allUsers[1]->description);
        $this->assertSame('3246', $allUsers[1]->token);
    }

    public function testFind()
    {
        $user = User::find(1);

        //assert that returned type is correct
        $this->assertInstanceOf("BB8\Tests\Mocks\User", $user);
        $this->assertSame('1', $user->id);
        $this->assertSame('Hedy Copeland', $user->full_name);
    }

    public function testSelectWhere()
    {
        $users = User::selectWhere(['full_name' => 'Hedy Copeland']);
        $this->assertInternalType('array', $users);
        $this->assertSame('Hedy Copeland', $users[0]->full_name);
    }

    public function testCreate()
    {
        $user = new User();
        $user->full_name = 'James George Okpe';
        $user->description = 'There is no knowledge that is not power';
        $user->save();

        $count = count($user::getAll());
        $this->assertSame(4, $count);
    }

    public function testUpdate()
    {
        $user = User::find(2);
        $user->full_name = 'Darth Vader';
        $user->save();
        $updatedUser = User::find(2);
        $this->assertSame('Darth Vader', $updatedUser->full_name);
    }

    public function testDelete()
    {
        $user = User::find(1);
        User::destroy($user->id);
        $this->assertSame(null, User::find(1));
    }


    /**
     * @expectedException \Exception
     */
    public function testInvalidTableNameException()
    {
        InvalidUser::getAll();
    }
}
