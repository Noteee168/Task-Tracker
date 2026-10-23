<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'judul' => fake()->sentence(4),
            'deskripsi' => fake()->paragraph(),
            'nama_klien' => fake()->company(),
            'prioritas' => fake()->randomElement(['rendah', 'sedang', 'tinggi']),
            'tanggal_deadline' => fake()->dateTimeBetween('now', '+2 weeks')->format('Y-m-d'),
            'status' => fake()->randomElement(['belum_dikerjakan', 'sedang_dikerjakan', 'menunggu_antrian', 'selesai']),
        ];
    }
}
