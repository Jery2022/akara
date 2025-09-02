import React, { useState, useEffect } from 'react';
import { createPortal } from 'react-dom';
import AutocompleteSelect from './AutocompleteSelect';
import { X } from 'lucide-react';

function EditAchatModal({ api, achatToEdit, onClose, onSave, loading, errorMessage, onClearBackendError }) {
  const [formState, setFormState] = useState({
    id: '',
    name: '',
    type: '',
    amount: '',
    date_achat: '',
    category: '',
    supplier_id: '',
    contrat_id: '',
    description: '',
    status: ''
  });

  const [suppliers, setSuppliers] = useState([]);
  const [contrats, setContrats] = useState([]);
  const [validationError, setValidationError] = useState('');
  const token = localStorage.getItem('authToken');

  useEffect(() => {
    const fetchData = async (endpoint, setter) => {
      try {
        const response = await fetch(`${api}/${endpoint}`, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        if (!response.ok) throw new Error(`Failed to fetch ${endpoint}`);
        const result = await response.json();
        setter(result.data || []);
      } catch (error) {
        console.error(`Error fetching ${endpoint}:`, error);
        setValidationError(`Impossible de charger les ${endpoint}.`);
      }
    };

    if (api) {
      fetchData('suppliers', setSuppliers);
      fetchData('contrats', setContrats);
    }
  }, [api, token]);

  useEffect(() => {
    if (achatToEdit) {
      setFormState({
        id: achatToEdit.id,
        name: achatToEdit.name || '',
        type: achatToEdit.type || '',
        amount: achatToEdit.amount || '',
        date_achat: achatToEdit.date_achat ? achatToEdit.date_achat.split(' ')[0] : '',
        category: achatToEdit.category || '',
        supplier_id: achatToEdit.supplier_id || '',
        contrat_id: achatToEdit.contrat_id || '',
        description: achatToEdit.description || '',
        status: achatToEdit.status || 'en attente'
      });
    }
  }, [achatToEdit]);

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

    if (!formState.type || !formState.amount || !formState.category) {
      setValidationError('Les champs Type, Montant et Catégorie sont obligatoires.');
      return;
    }

    const payload = {
      ...formState,
      amount: Number(formState.amount),
      supplier_id: formState.supplier_id || null,
      contrat_id: formState.contrat_id || null,
    };
    onSave(payload);
  };

  const handleCloseError = () => {
    setValidationError('');
    if (onClearBackendError) onClearBackendError();
  };

  if (!achatToEdit) {
    return null;
  }

  return createPortal(
    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full h-[90vh] flex flex-col overflow-hidden">
        <h2 className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md">Modifier l'Achat</h2>
        <div className="flex-grow overflow-y-auto p-6">
          <form id="editAchatForm" onSubmit={handleSubmit} className="space-y-4" noValidate>
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div className="sm:col-span-2">
                <label htmlFor="name" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom de l'achat</label>
                <input type="text" id="name" name="name" value={formState.name} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
              </div>
              <div>
                <label htmlFor="type" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Type <span className="text-red-500">*</span></label>
                <select id="type" name="type" value={formState.type} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                  <option value="espèces">Espèces</option>
                  <option value="virement">Virement</option>
                  <option value="chèque">Chèque</option>
                </select>
              </div>
              <div>
                <label htmlFor="amount" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Montant <span className="text-red-500">*</span></label>
                <input type="number" id="amount" name="amount" value={formState.amount} onChange={handleChange} required min="0.01" step="0.01" className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
              </div>
              <div>
                <label htmlFor="date_achat" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Date de l'achat</label>
                <input type="date" id="date_achat" name="date_achat" value={formState.date_achat} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200" />
              </div>
              <div>
                <label htmlFor="category" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Catégorie <span className="text-red-500">*</span></label>
                <select id="category" name="category" value={formState.category} onChange={handleChange} required className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
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
                <label htmlFor="status" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Statut</label>
                <select id="status" name="status" value={formState.status} onChange={handleChange} className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                  <option value="en attente">En attente</option>
                  <option value="réglé">Réglé</option>
                  <option value="annulé">Annulé</option>
                </select>
              </div>
              <div className="sm:col-span-2">
                <label htmlFor="supplier_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Fournisseur</label>
                <AutocompleteSelect
                  options={suppliers}
                  value={formState.supplier_id}
                  onChange={(value) => handleAutocompleteChange('supplier_id', value)}
                  placeholder="Rechercher un fournisseur"
                />
              </div>
              <div className="sm:col-span-2">
                <label htmlFor="contrat_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Contrat</label>
                <AutocompleteSelect
                  options={contrats}
                  value={formState.contrat_id}
                  onChange={(value) => handleAutocompleteChange('contrat_id', value)}
                  placeholder="Rechercher un contrat"
                />
              </div>
            </div>
            <div>
              <label htmlFor="description" className="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
              <textarea id="description" name="description" value={formState.description} onChange={handleChange} rows="3" className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"></textarea>
            </div>
          </form>
        </div>
        <div className="flex flex-col p-4 border-t dark:border-gray-700">
          {(errorMessage || validationError) && (
            <div className="relative mb-3 text-red-500 text-sm bg-red-100 border border-red-400 rounded p-3 pr-10" role="alert">
              <span>{validationError || errorMessage}</span>
              <button onClick={handleCloseError} className="absolute top-1/2 right-2 transform -translate-y-1/2 text-red-500 hover:text-red-700" aria-label="Fermer la notification">
                <X size={18} />
              </button>
            </div>
          )}
          <div className="flex justify-end space-x-3">
            <button type="button" onClick={onClose} className="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-100 dark:hover:bg-gray-700" disabled={loading}>Annuler</button>
            <button type="submit" form="editAchatForm" className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800" disabled={loading}>Modifier</button>
          </div>
        </div>
      </div>
    </div>,
    document.body
  );
}

export default EditAchatModal;
