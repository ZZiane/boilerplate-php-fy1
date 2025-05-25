<?php

namespace App\Repositories;

use App\Models\Employee;
use Core\Facades\RepositoryCache;

class EmployeeRepository extends RepositoryCache
{
    private array $employees = [];

    public function __construct()
    {
        parent::__construct();
    }

    public function findAllEmployees(): array
    {
        return array_values($this->employees);
    }

    public function findById($id): ?Employee
    {
        $data = $this->employees[$id] ?? null;
        if (is_array($data)){
            return $this->mapper($data);
        }

        return $data instanceof Employee ? $data : null;
    }

    public function augmentSalaryEmployee($id, $augmentation): ?Employee
    {
        if (!isset($this->employees[$id])) {
            return null;
        }

        $employee = $this->mapper($this->employees[$id]);
        $employee->setSalary($employee->getSalary() + $augmentation);

        $this->employees[$id] = $employee;
        $this->commit();

        return $employee;
    }

    public function updateEmployee($id, array $data): bool
    {
        if (!isset($this->employees[$id])) {
            return false;
        }

        $employee = $this->mapper($this->employees[$id]);

        if (isset($data['name'])) {
            $employee->setName($data['name']);
        }

        if (isset($data['email'])) {
            $employee->setEmail($data['email']);
        }

        $this->employees[$id] = $employee;
        $this->commit();

        return true;
    }

    public function deleteEmployee($id): bool
    {
        if (!isset($this->employees[$id])) {
            return false;
        }

        unset($this->employees[$id]);
        $this->commit();

        return true;
    }

    public function saveEmployee(Employee $employee): bool
    {
        $this->employees[$employee->getId()] = $employee;
        $this->commit();
        return true;
    }

    private function mapper(array $data): Employee{
            return new Employee(
                $data['id'],
                $data['name'],
                $data['salary'],
                $data['email'],
                $data['password'] ?? null,
                $data['photo'] ?? null
            );
    }

    protected function getData(): array {
        return $this->employees;
    }

    protected function setData(array $data): void {
        $this->employees = $data;
    }
}