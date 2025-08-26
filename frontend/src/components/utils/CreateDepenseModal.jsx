import React, { useState } from 'react';
import MessageDisplay from './MessageDisplay'; // Assurez-vous que ce chemin est correct

// Composant pour la modale de création d'une dépense
const CreateDepenseModal = ({ onClose, onSave }) => {
  const [formData, setFormData] = useState({
    produit_id: '',
    supplier_id: '',
    quantity: '',
    price: '',
    nature: '',
    category: '',
    contrat_id: '',
    date_depense: new Date().toISOString().split('T')[0], // Date du jour par défaut
    description: '',
  });

  const [message, setMessage] = useState(null);

  // Gère les changements dans le formulaire
  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  // Gère la soumission du formulaire
  const handleSubmit = (e) => {
    e.preventDefault();
    // Validation simple pour s'assurer que les champs requis ne sont pas vides
    if (
      !formData.produit_id ||
      !formData.supplier_id ||
      !formData.quantity ||
      !formData.price ||
      !formData.nature ||
      !formData.category
    ) {
      setMessage({
        type: 'error',
        text: 'Veuillez remplir tous les champs obligatoires.',
      });
      return;
    }
    // Appel à la fonction de sauvegarde passée en prop
    onSave(formData);
    onClose();
  };

  const closeMessage = () => setMessage(null);

  return (
    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div className="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-lg w-full h-[500px] flex flex-col overflow-hidden">
        <h3 className="text-lg font-semibold text-white bg-emerald-600 p-4 shadow-md">Ajouter une dépense</h3>
        <div className="flex-grow overflow-y-auto p-6">
          <MessageDisplay message={message} onClose={closeMessage} />
          <form id="depenseForm" onSubmit={handleSubmit} className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                ID Produit <span className="text-red-500">*</span>
              </label>
              <input
                type="number"
                name="produit_id"
                value={formData.produit_id}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                ID Fournisseur <span className="text-red-500">*</span>
              </label>
              <input
                type="number"
                name="supplier_id"
                value={formData.supplier_id}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Quantité <span className="text-red-500">*</span>
              </label>
              <input
                type="number"
                name="quantity"
                value={formData.quantity}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Prix <span className="text-red-500">*</span>
              </label>
              <input
                type="number"
                name="price"
                value={formData.price}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Nature <span className="text-red-500">*</span>
              </label>
              <input
                type="text"
                name="nature"
                value={formData.nature}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Catégorie <span className="text-red-500">*</span>
              </label>
              <input
                type="text"
                name="category"
                value={formData.category}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                ID Contrat (facultatif)
              </label>
              <input
                type="number"
                name="contrat_id"
                value={formData.contrat_id}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Date de la dépense
              </label>
              <input
                type="date"
                name="date_depense"
                value={formData.date_depense}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 dark:text-gray-300">
                Description
              </label>
              <textarea
                name="description"
                value={formData.description}
                onChange={handleChange}
                rows="3"
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
              ></textarea>
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
            form="depenseForm"
            className="px-4 py-2 bg-emerald-600 text-white rounded-md hover:bg-emerald-700 dark:bg-emerald-700 dark:hover:bg-emerald-800"
          >
            Ajouter
          </button>
        </div>
      </div>
    </div>
  );
};

export default CreateDepenseModal;
