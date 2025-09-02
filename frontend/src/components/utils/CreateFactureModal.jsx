import React, { useState, useEffect } from 'react';
import { createPortal } from 'react-dom';
import AutocompleteSelect from './AutocompleteSelect';
import { X } from 'lucide-react';

function CreateFactureModal({ api, onClose, onSave, loading, errorMessage, onClearBackendError }) {
  const [formState, setFormState] = useState({
    customer_id: '',
    date_facture: new Date().toISOString().slice(0, 10),
    amount_total: '',
    amount_tva: '',
    amount_css: '',
    amount_ttc: '',
    status: 'en attente',
    avance_status: 'non',
  });
  
  const [validationError, setValidationError] = useState('');
  const [customers, setCustomers] = useState([]);



  useEffect(() => {
    const fetchCustomers = async () => {
      const token = localStorage.getItem('authToken');
      try {
        const response = await fetch(`${api}/customers`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        if (!response.ok) {
          throw new Error('Network response was not ok');
        }
        const result = await response.json();
        if (result.data) {
          setCustomers(result.data);
        }
      } catch (error) {
        console.error('Failed to fetch customers:', error);
        setValidationError('Impossible de charger la liste des clients.');
      }
    };

    if (api) {
      fetchCustomers();
    }
  }, [api]);



  // Centralized calculation of amount_ttc using useEffect for consistency
  useEffect(() => {
    const total = parseFloat(formState.amount_total) || 0;
    const tva = parseFloat(formState.amount_tva) || 0;
    const css = parseFloat(formState.amount_css) || 0;
    const ttc = (total + tva + css).toFixed(2);
    if (ttc !== formState.amount_ttc) {
      setFormState((prev) => ({ ...prev, amount_ttc: ttc }));
    }
  }, [formState.amount_total, formState.amount_tva, formState.amount_css,  formState.amount_ttc]);

  // Handle input changes with type normalization for numbers
  const handleChange = (e) => {
    const { name, value, type } = e.target;
    let val = value;
    if (type === 'number') {
      // Normalize empty string to '' to allow clearing input
      val = value === '' ? '' : Number(value);
    }
    setFormState((prev) => ({ ...prev, [name]: val }));
  };

  const handleAutocompleteChange = (name, value) => {
    setFormState((prev) => ({ ...prev, [name]: value }));
  };

  const validateForm = () => {
    if (!formState.customer_id) {
      setValidationError('Le champ Client est obligatoire.');
      return false;
    }
    if (!formState.date_facture) {
      setValidationError('Le champ Date de facture est obligatoire.');
      return false;
    }
    if (formState.amount_total === '' || isNaN(formState.amount_total) || formState.amount_total < 0) {
      setValidationError('Le Montant Total doit être un nombre positif.');
      return false;
    }
    return true;
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setValidationError('');
    if (!validateForm()) return;
    // Convert numeric fields to numbers before saving
    const payload = {
      ...formState,
      amount_total: Number(formState.amount_total),
      amount_tva: Number(formState.amount_tva) || 0,
      amount_css: Number(formState.amount_css) || 0,
      amount_ttc: Number(formState.amount_ttc),
    };
    onSave(payload);
  };

  const handleCloseError = () => {
    setValidationError('');
    if (onClearBackendError) onClearBackendError();
  };

  return createPortal(
    <div
      className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50"
      role="dialog"
      aria-modal="true"
      aria-labelledby="modal-title"
    >
      <div className="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full flex flex-col overflow-hidden">
        <h2
          id="modal-title"
          className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md"
        >
          Créer une Facture
        </h2>
        <div className="flex-grow overflow-y-auto p-6">
          <form id="factureForm" onSubmit={handleSubmit} className="space-y-4" noValidate>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div className="sm:col-span-2">
                <label
                  htmlFor="customer_id"
                  className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                  Client <span className="text-red-500">*</span>
                </label>
                <AutocompleteSelect
                  // id="customer_id"
                  options={customers}
                  value={formState.customer_id}
                  onChange={(value) => handleAutocompleteChange('customer_id', value)}
                  placeholder="Rechercher un client..."
                />
              </div>
              <div>
                <label
                  htmlFor="date_facture"
                  className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                  Date de Facture <span className="text-red-500">*</span>
                </label>
                <input
                  type="date"
                  id="date_facture"
                  name="date_facture"
                  value={formState.date_facture}
                  onChange={handleChange}
                  required
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                />
              </div>
              <div>
                <label
                  htmlFor="status"
                  className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                  Statut <span className="text-red-500">*</span>
                </label>
                <select
                  id="status"
                  name="status"
                  value={formState.status}
                  onChange={handleChange}
                  required
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                >
                  <option value="en attente">En attente</option>
                  <option value="payée">Payée</option>
                  <option value="annulée">Annulée</option>
                </select>
              </div>
              <div>
                <label
                  htmlFor="avance_status"
                  className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                  Avance Payée <span className="text-red-500">*</span>
                </label>
                <select
                  id="avance_status"
                  name="avance_status"
                  value={formState.avance_status}
                  onChange={handleChange}
                  required
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                >
                  <option value="non">Non</option>
                  <option value="oui">Oui</option>
                </select>
              </div>
              <div>
                <label
                  htmlFor="amount_total"
                  className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                  Montant Total (HT) <span className="text-red-500">*</span>
                </label>
                <input
                  type="number"
                  id="amount_total"
                  name="amount_total"
                  value={formState.amount_total}
                  onChange={handleChange}
                  required
                  min="0"
                  step="0.01"
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                  aria-describedby="amount_total_help"
                />
              </div>
              <div>
                <label
                  htmlFor="amount_tva"
                  className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                  Montant TVA
                </label>
                <input
                  type="number"
                  id="amount_tva"
                  name="amount_tva"
                  value={formState.amount_tva}
                  onChange={handleChange}
                  min="0"
                  step="0.01"
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                />
              </div>
              <div>
                <label
                  htmlFor="amount_css"
                  className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                  Montant CSS
                </label>
                <input
                  type="number"
                  id="amount_css"
                  name="amount_css"
                  value={formState.amount_css}
                  onChange={handleChange}
                  min="0"
                  step="0.01"
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                />
              </div>
              <div className="sm:col-span-2">
                <label
                  htmlFor="amount_ttc"
                  className="block text-sm font-medium text-gray-700 dark:text-gray-300"
                >
                  Montant TTC <span className="text-red-500">*</span>
                </label>
                <input
                  type="number"
                  id="amount_ttc"
                  name="amount_ttc"
                  value={formState.amount_ttc}
                  readOnly
                  required
                  min="0"
                  step="0.01"
                  className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-600 dark:border-gray-500 dark:text-gray-300"
                  aria-readonly="true"
                />
              </div>
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
            <button
              type="button"
              onClick={onClose}
              disabled={loading}
              className="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 disabled:opacity-50 dark:bg-gray-600 dark:text-gray-100 dark:hover:bg-gray-700"
            >
              Annuler
            </button>
            <button
              type="submit"
              form="factureForm"
              disabled={loading}
              className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 disabled:opacity-50 dark:bg-emerald-700 dark:hover:bg-emerald-800"
            >
              {loading ? 'Ajout en cours...' : 'Ajouter'}
            </button>
          </div>
        </div>
      </div>
    </div>,
    document.body
  );
}

export default CreateFactureModal;
