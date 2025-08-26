// frontend/src/components/utils/CreateStockModal.jsx

import React, { useState } from 'react';

function CreateStockModal({ onClose, onSave }) {
  const [formData, setFormData] = useState({
    //produit_id: '',
    quantity: '',
    unit: '',
    min: '',
    supplier_id: '',  
    entrepot_id: '',
  });

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
      <div className="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full h-[500px] flex flex-col overflow-hidden">
        <h3 className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md">
          Ajouter un nouvel article de stock
        </h3>
        <div className="flex-grow overflow-y-auto p-6">
          <form id="stockForm" onSubmit={handleSubmit} className="space-y-4">
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
                value={formData.supplier_id}
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
          </form>
        </div>
        <div className="flex justify-end space-x-3 p-4 border-t dark:border-gray-700">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-100 dark:hover:bg-gray-700"
            >
              Annuler
            </button>
            <button
              type="submit"
              form="stockForm"
              className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800"
            >
              Ajouter
            </button>
        </div>
      </div>
    </div>
  );
}

export default CreateStockModal;
