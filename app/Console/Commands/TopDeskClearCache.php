<?php

namespace App\Console\Commands;

use App\Services\TopDeskService;
use Illuminate\Console\Command;

/**
 * Clears the cached TopDesk API responses.
 *
 * @package App\Console\Commands
 */
class TopDeskClearCache extends Command {
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'topdesk:clear-cache';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Clear all cached TopDesk API responses';

  /**
   * Execute the console command.
   */
  public function handle(TopDeskService $topDeskService) {
    $topDeskService->clearCache();

    // Immediately warm the cache sequentially.
    $topDeskService->getLocations();
    $topDeskService->getAssetMakes();
    $topDeskService->getAssetTeams();
    $topDeskService->getAssetModels();
    $topDeskService->getStockRooms();
    $topDeskService->getDeviceTypes();

    $this->info('TopDesk cache cleared and refreshed.');
    return self::SUCCESS;
  }

}
