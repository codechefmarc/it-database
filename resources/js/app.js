import './bootstrap';

class AssetForm {
  constructor() {
    this.campusSelect = document.getElementById('campus');
    this.buildingSelect = document.getElementById('building');
    this.roomInput = document.getElementById('room');
    this.makeSelect = document.getElementById('make');
    this.modelInput = document.getElementById('model');
    this.templateSelect = document.getElementById('template');
    this.addToListForm = document.getElementById('addToListForm');
    this.submitAllForm = document.getElementById('submitAllForm');
    this.messagesDiv = document.getElementById('form-messages');
    this.formData = document.getElementById('form-data');
    this.srjcTagInput = document.getElementById('srjc_tag');
    this.serialNumberInput = document.getElementById('serial_number');
    this.loadingDiv = document.getElementById('loading');
    this.templates = [];

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
    const saved = localStorage.getItem('bulk_scan_last');
    if (!saved) return {};

    try {
      const parsed = JSON.parse(saved);
      return {
        template: parsed.template || '',
        campus: parsed.campus || '',
        building: parsed.building || '',
        room: parsed.room || '',
        make: parsed.make || '',
        model: parsed.model || ''
      };
    } catch (e) {
      console.error('Failed to parse saved bulk scan values', e);
      return {};
    }
  }

  async init() {
    await this.loadTemplates();
    await this.loadCampuses();
    await this.loadMakes();
    this.setupEventListeners();

    // Restore saved values after everything is loaded
    this.restoreSavedValues();

    // Load and display existing assets from local storage
    this.loadAssetsFromStorage();
  }

  async restoreSavedValues() {
    const saved = this.getSavedValues();

    // Restore template
    if (saved.template) {
      this.templateSelect.value = saved.template;
      this.templateSelect.dispatchEvent(new Event('change'));
    }

    // Restore campus first
    if (saved.campus) {
      this.campusSelect.value = saved.campus;
      this.campusSelect.dispatchEvent(new Event('change'));
    }

    // Restore building after a short delay to wait for buildings to load
    if (saved.building) {
      setTimeout(() => {
        this.buildingSelect.value = saved.building;
        this.buildingSelect.dispatchEvent(new Event('change'));
      }, 100);
    }

    // Restore make
    if (saved.make) {
      this.makeSelect.value = saved.make;
      this.makeSelect.dispatchEvent(new Event('change'));
    }

    if (saved.room) this.roomInput.value = saved.room;
    if (saved.model) this.modelInput.value = saved.model;
  }

  async loadCampuses() {
    try {
      this.setCampusLoading(true);

      const response = await fetch(window.apiRoutes.campuses);
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

        const response = await fetch(`${window.apiRoutes.buildings}?campus_id=${campusId}`);
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

      const response = await fetch(window.apiRoutes.assetMakes);
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

  async loadTemplates() {
    try {
      this.setTemplatesLoading(true);

      const response = await fetch(window.apiRoutes.templates);
      const data = await response.json();
      if (data.success) {
          this.templates = data.data;
          this.populateTemplates(this.templates);
          this.hideError('template-error');
      } else {
          this.showError('template-error', 'Failed to load templates');
      }
    } catch (error) {
      console.error('Error loading templates:', error);
      this.showError('template-error', 'Error loading templates');
    } finally {
      this.setTemplatesLoading(false);
    }
  }

  populateTemplates(templates) {
    this.templateSelect.innerHTML = '<option value="">Select a template</option>';

    templates.forEach(template => {
      const option = document.createElement('option');
      option.value = template.id;
      option.textContent = template.text;
      this.templateSelect.appendChild(option);
    });

    // Restore saved template value after populating
    if (this.savedValues.template) {
      this.templateSelect.value = this.savedValues.template;
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

  setTemplatesLoading(loading) {
    if (loading) {
      this.templateSelect.innerHTML = '<option value="">Loading templates...</option>';
      this.templateSelect.disabled = true;
    } else {
      this.templateSelect.disabled = false;
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
    const requiredFields = ['template', 'campus', 'building', 'room', 'make', 'model', 'srjc_tag', 'serial_number'];
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
      template: formData.get('template'),
      templateName: this.getSelectText(this.templateSelect),
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

    // Save last submitted values for form restoration
    localStorage.setItem('bulk_scan_last', JSON.stringify({
      template: asset.template,
      campus: asset.campus,
      building: asset.building,
      room: asset.room,
      make: asset.make,
      model: asset.model
    }));

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
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${asset.templateName}</td>
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

  async handleSubmitAll() {
    const assets = this.getAssetsFromStorage();

    if (assets.length === 0) {
      this.showMessage('No assets to submit', 'error');
      return;
    }

    this.loadingDiv.classList.remove('hidden');
    this.submitAllButton.disabled = true;

    // Show progress bar
    const progressContainer = document.getElementById('submit-progress');
    const progressBar = document.getElementById('submit-progress-bar');
    progressContainer.classList.remove('hidden');
    progressBar.style.width = '0%';
    progressBar.textContent = '0%';

    const results = [];
    const errors = []; // Collect all errors here
    const skippedAssets = []; // Track skipped assets

    for (let i = 0; i < assets.length; i++) {
      const asset = assets[i];

      // Check template before submitting
      const templateCheck = await this.checkTemplateMatch(asset.srjc_tag);

      if (!templateCheck.matches) {
        if (templateCheck.reason === 'unapproved_existing_template') {
          errors.push(`Cannot update existing asset ${asset.srjc_tag}: It has template ${templateCheck.existingTemplate} (must be Desktop or Laptop)`);
        } else {
          errors.push(`Template of ${asset.srjc_tag} is ${templateCheck.existingTemplate} not ${templateCheck.selectedTemplate}`);
        }
        skippedAssets.push(asset.srjc_tag);
        results.push({ asset: asset.srjc_tag, success: false, skipped: true, reason: 'template_mismatch' });

        // Update progress
        const percent = Math.round(((i + 1) / assets.length) * 100);
        progressBar.style.width = `${percent}%`;
        progressBar.textContent = `${i + 1} / ${assets.length}`;
        continue;
      }

      try {
        const formData = new FormData(this.submitAllForm);
        formData.append('assets', JSON.stringify([asset]));

        const response = await fetch(this.submitAllForm.action, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: formData
        });

        const data = await response.json();

        if (response.ok && data.success) {
          results.push({ asset: asset.srjc_tag, success: true, data });
        } else {
          // Handle API errors
          const errorMessage = data.message || 'Unknown error occurred';
          errors.push(`Failed "${asset.srjc_tag}": ${errorMessage}`);
          results.push({ asset: asset.srjc_tag, success: false, error: errorMessage });
        }
      } catch (error) {
        console.error('Error submitting asset', asset, error);
        errors.push(`Error "${asset.srjc_tag}": ${error.message}`);
        results.push({ asset: asset.srjc_tag, success: false, error: error.message });
      }

      // Update progress
      const percent = Math.round(((i + 1) / assets.length) * 100);
      progressBar.style.width = `${percent}%`;
      progressBar.textContent = `${i + 1} / ${assets.length}`;
    }

    // Calculate results
    const successCount = results.filter(r => r.success).length;
    const errorCount = results.filter(r => !r.success && !r.skipped).length;
    const skippedCount = results.filter(r => r.skipped).length;

    // Clean up successful assets from storage, but keep failed/skipped ones
    if (successCount > 0) {
      const successfulTags = results.filter(r => r.success).map(r => r.asset);
      const remainingAssets = assets.filter(asset => !successfulTags.includes(asset.srjc_tag));
      this.saveAssetsToStorage(remainingAssets);
      this.updateAssetsTable(remainingAssets);
    }

    // Show final results
    this.displayFinalResults(successCount, errorCount, skippedCount, errors);

    // Clean up UI
    progressContainer.classList.add('hidden');
    this.loadingDiv.classList.add('hidden');
    this.submitAllButton.disabled = false;

    if (successCount > 0) {
      document.getElementById('srjc_tag').focus();
    }
  }

  // New method to display comprehensive results
  displayFinalResults(successCount, errorCount, skippedCount, errors) {
    let message = '';
    let messageType = 'success';

    // Build summary message
    const parts = [];
    if (successCount > 0) {
      parts.push(`${successCount} asset${successCount !== 1 ? 's' : ''} submitted successfully`);
    }
    if (errorCount > 0) {
      parts.push(`${errorCount} failed`);
      messageType = 'error';
    }
    if (skippedCount > 0) {
      parts.push(`${skippedCount} skipped`);
      messageType = 'error';
    }

    if (parts.length === 0) {
      message = 'No assets processed';
      messageType = 'error';
    } else {
      message = parts.join(', ');
    }

    // Display summary and errors
    if (errors.length > 0) {
      const errorHtml = `
        <div class="text-red-600 text-sm font-medium mb-2">
          Processing completed: ${message}
        </div>
        <div class="text-red-600 text-sm font-medium mb-2">
          Issues encountered:
        </div>
        <ul class="text-red-600 text-sm list-disc list-inside space-y-1 ml-4">
          ${errors.map(error => `<li>${error}</li>`).join('')}
        </ul>
      `;
      this.messagesDiv.innerHTML = errorHtml;
    } else if (successCount > 0) {
      this.showMessage(message, 'success');
    } else {
      this.showMessage('No assets were processed', 'error');
    }
  }

  // New method to check template match and return detailed info
  async checkTemplateMatch(srjcTag) {
    try {
      const response = await fetch(`${window.apiRoutes.searchAssets}?name=${encodeURIComponent(srjcTag)}`);
      const data = await response.json();

      // Get the currently selected template name from the form
      const selectedTemplateId = this.templateSelect.value;
      const selectedTemplate = this.templates.find(template => template.id === selectedTemplateId);
      const selectedTemplateName = selectedTemplate ? selectedTemplate.text : 'Unknown';

      // Get list of approved template names
      const approvedTemplateNames = this.templates.map(template => template.text);

      if (data.success && data.data) {
        const asset = data.data;
        const existingTemplateName = asset.template_name;

        // console.log('Existing template:', existingTemplateName);
        // console.log('Selected template:', selectedTemplateName);
        // console.log('Approved templates:', approvedTemplateNames);

        // For existing assets, check if the existing template is in our approved list
        const existingTemplateApproved = approvedTemplateNames.includes(existingTemplateName);

        if (!existingTemplateApproved) {
          return {
            matches: false,
            existingTemplate: existingTemplateName,
            selectedTemplate: selectedTemplateName,
            assetExists: true,
            reason: 'unapproved_existing_template'
          };
        }

        // If existing template is approved (Desktop or Laptop), it's fine regardless of selection
        return {
          matches: true,
          existingTemplate: existingTemplateName,
          selectedTemplate: selectedTemplateName,
          assetExists: true
        };
      }

      // Asset doesn't exist, so template is "correct" (no conflict) - will use selected template
      return {
        matches: true,
        existingTemplate: null,
        selectedTemplate: selectedTemplateName,
        assetExists: false
      };

    } catch (error) {
      console.error('Error checking template for SRJC Tag', srjcTag, error);
      // Return true on error to not block the process
      const selectedTemplateId = this.templateSelect.value;
      const selectedTemplate = this.templates.find(template => template.id === selectedTemplateId);
      const selectedTemplateName = selectedTemplate ? selectedTemplate.text : 'Unknown';

      return {
        matches: true,
        existingTemplate: null,
        selectedTemplate: selectedTemplateName,
        assetExists: false
      };
    }
  }

}
// Initialize the form when the page loads
document.addEventListener('DOMContentLoaded', () => {
  window.assetForm = new AssetForm();
});
