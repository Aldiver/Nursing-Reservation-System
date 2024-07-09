<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Venue;
use App\Models\Option;

class VenueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $venues = [
            [
                'name' => 'Nursing Skill Laboratory 1',
                'description' => 'Description for Nursing Skill Laboratory 1',
                'options' => [
                    ['name' => 'Cubicle #1 - Obstetric Ward'],
                    ['name' => 'Cubicle #2 - Surgical Ward'],
                    ['name' => 'Cubicle #3 - Geriatric Ward'],
                    ['name' => 'Cubicle #4 - Orthopedic Ward'],
                    ['name' => 'Cubicle #5 - Medical Ward'],
                    ['name' => 'Cubicle #6 - Surgical Ward'],
                    ['name' => 'Cubicle #7 - Pre-Natal Ward'],
                    ['name' => 'Operating Room'],
                    ['name' => 'Delivery Room'],
                    ['name' => 'Central Supply Room'],
                    ['name' => 'Amphitheater (40 persons)', 'with_pax' => true, 'max' => 40],
                    ['name' => 'Nutrition Room (30 persons)', 'with_pax' => true, 'max' => 30],
                ],
            ],
            [
                'name' => 'Nursing Skills Laboratory 2',
                'description' => 'Description for Nursing Skills Laboratory 2',
                'options' => [
                    ['name' => 'Ward with 9 bed'],
                ],
            ],
        ];

        foreach ($venues as $venueData) {
            $venue = Venue::create([
                'name' => $venueData['name'],
                'description' => $venueData['description'],
            ]);

            foreach ($venueData['options'] as $optionData) {
                Option::create([
                    'name' => $optionData['name'],
                    'venue_id' => $venue->id,
                    'with_pax' => $optionData['with_pax'] ?? false,
                    'max_pax' => $optionData['max'] ?? null,
                ]);
            }
        }
    }
}
