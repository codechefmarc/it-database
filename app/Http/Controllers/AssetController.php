<?php

namespace App\Http\Controllers;

use App\Services\TopDeskService;
use Illuminate\Http\Request;

class AssetController extends Controller {
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
    $request->validate([
      'campus' => 'required|string',
      'building' => 'required|string',
      'room' => 'required|string',
      'srjc_tag' => 'required|string',
    ]);

    try {
      $assetData = [
        'srjc_tag' => $request->input('srjc_tag'),
        'campus' => $request->input('campus'),
        'building' => $request->input('building'),
        'room' => $request->input('room'),
      ];
      $asset = $this->topDeskService->createAndAssignAsset($assetData);

      return redirect()->back()->with('success', "Asset '{$request->input('srjc_tag')}' created and assigned successfully!");

    }
    catch (\Exception $e) {
      return redirect()->back()
        ->with('error', 'Failed to create asset: ' . $e->getMessage())
        ->withInput();
    }
  }

}
