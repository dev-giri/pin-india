<?php

namespace PinIndia\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DownloadCommand extends Command
{
    protected $signature = 'pinindia:download';

    protected $description = 'Download PinIndia data from API.DATA.GOV.IN';

    public function handle()
    {
        $this->info('⏳ Downloading PinIndia data from API.DATA.GOV.IN. This may take a while.');

        $apiKey = config('pinindia.data_gov_in_api_key');
        if (!$apiKey) {
            $this->error('API key not found. Please set DATA_GOV_IN_API_KEY in your .env file.');
            return;
        }

        $batchSize = 10000;
        $limit = 165314;
        $offset = 0;
        $data = [];

        try {
            while ($offset < $limit) {
                sleep(1);
                $url = "https://api.data.gov.in/resource/5c2f62fe-5afa-4119-a499-fec9d604d5bd?api-key={$apiKey}&offset={$offset}&limit={$batchSize}&format=json";
                $this->info("🌐 Fetching data from API.DATA.GOV.IN: {$url}");
                $response = Http::timeout(60)->get($url);
                $data = array_merge($data, $response->json()['records']);
                $limit = $response->json()['total']; // Update the limit to the total number of records
                $offset += $batchSize; // Increment the offset by the batch size amount
                $this->info("Fetched " . count($data) . " records so far.");
            }
        } catch (\Exception $e) {
            $this->error('Failed to fetch data: ' . $e->getMessage());
            return;
        }

        if (!isset($data) || !is_array($data) || empty($data)) {
            $this->error('No data found to download.');
            return;
        }

        $this->info("✅ Data fetched successfully. Total records: " . count($data));

        //Save as JSON file
        $jsonPath = config('pinindia.data_path'); // 'pinindia/post_offices.json' by default
        Storage::disk('local')->put($jsonPath, json_encode($data));
        $this->info("✅ Data saved as JSON file at: " . $jsonPath);
    }
}
