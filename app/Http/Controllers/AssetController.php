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
      'srjc_tag' => 'required|string',
    ]);

    try {
      $asset = $this->topDeskService->createAndAssignAsset(
        $request->input('srjc_tag'),
        $request->input('campus'),
        $request->input('building')
      );

      return redirect()->back()->with('success', "Asset '{$request->input('srjc_tag')}' created and assigned successfully!");

    }
    catch (\Exception $e) {
      return redirect()->back()
        ->with('error', 'Failed to create asset: ' . $e->getMessage())
        ->withInput();
    }
  }

}
