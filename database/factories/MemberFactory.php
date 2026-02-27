<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Member;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = $this->faker->randomElement(['Nam', 'Nữ']);
        $status = $this->faker->randomElement(['Chính thức', 'Chính thức', 'Chính thức', 'Thân hữu', 'Chuyển đi', 'Hội viên liên kết']);
        $is_baptized = $this->faker->boolean(80); // 80% đã báp-têm
        
        $join_date = $this->faker->dateTimeBetween('-5 years', 'now');
        $baptism_date = $is_baptized ? clone $join_date : null;
        if ($baptism_date) {
            $baptism_date->modify('+'.rand(3, 12).' months'); // Chịu báp-têm sau khi tin Chúa một thời gian
        }

        return [
            'member_code' => 'TH' . \Carbon\Carbon::now()->format('y') . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'full_name' => $this->faker->name($gender === 'Nam' ? 'male' : 'female'),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'date_of_birth' => $this->faker->dateTimeBetween('-70 years', '-15 years')->format('Y-m-d'),
            'gender' => $gender,
            'is_baptized' => $is_baptized,
            'baptism_date' => $baptism_date ? $baptism_date->format('Y-m-d') : null,
            'joined_date' => $join_date->format('Y-m-d'),
            'status' => $status,
            'address' => $this->faker->address(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Member $member) {
            $member->sensitiveInfo()->create([
                'marital_status' => $this->faker->randomElement(['Độc thân', 'Đã kết hôn', 'Đã kết hôn', 'Góa']),
            ]);
        });
    }
}
