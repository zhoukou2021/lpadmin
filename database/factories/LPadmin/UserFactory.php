<?php

namespace Database\Factories\LPadmin;

use App\Models\LPadmin\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LPadmin\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => $this->faker->unique()->userName(),
            'password' => Hash::make('password'),
            'nickname' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->unique()->phoneNumber(),
            'avatar' => null,
            'gender' => $this->faker->randomElement([
                User::GENDER_UNKNOWN,
                User::GENDER_MALE,
                User::GENDER_FEMALE
            ]),
            'birthday' => $this->faker->date(),
            'status' => User::STATUS_ENABLED,
            'remark' => $this->faker->optional()->sentence(),
            'last_login_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'last_login_ip' => $this->faker->optional()->ipv4(),
        ];
    }

    /**
     * 指定用户为启用状态
     */
    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => User::STATUS_ENABLED,
        ]);
    }

    /**
     * 指定用户为禁用状态
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => User::STATUS_DISABLED,
        ]);
    }

    /**
     * 指定用户为男性
     */
    public function male(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => User::GENDER_MALE,
        ]);
    }

    /**
     * 指定用户为女性
     */
    public function female(): static
    {
        return $this->state(fn (array $attributes) => [
            'gender' => User::GENDER_FEMALE,
        ]);
    }

    /**
     * 指定用户今天创建
     */
    public function createdToday(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => now()->startOfDay()->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
        ]);
    }

    /**
     * 指定用户昨天创建
     */
    public function createdYesterday(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => now()->subDay()->startOfDay()->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
        ]);
    }

    /**
     * 指定用户本周创建
     */
    public function createdThisWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween(now()->startOfWeek(), now()->endOfWeek()),
        ]);
    }
}
