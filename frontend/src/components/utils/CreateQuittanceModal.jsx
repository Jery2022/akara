// frontend/src/components/utils/CreateQuittanceModal.jsx
// --- Composant Modale: Création d'une quittance ---

import React, { useState } from 'react';
import { createPortal } from 'react-dom';

/**
 * Composant de modale pour la création d'une nouvelle quittance.
 * @param {object} props Les propriétés du composant.
 * @param {Function} props.onClose Fonction pour fermer la modale.
 * @param {Function} props.onSave Fonction pour sauvegarder la nouvelle quittance.
 * @param {Array} props.employees La liste des employés pour le sélecteur.
 * @param {boolean} props.loading Indique si une action de sauvegarde est en cours.
 */
function CreateQuittanceModal({ onClose, onSave, employees, loading }) {
  // Initialise l'état du formulaire avec des valeurs par défaut.
  const [formState, setFormState] = useState({
    montant: '',
    date_paiement: '',
    date_emission: '',
    type: '',
    numero_quittance: '',
    employee_id: '',
  });

  // Gère les changements dans les champs du formulaire.
  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormState(prevState => ({ ...prevState, [name]: value }));
  };

  // Gère la soumission du formulaire.
  const handleSubmit = (e) => {
    e.preventDefault();
    onSave(formState);
  };

  return createPortal(
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div className="bg-white p-6 rounded-xl shadow-2xl max-w-lg w-full">
        <h2 className="text-2xl font-bold mb-4 text-gray-800">
          Créer une nouvelle Quittance
        </h2>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {/* Montant */}
            <div>
              <label
                htmlFor="create-montant"
                className="block text-sm font-medium text-gray-700"
              >
                Montant <span className="text-red-500">*</span>
              </label>
              <input
                type="number"
                id="create-montant"
                name="montant"
                value={formState.montant}
                onChange={handleChange}
                required
                min="0.01"
                step="0.01"
                className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm"
              />
            </div>
            {/* Date de paiement */}
            <div>
              <label
                htmlFor="create-date_paiement"
                className="block text-sm font-medium text-gray-700"
              >
                Date de Paiement
              </label>
              <input
                type="date"
                id="create-date_paiement"
                name="date_paiement"
                value={formState.date_paiement}
                onChange={handleChange}
                className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm"
              />
            </div>
            {/* Date de émission */}
            <div>
              <label
                htmlFor="create-date_emission"
                className="block text-sm font-medium text-gray-700"
              >
                Date d'émission
              </label>
              <input
                type="date"
                id="create-date_emission"
                name="date_emission"
                value={formState.date_emission}
                onChange={handleChange}
                className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm"
              />
            </div>
            {/* Type de quittance */}
            <div>
              <label
                htmlFor="create-type"
                className="block text-sm font-medium text-gray-700 dark:text-gray-300"
              >
                Type de quittance
              </label>
              <select
                id="create-type"
                name="type"
                value={formState.type}
                onChange={handleChange}
                className="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"
                required
              >
                <option value="">Sélectionner un type</option>
                <option value="client">Client</option>
                <option value="fournisseur">Fournisseur</option>
              </select>
            </div>
            {/* Numéro de la quittance */}
            <div>
              <label
                htmlFor="create-numero_quittance"
                className="block text-sm font-medium text-gray-700"
              >
                Quittance N°
              </label>
              <input
                type="text"
                id="create-numero_quittance"
                name="numero_quittance"
                value={formState.numero_quittance}
                onChange={handleChange}
                className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm"
              />
            </div>
            {/* Employé associé */}
            <div>
              <label
                htmlFor="create-employee_id"
                className="block text-sm font-medium text-gray-700"
              >
                Employé <span className="text-red-500">*</span>
              </label>
              <select
                id="create-employee_id"
                name="employee_id"
                value={formState.employee_id || ''}
                onChange={handleChange}
                required
                className="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm"
              >
                <option value="">Sélectionnez un employé</option>
                {/* Affiche la liste des employés si c'est un tableau */}
                {Array.isArray(employees) && employees.map((employee) => (
                  <option key={employee.id} value={employee.id}>
                    {`${employee.firstname} ${employee.lastname} (${employee.fonction})`}
                  </option>
                ))}
              </select>
            </div>
          </div>
          <div className="flex justify-end space-x-2 mt-6">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors"
              disabled={loading}
            >
              Annuler
            </button>
            <button
              type="submit"
              className="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors shadow-md"
              disabled={loading}
            >
              Créer
            </button>
          </div>
        </form>
      </div>
    </div>,
    document.body
  );
}

export default CreateQuittanceModal;
