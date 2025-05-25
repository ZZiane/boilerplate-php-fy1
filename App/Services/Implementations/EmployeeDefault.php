<?php
namespace App\Services\Implementations;
use App\Repositories\EmployeeRepository;
use App\Services\Interfaces\EmployeeService;
use App\Services\Interfaces\FileService;
use App\Models\Employee;
use ErrorException;

class EmployeeDefault implements EmployeeService
{

    public function __construct(private EmployeeRepository $employeeRepository = new EmployeeRepository(),
    private FileService $fileService = new FileDefault())
    {
    }
    public function getEmployee($id): Employee
    {
        return $this->employeeRepository->findById($id);
    }

    public function getEmployees(?array $data): array
    {
        return $this->employeeRepository->findAllEmployees();
    }

    public function augmentSalary(int $employeeId, float $augmentation): Employee
    {

        $employee = $this->employeeRepository->augmentSalaryEmployee($employeeId, $augmentation);
        return $employee;
        
        throw new ErrorException("We cant do this now");
    }

    public function updateEmployee(int $employeeId, array $data): Employee
    {
        $employee = $this->employeeRepository->findById($employeeId);

        if (!$employee) {
            throw new \RuntimeException("Employee not found.");
        }

        $allowedFields = ['name', 'email', 'password'];
        $updateData = [];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $field === 'password'
                    ? password_hash($data[$field], PASSWORD_BCRYPT)
                    : $data[$field];
            }
        }

        if (empty($updateData)) {
            throw new \InvalidArgumentException("No valid fields provided for update.");
        }

        $this->employeeRepository->updateEmployee($employeeId, $updateData);

        return $this->employeeRepository->findById($employeeId);
    }


    

    public function createEmployee($name, $salary, $email, $password, $photo) : Employee {
        $ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid() . ($ext ? '.' . $ext : '');
        $this->fileService->upload('/employees_images', $photo, $uniqueName);
        $photoPath = '/employees_images/'.$uniqueName;
        $password = password_hash($password, PASSWORD_BCRYPT);
        $employee = new Employee(abs(crc32(uniqid())) , $name, $salary, $email, $password, $photoPath);
        if($this->employeeRepository->saveEmployee($employee))
            return $employee;
        throw new ErrorException("We cant do this now");
    }

    public function deleteEmployee(int $employeeId): Employee
    {
        $employee = $this->employeeRepository->findById($employeeId);
        if(!$employee){
            throw new ErrorException("We didn't find employee with this id");
        }
        if ($this->employeeRepository->deleteEmployee($employeeId)) {
            return $employee;
        }
        throw new ErrorException("We cant do this now");
    }

}
