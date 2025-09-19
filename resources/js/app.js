import './bootstrap';

class AssetForm {
  constructor() {
    this.campusSelect = document.getElementById('campus');
    this.buildingSelect = document.getElementById('building');
    this.makeSelect = document.getElementById('make');
    this.form = document.getElementById('assetForm');
    this.messagesDiv = document.getElementById('form-messages');

    this.init();
  }

  async init() {
      await this.loadCampuses();
      await this.loadMakes();
      this.setupEventListeners();
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
  }

  setupEventListeners() {
    this.campusSelect.addEventListener('change', (e) => {
      const campusId = e.target.value;

      if (campusId) {
          this.loadBuildings(campusId);
      } else {
          this.resetBuildings();
      }
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
  new AssetForm();
});
