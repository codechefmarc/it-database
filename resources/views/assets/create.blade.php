<x-layout>
  <x-slot:heading>
    Bulk Scan Assets
  </x-slot:heading>

  <form id="assetForm" method="POST" action="{{ route('assets.store') }}">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
    </div>

    <!-- Placeholder for future asset fields -->
    <div class="space-y-2 mt-6">
        <label for="srjc_tag" class="block text-sm font-semibold text-gray-700">SRJC Tag</label>
        <input type="text" id="srjc_tag" name="srjc_tag" required
          placeholder="12345"
          class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
    </div>

    <div class="mt-8">
      <button type="submit"
        class="bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-semibold px-6 py-2 rounded-md transition-colors">
        Create Asset
      </button>
    </div>
  </form>

</x-layout>

