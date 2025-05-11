<?php

namespace PinIndia\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JsonPostOfficeSeeder extends Seeder
{
    public function run()
    {
        $start = microtime(true);
        $jsonPath = config('pinindia.data_path'); // 'pinindia/post_offices.json' by default

        if (!Storage::disk('local')->exists($jsonPath)) {
            $this->command->error('JSON file not found at: ' . $jsonPath);
            throw new \Exception('JSON file not found at: ' . $jsonPath);
            return;
        }

        $data = json_decode(Storage::get($jsonPath), true);

        $chunk_size = 1000;

        // Lookup arrays with auto-increment IDs
        $states = [];
        $districts = [];
        $circles = [];
        $regions = [];
        $divisions = [];
        $pincodes = [];

        $stateId = $districtId = $circleId = $regionId = $divisionId = $pincodeId = 1;
        $postOffices = [];

        foreach ($data as $item) {
            // -- State --
            $stateName = trim($item['statename']);
            if (!isset($states[$stateName])) {
                $states[$stateName] = [
                    'id' => $stateId++,
                    'name' => $stateName,
                ];
            }

            // -- District --
            $districtKey = $stateName . '|' . trim($item['district']);
            if (!isset($districts[$districtKey])) {
                $districts[$districtKey] = [
                    'id' => $districtId++,
                    'name' => trim($item['district']),
                    'state_id' => $states[$stateName]['id'],
                ];
            }

            // -- Circle --
            $circleName = trim($item['circlename']);
            if (!isset($circles[$circleName])) {
                $circles[$circleName] = [
                    'id' => $circleId++,
                    'name' => $circleName,
                ];
            }

            // -- Region --
            $regionKey = $circleName . '|' . trim($item['regionname']);
            if (!isset($regions[$regionKey])) {
                $regions[$regionKey] = [
                    'id' => $regionId++,
                    'name' => trim($item['regionname']),
                    'circle_id' => $circles[$circleName]['id'],
                ];
            }

            // -- Division --
            $divisionKey = $regionKey . '|' . trim($item['divisionname']);
            if (!isset($divisions[$divisionKey])) {
                $divisions[$divisionKey] = [
                    'id' => $divisionId++,
                    'name' => trim($item['divisionname']),
                    'region_id' => $regions[$regionKey]['id'],
                ];
            }

            // -- Pincode --
            $pincodeKey = $districtKey . '|' . $item['pincode'];
            if (!isset($pincodes[$pincodeKey])) {
                $pincodes[$pincodeKey] = [
                    'id' => $pincodeId++,
                    'pincode' => $item['pincode'],
                    'district_id' => $districts[$districtKey]['id'],
                    'division_id' => $divisions[$divisionKey]['id'],
                ];
            }

            // -- Post Office --
            $name = str_replace(
                [' B.O', ' BO', ' G.P.O', ' GPO', ' S.O', ' SO', ' H.O', ' HO', ' P.O', ' PO'],
                '',
                trim($item['officename'])
            );

            $postOffices[] = [
                'pincode_id' => $pincodes[$pincodeKey]['id'],
                'name' => $name,
                'office' => trim($item['officename']),
                'latitude' => is_numeric($item['latitude']) ? $item['latitude'] : null,
                'longitude' => is_numeric($item['longitude']) ? $item['longitude'] : null,
                'type' => $item['officetype'] ?? null,
                'delivery' => $item['delivery'] ?? null,
            ];
        }

        $table_prefix = config('pinindia.table_prefix') ? config('pinindia.table_prefix') . '_' : '';
        
        try {
            // Disable foreign key checks and truncate tables
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            DB::table($table_prefix.'states')->truncate();
            DB::table($table_prefix.'districts')->truncate();
            DB::table($table_prefix.'circles')->truncate();
            DB::table($table_prefix.'regions')->truncate();
            DB::table($table_prefix.'divisions')->truncate();
            DB::table($table_prefix.'pincodes')->truncate();
            DB::table($table_prefix.'post_offices')->truncate();

            // ✅ Bulk Inserts
            DB::table($table_prefix.'states')->insert(array_values($states));
            DB::table($table_prefix.'districts')->insert(array_values($districts));
            DB::table($table_prefix.'circles')->insert(array_values($circles));
            DB::table($table_prefix.'regions')->insert(array_values($regions));
            DB::table($table_prefix.'divisions')->insert(array_values($divisions));

            // Chunk pincodes into chunk_size record inserts
            foreach (array_chunk(array_values($pincodes), $chunk_size) as $chunk) {
                DB::table($table_prefix.'pincodes')->insert($chunk);
            }

            // Chunk post offices into chunk_size record inserts
            foreach (array_chunk($postOffices, $chunk_size) as $chunk) {
                DB::table($table_prefix.'post_offices')->insert($chunk);
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        } catch (\Exception $e) {
            $this->command->error('Failed to insert data: ' . $e->getMessage());
            throw new \Exception('Failed to insert data: ' . $e->getMessage());
            return;
        }

        $this->command->info("Post offices inserted successfully.");
        $this->command->info("Total post offices: " . count($postOffices));

        $end = microtime(true);
        $duration = $end - $start;

        $this->command->info("✅ Seeding completed in " . floor($duration / 60) . " minutes and " . number_format($duration % 60, 2) . " seconds.");
    }
}
