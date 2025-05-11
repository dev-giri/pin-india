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
        $apiKey = config('pinindia.data_gov_in_api_key');
        if (!$apiKey) {
            $this->error('⚠️ data.gov.in API key not found!.');
            $this->error('ⓘ Please set DATA_GOV_IN_API_KEY in your .env file.');
            $this->error('🌐 Visit https://data.gov.in/ to get an API key.');
            return;
        }

        $this->info('⏳ Downloading PinIndia data from API.DATA.GOV.IN.');
        $this->info('⚠️ Please be patient. This may take a while.');

        $batchSize = 10000;
        $limit = 165314;
        $offset = 0;
        $data = [];

        try {
            while ($offset < $limit) {
                sleep(1);
                $url = "https://api.data.gov.in/resource/5c2f62fe-5afa-4119-a499-fec9d604d5bd?api-key={$apiKey}&offset={$offset}&limit={$batchSize}&format=json";
                
                $response = Http::timeout(0)->retry(3)->get($url);
                if ($response->failed()) {
                    $this->error('Failed to fetch data: ' . $response->status());
                    throw new \Exception('Failed to fetch data: ' . $response->status());
                    return;
                }

                $data = array_merge($data, $response->json()['records']);
                $limit = $response->json()['total']; // Update the limit to the total number of records
                $offset += $batchSize; // Increment the offset by the batch size amount
                
                $percentage = ($offset / $limit) * 100;
                $this->info("⏳".$offset."/".$limit." records fetched. " . number_format($percentage, 2) . "% completed.");
            }
        } catch (\Exception $e) {
            $this->error('Failed to fetch data: ' . $e->getMessage());
            throw new \Exception('Failed to fetch data: ' . $e->getMessage());
            return;
        }

        if (!isset($data) || !is_array($data) || empty($data)) {
            $this->error('No data found to download.');
            throw new \Exception('No data found to download.');
            return;
        }

        $this->info("✅ Data fetched successfully. Total records: " . count($data));

        //Save as JSON file
        $jsonPath = config('pinindia.data_path'); // 'pinindia/post_offices.json' by default
        Storage::disk('local')->put($jsonPath, json_encode($data));
        $this->info("✅ Data saved as JSON file at: " . $jsonPath);
    }
}
