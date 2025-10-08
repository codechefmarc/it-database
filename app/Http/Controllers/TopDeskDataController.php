<?php

namespace App\Http\Controllers;

use App\Services\TopDeskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TopDeskDataController extends Controller {
  private TopDeskService $topDeskService;

  public function __construct(TopDeskService $topDeskService) {
    $this->topDeskService = $topDeskService;
  }

  /**
   * Get all campuses for the campus select dropdown.
   */
  public function getCampuses(): JsonResponse {
    try {
      $campuses = $this->topDeskService->getCampuses();

      return response()->json([
        'success' => TRUE,
        'data' => $campuses,
      ]);
    }
    catch (\Exception $e) {
      return response()->json([
        'success' => FALSE,
        'message' => 'Failed to load campuses',
      ], 500);
    }
  }

  /**
   * Get buildings for a specific campus.
   */
  public function getBuildingsByCampus(Request $request): JsonResponse {
    $branchId = $request->get('campus_id');

    if (!$branchId) {
      return response()->json([
        'success' => FALSE,
        'message' => 'Campus ID is required',
      ], 400);
    }

    try {
      $buildings = $this->topDeskService->getBuildingsByCampus($branchId);

      return response()->json([
        'success' => TRUE,
        'data' => $buildings,
      ]);
    }
    catch (\Exception $e) {
      return response()->json([
        'success' => FALSE,
        'message' => 'Failed to load buildings',
      ], 500);
    }
  }

  /**
   * Get all data at once (useful for JavaScript handling)
   */
  public function getAllLocationData(): JsonResponse {
    try {
      $locationData = $this->topDeskService->getLocationsByCampus();

      return response()->json([
        'success' => TRUE,
        'data' => $locationData,
      ]);
    }
    catch (\Exception $e) {
      return response()->json([
        'success' => FALSE,
        'message' => 'Failed to load location data',
      ], 500);
    }
  }

  /**
   * Get all asset makes (for make/model dropdowns)
   */
  public function getAssetMakes(): JsonResponse {
    try {
      $makes = $this->topDeskService->getAssetMakes();

      return response()->json([
        'success' => TRUE,
        'data' => $makes['results'],
      ]);
    }
    catch (\Exception $e) {
      return response()->json([
        'success' => FALSE,
        'message' => 'Failed to load makes',
      ], 500);
    }
  }

  /**
   * Get all device types (for device type dropdowns)
   */
  public function getDeviceTypes(): JsonResponse {
    try {
      $makes = $this->topDeskService->getDeviceTypes();

      return response()->json([
        'success' => TRUE,
        'data' => $makes['results'],
      ]);
    }
    catch (\Exception $e) {
      return response()->json([
        'success' => FALSE,
        'message' => 'Failed to load device types',
      ], 500);
    }
  }

  /**
   * Get all asset templates.
   */
  public function getTemplates($filtered = TRUE): JsonResponse {
    try {
      $templates = $this->topDeskService->getTemplates();

      // Filter to only allowed templates.
      if ($filtered) {
        $templates['dataSet'] = array_filter($templates['dataSet'], function ($template) {
          return in_array($template['text'], $this->topDeskService->allowedTemplates);
        });
      }
      return response()->json([
        'success' => TRUE,
        'data' => array_values($templates['dataSet']),
      ]);
    }
    catch (\Exception $e) {
      return response()->json([
        'success' => FALSE,
        'message' => 'Failed to load templates',
      ], 500);
    }
  }

  /**
   * Search assets by name for JS (for checking on correct template).
   */
  public function searchAssets(Request $request): JsonResponse {
    $name = $request->get('name');
    if (!$name) {
      return response()->json([
        'success' => FALSE,
        'message' => 'Asset name is required',
      ], 400);
    }

    try {
      $assets = $this->topDeskService->searchAssetsByName($name);

      return response()->json([
        'success' => TRUE,
        'data' => $assets,
      ]);
    }
    catch (\Exception $e) {
      return response()->json([
        'success' => FALSE,
        'message' => 'Failed to search assets',
      ], 500);
    }
  }

  /**
   * Clear the cache (for admin use)
   */
  public function clearCache(): JsonResponse {
    try {
      $this->topDeskService->clearCache();

      return response()->json([
        'success' => TRUE,
        'message' => 'Cache cleared successfully',
      ]);
    }
    catch (\Exception $e) {
      return response()->json([
        'success' => FALSE,
        'message' => 'Failed to clear cache',
      ], 500);
    }
  }

}
