import React, { useState, useEffect } from 'react';
import AutocompleteSelect from './AutocompleteSelect';
import { X } from 'lucide-react';

function EditStockModal({ api, stockItem, onClose, onSave }) {
  const [formData, setFormData] = useState(stockItem);
  const [suppliers, setSuppliers] = useState([]);
  const [entrepots, setEntrepots] = useState([]);
  const [error, setError] = useState(null);

  useEffect(() => {
    // Initialiser le formulaire quand stockItem change
    setFormData(stockItem);
  }, [stockItem]);

  useEffect(() => {
    const fetchData = async (url, setData, name) => {
      const token = localStorage.getItem('authToken');
      if (!token) {
        console.error('Authentication token not found.');
        return;
      }
      try {
        const response = await fetch(url, {
          headers: { 'Authorization': `Bearer ${token}` }
        });
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        const data = await response.json();
        setData(data.data || []);
      } catch (err) {
        console.error(`Failed to fetch ${name}:`, err);
        setError(`Impossible de charger les ${name}.`);
      }
    };

    fetchData(`${api}/suppliers`, setSuppliers, 'fournisseurs');
    fetchData(`${api}/entrepots`, setEntrepots, 'entrepôts');
  }, [api]); // Dépendance `api`

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleAutocompleteChange = (name, value) => {
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    onSave(formData);
  };

  // Le nom du produit est déjà dans formData (stockItem)
  const produitNom = formData?.produit_nom || 'N/A';

  return (
    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full h-[500px] flex flex-col overflow-hidden">
        <h3 className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md">
          Modifier l'article de stock
        </h3>
        <div className="flex-grow overflow-y-auto p-6">
          <form id="editStockForm" onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label htmlFor="produit_nom" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Désignation :
              </label>
              <input
                type="text"
                id="produit_nom"
                name="produit_nom"
                value={produitNom}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm bg-gray-100 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-400"
                disabled
                readOnly
              />
            </div>
            <div>
              <label htmlFor="quantity" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Quantité en stock :
              </label>
              <input
                type="number"
                id="quantity"
                name="quantity"
                value={formData.quantity || ''}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              />
            </div>
            <div>
              <label htmlFor="min" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Seuil minimum d'alerte :
              </label>
              <input
                type="number"
                id="min"
                name="min"
                value={formData.min || ''}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              />
            </div>
            <div>
              <label htmlFor="unit" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Unité (Ex: kg, L, pcs, m2, m3, etc.):
              </label>
              <input
                type="text"
                id="unit"
                name="unit"
                value={formData.unit || ''}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              />
            </div>
            <div>
              <label htmlFor="supplier_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Fournisseur (Optionnel) :
              </label>
              <AutocompleteSelect
                options={suppliers}
                value={formData.supplier_id || ''}
                onChange={(value) => handleAutocompleteChange('supplier_id', value)}
                placeholder="Rechercher un fournisseur"
              />
            </div>
            <div>
              <label htmlFor="entrepot_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Entrepôt de stockage (Obligatoire) :
              </label>
              <AutocompleteSelect
                options={entrepots}
                value={formData.entrepot_id || ''}
                onChange={(value) => handleAutocompleteChange('entrepot_id', value)}
                placeholder="Rechercher un entrepôt"
              />
            </div>
          </form>
        </div>
        <div className="flex flex-col p-4 border-t dark:border-gray-700">
          {error && (
            <div className="relative mb-3 text-red-500 text-sm bg-red-100 border border-red-400 rounded p-3 pr-10">
              <span>{error}</span>
              <button
                onClick={() => setError(null)}
                className="absolute top-1/2 right-2 transform -translate-y-1/2 text-red-500 hover:text-red-700"
                aria-label="Fermer la notification"
              >
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
              form="editStockForm"
              className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800"
            >
              Enregistrer
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}

export default EditStockModal;
