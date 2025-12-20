<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Address::class;

    /**
     * Australian states for random selection.
     *
     * @var array<string, string>
     */
    protected array $australianStates = [
        'NSW' => 'New South Wales',
        'VIC' => 'Victoria',
        'QLD' => 'Queensland',
        'WA' => 'Western Australia',
        'SA' => 'South Australia',
        'TAS' => 'Tasmania',
        'ACT' => 'Australian Capital Territory',
        'NT' => 'Northern Territory',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $state = fake()->randomElement(array_keys($this->australianStates));

        return [
            'user_id' => User::factory(),
            'label' => fake()->randomElement(['Home', 'Work', 'Other']),
            'street' => fake()->streetAddress(),
            'suburb' => fake()->optional(0.7)->city(),
            'city' => fake()->city(),
            'state' => $state,
            'postcode' => (string) fake()->numberBetween(1000, 9999),
            'country' => 'AU',
            'is_default' => false,
        ];
    }

    /**
     * Indicate address is the default.
     */
    public function default(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Set label to Home.
     */
    public function home(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'Home',
        ]);
    }

    /**
     * Set label to Work.
     */
    public function work(): static
    {
        return $this->state(fn(array $attributes) => [
            'label' => 'Work',
        ]);
    }

    /**
     * Set address in Victoria.
     */
    public function victoria(): static
    {
        return $this->state(fn(array $attributes) => [
            'suburb' => fake()->randomElement(['South Yarra', 'Richmond', 'Fitzroy', 'Carlton']),
            'city' => 'Melbourne',
            'state' => 'VIC',
            'postcode' => (string) fake()->numberBetween(3000, 3999),
        ]);
    }

    /**
     * Set address in New South Wales.
     */
    public function newSouthWales(): static
    {
        return $this->state(fn(array $attributes) => [
            'suburb' => fake()->randomElement(['Parramatta', 'Bondi', 'Manly', 'Surry Hills']),
            'city' => 'Sydney',
            'state' => 'NSW',
            'postcode' => (string) fake()->numberBetween(2000, 2999),
        ]);
    }

    /**
     * Set address in Queensland.
     */
    public function queensland(): static
    {
        return $this->state(fn(array $attributes) => [
            'suburb' => fake()->randomElement(['Fortitude Valley', 'South Bank', 'West End']),
            'city' => 'Brisbane',
            'state' => 'QLD',
            'postcode' => (string) fake()->numberBetween(4000, 4999),
        ]);
    }

    /**
     * Set a specific suburb.
     */
    public function inSuburb(string $suburb, string $city = 'Melbourne', string $state = 'VIC', string $postcode = '3000'): static
    {
        return $this->state(fn(array $attributes) => [
            'suburb' => $suburb,
            'city' => $city,
            'state' => $state,
            'postcode' => $postcode,
        ]);
    }

    /**
     * Create address for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
