// frontend/src/components/utils/CreateRecetteModal.jsx
// --- Composant Modale: Création d'une recette ---

import React, { useState, useEffect } from 'react';
import { createPortal } from 'react-dom';

/**
 * Composant de modale pour la création d'une recette.
 * @param {object} props Les propriétés du composant.
 * @param {Function} props.onClose Fonction pour fermer la modale.
 * @param {Function} props.onSave Fonction pour sauvegarder les données de la nouvelle recette.
 * @param {Array} props.produits La liste des produits pour le sélecteur.
 * @param {Array} props.customers La liste des clients pour le sélecteur.
 * @param {Array} props.contrats La liste des contrats pour le sélecteur.
 */
function CreateRecetteModal ({ onClose, onSave, produits, customers, contrats }) {
  // Initialise l'état du formulaire avec des valeurs par défaut
  const [formState, setFormState] = useState({
    produit_id: '',
    customer_id: '',
    contrat_id: '',
    quantity: 1,
    price: 0,
    total: 0,
    date_recette: new Date().toISOString().slice(0, 10),
    description: '',
    nature: 'vente',
    category: 'construction',
  });

  // Utilise useEffect pour calculer le total chaque fois que la quantité ou le prix changent
  useEffect(() => {
    const quantity = parseFloat(formState.quantity) || 0;
    const price = parseFloat(formState.price) || 0;
    setFormState(prevState => ({ ...prevState, total: (quantity * price).toFixed(2) }));
  }, [formState.quantity, formState.price]);

  // Gère la modification des champs du formulaire
  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormState(prevState => ({
      ...prevState,
      [name]: value
    }));
  };

  // Gère la soumission du formulaire
  const handleSubmit = (e) => {
    e.preventDefault();
    onSave(formState);
  };
  
  // createPortal rend le composant dans un autre nœud du DOM (ici, le body)
  // ce qui est une bonne pratique pour les modales.
  return createPortal(
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div className="bg-white p-6 rounded-xl shadow-2xl max-w-lg w-full">
        <h2 className="text-2xl font-bold mb-4 text-gray-800">Ajouter une Recette</h2>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label htmlFor="produit_id" className="block text-sm font-medium text-gray-700">Produit <span className="text-red-500">*</span></label>
              <select id="produit_id" name="produit_id" value={formState.produit_id} onChange={handleChange} required className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm">
                <option value="">Sélectionnez un produit</option>
                {produits && produits.map(p => (<option key={p.id} value={p.id}>{p.name}</option>))}
              </select>
            </div>
            <div>
              <label htmlFor="customer_id" className="block text-sm font-medium text-gray-700">Client <span className="text-red-500">*</span></label>
              <select id="customer_id" name="customer_id" value={formState.customer_id} onChange={handleChange} required className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm">
                <option value="">Sélectionnez un client</option>
                {customers && customers.map(c => (<option key={c.id} value={c.id}>{c.name}</option>))}
              </select>
            </div>
            <div>
              <label htmlFor="contrat_id" className="block text-sm font-medium text-gray-700">Contrat</label>
              <select id="contrat_id" name="contrat_id" value={formState.contrat_id || ''} onChange={handleChange} className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm">
                <option value="">Sans contrat</option>
                {contrats && contrats.map(c => (<option key={c.id} value={c.id}>{c.ref}</option>))}
              </select>
            </div>
            <div>
              <label htmlFor="date_recette" className="block text-sm font-medium text-gray-700">Date de la Recette</label>
              <input type="date" id="date_recette" name="date_recette" value={formState.date_recette} onChange={handleChange} className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm" />
            </div>
            <div>
              <label htmlFor="quantity" className="block text-sm font-medium text-gray-700">Quantité <span className="text-red-500">*</span></label>
              <input type="number" id="quantity" name="quantity" value={formState.quantity} onChange={handleChange} required min="1" className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm" />
            </div>
            <div>
              <label htmlFor="price" className="block text-sm font-medium text-gray-700">Prix unitaire <span className="text-red-500">*</span></label>
              <input type="number" id="price" name="price" value={formState.price} onChange={handleChange} required min="0.01" step="0.01" className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm" />
            </div>
            <div>
              <label htmlFor="total" className="block text-sm font-medium text-gray-700">Total</label>
              <input type="text" id="total" name="total" value={formState.total} readOnly className="mt-1 block w-full px-3 py-2 bg-gray-200 text-gray-600 border border-gray-300 rounded-md shadow-sm cursor-not-allowed" />
            </div>
            <div>
              <label htmlFor="nature" className="block text-sm font-medium text-gray-700">Nature</label>
              <select id="nature" name="nature" value={formState.nature} onChange={handleChange} className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm">
                <option value="vente">Vente</option>
                <option value="location">Location</option>
              </select>
            </div>
            <div>
              <label htmlFor="category" className="block text-sm font-medium text-gray-700">Catégorie</label>
              <select id="category" name="category" value={formState.category} onChange={handleChange} className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm">
                <option value="construction">Construction</option>
                <option value="sécurité">Sécurité</option>
                <option value="hygiène">Hygiène</option>
                <option value="entretien">Entretien</option>
                <option value="logistique">Logistique</option>
                <option value="mobilité">Mobilité</option>
              </select>
            </div>
          </div>
          <div>
            <label htmlFor="description" className="block text-sm font-medium text-gray-700">Description</label>
            <textarea id="description" name="description" value={formState.description} onChange={handleChange} rows="3" className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm"></textarea>
          </div>
          <div className="flex justify-end space-x-2 mt-6">
            <button type="button" onClick={onClose} className="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">Annuler</button>
            <button type="submit" className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors shadow-md">Ajouter</button>
          </div>
        </form>
      </div>
    </div>,
    document.body
  );
};

export default CreateRecetteModal;
