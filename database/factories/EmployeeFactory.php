<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Employee::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'firstname' => $this->faker->firstName,
            'lastname' => $this->faker->lastName,
            'username' => $this->faker->userName,
            'email' => $this->faker->unique()->safeEmail,
            'gender' => $this->faker->randomElement(['male', 'female']),
            'phone' => $this->faker->phoneNumber,
            'role_users_id' => 2, // Default to employee role
            'remaining_leave' => 20,
            'total_leave' => 20,
            'sick_leave' => 5,
            'casual_leave' => 15,
            'birth_date' => $this->faker->date(),
            'department_id' => 1, // Default department
            'designation_id' => 1, // Default designation
            'office_shift_id' => 1, // Default shift
            'joining_date' => $this->faker->date(),
            'marital_status' => $this->faker->randomElement(['single', 'married']),
            'employment_type' => $this->faker->randomElement(['full_time', 'part_time', 'contract']),
            'city' => $this->faker->city,
            'province' => $this->faker->state,
            'zipcode' => $this->faker->postcode,
            'address' => $this->faker->address,
            'country' => $this->faker->country,
            'company_id' => 1, // Default company
            'hourly_rate' => $this->faker->randomFloat(2, 10, 50),
            'basic_salary' => $this->faker->numberBetween(3000, 10000),
            'mode' => 'monthly',
            'expected_hours' => 8.0,
        ];
    }
}
