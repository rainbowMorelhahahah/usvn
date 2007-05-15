<?php
/**
 * Class to test group's model
 *
 * @author Team USVN <contact@usvn.info>
 * @link http://www.usvn.info
 * @license http://www.cecill.info/licences/Licence_CeCILL_V2-en.txt CeCILL V2
 * @copyright Copyright 2007, Team USVN
 * @since 0.5
 * @package Db
 * @subpackage Table
 *
 * This software has been written at EPITECH <http://www.epitech.net>
 * EPITECH, European Institute of Technology, Paris - FRANCE -
 * This project has been realised as part of
 * end of studies project.
 *
 * $Id$
 */

// Call USVN_Auth_Adapter_DbTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
	define("PHPUnit_MAIN_METHOD", "USVN_AuthzTest::main");
}

require_once "PHPUnit/Framework/TestCase.php";
require_once "PHPUnit/Framework/TestSuite.php";

require_once 'www/USVN/autoload.php';

/**
 * Test class for USVN_Auth_Adapter_Db.
 * Generated by PHPUnit_Util_Skeleton on 2007-03-25 at 09:51:30.
 */
class USVN_AuthzTest extends USVN_Test_Test {

	public static function main() {
		require_once "PHPUnit/TextUI/TestRunner.php";

		$suite  = new PHPUnit_Framework_TestSuite("USVN_AuthzTest");
		$result = PHPUnit_TextUI_TestRunner::run($suite);
	}

	protected function setUp() {
		parent::setUp();
		$params = array ('host'     => 'localhost',
		'username' => 'usvn-test',
		'password' => 'usvn-test',
		'dbname'   => 'usvn-test');

		$this->db = Zend_Db::factory('PDO_MYSQL', $params);
		Zend_Db_Table::setDefaultAdapter($this->db);
		USVN_Db_Table::$prefix = "usvn_";
		USVN_Db_Utils::deleteAllTables($this->db);
		USVN_Db_Utils::loadFile($this->db, "www/SQL/SVNDB.sql");
		USVN_Db_Utils::loadFile($this->db, "www/SQL/mysql.sql");
	}

	protected function tearDown() {
		//		USVN_Db_Utils::deleteAllTables($this->db);
		parent::tearDown();
	}

	public function giveConfig() {
		$configArray = array('subversion' => array('path' => 'tests/tmp/'));
		$config = new Zend_Config($configArray);
		Zend_Registry::set('config', $config);
	}

	public function testNoGroup()
	{
		$this->giveConfig();

		$table = new USVN_Db_Table_Groups();
		$table->delete(1);

		$file = file_get_contents(Zend_Registry::get('config')->subversion->path . "authz");
		$this->assertEquals("[/]\n* = \n\n[groups]\n", $file);
	}

	public function testEmptyGroup()
	{
		$this->giveConfig();

		$table = new USVN_Db_Table_Groups();
		$table->delete(1);
		$group = $table->fetchNew();
		$group->name = "toto";
		$group->save();

		$file = file_get_contents(Zend_Registry::get('config')->subversion->path . "authz");
		$this->assertEquals("[/]\n* = \n\n[groups]\ntoto = \n", $file);
	}

	public function testOneUsersInOneGroup()
	{
		$this->giveConfig();

		$table = new USVN_Db_Table_Groups();
		$table->delete(1);
		$group = $table->fetchNew();
		/* @var $group USVN_Db_Table_Row_Group */
		$group->name = "toto";
		$group->save();

		list($user1) = $this->_generateUsers(1);
		/* @var $user1 USVN_Db_Table_Row_User */

		$group->addUser($user1);

		$file = file_get_contents(Zend_Registry::get('config')->subversion->path . "authz");
		$this->assertEquals("[/]\n* = \n\n[groups]\ntoto = user1\n", $file);
	}

	public function testTwoUsersInOneGroup()
	{
		$this->giveConfig();

		$table = new USVN_Db_Table_Groups();
		$table->delete(1);
		$group = $table->fetchNew();
		/* @var $group USVN_Db_Table_Row_Group */
		$group->name = "toto";
		$group->save();

		list($user1, $user2) = $this->_generateUsers(2);
		/* @var $user1 USVN_Db_Table_Row_User */
		/* @var $user2 USVN_Db_Table_Row_User */

		$group->addUser($user1);
		$group->addUser($user2);

		$file = file_get_contents(Zend_Registry::get('config')->subversion->path . "authz");
		$this->assertEquals("[/]\n* = \n\n[groups]\ntoto = user1, user2\n", $file);
	}

	public function testThreeUsersInOneGroup()
	{
		$this->giveConfig();

		$table = new USVN_Db_Table_Groups();
		$table->delete(1);
		$group = $table->fetchNew();
		/* @var $group USVN_Db_Table_Row_Group */
		$group->name = "toto";
		$group->save();

		list($user1, $user2, $user3) = $this->_generateUsers(3);
		/* @var $user1 USVN_Db_Table_Row_User */
		/* @var $user2 USVN_Db_Table_Row_User */
		/* @var $user3 USVN_Db_Table_Row_User */

		$group->addUser($user1);
		$group->addUser($user2);
		$group->addUser($user3);

		$file = file_get_contents(Zend_Registry::get('config')->subversion->path . "authz");
		$this->assertEquals("[/]\n* = \n\n[groups]\ntoto = user1, user2, user3\n", $file);
	}

	public function testOneUsersInTwoGroups()
	{
		$this->giveConfig();

		list($group1, $group2) = $this->_generateGroups(2);
		/* @var $group1 USVN_Db_Table_Row_Group */
		/* @var $group2 USVN_Db_Table_Row_Group */

		list($user1) = $this->_generateUsers(2);
		/* @var $user1 USVN_Db_Table_Row_User */

		$group1->addUser($user1);
		$group2->addUser($user1);

		$file = file_get_contents(Zend_Registry::get('config')->subversion->path . "authz");
		$this->assertEquals("[/]\n* = \n\n[groups]\ngroup1 = user1\ngroup2 = user1\n", $file);
	}

	public function testTwoUsersInTwoGroups()
	{
		$this->giveConfig();

		list($group1, $group2) = $this->_generateGroups(2);
		/* @var $group1 USVN_Db_Table_Row_Group */
		/* @var $group2 USVN_Db_Table_Row_Group */

		list($user1, $user2) = $this->_generateUsers(2);
		/* @var $user1 USVN_Db_Table_Row_User */
		/* @var $user2 USVN_Db_Table_Row_User */

		$group1->addUser($user1);
		$group2->addUser($user2);

		$file = file_get_contents(Zend_Registry::get('config')->subversion->path . "authz");
		$this->assertEquals("[/]\n* = \n\n[groups]\ngroup1 = user1\ngroup2 = user2\n", $file);
	}

	public function testThreeUsersInTwoGroups()
	{
		$this->giveConfig();

		list($group1, $group2) = $this->_generateGroups(2);
		/* @var $group1 USVN_Db_Table_Row_Group */
		/* @var $group2 USVN_Db_Table_Row_Group */

		list($user1, $user2, $user3) = $this->_generateUsers(3);
		/* @var $user1 USVN_Db_Table_Row_User */
		/* @var $user2 USVN_Db_Table_Row_User */
		/* @var $user3 USVN_Db_Table_Row_User */

		$group1->addUser($user1);
		$group1->addUser($user2);
		$group1->addUser($user3);
		$group2->addUser($user1);
		$group2->addUser($user2);

		$file = file_get_contents(Zend_Registry::get('config')->subversion->path . "authz");
		$this->assertEquals("[/]\n* = \n\n[groups]\ngroup1 = user1, user2, user3\ngroup2 = user1, user2\n", $file);
	}

	public function testThreeUsersInTwoGroupsAndOneProject()
	{
		$this->giveConfig();

		list($group1, $group2) = $this->_generateGroups(2);
		/* @var $group1 USVN_Db_Table_Row_Group */
		/* @var $group2 USVN_Db_Table_Row_Group */

		list($user1, $user2, $user3) = $this->_generateUsers(3);
		/* @var $user1 USVN_Db_Table_Row_User */
		/* @var $user2 USVN_Db_Table_Row_User */
		/* @var $user3 USVN_Db_Table_Row_User */

		$group1->addUser($user1);
		$group1->addUser($user2);
		$group1->addUser($user3);
		$group2->addUser($user1);
		$group2->addUser($user2);

		$table = new USVN_Db_Table_Projects();
		$table->delete(1);
		$project1 = $table->fetchNew();
		$project1->name = "project1";
		$project1->start_date = "NOW()";
		$project1->save();

		$file = file_get_contents(Zend_Registry::get('config')->subversion->path . "authz");
		$this->assertEquals("[/]\n* = \n\n[groups]\ngroup1 = user1, user2, user3\ngroup2 = user1, user2\n\n\n# Project project1\n", $file);
	}

	public function testThreeUsersInTwoGroupsAndOneProjectWithPermission()
	{
		$this->giveConfig();

		list($group1, $group2) = $this->_generateGroups(2);
		/* @var $group1 USVN_Db_Table_Row_Group */
		/* @var $group2 USVN_Db_Table_Row_Group */

		list($user1, $user2, $user3) = $this->_generateUsers(3);
		/* @var $user1 USVN_Db_Table_Row_User */
		/* @var $user2 USVN_Db_Table_Row_User */
		/* @var $user3 USVN_Db_Table_Row_User */

		$group1->addUser($user1);
		$group1->addUser($user2);
		$group1->addUser($user3);
		$group2->addUser($user1);
		$group2->addUser($user2);

		list($project1) = $this->_generateProjects(1);
		/* @var $project1 USVN_Db_Table_Row_Project */

		$table = new USVN_Db_Table_FilesRights();
		$table->delete(1);
		for ($i = 1; $i <= 3; $i++) {
			${"files_rights" . $i} = $table->fetchNew();
			${"files_rights" . $i}->projects_id = $project1->id;
			${"files_rights" . $i}->path = "/directory$i/";
			${"files_rights" . $i}->save();
		}

		$file = file_get_contents(Zend_Registry::get('config')->subversion->path . "authz");
		$this->assertEquals("[/]\n* = \n\n[groups]\ngroup1 = user1, user2, user3\ngroup2 = user1, user2\n\n\n# Project project1\n[project1:/directory1/]\n\n[project1:/directory2/]\n\n[project1:/directory3/]\n\n", $file);
	}

	public function testThreeUsersInTwoGroupsAndOneProjectWithPermissionAndGroupPermissions()
	{
		$this->giveConfig();

		list($group1, $group2, $group3) = $this->_generateGroups(3);
		/* @var $group1 USVN_Db_Table_Row_Group */
		/* @var $group2 USVN_Db_Table_Row_Group */

		list($user1, $user2, $user3) = $this->_generateUsers(3);
		/* @var $user1 USVN_Db_Table_Row_User */
		/* @var $user2 USVN_Db_Table_Row_User */
		/* @var $user3 USVN_Db_Table_Row_User */

		$group1->addUser($user1);
		$group1->addUser($user2);
		$group1->addUser($user3);
		$group2->addUser($user1);
		$group2->addUser($user2);
		$group3->addUser($user1);
		$group3->addUser($user3);

		list($project1, $project2, $project3) = $this->_generateProjects(3);
		/* @var $project1 USVN_Db_Table_Row_Project */
		/* @var $project2 USVN_Db_Table_Row_Project */
		/* @var $project3 USVN_Db_Table_Row_Project */

		$table = new USVN_Db_Table_FilesRights();
		$table->delete(1);
		for ($i = 1; $i <= 5; $i++) {
			for ($j = 1; $j <= 3; $j++) {
				${"files_rights" . $i . $j} = $table->fetchNew();
				${"files_rights" . $i . $j}->projects_id = ${"project" . $j}->id;
				${"files_rights" . $i . $j}->path = "/directory$i/";
				${"files_rights" . $i . $j}->save();
				for ($k = 1; $k <= 3; $k++) {
					if ($k == $j) {
						$tmp = new USVN_Db_Table_GroupsToFilesRights();
						$array = array();
						$array["groups_id"] = ${"group" . $k}->id;
						$array["files_rights_is_readable"] = true;
						$array["files_rights_is_writable"] = true;
						$array["files_rights_id"] = ${"files_rights" . $i . $j}->id;
						$tmp = $tmp->createRow($array);
						$tmp->save();
					} else {
						if ($k & 1 && $i & 1) {
							$tmp = new USVN_Db_Table_GroupsToFilesRights();
							$array = array();
							$array["groups_id"] = ${"group" . $k}->id;
							$array["files_rights_is_readable"] = true;
							$array["files_rights_is_writable"] = false;
							$array["files_rights_id"] = ${"files_rights" . $i . $j}->id;
							$tmp = $tmp->createRow($array);
							$tmp->save();
						} else {
							$tmp = new USVN_Db_Table_GroupsToFilesRights();
							$array = array();
							$array["groups_id"] = ${"group" . $k}->id;
							$array["files_rights_is_readable"] = false;
							$array["files_rights_is_writable"] = false;
							$array["files_rights_id"] = ${"files_rights" . $i . $j}->id;
							$tmp = $tmp->createRow($array);
							$tmp->save();
						}
					}
				}
			}
		}

		$file = file_get_contents(Zend_Registry::get('config')->subversion->path . "authz");
		Zend_Debug::dump($file);
		$this->assertEquals("[/]
* = 

[groups]
group1 = user1, user2, user3
group2 = user1, user2
group3 = user1, user3


# Project project1
[project1:/directory1/]
@group1 = rw
@group2 = 
@group3 = r

[project1:/directory2/]
@group1 = rw
@group2 = 
@group3 = 

[project1:/directory3/]
@group1 = rw
@group2 = 
@group3 = r

[project1:/directory4/]
@group1 = rw
@group2 = 
@group3 = 

[project1:/directory5/]
@group1 = rw
@group2 = 
@group3 = r



# Project project2
[project2:/directory1/]
@group1 = r
@group2 = rw
@group3 = r

[project2:/directory2/]
@group1 = 
@group2 = rw
@group3 = 

[project2:/directory3/]
@group1 = r
@group2 = rw
@group3 = r

[project2:/directory4/]
@group1 = 
@group2 = rw
@group3 = 

[project2:/directory5/]
@group1 = r
@group2 = rw
@group3 = r



# Project project3
[project3:/directory1/]
@group1 = r
@group2 = 
@group3 = rw

[project3:/directory2/]
@group1 = 
@group2 = 
@group3 = rw

[project3:/directory3/]
@group1 = r
@group2 = 
@group3 = rw

[project3:/directory4/]
@group1 = 
@group2 = 
@group3 = rw

[project3:/directory5/]
@group1 = r
@group2 = 
@group3 = rw

", $file);
		exit(0);
	}

	/**
	 * Genere un tableau d'utilisateur
	 *
	 * @param int $n
	 */
	function _generateUsers($n)
	{
		$table = new USVN_Db_Table_Users();
		$table->delete(1);
		$ret = array();
		for ($i = 1; $i <= $n; $i++) {
			$ret[$i - 1] = $table->fetchNew();
			$ret[$i - 1]->login = "user{$i}";
			$ret[$i - 1]->password = "user{$i}user{$i}";
			$ret[$i - 1]->save();
		}
		return $ret;
	}

	/**
	 * Genere un tableau de groupe
	 *
	 * @param int $n
	 */
	function _generateGroups($n)
	{
		$table = new USVN_Db_Table_Groups();
		$table->delete(1);
		$ret = array();
		for ($i = 1; $i <= $n; $i++) {
			$ret[$i - 1] = $table->fetchNew();
			$ret[$i - 1]->name = "group{$i}";
			$ret[$i - 1]->save();
		}
		return $ret;
	}

	/**
	 * Genere un tableau de projet
	 *
	 * @param int $n
	 */
	function _generateProjects($n)
	{
		$table = new USVN_Db_Table_Projects();
		$table->delete(1);
		$ret = array();
		for ($i = 1; $i <= $n; $i++) {
			$ret[$i - 1] = $table->fetchNew();
			$ret[$i - 1]->name = "project{$i}";
			$ret[$i - 1]->start_date = "NOW()";
			$ret[$i - 1]->save();
		}
		return $ret;
	}

}

// Call USVN_Auth_Adapter_DbTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "USVN_AuthzTest::main") {
	USVN_AuthzTest::main();
}
?>
