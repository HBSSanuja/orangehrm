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

namespace OrangeHRM\Admin\Service;

use OrangeHRM\Admin\Dao\LocationDao;
use OrangeHRM\Admin\Service\Model\LocationModel;
use OrangeHRM\Core\Exception\DaoException;
use OrangeHRM\Core\Traits\Service\NormalizerServiceTrait;
use OrangeHRM\Core\Traits\UserRoleManagerTrait;
use OrangeHRM\Entity\Location;
use OrangeHRM\Pim\Dto\LocationSearchFilterParams;
use OrangeHRM\Pim\Traits\Service\EmployeeServiceTrait;

class LocationService
{
    use UserRoleManagerTrait;
    use NormalizerServiceTrait;
    use EmployeeServiceTrait;

    /**
     * @var LocationDao|null
     */
    private ?LocationDao $locationDao = null;

    /**
     * @return LocationDao
     */
    public function getLocationDao(): LocationDao
    {
        if (!($this->locationDao instanceof LocationDao)) {
            $this->locationDao = new LocationDao();
        }
        return $this->locationDao;
    }

    /**
     * @param LocationDao $locationDao
     */
    public function setLocationDao(LocationDao $locationDao): void
    {
        $this->locationDao = $locationDao;
    }

    /**
     * Get Location by id
     * @param int $locationId
     * @return Location|null
     * @throws DaoException
     */
    public function getLocationById(int $locationId): ?Location
    {
        return $this->getLocationDao()->getLocationById($locationId);
    }

    /**
     * Search location by location name, city and country.
     *
     * @param LocationSearchFilterParams $locationSearchFilterParams
     * @return Location[]
     * @throws DaoException
     */
    public function searchLocations(LocationSearchFilterParams $locationSearchFilterParams): array
    {
        return $this->getLocationDao()->searchLocations($locationSearchFilterParams);
    }

    /**
     * Get location count of the search results.
     *
     * @param LocationSearchFilterParams $locationSearchFilterParams
     * @return int
     * @throws DaoException
     */
    public function getSearchLocationListCount(LocationSearchFilterParams $locationSearchFilterParams): int
    {
        return $this->getLocationDao()->getSearchLocationListCount($locationSearchFilterParams);
    }

    /**
     * Get total number of employees in a location.
     *
     * @param int $locationId
     * @return int
     * @throws DaoException
     */
    public function getNumberOfEmplyeesForLocation(int $locationId): int
    {
        return $this->getLocationDao()->getNumberOfEmployeesForLocation($locationId);
    }

    /**
     * Get all locations
     *
     * @return Location[]
     */
    public function getLocationList(): array
    {
        return $this->getLocationDao()->getLocationList();
    }

    /**
     * Get LocationIds for Employees with the given employee numbers
     *
     * @param int[] $empNumbers Array of employee numbers
     * @return int[] of locationIds of the given employees
     */
    public function getLocationIdsForEmployees(array $empNumbers): array
    {
        return $this->getLocationDao()->getLocationIdsForEmployees($empNumbers);
    }

    /**
     * @param int|null $empNumber
     * @return array
     * @throws DaoException
     */
    public function getAccessibleLocationsArray(?int $empNumber = null): array
    {
        $employeeLocationsIds = [];
        if (!is_null($empNumber)) {
            $employee = $this->getEmployeeService()->getEmployeeByEmpNumber($empNumber);
            foreach ($employee->getLocations() as $location) {
                $employeeLocationsIds[] = $location;
            }
        }
        $accessibleLocationIds = $this->getUserRoleManager()->getAccessibleEntityIds(Location::class);
        $accessibleLocationIds = array_unique(array_merge($accessibleLocationIds, $employeeLocationsIds));
        $accessibleLocations = $this->getLocationDao()->getLocationsByIds($accessibleLocationIds);
        return $this->getNormalizerService()->normalizeArray(LocationModel::class, $accessibleLocations);
    }
}
