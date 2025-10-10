<?php

namespace App\Http\Controllers;

use App\Services\TopDeskService;
use Illuminate\Http\Request;

class AssetController extends Controller {
  /**
   * The TopDesk service instance.
   */
  private TopDeskService $topDeskService;

  public function __construct(TopDeskService $topDeskService) {
    $this->topDeskService = $topDeskService;
  }

  /**
   * Show the asset creation form.
   */
  public function create() {
    return view('assets.create');
  }

  /**
   * Create and assign an asset.
   */
  public function store(Request $request) {
    $jsonAssets = $request->input('assets');
    $assets = json_decode($jsonAssets, TRUE);

    if (!$assets || !is_array($assets)) {
      return response()->json(['error' => 'Invalid asset data'], 422);
    }

    $results = [];

    foreach ($assets as $assetData) {
      try {
        $result = $this->topDeskService->createAndAssignAsset($assetData);
        $results[] = [
          'asset' => $assetData['srjc_tag'] ?? NULL,
          'success' => TRUE,
          'model_id' => $result['model_id'],
        ];
      }
      catch (\Exception $e) {
        $results[] = [
          'asset' => $assetData['srjc_tag'] ?? NULL,
          'success' => FALSE,
          'error' => $e->getMessage(),
        ];
      }
    }

    if ($request->ajax()) {
      return response()->json([
        'success' => TRUE,
        'results' => $results,
      ]);
    }

    // Fallback for non-AJAX.
    return redirect()->back()->with('status', 'Assets processed.');
  }

}
