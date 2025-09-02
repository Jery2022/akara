import React, { useState, useEffect } from 'react';
import { createPortal } from 'react-dom';
import AutocompleteSelect from './AutocompleteSelect';
import { X } from 'lucide-react';

function EditQuittanceModal({ api, quittanceToEdit, onClose, onSave, loading, errorMessage, onClearBackendError }) {
  const [formState, setFormState] = useState({
    id: '',
    montant: '',
    date_paiement: '',
    date_emission: '',
    type: 'client',
    numero_quittance: '',
    employee_id: '',
    periode_service: '',
  });
  const [employees, setEmployees] = useState([]);
  const [validationError, setValidationError] = useState('');
  const token = localStorage.getItem('authToken');

  useEffect(() => {
    const fetchEmployees = async () => {
      try {
        const response = await fetch(`${api}/employees`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        if (!response.ok) throw new Error('Failed to fetch employees');
        const result = await response.json();
        setEmployees(result.data || []);
      } catch (error) {
        console.error("Error fetching employees:", error);
        setValidationError('Impossible de charger la liste des employés.');
      }
    };

    if (api) {
      fetchEmployees();
    }
  }, [api, token]);

  useEffect(() => {
    if (quittanceToEdit) {
      setFormState({
        id: quittanceToEdit.id,
        montant: quittanceToEdit.montant || '',
        date_paiement: quittanceToEdit.date_paiement ? quittanceToEdit.date_paiement.split(' ')[0] : '',
        date_emission: quittanceToEdit.date_emission ? quittanceToEdit.date_emission.split(' ')[0] : '',
        type: quittanceToEdit.type || 'client',
        numero_quittance: quittanceToEdit.numero_quittance || '',
        employee_id: quittanceToEdit.employee_id || '',
        periode_service: quittanceToEdit.periode_service || '',
      });
    }
  }, [quittanceToEdit]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormState(prevState => ({ ...prevState, [name]: value }));
  };

  const handleAutocompleteChange = (name, value) => {
    setFormState((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setValidationError('');
    const requiredFields = ['montant', 'date_paiement', 'date_emission', 'type', 'numero_quittance', 'employee_id', 'periode_service'];
    for (const field of requiredFields) {
      if (!formState[field]) {
        setValidationError(`Le champ '${field.replace('_', ' ')}' est obligatoire.`);
        return;
      }
    }
    
    const payload = {
      ...formState,
      montant: Number(formState.montant),
    };
    onSave(payload);
  };

  const handleCloseError = () => {
    setValidationError('');
    if (onClearBackendError) onClearBackendError();
  };

  if (!quittanceToEdit) {
    return null;
  }

  return createPortal(
    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
      role="dialog"
      aria-modal="true"
      aria-labelledby="modal-title"
    >
      <div className="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full h-[90vh] flex flex-col overflow-hidden">
        <h2 id="modal-title" className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md">
          Modifier la Quittance
        </h2>
        <div className="flex-grow overflow-y-auto p-6">
          <form id="editQuittanceForm" onSubmit={handleSubmit} className="space-y-4" noValidate>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Employé <span className="text-red-500">*</span></label>
              <AutocompleteSelect
                options={employees}
                value={formState.employee_id}
                onChange={(value) => handleAutocompleteChange('employee_id', value)}
                placeholder="Rechercher un employé"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Montant <span className="text-red-500">*</span></label>
              <input type="number" name="montant" value={formState.montant} onChange={handleChange} required min="0.01" step="0.01" className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Période de Service <span className="text-red-500">*</span></label>
              <input type="text" name="periode_service" value={formState.periode_service} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Date de Paiement <span className="text-red-500">*</span></label>
              <input type="date" name="date_paiement" value={formState.date_paiement} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Date d'Émission <span className="text-red-500">*</span></label>
              <input type="date" name="date_emission" value={formState.date_emission} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Numéro de Quittance <span className="text-red-500">*</span></label>
              <input type="text" name="numero_quittance" value={formState.numero_quittance} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">Type <span className="text-red-500">*</span></label>
              <select name="type" value={formState.type} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                <option value="client">Client</option>
                <option value="fournisseur">Fournisseur</option>
              </select>
            </div>
          </form>
        </div>
        <div className="flex flex-col p-4 border-t dark:border-gray-700">
          {(errorMessage || validationError) && (
            <div
              className="relative mb-3 text-red-500 text-sm bg-red-100 border border-red-400 rounded p-3 pr-10"
              role="alert"
              aria-live="assertive"
            >
              <span>{validationError || errorMessage}</span>
              <button
                onClick={handleCloseError}
                className="absolute top-1/2 right-2 transform -translate-y-1/2 text-red-500 hover:text-red-700"
                aria-label="Fermer la notification"
                type="button"
              >
                <X size={18} />
              </button>
            </div>
          )}
          <div className="flex justify-end space-x-3">
            <button type="button" onClick={onClose} className="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-100 dark:hover:bg-gray-700" disabled={loading}>
              Annuler
            </button>
            <button type="submit" form="editQuittanceForm" className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800" disabled={loading}>
              Modifier
            </button>
          </div>
        </div>
      </div>
    </div>,
    document.body
  );
}

export default EditQuittanceModal;
