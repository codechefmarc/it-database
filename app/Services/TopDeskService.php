<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TopDeskService {
  private string $baseUrl;
  private string $username;
  private string $password;
  private int $cacheMinutes = 60;
  private string $topDeskTemplateId = "A273AF5F-0881-4ABB-A66A-E3307631BF46";

  public function __construct() {
    $this->baseUrl = rtrim(config('services.topdesk.base_url'), '/');
    $this->username = config('services.topdesk.username');
    $this->password = config('services.topdesk.password');

    if (empty($this->username) || empty($this->password)) {
      throw new \Exception('TopDesk credentials not configured. Please set TOPDESK_USERNAME and TOPDESK_PASSWORD in your .env file.');
    }

    if (empty($this->baseUrl)) {
      throw new \Exception('TopDesk base URL not configured. Please set TOPDESK_BASE_URL in your .env file.');
    }
  }

  /**
   * Get all locations from TopDesk (cached)
   *
   * @return array
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
            'message' => $e->getMessage()
        ]);
        throw $e;
      }
    });
  }

  /**
   * Get campuses/branches for select dropdown.
   *
   * @return array
   */
  public function getCampuses(): array {
    $locations = $this->getLocations();
    $campuses = [];

    foreach ($locations as $location) {
      if (isset($location['branch']) && $location['branch']) {
        $branchId = $location['branch']['id'];

        // Only add if we haven't seen this branch before
        if (!isset($campuses[$branchId])) {
          $campuses[$branchId] = [
            'id' => $branchId,
            'name' => $location['branch']['name'],
          ];
        }
      }
    }

    // Return as indexed array, sorted by name
    $result = array_values($campuses);
    usort($result, fn($a, $b) => strcasecmp($a['name'], $b['name']));

    return $result;
  }

  /**
   * Get buildings for a specific campus/branch
   * Returns array with id and name for buildings in the specified branch
   *
   * @param string $branchId
   * @return array
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

    // Sort by name
    usort($buildings, fn($a, $b) => strcasecmp($a['name'], $b['name']));

    return $buildings;
  }

  /**
   * Get all locations grouped by campus for easier frontend handling
   *
   * @return array
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
              'buildings' => []
          ];
        }

        $grouped[$branchId]['buildings'][] = [
          'id' => $location['id'],
          'name' => $location['name'],
        ];
      }
    }

    // Sort campuses and buildings by name
    foreach ($grouped as &$campus) {
      usort($campus['buildings'], fn($a, $b) => strcasecmp($a['name'], $b['name']));
    }

    $result = array_values($grouped);
    usort($result, fn($a, $b) => strcasecmp($a['name'], $b['name']));

    return $result;
  }

  /**
   * Clear the locations cache
   *
   * @return void
   */
  public function clearCache(): void {
    Cache::forget('topdesk.locations');
  }

  /**
   * Set cache duration in minutes
   *
   * @param int $minutes
   * @return void
   */
  public function setCacheDuration(int $minutes): void {
    $this->cacheMinutes = $minutes;
  }

  /**
   * Create a single asset in TopDesk.
   *
   * @param string $assetName
   * @return array Asset data with ID
   * @throws Exception
   */
  public function createAsset(string $srjcTag): array {
    try {
      $response = Http::withBasicAuth($this->username, $this->password)
        ->withHeaders([
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
        ])->timeout(30)->post($this->baseUrl . '/tas/api/assetmgmt/assets', [
          'type_id' => $this->topDeskTemplateId,
          'name' => $srjcTag,
        ]);

      if ($response->successful()) {
        $asset = $response->json();
        Log::info('TopDesk Asset Created', [
          'asset_id' => $asset['data']['unid'] ?? null,
          'name' => $srjcTag,
        ]);
        return $asset;
      }

      throw new \Exception('Failed to create asset in TopDesk API. Status: ' . $response->status());

    }
    catch (\Exception $e) {
      Log::error('TopDesk API Error - Create Asset', [
          'message' => $e->getMessage(),
          'asset_name' => $srjcTag
      ]);
      throw $e;
    }
  }

  /**
   * Get all assignments for an asset
   *
   * @param string $assetId
   * @return array
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
          'asset_id' => $assetId
      ]);
      throw $e;
    }
  }

  /**
   * Unlink an asset from a specific target (like a location)
   *
   * @param string $assetId
   * @param string $type (e.g., 'location')
   * @param string $targetId
   * @return bool
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
        Log::info('TopDesk Asset Unlinked', [
          'asset_id' => $assetId,
          'type' => $type,
          'target_id' => $targetId
        ]);
        return true;
      }

      throw new \Exception('Failed to unlink asset from target. Status: ' . $response->status());

    }
    catch (\Exception $e) {
      Log::error('TopDesk API Error - Unlink Asset', [
        'message' => $e->getMessage(),
        'asset_id' => $assetId,
        'type' => $type,
        'target_id' => $targetId
      ]);
      throw $e;
    }
  }

  /**
   * Clear all location assignments for an asset
   *
   * @param string $assetId
   * @return int Number of assignments cleared
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
    // Look for location assignments and unlink them


    if ($clearedCount > 0) {
      Log::info('TopDesk Asset Location Assignments Cleared', [
        'asset_id' => $assetId,
        'cleared_count' => $clearedCount,
      ]);
    }

    return $clearedCount;
  }

  /**
   * Assign an asset to a location.
   *
   * @param string $assetId
   * @param string $branchId
   * @param string $locationId
   * @return bool
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
          'linkType' => 'location'
        ]);

      if ($response->successful()) {
        Log::info('TopDesk Asset Assigned', [
          'asset_id' => $assetId,
          'branch_id' => $branchId,
          'location_id' => $locationId
        ]);
        return true;
      }

      throw new \Exception('Failed to assign asset to location. Status: ' . $response->status());

    }
    catch (\Exception $e) {
      Log::error('TopDesk API Error - Assign Asset', [
        'message' => $e->getMessage(),
        'asset_id' => $assetId,
        'branch_id' => $branchId,
        'location_id' => $locationId
      ]);
      throw $e;
    }
  }

  public function createAndAssignAsset(string $srjcTag, string $branchId, string $locationId): array
{
    // Search for existing asset
    $existingAsset = $this->searchAssetsByName($srjcTag);

    if ($existingAsset) {
        // Asset exists - clear existing location assignments first
        $assetId = $existingAsset['id'];
        $clearedCount = $this->clearAssetLocationAssignments($assetId);

        Log::info('TopDesk Asset Found - Reassigning Location', [
            'asset_id' => $assetId,
            'name' => $srjcTag,
            'cleared_assignments' => $clearedCount
        ]);

        // Assign to new location
        $this->assignAssetToLocation($assetId, $branchId, $locationId);

        return [
            'asset' => $existingAsset,
            'operation' => 'reassigned',
            'cleared_assignments' => $clearedCount
        ];
    } else {
        // Asset doesn't exist - create new one
        $asset = $this->createAsset($srjcTag);
        $assetId = $asset['data']['unid'] ?? null;

        if (!$assetId) {
            throw new \Exception('Asset creation succeeded but no unid returned');
        }

        // Assign newly created asset to location
        $this->assignAssetToLocation($assetId, $branchId, $locationId);

        return [
            'asset' => $asset,
            'operation' => 'created'
        ];
    }
  }
  /**
   * Search for assets by name using the filter endpoint.
   *
   * @param string $assetName
   * @return array|null
   * @throws Exception
   */
  public function searchAssetsByName(string $assetName): array|null {
    try {
      $response = Http::withBasicAuth($this->username, $this->password)
        ->withHeaders([
          'Content-Type' => 'application/json',
          'Accept' => 'application/json',
        ])->timeout(30)->post($this->baseUrl . '/tas/api/assetmgmt/assets/filter', [
          'templateId' => [$this->topDeskTemplateId],
          '$filter' => "name eq '{$assetName}'",
        ]);

      if ($response->successful()) {
        $result = $response->json();
        $dataSet = $result['dataSet'] ?? [];
        // TopDesk filter endpoint typically returns { "dataSet": [...] }
        return !empty($dataSet) ? $dataSet[0] : NULL;
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
     * Search for assets by name and return the first match
     *
     * @param string $assetName
     * @return array|null Asset data or null if not found
     * @throws Exception
     */
    public function findAssetByName(string $assetName): ?array
    {
        $existingAssets = $this->searchAssetsByName($assetName);
        return count($existingAssets) > 0 ? $existingAssets[0] : null;
    }

}

