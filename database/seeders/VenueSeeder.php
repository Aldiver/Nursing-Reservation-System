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
                    'Cubicle #1 - Obstetric Ward',
                    'Cubicle #2 - Surgical Ward',
                    'Cubicle #3 - Geriatric Ward',
                    'Cubicle #4 - Orthopedic Ward',
                    'Cubicle #5 - Medical Ward',
                    'Cubicle #6 - Surgical Ward',
                    'Cubicle #7 - Pre-Natal Ward',
                    'Operating Room',
                    'Delivery Room',
                    'Central Supply Room',
                    'Amphitheater (40 persons)',
                    'Nutrition Room (30 persons)',
                ],
            ],
            [
                'name' => 'Nursing Skills Laboratory 2',
                'description' => 'Description for Nursing Skills Laboratory 2',
                'options' => [
                    'Ward with 9 bed',
                ],
            ],
        ];

        foreach ($venues as $venueData) {
            $venue = Venue::create([
                'name' => $venueData['name'],
                'description' => $venueData['description'],
            ]);

            foreach ($venueData['options'] as $optionName) {
                $option = Option::create([
                    'name' => $optionName,
                    'venue_id' => $venue->id,
                ]);
            }
        }
    }
}
