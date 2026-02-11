<x-layout>
  <x-slot:heading>
    Bulk Scan Assets
  </x-slot:heading>

  <div class="mb-8">
    <p class="text-sm text-gray-700">Note: If an asset already exists in TOPdesk with the same SRJC Tag, all information here will override any existing information in TOPdesk.</p>
  </div>

  <!-- First Form: Add to Local Storage -->
  <form id="addToListForm">
    @csrf

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-baseline">
      <div class="space-y-2">
        <label for="device_type" class="block text-sm font-semibold text-gray-700">Device Type <span class="text-red-500 text-sm">*</span></label>
        <select id="device_type" name="device_type" required
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed">
          <option value="">Loading device types...</option>
        </select>
        <div id="device-type-error" class="hidden text-sm text-red-600"></div>
      </div>

      <div class="space-y-2">
        <label for="make" class="block text-sm font-semibold text-gray-700">Make <span class="text-red-500 text-sm">*</span></label>
        <select id="make" name="make" required
          class="w-full px-3 h-10 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed">
          <option value="">Loading asset makes...</option>
        </select>
        <div id="make-error" class="hidden text-sm text-red-600"></div>
      </div>

      <div class="space-y-2">
        <label for="model" class="block text-sm font-semibold text-gray-700">Model <small>(Search or add new) <span class="text-red-500 text-sm">*</span></small></label>
        <input type="text" id="model" name="model" required
          value="{{ old('model', session('bulk_scan.model', '')) }}"
          class="w-full">
          <div id="model-error" class="hidden text-sm text-red-600"></div>
        </div>
    </div>

    <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">
      <div class="space-y-2">
        <label for="campus" class="block text-sm font-semibold text-gray-700">Campus <span class="text-red-500 text-sm">*</span></label>
        <select id="campus" name="campus" required
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed">
          <option value="">Loading campuses...</option>
        </select>
        <div id="campus-error" class="hidden text-sm text-red-600"></div>
      </div>

      <div class="space-y-2">
        <label for="building" class="block text-sm font-semibold text-gray-700">Building <span class="text-red-500 text-sm">*</span></label>
        <select id="building" name="building" required disabled
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed">
          <option value="">Select a campus first</option>
        </select>
        <div id="building-error" class="hidden text-sm text-red-600"></div>
      </div>

      <div class="space-y-2">
        <label for="room" class="block text-sm font-semibold text-gray-700">Room <span class="text-red-500 text-sm">*</span></label>
        <input type="text" id="room" name="room" required
          value="{{ old('room', session('bulk_scan.room', '')) }}"
          class="bg-white w-full px-3 h-10 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
      </div>
    </div>

    <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">

      <div class="space-y-2">
        <label for="team" class="block text-sm font-semibold text-gray-700">Responsible Team <span class="text-red-500 text-sm">*</span></label>
        <select id="team" name="team" required
          class="w-full px-3 h-10 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed">
          <option value="">Loading teams...</option>
        </select>
        <div id="team-error" class="hidden text-sm text-red-600"></div>
      </div>


      <div class="space-y-2">
        <label for="purchased" class="block text-sm font-semibold text-gray-700">Purchase Date</label>

        <div class="relative max-w-sm">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
            </div>
            <input
              type="date"
              id="purchased"
              name="purchased"
              value="{{ old('purchased', session('bulk_scan.purchased', '')) }}"
              class="bg-white w-full px-3 h-10 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
             />
          </div>


        <div id="purchase-date-error" class="hidden text-sm text-red-600"></div>
      </div>

      <div class="space-y-2">
        <label for="stock" class="block text-sm font-semibold text-gray-700">Stockroom</label>
        <select id="stock" name="stock"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed">
          <option value="">Loading stockrooms...</option>
        </select>
        <small>(If not set, will unset any currently linked stockrooms)</small>
        <div id="stock-error" class="hidden text-sm text-red-600"></div>
      </div>

      <div class="space-y-2 flex gap-1 items-center align-middle">
        <input type="checkbox" id="surplus" name="surplus"
          value="{{ old('surplus', session('bulk_scan.surplus', '')) }}"
          class="m-0 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
        <label for="surplus" class="text-sm font-semibold text-gray-700">Surplus</label>
      </div>
    </div>

    <hr class="my-6 border-gray-300">

    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 items-baseline">
      <div class="space-y-2">
        <label for="srjc_tag" class="block text-sm font-semibold text-gray-700">SRJC Tag <span class="text-red-500 text-sm">*</span></label>
        <input type="text" id="srjc_tag" name="srjc_tag" required autofocus
          value="{{ old('srjc_tag') }}"
          class="bg-white w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div class="space-y-2">
        <label for="serial_number" class="block text-sm font-semibold text-gray-700">Serial Number <span class="text-red-500 text-sm">*</span></label>
        <input type="text" id="serial_number" name="serial_number" required
          value="{{ old('serial_number') }}"
          class="bg-white w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
      </div>
    </div>

    <div class="mt-8">
      <button type="submit"
        class="bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-semibold px-6 py-2 rounded-md transition-colors">
        Add to List
      </button>
    </div>
  </form>

  <!-- Form Messages -->

  <div id="form-messages" class="mt-3"></div>

  <div id="submit-progress" class="hidden mt-2 w-full bg-gray-400 rounded">
    <div id="submit-progress-bar" class="bg-blue-600 text-xs text-white text-center rounded h-4 w-0">0%</div>
  </div>

  <!-- Assets Table -->

  <div class="mt-12">
    <div class="flex justify-between items-center mb-4">
      <h3 class="text-lg font-semibold text-gray-800">Assets to Submit</h3>
      <div id="asset-count" class="text-sm text-gray-600">0 assets</div>
    </div>

    <div id="assets-table-container" class="hidden">
      <div class="overflow-x-auto shadow-md rounded-lg">
        <table class="min-w-full bg-white">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tracking</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Surplus</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody id="assets-table-body" class="bg-white divide-y divide-gray-200">
            <!-- Rows will be inserted here dynamically -->
          </tbody>
        </table>
      </div>

      <!-- Second Form: Submit All Assets -->
      <div class="mt-8 p-4 bg-gray-50 rounded-lg">
        <form id="submitAllForm" method="POST" action="{{ route('assets.store') }}">
          @csrf
          <div class="flex justify-between items-center">
            <p class="text-sm text-gray-600">Ready to submit <span id="submit-count">0</span> assets to TOPdesk?</p>
            <button type="submit"
              class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-semibold px-6 py-2 rounded-md transition-colors"
              disabled>
              Submit All Assets
            </button>
          </div>
        </form>
        <div id="loading" class="hidden flex justify-end mt-2">
          <p><i class="fa-solid fa-spinner"></i> Please wait, submitting assets</p>
        </div>
      </div>
    </div>

    <div id="no-assets-message" class="text-center py-8 text-gray-500">
      <p>No assets added yet. Use the form above to add assets to your list.</p>
    </div>
  </div>

</x-layout>
