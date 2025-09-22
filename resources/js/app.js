import './bootstrap';

class AssetForm {
  constructor() {
    this.campusSelect = document.getElementById('campus');
    this.buildingSelect = document.getElementById('building');
    this.makeSelect = document.getElementById('make');
    this.addToListForm = document.getElementById('addToListForm');
    this.submitAllForm = document.getElementById('submitAllForm');
    this.messagesDiv = document.getElementById('form-messages');
    this.formData = document.getElementById('form-data');
    this.srjcTagInput = document.getElementById('srjc_tag');
    this.serialNumberInput = document.getElementById('serial_number');
    this.loadingDiv = document.getElementById('loading');

    // Table elements
    this.assetsTableContainer = document.getElementById('assets-table-container');
    this.assetsTableBody = document.getElementById('assets-table-body');
    this.noAssetsMessage = document.getElementById('no-assets-message');
    this.assetCount = document.getElementById('asset-count');
    this.submitCount = document.getElementById('submit-count');
    this.submitAllButton = this.submitAllForm.querySelector('button[type="submit"]');

    // Store saved values from data attributes
    this.savedValues = this.getSavedValues();

    // Local storage key
    this.storageKey = 'bulk_scan_assets';

    this.init();
  }

  getSavedValues() {
    if (!this.formData) return {};

    return {
      campus: this.formData.dataset.savedCampus || '',
      building: this.formData.dataset.savedBuilding || '',
      make: this.formData.dataset.savedMake || ''
    };
  }

  async init() {
      await this.loadCampuses();
      await this.loadMakes();
      this.setupEventListeners();

      // Restore saved values after everything is loaded
      this.restoreSavedValues();

      // Load and display existing assets from local storage
      this.loadAssetsFromStorage();
  }

  async restoreSavedValues() {
    // Restore campus first
    if (this.savedValues.campus) {
      this.campusSelect.value = this.savedValues.campus;
      this.campusSelect.dispatchEvent(new Event('change'));
    }

    // Wait for buildings to load if campus was restored
    if (this.savedValues.campus && this.savedValues.building) {
      // Wait a bit for buildings to load, then restore building
      setTimeout(() => {
        if (this.savedValues.building) {
          this.buildingSelect.value = this.savedValues.building;
          this.buildingSelect.dispatchEvent(new Event('change'));
        }
      }, 100);
    }

    // Restore make
    if (this.savedValues.make) {
      this.makeSelect.value = this.savedValues.make;
      this.makeSelect.dispatchEvent(new Event('change'));
    }
  }

  async loadCampuses() {
    try {
      this.setCampusLoading(true);

      const response = await fetch('/topdesk/campuses');
      const data = await response.json();

      if (data.success) {
          this.populateCampuses(data.data);
          this.hideError('campus-error');
      } else {
          this.showError('campus-error', 'Failed to load campuses');
      }
    } catch (error) {
      console.error('Error loading campuses:', error);
      this.showError('campus-error', 'Error loading campuses');
    } finally {
      this.setCampusLoading(false);
    }
  }

  populateCampuses(campuses) {
    this.campusSelect.innerHTML = '<option value="">Select a campus</option>';

    campuses.forEach(campus => {
      const option = document.createElement('option');
      option.value = campus.id;
      option.textContent = campus.name;
      this.campusSelect.appendChild(option);
    });

    // Restore saved campus value after populating
    if (this.savedValues.campus) {
      this.campusSelect.value = this.savedValues.campus;
    }
  }

  async loadBuildings(campusId) {
      try {
        this.setBuildingLoading(true);

        const response = await fetch(`/topdesk/buildings?campus_id=${campusId}`);
        const data = await response.json();

        if (data.success) {
            this.populateBuildings(data.data);
            this.hideError('building-error');
            this.buildingSelect.disabled = false;
        } else {
            this.showError('building-error', 'Failed to load buildings');
        }
      } catch (error) {
        console.error('Error loading buildings:', error);
        this.showError('building-error', 'Error loading buildings');
      } finally {
        this.setBuildingLoading(false);
      }
  }

  populateBuildings(buildings) {
    this.buildingSelect.innerHTML = '<option value="">Select a building</option>';

    buildings.forEach(building => {
      const option = document.createElement('option');
      option.value = building.id;
      option.textContent = building.name;
      this.buildingSelect.appendChild(option);
    });

    // Restore saved building value after populating
    if (this.savedValues.building) {
      this.buildingSelect.value = this.savedValues.building;
    }
  }

  async loadMakes() {
    try {
      this.setMakesLoading(true);

      const response = await fetch('/topdesk/asset-makes');
      const data = await response.json();

      if (data.success) {
          this.populateMakes(data.data);
          this.hideError('make-error');
      } else {
          this.showError('make-error', 'Failed to load makes');
      }
    } catch (error) {
      console.error('Error loading makes:', error);
      this.showError('make-error', 'Error loading makes');
    } finally {
      this.setMakesLoading(false);
    }
  }

  populateMakes(makes) {
    this.makeSelect.innerHTML = '<option value="">Select a make</option>';

    makes.forEach(make => {
      const option = document.createElement('option');
      option.value = make.id;
      option.textContent = make.name;
      this.makeSelect.appendChild(option);
    });

    // Restore saved make value after populating
    if (this.savedValues.make) {
      this.makeSelect.value = this.savedValues.make;
    }
  }

  setupEventListeners() {
    this.srjcTagInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        this.serialNumberInput.focus();
      }
    });

    this.campusSelect.addEventListener('change', (e) => {
      const campusId = e.target.value;

      if (campusId) {
          this.loadBuildings(campusId);
      } else {
          this.resetBuildings();
      }
    });

    // Add to list form submission
    this.addToListForm.addEventListener('submit', (e) => {
      e.preventDefault();
      this.handleAddToList();
    });

    // Submit all form submission
    this.submitAllForm.addEventListener('submit', (e) => {
      e.preventDefault();
      this.handleSubmitAll();
    });
  }

  resetBuildings() {
    this.buildingSelect.innerHTML = '<option value="">Select a campus first</option>';
    this.buildingSelect.disabled = true;
    this.hideError('building-error');
  }

  setCampusLoading(loading) {
    if (loading) {
      this.campusSelect.innerHTML = '<option value="">Loading campuses...</option>';
      this.campusSelect.disabled = true;
    } else {
      this.campusSelect.disabled = false;
    }
  }

  setBuildingLoading(loading) {
    if (loading) {
      this.buildingSelect.innerHTML = '<option value="">Loading buildings...</option>';
      this.buildingSelect.disabled = true;
    }
  }

  setMakesLoading(loading) {
    if (loading) {
      this.makeSelect.innerHTML = '<option value="">Loading makes...</option>';
      this.makeSelect.disabled = true;
    } else {
      this.makeSelect.disabled = false;
    }
  }

  showError(elementId, message) {
    const errorDiv = document.getElementById(elementId);
    errorDiv.textContent = message;
    errorDiv.classList.remove('hidden');
  }

  hideError(elementId) {
    const errorDiv = document.getElementById(elementId);
    errorDiv.classList.add('hidden');
  }

  showMessage(message, type = 'info') {
    const colorClass = type === 'success' ? 'text-green-600' : type === 'error' ? 'text-red-600' : 'text-blue-600';
    this.messagesDiv.innerHTML = `<div class="${colorClass} text-sm font-medium">${message}</div>`;
  }

  // Local Storage Management Methods
  getAssetsFromStorage() {
    try {
      const assets = localStorage.getItem(this.storageKey);
      return assets ? JSON.parse(assets) : [];
    } catch (error) {
      console.error('Error reading from localStorage:', error);
      return [];
    }
  }

  saveAssetsToStorage(assets) {
    try {
      localStorage.setItem(this.storageKey, JSON.stringify(assets));
    } catch (error) {
      console.error('Error saving to localStorage:', error);
      this.showMessage('Error saving data', 'error');
    }
  }

  loadAssetsFromStorage() {
    const assets = this.getAssetsFromStorage();
    this.updateAssetsTable(assets);
  }

  handleAddToList() {
    const formData = new FormData(this.addToListForm);

    // Validate required fields
    const requiredFields = ['campus', 'building', 'room', 'make', 'model', 'srjc_tag', 'serial_number'];
    const missingFields = [];

    requiredFields.forEach(field => {
      if (!formData.get(field)) {
        missingFields.push(field);
      }
    });

    if (missingFields.length > 0) {
      this.showMessage(`Please fill in all required fields: ${missingFields.join(', ')}`, 'error');
      return;
    }

    // Create asset object
    const asset = {
      id: Date.now(), // Simple unique ID
      campus: formData.get('campus'),
      campusName: this.getSelectText(this.campusSelect),
      building: formData.get('building'),
      buildingName: this.getSelectText(this.buildingSelect),
      room: formData.get('room'),
      make: formData.get('make'),
      makeName: this.getSelectText(this.makeSelect),
      model: formData.get('model'),
      srjc_tag: formData.get('srjc_tag'),
      serial_number: formData.get('serial_number'),
      created_at: new Date().toLocaleString()
    };

    // Check for duplicate SRJC tag
    const existingAssets = this.getAssetsFromStorage();
    const duplicateTag = existingAssets.find(a => a.srjc_tag === asset.srjc_tag);

    if (duplicateTag) {
      this.showMessage(`SRJC Tag "${asset.srjc_tag}" already exists in the list`, 'error');
      return;
    }

    // Add to storage
    existingAssets.push(asset);
    this.saveAssetsToStorage(existingAssets);

    // Clear only the non-persistent fields
    document.getElementById('srjc_tag').value = '';
    document.getElementById('serial_number').value = '';

    // Update display
    this.updateAssetsTable(existingAssets);
    this.showMessage(`Asset "${asset.srjc_tag}" added to list successfully!`, 'success');

    // Focus on next input for faster data entry
    document.getElementById('srjc_tag').focus();
  }

  getSelectText(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    return selectedOption ? selectedOption.textContent : '';
  }

  updateAssetsTable(assets) {
    const count = assets.length;

    // Update counters
    this.assetCount.textContent = `${count} asset${count !== 1 ? 's' : ''}`;
    this.submitCount.textContent = count;

    if (count === 0) {
      this.assetsTableContainer.classList.add('hidden');
      this.noAssetsMessage.classList.remove('hidden');
      this.submitAllButton.disabled = true;
    } else {
      this.assetsTableContainer.classList.remove('hidden');
      this.noAssetsMessage.classList.add('hidden');
      this.submitAllButton.disabled = false;
    }

    // Update table body
    this.assetsTableBody.innerHTML = assets.map(asset => `
      <tr class="hover:bg-gray-50">
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${asset.campusName}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${asset.buildingName}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${asset.room}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${asset.makeName}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${asset.model}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${asset.srjc_tag}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${asset.serial_number}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
          <button onclick="assetForm.removeAsset(${asset.id})"
            class="text-red-600 hover:text-red-900 transition-colors">
            Remove
          </button>
        </td>
      </tr>
    `).join('');
  }

  removeAsset(assetId) {
    const assets = this.getAssetsFromStorage();
    const updatedAssets = assets.filter(asset => asset.id !== assetId);

    this.saveAssetsToStorage(updatedAssets);
    this.updateAssetsTable(updatedAssets);

    const removedAsset = assets.find(asset => asset.id === assetId);
    if (removedAsset) {
      this.showMessage(`Asset "${removedAsset.srjc_tag}" removed from list`, 'success');
    }
  }

  handleSubmitAll() {
    const assets = this.getAssetsFromStorage();
    const formData = new FormData(this.submitAllForm);
    formData.append('assets', JSON.stringify(assets));

    if (assets.length === 0) {
      this.showMessage('No assets to submit', 'error');
      return;
    }

    // For now, just log what would be submitted
    console.log('Would submit these assets:', assets);
    this.showMessage(`Would submit ${assets.length} assets to database`, 'info');

    this.loadingDiv.classList.remove('hidden');

    fetch(this.submitAllForm.action, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: formData
    })
    .then((response) => response.json())
    .then((data) => {
      console.log('Saved:', data); // now you see each asset's success/error

      // Clear storage, reset form, refocus tag input
      localStorage.removeItem(this.storageKey);
      this.submitAllForm.reset();
      document.getElementById('srjc_tag').focus();
    })
    .catch((error) => {
      console.error('Error:', error);
      alert('Something went wrong. Please try again.');
    })
    .finally(() => {
      // Hide spinner
      this.loadingDiv.classList.add('hidden');
      this.updateAssetsTable([]);
      this.showMessage('All assets submitted successfully!', 'success');
    });

  }

  handleSubmit() {
    const formData = new FormData(this.form);
    const data = {
      campus: formData.get('campus'),
      building: formData.get('building'),
      assetName: formData.get('assetName')
    };

    // For now, just show what would be submitted
    this.showMessage(`Form data: Campus ID: ${data.campus}, Building ID: ${data.building}, Asset Name: ${data.assetName}`, 'success');

    console.log('Form submission data:', data);
  }
}

// Initialize the form when the page loads
document.addEventListener('DOMContentLoaded', () => {
  window.assetForm = new AssetForm();
});
