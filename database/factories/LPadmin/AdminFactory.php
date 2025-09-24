<?php

namespace Database\Factories\LPadmin;

use App\Models\LPadmin\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LPadmin\Admin>
 */
class AdminFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Admin::class;

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
            'status' => Admin::STATUS_ENABLED,
            'last_login_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
            'last_login_ip' => $this->faker->optional()->ipv4(),
        ];
    }

    /**
     * 指定管理员为启用状态
     */
    public function enabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Admin::STATUS_ENABLED,
        ]);
    }

    /**
     * 指定管理员为禁用状态
     */
    public function disabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Admin::STATUS_DISABLED,
        ]);
    }
}
