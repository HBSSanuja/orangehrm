<?php
/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 */

namespace OrangeHRM\Tests\Pim\Dao;

use OrangeHRM\Config\Config;
use OrangeHRM\Entity\Employee;
use OrangeHRM\Pim\Dao\EmployeeDao;
use OrangeHRM\Tests\Util\TestCase;
use OrangeHRM\Tests\Util\TestDataService;

/**
 * @group Pim
 * @group Dao
 */
class EmployeeDaoTest extends TestCase
{
    private EmployeeDao $employeeDao;
    protected string $fixture;

    protected function setUp(): void
    {
        $this->employeeDao = new EmployeeDao();
        $this->fixture = Config::get(Config::PLUGINS_DIR) . '/orangehrmPimPlugin/test/fixtures/EmployeeDao.yml';
        TestDataService::populate($this->fixture);
    }

    public function testGetSubordinateIdListBySupervisorId(): void
    {
        $subordinateIdList = $this->employeeDao->getSubordinateIdListBySupervisorId(3, true);
        $this->assertEquals(2, count($subordinateIdList));

        $subordinateIdList = $this->employeeDao->getSubordinateIdListBySupervisorId(4, true);
        $this->assertEquals(3, count($subordinateIdList));

        $subordinateIdList = $this->employeeDao->getSubordinateIdListBySupervisorId(4, false);
        $this->assertEquals(1, count($subordinateIdList));

        $subordinateIdList = $this->employeeDao->getSubordinateIdListBySupervisorId(10, true);
        $this->assertEquals(0, count($subordinateIdList));

        $subordinateIdList = $this->employeeDao->getSubordinateIdListBySupervisorId(1, true);
        $this->assertEquals(0, count($subordinateIdList));

        $subordinateIdList = $this->employeeDao->getSubordinateIdListBySupervisorId(3, true);
        $subordinateIdArray = [1, 2];
        $this->assertEquals($subordinateIdArray, $subordinateIdList);
    }

    /**
     * @group ReportingChain
     */
    public function testGetSubordinateList(): void
    {
        $chain = $this->employeeDao->getSubordinateList(3, false, true);
        $this->assertTrue(count($chain) > 0);
    }

    /**
     * @group ReportingChain
     */
    public function testGetSubordinateList_ReportingChain_Simple2LevelHierarchy(): void
    {
        $chain = $this->employeeDao->getSubordinateList(3, false, true);
        $this->assertTrue(is_array($chain));
        $this->assertEquals(2, count($chain));

        list($subordinate1, $subordinate2) = $chain;

        $this->assertTrue($subordinate1 instanceof Employee);
        $this->assertEquals(1, $subordinate1->getEmpNumber());

        $this->assertTrue($subordinate2 instanceof Employee);
        $this->assertEquals(2, $subordinate2->getEmpNumber());
    }

    /**
     * @group ReportingChain
     */
    public function testGetSubordinateList_ReportingChain_3LevelHierarchy(): void
    {
        $chain = $this->employeeDao->getSubordinateList(5, false, true);
        $this->assertTrue(is_array($chain));
        $this->assertEquals(3, count($chain));

        list($subordinate1, $subordinate2, $subordinate3) = $chain;

        $this->assertTrue($subordinate1 instanceof Employee);
        $this->assertEquals(3, $subordinate1->getEmpNumber());

        $this->assertTrue($subordinate2 instanceof Employee);
        $this->assertEquals(1, $subordinate2->getEmpNumber());

        $this->assertTrue($subordinate3 instanceof Employee);
        $this->assertEquals(2, $subordinate3->getEmpNumber());

        $chain = $this->employeeDao->getSubordinateList(4, false, true);
        $this->assertTrue(is_array($chain));
        $this->assertEquals(3, count($chain));

        list($subordinate1, $subordinate2, $subordinate3) = $chain;

        $this->assertTrue($subordinate1 instanceof Employee);
        $this->assertEquals(3, $subordinate1->getEmpNumber());

        $this->assertTrue($subordinate2 instanceof Employee);
        $this->assertEquals(1, $subordinate2->getEmpNumber());

        $this->assertTrue($subordinate3 instanceof Employee);
        $this->assertEquals(2, $subordinate3->getEmpNumber());
    }

    public function testIsSupervisor(): void
    {
        $result = $this->employeeDao->isSupervisor(3);

        $this->assertTrue($result);
    }

    /**
     * @param Employee[] $employees
     * @return int[]
     */
    protected function getEmployeeIds(array $employees): array
    {
        $ids = [];
        foreach ($employees as $employee) {
            $ids[] = $employee->getEmpNumber();
        }
        return $ids;
    }

    public function testGetEmployeeIdList(): void
    {
        $employeeIdList = $this->employeeDao->getEmployeeIdList();
        $this->assertEquals(5, count($employeeIdList));

        $employeeIdList = $this->employeeDao->getEmployeeIdList();
        $employees = TestDataService::loadObjectList(Employee::class, $this->fixture, 'Employee');
        $employeeIdArray = $this->getEmployeeIds($employees);
        $this->assertEquals($employeeIdArray, $employeeIdList);
    }

    public function testGetEmployeeIdListOneEmployee(): void
    {
        $q = $this->getEntityManager()->createQueryBuilder()->from(Employee::class, 'e');
        $q->delete()->where('e.empNumber > 1')->getQuery()->execute();

        $employeeIdList = $this->employeeDao->getEmployeeIdList();
        $this->assertTrue(is_array($employeeIdList));
        $this->assertEquals(1, count($employeeIdList));
        $this->assertEquals(1, $employeeIdList[0]);
    }
}
