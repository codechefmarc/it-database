<?php

namespace App\Services;

use App\Http\Controllers\TopDeskDataController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service to interact with TopDesk API for locations and asset management.
 */
class TopDeskService {
  /**
   * TopDesk API base URL.
   */
  private string $baseUrl;
  /**
   * TopDesk API credentials (username).
   */
  private string $username;
  /**
   * TopDesk API credentials (password).
   */
  private string $password;
  /**
   * Cache duration in minutes (default: 60 minutes).
   */
  private int $cacheMinutes = 60;
  /**
   * TopDesk template ID.
   */
  private string $templateId;
  /**
   * Allowed templates for asset creation.
   *
   * @var array
   */
  public array $allowedTemplates = [
    "Computer",
  ];
  /**
   * TopDesk capability ID for stock room assignment. From API docs.
   */
  private string $topDeskStockRoomCapabilityId = "DAD98DAD-054B-41AE-A727-3E3B37342739";

  public function __construct() {
    $this->baseUrl = rtrim(config('services.topdesk.base_url'), '/');
    $this->username = config('services.topdesk.username');
    $this->password = config('services.topdesk.password');
    $this->templateId = config('services.topdesk.template_id');

    if (empty($this->username) || empty($this->password)) {
      throw new \Exception('TopDesk credentials not configured. Please set TOPDESK_USERNAME and TOPDESK_PASSWORD in your .env file.');
    }

    if (empty($this->baseUrl)) {
      throw new \Exception('TopDesk base URL not configured. Please set TOPDESK_BASE_URL in your .env file.');
    }

    if (empty($this->templateId)) {
      throw new \Exception('TopDesk template ID not configured. Please set TOPDESK_TEMPLATE_ID in your .env file.');
    }

  }

  /**
   * Get all locations from TopDesk (cached)
   *
   * @return array
   *   Retrieves all locations from TopDesk, cached to minimize API calls.
   *
   * @throws Exception
   */
  public function getLocations(): array {
    return Cache::remember('topdesk.locations', $this->cacheMinutes * 60, function () {
      try {
        $response = Http::withBasicAuth($this->username, $this->password)
          ->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
          ])->timeout(30)->get($this->baseUrl . '/tas/api/locations');

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Failed to fetch locations from TopDesk API. Status: ' . $response->status());

      }
      catch (\Exception $e) {
        Log::error('TopDesk API Error - Get Locations', [
          'message' => $e->getMessage(),
        ]);
        throw $e;
      }
    });
  }

  /**
   * Get campuses/branches for select dropdown.
   *
   * @return array
   *   Returns array with id and name for campuses (branches).
   */
  public function getCampuses(): array {
    $locations = $this->getLocations();
    $campuses = [];

    foreach ($locations as $location) {
      if (isset($location['branch']) && $location['branch']) {
        $branchId = $location['branch']['id'];

        // Only add if we haven't seen this branch before.
        if (!isset($campuses[$branchId])) {
          $campuses[$branchId] = [
            'id' => $branchId,
            'name' => $location['branch']['name'],
          ];
        }
      }
    }

    // Return as indexed array, sorted by name.
    $result = array_values($campuses);
    usort($result, fn($a, $b) => strcasecmp($a['name'], $b['name']));

    return $result;
  }

  /**
   * Get buildings for a specific campus/branch.
   *
   * @param string $branchId
   *   The ID of the campus/branch to filter buildings.
   *
   * @return array
   *   Returns array with id and name for buildings in the specified campus.
   */
  public function getBuildingsByCampus(string $branchId): array {
    $locations = $this->getLocations();
    $buildings = [];

    foreach ($locations as $location) {
      if (isset($location['branch']) &&
        $location['branch']['id'] === $branchId) {

        $buildings[] = [
          'id' => $location['id'],
          'name' => $location['name'],
        ];
      }
    }

    // Sort by name.
    usort($buildings, fn($a, $b) => strcasecmp($a['name'], $b['name']));

    return $buildings;
  }

  /**
   * Get all locations grouped by campus for easier frontend handling.
   *
   * @return array
   *   Returns array of campuses, each with their associated buildings.
   */
  public function getLocationsByCampus(): array {
    $locations = $this->getLocations();
    $grouped = [];

    foreach ($locations as $location) {
      if (isset($location['branch']) && $location['branch']) {
        $branchId = $location['branch']['id'];
        $branchName = $location['branch']['name'];

        if (!isset($grouped[$branchId])) {
          $grouped[$branchId] = [
            'id' => $branchId,
            'name' => $branchName,
            'buildings' => [],
          ];
        }

        $grouped[$branchId]['buildings'][] = [
          'id' => $location['id'],
          'name' => $location['name'],
        ];
      }
    }

    // Sort campuses and buildings by name.
    foreach ($grouped as &$campus) {
      usort($campus['buildings'], fn($a, $b) => strcasecmp($a['name'], $b['name']));
    }

    $result = array_values($grouped);
    usort($result, fn($a, $b) => strcasecmp($a['name'], $b['name']));

    return $result;
  }

  /**
   * Get makes for select dropdown.
   */
  public function getAssetMakes(): array {
    return Cache::remember('topdesk.make', $this->cacheMinutes * 60, function () {
      try {
        $response = Http::withBasicAuth($this->username, $this->password)
          ->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
          ])->timeout(30)->get($this->baseUrl . '/tas/api/assetmgmt/dropdowns/make?field=name');

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Failed to fetch make from TopDesk API. Status: ' . $response->status());

      }
      catch (\Exception $e) {
        Log::error('TopDesk API Error - Get Make', [
          'message' => $e->getMessage(),
        ]);
        throw $e;
      }
    });
  }

  /**
   * Get models for select dropdown.
   */
  public function getAssetModels(): array {
    return Cache::remember('topdesk.models', $this->cacheMinutes * 60, function () {
      try {
        $response = Http::withBasicAuth($this->username, $this->password)
          ->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
          ])->timeout(30)->get($this->baseUrl . '/tas/api/assetmgmt/dropdowns/model-1?field=name');

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Failed to fetch models from TopDesk API. Status: ' . $response->status());

      }
      catch (\Exception $e) {
        Log::error('TopDesk API Error - Get Models', [
          'message' => $e->getMessage(),
        ]);
        throw $e;
      }
    });
  }

  /**
   * Get models for select dropdown.
   */
  public function getStockRooms(): array {
    return Cache::remember('topdesk.stock_rooms', $this->cacheMinutes * 60, function () {
      try {
        $response = Http::withBasicAuth($this->username, $this->password)
          ->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
          ])->timeout(30)->get($this->baseUrl . '/tas/api/assetmgmt/assets?resourceCategory=stock');

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Failed to fetch stock rooms from TopDesk API. Status: ' . $response->status());

      }
      catch (\Exception $e) {
        Log::error('TopDesk API Error - Get stock rooms', [
          'message' => $e->getMessage(),
        ]);
        throw $e;
      }
    });
  }

  /**
   * Get device types for select dropdown.
   */
  public function getDeviceTypes(): array {
    return Cache::remember('topdesk.device_types', $this->cacheMinutes * 60, function () {
      try {
        $response = Http::withBasicAuth($this->username, $this->password)
          ->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
          ])->timeout(30)->get($this->baseUrl . '/tas/api/assetmgmt/dropdowns/computer-type?field=name');

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Failed to fetch device types from TopDesk API. Status: ' . $response->status());

      }
      catch (\Exception $e) {
        Log::error('TopDesk API Error - Get Device types', [
          'message' => $e->getMessage(),
        ]);
        throw $e;
      }
    });
  }

  /**
   * Get asset templates.
   */
  public function getTemplates(): array {
    return Cache::remember('topdesk.templates', $this->cacheMinutes * 60, function () {
      try {
        $response = Http::withBasicAuth($this->username, $this->password)
          ->withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
          ])->timeout(30)->get($this->baseUrl . '/tas/api/assetmgmt/templates');
        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception('Failed to fetch templates from TopDesk API. Status: ' . $response->status());

      }
      catch (\Exception $e) {
        Log::error('TopDesk API Error - Get Make', [
          'message' => $e->getMessage(),
        ]);
        throw $e;
      }
    });
  }

  /**
   * Clear the locations cache.
   *
   * @return void
   *   Clears the cached.
   */
  public function clearCache(): void {
    Cache::forget('topdesk.locations');
    Cache::forget('topdesk.make');
    Cache::forget('topdesk.templates');
  }

  /**
   * Set cache duration in minutes.
   *
   * @param int $minutes
   *   Duration in minutes to cache API responses.
   *
   * @return void
   *   Sets the cache duration for API responses.
   */
  public function setCacheDuration(int $minutes): void {
    $this->cacheMinutes = $minutes;
  }

  /**
   * Create a single asset in TopDesk.
   *
   * @param string $assetData
   *   Asset data passed from the form.
   * @param string $modelId
   *   The ID or string to lookup/create the model.
   *
   * @return array
   *   Returns the created asset data including its ID.
   *
   * @throws Exception
   */
  public function createAsset(array $assetData, ?string $modelId = NULL): array {

    $model = $modelId ?? $this->modelLookup($assetData['model'] ?? '');

    try {
      $response = Http::withBasicAuth($this->username, $this->password)
        ->withHeaders([
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
        ])->timeout(30)->post($this->baseUrl . '/tas/api/assetmgmt/assets', [
          'type_id' => $this->templateId,
          'name' => $assetData['srjc_tag'],
          'computer-type' => $assetData['device_type'] ?? NULL,
          'room' => $assetData['room'] ?? NULL,
          'make' => $assetData['make'] ?? NULL,
          'model-1' => $model,
          'serial-number' => $assetData['serial_number'] ?? NULL,
          'notes' => $assetData['notes'] . "Added by IT database web app on " . date('Y-m-d H:i:s'),
          'surplus' => !empty($assetData['surplus']) ? TRUE : FALSE,
        ]);

      if ($response->successful()) {
        $asset = $response->json();
        if (env('APP_DEBUG')) {
          Log::info('TopDesk Asset Created', [
            'asset_id' => $asset['data']['unid'] ?? NULL,
            'name' => $assetData['srjc_tag'],
            'room' => $assetData['room'] ?? NULL,
          ]);
        }
        return $asset;
      }

      throw new \Exception('Failed to create asset in TopDesk API. Status: ' . $response->status());

    }
    catch (\Exception $e) {
      Log::error('TopDesk API Error - Create Asset', [
        'message' => $e->getMessage(),
        'asset_name' => $assetData['srjc_tag'],
      ]);
      throw $e;
    }
  }

  /**
   * Get all assignments for an asset.
   *
   * @param string $assetId
   *   The ID of the asset to retrieve assignments for.
   *
   * @return array
   *   Returns array of assignments for the specified asset.
   *
   * @throws Exception
   */
  public function getAssetAssignments(string $assetId): array {
    try {
      $response = Http::withBasicAuth($this->username, $this->password)
        ->withHeaders([
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
        ])->timeout(30)->get($this->baseUrl . "/tas/api/assetmgmt/assets/{$assetId}/assignments");

      if ($response->successful()) {
        return $response->json();
      }

      throw new \Exception('Failed to get asset assignments from TopDesk API. Status: ' . $response->status());

    }
    catch (\Exception $e) {
      Log::error('TopDesk API Error - Get Asset Assignments', [
        'message' => $e->getMessage(),
        'asset_id' => $assetId,
      ]);
      throw $e;
    }
  }

  /**
   * Unlink an asset from a specific target (like a location)
   *
   * @param string $assetId
   *   The ID of the asset to unlink.
   * @param string $type
   *   The type of target to unlink from (e.g., 'location').
   * @param string $targetId
   *   The ID of the target to unlink from.
   *
   * @return bool
   *   Returns true if unlinking was successful.
   *
   * @throws Exception
   */
  public function unlinkAssetFromTarget(string $assetId, string $type, string $targetId): bool {
    try {
      $response = Http::withBasicAuth($this->username, $this->password)
        ->withHeaders([
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
        ])->timeout(30)->delete($this->baseUrl . "/tas/api/assetmgmt/assets/{$assetId}/assignments/{$targetId}");

      if ($response->successful()) {
        if (env('APP_DEBUG')) {
          Log::info('TopDesk Asset Unlinked', [
            'asset_id' => $assetId,
            'type' => $type,
            'target_id' => $targetId,
          ]);
        }
        return TRUE;
      }

      throw new \Exception('Failed to unlink asset from target. Status: ' . $response->status());

    }
    catch (\Exception $e) {
      Log::error('TopDesk API Error - Unlink Asset', [
        'message' => $e->getMessage(),
        'asset_id' => $assetId,
        'type' => $type,
        'target_id' => $targetId,
      ]);
      throw $e;
    }
  }

  /**
   * Clear all location assignments for an asset.
   *
   * @param string $assetId
   *   The ID of the asset to clear assignments for.
   *
   * @return int
   *   Number of assignments cleared.
   *
   * @throws Exception
   */
  public function clearAssetLocationAssignments(string $assetId): int {
    $assignments = $this->getAssetAssignments($assetId);
    $clearedCount = 0;
    if (isset($assignments['locations']) && !empty($assignments['locations'])) {
      foreach ($assignments['locations'] as $assignment) {

        $locationId = $assignment['linkId'];
        if ($locationId) {
          $this->unlinkAssetFromTarget($assetId, 'location', $locationId);
          $clearedCount++;
        }
      }
    }

    if ($clearedCount > 0) {
      if (env('APP_DEBUG')) {
        Log::info('TopDesk Asset Location Assignments Cleared', [
          'asset_id' => $assetId,
          'cleared_count' => $clearedCount,
        ]);
      }
    }

    return $clearedCount;
  }

  /**
   * Assign an asset to a location.
   *
   * @param string $assetId
   *   The ID of the asset to assign.
   * @param string $branchId
   *   The ID of the branch (campus) where the location resides.
   * @param string $locationId
   *   The ID of the location to assign the asset to.
   *
   * @return bool
   *   Returns true if assignment was successful.
   *
   * @throws Exception
   */
  public function assignAssetToLocation(string $assetId, string $branchId, string $locationId): bool {
    try {
      $response = Http::withBasicAuth($this->username, $this->password)
        ->withHeaders([
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
        ])->timeout(30)->put($this->baseUrl . '/tas/api/assetmgmt/assets/assignments', [
          'assetIds' => [$assetId],
          'branchId' => $branchId,
          'linkToId' => $locationId,
          'linkType' => 'location',
        ]);

      if ($response->successful()) {
        if (env('APP_DEBUG')) {
          Log::info('TopDesk Asset Assigned', [
            'asset_id' => $assetId,
            'branch_id' => $branchId,
            'location_id' => $locationId,
          ]);
        }
        return TRUE;
      }

      throw new \Exception('Failed to assign asset to location. Status: ' . $response->status());

    }
    catch (\Exception $e) {
      Log::error('TopDesk API Error - Assign Asset', [
        'message' => $e->getMessage(),
        'asset_id' => $assetId,
        'branch_id' => $branchId,
        'location_id' => $locationId,
      ]);
      throw $e;
    }
  }

  /**
   * Create or update (if exists) an asset and assign it to a location.
   */
  public function createAndAssignAsset(array $assetData): array {
    $existingAsset = $this->searchAssetsByName($assetData['srjc_tag']);

    $resolvedModelId = $this->modelLookup($assetData['model'] ?? '');

    $assetData['notes'] = $existingAsset['notes'] ?? '';
    if ($existingAsset) {
      if (in_array($existingAsset['template_name'], $this->allowedTemplates, TRUE) === FALSE) {
        $message = 'Asset with name "' . $assetData['srjc_tag'] . '" already exists but has an invalid template: ' . ($existingAsset['template_name'] ?? 'unknown');
        Log::info($message);
        throw new \Exception($message);
      }
      $operation = 'reassigned';
      // Asset exists - clear existing location assignments first.
      $assetId = $existingAsset['unid'];
      $clearedCount = $this->clearAssetLocationAssignments($assetId);

      $this->updateExistingAssetData($assetId, $assetData, $resolvedModelId);

      if (env('APP_DEBUG')) {
        Log::info('TopDesk Asset Found - Reassigning Location', [
          'asset_id' => $assetId,
          'name' => $assetData['srjc_tag'],
          'cleared_assignments' => $clearedCount,
        ]);
      }

    }
    else {
      $operation = 'created';
      $asset = $this->createAsset($assetData, $resolvedModelId);
      $assetId = $asset['data']['unid'] ?? NULL;

      if (!$assetId) {
        throw new \Exception('Asset creation succeeded but no unid returned');
      }

    }
    if ($assetId) {
      $this->assignAssetToLocation($assetId, $assetData['campus'], $assetData['building']);
      $this->assignAssetToStockroom($assetId, $assetData['stockRoom']);
    }

    return [
      'asset' => $assetId,
      'operation' => $operation,
      'model_id' => $resolvedModelId,
    ];
  }

  /**
   * Search for assets by name using the filter endpoint.
   *
   * @param string $assetName
   *   The name of the asset to search for.
   *
   * @return array|null
   *   Returns array of matching assets or null if none found.
   *
   * @throws Exception
   */
  public function searchAssetsByName(string $assetName): array|null {

    $controller = new TopDeskDataController($this);
    $templatesResponse = $controller->getTemplates(FALSE);
    $templates = json_decode($templatesResponse->getContent(), TRUE);
    $templateIds = array_column($templates['data'], 'id');

    try {
      $response = Http::withBasicAuth($this->username, $this->password)
        ->withHeaders([
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
        ])->timeout(30)->post($this->baseUrl . '/tas/api/assetmgmt/assets/filter', [
          'templateId' => $templateIds,
          '$filter' => "name eq '{$assetName}'",
          'fields' => "notes",
          'includeTemplates' => 'relevant',
        ]);

      if ($response->successful()) {
        $result = $response->json();
        $dataSet = $result['dataSet'] ?? [];
        if (!empty($dataSet)) {
          $assetData = $dataSet[0];
          $assetData['template'] = $result['templates'][0]['id'] ?? NULL;
          $assetData['template_name'] = $result['templates'][0]['text'] ?? NULL;
          return $assetData;
        }
        return NULL;
      }

      throw new \Exception('Failed to search for assets in TopDesk API. Status: ' . $response->status());

    }
    catch (\Exception $e) {
      Log::error('TopDesk API Error - Search Assets', [
        'message' => $e->getMessage(),
        'asset_name' => $assetName,
      ]);
      throw $e;
    }
  }

  /**
   * Update existing asset data.
   *
   * @param string $assetId
   *   The ID of the asset to update.
   * @param array $assetData
   *   The data to update the asset with.
   *
   * @return bool
   *   Returns true if update was successful.
   *
   * @throws Exception
   */
  public function updateExistingAssetData(string $assetId, array $assetData): bool {

    $model = $modelId ?? $this->modelLookup($assetData['model'] ?? '');

    try {
      $response = Http::withBasicAuth($this->username, $this->password)
        ->withHeaders([
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
        ])->timeout(30)->post($this->baseUrl . "/tas/api/assetmgmt/assets/{$assetId}", [
          'computer-type' => $assetData['device_type'] ?? NULL,
          'room' => $assetData['room'],
          'make' => $assetData['make'] ?? NULL,
          'model-1' => $model,
          'serial-number' => $assetData['serial_number'] ?? NULL,
          'notes' => $assetData['notes'] . "\nUpdated by IT database web app on " . date('Y-m-d H:i:s'),
          'surplus' => !empty($assetData['surplus']) ? TRUE : FALSE,
        ]);
      if ($response->successful()) {
        if (env('APP_DEBUG')) {
          Log::info('TopDesk Asset Updated', [
            'asset_id' => $assetId,
            'room' => $assetData['room'],
            'notes' => $assetData['notes'],
          ]);
        }
        return TRUE;
      }
      throw new \Exception('Failed to update asset. Status: ' . $response->status());
    }
    catch (\Exception $e) {
      Log::error('TopDesk API Error - Unable to update existing asset', [
        'message' => $e->getMessage(),
        'asset_name' => $assetData['srjc_tag'],
      ]);
      throw $e;
    }
  }

  /**
   * Look up model in TOPdesk by ID. If not found, create first then return ID.
   */
  private function modelLookup(string $model) {
    $models = $this->getAssetModels();
    $matchedModel = collect($models['results'] ?? [])->firstWhere('id', $model);

    if ($matchedModel) {
      $modelId = $matchedModel['id'];
    }
    else {
      $modelId = $this->createModel($model);
    }
    return $modelId;

  }

  /**
   * Create a new model in TOPdesk and return its ID.
   */
  private function createModel(string $modelName): string {

    try {
      $response = Http::withBasicAuth($this->username, $this->password)
        ->withHeaders([
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
        ])->timeout(30)->post($this->baseUrl . "/tas/api/assetmgmt/dropdowns/model-1", [
          'name' => $modelName,
        ]);
      if ($response->successful()) {
        if (env('APP_DEBUG')) {
          Log::info('TopDesk Model created', [
            'model_name' => $modelName,
            'model_id' => $response->json()['id'] ?? NULL,
          ]);
        }
        Cache::forget('topdesk.models');
        return $response->json()['id'];
      }
      throw new \Exception('Failed to create model. Status: ' . $response->status());
    }
    catch (\Exception $e) {
      Log::error('TopDesk API Error - Unable to create model', [
        'message' => $e->getMessage(),
        'model_name' => $modelName,
      ]);
      throw $e;
    }
  }

  /**
   * Assign an asset to a stock room or remove from stock room.
   *
   * @param string $assetId
   *   The asset ID to assign to or remove from stock.
   * @param string $stockRoomId
   *   The stockroom ID or empty string.
   *
   * @return bool
   *   If successful, returns TRUE.
   */
  private function assignAssetToStockroom(string $assetId, string $stockRoomId): bool {
    // Always unlink stock first.
    $stockLinkId = $this->getStockLinkId($assetId);

    if ($stockLinkId) {
      $this->unlinkStockroom($stockLinkId);
    }

    if (!$stockRoomId) {
      return TRUE;
    }

    try {
      $response = Http::withBasicAuth($this->username, $this->password)
        ->withHeaders([
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
        ])->timeout(30)->post($this->baseUrl . '/tas/api/assetmgmt/assetLinks', [
          'capabilityId' => $this->topDeskStockRoomCapabilityId,
          'sourceId' => $stockRoomId,
          'targetId' => $assetId,
          'type' => 'parent',
        ]);

      if ($response->successful()) {
        if (env('APP_DEBUG')) {
          Log::info('TopDesk Asset Assigned to Stockroom', [
            'asset_id' => $assetId,
            'stock_room_id' => $stockRoomId,
          ]);
        }
        return TRUE;
      }

      throw new \Exception('Failed to assign asset to stock room. Status: ' . $response->status());

    }
    catch (\Exception $e) {
      Log::error('TopDesk API Error - Assign Asset to Stock Room', [
        'message' => $e->getMessage(),
        'asset_id' => $assetId,
        'stock_room_id' => $stockRoomId,
      ]);
      throw $e;
    }
  }

  /**
   * Gets the stock link ID or NULL so we can unlink.
   *
   * @param string $assetId
   *   The asset ID to get a stock link.
   *
   * @return string|null
   *   Returns a link ID from asset to stock or NULL.
   */
  private function getStockLinkId(string $assetId): string|null {
    try {
      $response = Http::withBasicAuth($this->username, $this->password)
        ->withHeaders([
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
        ])->timeout(30)->get($this->baseUrl . "/tas/api/assetmgmt/assetLinks?capabilityId={$this->topDeskStockRoomCapabilityId}&sourceId={$assetId}");

      $linkId = NULL;

      if ($response->successful()) {
        $result = $response->json();
        if (!empty($result) && isset($result[0]['linkId'])) {
          $linkId = $result[0]['linkId'];
        }
        if (env('APP_DEBUG')) {
          Log::info('TopDesk LinkId for stock', [
            'asset_id' => $assetId,
            'link_id' => $linkId,
          ]);
        }
        return $linkId;
      }

      throw new \Exception('Failed to retrieve stock link ID: ' . $response->status());

    }
    catch (\Exception $e) {
      Log::error('TopDesk API Error - Retrieve stock link ID', [
        'message' => $e->getMessage(),
        'asset_id' => $assetId,
      ]);
      throw $e;
    }
  }

  /**
   * Unlinks stock from asset.
   *
   * @param string $stockLinkId
   *   A linkID that links an asset with a stock room.
   *
   * @return bool
   *   Returns TRUE if successful.
   */
  private function unlinkStockroom(string $stockLinkId): bool {
    try {
      $response = Http::withBasicAuth($this->username, $this->password)
        ->withHeaders([
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
        ])->timeout(30)->delete($this->baseUrl . "/tas/api/assetmgmt/assetLinks/{$stockLinkId}", []);

      if ($response->successful()) {
        if (env('APP_DEBUG')) {
          Log::info('TopDesk stock unlinked', [
            'link_id' => $stockLinkId,
          ]);
        }
        return TRUE;
      }

      throw new \Exception('Failed to unlink stock: ' . $response->status());

    }
    catch (\Exception $e) {
      Log::error('TopDesk API Error - Unlink stock', [
        'message' => $e->getMessage(),
        'link_id' => $stockLinkId,
      ]);
      throw $e;
    }
  }

}
