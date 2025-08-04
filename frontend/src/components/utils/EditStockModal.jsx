// frontend/src/components/utils/EditStockModal.jsx

import React, { useState, useEffect } from 'react';

function EditStockModal({ stockItem, onClose, onSave }) {
  const [formData, setFormData] = useState(stockItem);

  // Mettre à jour le formulaire si stockItem change (par ex. si la prop est rechargée)
  useEffect(() => {
    setFormData(stockItem);
  }, [stockItem]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    onSave(formData);
  };

  return (
    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl max-w-lg w-full">
        <h3 className="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">
          Modifier l'article de stock
        </h3>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label htmlFor="produit_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
              ID Produit:
            </label>
            <input
              type="number"
              id="produit_id"
              name="produit_id"
              value={formData.produit_id}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div>
            <label htmlFor="quantity" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Quantité:
            </label>
            <input
              type="number"
              id="quantity"
              name="quantity"
              value={formData.quantity}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div>
            <label htmlFor="unit" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Unité:
            </label>
            <input
              type="text"
              id="unit"
              name="unit"
              value={formData.unit}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div>
            <label htmlFor="min" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
              Seuil minimum:
            </label>
            <input
              type="number"
              id="min"
              name="min"
              value={formData.min}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>
          <div>
            <label htmlFor="supplier_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
              ID Fournisseur (Optionnel):
            </label>
            <input
              type="number"
              id="supplier_id"
              name="supplier_id"
              value={formData.supplier_id || ''} // Gérer null/undefined
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
            />
          </div>
          <div>
            <label htmlFor="entrepot_id" className="block text-sm font-medium text-gray-700 dark:text-gray-300">
              ID Entrepôt:
            </label>
            <input
              type="number"
              id="entrepot_id"
              name="entrepot_id"
              value={formData.entrepot_id}
              onChange={handleChange}
              className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              required
            />
          </div>

          <div className="flex justify-end space-x-3 mt-6">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-100 dark:hover:bg-gray-700"
            >
              Annuler
            </button>
            <button
              type="submit"
              className="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800"
            >
              Enregistrer
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

export default EditStockModal;