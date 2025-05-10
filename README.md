# PinIndia

A Laravel package for managing and querying Indian post office data. PinIndia provides a comprehensive solution for handling postal addresses, location-based services, and geographic data across India. With over 150,000+ post offices indexed, this package enables developers to build robust location-aware applications with minimal effort.

## Features

- **Complete Postal Database**: Access to all Indian post offices, pincodes, districts, and states
- **Geolocation Support**: Find nearest post offices based on coordinates or existing postal references
- **Hierarchical Data Structure**: Navigate through the postal hierarchy (State → District → Pincode → Post Office)
- **Optimized Performance**: Database indexing on frequently queried fields for fast lookups
- **Laravel Integration**: Seamless integration with Laravel's ecosystem including migrations, seeders, and Eloquent models
- **Official Data Source**: All data sourced from the official data.gov.in API

## Installation

### 1. Require the package via Composer

```bash
composer require dev-giri/pin-india
```

### 2. Add API key to your .env file

```
DATA_GOV_IN_API_KEY=your_api_key_here
```

You can obtain an API key from [data.gov.in](https://data.gov.in/) by registering for an account and requesting API access. This key is required for the initial data download during installation.

### 3. Install the package

```bash
php artisan pinindia:install
```

This command will:
- Publish the configuration file to your application
- Run migrations to create the necessary database tables
- Download post office data from data.gov.in using your API key
- Seed the database with comprehensive post office data

The installation process may take several minutes as it downloads and processes the complete Indian postal database.

## Configuration

You can publish the configuration file manually:

```bash
php artisan vendor:publish --provider="PinIndia\PinIndiaServiceProvider" --tag="pinindia-config"
```

Available configuration options in `config/pinindia.php`:

```php
return [
    // Your data.gov.in API key for downloading postal data
    'data_gov_in_api_key' => env('DATA_GOV_IN_API_KEY'),

    // Prefix for database tables (helps avoid conflicts)
    'table_prefix' => env('PININDIA_TABLE_PREFIX', 'pinindia'),

    // Local storage path for downloaded postal data
    'data_path' => env('PININDIA_DATA_PATH', 'pinindia/post_offices.json'),
];
```

## Usage

### Using the Facade

The package provides a simple facade for common postal queries:

```php
use PinIndia\Facades\PinIndia;

// Find post offices by pincode
$postOffices = PinIndia::findByPincode(110001);

// Find post offices by name
$postOffices = PinIndia::findByPostOffice('Delhi');

// Find nearest post offices by coordinates
$postOffices = PinIndia::getNearestByCoordinates(28.6139, 77.2090, 5); // lat, long, radius in km

// Find nearest post offices by pincode
$postOffices = PinIndia::getNearestByPincode(110001, 5); // pincode, radius in km

// Find nearest post offices by post office name
$postOffices = PinIndia::getNearestByPostOffice('Delhi GPO', 5); // name, radius in km
```

### State and District Autocomplete

Implement intelligent address autocomplete with real-time suggestions:

```php
use PinIndia\Models\State;
use PinIndia\Models\District;

// Autocomplete states
public function autocompleteStates(Request $request)
{
    $query = $request->input('query');
    $states = State::where('name', 'like', "%{$query}%")
        ->orderBy('name')
        ->limit(10)
        ->get()
        ->map(function($state) {
            return [
                'id' => $state->id,
                'name' => $state->name
            ];
        });

    return response()->json($states);
}

// Autocomplete districts within a state
public function autocompleteDistricts(Request $request)
{
    $query = $request->input('query');
    $stateId = $request->input('state_id');

    $districts = District::when($stateId, function($q) use ($stateId) {
            return $q->where('state_id', $stateId);
        })
        ->where('name', 'like', "%{$query}%")
        ->orderBy('name')
        ->limit(10)
        ->get()
        ->map(function($district) {
            return [
                'id' => $district->id,
                'name' => $district->name,
                'state_id' => $district->state_id,
                'state_name' => $district->state->name
            ];
        });

    return response()->json($districts);
}
```

### Cascading Dropdowns for Address Selection

Create intuitive address forms with dependent dropdowns:

```php
use PinIndia\Models\State;
use PinIndia\Models\District;
use PinIndia\Models\Pincode;
use PinIndia\Models\PostOffice;

// Get all states for the first dropdown
public function getStates()
{
    $states = State::orderBy('name')->get();
    return response()->json($states);
}

// Get districts based on selected state
public function getDistricts($stateId)
{
    $districts = District::where('state_id', $stateId)
        ->orderBy('name')
        ->get();
    return response()->json($districts);
}

// Get pincodes based on selected district
public function getPincodes($districtId)
{
    $pincodes = Pincode::where('district_id', $districtId)
        ->orderBy('pincode')
        ->get()
        ->pluck('pincode')
        ->unique()
        ->values();
    return response()->json($pincodes);
}

// Get post offices based on selected pincode
public function getPostOffices($pincode)
{
    $postOffices = PostOffice::whereHas('pincode', function($query) use ($pincode) {
            $query->where('pincode', $pincode);
        })
        ->orderBy('name')
        ->get(['id', 'name', 'office']);
    return response()->json($postOffices);
}
```

### Response Format

All methods return a collection of `PostOfficeResource` objects with the following structure:

```json
[
  {
    "name": "Delhi GPO",
    "pincode": "110001",
    "distance": 0.5, // Only available for nearest queries
    "latitude": 28.6139,
    "longitude": 77.2090,
    "district": "New Delhi",
    "state": "Delhi"
  }
]
```
## Use Cases

### 1. Find Address by User's Current Location

Retrieve the nearest post office details based on the user's current GPS coordinates:

```php
// Get user's current location from browser/device
$latitude = $request->input('latitude');
$longitude = $request->input('longitude');
$radius = 2; // 2 km radius

// Find nearest post offices
$nearestPostOffices = PinIndia::getNearestByCoordinates($latitude, $longitude, $radius);

// First result is likely the user's current postal area
$currentLocation = $nearestPostOffices->first();

// Display address information
echo "Your current area: {$currentLocation->name}, {$currentLocation->district}, {$currentLocation->state} - {$currentLocation->pincode}";
```

### 2. Address Autocomplete for Forms

Implement an address autocomplete feature in your forms:

```php
// User starts typing post office name
$query = $request->input('query'); // e.g., "Andheri"

// Search for matching post offices
$suggestions = PinIndia::findByPostOffice($query, 5); // Limit to 5 results

return response()->json([
    'suggestions' => $suggestions->map(function($po) {
        return [
            'label' => "{$po->name}, {$po->district}, {$po->state} - {$po->pincode}",
            'value' => $po->pincode
        ];
    })
]);
```

### 3. Delivery Radius Calculation for E-commerce

Determine if a delivery location is within your service area:

```php
// Customer's pincode
$customerPincode = $request->input('pincode');

// Your warehouse/store location
$warehousePincode = 400001; // Mumbai GPO
$maxDeliveryRadius = 25; // 25 km delivery radius

// Find distance between warehouse and customer
$nearestPostOffices = PinIndia::getNearestByPincode($warehousePincode, $maxDeliveryRadius);

// Check if customer's pincode is in delivery range
$isDeliverable = $nearestPostOffices->contains(function($postOffice) use ($customerPincode) {
    return $postOffice->pincode == $customerPincode;
});

if ($isDeliverable) {
    echo "Delivery available to your location!";
} else {
    echo "Sorry, we don't deliver to your area yet.";
}
```

### 4. Regional Data Analysis

Analyze post office distribution by state or district:

```php
use PinIndia\Models\State;
use PinIndia\Models\District;

// Count post offices by state
$stateStats = State::withCount('districts.pincodes.postOffices')
    ->get()
    ->map(function($state) {
        return [
            'state' => $state->name,
            'post_office_count' => $state->districts_pincodes_post_offices_count
        ];
    })
    ->sortByDesc('post_office_count');

// Find districts with highest post office density
$districtStats = District::withCount('pincodes.postOffices')
    ->with('state')
    ->get()
    ->map(function($district) {
        return [
            'district' => $district->name,
            'state' => $district->state->name,
            'post_office_count' => $district->pincodes_post_offices_count
        ];
    })
    ->sortByDesc('post_office_count')
    ->take(10);
```

## Database Structure

The package creates a relational database structure that mirrors India's postal hierarchy:

- **States**: Top-level administrative divisions
- **Districts**: Administrative subdivisions within states
- **Circles**: Postal administrative regions
- **Regions**: Subdivisions within postal circles
- **Divisions**: Operational units within postal regions
- **Pincodes**: 6-digit postal codes
- **Post Offices**: Individual post office locations with coordinates

This structure allows for efficient querying and navigation through the postal hierarchy.

## Available Commands

```bash
# Install the package
php artisan pinindia:install

# Download post office data from data.gov.in
php artisan pinindia:download

# Uninstall the package
php artisan pinindia:uninstall
```

## Models

The package includes the following Eloquent models:

- `State`: Represents Indian states and union territories
- `District`: Represents districts within states
- `Circle`: Represents postal circles (usually state-level)
- `Region`: Represents postal regions within circles
- `Division`: Represents postal divisions within regions
- `Pincode`: Represents 6-digit postal codes
- `PostOffice`: Represents individual post office locations

Each model includes appropriate relationships for navigating the postal hierarchy.

## Notes

- **Data Source**: All postal data is sourced from the official [data.gov.in](https://data.gov.in/) API, ensuring accuracy and reliability.
- **Data Freshness**: The package downloads the latest available postal data during installation. You can refresh the data anytime using the `pinindia:download` command.
- **Performance**: The package is optimized for performance with proper database indexing on frequently queried fields.
- **Coordinate Accuracy**: Geographic coordinates (latitude/longitude) are based on data.gov.in's records and may have varying levels of precision across different regions.
- **API Rate Limits**: Be aware that data.gov.in may impose rate limits on API requests during the initial data download.
- **Storage Requirements**: The complete dataset requires approximately 150-200MB of database storage.

## Legal Disclaimer

- This package accesses data from [data.gov.in](https://data.gov.in/). Users must comply with data.gov.in's [terms of service](https://www.data.gov.in/terms-of-use) when using this package.
- The API key required for this package must be obtained directly from data.gov.in by the end user.
- This package is provided "as is" without warranty of any kind. The accuracy and completeness of postal data depend on data.gov.in's records.
- When implementing location-based features, ensure you comply with applicable privacy laws and obtain appropriate user consent for collecting location data.
- The MIT license of this package applies to the code only, not to the data retrieved from data.gov.in.

## Testing


The package includes a comprehensive test suite covering models, facades, resources, and services. To run the tests:

```bash
# Run all tests
composer test

# Run tests with coverage report
composer test-coverage
```

### Test Requirements

- The tests are configured to use MySQL by default, as some geospatial functions like `acos()` are required for distance calculations
- You can configure the test database connection in the `phpunit.xml` file
- Make sure you have a MySQL database named `pinindia_test` created before running the tests

### Running Specific Test Groups

```bash
# Run only model tests
vendor/bin/phpunit tests/Unit/Models

# Run only facade tests
vendor/bin/phpunit tests/Unit/Facades

# Run only resource tests
vendor/bin/phpunit tests/Unit/Resources

# Run only service tests
vendor/bin/phpunit tests/Unit/Services
```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).