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
        <label for="device_type" class="block text-sm font-semibold text-gray-700">Device Type</label>
        <select id="device_type" name="device_type" required
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed">
          <option value="">Loading device types...</option>
        </select>
        <div id="device-type-error" class="hidden text-sm text-red-600"></div>
      </div>

      <div class="space-y-2">
        <label for="make" class="block text-sm font-semibold text-gray-700">Make *</label>
        <select id="make" name="make" required
          class="w-full px-3 h-10 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed">
          <option value="">Loading asset makes...</option>
        </select>
        <div id="make-error" class="hidden text-sm text-red-600"></div>
      </div>

      <div class="space-y-2">
        <label for="model" class="block text-sm font-semibold text-gray-700">Model</label>
        <input type="text" id="model" name="model" required
          value="{{ old('model', session('bulk_scan.model', '')) }}"
          class="bg-white w-full px-3 h-10 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
          <div id="model-error" class="hidden text-sm text-red-600"></div>
        </div>
    </div>

    <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">
      <div class="space-y-2">
        <label for="campus" class="block text-sm font-semibold text-gray-700">Campus *</label>
        <select id="campus" name="campus" required
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed">
          <option value="">Loading campuses...</option>
        </select>
        <div id="campus-error" class="hidden text-sm text-red-600"></div>
      </div>

      <div class="space-y-2">
        <label for="building" class="block text-sm font-semibold text-gray-700">Building *</label>
        <select id="building" name="building" required disabled
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-gray-900 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-50 disabled:text-gray-500 disabled:cursor-not-allowed">
          <option value="">Select a campus first</option>
        </select>
        <div id="building-error" class="hidden text-sm text-red-600"></div>
      </div>

      <div class="space-y-2">
        <label for="room" class="block text-sm font-semibold text-gray-700">Room</label>
        <input type="text" id="room" name="room" required
          value="{{ old('room', session('bulk_scan.room', '')) }}"
          class="bg-white w-full px-3 h-10 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
      </div>
    </div>



    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3 items-baseline">
      <div class="space-y-2">
        <label for="srjc_tag" class="block text-sm font-semibold text-gray-700">SRJC Tag</label>
        <input type="text" id="srjc_tag" name="srjc_tag" required autofocus
          value="{{ old('srjc_tag') }}"
          class="bg-white w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div class="space-y-2">
        <label for="serial_number" class="block text-sm font-semibold text-gray-700">Serial Number</label>
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
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Device Type</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campus</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Building</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Make</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SRJC Tag</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serial Number</th>
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
