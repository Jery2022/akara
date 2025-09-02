// frontend/src/components/utils/CreateVenteModal.jsx

import React, { useState, useEffect } from 'react';
import { X } from 'lucide-react';
import AutocompleteSelect from './AutocompleteSelect';

function CreateVenteModal({ onClose, onSave, api, errorMessage, onClearBackendError }) {
  const [formData, setFormData] = useState({
    name: '',
    type: '',
    amount: '',
    date_vente: '', // Format YYYY-MM-DD
    category: '',
    customer_id: '',  
    contrat_id: '',  
    description: '',  
  });
  const [customers, setCustomers] = useState([]);
  const [contrats, setContrats] = useState([]);
  const [validationError, setValidationError] = useState('');
  const token = localStorage.getItem('authToken');

  useEffect(() => {
    const fetchData = async (endpoint, setter) => {
      try {
        const response = await fetch(`${api}/${endpoint}`, {
          headers: { 'Authorization': `Bearer ${token}` },
        });
        const result = await response.json();
        if (result.data) {
          setter(result.data);
        }
      } catch (error) {
        console.error(`Erreur lors du chargement de ${endpoint}:`, error);
      }
    };

    fetchData('customers', setCustomers);
    fetchData('contrats', setContrats);
  }, [api, token]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleAutocompleteChange = (name, value) => {
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setValidationError('');

    if (!formData.type || !formData.amount || !formData.date_vente || !formData.category) {
      setValidationError('Le type, le montant, la date et la catégorie sont obligatoires.');
      return;
    }
    if (parseFloat(formData.amount) < 0) {
      setValidationError('Le montant ne peut pas être négatif.');
      return;
    }

    onSave(formData);
  };

  const handleCloseError = () => {
    setValidationError('');
    if (onClearBackendError) {
      onClearBackendError();
    }
  };

  return (
    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full h-[500px] flex flex-col overflow-hidden">
        <h3 className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md">
          Enregistrer une nouvelle vente
        </h3>
        <div className="flex-grow overflow-y-auto p-6">
          <form id="venteForm" onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label htmlFor="name" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Nom / Référence:
              </label>
              <input
                type="text"
                id="name"
                name="name"
                value={formData.name}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              />
            </div>
            <div>
              <label htmlFor="type" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Type:
              </label>
              <select
                id="type"
                name="type"
                value={formData.type}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              >
                <option value="chèque">Chèque</option>
                <option value="espèces">Espèces</option>
                <option value="virement">Virement</option>
              </select>
            </div>
            <div>
              <label htmlFor="amount" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Montant:
              </label>
              <input
                type="number"
                id="amount"
                name="amount"
                value={formData.amount}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              />
            </div>
            <div>
              <label htmlFor="date_vente" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Date de vente (Format YYYY-MM-DD):
              </label>
              <input
                type="date" // Utilise type="date" pour une sélection facile
                id="date_vente"
                name="date_vente"
                value={formData.date_vente}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              />
            </div>
            <div>
              <label htmlFor="category" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Catégorie:
              </label>
              <select
                id="category"
                name="category"
                value={formData.category}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              >
                <option value="fournitures">Fournitures</option>
                <option value="électricité">Électricité</option>
                <option value="téléphone">Téléphone</option>
                <option value="carburant">Carburant</option>
                <option value="eau">Eau</option>
                <option value="mobiliers">Mobiliers</option>
                <option value="fiscalité">Fiscalité</option>
                <option value="impôts">Impôts</option>
                <option value="taxes">Taxes</option>
              </select>
            </div>
            <div>
              <label htmlFor="customer_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Client (Optionnel):
              </label>
              <AutocompleteSelect
                options={customers}
                value={formData.customer_id}
                onChange={(value) => handleAutocompleteChange('customer_id', value)}
                placeholder="Rechercher un client..."
              />
            </div>
            <div>
              <label htmlFor="contrat_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Contrat (Optionnel):
              </label>
              <AutocompleteSelect
                options={contrats}
                value={formData.contrat_id}
                onChange={(value) => handleAutocompleteChange('contrat_id', value)}
                placeholder="Rechercher un contrat..."
              />
            </div>
            <div>
              <label htmlFor="description" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Description (Optionnel):
              </label>
              <textarea
                id="description"
                name="description"
                value={formData.description}
                onChange={handleChange}
                rows="3"
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              ></textarea>
            </div>
          </form>
        </div>
        <div className="flex flex-col p-4 border-t dark:border-gray-700">
          {(errorMessage || validationError) && (
            <div className="relative mb-3 text-red-500 text-sm bg-red-100 border border-red-400 rounded p-3 pr-10">
              <span>{validationError || errorMessage}</span>
              <button onClick={handleCloseError} className="absolute top-1/2 right-2 transform -translate-y-1/2 text-red-500 hover:text-red-700" aria-label="Fermer la notification">
                <X size={18} />
              </button>
            </div>
          )}
          <div className="flex justify-end space-x-3">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-100 dark:hover:bg-gray-700"
            >
              Annuler
            </button>
            <button
              type="submit"
              form="venteForm"
              className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800"
            >
              Ajouter
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default CreateVenteModal;
