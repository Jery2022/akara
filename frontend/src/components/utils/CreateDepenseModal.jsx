import React, { useState } from 'react';
import { X } from 'lucide-react';
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
    <div className="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex justify-center items-center">
      <div className="relative p-6 border w-96 max-w-lg shadow-lg rounded-xl bg-white animate-fade-in-up">
        {/* En-tête de la modale */}
        <div className="flex justify-between items-center pb-3 border-b-2 mb-4">
          <h3 className="text-xl font-bold text-gray-900">Ajouter une dépense</h3>
          <button onClick={onClose} className="text-gray-400 hover:text-gray-600">
            <X size={24} />
          </button>
        </div>

        {/* Affichage des messages */}
        <MessageDisplay message={message} onClose={closeMessage} />

        {/* Formulaire */}
        <form onSubmit={handleSubmit}>
          {/* ID du Produit (number) */}
          <div className="mb-4">
            <label className="block text-gray-700 text-sm font-bold mb-2">
              ID Produit <span className="text-red-500">*</span>
            </label>
            <input
              type="number"
              name="produit_id"
              value={formData.produit_id}
              onChange={handleChange}
              className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              required
            />
          </div>

          {/* ID du Fournisseur (number) */}
          <div className="mb-4">
            <label className="block text-gray-700 text-sm font-bold mb-2">
              ID Fournisseur <span className="text-red-500">*</span>
            </label>
            <input
              type="number"
              name="supplier_id"
              value={formData.supplier_id}
              onChange={handleChange}
              className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              required
            />
          </div>

          {/* Quantité (number) */}
          <div className="mb-4">
            <label className="block text-gray-700 text-sm font-bold mb-2">
              Quantité <span className="text-red-500">*</span>
            </label>
            <input
              type="number"
              name="quantity"
              value={formData.quantity}
              onChange={handleChange}
              className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              required
            />
          </div>

          {/* Prix (number) */}
          <div className="mb-4">
            <label className="block text-gray-700 text-sm font-bold mb-2">
              Prix <span className="text-red-500">*</span>
            </label>
            <input
              type="number"
              name="price"
              value={formData.price}
              onChange={handleChange}
              className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              required
            />
          </div>

          {/* Nature (text) */}
          <div className="mb-4">
            <label className="block text-gray-700 text-sm font-bold mb-2">
              Nature <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              name="nature"
              value={formData.nature}
              onChange={handleChange}
              className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              required
            />
          </div>

          {/* Catégorie (text) */}
          <div className="mb-4">
            <label className="block text-gray-700 text-sm font-bold mb-2">
              Catégorie <span className="text-red-500">*</span>
            </label>
            <input
              type="text"
              name="category"
              value={formData.category}
              onChange={handleChange}
              className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              required
            />
          </div>

          {/* ID du Contrat (number - facultatif) */}
          <div className="mb-4">
            <label className="block text-gray-700 text-sm font-bold mb-2">
              ID Contrat (facultatif)
            </label>
            <input
              type="number"
              name="contrat_id"
              value={formData.contrat_id}
              onChange={handleChange}
              className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            />
          </div>

          {/* Date de la dépense (date) */}
          <div className="mb-4">
            <label className="block text-gray-700 text-sm font-bold mb-2">
              Date de la dépense
            </label>
            <input
              type="date"
              name="date_depense"
              value={formData.date_depense}
              onChange={handleChange}
              className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            />
          </div>

          {/* Description (textarea) */}
          <div className="mb-4">
            <label className="block text-gray-700 text-sm font-bold mb-2">
              Description
            </label>
            <textarea
              name="description"
              value={formData.description}
              onChange={handleChange}
              rows="3"
              className="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            ></textarea>
          </div>

          {/* Boutons d'action */}
          <div className="flex items-center justify-end space-x-4">
            <button
              type="button"
              onClick={onClose}
              className="bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-md hover:bg-gray-400 transition-colors duration-200"
            >
              Annuler
            </button>
            <button
              type="submit"
              className="bg-blue-600 text-white font-bold py-2 px-4 rounded-md hover:bg-blue-700 transition-colors duration-200"
            >
              Ajouter
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default CreateDepenseModal;
