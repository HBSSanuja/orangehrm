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

namespace OrangeHRM\Tests\Admin\Service;

use OrangeHRM\Admin\Dao\LocationDao;
use OrangeHRM\Admin\Service\LocationService;
use OrangeHRM\Config\Config;
use OrangeHRM\Entity\Location;
use OrangeHRM\Pim\Dto\LocationSearchFilterParams;
use OrangeHRM\Tests\Util\TestCase;
use OrangeHRM\Tests\Util\TestDataService;

/**
 * @group Admin
 * @group Service
 */
class LocationServiceTest extends TestCase
{
    private LocationService $locationService;
    private string $fixture;

    /**
     * Set up method
     */
    protected function setUp(): void
    {
        $this->locationService = new LocationService();
        $this->fixture = Config::get(Config::PLUGINS_DIR) . '/orangehrmAdminPlugin/test/fixtures/LocationDao.yml';
        TestDataService::populate($this->fixture);
    }

    public function testGetLocationById(): void
    {
        $locationList = TestDataService::loadObjectList(Location::class, $this->fixture, 'Location');

        $locationDao = $this->getMockBuilder(LocationDao::class)->getMock();
        $locationDao->expects($this->once())
            ->method('getLocationById')
            ->with(1)
            ->will($this->returnValue($locationList[0]));

        $this->locationService->setLocationDao($locationDao);

        $result = $this->locationService->getLocationById(1);
        $this->assertEquals($result, $locationList[0]);
    }

    public function testSearchLocations(): void
    {
        $locationList = TestDataService::loadObjectList(Location::class, $this->fixture, 'Location');
        $locationSearchFilterParams = new LocationSearchFilterParams();
        $locationSearchFilterParams->setName('location 1');

        $locationDao = $this->getMockBuilder(LocationDao::class)->getMock();
        $locationDao->expects($this->once())
            ->method('searchLocations')
            ->with($locationSearchFilterParams)
            ->will($this->returnValue($locationList));

        $this->locationService->setLocationDao($locationDao);

        $result = $this->locationService->searchLocations($locationSearchFilterParams);
        $this->assertEquals($result, $locationList);
    }

    public function testGetSearchLocationListCount(): void
    {
        $locationSearchFilterParams = new LocationSearchFilterParams();
        $locationSearchFilterParams->setName('location 1');

        $locationDao = $this->getMockBuilder(LocationDao::class)->getMock();
        $locationDao->expects($this->once())
            ->method('getSearchLocationListCount')
            ->with($locationSearchFilterParams)
            ->will($this->returnValue(1));

        $this->locationService->setLocationDao($locationDao);

        $result = $this->locationService->getSearchLocationListCount($locationSearchFilterParams);
        $this->assertEquals($result, 1);
    }

    public function testGetNumberOfEmployeesForLocation(): void
    {
        $locationDao = $this->getMockBuilder(LocationDao::class)->getMock();
        $locationDao->expects($this->once())
            ->method('getNumberOfEmployeesForLocation')
            ->with(1)
            ->will($this->returnValue(2));

        $this->locationService->setLocationDao($locationDao);

        $result = $this->locationService->getNumberOfEmplyeesForLocation(1);
        $this->assertEquals($result, 2);
    }

    public function testGetLocationList(): void
    {
        $locationList = TestDataService::loadObjectList(Location::class, $this->fixture, 'Location');

        $locationDao = $this->getMockBuilder(LocationDao::class)->getMock();
        $locationDao->expects($this->once())
            ->method('getLocationList')
            ->will($this->returnValue($locationList));

        $this->locationService->setLocationDao($locationDao);

        $result = $this->locationService->getLocationList();
        $this->assertEquals($result, $locationList);
    }

    public function testGetLocationIdsForEmployees(): void
    {
        $empNumbers = [2, 34, 1, 20];
        $locationIds = [2, 3, 1];

        $locationDao = $this->getMockBuilder(LocationDao::class)->getMock();
        $locationDao->expects($this->once())
            ->method('getLocationIdsForEmployees')
            ->with($empNumbers)
            ->will($this->returnValue($locationIds));

        $this->locationService->setLocationDao($locationDao);

        $result = $this->locationService->getLocationIdsForEmployees($empNumbers);
        $this->assertEquals($locationIds, $result);
    }
}
